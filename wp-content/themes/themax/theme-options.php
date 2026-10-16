<?php

if (!function_exists('add_action')) {
    exit;
}

$theme_options = get_option('tr_theme_options', array());
$theme_options = is_array($theme_options) ? $theme_options : array();
$smtp_value = static function ($key) use ($theme_options) {
    return isset($theme_options[$key]) ? (string) $theme_options[$key] : '';
};
?>
<h1><?php esc_html_e('Cấu hình thông báo', 'wonom'); ?></h1>
<?php if (isset($_GET['wonom_smtp_saved'])) : ?>
    <?php $smtp_saved = 'success' === sanitize_key(wp_unslash($_GET['wonom_smtp_saved'])); ?>
    <div class="notice <?php echo $smtp_saved ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p><?php echo esc_html($smtp_saved ? __('Đã lưu cấu hình SMTP.', 'wonom') : __('Không thể lưu cấu hình SMTP. Vui lòng kiểm tra lại dữ liệu.', 'wonom')); ?></p>
    </div>
<?php endif; ?>
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
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:1000px">
        <input type="hidden" name="action" value="wonom_save_smtp_settings">
        <?php wp_nonce_field('wonom_save_smtp_settings'); ?>
        <h2>SMTP</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="smtp_host">SMTP Host</label></th>
                <td><input class="regular-text" id="smtp_host" name="smtp_host" type="text" value="<?php echo esc_attr($smtp_value('smtp_host')); ?>" autocomplete="off" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_port">SMTP Port</label></th>
                <td><input class="small-text" id="smtp_port" name="smtp_port" type="number" min="1" max="65535" value="<?php echo esc_attr($smtp_value('smtp_port') ?: '587'); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_username">SMTP Username</label></th>
                <td><input class="regular-text" id="smtp_username" name="username" type="text" value="<?php echo esc_attr($smtp_value('username')); ?>" autocomplete="username" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_password">Password</label></th>
                <td>
                    <input class="regular-text" id="smtp_password" name="smtp_password" type="password" value="" autocomplete="new-password" placeholder="<?php echo esc_attr($smtp_value('smtp_password') ? __('Đã lưu — để trống nếu không đổi', 'wonom') : ''); ?>">
                    <p class="description"><?php esc_html_e('Với Gmail, dùng App Password 16 ký tự; không dùng mật khẩu đăng nhập Gmail.', 'wonom'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">SMTP Authentication</th>
                <td><label><input name="authentication" type="checkbox" value="1" <?php checked(!empty($theme_options['authentication'])); ?>> <?php esc_html_e('Bật xác thực SMTP', 'wonom'); ?></label></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_encryption">Encryption</label></th>
                <td>
                    <select id="smtp_encryption" name="encryption">
                        <option value="tls" <?php selected($smtp_value('encryption'), 'tls'); ?>>TLS</option>
                        <option value="ssl" <?php selected($smtp_value('encryption'), 'ssl'); ?>>SSL</option>
                        <option value="" <?php selected($smtp_value('encryption'), ''); ?>>Không mã hóa</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_from_email">From Email</label></th>
                <td><input class="regular-text" id="smtp_from_email" name="from_email" type="email" value="<?php echo esc_attr($smtp_value('from_email')); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_from_name">From Name</label></th>
                <td><input class="regular-text" id="smtp_from_name" name="from_name" type="text" value="<?php echo esc_attr($smtp_value('from_name')); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="smtp_receive_email">Email nhận thông báo bổ sung</label></th>
                <td>
                    <input class="regular-text" id="smtp_receive_email" name="receive_email" type="text" value="<?php echo esc_attr($smtp_value('receive_email')); ?>">
                    <p class="description"><?php esc_html_e('Mọi yêu cầu luôn được gửi đến wonomcomplex@gmail.com. Có thể nhập thêm nhiều email, phân cách bằng dấu phẩy.', 'wonom'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Lưu cấu hình', 'wonom')); ?>
    </form>

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
