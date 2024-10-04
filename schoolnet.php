<?php
/**
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
                'supports' => array( 'title', 'comments', 'thumbnail' ),
            )
        );

        /* register school year */
        /*    
	    register_taxonomy(
		    'scn_course_school_year',
		    'scn_course',
		    array(
			    'labels' => array(
				    'name' => 'School Year',
				    'add_new_item' => 'Add New School Year',
				    'new_item_name' => 'New School Year (YYYY-YYYY)',
			),
			'show_ui' => true,
			'meta_box_cb' => false,
			'show_tagcloud' => false,
			'hierarchical' => true,
		)*/
    }
}

add_action( 'init', array('SCN_Course', 'init') );


// Register function to be called when admin interface is visited
add_action( 'admin_init', 'scn_admin_init' );

// Function to register new meta box for book review post editor
function scn_admin_init() {
	add_meta_box( 'scn_course_details_meta_box', 'Course Details', 'scn_display_course_details_mb', 'scn_course', 'normal', 'high' );
}

// Function to display meta box contents
function scn_display_course_details_mb( $scn_course ) { 
	// Retrieve current author and rating based on book review ID
	$teacher = get_post_meta( $scn_course->ID, 'teacher', true );
	$capacity = get_post_meta( $scn_course->ID, 'capacity', true );
    $tuition = get_post_meta( $scn_course->ID, 'tuition', true);

    $all_teachers = array( 'Master A', 'Master B', 'Dr C' );
	?>
	<table>
		<tr>
			<td style="width: 150px">Capacity</td>
			<td><input type="number" style="width:100%" name="scn_course_capacity" value="<?php echo intval( $capacity ); ?>" /></td>
		</tr>
		<tr>
			<td style="width: 150px">Teacher</td>
			<td>
				<select style="width: 130px" name="scn_course_teacher">
					<option value="">Select teacher</option>
					<!-- Loop to generate all items in dropdown list -->
					<?php foreach ( $all_teachers as $t) { ?>
					<option value="<?php echo $t; ?>" <?php echo selected( $t, $teacher ); ?>><?php echo $t; ?>
					<?php } ?>
				</select>
			</td>
		</tr>
        <tr>
        <td style="width: 150px">Tuition</td>
        <td><input type="text" style="width:100%" name="scn_course_tuition" value="<?php echo floatval( $tuition ); ?>" /></td>
        </tr>
	</table>

<?php }

// Register function to be called when posts are saved
// The function will receive 2 arguments
add_action( 'save_post', 'scn_add_course_fields', 10, 2 );

function scn_add_course_fields( $course_id, $course ) {
    error_log(print_r($course) );
	if ( 'scn_course' != $course->post_type ) {
		return;
	}

	if ( isset( $_POST['scn_course_teacher'] ) ) {
		update_post_meta( $course_id, 'teacher', sanitize_text_field( $_POST['scn_course_teacher'] ) );
	}
	if ( isset( $_POST['scn_course_capacity'] ) && !empty( $_POST['scn_course_capacity'] ) ) {
		update_post_meta( $course_id, 'capacity', intval( $_POST['scn_course_capacity'] ) );
	}
    if ( isset( $_POST['scn_course_tuition'] ) && !empty( $_POST['scn_course_tuition'] ) ) {
		update_post_meta( $course_id, 'tuition', floatval( $_POST['scn_course_tuition'] ) );
	}

	/*******************************************************************
	* Code from recipe 'Hiding the taxonomy editor from the post editor 
	* while remaining in the admin menu'
	*******************************************************************/
/*
	if ( isset( $_POST['book_review_book_type'] ) ) {
		wp_set_post_terms( $book_review->ID, intval( $_POST['book_review_book_type'] ), 'book_reviews_book_type' );
	}
*/
}

/****************************************************************************
 * Code from recipe 'Displaying additional columns in custom post list page'
 ****************************************************************************/

// Register function to be called when column list is being prepared
add_filter( 'manage_edit-scn_course_columns', 'scn_add_columns' );

// Function to add columns for author and type in book review listing
// and remove comments columns
function scn_add_columns( $columns ) {
    $new_columns = array();
    $new_columns['scn_course_teacher'] = 'Teacher';
    $new_columns['scn_course_capacity'] = 'Capacity';
    $new_columns['scn_course_tuition'] = 'Tuition';

    unset( $columns['comments'] );
    $columns = array_slice( $columns, 0, 2 ) + $new_columns + array_slice( $columns, 2 );
    
    return $columns;
}

// Register function to be called when custom post columns are rendered
add_action( 'manage_posts_custom_column', 'scn_populate_columns' );

// Function to send data for custom columns when displaying items
function scn_populate_columns( $column ) {
	if ( 'scn_course' != get_post_type() ) {
		return;
    }

	// Check column name and send back appropriate data
	if ( 'scn_course_teacher' == $column ) {
        echo esc_html( get_post_meta( get_the_ID(), 'teacher', true ) );
    }
    elseif ( 'scn_course_capacity' == $column ) {
		echo intval( get_post_meta( get_the_ID(), 'capacity', true ) );
	}
	elseif ( 'scn_course_tuition' == $column ) {
		echo '$' . floatval( get_post_meta( get_the_ID(), 'tuition', true ) );
	}
}

add_filter( 'manage_edit-scn_course_sortable_columns', 'scn_course_column_sortable' );

// Register the author and rating columns are sortable columns
function scn_course_column_sortable( $columns ) {
	$columns['scn_course_teacher'] = 'scn_course_teacher';
	$columns['scn_course_capacity'] = 'scn_course_capacity';

	return $columns;
}

// Register function to be called when queries are being prepared to
// display post listing
add_filter( 'request', 'scn_course_column_ordering' );

// Function to add elements to query variable based on incoming arguments
function scn_course_column_ordering( $vars ) {
	if ( !is_admin() ) {
		return $vars;
	}
    error_log( 'ordering ' . print_r($vars) );
	if ( isset( $vars['orderby'] ) && 'scn_course_teacher' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'teacher',
				'orderby' => 'meta_value'
		) );
	}
	elseif ( isset( $vars['orderby'] ) && 'scn_course_tuition' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'tuition',
				'orderby' => 'meta_value_num'
		) );
	}

	return $vars;
}


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
        'SchoolNet', 'manage_options', 'schoolnet', 'scn_main_page', 'dashicons-welcome-learn-more', 10);
    add_submenu_page('schoolnet', 'Courses', 'Courses', 'manage_options', 'edit.php?post_type=scn_course');

    add_submenu_page('schoolnet', 'Transactions', 'Transactions', 'manage_options', 'edit.php?post_type=scn_transaction');
}

/*
function scn_override_user_edit() {
    require_once( 'scn_test.php' );
    die();
}
add_action( 'load-user-edit.php', 'scn_override_user_edit' );
*/

add_filter( 'show_admin_bar', 'scn_restrict_admin_bar');
function scn_restrict_admin_bar() {
    return current_user_can( 'administrator') ? true : false;
}
?>