<?php
/**
 * Plugin Name: VK VWS Plugin Beta Tester
 * Plugin URI: https://github.com/vektor-inc/vk-vws-plugin-beta-tester
 * Description: Enables beta channel for VWS plugins (VK Blocks Pro, Lightning Pro, etc.). When activated, you will receive beta versions instead of stable releases.
 * Version: 0.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Vektor,Inc.
 * Author URI: https://vektor-inc.co.jp
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vk-vws-plugin-beta-tester
 *
 * @package vk-vws-plugin-beta-tester
 */

// Do not load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VK VWS Plugin Beta Tester Class
 */
class VK_VWS_Plugin_Beta_Tester {

	/**
	 * Supported plugins configuration
	 *
	 * @var array
	 */
	private $supported_plugins = array(
		'vk-blocks-pro' => array(
			'name'        => 'VK Blocks Pro',
			'filter_hook' => 'vk_blocks_pro_vws_update_check_query_args',
			'main_file'   => 'vk-blocks.php',
		),
		// Future support (uncomment when ready):
		// 'lightning-pro' => array(
		//     'name'        => 'Lightning Pro',
		//     'filter_hook' => 'lightning_pro_vws_update_check_query_args',
		// ),
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Load plugin textdomain
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'vk-vws-plugin-beta-tester', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init() {
		// Hook into each supported plugin's update check filter
		foreach ( $this->supported_plugins as $config ) {
			if ( ! empty( $config['filter_hook'] ) ) {
				add_filter( $config['filter_hook'], array( $this, 'add_beta_channel' ), 10, 1 );
			}
		}

		// Add admin notice
		if ( is_admin() ) {
			add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
		}
	}

	/**
	 * Check if a plugin version string looks like a beta/pre-release version.
	 * Detects patterns like: 1.118.0.0-beta1, beta_1.118.0.0, 1.118.0.0-rc1, 1.118.0.0-pre1
	 *
	 * @param string $version Version string to check.
	 * @return bool True if the version looks like a beta/pre-release version.
	 */
	public function is_beta_version( $version ) {
		return (bool) preg_match( '/beta|rc|pre|alpha/i', $version );
	}

	/**
	 * Add beta channel parameter to update check query
	 *
	 * @param array $query_args Query arguments for update check.
	 * @return array Modified query arguments with beta channel.
	 */
	public function add_beta_channel( $query_args ) {
		$query_args['channel'] = 'beta';
		return $query_args;
	}

	/**
	 * Show admin notice when plugin is active
	 *
	 * @return void
	 */
	public function show_admin_notice() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Collect plugin names and detect any currently-installed beta versions.
		$plugin_names        = array();
		$installed_beta_info = array();

		foreach ( $this->supported_plugins as $slug => $config ) {
			$plugin_names[] = $config['name'];

			// Try to read the installed version from the plugin file header.
			$main_file   = ! empty( $config['main_file'] ) ? $config['main_file'] : $slug . '.php';
			$plugin_file = WP_PLUGIN_DIR . '/' . $slug . '/' . $main_file;
			if ( file_exists( $plugin_file ) ) {
				$data    = get_file_data( $plugin_file, array( 'Version' => 'Version' ) );
				$version = ! empty( $data['Version'] ) ? $data['Version'] : '';
				if ( $version && $this->is_beta_version( $version ) ) {
					$installed_beta_info[] = $config['name'] . ' ' . $version;
				}
			}
		}

		if ( ! empty( $installed_beta_info ) ) {
			// If a beta version is currently installed, show an error-level notice on all admin screens.
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( '[Beta Tester] Beta version is currently installed', 'vk-vws-plugin-beta-tester' ); ?></strong>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: Plugin name and version (e.g. "VK Blocks Pro 1.118.0.0-beta1") */
						esc_html__( 'You are running a beta version: %s', 'vk-vws-plugin-beta-tester' ),
						'<strong>' . esc_html( implode( ', ', $installed_beta_info ) ) . '</strong>'
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Do not use beta versions in production environments.', 'vk-vws-plugin-beta-tester' ); ?>
				</p>
				<p>
					<?php
					esc_html_e(
						'To return to the stable version: deactivate this plugin, then wait for the next stable release — or manually re-install the stable version via the plugins screen.',
						'vk-vws-plugin-beta-tester'
					);
					?>
				</p>
				<p>
					<a href="https://vws.vektor-inc.co.jp/forums" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Report a bug on the VWS support forum', 'vk-vws-plugin-beta-tester' ); ?>
					</a>
				</p>
			</div>
			<?php
		} else {
			// No beta installed — show info notice so the user knows the plugin is active.
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: comma-separated list of plugin names (e.g. "VK Blocks Pro, Lightning Pro") */
						esc_html__( '[Beta Tester] Beta channel is active for %s. You will automatically receive the next beta release.', 'vk-vws-plugin-beta-tester' ),
						'<strong>' . esc_html( implode( ', ', $plugin_names ) ) . '</strong>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get the list of supported plugins
	 *
	 * @return array Supported plugins configuration.
	 */
	public function get_supported_plugins() {
		return $this->supported_plugins;
	}
}

// Initialize the plugin
new VK_VWS_Plugin_Beta_Tester();
