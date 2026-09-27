<?php 
    if(!empty($template_args['post'])){
        $post = $template_args['post'];
    }
    $title = get_the_title($post->ID);
    $link = get_permalink($post->ID);
    //$link = "javascript:void(0)";
    $description = get_the_excerpt($post->ID);
    $images = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'full', false, false);
    if(empty($images)){
        $images =get_template_directory_uri()."/images/default-".$post->post_type.".jpg";
    }
    else{
        $images= $images[0];
    }
    $post_date = date('d.m.Y', strtotime($post->post_date));
?>

<div class="item feature" >
    <a href="<?= $link ?>" class="d-block item-thumb fit-ratio animation cover" style="" >
        <img src="<?= $images ?>" alt="<?=  $title ?>" class="img-fluid">
    </a>
    <div class="item-body">
        <h3 class="item-title"><a href="<?= $link ?>"><?=  $title ?></a></h3>
        <p class="description ellips "><?=  $description ?></p>
        <div class="item-control ">
            <a class="btn btn-default btn-viewmore" href="<?= $link ?>"><span><?= __("Xem thêm","tbs") ?></span> <i class="fal fa-long-arrow-right"></i></a>
        </div>
    </div>
</div>
