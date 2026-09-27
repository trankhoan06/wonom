<?php
add_action('edit_form_after_title', function($post) {
    if ($post->post_type == 'page' && basename(get_page_template()) == "homepage.php") {
        remove_post_type_support( 'page', 'editor' );
        $form = tr_form();
        echo '<div class="typerocket-container">';
        
        echo beginBox("Banner Chính",true);
        echo $form->file('home_banner_video')->setLabel("Banner Video (.mp4)");
        echo $form->image('home_banner_poster')->setLabel("Banner Video Poster Image");
        echo endBox();

        echo beginBox("Intro Text & Images (Giới thiệu)",true);
        echo $form->text('home_intro_text_1')->setLabel("Tiêu đề dòng 1");
        echo $form->text('home_intro_text_2')->setLabel("Tiêu đề dòng 2 (Translate)");
        echo $form->text('home_intro_text_3')->setLabel("Tiêu đề dòng 3 (Màu đỏ)");
        echo $form->textarea('home_intro_subtext')->setLabel("Mô tả phụ");
        echo $form->gallery('home_intro_images')->setLabel("Danh sách hình ảnh");
        echo endBox();

        echo beginBox("Specialize (Lĩnh vực chuyên môn)",true);
        echo $form->image('specialize_bg')->setLabel("Hình nền chuyên môn");
        echo $form->text('specialize_title_1')->setLabel("Tiêu đề phần 1 (Trước chữ đỏ)");
        echo $form->repeater('specialize_red_texts')->setLabel("Các dòng chữ đỏ (Loop)")->setFields([
            $form->text('text')->setLabel("Nội dung")
        ]);
        echo $form->textarea('specialize_title_2')->setLabel("Tiêu đề phần 2 (Sau chữ đỏ)");
        echo $form->text('specialize_subtitle')->setLabel("Tiêu đề phụ");
        echo endBox();

        echo beginBox("Services (Dịch vụ)",true);
        echo $form->text('home_services_subtitle')->setLabel("Tiêu đề phụ (VD: OUR SERVICES)");
        echo $form->textarea('home_services_title')->setLabel("Tiêu đề chính (VD: We provide full-service...)");
        echo $form->textarea('home_services_desc')->setLabel("Mô tả thêm (VD: OUR PROCESS...)");
        echo $form->repeater('home_services')->setLabel("Danh sách dịch vụ")->setFields([
            $form->text('title')->setLabel("Tên dịch vụ"),
            $form->textarea('description')->setLabel("Mô tả dịch vụ"),
            $form->text('bottom_des')->setLabel("Tiêu đề phụ (MARKETING CAMPAIGN)"),
            $form->image('image')->setLabel("Hình ảnh dịch vụ"),
            $form->repeater('service_list')->setLabel("Các hạng mục con")->setFields([
                $form->text('item')->setLabel("Tên hạng mục")
            ])
        ]);
        echo endBox();

        echo beginBox("Case Studies (Dự án)",true);
        echo $form->text('home_case_subtitle')->setLabel("Tiêu đề phụ (VD: OUR WORKS)");
        echo $form->text('home_case_title')->setLabel("Tiêu đề chính (VD: Discover featured projects)");
        echo $form->repeater('home_case_works')->setLabel("Danh sách dự án hiển thị (kéo thả để sắp xếp)")->setFields([
            $form->search('work_id')->setLabel("Chọn dự án")->setPostType('work')
        ]);
        echo $form->row(
            $form->text('home_case_btn_text')->setLabel("Text nút bấm"),
            $form->text('home_case_btn_link')->setLabel("Link nút bấm")
        );
        echo endBox();
        echo beginBox("Our Clients (Khách hàng)",true);
        echo $form->text('home_clients_subtitle')->setLabel("Tiêu đề phụ (VD: TYPICAL CLIENTS)");
        echo $form->textarea('home_clients_title')->setLabel("Tiêu đề chính (VD: We are proud to partner...)");

        echo endBox();


        echo '</div>';
    }
});
