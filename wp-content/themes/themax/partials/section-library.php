<?php 
    
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] : get_option('page_on_front');

    $sectionAnchor = !empty($template_args['anchor']) ?$template_args['anchor'] :"thu-vien";
    $title = !empty($template_args['title']) ?$template_args['title'] :__("Thư viện","tbs");

    $gallery_items = tr_posts_field('gallery_items',$sectionId);
    $video_items= tr_posts_field("video_items",$sectionId);
    $document_items= tr_posts_field("document_items",$sectionId);

    $othersItems=[];
    $albumImage="";
    foreach ($gallery_items as $key => $value) {
        if(empty($value["is_home"])){
            continue;
        }
        $albumImage =  wp_get_attachment_image_src($value["image"],'full', false, false)[0];
        $gallery = $value["albums"];
        foreach ($gallery as  $g) {
            $othersItems[]='<a data-fancybox="gallery-slide-'.$key.'" data-transition-effect="slide" href="'.wp_get_attachment_image_src($g,'full', false, false)[0].'"></a>';
        }
    }

    foreach ($video_items as $key => $value) {
        if(empty($value["is_home"])){
            continue;
        }
        $albumImage =  wp_get_attachment_image_src($value["image"],'full', false, false)[0];
        $gallery = $value["albums"];
        foreach ($gallery as  $g) {
            $othersItems[]='<a data-fancybox="gallery-slide-'.$key.'" data-transition-effect="slide" href="'.wp_get_attachment_image_src($g,'full', false, false)[0].'"></a>';
        }
    }


?>
<section  class=" animatedParent animateOnce  fp-auto-height-responsive fp-noscroll section-cover section-library  " data-anchor="<?= $sectionAnchor ?>"  data-title="<?= $title ?>" >
    
    <div class="section-padding animated fadeInUp div_zindex">
        <div class="container">
            <h2 class="section-title font-2 text-center animated <?= defaultAnimation(0,"fadeInUpShort") ?>">
                <?= $title ?>
            </h2>
            <div class="items row gutter-xxl">
                <?php foreach ($gallery_items as $key => $value) {
                    if(empty($value["is_home"])){
                        continue;
                    }
                    else{
                        $albumImage =  wp_get_attachment_image_src($value["image"],'full', false, false)[0];
                        $gallery = $value["albums"];
                        foreach ($gallery as  $g) {
                            $othersItems[]='<a data-fancybox="gallery-slide-'.$key.'" data-transition-effect="slide" href="'.wp_get_attachment_image_src($g,'full', false, false)[0].'"></a>';
                        }
                ?>
                <div class="col-md-4">
                    <a class="item d-block trigger-fancybox" href="javascript:void(0)" data-target="gallery-slide-<?= $key ?>">
                        <div class="item-thumb fit-ratio animation">
                            <img class="img-fluid" src="<?= $albumImage ?>" alt="<?= $value["title"] ?>">
                        </div>
                        <h4 class="title"><?= $value["title"] ?></h4>
                        <div class="btn btn-default btn-detail btn-icon " ><?= __("Xem Album","tbs") ?></div>
                    </a>
                    <div class="item-album d-none"><?= implode("", $othersItems) ?></div>
                </div>
                <?php
                        break;
                    }

                } ?>

                <?php
                    foreach ($document_items as $key => $value) {
                        if(empty($value["is_home"])){
                            continue;
                        }
                        else{

                        $title = $value["title"];
                        $link =  $value["link"];
                        $image =  wp_get_attachment_image_src($value["image"],'full', false, false)[0];
                     ?>
                    <div class="col-md-4">
                        <a class="item d-block item-document " href="<?= $link ?>" download target="_blank">
                            <div class="item-thumb fit-ratio animation">
                                <img class="img-fluid" src="<?= $image ?>" alt="<?= $title ?>">
                            </div>
                            <h4 class="title"><?= __("Tài liệu","tbs") ?></h4>
                            <div class="btn btn-default btn-detail btn-download btn-icon"><?= __("Tải tài liệu","tbs") ?></div>
                        </a>
                    </div>
                     <?php
                            break;
                        }

                    } ?>

                <?php
                    foreach ($video_items as $key => $value) {
                        if(empty($value["is_home"])){
                            continue;
                        }
                        else{

                        $title = $value["title"];
                        $link =  $value["link"];
                        $image =  wp_get_attachment_image_src($value["image"],'full', false, false)[0];
                     ?>
                    <div class="col-md-4">
                        <a class="item d-block"  href="<?= $link ?>" data-fancybox data-caption="<?= $title ?>" >
                            <div class="item-thumb fit-ratio animation">
                                <img class="img-fluid" src="<?= $image ?>" alt="<?= $title ?>">
                            </div>
                            <h4 class="title"><?= $title ?></h4>
                            <div class="btn btn-default btn-detail btn-icon " ><?= __("Xem Video","tbs") ?></div>
                        </a>
                    </div>
                     <?php
                            break;
                        }

                    } ?>

            </div>
        </div>

    </div>
</section>