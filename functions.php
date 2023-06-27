<?php

/***************************************************************************************************/
/*************************************** HIDE THE ADMIN BAR ****************************************/
/***************************************************************************************************/
add_filter('show_admin_bar', '__return_false');

/******************* EDIT THE CURRENT POST OR PAGE WITH CTRL+E KEYBOARD SHORTCUT *******************/
function add_keyboard_shortcut_to_edit_current_page() {
    if (current_user_can('administrator')) {
        global $post;
        $edit_link = html_entity_decode(get_edit_post_link($post->ID));
        echo '<script>
        document.addEventListener("keydown", function(e) {
            if (e.keyCode == 69 && e.ctrlKey) {
                window.location.href = "' . $edit_link . '";
            }
        });
        </script>';
    }
}
add_action('wp_footer', 'add_keyboard_shortcut_to_edit_current_page');

/*************************** ADD REUSABLE BLOCKS MENU ITEM TO ADMIN MENU ***************************/
function add_reusable_blocks_menu_item_to_admin_menu() {
    add_menu_page(
        'Reusable Blocks',
        'Reusable Blocks',
        'edit_posts',
        'edit.php?post_type=wp_block',
        '',
        'dashicons-editor-table',
        22
    );
}
add_action( 'admin_menu', 'add_reusable_blocks_menu_item_to_admin_menu' );

/********************** REMOVE GENERATEPRESS DEFAULT 404 PAGE TITLE AND TEXT **********************/
// Remove default 404 title and text
function remove_default_404_title_and_text() {
    add_filter( 'generate_404_title', '__return_false' );
    add_filter( 'generate_404_text', '__return_false' );
}
add_action( 'after_setup_theme', 'remove_default_404_title_and_text' );

/************************ REDIRECT ALL ATTACHMENT PAGES TO PARENT POST URL ************************/
add_action( 'template_redirect', 'wpsites_attachment_redirect' );
function wpsites_attachment_redirect(){
    global $post;
    if ( is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent != 0) ) :
        wp_redirect( get_permalink( $post->post_parent ), 301 );
        exit();
        // wp_reset_postdata();
    endif;
}

/*********************************** DUPLICATE POSTS AND PAGES ************************************/
    
// Add the duplicate link to action list for post_row_actions for "post" and custom post types
add_filter( 'post_row_actions', 'kh_duplicate_post_link', 10, 2 );
// For "page" post type
add_filter( 'page_row_actions', 'kh_duplicate_post_link', 10, 2 );

function kh_duplicate_post_link( $actions, $post ) {
    
    if( ! current_user_can( 'edit_posts' ) ) {
        return $actions;
    }
    
    $url = wp_nonce_url(
        add_query_arg(
            array(
                'action' => 'kh_duplicate_post_as_draft',
                'post' => $post->ID,
            ),
            'admin.php'
        ),
        basename(__FILE__),
        'duplicate_nonce'
    );
    
    $actions[ 'duplicate' ] = '<a href="' . $url . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    
    return $actions;
}
/* Create post duplicate as a draft and redirects to the edit post screen */
add_action( 'admin_action_kh_duplicate_post_as_draft', 'kh_duplicate_post_as_draft' );

/************************ REGISTER AJAX URL GLOBAL VARIABLE FOR FRONT END /************************/
// Allows us to make native ajax calls from javascript
add_action('wp_head', 'kh_define_ajaxurl');

function kh_define_ajaxurl() { ?>
    <script type="text/javascript">
    var ajaxurl = '<?php get_site_url(); ?>/wp-admin/admin-ajax.php';
    </script>
    <?php 
}

/*********************** REMOVE ADMIN COLOR THEME OPTION FROM USER SETTINGS ***********************/
function admin_color_scheme() {
    global $_wp_admin_css_colors;
    $_wp_admin_css_colors = [];
}
add_action("admin_head", "admin_color_scheme");

// remove application passwords from user profile
add_filter( 'wp_is_application_passwords_available', '__return_false' );

/***************************************************************************************************/
/************************************* DATE DISPLAY SHORTCODES *************************************/
/***************************************************************************************************/

/************************************* DATE AND TIME SHORTCODE *************************************/
function shortcode_date_time() {
    return date('F j, Y \a\t g:i a');
}
add_shortcode('date-and-time','shortcode_date_time');

/***************************************** DATE SHORTCODE ******************************************/
function shortcode_date() {
    // format like 01/25/2020
    return date('m/d/Y');
}
add_shortcode('date','shortcode_date');

/***************************************************************************************************/
/************************************** POST INFO SHORTCODES ***************************************/
/***************************************************************************************************/
function get_post_meta_data_id( $atts ) {
    return get_the_ID();
}
add_shortcode( 'get-post-id', 'get_post_meta_data_id' );

function get_post_meta_data_slug( $atts ) {
    global $post;
    $post_slug = $post->post_name;
    return $post_slug;
}
add_shortcode( 'get-post-slug', 'get_post_meta_data_slug' );

/***************************************************************************************************/
/************************************** USER INFO SHORTCODES ***************************************/
/***************************************************************************************************/

/************************************ USER FIRST NAME SHORTCODE ************************************/
function get_user_meta_data_first_name( $atts ) {
    $current_user = wp_get_current_user();
    return $current_user->first_name; 
}
add_shortcode( 'get-user-first-name', 'get_user_meta_data_first_name' );

/************************************ USER LAST NAME SHORTCODE *************************************/
function get_user_meta_data_last_name( $atts ) {
    $current_user = wp_get_current_user();
    return $current_user->last_name; 
}
add_shortcode( 'get-user-last-name', 'get_user_meta_data_last_name' );

/************************************ USER LOGIN NAME SHORTCODE ************************************/
function get_user_meta_data_login_name( $atts ) {
    $current_user = wp_get_current_user();
    return $current_user->user_login; 
}
add_shortcode( 'get-user-login-name', 'get_user_meta_data_login_name' );

/************************************** USER EMAIL SHORTCODE ***************************************/
function get_user_meta_data_email( $atts ) {
    $current_user = wp_get_current_user();
    return $current_user->user_email; 
}
add_shortcode( 'get-user-email-address', 'get_user_meta_data_email' );

/*********************************** USER DISPLAY NAME SHORTCODE ***********************************/
function get_user_meta_data_display_name( $atts ) {
    $current_user = wp_get_current_user();
    return $current_user->display_name; 
}
add_shortcode( 'get-user-display-name', 'get_user_meta_data_display_name' );

/*************************************** USER BIO SHORTCODE ****************************************/
function get_user_meta_data_description( $atts ) {
    $current_user = wp_get_current_user();
    return wpautop($current_user->description); 
}
add_shortcode( 'get-user-bio', 'get_user_meta_data_description' );
    
/************************************** USER AVATAR SHORTCODE **************************************/
function shortcode_user_avatar() {
    if(is_user_logged_in()){
        $current_user = wp_get_current_user();
        return get_avatar( $current_user -> ID, 520 );
    }
    else {
        // If not logged in then show default avatar.
        return get_avatar( 'http://1.gravatar.com/avatar/ad524503a11cd5ca435acc9bb6523536?s=64', 120 );
    }
}
add_shortcode('display-user-avatar','shortcode_user_avatar');
    
/************************************ USER AVATAR URL SHORTCODE ************************************/

function shortcode_user_avatar_url() {
    if(is_user_logged_in()) {
        global $current_user;
        wp_get_current_user();
        return get_avatar_url( $current_user );
    }
}
add_shortcode('get-user-avatar-url','shortcode_user_avatar_url');
    

/**********************************************************/
/********* VISITOR INFO ACQUISITION FUNCTIONS *************/
/**********************************************************/

/*********** FUNCTION TO GET IP OF CURRENT USER **********/
function get_visitor_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}

/*********** FUNCTION TO GET COUNTRY OF CURRENT USER **********/
function get_visitor_country() {
    $ip = get_visitor_ip();
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $country = $details->country;
    return $country;
}

/*********** FUNCTION TO GET CITY OF CURRENT USER **********/
function get_visitor_city() {
    $ip = get_visitor_ip();
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $city = $details->city;
    return $city;
}

/*********** FUNCTION TO GET REGION OF CURRENT USER **********/
function get_visitor_region() {
    $ip = get_visitor_ip();
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $region = $details->region;
    return $region;
}

/*********** FUNCTION TO GET LOCATION OF CURRENT USER **********/
function get_visitor_location() {
    $ip = get_visitor_ip();
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $location = $details->loc;
    return $location;
}

/**********************************************************/
/*************** NUMBER DISPLAY FUNCTIONS *****************/
/**********************************************************/

/********* FUNCTION TO DISPLAY NUMBER WITH COMMAS *********/
function display_number_with_commas( $atts ) {
    $number = $atts['number'];
    $number = number_format($number);
    return $number;
}

/******** FUNCTION TO SHORTEN NUMBERS FOR DISPLAY *********/
// ex: 1000 -> 1k
function shorten_number($number, $precision = 1) {
    if ($number < 1000) {
        $shortened_number = $number;
    } else if ($number < 1000000) {
        $shortened_number = round($number / 1000, $precision) . 'k';
    } else if ($number < 1000000000) {
        $shortened_number = round($number / 1000000, $precision) . 'm';
    } else if ($number < 1000000000000) {
        $shortened_number = round($number / 1000000000, $precision) . 'b';
    } else {
        $shortened_number = round($number / 1000000000000, $precision) . 't';
    }
    return $shortened_number;
}