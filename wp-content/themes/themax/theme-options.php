<?php

if (!function_exists('add_action')) {
    exit;
}

$form = tr_form()->useJson()->setGroup($this->getName());
?>
<h1><?php esc_html_e('Cấu hình gửi email', 'wonom'); ?></h1>
<div class="typerocket-container">
    <?php
    echo $form->open(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    $smtp_settings = function () use ($form) {
        echo '<h3>SMTP</h3>';
        echo $form->text('smtp_host')->setLabel('SMTP Host');
        echo $form->text('smtp_port')->setLabel('SMTP Port');
        echo $form->text('username')->setLabel('Username');
        echo $form->text('smtp_password')->setLabel('Password')->setAttribute('type', 'password');
        echo $form->checkbox('authentication')->setLabel('SMTP Authentication')->setText('Bật xác thực SMTP');
        echo $form->select('encryption')->setLabel('Encryption')->setOptions(array(
            'SSL' => 'ssl',
            'TLS' => 'tls',
            'Không mã hóa' => '',
        ));
        echo $form->text('from_email')->setLabel('From Email');
        echo $form->text('receive_email')->setLabel('Email nhận thông báo');
    };

    tr_tabs()
        ->setSidebar($form->submit('Lưu cấu hình'))
        ->addTab('SMTP', $smtp_settings)
        ->render('box');

    echo $form->close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>
</div>
