<?php 
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] :0;
    $sectionTitle = tr_posts_field('introduction_title',$sectionId);
    $sectionSubTitle = tr_posts_field('introduction_description',$sectionId);
    $sectionContent = tr_posts_field('introduction_content',$sectionId);

    $introduction_bg = tr_posts_field('introduction_bg',$sectionId);
    $introduction_bg_mobile = tr_posts_field('introduction_bg_mobile',$sectionId);
    if(empty($introduction_bg_mobile)){
        $introduction_bg_mobile = $introduction_bg;
    }
    $sectionTagline = tr_posts_field('introduction_content_title',$sectionId);
    $image =  wp_get_attachment_image_src($introduction_bg,'full', false, false)[0];


    //$image = get_template_directory_uri() ."/images/tmp/banner-1.jpg";
    //$introduction_bg_mobile = get_template_directory_uri() ."/images/tmp/banner-1.jpg";


?>
<section class=" animatedParent animateOnce  fp-auto-height-responsive fp-noscroll section-cover section-with-content section-introduction" data-anchor="gioi-thieu" style="background-image: url('<?= $image ?>')" data-title="<?= __("Giới thiệu","tbs") ?>">
    <div class="bg"></div>
    <div class="d-block d-lg-none">
        <div class="animated <?= defaultAnimation(3,"fadeInUpShort") ?>">
            <img src="<?= $image ?>" alt="" class="img-fluid">
        </div>
    </div>
    <div class="section-content-wrapper section-content-fitbottom  div_zindex section-padding">
        <div class="p-both-xl">
            <div class="row gutter-0">
                <div class="col-lg-4 col-xxl-4">
                    <div class="section-content-inner d-flex flex-column">
                        <div class="section-content mt-lg-4">
                            <h2 class="section-title <?= defaultAnimation(0) ?>">
                                <span class="text-g"><?= $sectionTitle ?></span>
                            </h2>
                            <div class="subtitle <?= defaultAnimation(1) ?>"><?= $sectionSubTitle ?></div>
                            <div class="section-info section-content <?= defaultAnimation(2) ?>">

                                <?= apply_filters("the_content",$sectionContent) ?>
                            </div>
                            <a href="<?= getPageTemplateUrl("aboutus") ?>" class="btn btn-default btn-detail <?= defaultAnimation(3) ?>" ><span><?= __("Xem thêm","tbs") ?></span> <i class="far fa-arrow-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>