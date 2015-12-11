<?php
/*
 * Plugin Name: PBS Passport Authenticate
 * Version: 0.1
 * Plugin URI: http://ieg.wnet.org/
 * Description: PBS Passport Authenticate
 * Author: William Tam
 * Author URI: http://ieg.wnet.org/
 * Requires at least: 4.0 
 * Tested up to: 4.2.2
 * 
 * @package WordPress
 * @author William Tam 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Include plugin class files
require_once( 'classes/class-pbs-passport-authenticate.php' );
require_once( 'classes/class-pbs-passport-authenticate-settings.php' );
require_once('classes/class-PBS-LAAS-client.php');
if (!class_exists('PBS_MVault_Client')) {
  require_once('classes/class-PBS-MVault-client.php');
}
global $plugin_obj;
$plugin_obj = new PBS_Passport_Authenticate( __FILE__ );

if ( is_admin() ) {
  $plugin_settings_obj = new PBS_Passport_Authenticate_Settings( __FILE__ );
}

register_activation_hook(__FILE__, 'pbs_passport_authenticate_activation');

function pbs_passport_authenticate_activation() {
  // init the object, which will setup the object
  $plugin_obj = new PBS_Passport_Authenticate( __FILE__ );
  $plugin_obj->setup_rewrite_rules();
  flush_rewrite_rules();    
}

// always cleanup after yourself
register_deactivation_hook(__FILE__, 'pbs_passport_authenticate_deactivation');

function pbs_passport_authenticate_deactivation() {
  flush_rewrite_rules();
}


//TEMPORARY will find a better way to store this.  
//it is a server-by-server thing
if (!function_exists('mvault_curl_extras')) {
  function mvault_curl_extras($ch) {
  curl_setopt($ch, CURLOPT_CAINFO, '/etc/pki/tls/certs/AddTrustExternalCARoot.crt');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  return $ch;
}
}


function pbs_passport_authenticate_render_video($vidid) {



}


