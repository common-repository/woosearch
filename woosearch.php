<?php
/**
 * Plugin Name: WooSearch
 * Plugin URI: https://www.ctkpro.com/woosearch/
 * Description: An search engine that helps you search products based on WooCommerce.
 * Version: 1.0.0
 * Author: CTKPro
 * Author URI: https://www.ctkpro.com
 * Requires at least: 4.4
 * Tested up to: 4.5
 *
 * Text Domain: woosearch
 * Domain Path: /i18n/languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once 'includes/woosearch-db-keywords.php';
require_once 'includes/woosearch-apis.php';
const WOOSEARCH_MAX_PLACEHOLDER_COUNT = 5;
const WOOSEARCH_VERSION = '1.0.0';

function woosearch_alert_active__error() {
	$class   = 'notice notice-error';
	$message = esc_html__( 'WooCommerce is deactivate! Please activate WooCommerce first.', 'woosearch' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

function woosearch_detecting_woocommerce_is_exists() {
	if ( ! class_exists( 'woocommerce' ) ) {
		woosearch_alert_active__error();
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		add_action( 'init', 'woosearch_init' );
	}
}

add_action( 'plugins_loaded', 'woosearch_detecting_woocommerce_is_exists' );

/*
 * WooSearch Table Install
 */

function woosearch_install() {
	global $wpdb;
	$table_name = $wpdb->prefix . WOOSEARCH_KEYWORDS_TABLE_NAME;
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( woosearch_table_schema() );
		woosearch_insert_default_placeholders();
	}
}

function woosearch_insert_default_placeholders() {
	$db_keyword = new WooSearch_DB_Keywords();
	for ( $i = 0; $i < WOOSEARCH_MAX_PLACEHOLDER_COUNT; $i ++ ) {
		$insert_data = array(
			'placeholder' => '',
			'imgUrl'  => '',
			'imgAlt'  => '',
			'sort'    => $i,
		);
		$db_keyword->insert( $insert_data );
	}
}

register_activation_hook( __FILE__, 'woosearch_install' );

add_action( 'init', 'woosearch_load_custom_language_files' );
//add_action('load_textdomain', 'load_custom_language_files_for_woosearch', 10, 2);
function woosearch_load_custom_language_files() {
	error_log('load text domain');
	$locale = apply_filters( 'plugin_locale', get_locale(), 'woosearch' );

	load_textdomain( 'woosearch', WP_LANG_DIR . '/woosearch/woosearch-' . $locale . '.mo' );
	load_plugin_textdomain( 'woosearch', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
}

/*
 * WooSearch Init.
 */
function woosearch_init() {
}

add_action( 'admin_menu', 'woosearch_menu' );

function woosearch_menu() {
	add_menu_page( esc_html__( 'WooSearch', 'woosearch' ), esc_html__( 'WooSearch', 'woosearch' ), 'administrator', 'woosearch-identifier', 'woosearch_options', 'dashicons-search' );
}

function woosearch_options() {
	//if(wp_verify_nonce( $_REQUEST['my_nonce'], 'woosearch-setup-options' ))
	if (current_user_can('activate_plugins') &&
	    $_SERVER['REQUEST_METHOD'] == 'POST' &&
	    isset($_REQUEST['_wpnonce']) &&
	    wp_verify_nonce( $_REQUEST['_wpnonce'], 'woosearch-setup-options' )) {
		$ws_req_data = array();
		if ( !empty( $_POST['ws_data'] ) ) {
			foreach ($_POST['ws_data'] as $index => $data) {
				if ( !empty( $data['imgUrl'] ) || !empty($data['placeholder']) ) {
//					error_log(json_encode($data));
					// do not add $data array directly for $_POST data safety issue
					$ws_req_data[] = array(
						'imgUrl'=>sanitize_text_field((string)$data['imgUrl']),
						'imgAlt'=>sanitize_text_field((string)$data['imgAlt']),
						'placeholder'=>sanitize_text_field((string)$data['placeholder']),
					);
				}
			}
		}
		woosearch_save_options($ws_req_data);
	}
	$ws_data = woosearch_convert_std_class_to_php_array( woosearch_get_placeholder_options() );

	include_once( 'views/woosearch-options.php' );
}

function woosearch_get_placeholder_options(){
	$db_keyword = new WooSearch_DB_Keywords();
	return $db_keyword->get_all('sort');
}

function woosearch_save_option($id, $placeholder, $imgUrl, $imgAlt, $sort){
	$db_keyword = new WooSearch_DB_Keywords();
	$update_data = array(
		'placeholder' => $placeholder,
		'imgUrl'  => $imgUrl,
		'imgAlt'  => $imgAlt,
		'sort' => $sort,
	);
	$db_keyword->update( $update_data, array( 'ID' => $id ) );
}

function woosearch_save_options( $ws_req_data ) {
	include_once( 'includes/woosearch-db-keywords.php' );

	$ws_req_data = woosearch_filled_save_data_of_woosearch_option( $ws_req_data );
	foreach ( $ws_req_data as $index => $data ) {
		woosearch_save_option($index + 1, $data['placeholder'], $data['imgUrl'], $data['imgAlt'], $index + 1);
	}

	/*
	$all_keywords = $db_keyword->get_all();
	error_log( json_encode( $all_keywords ) );

	$deleted_keyword = $db_keyword->delete( array( 'ID' => $new_keyword_id ) );
	error_log( $deleted_keyword );
	*/
}

function woosearch_filled_save_data_of_woosearch_option($ws_req_data) {
	if ( count($ws_req_data) < WOOSEARCH_MAX_PLACEHOLDER_COUNT ) {
		$i = count($ws_req_data);
		while ( $i < WOOSEARCH_MAX_PLACEHOLDER_COUNT ) {
			$ws_req_data[] = array(
				'placeholder' => '',
				'imgUrl' => '',
				'imgAlt' => ''
			);
			$i++;
		}
	}
	return $ws_req_data;
}

add_action( 'widgets_init', 'woosearch_register_widget' );
function woosearch_register_widget() {
	include_once( 'includes/woosearch-widget.php' );
	register_widget( 'WooSearch_Widget' );
}

function woosearch_convert_std_class_to_php_array($query) {
	return json_decode(json_encode($query), true);
}

?>