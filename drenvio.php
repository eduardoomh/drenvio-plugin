<?php
/**
 * Plugin Name: DrEnvio for WooCommerce
 * Plugin URI: https://drenvio.com
 * Author: DrEnvio
 * Author URI: https://profiles.wordpress.org/jesusemh/
 * Description: El plugin de DrEnvio para su tienda en WooCommerce, muestre costos de envío dinámicos desde su dirección hasta la de su cliente con hasta 15 paqueterias.
 * Version: 1.0
 * Text Domain: drenvio.php
 * Domain Path: /languages
 */


add_filter( 'wc_get_template', function ( $file, $name ) {
    if ( $name === 'cart/cart-shipping.php' ) {
        $file = __DIR__ . '/public/templates/cart-shipping.php';
    }
    return $file;
}, 10, 2 );

add_action('admin_menu','DrEnvioFWoo_add_menu');

function DrEnvioFWoo_add_menu(){
    $DrEnvioFWoo_url = '/admin.php?page=wc-settings&tab=shipping&section=drenviofwoo';
    $DrEnvioFWoo_total_url = admin_url().$DrEnvioFWoo_url;

    add_menu_page(
        'Dr Envio for Woocommerce',
        'Dr Envio for Woocommerce',
        'manage_options',
        $DrEnvioFWoo_total_url,
        null,
        plugin_dir_url(__FILE__).'public/img/icono.png'
    );
}


add_filter( 'plugin_row_meta', 'DrEnvioFWoo_pluginRowMeta', 10, 2 );
function DrEnvioFWoo_pluginRowMeta( $links, $file ) {
    global $DrEnvioFWoo_language;
    if ( plugin_basename( __FILE__ ) == $file ) {
        $row_meta = array(
            'documentation'    => '<a href="'.esc_url( 'https://jesusmh.s3.us-east-2.amazonaws.com/drenvio/configure-su-plugin-de-wordpress.pdf' ) .' "target="_blank" >' . esc_html__( __($DrEnvioFWoo_language->documentation, DRENVIOFWOO_PLUGIN), 'domain' ) . '</a>',
            'premium_support'    => '<a href="'.esc_url( 'https://drenvio.com' ) .' "target="_blank" >' . esc_html__( __($DrEnvioFWoo_language->premium_support, DRENVIOFWOO_PLUGIN), 'domain' ) . '</a>'
        );
        return array_merge( $links, $row_meta );
    }
    return (array) $links;
}

if( !function_exists('is_plugin_active') ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    if (!defined('ABSPATH')) {
        exit;
    }

		
	if (!defined('DRENVIOFWOO_FILE')) {
		define('DRENVIOFWOO_FILE', __FILE__);
	}

	if (!defined('DRENVIOFWOO_PLUGIN')) {
		define('DRENVIOFWOO_PLUGIN', 'drenvio-woocommerce.php');
	}


	global $DrEnvioFWoo_language;
	if(!isset($DrEnvioFWoo_language)) {
		$DrEnvioFWoo_language = DrEnvioFWoo_get_languages();
	}

	require_once __DIR__."/includes/drenvio.php";
	require_once __DIR__."/admin/dr. envio-admin.php";

	add_action('wp_enqueue_scripts', 'DrEnvioFWoo_add_js_scripts');

	function DrEnvioFWoo_add_js_scripts(){
		wp_register_script('DrEnvioFWoo_jscript',plugins_url('public/js/ajax.js', DRENVIOFWOO_FILE), array('jquery'), '1', true);
		wp_enqueue_script('DrEnvioFWoo_jscript');
		wp_localize_script('DrEnvioFWoo_jscript','DrEnvioFWooAjax',['ajaxurl'=>admin_url('admin-ajax.php')]);
		wp_register_style('DrEnvioFWoo_css',plugins_url('public/css/styles.css', DRENVIOFWOO_FILE));
		wp_enqueue_style('DrEnvioFWoo_css');
	}


	add_filter('woocommerce_shipping_methods',  ['DrEnvioFWooClass', 'DrEnvioFWoo_shipping_methods']);

	add_filter('woocommerce_package_rates','DrEnvioFWoo_runtimeAddShippingMethords',100,2);
	function DrEnvioFWoo_runtimeAddShippingMethords($rates,$package) {

		return $rates;
	}

	add_filter('plugin_action_links_' . plugin_basename(DRENVIOFWOO_FILE), ['DrEnvioFWooClass', 'DrEnvioFWoo_plugin_settings_link']);

 

} else {
    deactivate_plugins('drenvio/drenvio.php');
}


function DrEnvioFWoo_get_languages($returnArray = false){
    $dir = __DIR__.'/languages/';
    if (is_dir($dir)){
        $request = file_get_contents( __DIR__.'/languages/en_US.json');
        if ($languagesDirectory = opendir($dir)){
            while (($file = readdir($languagesDirectory)) !== false) {
                if ($file === get_locale().'.json') {
                    $request = file_get_contents( __DIR__.'/languages/'.get_locale().'.json');
                }

            }
            closedir($languagesDirectory);
        } else {
            error_log("LOCALISATION_ERROR: can't open the dir \"{$dir}\"");
        }
    } else {
        error_log("LOCALISATION_ERROR: can't find the dir \"{$dir}\"");
        return false;
    }

    if(!$request){
        error_log("LOCALISATION_ERROR: error in lang file");
        return false;
    }

    return json_decode($request, $returnArray);
}














