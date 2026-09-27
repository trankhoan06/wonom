<?php

function wonom_register_submission_post_type()
{
    register_post_type('wonom_submission', array(
        'labels' => array(
            'name' => 'Yêu cầu từ website',
            'singular_name' => 'Yêu cầu',
            'menu_name' => 'Yêu cầu website',
            'all_items' => 'Tất cả yêu cầu',
            'view_item' => 'Xem yêu cầu',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'supports' => array('title'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-email-alt',
        'menu_position' => 25,
    ));
}
add_action('init', 'wonom_register_submission_post_type');

function wonom_submission_normalize_phone($phone)
{
    return preg_replace('/[^0-9+]/', '', (string) $phone);
}

function wonom_submission_valid_phone($phone)
{
    return (bool) preg_match('/^(?:\+?84|0)(?:3|5|7|8|9)[0-9]{8}$/', wonom_submission_normalize_phone($phone));
}

function wonom_submission_valid_date($date)
{
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', (string) $date, $matches)) {
        return false;
    }
    if (!checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])) {
        return false;
    }
    $submitted = DateTimeImmutable::createFromFormat('!d/m/Y', $date, wp_timezone());
    $today = new DateTimeImmutable('today', wp_timezone());
    return $submitted && $submitted >= $today;
}

function wonom_submission_valid_time($time)
{
    return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?:\s*-\s*(?:[01]\d|2[0-3]):[0-5]\d)?$/', trim((string) $time));
}

function wonom_handle_home_form_submission()
{
    if (!check_ajax_referer('wonom_submit_form', 'nonce', false)) {
        wp_send_json_error(array('message' => 'Phiên gửi yêu cầu đã hết hạn. Vui lòng tải lại trang.'), 403);
    }

    $honeypot = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
    if ('' !== $honeypot) {
        wp_send_json_success(array('message' => 'Yêu cầu của bạn đã được ghi nhận.'));
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $rate_key = 'wonom_submit_' . md5($ip);
    $rate_count = (int) get_transient($rate_key);
    if ($rate_count >= 8) {
        wp_send_json_error(array('message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.'), 429);
    }

    $form_type = isset($_POST['form_type']) ? sanitize_key(wp_unslash($_POST['form_type'])) : '';
    if (!in_array($form_type, array('booking', 'tour', 'membership'), true)) {
        wp_send_json_error(array('message' => 'Loại biểu mẫu không hợp lệ.'), 400);
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $errors = array();
    $name_length = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
    if ($name_length < 2 || $name_length > 80 || preg_match('/[0-9]/', $name)) {
        $errors['name'] = 'Vui lòng nhập họ tên hợp lệ.';
    }
    if (!wonom_submission_valid_phone($phone)) {
        $errors['phone'] = 'Vui lòng nhập đúng số điện thoại Việt Nam.';
    }

    $data = array(
        'form_type' => $form_type,
        'name' => $name,
        'phone' => wonom_submission_normalize_phone($phone),
    );

    if ('membership' === $form_type) {
        $consent = !empty($_POST['consent']);
        if (!$consent) {
            $errors['consent'] = 'Bạn cần đồng ý điều khoản để đăng ký.';
        }
        $data['consent'] = $consent ? 'yes' : 'no';
    } else {
        $date = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
        $time = isset($_POST['time']) ? sanitize_text_field(wp_unslash($_POST['time'])) : '';
        $guests = isset($_POST['guests']) ? sanitize_text_field(wp_unslash($_POST['guests'])) : '';
        if (!wonom_submission_valid_date($date)) $errors['date'] = 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.';
        if (!wonom_submission_valid_time($time)) $errors['time'] = 'Vui lòng nhập thời gian theo định dạng HH:mm.';
        if (!preg_match('/^(?:[1-9][0-9]*|5\+)$/', $guests)) $errors['guests'] = 'Vui lòng chọn số người.';
        $data = array_merge($data, array(
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
        ));
    }

    if ('booking' === $form_type) {
        $booking_type = isset($_POST['booking_type']) ? sanitize_key(wp_unslash($_POST['booking_type'])) : 'general';
        $floor = isset($_POST['floor']) ? sanitize_text_field(wp_unslash($_POST['floor'])) : '';
        if ('general' === $booking_type && '' === $floor) $errors['floor'] = 'Vui lòng chọn tầng bạn muốn đến.';
        $data = array_merge($data, array(
            'booking_type' => $booking_type,
            'floor' => $floor,
            'venue' => isset($_POST['venue']) ? sanitize_text_field(wp_unslash($_POST['venue'])) : '',
            'product_id' => isset($_POST['product_id']) ? sanitize_text_field(wp_unslash($_POST['product_id'])) : '',
            'product_name' => isset($_POST['product_name']) ? sanitize_text_field(wp_unslash($_POST['product_name'])) : '',
        ));
    } elseif ('tour' === $form_type) {
        $data['tour_name'] = isset($_POST['tour_name']) ? sanitize_text_field(wp_unslash($_POST['tour_name'])) : '';
    }

    if ($errors) {
        wp_send_json_error(array('message' => 'Vui lòng kiểm tra lại các thông tin.', 'fields' => $errors), 422);
    }

    $labels = array('booking' => 'Đặt lịch', 'tour' => 'Đặt Tour', 'membership' => 'Thành viên');
    $post_id = wp_insert_post(array(
        'post_type' => 'wonom_submission',
        'post_status' => 'private',
        'post_title' => sprintf('[%s] %s · %s', $labels[$form_type], $name, current_time('d/m/Y H:i')),
    ), true);
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Chưa thể lưu yêu cầu. Vui lòng thử lại.'), 500);
    }

    foreach ($data as $key => $value) {
        update_post_meta($post_id, '_wonom_' . sanitize_key($key), $value);
    }
    update_post_meta($post_id, '_wonom_status', 'new');
    update_post_meta($post_id, '_wonom_source_url', esc_url_raw(wp_get_referer() ?: ''));

    set_transient($rate_key, $rate_count + 1, 10 * MINUTE_IN_SECONDS);

    $mail_lines = array('Loại: ' . $labels[$form_type]);
    foreach ($data as $key => $value) {
        $mail_lines[] = $key . ': ' . (is_bool($value) ? ($value ? 'yes' : 'no') : $value);
    }
    wp_mail(
        get_option('admin_email'),
        sprintf('[WONOM] Yêu cầu mới từ %s', $name),
        implode("\n", $mail_lines),
        array('Content-Type: text/plain; charset=UTF-8')
    );

    wp_send_json_success(array(
        'message' => 'Gửi yêu cầu thành công! WONOM sẽ liên hệ với bạn trong thời gian sớm nhất.',
        'submission_id' => $post_id,
    ));
}
add_action('wp_ajax_wonom_submit_form', 'wonom_handle_home_form_submission');
add_action('wp_ajax_nopriv_wonom_submit_form', 'wonom_handle_home_form_submission');

function wonom_submission_admin_columns($columns)
{
    return array(
        'cb' => isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox">',
        'title' => 'Yêu cầu',
        'wonom_phone' => 'Điện thoại',
        'wonom_type' => 'Loại',
        'wonom_status' => 'Trạng thái',
        'date' => 'Ngày gửi',
    );
}
add_filter('manage_wonom_submission_posts_columns', 'wonom_submission_admin_columns');

function wonom_submission_admin_column($column, $post_id)
{
    if ('wonom_phone' === $column) echo esc_html(get_post_meta($post_id, '_wonom_phone', true));
    if ('wonom_type' === $column) echo esc_html(get_post_meta($post_id, '_wonom_form_type', true));
    if ('wonom_status' === $column) echo esc_html(get_post_meta($post_id, '_wonom_status', true));
}
add_action('manage_wonom_submission_posts_custom_column', 'wonom_submission_admin_column', 10, 2);

function wonom_submission_add_meta_box()
{
    add_meta_box(
        'wonom-submission-details',
        'Chi tiết yêu cầu',
        'wonom_submission_render_meta_box',
        'wonom_submission',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes_wonom_submission', 'wonom_submission_add_meta_box');

function wonom_submission_render_meta_box($post)
{
    $fields = array(
        'form_type' => 'Loại biểu mẫu',
        'name' => 'Họ và tên',
        'phone' => 'Số điện thoại',
        'date' => 'Ngày đến',
        'time' => 'Thời gian',
        'guests' => 'Số người',
        'booking_type' => 'Loại đặt lịch',
        'floor' => 'Tầng',
        'venue' => 'Khu vực',
        'product_id' => 'Mã sản phẩm',
        'product_name' => 'Tên sản phẩm',
        'tour_name' => 'Tên Tour',
        'consent' => 'Đồng ý nhận ưu đãi',
        'status' => 'Trạng thái',
        'source_url' => 'Trang gửi yêu cầu',
    );
    echo '<table class="widefat striped"><tbody>';
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, '_wonom_' . $key, true);
        if ('' === (string) $value) continue;
        echo '<tr><th style="width:180px">' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
    }
    echo '</tbody></table>';
}
