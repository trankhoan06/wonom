<?php

if (!defined('ABSPATH')) exit;

/**
 * Email-OTP membership flow. SMTP credentials are deliberately not stored
 * here: wp_mail() uses the SMTP settings already available in Theme Options.
 */

function wonom_membership_find($email = '', $phone = '')
{
    $meta_query = array(
        'relation' => 'AND',
        array('key' => '_wonom_form_type', 'value' => 'membership'),
        array('key' => '_wonom_member_status', 'value' => 'active'),
    );
    $identity = array('relation' => 'OR');
    if ($email) $identity[] = array('key' => '_wonom_email', 'value' => strtolower($email));
    if ($phone) $identity[] = array('key' => '_wonom_phone', 'value' => wonom_submission_phone_rate_key($phone));
    if (count($identity) < 2) return 0;
    $meta_query[] = $identity;

    $query = new WP_Query(array(
        'post_type' => 'wonom_submission',
        'post_status' => 'private',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'meta_query' => $meta_query,
    ));
    return $query->posts ? absint($query->posts[0]) : 0;
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
        if (!$post_id || 'active' !== get_post_meta($post_id, '_wonom_member_status', true)) {
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
        'post_type' => 'wonom_submission',
        'post_status' => 'private',
        'post_title' => sprintf('[Thành viên] %s · %s', $data['name'], current_time('d/m/Y H:i')),
    ), true);
    if (is_wp_error($post_id)) wonom_membership_json_error(wonom_home_t('save_failed'), 500);

    $member_number = 'WN-' . str_pad((string) $post_id, 6, '0', STR_PAD_LEFT);
    $member_data = array(
        'form_type' => 'membership',
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

