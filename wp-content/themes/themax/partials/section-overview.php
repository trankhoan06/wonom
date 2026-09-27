<?php 
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] :0;
    $sectionImageId = tr_posts_field('oveview_bg',$sectionId);
    $sectionImageMobileId = tr_posts_field('oveview_bg_mobile',$sectionId);
    $sectionLink = tr_posts_field('oveview_link',$sectionId);
    $sectionImage = $sectionImageMobile =  wp_get_attachment_image_src($sectionImageId,'full', false, false)[0];
    if(!empty($sectionImageMobileId)){
        $sectionImageMobile =  wp_get_attachment_image_src($sectionImageMobileId,'full', false, false)[0];
    }
    $sectionItems = tr_posts_field('oveview_items',$sectionId);

?>

<section class=" animatedParent animateOnce fp-auto-height-responsive fp-noscroll section-cover section-introduction d-bg" data-anchor="gioi-thieu" style="background-image: url('<?= $sectionImage ?>')" data-title="<?= __("Tổng quan","tbs") ?>">

    <div class="section-content-wrapper  div_zindex section-padding">
        <div class="p-both-xxl">
            <div class="row gutter-0">
                <div class="col-12">
                    <div class="section-content-inner d-flex flex-column">
                        <div class="section-content">
                            <h2 class="section-title mb-0 <?= defaultAnimation(1) ?>">
                                <strong><?= __("Tổng quan","tbs") ?></strong>
                            </h2>
                            <div class="section-items-wrapper">
                                <div class="subtitle font-1 <?= defaultAnimation(2) ?>"><?= __("Dự án","tbs") ?></div>
                                <div class="section-items font-2">
                                    <div class="line <?= defaultAnimation(3) ?>"></div>
                                    <div class="items <?= defaultAnimation(3) ?>">
                                        <?php foreach ($sectionItems as $key => $value) { ?>
                                        <div class="item ">
                                            <div class="title"><?= $value["title"] ?></div>
                                            <div class="content"><span class="text-g"><?= $value["content"] ?></span></div>
                                        </div>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                            <?php if(!empty($sectionLink)){ ?>
                            <div class="section-items-other font-1 <?= defaultAnimation(4) ?>">
                                <a class="btn btn-icon" href="<?= $sectionLink ?>">
                                    <span class="icon">
                                        <img src="<?=  get_template_directory_uri() . '/images/icon-plus.svg' ?>">
                                    </span>
                                    <?= __("Xem thêm","tbs") ?>
                                </a>
                            </div>
                            <?php } ?>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="d-block d-xl-none">
        <div class="animated <?= defaultAnimation(3,"fadeInUpShort") ?>">
            <img src="<?= $sectionImageMobile ?>" alt="<?= __("Tổng quan","tbs") ?>" class="img-fluid">
        </div>
    </div>
</section>