<?php
/*
Plugin Name: Visit Tracker
Plugin URI: https://yourwebsite.com/visit-tracker
Description: Tracks visits to your website and logs them in a database
Version: 1.0
Author: SAS MAPROSPECTION
Author URI: https://maprospection.fr/
*/


// Define database table and prefix constants
define('STATS_VISITES_TABLE', 'stats_visites');
define('DB_PREFIX', $wpdb->prefix);

// Create the stats_visites table
function create_stats_visites_table() {
global $wpdb;
$table_name = $wpdb->prefix . 'stats_visites';
$charset_collate = $wpdb->get_charset_collate();
$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    ip varchar(255) NOT NULL,
    date_visite date NOT NULL,
    url varchar(255) NOT NULL,
    actualisation int(11) NOT NULL,
    derniere_visite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY id (id)
) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

function tracabilite_et_compter_visite(){
    // This variable should be defined and initialized outside of the function
    global $wpdb;
    try {
        // Get the user's IP address
        $ip = get_user_ip();
        // Get the current date, URL, and user ID (if logged in)
        $date = date('Y-m-d');
        $now = date("Y-m-d G:i:s");
        $url = $_SERVER['REQUEST_URI'];
        
        // Check if the user's IP address, date, and URL have already been recorded
        $visit_check_stmt = $wpdb->prepare('SELECT id, actualisation FROM '.$wpdb->prefix .'stats_visites WHERE ip = ? AND date_visite = ? AND url = ?');
        $visit_check_stmt->execute([$ip, $date, $url]);
        $previous_visit = $visit_check_stmt->fetch();
        if($previous_visit){
            // Update the existing record
            $update_stmt = $wpdb->prepare('UPDATE '.$wpdb->prefix .'stats_visites SET actualisation = actualisation + 1, derniere_visite = ? WHERE id = ?');
            $update_stmt->execute([$now, $previous_visit['id']]);
        } else {
            // Add a new record
            $insert_stmt = $wpdb->prepare('INSERT INTO '.$wpdb->prefix .'stats_visites (ip, date_visite, url, actualisation) VALUES (?, ?, ?, ?)');
            $insert_stmt->execute([$ip, $date, $url, 1]);
        }
    } catch (PDOException $e) {
        // Handle any errors that occur during the database operations
        error_log("Error: " . $e->getMessage());
    }
}

function get_user_ip() {
    // Get the user's IP address
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        return $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
            return $_SERVER['REMOTE_ADDR'];
            }
            }
        }
