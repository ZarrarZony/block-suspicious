<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
global $wpdb;
$table1 = $wpdb->prefix . "suspicious_ip_addresses";
$wpdb->query("DROP TABLE IF EXISTS $table1");
?>