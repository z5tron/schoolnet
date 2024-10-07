<?php

/** class */

class SCN_Student {
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
            'scn_student',
            array(
                'labels' => array(
                    'name' => __( 'Students' ),
                    'singular_name' => __( 'Student' ),
                    'add_new' => __( 'Add New' ),
                    'add_new_item' => __( 'Add New Student' ),
                    'edit_item' => __( 'Edit Student' ),
                    'new_item' => __( 'New Student' ),
                    'view_item' => __( 'View Student' ),
                    'search_item' => __( 'Search Student' ),
                ),
                'public' => true,
                'has_archive' => false,
                'show_in_menu' => false,
                'menu_icon' => 'dashicons-book-alt',
                'supports' => array( 'title', 'comments', 'author', 'thumbnail' ),
				'capability_type' => array('scn_student', 'scn_students'),
				'map_meta_cap' => true,
            )
        );
    }
}

add_action( 'init', array('SCN_Student', 'init') );



// Function to display meta box contents
function scn_display_student_details_mb( $scn_student ) { 
	// Retrieve current author and rating based on book review ID
	$first_name   = get_post_meta( $scn_student->ID, 'first_name', true );
	$last_name    = get_post_meta( $scn_student->ID, 'last_name', true );
    $chinese_name = get_post_meta( $scn_student->ID, 'chinese_name', true);
    $enrollment   = get_post_meta( $scn_student->ID, 'enrollment', true);

	$courses = scn_get_all_courses( '2022 - 2023' );
	error_log( 'all courses: ' . print_r($courses) );

    ?>
	<table>
        <tr>
			<td style="width: 150px">First Name</td>
			<td><input type="text" style="width:100%" name="scn_student_first_name" value="<?php echo $first_name; ?>"/></td>
		</tr>
		<tr>
			<td style="width: 150px">Last Name</td>
			<td><input type="text" style="width:100%" name="scn_student_last_name" value="<?php echo $last_name; ?>" /></td>
		</tr>
		<tr>
			<td style="width: 150px">Chinese Name</td>
			<td><input type="text" style="width:100%" name="scn_student_chinese_name" value="<?php echo $chinese_name; ?>" /></td>
		</tr>
	</table>

<?php }

// Register function to be called when posts are saved
// The function will receive 2 arguments
add_action( 'save_post', 'scn_add_student_fields', 10, 2 );

function scn_add_student_fields( $student_id, $student ) {
    error_log(print_r($student) );
	if ( 'scn_student' != $student->post_type ) {
		return;
	}

	$first_name = ''; // $_POST['scn_student_first_name'];
	$last_name = ''; // $_POST['scn_student_last_name'];
	
	if ( isset( $_POST['scn_student_first_name'] ) ) {
		$first_name = sanitize_text_field( $_POST['scn_student_first_name'] );
		update_post_meta( $student_id, 'first_name', $first_name );
	}
	if ( isset( $_POST['scn_student_last_name'] ) ) 
	{
		$last_name = sanitize_text_field( $_POST['scn_student_last_name'] );
		update_post_meta( $student_id, 'last_name', $last_name );
	}
	if ( isset( $_POST['scn_student_chinese_name'] ) ) {
		update_post_meta( $student_id, 'chinese_name', sanitize_text_field( $_POST['scn_student_chinese_name'] ) );
	}
    
	if (!empty($first_name) || !empty($last_name)) {
		remove_action( 'save_post', 'scn_add_student_fields' );
		wp_update_post( array('ID' => $student_id, 'post_title' => $first_name . ' ' . $last_name) );
		add_action( 'save_post', 'scn_add_student_fields' );
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
add_filter( 'manage_edit-scn_student_columns', 'scn_student_add_columns' );

// Function to add columns for author and type in book review listing
// and remove comments columns
function scn_student_add_columns( $columns ) {
    $new_columns = array();
    $new_columns['scn_student_first_name'] = 'First Name';
    $new_columns['scn_student_last_name'] = 'Last Name';
    $new_columns['scn_student_chinese_name'] = 'Chinese Name';
    
    unset( $columns['comments'] );
    $columns = array_slice( $columns, 0, 2 ) + $new_columns + array_slice( $columns, 2 );
    
    return $columns;
}

// Register function to be called when custom post columns are rendered
add_action( 'manage_posts_custom_column', 'scn_student_populate_columns' );

// Function to send data for custom columns when displaying items
function scn_student_populate_columns( $column ) {
	if ( 'scn_student' != get_post_type() ) {
		return;
    }

	// Check column name and send back appropriate data
	if ( 'scn_student_first_name' == $column ) {
        echo esc_html( get_post_meta( get_the_ID(), 'first_name', true ) );
    }
    elseif ( 'scn_student_last_name' == $column ) {
        echo esc_html( get_post_meta( get_the_ID(), 'last_name', true ) );
    }
    elseif ( 'scn_student_chinese_name' == $column ) {
        echo esc_html( get_post_meta( get_the_ID(), 'chinese_name', true ) );
    }
    /*elseif ( 'scn_student_capacity' == $column ) {
		echo intval( get_post_meta( get_the_ID(), 'capacity', true ) );
	}
	elseif ( 'scn_student_tuition' == $column ) {
		echo '$' . floatval( get_post_meta( get_the_ID(), 'tuition', true ) );
	}*/
}

add_filter( 'manage_edit-scn_student_sortable_columns', 'scn_student_column_sortable' );

// Register the author and rating columns are sortable columns
function scn_student_column_sortable( $columns ) {
	$columns['scn_student_first_name'] = 'scn_student_first_name';
	$columns['scn_student_last_name'] = 'scn_student_last_name';
	$columns['scn_student_chinese_name'] = 'scn_student_chinese_name';

	return $columns;
}

// Register function to be called when queries are being prepared to
// display post listing
add_filter( 'request', 'scn_student_column_ordering' );

// Function to add elements to query variable based on incoming arguments
function scn_student_column_ordering( $vars ) {
	if ( !is_admin() ) {
		return $vars;
	}
    // error_log( 'ordering ' . print_r($vars) );
	if ( isset( $vars['orderby'] ) && 'scn_student_first_name' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'first_name',
				'orderby' => 'meta_value'
		) );
	}
	/*elseif ( isset( $vars['orderby'] ) && 'scn_student_tuition' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
				'meta_key' => 'tuition',
				'orderby' => 'meta_value_num'
		) );
	}*/

	return $vars;
}

