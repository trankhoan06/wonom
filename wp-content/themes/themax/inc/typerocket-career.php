<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "career.php") {
        remove_post_type_support( 'page', 'editor' );
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Hero Section", true);
        echo $form->text('career_hero_subtitle')->setLabel("Hero Subtitle (Optional)");
        echo $form->textarea('career_hero_title')->setLabel("Hero Title");
        echo $form->textarea('career_hero_desc')->setLabel("Hero Description");
        echo $form->text('career_hero_btn_text')->setLabel("Button Text");
        // echo $form->text('career_hero_btn_link')->setLabel("Button Link");
        echo endBox();

        echo beginBox("Career Images", true);
        echo $form->image('career_img_desktop')->setLabel("Image Desktop");
        // echo $form->image('career_img_mobile')->setLabel("Image Mobile");
        echo endBox();

        echo beginBox("Why Choose Wonom", true);
        echo $form->text('career_why_subtitle')->setLabel("Subtitle");
        echo $form->textarea('career_why_title')->setLabel("Title");
        echo $form->text('career_why_desc')->setLabel("Description");
        echo $form->repeater('career_why_list')->setLabel("Why Items")->setFields([
            $form->image('icon')->setLabel("Icon"),
            $form->text('title')->setLabel("Title"),
            $form->textarea('desc')->setLabel("Description")
        ]);
        echo $form->image('career_why_bg')->setLabel("Background Image");
        echo $form->image('career_why_choose_desktop')->setLabel("Choose Image Desktop");
        echo $form->image('career_why_choose_mobile')->setLabel("Choose Image Mobile");
        echo $form->text('career_why_bottom_text')->setLabel("Bottom Text (Are you ready...)");
        echo endBox();

        echo beginBox("Job List Settings", true);
        echo $form->text('career_list_col1')->setLabel("Column 1 Header")->setDefault("POSITION");
        echo $form->text('career_list_col2')->setLabel("Column 2 Header")->setDefault("LEVEL");
        echo $form->text('career_list_col3')->setLabel("Column 3 Header")->setDefault("QUANTITY");
        echo $form->text('career_list_col4')->setLabel("Column 4 Header")->setDefault("DEADLINE");
        echo endBox();

        echo beginBox("Call to Action (CTA)", true);
        echo $form->image('career_cta_img')->setLabel("CTA Image");
        echo $form->text('career_cta_title')->setLabel("Title");
        echo $form->textarea('career_cta_desc')->setLabel("Description");
        echo $form->text('career_cta_btn_text')->setLabel("Button Text");
        // echo $form->text('career_cta_btn_link')->setLabel("Button Link");
        echo endBox();

        echo '</div>';
    }
});
