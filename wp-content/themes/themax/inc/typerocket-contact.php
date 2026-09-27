<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "contact.php") {
        remove_post_type_support( 'page', 'editor' );
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Hero & Form", true);
        echo $form->textarea('contact_hero_title')->setLabel("Hero Title");
        echo $form->text('contact_hero_subtitle')->setLabel("Hero Subtitle");
        echo $form->text('contact_hero_name')->setLabel("Contact Name");
        echo $form->text('contact_hero_tel')->setLabel("Contact Telephone");
        echo $form->textarea('contact_form_shortcode')->setLabel("Form Shortcode (e.g. Contact Form 7 shortcode). If empty, shows default HTML form.");
        echo endBox();

        echo beginBox("Contact Image", true);
        echo $form->image('contact_img_desktop')->setLabel("Image Desktop");
        // echo $form->image('contact_img_mobile')->setLabel("Image Mobile");
        echo endBox();

        echo beginBox("Contact Form Text", true);
        echo $form->text('contact_form_name')->setLabel('Name Placeholder')->setDefault('Your name');
        echo $form->text('contact_form_email')->setLabel('Email Placeholder')->setDefault('Email address');
        echo $form->text('contact_form_phone')->setLabel('Phone Placeholder')->setDefault('Phone number');
        echo $form->text('contact_form_company')->setLabel('Company Placeholder')->setDefault('Company name');
        echo $form->text('contact_form_advice')->setLabel('Advice Placeholder')->setDefault('What area do you need advice on?');
        echo $form->text('contact_form_submit')->setLabel('Submit Button Text')->setDefault('SUBMIT');

        echo "<hr><h4>Error Messages</h4>";
        echo $form->text('contact_err_name')->setLabel('Error Name')->setDefault('Please enter your name');
        echo $form->text('contact_err_email')->setLabel('Error Email')->setDefault('Please enter a valid email');
        echo $form->text('contact_err_phone')->setLabel('Error Phone')->setDefault('Please enter your phone number');
        echo endBox();

        echo '</div>';
    }
});
