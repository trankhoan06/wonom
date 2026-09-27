<?php 
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] :0;    
    $anchor =  !empty($template_args['anchor']) ?$template_args['anchor'] :"tbs";
    $sectionImageID = tr_posts_field('banner_image',$sectionId);
    $sectionImageMobileID = tr_posts_field('banner_image_mobile',$sectionId);
    $sectionImageMobile = $sectionImage =  wp_get_attachment_image_src($sectionImageID,'full', false, false)[0];
    if(!empty($sectionImageMobileID)){
        $sectionImageMobile = wp_get_attachment_image_src($sectionImageMobileID,'full', false, false)[0];
    }

    $sectionTitle =  tr_posts_field('banner_title',$sectionId);
    $sectionContent =  tr_posts_field('banner_content',$sectionId);

?>

<section class=" animatedParent animateOnce  fp-auto-height-responsive fp-noscroll section-cover section-with-content section-page-banner dark" data-anchor="gioi-thieu" style="background-image: url('<?= $sectionImage ?>')" data-title="<?= __("Giới thiệu","tbs") ?>">
    <div class="bg"></div>
    <div class="d-block d-lg-none">
        <div class="animated <?= defaultAnimation(3,"fadeInUpShort") ?>">
            <img src="<?= $sectionImageMobile ?>" alt="" class="img-fluid">
        </div>
    </div>
    <div class="section-content-wrapper div_zindex section-padding">
        <div class="container-d w-100">
            <div class="row gutter-0 justify-content-center">
                <div class="col-lg-10">
                    <div class="section-content-inner d-flex flex-column text-center">
                        <div class="section-content">
                            <h2 class="section-title <?= defaultAnimation(0) ?>">
                               <strong><?= $sectionTitle ?></strong>
                            </h2>
                            <div class="section-info section-content font-2 fz-20 <?= defaultAnimation(2) ?>">
                                <?= nl2br($sectionContent) ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>