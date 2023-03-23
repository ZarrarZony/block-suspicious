<?php
/*
Plugin Name: Block-Suspicious
Description: Blocks ip address, if orders more than 10 times
Author:      Zarrar aka Zony
Version:     1.0.0
Author URI:  https://linkedin.com/in/muhammadzarrar
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die( 'Action will be taken' );
define( 'PLUGIN_PATH_FOR_Block_Suspicious', plugin_dir_path( __FILE__ ) );

include( PLUGIN_PATH_FOR_Block_Suspicious . 'includes/DB_Handler.php');

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    '',
    __FILE__,
    'block-suspicious'
);
$myUpdateChecker->setAuthentication('');
$myUpdateChecker->setBranch('master');

class BlockSuspicious{
    private $cur_user_ip;
    public $table;

    private static $instance = null;

    private function __construct() {
    }
 
    public static function getInstance() {
       if (self::$instance == null) {
          self::$instance = new Sample();
       }
       return self::$instance;
    }

    public function init(){
        global $wpdb;
        $this->cur_user_ip = $this->getUserIpAddr();
        $this->table = $wpdb->prefix . "suspicious_ip_addresses";
        add_action( 'woocommerce_new_order', array($this,'capture_user_ip'), 10, 1);
        add_action( 'plugins_loaded', array($this,'after_all_plugins_load') );
        register_activation_hook( __FILE__, array( $this, 'plugin_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'plugin_uninstall' ) );
    }

    public function after_all_plugins_load(){
        if($this->check_to_blacklist_ip()){
            add_filter('woocommerce_order_button_html', array($this,'remove_order_button_html'));
            add_action('woocommerce_after_checkout_validation', array($this,'prevent_order_placed'));
        }
    }

    public function remove_order_button_html( $button ) {
        $button = '';
        return $button;
    }

    public function prevent_order_placed($data){
        wc_add_notice( 'Sorry, no more than 10 orders', 'error');
    }


    public function capture_user_ip( $order_id ) {
        global $wpdb;
        $order = new WC_Order( $order_id );
        $user_ip = $order->get_customer_ip_address();
        $ip_match = $wpdb->get_results( "SELECT * FROM $this->table WHERE ip='$user_ip'",ARRAY_A);
        if(!$ip_match){
            $wpdb->insert($this->table,array('ip'=>$user_ip,'count'=>1,'created_at'=>time()));
        }
        else{
            $count = $ip_match[0]['count']+1;
            $wpdb->update($this->table,array('count'=> $count),array('id'=>$ip_match[0]['id']));
        }

    }

    public function clean_ip_row($ip_id){
        global $wpdb;
        $wpdb->delete($this->table,array('id'=>$ip_id));
    }

    public function getUserIpAddr(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function plugin_install() {
        $db_handler = new Db_Handler_For_Block_suspicious();
        $db_handler->DB_Installation();
    }

    public function plugin_uninstall() {
    }

}

$instance = BlockSuspicious::getInstance();
$instance->init();