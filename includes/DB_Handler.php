<?php

class Db_Handler_For_Block_suspicious{
  public $wpdb;
  public $table;

  function __construct(){
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->table = $wpdb->prefix . "suspicious_ip_addresses";

  }
  function DB_Installation() {
  }

}
?>
