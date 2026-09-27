<?php
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "our-client.php") {
        remove_post_type_support('page', 'editor');
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Hero Section", true);
        echo $form->textarea('client_hero_title')->setLabel("Hero Title");
        echo $form->textarea('client_hero_desc')->setLabel("Hero Description");
        echo $form->text('client_hero_subtitle')->setLabel("Hero Subtitle");
        echo endBox();

        // Tabs moved to Theme Options
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
