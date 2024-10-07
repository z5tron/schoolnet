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

/** main setting */

// Register function to be called when the plugin is activated
register_activation_hook( __FILE__, 'scn_set_default_options' );

// Function called upon plugin activation to initialize the options values
// if they are not present already
function scn_set_default_options() { 
	scn_get_options();
}

function scn_school_year_str($start) { return $start . ' - ' . ($start + 1); }
function scn_school_year_str_today() {
    $mmdd = date("nd"); // no leading zero for month
    $year = date("Y");
    if ($mmdd <= 707) $year--;
    return scn_school_year_str($year); 
}
function scn_list_school_years($first=2023) {
    $last = date("Y") + 1;
    $years = array("");
    for ($x = $first; $x <= $last; $x++) { $years[] = scn_school_year_str($x); }
    return $years;
}

function scn_school_year_ok() {
    $options = get_option( 'scn_options', array() );
    return isset($options['scn_school_year']) && $options['scn_school_year'] == scn_school_year_str_today();
}

function scn_get_school_year($def = true) {
    $options = get_option( 'scn_options', array() );
    if (isset($options['scn_school_year']) && $options['scn_school_year']) 
        return $options['scn_school_year'];
    if ($def) return scn_school_year_str_today();
    return "";
}

// Function to retrieve options from database as well as create or 
// add new options
function scn_get_options() {
    $options = get_option( 'scn_options', array() );

    // $new_options['scn_date']
    $new_options['scn_school_year'] = "";
    $new_options['scn_school_year_first_day'] = ""; 
    $new_options['scn_test_text'] = 'This is a test';
	
    $merged_options = wp_parse_args( $options, $new_options ); 

    $compare_options = array_diff_key( $new_options, $options );   
    if ( empty( $options ) || !empty( $compare_options ) ) {
        update_option( 'scn_options', $merged_options );
    }
    return $merged_options;
}

//
/**
// add new role : SCN Parent
function scn_add_parent_role() {
    add_role('scn_parent',
                'SCN Parent',
                array(
                    'read' => true,
                    'edit_posts' => false,
                    'delete_posts' => false,
                    'upload_files' => true,
            )
    );
}
register_activation_hook( __FILE__, 'scn_add_parent_role' );

add_action( 'admin_init', 'scn_add_role_caps', 999 );
function scn_add_role_caps() {
    $roles = array('scn_parent', 'editor', 'administrator' );
    foreach ($roles as $the_role) {
        $role = get_role($the_role);

        // $role->add_cap( 'read' );
        $role->add_cap( 'read_scn_course' );
        $role->add_cap( 'read_scn_student' );
        $role->add_cap( 'read_private_scn_students' );
        $role->add_cap( 'add_scn_student' );
        $role->add_cap( 'edit_scn_student' );
        $role->add_cap( 'edit_scn_students' );
        $role->add_cap( 'edit_private_scn_students' );
        $role->add_cap( 'edit_published_scn_student' );
        $role->add_cap( 'publish_scn_student' );
        $role->add_cap( 'publish_scn_students' );
        $role->add_cap( 'delete_private_scn_students' );
        $role->add_cap( 'delete_published_scn_students' );
        $role->add_cap( 'delete_scn_student' );
    }
}
 */

 
// Register action hook function to be called when the admin pages are
// starting to be prepared for display
add_action( 'admin_init', 'scn_options_admin_init' );

// Function to register the Settings for this plugin
// and declare the fields to be displayed
function scn_options_admin_init() {
	// Register our setting group with a validation function
	// so that $_POST handling is done automatically for us
	register_setting( 'scn_settings',
		'scn_options','scn_validate_options' );

	// Add a new settings section within the group
	add_settings_section( 'scn_main_section',
		'Main Settings', 'scn_main_setting_section_callback',
		'scn_settings_section' );

	// Add the fields with the names and function to use for our new
	// settings, put them in our new section
	/* add_settings_field( 'scn_school_year', 'School Year',
		'scn_display_text_field', 'scn_settings_section',
		'scn_main_section', array( 'name' => 'scn_school_year' ) );
    */
    // _display_check_box, _select_list, _text_area 
    add_settings_field( 'select_list', 'School Year', 'scn_select_list',
		'scn_settings_section', 'scn_main_section',
		array( 'name' => 'scn_school_year', 
			'choices' => scn_list_school_years() ) );
}

// Validation function to be called when data is posted by user
// No validation done at this time. Straight return of values.
function scn_validate_options( $input ) {
    // Cycle through all text form fields and store their values 
    // in the options array 
    foreach ( array( 'scn_school_year', 'select_list', 'text_area_desc' ) as $option_name ) { 
        if ( isset( $input[$option_name] ) ) { 
            $input[$option_name] = 
                sanitize_text_field( $input[$option_name] ); 
        } 
    } 
 	
	return $input;
}

// Function to display text at the beginning of the main section
function scn_main_setting_section_callback() { ?>
	<p>This is the main configuration section.</p>
<?php }

// Function to render a text input field
function scn_display_text_field( $data = array() ) {
	extract( $data );
	$options = scn_get_options(); 
	?>
	<input type="text" name="scn_options[<?php echo esc_html( $name ); ?>]" value="<?php echo esc_html( $options[$name] ); ?>"/><br />

<?php }

// Function to render a check box
function scn_display_check_box( $data = array() ) {
	extract ( $data );
	$options = scn_get_options(); 
	?>
	<input type="checkbox" name="scn_options[<?php echo esc_html( $name ); ?>]" <?php checked( $options[$name] ); ?>/>
<?php }

function scn_select_list( $data = array() ) {
	extract ( $data );
	$options = scn_get_options(); 
	?>
	<select name='scn_options[<?php echo esc_html( $name ); ?>]'>  
		<?php foreach( $choices as $item ) { ?>
			<option value="<?php echo esc_html( $item ); ?>" <?php selected( $options[$name] == $item ); ?>><?php echo esc_html( $item ); ?></option>;  
		<?php } ?>
	</select>  
<?php }

function scn_display_text_area( $data = array() ) {
	extract ( $data );
	$options = scn_get_options(); 
	?>
	<textarea type='text' name='scn_options[<?php echo esc_html( $name ); ?>]' rows='5' cols='30'><?php echo esc_html( $options[$name] ) ; ?></textarea>
<?php }


// Function called to render the contents of the plugin
// configuration page
function scn_main_page() { ?>
	<div id="scn-general" class="wrap">
	<h2>SchoolNet - Settings</h2>

	<form name="scn_options_form_settings_api" method="post" action="options.php">

	<?php settings_fields( 'scn_settings' ); ?>
	<?php do_settings_sections( 'scn_settings_section' ); ?> 

	<input type="submit" value="Submit" class="button-primary" />
	</form>
	</div>
<?php }


// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-scn-course.php';
require_once plugin_dir_path(  __FILE__ ) . 'class-scn-course.php';
require_once plugin_dir_path(  __FILE__ ) . 'class-scn-student.php';

function scn_get_all_courses($school_year) {
    $posts = get_posts([
        'post_type' => 'scn_course',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);
    $results = array();
    foreach($posts as $p) {
        $r = get_post_meta($p->ID, 'school_year', true);
        if (!empty($r) && $r == $school_year) $results[] = $r;
    }
    return $results;
}

// Register function to be called when admin interface is visited
add_action( 'admin_init', 'scn_admin_init' );

// Function to register new meta box for book review post editor
function scn_admin_init() {
    add_meta_box( 'scn_course_details_meta_box', 'Course Details', 'scn_display_course_details_mb', 'scn_course', 'normal', 'high' );
	add_meta_box( 'scn_student_details_meta_box', 'Student Details', 'scn_display_student_details_mb', 'scn_student', 'normal', 'high' );
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
/*
function scn_main_page() {
    echo '<h1 class="wp-heading-inline">interesting main page!!</h1>';
}*/
function scn_main_menu() {
    add_menu_page( 'SchoolNet Plugin Page',
        'SchoolNet', 'manage_options', 'schoolnet', 'scn_main_page', 'dashicons-welcome-learn-more', 10);
    add_submenu_page('schoolnet', 'Courses', 'Courses', 'manage_options', 'edit.php?post_type=scn_course');
    add_submenu_page('schoolnet', 'Students', 'Students', 'manage_options', 'edit.php?post_type=scn_student');

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