<?php

if (!defined('ABSPATH')) exit;

/**
 * Email-OTP membership flow. SMTP credentials are deliberately not stored
 * here: wp_mail() uses the SMTP settings already available in Theme Options.
 */

function wonom_register_member_post_type()
{
    register_post_type('wonom_member', array(
        'labels' => array(
            'name' => 'Thành viên WONOM',
            'singular_name' => 'Thành viên',
            'menu_name' => 'Thành viên WONOM',
            'all_items' => 'Danh sách thành viên',
            'add_new' => 'Thêm thành viên',
            'add_new_item' => 'Thêm thành viên mới',
            'edit_item' => 'Sửa thành viên',
            'new_item' => 'Thành viên mới',
            'search_items' => 'Tìm thành viên',
            'not_found' => 'Chưa có thành viên nào',
            'not_found_in_trash' => 'Thùng rác không có thành viên',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'supports' => array(),
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-groups',
        'menu_position' => 24,
    ));
}
add_action('init', 'wonom_register_member_post_type');

function wonom_membership_migrate_legacy_records()
{
    if ('1' === get_option('wonom_member_migration_v1')) return;

    $legacy_ids = get_posts(array(
        'post_type' => 'wonom_submission',
        'post_status' => 'private',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => array(
            array('key' => '_wonom_form_type', 'value' => 'membership'),
            array('key' => '_wonom_member_status', 'value' => 'active'),
        ),
    ));

    foreach ($legacy_ids as $legacy_id) {
        $email = (string) get_post_meta($legacy_id, '_wonom_email', true);
        $phone = (string) get_post_meta($legacy_id, '_wonom_phone', true);
        if (!$email || wonom_membership_query_id('wonom_member', 'publish', $email, $phone, false)) continue;

        $name = (string) get_post_meta($legacy_id, '_wonom_name', true);
        $member_id = wp_insert_post(array(
            'post_type' => 'wonom_member',
            'post_status' => 'publish',
            'post_title' => $name ?: ('Thành viên #' . $legacy_id),
            'post_date' => get_post_field('post_date', $legacy_id),
        ), true);
        if (is_wp_error($member_id)) continue;

        foreach (array('name', 'phone', 'email', 'consent', 'member_number', 'member_status', 'verified_at', 'source_url') as $key) {
            $value = get_post_meta($legacy_id, '_wonom_' . $key, true);
            if ('' !== (string) $value) update_post_meta($member_id, '_wonom_' . $key, $value);
        }
        update_post_meta($member_id, '_wonom_form_type', 'membership');
        update_post_meta($member_id, '_wonom_form_title', 'ĐĂNG KÝ THÀNH VIÊN');
        update_post_meta($member_id, '_wonom_status', 'completed');
        update_post_meta($member_id, '_wonom_legacy_submission_id', $legacy_id);
    }

    update_option('wonom_member_migration_v1', '1', false);
}
add_action('admin_init', 'wonom_membership_migrate_legacy_records');

function wonom_membership_query_id($post_type, $post_status, $email, $phone, $require_active = true)
{
    $meta_query = array('relation' => 'AND');
    if ($require_active) $meta_query[] = array('key' => '_wonom_member_status', 'value' => 'active');
    if ('wonom_submission' === $post_type) {
        $meta_query[] = array('key' => '_wonom_form_type', 'value' => 'membership');
    }

    $identity = array('relation' => 'OR');
    if ($email) $identity[] = array('key' => '_wonom_email', 'value' => strtolower($email));
    if ($phone) $identity[] = array('key' => '_wonom_phone', 'value' => wonom_submission_phone_rate_key($phone));
    if (count($identity) < 2) return 0;
    $meta_query[] = $identity;

    $query = new WP_Query(array(
        'post_type' => $post_type,
        'post_status' => $post_status,
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => $meta_query,
    ));
    return $query->posts ? absint($query->posts[0]) : 0;
}

function wonom_membership_find($email = '', $phone = '')
{
    $member_id = wonom_membership_query_id('wonom_member', 'publish', $email, $phone);
    if ($member_id) return $member_id;

    // Keep cards created before the dedicated member area was introduced.
    return wonom_membership_query_id('wonom_submission', 'private', $email, $phone);
}

function wonom_membership_mask_email($email)
{
    $parts = explode('@', (string) $email, 2);
    if (2 !== count($parts)) return '';
    $name = $parts[0];
    $visible = substr($name, 0, min(4, strlen($name)));
    return $visible . str_repeat('*', max(3, strlen($name) - strlen($visible))) . '@' . $parts[1];
}

function wonom_membership_pending_key($token)
{
    return 'wonom_member_otp_' . hash_hmac('sha256', (string) $token, wp_salt('nonce'));
}

function wonom_membership_send_code($email, $code)
{
    $subject = '[WONOM] Mã xác nhận thành viên';
    $message = "Xin chào,\n\nMã xác nhận WONOM của bạn là: " . $code
        . "\n\nMã có hiệu lực trong 10 phút. Không chia sẻ mã này với bất kỳ ai."
        . "\n\nWONOM Culture Complex";
    return wp_mail($email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));
}

function wonom_membership_json_error($message, $status = 400, $fields = array())
{
    $payload = array('message' => $message);
    if ($fields) $payload['fields'] = $fields;
    wp_send_json_error($payload, $status);
}

function wonom_membership_read_pending($token)
{
    if (!$token || strlen($token) > 128) return false;
    return get_transient(wonom_membership_pending_key($token));
}

function wonom_membership_begin()
{
    $mode = sanitize_key(wonom_submission_post_text('mode', 20));
    $email = strtolower(sanitize_email(wonom_submission_post_text('email', 190)));
    $errors = array();
    if (!is_email($email)) $errors['email'] = 'Vui lòng nhập địa chỉ email hợp lệ.';

    $data = array('mode' => $mode, 'email' => $email);
    if ('register' === $mode) {
        $name = wonom_submission_post_text('name', 80);
        $phone = wonom_submission_post_text('phone', 30);
        $name_length = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($name_length < 2 || $name_length > 80 || preg_match('/[0-9]/', $name)) $errors['name'] = wonom_home_t('invalid_name');
        if (!wonom_submission_valid_phone($phone)) $errors['phone'] = wonom_home_t('invalid_phone');
        if (!in_array(wonom_submission_post_text('consent', 10), array('1', 'yes', 'on'), true)) $errors['consent'] = wonom_home_t('required_consent');
        $phone = wonom_submission_phone_rate_key($phone);
        if (!$errors && wonom_membership_find($email, $phone)) {
            wonom_membership_json_error('Email hoặc số điện thoại này đã là thành viên. Vui lòng tra cứu thẻ.', 409);
        }
        $data['name'] = $name;
        $data['phone'] = $phone;
    } elseif ('lookup' === $mode) {
        if (!$errors) {
            $member_id = wonom_membership_find($email);
            if (!$member_id) wonom_membership_json_error('Không tìm thấy thẻ thành viên với email này.', 404, array('email' => 'Email chưa được đăng ký thành viên.'));
            $data['member_id'] = $member_id;
        }
    } else {
        wonom_membership_json_error('Yêu cầu không hợp lệ.');
    }

    if ($errors) wonom_membership_json_error(wonom_home_t('check_information'), 422, $errors);
    if (wonom_submission_rate_limited('member_email_hourly', $email, 5, HOUR_IN_SECONDS)) {
        wonom_submission_rate_limit_response(HOUR_IN_SECONDS);
    }

    $token = wp_generate_password(48, false, false);
    $code = (string) random_int(100000, 999999);
    $data['code_hash'] = wp_hash_password($code);
    $data['expires_at'] = time() + 10 * MINUTE_IN_SECONDS;
    $data['resend_at'] = time() + MINUTE_IN_SECONDS;
    $data['attempts'] = 0;

    set_transient(wonom_membership_pending_key($token), $data, 10 * MINUTE_IN_SECONDS);
    if (!wonom_membership_send_code($email, $code)) {
        delete_transient(wonom_membership_pending_key($token));
        wonom_membership_json_error('Chưa thể gửi email xác nhận. Vui lòng kiểm tra cấu hình SMTP rồi thử lại.', 503);
    }

    wp_send_json_success(array(
        'token' => $token,
        'mode' => $mode,
        'masked_email' => wonom_membership_mask_email($email),
        'resend_after' => 60,
    ));
}

function wonom_membership_resend()
{
    $token = wonom_submission_post_text('token', 128);
    $data = wonom_membership_read_pending($token);
    if (!$data || empty($data['email'])) wonom_membership_json_error('Phiên xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.', 410);
    if (!empty($data['resend_at']) && time() < $data['resend_at']) {
        wonom_membership_json_error('Vui lòng chờ trước khi gửi lại mã.', 429);
    }

    $code = (string) random_int(100000, 999999);
    $data['code_hash'] = wp_hash_password($code);
    $data['expires_at'] = time() + 10 * MINUTE_IN_SECONDS;
    $data['resend_at'] = time() + MINUTE_IN_SECONDS;
    $data['attempts'] = 0;
    set_transient(wonom_membership_pending_key($token), $data, 10 * MINUTE_IN_SECONDS);
    if (!wonom_membership_send_code($data['email'], $code)) {
        wonom_membership_json_error('Chưa thể gửi lại email. Vui lòng kiểm tra cấu hình SMTP.', 503);
    }
    wp_send_json_success(array('resend_after' => 60, 'message' => 'Mã xác nhận mới đã được gửi.'));
}

function wonom_membership_card_payload($post_id)
{
    return array(
        'name' => (string) get_post_meta($post_id, '_wonom_name', true),
        'number' => (string) get_post_meta($post_id, '_wonom_member_number', true),
    );
}

function wonom_membership_verify()
{
    $token = wonom_submission_post_text('token', 128);
    $code = preg_replace('/\D/', '', wonom_submission_post_text('code', 12));
    $key = wonom_membership_pending_key($token);
    $data = wonom_membership_read_pending($token);
    if (!$data || empty($data['code_hash']) || time() > (int) $data['expires_at']) {
        delete_transient($key);
        wonom_membership_json_error('Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.', 410);
    }
    if (6 !== strlen($code) || !wp_check_password($code, $data['code_hash'])) {
        $data['attempts'] = isset($data['attempts']) ? ((int) $data['attempts'] + 1) : 1;
        if ($data['attempts'] >= 5) {
            delete_transient($key);
            wonom_membership_json_error('Bạn đã nhập sai quá nhiều lần. Vui lòng yêu cầu mã mới.', 429);
        }
        set_transient($key, $data, max(MINUTE_IN_SECONDS, (int) $data['expires_at'] - time()));
        wonom_membership_json_error('Mã xác nhận không chính xác.', 422);
    }

    if ('lookup' === $data['mode']) {
        $post_id = absint($data['member_id']);
        if (
            !$post_id
            || !in_array(get_post_type($post_id), array('wonom_member', 'wonom_submission'), true)
            || 'active' !== get_post_meta($post_id, '_wonom_member_status', true)
        ) {
            delete_transient($key);
            wonom_membership_json_error('Không tìm thấy thẻ thành viên.', 404);
        }
        delete_transient($key);
        wp_send_json_success(array('card' => wonom_membership_card_payload($post_id), 'mode' => 'lookup'));
    }

    $existing_id = wonom_membership_find($data['email'], $data['phone']);
    if ($existing_id) {
        delete_transient($key);
        wp_send_json_success(array('card' => wonom_membership_card_payload($existing_id), 'mode' => 'register'));
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'wonom_member',
        'post_status' => 'publish',
        'post_title' => $data['name'],
    ), true);
    if (is_wp_error($post_id)) wonom_membership_json_error(wonom_home_t('save_failed'), 500);

    $member_number = 'WN-' . str_pad((string) $post_id, 6, '0', STR_PAD_LEFT);
    $member_data = array(
        'form_type' => 'membership',
        'form_title' => 'ĐĂNG KÝ THÀNH VIÊN',
        'name' => $data['name'],
        'phone' => $data['phone'],
        'email' => $data['email'],
        'consent' => 'yes',
        'member_number' => $member_number,
        'member_status' => 'active',
        'verified_at' => current_time('mysql'),
        'status' => 'completed',
        'source_url' => esc_url_raw(wp_get_referer() ?: ''),
    );
    foreach ($member_data as $meta_key => $value) update_post_meta($post_id, '_wonom_' . $meta_key, $value);
    delete_transient($key);

    if (function_exists('wonom_zalo_queue_submission')) {
        wonom_zalo_queue_submission($post_id);
    }

    wp_mail(
        wonom_submission_notification_emails(),
        sprintf('[WONOM] Thành viên mới: %s', $data['name']),
        "Mã thành viên: {$member_number}\nHọ tên: {$data['name']}\nĐiện thoại: {$data['phone']}\nEmail: {$data['email']}",
        array('Content-Type: text/plain; charset=UTF-8')
    );

    wp_send_json_success(array('card' => wonom_membership_card_payload($post_id), 'mode' => 'register'));
}

function wonom_handle_membership_ajax()
{
    if (!check_ajax_referer('wonom_submit_form', 'nonce', false)) wonom_membership_json_error(wonom_home_t('expired'), 403);
    if (!wonom_submission_is_same_origin()) wonom_membership_json_error(wonom_home_t('invalid_form'), 403);
    $operation = sanitize_key(wonom_submission_post_text('operation', 20));
    if ('begin' === $operation) wonom_membership_begin();
    if ('resend' === $operation) wonom_membership_resend();
    if ('verify' === $operation) wonom_membership_verify();
    wonom_membership_json_error('Yêu cầu không hợp lệ.');
}
add_action('wp_ajax_wonom_membership', 'wonom_handle_membership_ajax');
add_action('wp_ajax_nopriv_wonom_membership', 'wonom_handle_membership_ajax');

/**
 * Member management in wp-admin.
 */
function wonom_member_add_meta_box()
{
    add_meta_box(
        'wonom-member-details',
        'Thông tin thành viên',
        'wonom_member_render_meta_box',
        'wonom_member',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_wonom_member', 'wonom_member_add_meta_box');

function wonom_member_render_meta_box($post)
{
    $name = (string) get_post_meta($post->ID, '_wonom_name', true);
    $phone = (string) get_post_meta($post->ID, '_wonom_phone', true);
    $email = (string) get_post_meta($post->ID, '_wonom_email', true);
    $number = (string) get_post_meta($post->ID, '_wonom_member_number', true);
    $status = (string) get_post_meta($post->ID, '_wonom_member_status', true) ?: 'active';
    $verified_at = (string) get_post_meta($post->ID, '_wonom_verified_at', true);
    wp_nonce_field('wonom_save_member', 'wonom_member_nonce');
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="wonom_member_name">Họ và tên</label></th>
            <td><input class="regular-text" id="wonom_member_name" name="wonom_member_name" type="text" maxlength="80" value="<?php echo esc_attr($name); ?>" required></td>
        </tr>
        <tr>
            <th scope="row"><label for="wonom_member_phone">Số điện thoại</label></th>
            <td><input class="regular-text" id="wonom_member_phone" name="wonom_member_phone" type="tel" maxlength="20" value="<?php echo esc_attr($phone); ?>" required></td>
        </tr>
        <tr>
            <th scope="row"><label for="wonom_member_email">Email</label></th>
            <td><input class="regular-text" id="wonom_member_email" name="wonom_member_email" type="email" maxlength="190" value="<?php echo esc_attr($email); ?>" required></td>
        </tr>
        <tr>
            <th scope="row"><label for="wonom_member_number">Mã thành viên</label></th>
            <td>
                <input class="regular-text" id="wonom_member_number" name="wonom_member_number" type="text" maxlength="30" value="<?php echo esc_attr($number); ?>" placeholder="Tự động tạo khi lưu">
                <p class="description">Có thể nhập mã riêng hoặc để trống để hệ thống tạo dạng WN-000123.</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wonom_member_status">Trạng thái</label></th>
            <td>
                <select id="wonom_member_status" name="wonom_member_status">
                    <option value="active" <?php selected($status, 'active'); ?>>Đang hoạt động</option>
                    <option value="inactive" <?php selected($status, 'inactive'); ?>>Tạm khóa</option>
                </select>
            </td>
        </tr>
        <?php if ($verified_at) : ?>
            <tr><th scope="row">Xác nhận lúc</th><td><?php echo esc_html($verified_at); ?></td></tr>
        <?php endif; ?>
    </table>
    <?php
}

function wonom_member_duplicate_id($post_id, $email, $phone, $number)
{
    $identity = array('relation' => 'OR');
    if ($email) $identity[] = array('key' => '_wonom_email', 'value' => strtolower($email));
    if ($phone) $identity[] = array('key' => '_wonom_phone', 'value' => $phone);
    if ($number) $identity[] = array('key' => '_wonom_member_number', 'value' => $number);
    if (count($identity) < 2) return 0;

    $query = new WP_Query(array(
        'post_type' => 'wonom_member',
        'post_status' => array('publish', 'draft', 'pending', 'private'),
        'post__not_in' => array(absint($post_id)),
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => $identity,
    ));
    return $query->posts ? absint($query->posts[0]) : 0;
}

function wonom_member_set_notice($message, $type = 'error')
{
    set_transient('wonom_member_notice_' . get_current_user_id(), array(
        'message' => (string) $message,
        'type' => 'success' === $type ? 'success' : 'error',
    ), MINUTE_IN_SECONDS);
}

function wonom_member_save($post_id, $post)
{
    if (
        empty($_POST['wonom_member_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wonom_member_nonce'])), 'wonom_save_member')
        || !current_user_can('edit_post', $post_id)
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_revision($post_id)
    ) {
        return;
    }

    $name = isset($_POST['wonom_member_name']) ? sanitize_text_field(wp_unslash($_POST['wonom_member_name'])) : '';
    $phone = isset($_POST['wonom_member_phone']) ? wonom_submission_phone_rate_key(wp_unslash($_POST['wonom_member_phone'])) : '';
    $email = isset($_POST['wonom_member_email']) ? strtolower(sanitize_email(wp_unslash($_POST['wonom_member_email']))) : '';
    $number = isset($_POST['wonom_member_number']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['wonom_member_number']))) : '';
    $status = isset($_POST['wonom_member_status']) ? sanitize_key(wp_unslash($_POST['wonom_member_status'])) : 'active';
    $name_length = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);

    if ($name_length < 2 || $name_length > 80 || !wonom_submission_valid_phone($phone) || !is_email($email)) {
        wonom_member_set_notice('Không thể lưu: vui lòng kiểm tra lại họ tên, số điện thoại và email.');
        return;
    }
    if (!in_array($status, array('active', 'inactive'), true)) $status = 'active';
    if (!$number) $number = 'WN-' . str_pad((string) $post_id, 6, '0', STR_PAD_LEFT);

    if (wonom_member_duplicate_id($post_id, $email, $phone, $number)) {
        wonom_member_set_notice('Không thể lưu: email, số điện thoại hoặc mã thành viên đã được sử dụng.');
        return;
    }

    update_post_meta($post_id, '_wonom_name', $name);
    update_post_meta($post_id, '_wonom_phone', $phone);
    update_post_meta($post_id, '_wonom_email', $email);
    update_post_meta($post_id, '_wonom_member_number', $number);
    update_post_meta($post_id, '_wonom_member_status', $status);
    update_post_meta($post_id, '_wonom_form_type', 'membership');
    update_post_meta($post_id, '_wonom_form_title', 'ĐĂNG KÝ THÀNH VIÊN');
    update_post_meta($post_id, '_wonom_status', 'completed');
    if (!get_post_meta($post_id, '_wonom_verified_at', true)) {
        update_post_meta($post_id, '_wonom_verified_at', current_time('mysql'));
    }

    if ($post->post_title !== $name) {
        remove_action('save_post_wonom_member', 'wonom_member_save', 10);
        wp_update_post(array('ID' => $post_id, 'post_title' => $name));
        add_action('save_post_wonom_member', 'wonom_member_save', 10, 2);
    }
    wonom_member_set_notice('Đã lưu thông tin thành viên.', 'success');
}
add_action('save_post_wonom_member', 'wonom_member_save', 10, 2);

function wonom_member_admin_notice()
{
    $screen = get_current_screen();
    if (!$screen || 'wonom_member' !== $screen->post_type) return;
    $key = 'wonom_member_notice_' . get_current_user_id();
    $notice = get_transient($key);
    if (!$notice || !is_array($notice)) return;
    delete_transient($key);
    echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
}
add_action('admin_notices', 'wonom_member_admin_notice');

function wonom_member_columns($columns)
{
    return array(
        'cb' => isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox">',
        'title' => 'Họ và tên',
        'wonom_member_number' => 'Mã thành viên',
        'wonom_member_email' => 'Email',
        'wonom_member_phone' => 'Số điện thoại',
        'wonom_member_status' => 'Trạng thái',
        'date' => 'Ngày tạo',
    );
}
add_filter('manage_wonom_member_posts_columns', 'wonom_member_columns');

function wonom_member_column($column, $post_id)
{
    if ('wonom_member_number' === $column) echo '<strong>' . esc_html(get_post_meta($post_id, '_wonom_member_number', true)) . '</strong>';
    if ('wonom_member_email' === $column) echo esc_html(get_post_meta($post_id, '_wonom_email', true));
    if ('wonom_member_phone' === $column) echo esc_html(get_post_meta($post_id, '_wonom_phone', true));
    if ('wonom_member_status' === $column) {
        $active = 'active' === get_post_meta($post_id, '_wonom_member_status', true);
        echo '<span class="wonom-member-status ' . ($active ? 'is-active' : 'is-inactive') . '">'
            . esc_html($active ? 'Đang hoạt động' : 'Tạm khóa') . '</span>';
    }
}
add_action('manage_wonom_member_posts_custom_column', 'wonom_member_column', 10, 2);

function wonom_member_admin_search($search, $query)
{
    global $wpdb;
    if (
        !is_admin()
        || !$query->is_main_query()
        || 'wonom_member' !== $query->get('post_type')
        || !$query->get('s')
    ) {
        return $search;
    }
    $like = '%' . $wpdb->esc_like((string) $query->get('s')) . '%';
    return $wpdb->prepare(
        " AND ({$wpdb->posts}.post_title LIKE %s OR EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} wm_search
            WHERE wm_search.post_id = {$wpdb->posts}.ID
            AND wm_search.meta_key IN ('_wonom_email','_wonom_phone','_wonom_member_number')
            AND wm_search.meta_value LIKE %s
        )) ",
        $like,
        $like
    );
}
add_filter('posts_search', 'wonom_member_admin_search', 10, 2);

function wonom_member_admin_styles()
{
    $screen = get_current_screen();
    if (!$screen || 'wonom_member' !== $screen->post_type) return;
    echo '<style>
        .wonom-member-status{display:inline-block;padding:4px 9px;border-radius:999px;font-weight:600}
        .wonom-member-status.is-active{background:#e7f7ed;color:#176b3a}
        .wonom-member-status.is-inactive{background:#fdebec;color:#a51d2d}
        #minor-publishing-actions{display:none}
    </style>';
}
add_action('admin_head', 'wonom_member_admin_styles');
