<?php 
    if(!empty($template_args['video'])){
        $video = $template_args['video'];
    }
    $title = $video["title"];
    $link =  $video["link"];
    $image =  wp_get_attachment_image_src($video["image"],'full', false, false)[0];
?>

 <a href="<?= $link ?>" data-fancybox data-caption="<?= $title ?>" class="item d-block" >
   <div class="item-thumb animation">
        <img src="<?= $image ?>" alt="<?= $title ?>" title="<?= $title ?>">
    </div>
    <h2><?= $title ?></h2>
</a>