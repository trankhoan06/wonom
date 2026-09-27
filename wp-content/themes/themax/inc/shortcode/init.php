<?php
//Add Shortcode


add_shortcode("partner", "partner"); 
function partner( $atts, $content = "" ) {
    $atts = shortcode_atts( array(
                "xclass"      => false
    ), $atts );

    $args = array(
      'numberposts' => -1,
      'post_type'=>'partner'
    );
    $latest_posts = get_posts( $args );
    foreach ($latest_posts as $key2 => $post) {
    	 //get_template_part( "partials/content", "partner" );
    	 nmc_get_template_part( 'partials/content-partner', [ 'post' => $post ] );
    }
    //var_dump($latest_posts); 
    

}

