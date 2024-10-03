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
            'course',
            array(
                'labels' => array(
                    'name' => __( 'Course' ),
                    'singular_name' => __( 'Course' )
                ),
                'public' => true,
                'has_archive' => true,
            )
        );
    }
}

add_action( 'init', array('SCN_Course', 'init') );

?>