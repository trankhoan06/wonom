<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 */

get_header();



while ( have_posts() ) : the_post(); 

$cur_post_id= get_the_ID();
$cur_post_type= get_post_type();
$cur_post_title =get_the_title();
$cur_post_content =get_the_content();

endwhile; 
?>


?>




<?php

get_footer();