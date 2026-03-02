<?php
/**
 * Plugin Name: VK VWS Plugin Beta Tester
 * Plugin URI: https://github.com/vektor-inc/vk-vws-plugin-beta-tester
 * Description: Enables beta channel for VWS plugins (VK Blocks Pro, Lightning Pro, etc.). When activated, you will receive beta versions instead of stable releases.
 * Version: 0.1.0
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
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init() {
		// Hook into each supported plugin's update check filter
		foreach ( $this->supported_plugins as $slug => $config ) {
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
		// Only show on plugins page
		$screen = get_current_screen();
		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}

		$plugin_names = array();
		foreach ( $this->supported_plugins as $config ) {
			$plugin_names[] = $config['name'];
		}

		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'VK VWS Plugin Beta Tester is active', 'vk-vws-plugin-beta-tester' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: Comma-separated list of plugin names */
					esc_html__( 'You are now receiving beta versions of: %s', 'vk-vws-plugin-beta-tester' ),
					'<strong>' . esc_html( implode( ', ', $plugin_names ) ) . '</strong>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'Beta versions may contain bugs or unfinished features. Do not use in production environments.', 'vk-vws-plugin-beta-tester' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'To return to stable versions, deactivate this plugin. Your plugins will switch back to stable releases when the next stable version is available.', 'vk-vws-plugin-beta-tester' ); ?>
			</p>
		</div>
		<?php
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
