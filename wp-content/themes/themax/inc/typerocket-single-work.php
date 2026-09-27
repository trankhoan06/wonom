<?php

$box = tr_meta_box('Single Page Details');
$box->addScreen('work');
$box->addScreen('case-study-detail');

$box->setCallback(function() {
    $form = tr_form();

    // Hero Section
    echo "<h3>Hero Section</h3>";
    echo $form->image('hero_bg')->setLabel('Background Image');
    echo $form->image('hero_logo')->setLabel('Logo Image');
    echo $form->text('hero_logo_mobile_width')->setLabel('Logo Mobile Width (Nhập số px, VD: 200)');
    echo $form->text('hero_subtitle')->setLabel('Subtitle Text');

    // Info Section
    echo "<h3>Info Section (Categories, Clients, etc.)</h3>";
    echo $form->text('info_client_title')->setLabel('Client Title');
    echo $form->text('info_client_content')->setLabel('Client Content');

    echo $form->text('info_year_title')->setLabel('industry Title');
    echo $form->text('info_year_content')->setLabel('industry Content');

    echo $form->text('info_link_title')->setLabel('Scope of work Title');
    echo $form->text('info_link_content')->setLabel('Scope of work Content');

    // Content Items (The Solution)
    echo "<h3>Content / Blog Items</h3>";
    $blog = $form->repeater('blog_items')->setFields([
        $form->text('title')->setLabel('Title (e.g., The Solution)'),
        $form->textarea('subtitle')->setLabel('Subtitle'),
        $form->textarea('description')->setLabel('Description'),
        $form->gallery('image')->setLabel('Images (Multiple allowed)'),
        $form->toggle('is_vertical')->setLabel('Hình dọc')->setText('Có (Dùng cho hình ảnh dọc)'),
        $form->text('spacing')->setLabel('Khoảng cách các ảnh (VD: 32)')
    ]);
    echo $blog;


     // View web
    echo "<h3>View website</h3>";
    echo $form->text('view_text')->setLabel('VIEW LIVE WEBSITE');
    echo $form->text('view_link')->setLabel('link');
    
});

add_action('init', function() {
    remove_post_type_support('post', 'editor');
}, 100);
