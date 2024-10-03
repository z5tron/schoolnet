/*
 * Plugin Name:       SchoolNet Plugin
 * Plugin URI:        
 * Description:       Student, Registration and Payment management plugin.
 * Version:           0.1
 * Requires PHP:      7.2
 * Author:            Alex
 * Author URI:        https://github.com/z5tron
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        
 * Text Domain:       schoolnet-plugin
 * Domain Path:       
 * Requires Plugins:  
 */

<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCN_Course {
    function __construct( $post_id = NULL ) {
        if ( !empty($post_id)) $this->getPost( $post_id );
    }

    function getPost( $post_id ) {
        $this->post = get_post( $post_id );

        // set properties for easy access
        if ( !empty( $this->post ) ) {
            $this->id = $this->post->ID;
            // ...
        }
        if ( !empty( $this->id ) ) return $this->id;
        return false;
    }

    public static function init() {
        register_post_type(
            'scn_course',
            array(
                'labels' => array(
                    'name' => __( 'Courses' ),
                    'singular_name' => __( 'Course' ),
                    'add_new' => __( 'Add New' ),
                    'add_new_item' => __( 'Add New Course' ),
                    'edit_item' => __( 'Edit Course' ),
                    'new_item' => __( 'New Course' ),
                    'view_item' => __( 'View Course' ),
                    'search_item' => __( 'Search Course' ),
                ),
                'public' => true,
                'has_archive' => false,
                'show_in_menu' => false,
                'menu_icon' => 'dashicons-book-alt',
                'supports' => array( 'title', 'comments', 'teacher', 'thumbnail', 'custom-fields' ),
            )
        );
    }
}

add_action( 'init', array('SCN_Course', 'init') );


class SCN_Transaction {
    function __construct( $post_id = NULL ) {
        if ( !empty($post_id)) $this->getPost( $post_id );
    }

    function getPost( $post_id ) {
        $this->post = get_post( $post_id );

        // set properties for easy access
        if ( !empty( $this->post ) ) {
            $this->id = $this->post->ID;
            // ...
        }
        if ( !empty( $this->id ) ) return $this->id;
        return false;
    }

    public static function init() {
        register_post_type(
            'scn_transaction',
            array(
                'labels' => array(
                    'name' => __( 'Transaction' ),
                    'singular_name' => __( 'Transaction' ),
                    'add_new' => __( 'Add New' ),
                    'add_new_item' => __( 'Add New Transaction' ),
                    'edit_item' => __( 'Edit Transaction' ),
                    'new_item' => __( 'New Transaction' ),
                    'view_item' => __( 'View Transaction' ),
                    'search_item' => __( 'Search Transaction' ),
                ),
                'public' => true,
                'has_archive' => false,
                'show_in_menu' => false,
                'menu_icon' => 'dashicons-book-alt',
                'supports' => array( 'title', 'comments', 'teacher', 'thumbnail', 'custom-fields' ),
            )
        );
    }
}

add_action( 'init', array('SCN_Transaction', 'init') );


add_action( 'admin_menu', 'scn_main_menu' );
function scn_main_page() {
    echo '<h1 class="wp-heading-inline">interesting main page!!</h1>';
}
function scn_main_menu() {
    add_menu_page( 'SchoolNet Plugin Page',
        'SchoolNet', 'manage_options', 'schoolnet-plugin', 'scn_main_page', 'dashicons-welcome-learn-more', 10);
    add_submenu_page('schoolnet-plugin', 'Courses', 'Courses', 'manage_options', 'edit.php?post_type=scn_course');

    add_submenu_page('schoolnet-plugin', 'Transactions', 'Transactions', 'manage_options', 'edit.php?post_type=scn_transaction');
}
?>