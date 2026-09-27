<?php
add_action('edit_form_after_title', function ($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "aboutus.php") {
        remove_post_type_support('page', 'editor');
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Hero Section", true);
        echo $form->textarea('about_hero_title1')->setLabel("Hero Title Line 1 (White)");
        echo $form->text('about_hero_title2')->setLabel("Hero Title Line 2 (Red)");
        echo $form->textarea('about_hero_title3')->setLabel("Hero Title Line 3 (White)");
        echo $form->textarea('about_hero_desc')->setLabel("Hero Description");
        echo endBox();

        echo beginBox("Impressive Numbers", true);
        echo $form->text('about_impr_subtitle')->setLabel("Subtitle");
        echo $form->textarea('about_impr_title')->setLabel("Title");
        echo $form->textarea('about_impr_desc')->setLabel("Description");
        echo $form->repeater('about_impr_list')->setLabel("Numbers List")->setFields([
            $form->text('number')->setLabel("Number (e.g. 500+)"),
            $form->text('label')->setLabel("Label (e.g. Complete the project)")
        ]);
        echo endBox();

        echo beginBox("Company Images", true);
        echo $form->image('about_company_img_desktop')->setLabel("Image Desktop");
        // echo $form->image('about_company_img_mobile')->setLabel("Image Mobile");
        echo endBox();

        echo beginBox("What We Do Best", true);
        echo $form->text('about_best_subtitle')->setLabel("Subtitle");
        echo $form->textarea('about_best_title')->setLabel("Title");
        echo $form->textarea('about_best_desc')->setLabel("Description");
        echo $form->text('about_best_cap')->setLabel("Caption");
        echo endBox();

        echo beginBox("The Team", true);
        echo $form->text('about_team_subtitle')->setLabel("Subtitle");
        echo $form->textarea('about_team_title')->setLabel("Title");
        echo $form->textarea('about_team_desc')->setLabel("Description");
        echo $form->repeater('about_team_list')->setLabel("Values/Insight")->setFields([
            $form->image('icon')->setLabel("Icon"),
            $form->image('icon_hover')->setLabel("Icon Hover"),
            $form->text('title')->setLabel("Title"),
            $form->text('sub')->setLabel("Subtitle"),
            $form->textarea('desc')->setLabel("Description")
        ]);
        echo $form->repeater('about_team_cards')->setLabel("Team Cards")->setFields([
            $form->image('img')->setLabel("Card Image"),
            $form->text('title')->setLabel("Card Title"),
            // $form->textarea('desc')->setLabel("Card Description"),
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
