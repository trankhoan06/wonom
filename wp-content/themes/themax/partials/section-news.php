<?php 
    $anchor = !empty($template_args['anchor']) ?$template_args['anchor'] :"tin-tuc";
    $sId = !empty($template_args['sId']) ?$template_args['sId'] :"news";
    $sClass = !empty($template_args['sClass']) ?$template_args['sClass'] :"";
    $title = !empty($template_args['title']) ?$template_args['title'] :__("Tin thị trường","tbs");
    $cateId = !empty($template_args['cateId']) ?$template_args['cateId'] :0;
    $moreLink = get_permalink( get_option( 'page_for_posts' ) );
?>

<?php 
    $args = array(
      'numberposts' => 4,
      'post_type'=>'post'
    );
    if(!empty($cateId)){
        $args["numberposts"]= 4;
        $args["category"]= $cateId;
        $moreLink= get_term_link($cateId);
    }
    $latest_posts = get_posts( $args );
?>

<section id="<?= $sId ?>" class="section-home-news <?= $sClass ?> animatedParent animateOnce fp-noscroll fp-auto-height-responsive cover d-bg " data-anchor="<?= $anchor ?>"  data-title="<?= $title ?>">
    <div class="bg clip-thumb-wrapper">
        <div class="item-thumb clip-thumb clip-right">
            <img src="<?= get_template_directory_uri() ?>/images/tmp/bg-home-new.jpg" alt="TBS Group" class="img-fluid" />
        </div>
    </div>
    <div class=" section-padding-top  animated fadeInUpShort div_zindex">
            <div class="container-d">
                <h2 class="section-title text-center animated <?= defaultAnimation(0,"fadeInDownShort") ?> ">
                    <strong><?= $title ?></strong>
                </h2>
                <div class="section-content-wrapper ">
                    <div class="items post-list  animated fadeInUpShort delay-500">
                        <div class="swiper swiper-default post-slide <?= !empty($cateId)?"post-slide-cate":"" ?>" >
                          <div class="swiper-wrapper">
                            <?php

                                $index=0;
                                foreach ($latest_posts as $key => $post) {
                                    echo '<div class="swiper-slide" >';
                                    $index++;
                                    nmc_get_template_part( 'partials/content-news-other', ["post"=>$post,"index"=>$index] );
                                    echo '</div>';
                                }
                            ?>
                            <?php wp_reset_postdata(); ?>

                          </div>
                          <div class="slide-control ">
                              <div class="swiper-pagination mt-4"></div>
                          </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a class="btn btn-link mt-3 mt-lg-4 <?= defaultAnimation(2) ?>" href="<?= $moreLink ?>"><span><?= __("Xem thêm","tbs") ?></span> <i class="fal fa-long-arrow-right"></i></a>
                </div>
            </div>
    </div>
</section>