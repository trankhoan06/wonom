<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other 'pages' on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage NMC
 * @since NMC 1.0
 */

global $disableFullpage;
$disableFullpage= true;

get_header();

while ( have_posts() ) : the_post(); 
$pageID = $cur_post_id= get_the_ID();
$cur_post_title =get_the_title();
$cur_post_content = $post->post_content;


endwhile;

?>

<?php
nmc_get_template_part( 'partials/section-banner', [
    'sectionId' => $pageID
] );

?>


<section  class=" relative section-top d-bg animatedParent animateOnce dark" >
    <div class="bg"></div>
        <div class="section-padding  div_zindex">
        <div class="container">
            <div class="section-content-wrapper post-detail page-detail">
                <div class="inner relative">
                    <h1 class="page-title post-title font-2 <?= defaultAnimation(1,"fadeInUpShort") ?>"><?= $cur_post_title ?></h1>
                    <div class="editor-content font-1  <?= defaultAnimation(2,"fadeInUpShort") ?>">
                        <?= apply_filters('the_content', $cur_post_content) ?>
                    </div>
                    <div class="post-control mt-4 d-flex <?= defaultAnimation(3,"fadeInUpShort") ?>">

                        <div class="share-box d-flex align-items-center">
                            <div class="share"><?= __("Chia sẻ","tbs") ?>:</div>
                            <div class="items social">
                                 <a class="btn-facebook" target="_blank" rel="nofollow" href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_link ?>">
                                  <i class="fab fa-facebook-f"></i>
                                </a>
                                <a class="btn-twitter" href="https://twitter.com/intent/tweet?text=<?= $share_link ?>" target="_blank" rel="noopener">
                                  <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<?php
get_footer();
