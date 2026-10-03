<?php

if (!function_exists('add_action')) {
    exit;
}

$form = tr_form()->useJson()->setGroup($this->getName());
?>
<h1><?php esc_html_e('Cấu hình gửi email', 'wonom'); ?></h1>
<?php if (isset($_GET['wonom_smtp_test'])) : ?>
    <?php $smtp_test_success = 'success' === sanitize_key(wp_unslash($_GET['wonom_smtp_test'])); ?>
    <div class="notice <?php echo $smtp_test_success ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p>
            <?php if ($smtp_test_success) : ?>
                <?php esc_html_e('Gửi email kiểm tra thành công. Vui lòng kiểm tra hộp thư nhận.', 'wonom'); ?>
            <?php else : ?>
                <?php esc_html_e('Không thể gửi email kiểm tra.', 'wonom'); ?>
                <?php if (!empty($_GET['wonom_smtp_error'])) echo ' ' . esc_html(sanitize_text_field(wp_unslash($_GET['wonom_smtp_error']))); ?>
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>
<div class="typerocket-container">
    <?php
    echo $form->open(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    $smtp_settings = function () use ($form) {
        echo '<h3>SMTP</h3>';
        echo $form->text('smtp_host')->setLabel('SMTP Host');
        echo $form->text('smtp_port')->setLabel('SMTP Port')->setAttribute('type', 'number');
        echo $form->text('username')->setLabel('SMTP Username');
        echo $form->text('smtp_password')->setLabel('Password')->setAttribute('type', 'password');
        echo $form->checkbox('authentication')->setLabel('SMTP Authentication')->setText('Bật xác thực SMTP');
        echo $form->select('encryption')->setLabel('Encryption')->setOptions(array(
            'SSL' => 'ssl',
            'TLS' => 'tls',
            'Không mã hóa' => '',
        ));
        echo $form->text('from_email')->setLabel('From Email');
        echo $form->text('from_name')->setLabel('From Name');
        echo $form->text('receive_email')->setLabel('Email nhận thông báo')->setHelp('Có thể nhập nhiều email, phân cách bằng dấu phẩy. Nếu để trống sẽ dùng email quản trị WordPress.');
    };

    tr_tabs()
        ->setSidebar($form->submit('Lưu cấu hình'))
        ->addTab('SMTP', $smtp_settings)
        ->render('box');

    echo $form->close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>

    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Kiểm tra SMTP', 'wonom'); ?></h2>
    <p><?php esc_html_e('Hãy lưu cấu hình trước, sau đó gửi email kiểm tra đến địa chỉ nhận thông báo.', 'wonom'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_test_smtp">
        <?php wp_nonce_field('wonom_test_smtp'); ?>
        <?php submit_button(__('Gửi email kiểm tra', 'wonom'), 'secondary', 'submit', false); ?>
    </form>
</div>
