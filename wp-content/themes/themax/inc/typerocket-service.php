<?php
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "service.php") {
        remove_post_type_support('page', 'editor');
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Hero Section", true);
        echo $form->textarea('service_hero_title')->setLabel("Hero Title");
        echo $form->textarea('service_hero_desc')->setLabel("Hero Description");
        echo $form->text('service_hero_btn_text')->setLabel("Button Text");
        // echo $form->text('service_hero_btn_link')->setLabel("Button Link");
        echo endBox();

        echo beginBox("Service Image", true);
        echo $form->image('service_img_desktop')->setLabel("Image Desktop");
        // echo $form->image('service_img_mobile')->setLabel("Image Mobile");
        echo endBox();

        echo beginBox("Service Section Text", true);
        echo $form->text('service_main_subtitle')->setLabel("Main Subtitle (e.g. OUR SERVICES)");
        echo $form->textarea('service_main_title')->setLabel("Main Title");
        echo $form->text('service_main_desc')->setLabel("Main Description (e.g. OUR PROCESS...)");
        echo endBox();

        echo beginBox("Service Cards", true);
        echo $form->repeater('home_services')->setLabel("Service Items")->setFields([
            $form->text('title')->setLabel("Title (e.g. Strategy)"),
            $form->text('desc')->setLabel("Description"),
            $form->text('subtitle')->setLabel("Subtitle (e.g. MARKETING CAMPAIGN)"),
            $form->image('image')->setLabel("Image"),
            $form->repeater('list')->setLabel("Features List")->setFields([
                $form->text('item')->setLabel("Feature Item Name")
            ])
        ]);
        echo endBox();

        echo beginBox("Call to Action (CTA)", true);
        echo $form->image('about_cta_img')->setLabel("CTA Image");
        echo $form->text('about_cta_link')->setLabel("CTA Link");
        echo $form->textarea('about_cta_text1')->setLabel("CTA Text Line 1");
        echo $form->text('about_cta_text2')->setLabel("CTA Text Line 2 (White)");
        echo $form->text('about_cta_text3')->setLabel("CTA Text Line 3 (Red)");
        echo endBox();

        echo '</div>';
    }
});
