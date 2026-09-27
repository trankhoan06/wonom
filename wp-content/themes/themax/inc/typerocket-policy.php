<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "policy.php") {
        remove_post_type_support( 'page', 'editor' );
        $form = tr_form();
        echo '<div class="typerocket-container">';

        echo beginBox("Policy Information", true);
        echo $form->text('policy_title')->setLabel("Policy Title");
        echo $form->editor('policy_content')->setLabel("Policy Content");
        echo endBox();

        echo '</div>';
    }
});
