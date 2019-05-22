<?php
/*
Plugin Name: Site Health Manager
Plugin URI:  https://wordpress.org/plugins/site-health-manager/
Description: Site Health Manager allows you to customize your info data visibility.
Version:     1.0.0
Author:      Rami Yushuvaev
Author URI:  https://GenerateWP.com/
Text Domain: site-health-manager
*/

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Site Health Manager class.
 *
 * @since 1.0.0
 */
class Site_Health_Manager {

	/**
	 * Holds the plugin basename.
	 *
	 * @access private
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private static $basename;

	/**
	 * The screen id used in WordPress dashboard.
	 *
	 * @access private
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $screen_id = 'tools_page_site-health-manager';

	/**
	 * The slug of the parent page in WordPress dashboard.
	 *
	 * @access private
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $parent_slug = 'tools.php';

	/**
	 * The slug of the plugin in WordPress dashboard.
	 *
	 * @access private
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $page_slug = 'site-health-manager';

	/**
	 * The capability required for viewing the settings page.
	 *
	 * @access private
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private $view_cap = 'manage_options';

	/**
	 * WordPress site health object.
	 *
	 * @access private
	 * @var object
	 *
	 * @since 1.0.0
	 */
	private $wp_site_health;

	/**
	 * WordPress site health info object.
	 *
	 * @access private
	 * @var object
	 *
	 * @since 1.0.0
	 */
	private $wp_site_health_info;

	/**
	 * Site Health Manager disabled info array.
	 *
	 * @access private
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private $site_health_manager_disabled_info = [];

	/**
	 * Site Health Manager class constructor.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load plugin data
		$this->load_data( get_option( 'site_health_manager_disabled_info' ) );

		// Disable selected site health information.
		add_filter( 'debug_information', [ $this, 'disable_info' ] );

		// The plugin runs only in the admin, but we need to initialized on init.
		add_action( 'init', [ $this, 'action_init' ] );

	}

	/**
	 * Load plugin data.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Plugin data to be used.
	 */
	public function load_data( $data ) {

		$this->site_health_manager_disabled_info = is_array( $data ) ? $data : [];

	}

	/**
	 * Disable selected site health information.
	 *
	 * This filter excludes Site Health Manager screen.
	 *
	 * Called by `debug_information` hook.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param array $info The debug information in the Site Health Info page.
	 *
	 * @return array The debug information.
	 */
	public function disable_info( $info ) {

		$current_screen = get_current_screen();

		if( $current_screen->id !== $this->screen_id ) {
			foreach ( $this->site_health_manager_disabled_info as $section_name => $fields ) {
				foreach ( $fields as $field_name ) {
					unset( $info[$section_name]['fields'][$field_name] );
				}
			}
		}

		return $info;

	}

	/**
	 * Site Health Manager initialization actions.
	 *
	 * Registers the hooks that run the plugin.
	 *
	 * Called by `init` action hook.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function action_init() {

		if ( ! is_admin() )
			return;

		isset( self::$basename ) || self::$basename = plugin_basename( __FILE__ );

		// Add action links.
		add_filter( 'plugin_action_links_' . self::$basename, [ $this, 'plugin_action_links' ], 10, 2 );

		// Add admin menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		// Load the plugin textdomain to allow multilingual translations.
		load_plugin_textdomain( 'site-health-manager' );

	}

	/**
	 * Site Health Manager plugin action links.
	 *
	 * Adds a link to the settings page in the plugins list.
	 *
	 * Called by `plugin_action_links_*` filter hook.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function plugin_action_links( $links, $file ) {

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			menu_page_url( $this->page_slug, false ),
			__( 'Settings' ) // No text-domain, use WordPress core string
		);
		return $links;

	}

	/**
	 * Site Health Manager plugin menu.
	 *
	 * Registers admin menu for the plugin.
	 *
	 * Called by `admin_menu` action hook.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {

		$admin_screen = add_submenu_page(
			$this->parent_slug,
			__( 'Site Health Manager', 'site-health-manager' ),
			__( 'Site Health Manager', 'site-health-manager' ),
			$this->view_cap,
			$this->page_slug, 
			[ $this, 'settings_page' ]
		);

		// TODO: move to a method.
		wp_enqueue_style( 'site-health' );

		// Add footer scripts
		add_action( "admin_footer-{$admin_screen}", [ $this,'admin_footer' ] );

	}

	/**
	 * Site Health Manager plugin footer scripts and styles in WordPress dashboard.
	 *
	 * Called by `admin_footer-*` action hook.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function admin_footer() {

		?>
		<style> 
			.tools_page_site-health-manager #wpcontent { padding-left: 0; }
			.tools_page_site-health-manager .health-check-body { padding-left: 20px; }
			.tools_page_site-health-manager table td:nth-of-type(1) { width: 20px; }
			.tools_page_site-health-manager table td:nth-of-type(2) { width: 30%; }
			.tools_page_site-health-manager table td:nth-of-type(3) { width: calc(100% - 30% - 20px); }
		</style>
		<?php

	}

	/**
	 * Site Health Manager plugin settings page.
	 *
	 * Called by `add_submenu_page()` function.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {

		// Check required user capability
		if ( !current_user_can( $this->view_cap ) )  {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'site-health-manager' ) );
		}

		// Save Settings
		if ( isset( $_REQUEST['submit'] )
			&& isset( $_REQUEST['data'] )
			&& isset( $_REQUEST[$this->page_slug.'_nonce'] )
			&& wp_verify_nonce( $_REQUEST[$this->page_slug.'_nonce'], $this->page_slug )
		) {
			$new_data = [];
			foreach ( $_REQUEST['data'] as $section_name => $fields ) {
				$new_data[$section_name] = [];
				foreach ( $fields as $field_name => $field ) {
					array_push( $new_data[$section_name], $field_name );
				}
			}

			// Save new data
			update_option( 'site_health_manager_disabled_info', $new_data );

			// Reload plugin data
			$this->load_data( $new_data );
		}
		?>
		<div class="health-check-header">
			<div class="health-check-title-section">
				<h1><?php _e( 'Site Health' ); // No text-domain, use WordPress core string ?></h1>

				<div class="site-health-progress hide-if-no-js loading">
					<svg role="img" aria-hidden="true" focusable="false" width="100%" height="100%" viewBox="0 0 200 200" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
						<circle id="bar" r="90" cx="100" cy="100" fill="transparent" stroke-dasharray="565.48" stroke-dashoffset="0"></circle>
					</svg>
					<span class="screen-reader-text"><?php _e( 'Current health score:' ); // No text-domain, use WordPress core string ?></span>
					<span class="site-health-progress-count"></span>
				</div>
			</div>

			<nav class="health-check-tabs-wrapper hide-if-no-js" aria-label="<?php esc_attr_e( 'Secondary menu' ); ?>">
				<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="health-check-tab">
					<?php _ex( 'Status', 'Site Health' ); // No text-domain, use WordPress core string ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'site-health.php?tab=debug' ) ); ?>" class="health-check-tab">
					<?php _ex( 'Info', 'Site Health' ); // No text-domain, use WordPress core string ?>
				</a>

				<a href="<?php echo esc_url( admin_url( 'site-health-manager' ) ); ?>" class="health-check-tab active" aria-current="true">
					<?php _e( 'Manager', 'site-health-manager' ); ?>
				</a>
			</nav>
		</div>

		<hr class="wp-header-end">

		<div class="health-check-body">

			<h2><?php echo esc_html__( 'Site Health Manager', 'site-health-manager' ); ?></h2>
			<p><?php echo esc_html__( 'Some configuration information in your Site Health Info screen is confidential. Sometimes you don\'t want others to get access to this data. Here you can disable critical data before sharing it.', 'site-health-manager' ); ?></p>
			<p><?php echo esc_html__( 'Select what information you want to disable in order to prevent your users from coping it to clipboard when sharing site data with third parties. For example when sending it to plugin/theme developers when debugging issues.', 'site-health-manager' ); ?></p>

			<form action="<?php echo $this->parent_slug; ?>?page=<?php echo $this->page_slug; ?>" method="post">
				<?php
				// Load WP_Debug_Data class
				if ( ! class_exists( 'WP_Debug_Data' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-debug-data.php' );
				}

				// Load WP_Site_Health class
				if ( ! class_exists( 'WP_Site_Health' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
				}

				$this->wp_site_health = new WP_Site_Health();
				$this->wp_site_health_info = WP_Debug_Data::debug_data();

				// Info List
				foreach ( $this->wp_site_health_info as $section => $details ) {

					if ( ! isset( $details['fields'] ) || empty( $details['fields'] ) ) {
						continue;
					}

					if ( isset( $details['label'] ) && ! empty( $details['label'] ) ) {
						printf( '<h3>%s</h3>', $details['label'] );
					}

					if ( isset( $details['description'] ) && ! empty( $details['description'] ) ) {
						printf( '<p>%s</p>', $details['description'] );
					}
					?>
					<table class="widefat striped" role="presentation" id="site-health-manager-section-<?php echo esc_attr( $section ); ?>">
						<tbody>
						<?php
						foreach ( $details['fields'] as $field_name => $field ) {
							// check if disabled
							$is_disabled = $this->is_info_disabled( $section, $field_name );

							// Info value (single value or an array of values)
							if ( is_array( $field['value'] ) ) {
								$values = '<ul>';
								foreach ( $field['value'] as $name => $value ) {
									$values .= sprintf( '<li>%s: %s</li>', $name, $value );
								}
								$values .= '</ul>';
							} else {
								$values = $field['value'];
							}

							// Info list
							printf(
								'<tr><td><input type="checkbox" name="%1$s" %2$s></td><td>%3$s</td><td>%4$s</td></tr>',
								esc_attr( 'data[' .$section . '][' . $field_name . ']' ),
								checked( $is_disabled, 1, 0 ),
								esc_html( $field['label'] ),
								esc_html( $values )
							);
						}
						?>
						</tbody>
					</table>
					<br>
					<?php
				}

				// Set security nonce
				wp_nonce_field( $this->page_slug, $this->page_slug . '_nonce' );

				submit_button();
				?>
			</form>

		</div>
	<?php
	}

	/**
	 * Check whether the info is disabled or not.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @param string $section Info section name.
	 * @param string $field   Info field name.
	 *
	 * @return boolean True if info is disabled, False otherwise.
	 */
	private function is_info_disabled( $section, $field ) {

		$disabled = false;

		if ( array_key_exists( $section, $this->site_health_manager_disabled_info ) ) {
			if ( in_array( $field, $this->site_health_manager_disabled_info[$section] ) ) {
				$disabled = true;
			}
		}

		return $disabled;

	}

}

/**
 * Site Health Manager plugin loader.
 *
 * @since 1.0.0
 */
function site_health_manager_loader() {
	return new Site_Health_Manager;
}
add_action( 'plugins_loaded', 'site_health_manager_loader' );
