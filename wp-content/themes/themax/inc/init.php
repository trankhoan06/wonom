<?php

include('cleanup.php');  // Xóa JS/CSS không cần thiết của WordPress
include('webfunc.php'); 
include('shortcode/init.php');
include('typerocket-config.php');
include('ajax.php');
//include('wp-bootstrap-navwalker.php');


// Update CSS within in Admin
function admin_style() {
  //wp_enqueue_style('admin-bootstrap-grid', get_template_directory_uri().'/typerocket/wordpress/assets/templates/css/bootstrap-grid.css');
  //wp_enqueue_style('admin-styles', get_template_directory_uri().'/typerocket/wordpress/assets/templates/css/custom.css');
  wp_enqueue_script('admin-script', get_template_directory_uri() . '/typerocket/wordpress/assets/templates/js/custom.js',array(),"1.0.1",true);
    
  //wp_enqueue_script('redactor-fontcolor-script', get_template_directory_uri() . '/typerocket/wordpress/assets/templates/js/redactor/fontcolor.js',array('typerocket-editor'),"1.0.1",true);
}
add_action('admin_enqueue_scripts', 'admin_style');

// Mail
add_action( 'phpmailer_init', 'my_phpmailer_example' );
function my_phpmailer_example( $phpmailer ) {
    $phpmailer->isSMTP();     
    $phpmailer->Host = tr_options_field('tr_theme_options.smtp_host');
    $phpmailer->SMTPAuth = tr_options_field('tr_theme_options.authentication');  // Force it to use Username and Password to authenticate
    $phpmailer->Port = tr_options_field('tr_theme_options.smtp_port');
    $phpmailer->Username = tr_options_field('tr_theme_options.username');
    $phpmailer->Password = tr_options_field('tr_theme_options.smtp_password');

    // Additional settings…
    $encryption= tr_options_field('tr_theme_options.encryption');
    $phpmailer->SMTPSecure =$encryption; // Choose SSL or TLS, if necessary for your server    
    if(empty($encryption)){
        $phpmailer->SMTPAutoTLS = false;
    }
    $phpmailer->From = tr_options_field('tr_theme_options.from_email');
    $phpmailer->FromName = get_bloginfo('name');                     
}

function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function fix_svg_thumb_display() {
  echo '<style type="text/css">
    td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail, #postimagediv .inside img { 
      width: 100% !important; 
      height: auto !important; 
    }
  </style>';
}
add_action('admin_head', 'fix_svg_thumb_display');


add_filter( 'posts_search', 'guid_search_so_14940004', 10, 2 );
function guid_search_so_14940004( $search, $a_wp_query ) 
{
    global $wpdb, $pagenow;

    // Only Admin side && Only Media Library page
    if ( !is_admin() && 'upload.php' != $pagenow ) 
        return $search;

    // Original search string:
    // AND (((wp_posts.post_title LIKE '%search-string%') OR (wp_posts.post_content LIKE '%search-string%')))
    $search = str_replace(
        'AND ((', 
        'AND (((' . $wpdb->prefix . 'posts.guid LIKE \'%' . $a_wp_query->query_vars['s'] . '%\') OR ', 
        $search
    ); 

    return $search;
}


add_action( 'restrict_manage_posts', 'add_export_button',10,2 );
function add_export_button($post_type, $which) {
    
    if('register' !== $post_type && 'newsletter' !== $post_type){
      return; 
    } 
    ?>
    <input type="submit" name="export_all_<?= $post_type ?>" id="export_all_<?= $post_type ?>" class="button button-primary" value="Export Danh Sách">
    <script type="text/javascript">
        jQuery(function($) {
            $('#export_all_<?= $post_type ?>').insertAfter('#post-query-submit');
        });
    </script>
    <?php
}

add_action( 'init', 'func_export_all_posts' );
function func_export_all_posts() {
    if(isset($_GET['export_all_register'])) {
        $arg = array(
                'post_type' => 'register',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );
 
        global $post;
        $arr_post = get_posts($arg);
        if ($arr_post) {
 
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="danh_sach_lien_he_'.time().'.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            $file = fopen('php://output', 'w');
 
            fputcsv($file, array('Ngày đăng ký','Họ Tên', 'Số điện thoại','Email','Quan tâm','Nội dung','Thiết bị','Nguồn'));
 
            foreach ($arr_post as $post) {
                setup_postdata($post);
                fputcsv($file, array(
                    get_the_date( 'd/m/Y H:i:s' ),
                    get_the_title(), 
                    get_post_meta( $post->ID, 'mobile', true ),
                    get_post_meta( $post->ID, 'email', true ),
                    //get_post_meta( $post->ID, 'address', true ),
                    get_post_meta( $post->ID, 'interest', true ),
                    get_post_meta( $post->ID, 'content', true ),
                    get_post_meta( $post->ID, 'device', true ),
                    get_post_meta( $post->ID, 'source', true ),
                    //get_post_meta( $post->ID, 'user_agent', true )

                ));
            }
 
            exit();
        }
    }

    if(isset($_GET['export_all_newsletter'])) {
        $arg = array(
                'post_type' => 'newsletter',
                'post_status' => 'publish',
                'posts_per_page' => -1,
            );
 
        global $post;
        $arr_post = get_posts($arg);
        if ($arr_post) {
 
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="danh_sach_newsletter_'.time().'.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            $file = fopen('php://output', 'w');
 
            fputcsv($file, array('Ngày đăng ký','Email'));
 
            foreach ($arr_post as $post) {
                setup_postdata($post);
                fputcsv($file, array(
                    get_the_date( 'd/m/Y H:i:s' ),
                    get_the_title(), 
                ));
            }
 
            exit();
        }
    }
}

function app_startSession() {
    // The session is only used by the legacy `checkpopup` AJAX endpoint.
    // Starting it for every public request sends a session cookie and prevents
    // reverse proxies/page-cache plugins from caching otherwise static pages.
    $is_checkpopup_request = wp_doing_ajax()
        && isset($_REQUEST['action'])
        && 'checkpopup' === sanitize_key(wp_unslash($_REQUEST['action']));

    if ($is_checkpopup_request && !session_id()) {
        session_start();
    }
}

add_action('init', 'app_startSession', 1);


add_action('wp_ajax_svg_get_attachment_url', 'get_attachment_url_media_library');
function get_attachment_url_media_library(){

    $url = '';
    $attachmentID = isset($_REQUEST['attachmentID']) ? $_REQUEST['attachmentID'] : '';
    if($attachmentID){
        $url = wp_get_attachment_url($attachmentID);
    }

    echo $url;

    die();
}
