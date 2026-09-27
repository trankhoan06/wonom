<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "thanks.php") {
        remove_post_type_support( 'page', 'editor' );
        $form = tr_form();
        echo '<div class="typerocket-container">';
        
        echo beginBox("Banner Chính",true);
        echo $form->image('banner')->setLabel("H�nh banner");
        echo $form->text('banner_title')->setLabel("Tiêu đề");
        echo $form->editor('banner_description')->setLabel("Nội dung");
        echo endBox();

        echo '</div>';
    }
});
