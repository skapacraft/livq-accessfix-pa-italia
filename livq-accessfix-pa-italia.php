<?php
/**
 * Plugin Name:       LivQ AccessFix - PA Italia Add-on
 * Plugin URI:        https://github.com/livqtech/livq-accessfix-pa-italia
 * Description:       Add-on for LivQ AccessFix: automatic WCAG 2.2 AA fixes for the Design Comuni Italia WordPress theme used by Italian public administrations. Fixes aria-current on menus, search modal labelling, generic alt text, Leaflet map accessibility, and megamenu keyboard state. Zero configuration.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  livq-accessfix
 * Author:            LivQ
 * Author URI:        https://livq.it
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       edg-pa-italia
 * Domain Path:       /languages
 *
 * @package EDG_PA_Italia
 */

defined( 'ABSPATH' ) || exit;

define( 'EDGPA_VERSION', '1.0.0' );
define( 'EDGPA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDGPA_PLUGIN_FILE', __FILE__ );

/**
 * Bail early if the parent plugin (LivQ AccessFix) is not active.
 * The parent provides the output buffer and the livqacea_sanitized_html filter.
 */
add_action(
	'plugins_loaded',
	static function () {
		if ( ! defined( 'LIVQACEA_VERSION' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>';
					printf(
					/* translators: %s: plugin name with link */
						esc_html__( 'LivQ AccessFix - PA Italia Add-on requires %s to be installed and active.', 'edg-pa-italia' ),
						'<strong>LivQ AccessFix – EAA &amp; A11y AutoFix</strong>'
					);
					echo '</p></div>';
				}
			);
			return;
		}

		require_once EDGPA_PLUGIN_DIR . 'includes/class-edgpa-fixes.php';
		EDGPA_Fixes::init();

		add_action(
			'init',
			static function () {
				load_plugin_textdomain( 'edg-pa-italia', false, dirname( plugin_basename( EDGPA_PLUGIN_FILE ) ) . '/languages/' );
			},
			0
		);
	},
	20
); // Priority 20 - after the parent plugin (priority default 10).
