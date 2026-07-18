<?php
/**
 * EDGPA_Fixes - Design Comuni Italia accessibility fixes.
 *
 * Detects the Design Comuni WordPress theme via multi-signal fingerprinting
 * (theme text-domain, unique PHP functions, PA-specific custom post types)
 * and applies five targeted WCAG 2.2 AA fixes that the theme does not cover:
 *
 * 1. aria-current="page" on active nav items (WCAG 4.1.2)
 * 2. aria-labelledby on the search modal (WCAG 4.1.2)
 * 3. Generic placeholder alt text replacement (WCAG 1.1.1)
 * 4. Leaflet map accessible name (WCAG 1.1.1 / 1.3.1)
 * 5. aria-expanded initial state on megamenu items (WCAG 4.1.2)
 *
 * Hooks into the parent plugin's `livqacea_sanitized_html` filter for buffer-based
 * fixes, and uses standard WordPress nav filters for menu fixes.
 *
 * @package EDG_PA_Italia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class EDGPA_Fixes
 */
class EDGPA_Fixes {

	/**
	 * Cached detection result - computed once per request.
	 *
	 * @var bool|null
	 */
	private static $detected = null;

	// -----------------------------------------------------------------------
	// Bootstrap
	// -----------------------------------------------------------------------

	/**
	 * Registers the bootstrap hook. Called from the main plugin file.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Detection runs after theme is fully loaded.
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_register_hooks' ) );
	}

	/**
	 * Registers the fix hooks, but only if the Design Comuni theme is detected.
	 *
	 * @return void
	 */
	public static function maybe_register_hooks(): void {
		if ( ! self::is_design_comuni() ) {
			return;
		}

		// Fix 1: aria-current="page" on active menu items.
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'fix_aria_current' ), 10, 4 );

		// Fix 5: aria-haspopup + aria-expanded initial state on items with children.
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'fix_megamenu_aria' ), 10, 4 );

		// Fix 3: post thumbnail generic alt replacement.
		add_filter( 'post_thumbnail_html', array( __CLASS__, 'fix_thumbnail_alt' ), 20, 2 );

		// Fixes 2 & 4 run on the parent plugin's HTML buffer output.
		add_filter( 'livqacea_sanitized_html', array( __CLASS__, 'fix_buffered_html' ) );

		// Admin notice - shown once to confirm the add-on is active.
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
	}

	// -----------------------------------------------------------------------
	// Detection - multi-signal fingerprinting
	// -----------------------------------------------------------------------

	/**
	 * Returns true when at least 2 of 3 Design Comuni signals are detected.
	 * Survives theme folder rename, style.css edits, and partial customisations.
	 *
	 * @return bool
	 */
	public static function is_design_comuni(): bool {
		if ( null !== self::$detected ) {
			return self::$detected;
		}

		$signals = 0;

		// Signal 1: Text Domain in style.css - survives folder rename.
		if ( 'design-comuni-italia' === wp_get_theme()->get( 'TextDomain' ) ) {
			++$signals;
		}

		// Signal 2: Unique PHP function registered by the theme's inc/ files.
		if ( function_exists( 'dci_get_breadcrumb_items' ) ) {
			++$signals;
		}

		// Signal 3: PA-specific custom post type combination.
		// These CPTs are only registered by Design Comuni / Bootstrap Italia PA themes.
		if ( post_type_exists( 'servizio' ) && post_type_exists( 'luogo' ) ) {
			++$signals;
		}

		self::$detected = $signals >= 2;
		return self::$detected;
	}

	// -----------------------------------------------------------------------
	// Fix 1 + Fix 5 - Nav walker filters
	// -----------------------------------------------------------------------

	/**
	 * Injects aria-current="page" on the active menu item's anchor.
	 * The Design Comuni walkers only set class="active" - screen readers
	 * cannot identify the current page from a CSS class alone. WCAG 4.1.2.
	 *
	 * @param string   $item_output Generated HTML for this menu item.
	 * @param WP_Post  $item        The current menu item object.
	 * @param int      $depth       Depth of the menu item.
	 * @param stdClass $args        An object of wp_nav_menu() arguments.
	 * @return string
	 */
	public static function fix_aria_current( string $item_output, $item, int $depth, $args ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if (
			in_array( 'current-menu-item', $item->classes, true ) ||
			in_array( 'current-menu-ancestor', $item->classes, true )
		) {
			if ( false === strpos( $item_output, 'aria-current=' ) ) {
				$value       = in_array( 'current-menu-item', $item->classes, true ) ? 'page' : 'true';
				$item_output = str_replace( '<a ', '<a aria-current="' . esc_attr( $value ) . '" ', $item_output );
			}
		}
		return $item_output;
	}

	/**
	 * Adds aria-haspopup and aria-expanded="false" on top-level items that
	 * control sub-menus / megamenus. Bootstrap Italia's JS sets the correct
	 * state at runtime, but without a static initial value screen readers
	 * announce no state before JS initialises. WCAG 4.1.2.
	 *
	 * @param string   $item_output Generated HTML for this menu item.
	 * @param WP_Post  $item        The current menu item object.
	 * @param int      $depth       Depth of the menu item.
	 * @param stdClass $args        An object of wp_nav_menu() arguments.
	 * @return string
	 */
	public static function fix_megamenu_aria( string $item_output, $item, int $depth, $args ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if (
			0 === $depth &&
			in_array( 'menu-item-has-children', $item->classes, true ) &&
			false === strpos( $item_output, 'aria-expanded=' )
		) {
			$item_output = str_replace(
				'<a ',
				'<a aria-haspopup="true" aria-expanded="false" ',
				$item_output
			);
		}
		return $item_output;
	}

	// -----------------------------------------------------------------------
	// Fix 3 - Post thumbnail alt text
	// -----------------------------------------------------------------------

	/**
	 * Replaces known Design Comuni placeholder alt values with the post title.
	 * `alt="banner"` and `alt="descrizione immagine"` are hardcoded in two
	 * theme template-parts and provide no useful information to screen readers.
	 * WCAG 1.1.1.
	 *
	 * @param string $html    The post thumbnail HTML.
	 * @param int    $post_id The post ID.
	 * @return string
	 */
	public static function fix_thumbnail_alt( string $html, int $post_id ): string {
		static $bad_alts = array( 'banner', 'descrizione immagine', "descrizione dell'immagine" );

		foreach ( $bad_alts as $bad ) {
			if ( false !== stripos( $html, 'alt="' . $bad . '"' ) ) {
				$title = esc_attr( get_the_title( $post_id ) );
				$html  = str_ireplace( 'alt="' . $bad . '"', 'alt="' . $title . '"', $html );
			}
		}

		return $html;
	}

	// -----------------------------------------------------------------------
	// Fixes 2 & 4 - Output buffer (via parent plugin's filter)
	// -----------------------------------------------------------------------

	/**
	 * Applies buffer-based fixes that require scanning the full rendered HTML:
	 *
	 * Fix 2: Adds aria-labelledby to the #search-modal dialog element
	 *        and an id to its first heading, so the modal is properly named.
	 *        WCAG 4.1.2.
	 *
	 * Fix 4: Adds role="application" and aria-label to the Leaflet map
	 *        container <div id="map_all"> used in single-luogo.php.
	 *        WCAG 1.1.1 / 1.3.1.
	 *
	 * @param string $html Full rendered page HTML from the output buffer.
	 * @return string
	 */
	public static function fix_buffered_html( string $html ): string {
		if ( empty( $html ) ) {
			return $html;
		}

		// Fix 2 - Search modal aria-labelledby.
		$html = self::fix_search_modal( $html );

		// Fix 4 - Leaflet map accessible name.
		$html = self::fix_leaflet_map( $html );

		// Fix 3 (buffer pass) - catch generic alt text not caught by post_thumbnail_html.
		$html = self::fix_generic_alt_buffer( $html );

		return $html;
	}

	/**
	 * Adds aria-labelledby to the Design Comuni search modal and injects an
	 * id on the heading inside it so the two can be linked.
	 *
	 * Before:
	 *   <div ... role="dialog" ...>
	 *     <h2 ...>Cerca nel sito</h2>
	 *
	 * After:
	 *   <div ... role="dialog" aria-labelledby="edgpa-search-title" ...>
	 *     <h2 id="edgpa-search-title" ...>Cerca nel sito</h2>
	 *
	 * @param string $html Full page HTML.
	 * @return string
	 */
	private static function fix_search_modal( string $html ): string {
		// Only process if the search modal exists and lacks aria-labelledby.
		if (
			false === strpos( $html, 'id="search-modal"' ) ||
			false !== strpos( $html, 'aria-labelledby="edgpa-search-title"' )
		) {
			return $html;
		}

		// Step 1: Add aria-labelledby to the dialog element.
		$html = preg_replace(
			'/(<[^>]+id=["\']search-modal["\'][^>]*role=["\']dialog["\'][^>]*|' .
			'<[^>]+role=["\']dialog["\'][^>]*id=["\']search-modal["\'][^>]*)>/i',
			'$1 aria-labelledby="edgpa-search-title">',
			$html
		) ?? $html;

		// Step 2: Add id to the first <h2> inside the modal section.
		// We target the first <h2> that appears after #search-modal in the source.
		$modal_pos = strpos( $html, 'id="search-modal"' );
		if ( false === $modal_pos ) {
			return $html;
		}

		$before = substr( $html, 0, $modal_pos );
		$after  = substr( $html, $modal_pos );

		$after = preg_replace(
			'/<h2\b(?![^>]*\bid=)/i',
			'<h2 id="edgpa-search-title"',
			$after,
			1 // Replace only the first occurrence inside the modal section.
		) ?? $after;

		return $before . $after;
	}

	/**
	 * Adds role="application" and aria-label to the Leaflet map container.
	 * Without an accessible name, the map div is announced as a generic
	 * element with no context by screen readers. WCAG 1.1.1 / 1.3.1.
	 *
	 * @param string $html Full page HTML.
	 * @return string
	 */
	private static function fix_leaflet_map( string $html ): string {
		if ( false === strpos( $html, 'id="map_all"' ) ) {
			return $html;
		}

		return preg_replace_callback(
			'/<div\b([^>]*)\bid=["\']map_all["\']([^>]*)>/i',
			static function ( array $m ): string {
				$before = $m[1];
				$after  = $m[2];

				// Skip if already has role or aria-label.
				if (
					preg_match( '/\brole=/i', $before . $after ) ||
					preg_match( '/\baria-label=/i', $before . $after )
				) {
					return $m[0];
				}

				$label = esc_attr( __( 'Interactive map', 'edg-pa-italia' ) );

				return '<div' . $before . ' id="map_all"' . $after .
					' role="application" aria-label="' . $label . '">';
			},
			$html
		) ?? $html;
	}

	/**
	 * Buffer-level catch for generic alt texts that bypass post_thumbnail_html.
	 * Some Design Comuni template-parts output images directly without going
	 * through WordPress thumbnail functions. WCAG 1.1.1.
	 *
	 * Replaces known placeholder values with alt="" (decorative) since no
	 * post context is available at this stage.
	 *
	 * @param string $html Full page HTML.
	 * @return string
	 */
	private static function fix_generic_alt_buffer( string $html ): string {
		$placeholders = array(
			'alt="banner"',
			"alt='banner'",
			'alt="descrizione immagine"',
			"alt='descrizione immagine'",
		);

		foreach ( $placeholders as $placeholder ) {
			if ( false !== stripos( $html, $placeholder ) ) {
				$html = str_ireplace( $placeholder, 'alt=""', $html );
			}
		}

		return $html;
	}

	// -----------------------------------------------------------------------
	// Admin notice
	// -----------------------------------------------------------------------

	/**
	 * Shows a one-time admin notice confirming the add-on detected the theme.
	 */
	public static function admin_notice(): void {
		$option_key = 'edgpa_notice_dismissed';

		if ( get_option( $option_key ) ) {
			return;
		}

		// Only show to admins on the plugin/theme pages.
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->base, array( 'plugins', 'themes', 'dashboard' ), true ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible" id="edgpa-notice">';
		echo '<p><strong>' . esc_html__( 'LivQ AccessFix - PA Italia', 'edg-pa-italia' ) . '</strong> ';
		echo esc_html__( 'Design Comuni Italia theme detected. WCAG 2.2 AA fixes for PA are now active.', 'edg-pa-italia' );
		echo '</p></div>';
		echo '<script>document.getElementById("edgpa-notice").addEventListener("click",function(e){';
		echo 'if(e.target.classList.contains("notice-dismiss")){';
		echo 'fetch(' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . '+';
		echo '"?action=edgpa_dismiss_notice&nonce=' . esc_js( wp_create_nonce( 'edgpa_dismiss' ) ) . '");';
		echo '}});</script>';

		add_action(
			'wp_ajax_edgpa_dismiss_notice',
			static function () {
				check_ajax_referer( 'edgpa_dismiss', 'nonce' );
				update_option( 'edgpa_notice_dismissed', 1 );
				wp_die();
			}
		);
	}
}
