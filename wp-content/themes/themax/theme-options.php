<?php
if ( ! function_exists( 'add_action' )) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Setup Form
$form = tr_form()->useJson()->setGroup( $this->getName() );
?>

<h1>Theme Options</h1>
<div class="typerocket-container">
    <?php
    echo $form->open();

    // CTA Single Case Study
    $cta_single = function() use ($form) {
        echo "<h3>CTA Section (Global for Single work)</h3>";
        echo "<p>Set the global CTA content here. This will appear at the bottom of all Case Study single pages.</p>";
        echo $form->image('about_cta_img')->setLabel('CTA Image');
        echo $form->text('about_cta_link')->setLabel('CTA Link URL');
        echo $form->textarea('about_cta_text1')->setLabel('CTA Text Line 1');
        echo $form->text('about_cta_text2')->setLabel('CTA Text Line 2 (White)');
        echo $form->text('about_cta_text3')->setLabel('CTA Text Line 3 (Red)');

        echo "<hr><h4>Vietnamese Translation</h4>";
        echo $form->textarea('about_cta_text1_vi')->setLabel('CTA Text Line 1 (VI)');
        echo $form->text('about_cta_text2_vi')->setLabel('CTA Text Line 2 (White) (VI)');
        echo $form->text('about_cta_text3_vi')->setLabel('CTA Text Line 3 (Red) (VI)');
    };

    // CTA Single Career
    $cta_career = function() use ($form) {
        echo "<h3>CTA Section (Global for Single Careers)</h3>";
        echo "<p>Set the global CTA content here. This will appear at the bottom of all Career single pages.</p>";
        echo $form->image('career_cta_img')->setLabel('CTA Image');
        // echo $form->text('career_cta_link')->setLabel('CTA Button Link URL');
        echo $form->text('career_cta_btn_text')->setLabel('CTA Button Text');
        echo $form->textarea('career_cta_title')->setLabel('CTA Title');
        echo $form->textarea('career_cta_des')->setLabel('CTA Description');

        echo "<hr><h4>Vietnamese Translation</h4>";
        echo $form->text('career_cta_btn_text_vi')->setLabel('CTA Button Text (VI)');
        echo $form->textarea('career_cta_title_vi')->setLabel('CTA Title (VI)');
        echo $form->textarea('career_cta_des_vi')->setLabel('CTA Description (VI)');

        echo "<hr><h4>Popup Form Text (English)</h4>";
        echo $form->text('career_popup_title_text')->setLabel('Title')->setDefault('Submit Your Resume');
        echo $form->text('career_popup_name')->setLabel('Name Placeholder')->setDefault('Your name');
        echo $form->text('career_popup_email')->setLabel('Email Placeholder')->setDefault('Email address');
        echo $form->text('career_popup_phone')->setLabel('Phone Placeholder')->setDefault('Phone number');
        echo $form->text('career_popup_upload_btn')->setLabel('Upload Button Text')->setDefault('Upload CV');
        echo $form->text('career_popup_upload_note')->setLabel('Upload Note Text')->setDefault('Upload PDF, PPT, PPTX, DOC, DOCX, JPG, PNG files (maximum 5 MB)');
        echo $form->text('career_popup_portfolio')->setLabel('Portfolio Placeholder')->setDefault('Link Portfolio');
        echo $form->text('career_popup_job_placeholder')->setLabel('Job Select Placeholder')->setDefault('Please select the title job');
        echo $form->text('career_popup_intro')->setLabel('Introduction Placeholder')->setDefault('A brief introduction about myself');
        echo $form->text('career_popup_submit')->setLabel('Submit Button Text')->setDefault('SUBMIT JOB APPLICATION');

        echo "<hr><h4>Popup Form Text (Tiếng Việt)</h4>";
        echo $form->text('career_popup_title_text_vi')->setLabel('Title')->setDefault('NôP đƠN ỨNG TUYểN Vị TRí NàY');
        echo $form->text('career_popup_name_vi')->setLabel('Name Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_email_vi')->setLabel('Email Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_phone_vi')->setLabel('Phone Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_upload_btn_vi')->setLabel('Upload Button Text (Tiếng Việt)');
        echo $form->text('career_popup_upload_note_vi')->setLabel('Upload Note Text (Tiếng Việt)');
        echo $form->text('career_popup_portfolio_vi')->setLabel('Portfolio Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_job_placeholder_vi')->setLabel('Job Select Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_intro_vi')->setLabel('Introduction Placeholder (Tiếng Việt)');
        echo $form->text('career_popup_submit_vi')->setLabel('Submit Button Text (Tiếng Việt)');
    };

    // Footer Settings
    $footer_settings = function() use ($form) {
        echo "<h3>Footer Settings</h3>";
        
        echo "<h4>Footer Video Button</h4>";
        echo $form->text('footer_video_button')->setLabel('Video Button Link (.mp4)');
        
        echo "<h4>Email</h4>";
        echo $form->text('footer_email')->setLabel('Email Address');
        
        echo "<h4>VN Office</h4>";
        echo $form->text('footer_vn_title')->setLabel('Title')->setDefault('VN OFFICE');
        echo $form->text('footer_vn_address1')->setLabel('Address Line 1');
        echo $form->text('footer_vn_address2')->setLabel('Address Line 2');
        echo $form->text('footer_vn_tel')->setLabel('Telephone');
        
        echo "<h4>US Office</h4>";
        echo $form->text('footer_us_title')->setLabel('Title')->setDefault('US OFFICE');
        echo $form->text('footer_us_address1')->setLabel('Address Line 1');
        echo $form->text('footer_us_address2')->setLabel('Address Line 2');
        echo $form->text('footer_us_tel')->setLabel('Telephone');
        
        echo "<h4>Social Links</h4>";
        echo $form->text('footer_social_fb')->setLabel('Facebook Link');
        echo $form->text('footer_social_zl')->setLabel('Zalo Link');
        echo $form->text('footer_social_li')->setLabel('LinkedIn Link');
        echo $form->text('footer_social_pi')->setLabel('Pinterest Link');
        
        echo "<h4>Copyright</h4>";
        echo $form->text('footer_text_policy')->setLabel('text policy')->setDefault('Privacy Policy');
        echo $form->text('footer_link_policy')->setLabel('Link policy')->setDefault('#');
        echo $form->text('footer_copyright')->setLabel('Copyright Text')->setDefault('© 2026 Wonom. All Rights Reserved.');

        echo "<h4>Footer Form Text</h4>";
        echo $form->text('footer_form_title')->setLabel('Form Title')->setDefault('GET IN TOUCH WITH US');
        echo $form->text('footer_form_name')->setLabel('Name Placeholder')->setDefault('Your name');
        echo $form->text('footer_form_phone')->setLabel('Phone Placeholder')->setDefault('Phone number');
        echo $form->text('footer_form_message')->setLabel('Message Placeholder')->setDefault('Your Message');
        echo $form->text('footer_form_submit')->setLabel('Submit Button Text')->setDefault('submit');

        echo "<hr><h3>Footer Settings (Tiếng Việt)</h3>";
        
        echo "<h4>VN Office (Tiếng Việt)</h4>";
        echo $form->text('footer_vn_title_vi')->setLabel('Title (Tiếng Việt)');
        echo $form->text('footer_vn_address1_vi')->setLabel('Address Line 1 (Tiếng Việt)');
        echo $form->text('footer_vn_address2_vi')->setLabel('Address Line 2 (Tiếng Việt)');
        
        echo "<h4>US Office (Tiếng Việt)</h4>";
        echo $form->text('footer_us_title_vi')->setLabel('Title (Tiếng Việt)');
        echo $form->text('footer_us_address1_vi')->setLabel('Address Line 1 (Tiếng Việt)');
        echo $form->text('footer_us_address2_vi')->setLabel('Address Line 2 (Tiếng Việt)');
        
        echo "<h4>Copyright (Tiếng Việt)</h4>";
        echo $form->text('footer_text_policy_vi')->setLabel('text policy (Tiếng Việt)');
        echo $form->text('footer_link_policy_vi')->setLabel('Link policy (Tiếng Việt)')->setDefault('#');
        echo $form->text('footer_copyright_vi')->setLabel('Copyright Text (Tiếng Việt)');

        echo "<h4>Footer Form Text (Tiếng Việt)</h4>";
        echo $form->text('footer_form_title_vi')->setLabel('Form Title (Tiếng Việt)');
        echo $form->text('footer_form_name_vi')->setLabel('Name Placeholder (Tiếng Việt)');
        echo $form->text('footer_form_phone_vi')->setLabel('Phone Placeholder (Tiếng Việt)');
        echo $form->text('footer_form_message_vi')->setLabel('Message Placeholder (Tiếng Việt)');
        echo $form->text('footer_form_submit_vi')->setLabel('Submit Button Text (Tiếng Việt)');
    };

    // SMTP Settings
    $smtp_settings = function() use ($form) {
        echo "<h3>SMTP Settings</h3>";
        echo "<p>Cấu hình SMTP để website có thể gửi email.</p>";
        echo $form->text('smtp_host')->setLabel('SMTP Host (vd: smtp.gmail.com)');
        echo $form->text('smtp_port')->setLabel('SMTP Port (vd: 465 hoặc 587)');
        echo $form->text('username')->setLabel('Username (Tài khoản Email SMTP)');
        echo $form->text('smtp_password')->setLabel('Password (Mật khẩu ứng dụng SMTP)')->setAttribute('type', 'password');
        echo $form->checkbox('authentication')->setLabel('SMTP Authentication')->setText('Bật xác thực SMTP');
        echo $form->select('encryption')->setLabel('Encryption (Mã hoá)')->setOptions(['SSL' => 'ssl', 'TLS' => 'tls', 'Không có' => '']);
        echo $form->text('from_email')->setLabel('Gửi từ Email (From Email - vd: no-reply@wonomcomplex.com)');
        
        echo "<hr><h4>Email Nhận Thông Báo</h4>";
        echo $form->text('receive_email')->setLabel('Nhận thông báo ứng tuyển/liên hệ tại Email');
    };

    // reCAPTCHA Settings
    $recaptcha_settings = function() use ($form) {
        echo "<h3>reCAPTCHA v3 Settings</h3>";
        echo "<p>Cấu hình Google reCAPTCHA v3 để bảo mật các form gửi thông tin.</p>";
        echo $form->text('recaptcha_site_key')->setLabel('reCAPTCHA Site Key');
        echo $form->text('recaptcha_secret_key')->setLabel('reCAPTCHA Secret Key');
    };

    // Save
    $save = $form->submit( 'Save Options' );

    // Layout
    tr_tabs()->setSidebar( $save )
        ->addTab( 'Single Case Study', $cta_single )
        ->addTab( 'Single Career', $cta_career )
        ->addTab( 'Footer', $footer_settings )
        ->addTab( 'SMTP', $smtp_settings )
        ->addTab( 'reCAPTCHA', $recaptcha_settings )
        ->render( 'box' );
        
    echo $form->close();
    ?>
</div>
