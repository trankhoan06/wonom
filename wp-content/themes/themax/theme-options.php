<?php

if (!function_exists('add_action')) {
    exit;
}

$form = tr_form()->useJson()->setGroup($this->getName());
?>
<h1><?php esc_html_e('Cấu hình thông báo', 'wonom'); ?></h1>
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
<?php if (isset($_GET['wonom_zalo_oauth'])) : ?>
    <?php $zalo_oauth_success = 'success' === sanitize_key(wp_unslash($_GET['wonom_zalo_oauth'])); ?>
    <div class="notice <?php echo $zalo_oauth_success ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p>
            <?php if ($zalo_oauth_success) : ?>
                <?php esc_html_e('Đã kết nối và lưu token của Zalo OA thành công.', 'wonom'); ?>
            <?php else : ?>
                <?php esc_html_e('Không thể kết nối Zalo OA.', 'wonom'); ?>
                <?php if (!empty($_GET['wonom_zalo_error'])) echo ' ' . esc_html(sanitize_text_field(wp_unslash($_GET['wonom_zalo_error']))); ?>
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>
<?php if (isset($_GET['wonom_zalo_test'])) : ?>
    <?php $zalo_test_success = 'success' === sanitize_key(wp_unslash($_GET['wonom_zalo_test'])); ?>
    <div class="notice <?php echo $zalo_test_success ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p>
            <?php if ($zalo_test_success) : ?>
                <?php esc_html_e('Đã gửi tin nhắn kiểm tra vào nhóm Zalo GMF.', 'wonom'); ?>
            <?php else : ?>
                <?php esc_html_e('Không thể gửi tin nhắn kiểm tra qua Zalo OA.', 'wonom'); ?>
                <?php if (!empty($_GET['wonom_zalo_error'])) echo ' ' . esc_html(sanitize_text_field(wp_unslash($_GET['wonom_zalo_error']))); ?>
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>
<?php if (isset($_GET['wonom_zalo_groups']) && 'error' === sanitize_key(wp_unslash($_GET['wonom_zalo_groups']))) : ?>
    <div class="notice notice-error is-dismissible"><p>
        <?php esc_html_e('Không thể lấy danh sách nhóm GMF.', 'wonom'); ?>
        <?php if (!empty($_GET['wonom_zalo_error'])) echo ' ' . esc_html(sanitize_text_field(wp_unslash($_GET['wonom_zalo_error']))); ?>
    </p></div>
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

    $zalo_settings = function () use ($form) {
        echo '<h3>Zalo OA - Nhóm GMF</h3>';
        echo '<p>Thông tin form mới sẽ được gửi từ OA vào nhóm GMF. Ứng dụng Zalo cần được cấp quyền quản lý thông tin nhóm.</p>';
        echo $form->checkbox('zalo_enabled')->setLabel('Thông báo Zalo')->setText('Bật gửi thông báo form vào nhóm GMF');
        echo $form->text('zalo_app_id')->setLabel('Zalo App ID');
        echo $form->text('zalo_app_secret')->setLabel('Zalo App Secret')->setAttribute('type', 'password');
        echo $form->text('zalo_group_id')->setLabel('GMF Group ID');
        echo $form->textarea('zalo_access_token')->setLabel('OA Access Token')->setHelp('Token ban đầu. Hệ thống sẽ tự làm mới khi hết hạn.');
        echo $form->textarea('zalo_refresh_token')->setLabel('OA Refresh Token')->setHelp('Refresh token ban đầu; token mới sẽ được lưu tự động sau mỗi lần làm mới.');
        echo '<p><em>Có thể khai báo các giá trị nhạy cảm bằng hằng WONOM_ZALO_* trong wp-config.php; hằng sẽ được ưu tiên hơn dữ liệu tại đây.</em></p>';
    };

    tr_tabs()
        ->setSidebar($form->submit('Lưu cấu hình'))
        ->addTab('SMTP', $smtp_settings)
        ->addTab('Zalo OA', $zalo_settings)
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

    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Kết nối Zalo OA', 'wonom'); ?></h2>
    <p><?php esc_html_e('Khai báo URL dưới đây làm Official Account Callback URL trên Zalo Developers, lưu App ID và App Secret, sau đó bấm Kết nối.', 'wonom'); ?></p>
    <p><code style="user-select:all"><?php echo esc_html(function_exists('wonom_zalo_oauth_callback_url') ? wonom_zalo_oauth_callback_url() : ''); ?></code></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_zalo_oauth_start">
        <?php wp_nonce_field('wonom_zalo_oauth_start'); ?>
        <?php submit_button(__('Kết nối Zalo OA', 'wonom'), 'primary', 'submit', false); ?>
    </form>

    <h3><?php esc_html_e('Nhóm GMF của OA', 'wonom'); ?></h3>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_zalo_load_groups">
        <?php wp_nonce_field('wonom_zalo_load_groups'); ?>
        <?php submit_button(__('Lấy danh sách nhóm GMF', 'wonom'), 'secondary', 'submit', false); ?>
    </form>
    <?php $zalo_groups = get_transient('wonom_zalo_groups_' . get_current_user_id()); ?>
    <?php if (is_array($zalo_groups)) : ?>
        <?php if ($zalo_groups) : ?>
            <table class="widefat striped" style="max-width:900px;margin-top:12px">
                <thead><tr><th><?php esc_html_e('Tên nhóm', 'wonom'); ?></th><th>Group ID</th><th><?php esc_html_e('Trạng thái', 'wonom'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($zalo_groups as $zalo_group) : ?>
                    <tr>
                        <td><?php echo esc_html(isset($zalo_group['name']) ? $zalo_group['name'] : ''); ?></td>
                        <td><code style="user-select:all"><?php echo esc_html(isset($zalo_group['group_id']) ? $zalo_group['group_id'] : ''); ?></code></td>
                        <td><?php echo esc_html(isset($zalo_group['status']) ? $zalo_group['status'] : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><em><?php esc_html_e('Sao chép Group ID của nhóm có trạng thái enabled vào ô GMF Group ID phía trên rồi lưu cấu hình.', 'wonom'); ?></em></p>
        <?php else : ?>
            <p><?php esc_html_e('OA chưa có nhóm GMF nào hoặc ứng dụng chưa được cấp quyền quản lý nhóm.', 'wonom'); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Kiểm tra Zalo OA', 'wonom'); ?></h2>
    <p><?php esc_html_e('Hãy lưu cấu hình trước, sau đó gửi một tin nhắn kiểm tra vào nhóm GMF.', 'wonom'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_test_zalo">
        <?php wp_nonce_field('wonom_test_zalo'); ?>
        <?php submit_button(__('Gửi tin Zalo kiểm tra', 'wonom'), 'secondary', 'submit', false); ?>
    </form>
</div>
