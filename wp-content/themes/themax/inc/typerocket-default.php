<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && 
        basename(get_page_template()) != "homepage.php" && 
        basename(get_page_template()) != "aboutus.php" && 
        basename(get_page_template()) != "career.php" && 
        basename(get_page_template()) != "contact.php" && 
        basename(get_page_template()) != "service.php" && 
        basename(get_page_template()) != "case-study.php" && 
        basename(get_page_template()) != "our-client.php" && 
        basename(get_page_template()) != "thanks.php" && 
        basename(get_page_template()) != "pdppolicy.php") {
        
        $form = tr_form();
        echo '<div class="typerocket-container">';
        
        echo beginBox("Banner Chính",true);
        echo $form->row(
            $form->image('banner_image')->setLabel("Banner"),
            $form->image('banner_image_mobile')->setLabel("Banner Mobile")
        );
        echo $form->row(
            $form->text('banner_title')->setLabel("Tiêu đề"),
            $form->textarea('banner_content')->setLabel("Nội dung")
        );
        echo endBox();

        echo '</div>';
    }
});
