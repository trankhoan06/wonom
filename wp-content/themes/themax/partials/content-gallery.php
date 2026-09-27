<?php 
    if(!empty($template_args['data'])){
        $gallery = $template_args['data'];
    }
    else{
        return;
    }
    $title = $gallery["title"];
    $albums =  $gallery["albums"];
?>

<div class="section-slide items animated <?= defaultAnimation(0,"fadeInUpShort") ?>">
    <div class="swiper gallery-slide overflow-hide  " data-swiper-autoplay="2000">
        <div class="swiper-wrapper ">
            <?php
            foreach ($albums as  $g) {
                $image= wp_get_attachment_image_src($g,'full', false, false)[0];

             ?>
            <a href="<?= $image ?>" data-fancybox="gallery-slide-<?= $key ?>" class="d-inline-block swiper-slide cover " data-caption="<?= $title ?>" >
                <div class="item item-album" >
                    <div class="item-thumb fit-ratio animation">
                        <img src="<?= $image ?>" alt="<?= $title?>" title="<?= $title ?>">
                    </div>
                </div>
            </a>
            <?php }  ?>
        </div>
    </div>

    <div class="slide-control">
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
    </div>

</div>