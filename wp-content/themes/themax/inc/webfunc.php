<?php

function beginBox($title,$close=true){
  $html = '<div class="postbox '.($close?"closed":"").'">';
  $html .= '<h2 class="hndle ui-sortable-handle"><span>'.$title.'</span></h2>';
  $html .= '<div class="inside">';
  return $html;
}
function endBox(){
  return '</div></div>';
}

function limitCharLength($excerpt,$charlength,$echo=false) {

  $charlength++;
  $result ="";

  if ( mb_strlen( $excerpt ) > $charlength ) {
    $subex = mb_substr( $excerpt, 0, $charlength - 5 );
    $exwords = explode( ' ', $subex );
    $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
    if ( $excut < 0 ) {
      $result = mb_substr( $subex, 0, $excut );
    } else {
      $result = $subex;
    }
    $result.= '[...]';
  } else {
    $result = $excerpt;
  }
  if($echo){
    echo $result;
  }
  else{
    return $result;
  }
}

function limit_text($text, $limit) {
  if (str_word_count($text, 0) > $limit) {
      $words = str_word_count($text, 2);
      $pos = array_keys($words);
      $text = substr($text, 0, $pos[$limit]) . '...';
  }
  return $text;
}

function showDistributionList($cateId){
    $html="";
    $posts = get_posts(array(
        'post_type' => 'distribution',
        'posts_per_page'  => -1,
        'orderby'=>'menu_order',
        'tax_query' => array(
          array(
            'taxonomy' => 'distributioncate',
            'field' => 'id',
            'terms' => $cateId,
            'include_children' => false
          )
        )
    ));
    if(!empty($posts)){
      $html.='<div class="distribution-list">';
      $html.='<div class="row">';
      foreach( $posts as $key=>$post ) : 
        $html.='<div class="col-sm-6 col-md-4">';
        $html.='<div class="item">';
        $html.= '<h4>'.get_the_title($post->ID).'</h4>';
        $html.= apply_filters('the_content', $post->post_content);
        $html.='</div>';
        $html.='</div>';
      endforeach;
      $html.='</div>';
      $html.='</div>';
    }
    
    return $html;
}

function getPageTemplateUrl($template){
  $args = [
      'post_type' => 'page',
      'numberposts' => 1,
      'nopaging' => true,
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-templates/'.$template.'.php'
  ];
  $pages = get_posts( $args );
  return count($pages)>0? get_permalink($pages[0]):"";
}

function getPageTemplateID($template){
  $args = [
      'post_type' => 'page',
      'numberposts' => 1,
      'nopaging' => true,
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-templates/'.$template.'.php'
  ];
  $pages = get_posts( $args );
  return count($pages)>0? $pages[0]->ID:"";
}


function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function notificationNewAccount($userName, $password){
  $body  ="Thông tin tài khoản<br>";
  $body .="Tài khoản: ".$userName."<br>";
  $body .="Mật khẩu: ".$password."<br>";
  $headers = array('Content-Type: text/html; charset=UTF-8');
  $check = wp_mail( get_option("admin_email").",".$userName, "Thông tin tài khoản từ website:", $body , $headers );
}



function nmc_get_template_part( $file, $template_args = array(), $cache_args = array() ) {

  $template_args = wp_parse_args( $template_args );
  $cache_args = wp_parse_args( $cache_args );

  if ( $cache_args ) {

    foreach ( $template_args as $key => $value ) {
      if ( is_scalar( $value ) || is_array( $value ) ) {
        $cache_args[$key] = $value;
      } else if ( is_object( $value ) && method_exists( $value, 'get_id' ) ) {
        $cache_args[$key] = call_user_method( 'get_id', $value );
      }
    }

    if ( ( $cache = wp_cache_get( $file, serialize( $cache_args ) ) ) !== false ) {

      if ( ! empty( $template_args['return'] ) )
        return $cache;

      echo $cache;
      return;
    }

  }

  $file_handle = $file;

  do_action( 'start_operation', 'hm_template_part::' . $file_handle );

  if ( file_exists( get_stylesheet_directory() . '/' . $file . '.php' ) )
    $file = get_stylesheet_directory() . '/' . $file . '.php';

  elseif ( file_exists( get_template_directory() . '/' . $file . '.php' ) )
    $file = get_template_directory() . '/' . $file . '.php';

  ob_start();
  $return = require( $file );
  $data = ob_get_clean();

  do_action( 'end_operation', 'hm_template_part::' . $file_handle );

  if ( $cache_args ) {
    wp_cache_set( $file, $data, serialize( $cache_args ), 3600 );
  }

  if ( ! empty( $template_args['return'] ) )
    if ( $return === false )
      return false;
    else
      return $data;

  echo $data;
}

function titleLineBreak($title){
  return str_replace("&nbsp;", "<br>", $title);
}

function getYoutubeID($url){
  preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
  return $match[1];
}

function formatTitle($title,$linebreak="<br />")
{
  return str_replace(' | ', $linebreak, $title);
}

function formatLink($link)
{
  return empty($link)?"javascript:void(0)":$link;
}

function defaultAnimation($delay=0,$animationName ="fadeInUpShort"){
  if($delay>0){
    return $animationName." animated delay-".(500 + 250 * $delay);
  }
  return $animationName." animated";
  
}