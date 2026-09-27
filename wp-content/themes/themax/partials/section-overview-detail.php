<?php 
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] :0;
    $sectionImageId = tr_posts_field('oveview_bg',$sectionId);
    //$sectionImageMobileId = tr_posts_field('oveview_bg_mobile',$sectionId);
    $sectionLink = tr_posts_field('oveview_link',$sectionId);
    $sectionImage = $sectionImageMobile =  wp_get_attachment_image_src($sectionImageId,'full', false, false)[0];
    /*if(!empty($sectionImageMobileId)){
        $sectionImageMobile =  wp_get_attachment_image_src($sectionImageMobileId,'full', false, false)[0];
    }*/
    $sectionItems = tr_posts_field('oveview_items',$sectionId);

?>

<section class=" animatedParent animateOnce fp-auto-height-responsive fp-noscroll section-cover section-introduction-detail d-bg oveflow-hide" data-anchor="gioi-thieu" style="" data-title="<?= __("Tổng quan","tbs") ?>">
    <div class="bg"></div>
    <div class="section-content-wrapper  div_zindex section-padding">
        <div class="p-both-xl pe-lg-0">
            <div class="row gutter-xxl justify-content-center">
                <div class="col-lg-6 col-xl-5">
                    <div class="section-content-inner mb-4 mb-lg-0">

                        <div class="section-content font-2 do-nicescrol">
                            <div class="nicescrol-inner">
                                <h2 class="section-title <?= defaultAnimation(0) ?>">
                                    <strong><?= __("Tổng quan","tbs") ?></strong>
                                    <span><?= __("Dự án","tbs") ?></span>
                                </h2>
                                <div class="section-info <?= defaultAnimation(1,"fadeInDownShort") ?>">
                                <?php foreach ($sectionItems as $key => $items) { ?>
                                    <ul>
                                        <?php foreach ($items["group"] as $value) {
                                            $vtag = !empty($value["highlight"]) ?"strong":"span";
                                        ?>
                                        <li>
                                            <span><?= $value["title"] ?></span><<?= $vtag ?>><?= $value["content"] ?></<?= $vtag ?>>
                                        </li>
                                         <?php } ?>
                                    </ul>
                                <?php } ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-7">
                    <div class="item-thumb h-100 <?= defaultAnimation(2,"fadeInRightShort") ?>">
                        <img src="<?= $sectionImage ?>" alt="<?= __("Tổng quan","tbs") ?>" class="img-fluid w-100">
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>