<?php 
    if(!empty($template_args['post'])){
        $post = $template_args['post'];
    }
    $title = get_the_title($post->ID);
    $link = get_permalink($post->ID);
    //$link = "javascript:void(0)";
    $description = get_the_excerpt($post->ID);
    $images = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'post-default', false, false);
    if(empty($images)){
        $images =get_template_directory_uri()."/images/default-".$post->post_type.".jpg";
    }
    else{
        $images= $images[0];
    }
    $post_day = date('d', strtotime($post->post_date));
    $post_date = date('m.Y', strtotime($post->post_date));
?>

<a class="item " href="<?= $link ?>">
    <div class="item-thumb fit-ratio animation" >
        <img src="<?= $images ?>" alt="<?=  $title ?>" class="img-fluid">
    </div>
    <div class="item-body">
        <h3 class="item-title font-2"><?=  $title ?></h3>
        <div class="item-bottom">
            <div class="item-date font-1">
                <strong class="font-2"><?= $post_day ?></strong>.<?= $post_date ?>
            </div>
            <div class="icon-circle">
                <i class="fal fa-long-arrow-right"></i>
            </div>
        </div>
    </div>

</a>
