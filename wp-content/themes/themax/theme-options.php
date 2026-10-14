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
<?php if (isset($_GET['wonom_zalo_saved'])) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Đã lưu cấu hình Zalo OA.', 'wonom'); ?></p>
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
<?php if (isset($_GET['wonom_zalo_group_saved'])) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Đã chọn nhóm Zalo GMF nhận thông báo đặt chỗ.', 'wonom'); ?></p>
    </div>
<?php endif; ?>
<?php if (isset($_GET['wonom_zalo_groups']) && 'error' === sanitize_key(wp_unslash($_GET['wonom_zalo_groups']))) : ?>
    <div class="notice notice-error is-dismissible">
        <p><?php esc_html_e('Không thể tải danh sách nhóm GMF.', 'wonom'); ?> <?php if (!empty($_GET['wonom_zalo_error'])) echo esc_html(sanitize_text_field(wp_unslash($_GET['wonom_zalo_error']))); ?></p>
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
        echo $form->text('receive_email')->setLabel('Email nhận thông báo bổ sung')->setHelp('Mọi yêu cầu luôn được gửi đến wonomcomplex@gmail.com. Có thể nhập thêm nhiều email, phân cách bằng dấu phẩy.');
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

    <?php
    $zalo_options = get_option('tr_theme_options', array());
    $zalo_options = is_array($zalo_options) ? $zalo_options : array();
    $zalo_value = static function ($key) use ($zalo_options) {
        return isset($zalo_options[$key]) ? (string) $zalo_options[$key] : '';
    };
    $zalo_runtime_tokens = function_exists('wonom_zalo_tokens') ? wonom_zalo_tokens() : array();
    $zalo_is_connected = !empty($zalo_runtime_tokens['access_token']) || !empty($zalo_runtime_tokens['refresh_token']);
    ?>
    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Cấu hình Zalo OA - Nhóm GMF', 'wonom'); ?></h2>
    <p><?php esc_html_e('Mỗi yêu cầu đặt chỗ mới sẽ được gửi tự động vào nhóm GMF đã chọn.', 'wonom'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:1000px">
        <input type="hidden" name="action" value="wonom_save_zalo_settings">
        <?php wp_nonce_field('wonom_save_zalo_settings'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Thông báo Zalo', 'wonom'); ?></th>
                <td><label><input type="checkbox" name="zalo_enabled" value="1" <?php checked(!empty($zalo_options['zalo_enabled'])); ?>> <?php esc_html_e('Bật gửi thông báo form đặt chỗ vào nhóm GMF', 'wonom'); ?></label></td>
            </tr>
            <tr>
                <th scope="row"><label for="zalo_app_id">Zalo App ID</label></th>
                <td><input class="regular-text" id="zalo_app_id" name="zalo_app_id" type="text" value="<?php echo esc_attr($zalo_value('zalo_app_id')); ?>" autocomplete="off"></td>
            </tr>
            <tr>
                <th scope="row"><label for="zalo_app_secret">Zalo App Secret</label></th>
                <td>
                    <input class="regular-text" id="zalo_app_secret" name="zalo_app_secret" type="password" value="" autocomplete="new-password" placeholder="<?php echo esc_attr($zalo_value('zalo_app_secret') ? __('Đã lưu — để trống nếu không đổi', 'wonom') : ''); ?>">
                    <p class="description"><?php esc_html_e('Để trống nếu App Secret đã lưu và không thay đổi.', 'wonom'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="zalo_group_id">Nhóm GMF nhận thông báo</label></th>
                <td>
                    <input type="hidden" id="zalo_group_name" name="zalo_group_name" value="<?php echo esc_attr($zalo_value('zalo_group_name')); ?>">
                    <?php if ($zalo_value('zalo_group_name')) : ?>
                        <p><strong><?php echo esc_html($zalo_value('zalo_group_name')); ?></strong></p>
                    <?php endif; ?>
                    <input class="regular-text" id="zalo_group_id" name="zalo_group_id" type="text" value="<?php echo esc_attr($zalo_value('zalo_group_id')); ?>" autocomplete="off">
                    <p class="description"><?php esc_html_e('Có thể nhập Group ID trực tiếp hoặc kết nối OA rồi chọn nhóm từ danh sách bên dưới.', 'wonom'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="zalo_access_token">OA Access Token</label></th>
                <td><textarea class="large-text" rows="3" id="zalo_access_token" name="zalo_access_token" autocomplete="off"></textarea><p class="description"><?php esc_html_e('Không cần nhập nếu dùng nút Kết nối Zalo OA ở dưới.', 'wonom'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="zalo_refresh_token">OA Refresh Token</label></th>
                <td><textarea class="large-text" rows="3" id="zalo_refresh_token" name="zalo_refresh_token" autocomplete="off"></textarea><p class="description"><?php esc_html_e('Không cần nhập nếu dùng nút Kết nối Zalo OA ở dưới.', 'wonom'); ?></p></td>
            </tr>
        </table>
        <?php submit_button(__('Lưu cấu hình GMF', 'wonom'), 'primary', 'submit', false); ?>
    </form>

    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Kết nối Zalo OA', 'wonom'); ?></h2>
    <p>
        <strong><?php esc_html_e('Trạng thái:', 'wonom'); ?></strong>
        <span style="color:<?php echo $zalo_is_connected ? '#008a20' : '#b32d2e'; ?>">
            <?php echo esc_html($zalo_is_connected ? __('Đã kết nối OA', 'wonom') : __('Chưa kết nối OA', 'wonom')); ?>
        </span>
    </p>
    <p><?php esc_html_e('Khai báo URL dưới đây làm Official Account Callback URL trên Zalo Developers, lưu App ID và App Secret, sau đó bấm Kết nối.', 'wonom'); ?></p>
    <p><code style="user-select:all"><?php echo esc_html(function_exists('wonom_zalo_oauth_callback_url') ? wonom_zalo_oauth_callback_url() : ''); ?></code></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_zalo_oauth_start">
        <?php wp_nonce_field('wonom_zalo_oauth_start'); ?>
        <?php submit_button(__('Kết nối Zalo OA', 'wonom'), 'primary', 'submit', false); ?>
    </form>

    <h3><?php esc_html_e('Chọn nhóm GMF', 'wonom'); ?></h3>
    <p><?php esc_html_e('Ứng dụng Zalo cần được cấp quyền quản lý thông tin nhóm. Sau khi kết nối OA, tải danh sách và chọn nhóm nhận thông báo.', 'wonom'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_zalo_load_groups">
        <?php wp_nonce_field('wonom_zalo_load_groups'); ?>
        <?php submit_button(__('Tải danh sách nhóm GMF', 'wonom'), 'secondary', 'submit', false); ?>
    </form>
    <?php $zalo_groups = get_transient('wonom_zalo_groups_' . get_current_user_id()); ?>
    <?php if (is_array($zalo_groups) && $zalo_groups) : ?>
        <table class="widefat striped" style="max-width:900px;margin-top:12px">
            <thead><tr><th><?php esc_html_e('Tên nhóm', 'wonom'); ?></th><th>Group ID</th><th><?php esc_html_e('Thành viên', 'wonom'); ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($zalo_groups as $zalo_group) : ?>
                <?php
                $zalo_group_id = isset($zalo_group['group_id']) ? (string) $zalo_group['group_id'] : '';
                $zalo_group_name = isset($zalo_group['group_name']) ? (string) $zalo_group['group_name'] : (isset($zalo_group['name']) ? (string) $zalo_group['name'] : '');
                $zalo_group_members = isset($zalo_group['total_member']) ? (string) $zalo_group['total_member'] : (isset($zalo_group['member_count']) ? (string) $zalo_group['member_count'] : '');
                if ('' === $zalo_group_id) continue;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($zalo_group_name ?: __('Nhóm GMF', 'wonom')); ?></strong></td>
                    <td><code style="user-select:all"><?php echo esc_html($zalo_group_id); ?></code></td>
                    <td><?php echo '' !== $zalo_group_members ? esc_html($zalo_group_members) : '—'; ?></td>
                    <td>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <input type="hidden" name="action" value="wonom_zalo_select_group">
                            <input type="hidden" name="zalo_group_id" value="<?php echo esc_attr($zalo_group_id); ?>">
                            <input type="hidden" name="zalo_group_name" value="<?php echo esc_attr($zalo_group_name); ?>">
                            <?php wp_nonce_field('wonom_zalo_select_group'); ?>
                            <?php submit_button($zalo_group_id === $zalo_value('zalo_group_id') ? __('Đang chọn', 'wonom') : __('Chọn nhóm này', 'wonom'), 'small', 'submit', false, array('disabled' => $zalo_group_id === $zalo_value('zalo_group_id'))); ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (false !== $zalo_groups) : ?>
        <p><?php esc_html_e('OA chưa có nhóm GMF nào hoặc ứng dụng chưa được cấp quyền quản lý thông tin nhóm.', 'wonom'); ?></p>
    <?php endif; ?>

    <hr style="margin:24px 0">
    <h2><?php esc_html_e('Kiểm tra Zalo OA', 'wonom'); ?></h2>
    <p><?php esc_html_e('Hãy chọn nhóm GMF trước, sau đó gửi một tin nhắn kiểm tra vào nhóm.', 'wonom'); ?></p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wonom_test_zalo">
        <?php wp_nonce_field('wonom_test_zalo'); ?>
        <?php submit_button(__('Gửi thử vào nhóm GMF', 'wonom'), 'secondary', 'submit', false); ?>
    </form>
</div>
