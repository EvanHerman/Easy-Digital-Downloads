<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function edd_tools_page() {

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'tools';
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_before' );
			do_action( 'edd_tools_tab_' . $active_tab );
			do_action( 'edd_tools_after' );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.0
 * @return      array
 */
function edd_get_tools_tabs() {

	$tabs                  = array();
	$tabs['tools']         = __( 'Tools', 'edd' );
	$tabs['system_info']   = __( 'System Info', 'edd' );
	$tabs['import_export'] = __( 'Import/Export', 'edd' );

	return apply_filters( 'edd_tools_tabs', $tabs );
}


/**
 * Display the tools import/export tab
 *
 * @since       2.0
 * @return      void
 */
function edd_tools_tab_import_export() {
	do_action( 'edd_import_export_before' );
?>
	<div class="postbox">
		<h3><span><?php _e( 'Export Settings', 'edd' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd' ); ?></p>
			<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?></p>
			<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
				<p><input type="hidden" name="edd_action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'edd_export_nonce', 'edd_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'edd' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="postbox">
		<h3><span><?php _e( 'Import Settings', 'edd' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the Easy Digital Downloads settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'edd' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="import_settings" />
					<?php wp_nonce_field( 'edd_import_nonce', 'edd_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'edd' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'edd_import_export_after' );
}
add_action( 'edd_tools_tab_import_export', 'edd_tools_tab_import_export' );


/**
 * Display the tools system info tab
 *
 * @since       2.0
 * @global $wpdb
 * @global object $wpdb Used to query the database using the WordPress
 *   Database API
 * @global $edd_options Array of all the EDD Options
 * @author Chris Christoff
 * @return void
 */
function edd_tools_tab_system_info() {
	global $wpdb, $edd_options;

	if ( ! class_exists( 'Browser' ) )
		require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();
	if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}

	// Try to identifty the hosting provider
	$host = false;
	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	}
?>
	<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-system-info' ) ); ?>" method="post" dir="ltr">
		<textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="edd-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'edd' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

<?php do_action( 'edd_system_info_before' ); ?>

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

EDD Version:              <?php echo EDD_VERSION . "\n"; ?>
Upgraded From:            <?php echo get_option( 'edd_version_upgraded_from', 'None' ) . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $theme . "\n"; ?>
<?php if( $host ) : ?>
Host:                     <?php echo $host . "\n"; ?>
<?php endif; ?>

Test Mode Enabled:        <?php echo edd_is_test_mode() ? "Yes\n" : "No\n"; ?>
Ajax Enabled:             <?php echo edd_is_ajax_enabled() ? "Yes\n" : "No\n"; ?>
Guest Checkout Enabled:   <?php echo edd_no_guest_checkout() ? "No\n" : "Yes\n"; ?>
Symlinks Enabled:         <?php echo apply_filters( 'edd_symlink_file_downloads', isset( $edd_options['symlink_file_downloads'] ) ) && function_exists( 'symlink' ) ? "Yes\n" : "No\n"; ?>

Checkout is:              <?php echo ! empty( $edd_options['purchase_page'] ) ? "Valid\n" : "Invalid\n"; ?>
Checkout Page:            <?php echo ! empty( $edd_options['purchase_page'] ) ? get_permalink( $edd_options['purchase_page'] ) . "\n" : "\n" ?>
Success Page:             <?php echo ! empty( $edd_options['success_page'] ) ? get_permalink( $edd_options['success_page'] ) . "\n" : "\n" ?>
Failure Page:             <?php echo ! empty( $edd_options['failure_page'] ) ? get_permalink( $edd_options['failure_page'] ) . "\n" : "\n" ?>
Downloads slug:           <?php echo defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG . "\n" : "/downloads\n"; ?>

Taxes Enabled:            <?php echo edd_use_taxes() ? "Yes\n" : "No\n"; ?>
Taxes After Discounts:    <?php echo edd_taxes_after_discounts() ? "Yes\n" : "No\n"; ?>
Tax Rate:                 <?php echo edd_get_tax_rate() * 100; ?>%
Country / State Rates:    <?php

$rates = edd_get_tax_rates();
if( ! empty( $rates ) ) {
	foreach( $rates as $rate ) {
		echo 'Country: ' . $rate['country'] . ', State: ' . $rate['state'] . ', Rate: ' . $rate['rate'] . ' | ';
	}
}
?>

Registered Post Stati:    <?php echo implode( ', ', get_post_stati() ) . "\n\n"; ?>

<?php echo $browser ; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
WordPress Memory Limit:   <?php echo ( edd_let_to_num( WP_MEMORY_LIMIT )/( 1024 ) )."MB"; ?><?php echo "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_for_posts' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>

<?php
$request['cmd'] = '_notify-validate';

$params = array(
	'sslverify'		=> false,
	'timeout'		=> 60,
	'user-agent'	=> 'EDD/' . EDD_VERSION,
	'body'			=> $request
);

$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
	$WP_REMOTE_POST =  'wp_remote_post() works' . "\n";
} else {
	$WP_REMOTE_POST =  'wp_remote_post() does not work' . "\n";
}
?>
WP Remote Post:           <?php echo $WP_REMOTE_POST; ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.'; ?><?php echo "\n"; ?>
SUHOSIN:                  <?php echo ( extension_loaded( 'suhosin' ) ) ? 'Your server has SUHOSIN installed.' : 'Your server does not have SUHOSIN installed.'; ?><?php echo "\n"; ?>

TEMPLATES:

<?php
// Show templates that have been copied to the theme's edd_templates dir
$dir = get_stylesheet_directory() . '/edd_templates/*';
if (!empty($dir)){
	foreach ( glob( $dir ) as $file ) {
		echo "Filename: " . basename( $file ) . "\n";
	}
}
else {
	echo 'No overrides found';
}
?>

ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

if ( is_multisite() ) :
?>

NETWORK ACTIVE PLUGINS:

<?php
$plugins = wp_get_active_network_plugins();
$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

foreach ( $plugins as $plugin_path ) {
	$plugin_base = plugin_basename( $plugin_path );

	// If the plugin isn't active, don't show it.
	if ( ! array_key_exists( $plugin_base, $active_plugins ) )
		continue;

	$plugin = get_plugin_data( $plugin_path );

	echo $plugin['Name'] . ' :' . $plugin['Version'] ."\n";
}

endif;

do_action( 'edd_system_info_after' );
?>
### End System Info ###</textarea>
		<p class="submit">
			<input type="hidden" name="edd-action" value="download_sysinfo" />
			<?php submit_button( 'Download System Info File', 'primary', 'edd-download-sysinfo', false ); ?>
		</p>
	</form>
<?php
}
add_action( 'edd_tools_tab_system_info', 'edd_tools_tab_system_info' );

/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.7
 * @return      void
 */
function edd_process_settings_export() {

	if( empty( $_POST['edd_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings = get_option( 'edd_settings' );

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=edd-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'edd_export_settings', 'edd_process_settings_export' );

/**
 * Process a settings import from a json file
 *
 * @since 1.7
 * @return void
 */
function edd_process_settings_import() {

	if( empty( $_POST['edd_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

    if( edd_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
        wp_die( __( 'Please upload a valid .json file', 'edd' ) );
    }

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'edd' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'edd_settings', $settings );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&edd-message=settings-imported' ) ); exit;

}
add_action( 'edd_import_settings', 'edd_process_settings_import' );

/**
 * Generates the System Info Download File
 *
 * @since 1.4
 * @return void
 */
function edd_generate_sysinfo_download() {
	nocache_headers();

	header( "Content-type: text/plain" );
	header( 'Content-Disposition: attachment; filename="edd-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['edd-sysinfo'] );
	edd_die();
}
add_action( 'edd_download_sysinfo', 'edd_generate_sysinfo_download' );
