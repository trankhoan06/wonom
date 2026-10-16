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
        'capabilities' => array(
            'create_posts' => 'do_not_allow',
        ),
        'menu_icon' => 'dashicons-email-alt',
        'menu_position' => 25,
    ));
}
add_action('init', 'wonom_register_submission_post_type');

function wonom_submission_type_labels()
{
    return array(
        'booking' => 'Đặt chỗ',
        'tour' => 'Đặt Tour',
        'membership' => 'Đăng ký thành viên',
    );
}

function wonom_submission_status_labels()
{
    return array(
        'new' => 'Mới',
        'contacted' => 'Đã liên hệ',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    );
}

function wonom_submission_notification_emails()
{
    $configured = (string) tr_options_field('tr_theme_options.receive_email');
    $candidates = preg_split('/[\s,;]+/', $configured, -1, PREG_SPLIT_NO_EMPTY);
    // The operations mailbox must receive every membership and booking request.
    $emails = array('wonomcomplex@gmail.com');

    foreach ($candidates as $candidate) {
        $email = sanitize_email($candidate);
        if ($email && is_email($email)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

function wonom_submission_normalize_phone($phone)
{
    return preg_replace('/[^0-9+]/', '', (string) $phone);
}

function wonom_submission_valid_phone($phone)
{
    return (bool) preg_match('/^(?:\+?84|0)(?:3|5|7|8|9)[0-9]{8}$/', wonom_submission_normalize_phone($phone));
}

function wonom_submission_phone_rate_key($phone)
{
    $phone = wonom_submission_normalize_phone($phone);
    if (0 === strpos($phone, '+84')) {
        return '0' . substr($phone, 3);
    }
    if (0 === strpos($phone, '84')) {
        return '0' . substr($phone, 2);
    }
    return $phone;
}

function wonom_submission_valid_date($date)
{
    $date = (string) $date;
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $year = (int) $matches[3];
        $format = '!d/m/Y';
    } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        $format = '!Y-m-d';
    } else {
        return false;
    }
    if (!checkdate($month, $day, $year)) {
        return false;
    }
    $submitted = DateTimeImmutable::createFromFormat($format, $date, wp_timezone());
    $today = new DateTimeImmutable('today', wp_timezone());
    return $submitted && $submitted >= $today;
}

function wonom_submission_valid_time($time)
{
    return (bool) preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?:\s*-\s*(?:[01]\d|2[0-3]):[0-5]\d)?$/', trim((string) $time));
}

function wonom_submission_post_text($key, $max_length = 200)
{
    if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
        return '';
    }

    $value = sanitize_text_field(wp_unslash((string) $_POST[$key]));
    if ($max_length > 0) {
        $value = function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }
    return trim($value);
}

function wonom_submission_client_ip()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) && is_scalar($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash((string) $_SERVER['REMOTE_ADDR']))
        : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function wonom_submission_is_same_origin()
{
    $source = '';
    if (!empty($_SERVER['HTTP_ORIGIN']) && is_scalar($_SERVER['HTTP_ORIGIN'])) {
        $source = wp_unslash((string) $_SERVER['HTTP_ORIGIN']);
    } elseif (!empty($_SERVER['HTTP_REFERER']) && is_scalar($_SERVER['HTTP_REFERER'])) {
        $source = wp_unslash((string) $_SERVER['HTTP_REFERER']);
    }
    if ('' === $source) {
        return true;
    }

    $home_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    $source_host = strtolower((string) wp_parse_url($source, PHP_URL_HOST));
    return '' === $source_host || ($home_host && hash_equals($home_host, $source_host));
}

function wonom_submission_passes_bot_checks()
{
    if (isset($_POST['website']) && !is_scalar($_POST['website'])) {
        return false;
    }
    if ('' !== wonom_submission_post_text('website', 200)) {
        return false;
    }

    $elapsed = wonom_submission_post_text('form_elapsed_ms', 20);
    return ctype_digit($elapsed)
        && (int) $elapsed >= 1200;
}

function wonom_submission_rate_limited($bucket, $identity, $limit, $window)
{
    $identity = (string) $identity;
    if ('' === $identity) {
        return false;
    }

    $digest = hash_hmac('sha256', $identity, wp_salt('nonce'));
    $key = 'wonom_' . sanitize_key($bucket) . '_' . $digest;
    $count = (int) get_transient($key);
    if ($count >= (int) $limit) {
        return true;
    }

    set_transient($key, $count + 1, (int) $window);
    return false;
}

function wonom_submission_rate_limit_response($retry_after)
{
    if (!headers_sent()) {
        header('Retry-After: ' . absint($retry_after));
    }
    wp_send_json_error(array('message' => wonom_home_t('rate_limited')), 429);
}

function wonom_handle_home_form_submission()
{
    $content_length = isset($_SERVER['CONTENT_LENGTH']) ? absint($_SERVER['CONTENT_LENGTH']) : 0;
    if ($content_length > 32768) {
        wp_send_json_error(array('message' => wonom_home_t('invalid_form')), 413);
    }

    if (!check_ajax_referer('wonom_submit_form', 'nonce', false)) {
        wp_send_json_error(array('message' => wonom_home_t('expired')), 403);
    }

    if (!wonom_submission_is_same_origin()) {
        wp_send_json_error(array('message' => wonom_home_t('invalid_form')), 403);
    }

    if (!wonom_submission_passes_bot_checks()) {
        wp_send_json_success(array('message' => wonom_home_t('request_received')));
    }

    $ip = wonom_submission_client_ip();
    if (wonom_submission_rate_limited('form_ip_burst', $ip, 12, 10 * MINUTE_IN_SECONDS)) {
        wonom_submission_rate_limit_response(10 * MINUTE_IN_SECONDS);
    }
    if (wonom_submission_rate_limited('form_ip_daily', $ip, 50, DAY_IN_SECONDS)) {
        wonom_submission_rate_limit_response(HOUR_IN_SECONDS);
    }

    $form_type = sanitize_key(wonom_submission_post_text('form_type', 20));
    if (!in_array($form_type, array('booking', 'tour', 'membership'), true)) {
        wp_send_json_error(array('message' => wonom_home_t('invalid_form')), 400);
    }

    $name = wonom_submission_post_text('name', 120);
    $phone = wonom_submission_post_text('phone', 30);
    $errors = array();
    $name_length = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
    if ($name_length < 2 || $name_length > 80 || preg_match('/[0-9]/', $name)) {
        $errors['name'] = wonom_home_t('invalid_name');
    }
    if (!wonom_submission_valid_phone($phone)) {
        $errors['phone'] = wonom_home_t('invalid_phone');
    }

    $normalized_phone = wonom_submission_normalize_phone($phone);
    $data = array(
        'form_type' => $form_type,
        'name' => $name,
        'phone' => $normalized_phone,
    );

    if ('membership' === $form_type) {
        $consent = in_array(wonom_submission_post_text('consent', 10), array('1', 'yes', 'on'), true);
        if (!$consent) {
            $errors['consent'] = wonom_home_t('required_consent');
        }
        $data['consent'] = $consent ? 'yes' : 'no';
    } else {
        $date = wonom_submission_post_text('date', 20);
        $time = wonom_submission_post_text('time', 30);
        $guests = wonom_submission_post_text('guests', 10);
        if (!wonom_submission_valid_date($date)) $errors['date'] = wonom_home_t('invalid_date');
        if (!wonom_submission_valid_time($time)) $errors['time'] = wonom_home_t('invalid_time');
        if (!preg_match('/^[1-9][0-9]{0,2}$/', $guests) || (int) $guests > 999) $errors['guests'] = wonom_home_t('required_guests');
        $data = array_merge($data, array(
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
        ));
    }

    if ('booking' === $form_type) {
        $booking_type = sanitize_key(wonom_submission_post_text('booking_type', 30));
        $allowed_booking_types = array('general', 'restaurant', 'rental', 'workshop', 'appointment', 'reservation');
        $floor = wonom_submission_post_text('floor', 10);
        $product_id = wonom_submission_post_text('product_id', 100);
        $product_name = wonom_submission_post_text('product_name', 160);
        if (!in_array($booking_type, $allowed_booking_types, true)) $errors['booking_type'] = wonom_home_t('invalid_form');
        if ('general' === $booking_type && !in_array($floor, array('1F', '2F', '3F', '4F', '5F'), true)) $errors['floor'] = wonom_home_t('required_floor');
        if ('' !== $product_id && '' === $product_name) $errors['product_name'] = 'Không xác định được tên sản phẩm workshop. Vui lòng chọn lại sản phẩm.';
        $data = array_merge($data, array(
            'booking_type' => $booking_type,
            'floor' => $floor,
            'venue' => wonom_submission_post_text('venue', 100),
            'product_id' => $product_id,
            'product_name' => $product_name,
        ));
    } elseif ('tour' === $form_type) {
        $data['tour_name'] = wonom_submission_post_text('tour_name', 160);
        if ('' === $data['tour_name']) $errors['tour_name'] = wonom_home_t('invalid_form');
    }

    if ($errors) {
        wp_send_json_error(array('message' => wonom_home_t('check_information'), 'fields' => $errors), 422);
    }

    $duplicate_key = 'wonom_form_duplicate_' . hash_hmac('sha256', wp_json_encode($data), wp_salt('nonce'));
    $duplicate_post_id = absint(get_transient($duplicate_key));
    if ($duplicate_post_id && 'wonom_submission' === get_post_type($duplicate_post_id)) {
        wp_send_json_success(array(
            'message' => wonom_home_t('success'),
            'submission_id' => $duplicate_post_id,
            'duplicate' => true,
        ));
    }

    $phone_rate_key = wonom_submission_phone_rate_key($normalized_phone);
    if (wonom_submission_rate_limited('form_phone_hourly', $phone_rate_key, 5, HOUR_IN_SECONDS)) {
        wonom_submission_rate_limit_response(HOUR_IN_SECONDS);
    }
    if (wonom_submission_rate_limited('form_phone_daily', $phone_rate_key, 10, DAY_IN_SECONDS)) {
        wonom_submission_rate_limit_response(DAY_IN_SECONDS);
    }

    $labels = wonom_submission_type_labels();
    $post_id = wp_insert_post(array(
        'post_type' => 'wonom_submission',
        'post_status' => 'private',
        'post_title' => sprintf('[%s] %s · %s', $labels[$form_type], $name, current_time('d/m/Y H:i')),
    ), true);
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => wonom_home_t('save_failed')), 500);
    }

    foreach ($data as $key => $value) {
        update_post_meta($post_id, '_wonom_' . sanitize_key($key), $value);
    }
    update_post_meta($post_id, '_wonom_status', 'new');
    update_post_meta($post_id, '_wonom_source_url', esc_url_raw(wp_get_referer() ?: ''));
    set_transient($duplicate_key, $post_id, 3 * MINUTE_IN_SECONDS);

    $mail_lines = array('Loại: ' . $labels[$form_type]);
    foreach ($data as $key => $value) {
        $mail_lines[] = $key . ': ' . (is_bool($value) ? ($value ? 'yes' : 'no') : $value);
    }
    $mail_sent = wp_mail(
        wonom_submission_notification_emails(),
        sprintf('[WONOM] Yêu cầu mới từ %s', $name),
        implode("\n", $mail_lines),
        array('Content-Type: text/plain; charset=UTF-8')
    );
    update_post_meta($post_id, '_wonom_email_status', $mail_sent ? 'sent' : 'failed');
    update_post_meta($post_id, '_wonom_email_sent_at', $mail_sent ? current_time('mysql') : '');

    if (function_exists('wonom_zalo_queue_submission')) {
        wonom_zalo_queue_submission($post_id);
    }

    wp_send_json_success(array(
        'message' => wonom_home_t('success'),
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
        'wonom_email' => 'Email',
        'wonom_zalo' => 'Zalo',
        'date' => 'Ngày gửi',
    );
}
add_filter('manage_wonom_submission_posts_columns', 'wonom_submission_admin_columns');

function wonom_submission_admin_column($column, $post_id)
{
    if ('wonom_phone' === $column) echo esc_html(get_post_meta($post_id, '_wonom_phone', true));
    if ('wonom_type' === $column) {
        $type = get_post_meta($post_id, '_wonom_form_type', true);
        $labels = wonom_submission_type_labels();
        echo esc_html(isset($labels[$type]) ? $labels[$type] : $type);
    }
    if ('wonom_status' === $column) {
        $status = get_post_meta($post_id, '_wonom_status', true) ?: 'new';
        $labels = wonom_submission_status_labels();
        echo '<span class="wonom-status wonom-status-' . esc_attr($status) . '">';
        echo esc_html(isset($labels[$status]) ? $labels[$status] : $status);
        echo '</span>';
    }
    if ('wonom_email' === $column) {
        $email_status = get_post_meta($post_id, '_wonom_email_status', true);
        echo 'sent' === $email_status
            ? '<span class="wonom-mail-sent">Đã gửi</span>'
            : ('failed' === $email_status ? '<span class="wonom-mail-failed">Gửi lỗi</span>' : '—');
    }
    if ('wonom_zalo' === $column) {
        $zalo_status = get_post_meta($post_id, '_wonom_zalo_status', true);
        $zalo_labels = array(
            'queued' => 'Đang chờ',
            'retrying' => 'Đang thử lại',
            'sent' => 'Đã gửi',
            'failed' => 'Gửi lỗi',
        );
        echo isset($zalo_labels[$zalo_status])
            ? '<span class="wonom-zalo wonom-zalo-' . esc_attr($zalo_status) . '">' . esc_html($zalo_labels[$zalo_status]) . '</span>'
            : '—';
    }
}
add_action('manage_wonom_submission_posts_custom_column', 'wonom_submission_admin_column', 10, 2);

function wonom_submission_admin_filters($post_type)
{
    if ('wonom_submission' !== $post_type) return;

    $selected_type = isset($_GET['wonom_form_type']) ? sanitize_key(wp_unslash($_GET['wonom_form_type'])) : '';
    $selected_status = isset($_GET['wonom_status']) ? sanitize_key(wp_unslash($_GET['wonom_status'])) : '';

    echo '<select name="wonom_form_type">';
    echo '<option value="">Tất cả loại form</option>';
    foreach (wonom_submission_type_labels() as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($selected_type, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    echo '<select name="wonom_status">';
    echo '<option value="">Tất cả trạng thái</option>';
    foreach (wonom_submission_status_labels() as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($selected_status, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'wonom_submission_admin_filters');

function wonom_submission_filter_admin_query($query)
{
    if (!is_admin() || !$query->is_main_query() || 'wonom_submission' !== $query->get('post_type')) return;

    $meta_query = array();
    $form_type = isset($_GET['wonom_form_type']) ? sanitize_key(wp_unslash($_GET['wonom_form_type'])) : '';
    $status = isset($_GET['wonom_status']) ? sanitize_key(wp_unslash($_GET['wonom_status'])) : '';

    if (isset(wonom_submission_type_labels()[$form_type])) {
        $meta_query[] = array('key' => '_wonom_form_type', 'value' => $form_type);
    }
    if (isset(wonom_submission_status_labels()[$status])) {
        $meta_query[] = array('key' => '_wonom_status', 'value' => $status);
    }
    if ($meta_query) {
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'wonom_submission_filter_admin_query');

function wonom_submission_admin_views($views)
{
    $base_url = admin_url('edit.php?post_type=wonom_submission');
    $current = isset($_GET['wonom_form_type']) ? sanitize_key(wp_unslash($_GET['wonom_form_type'])) : '';
    $links = array(
        'all' => '<a href="' . esc_url($base_url) . '"' . ('' === $current ? ' class="current"' : '') . '>Tất cả</a>',
    );

    foreach (wonom_submission_type_labels() as $type => $label) {
        $count_query = new WP_Query(array(
            'post_type' => 'wonom_submission',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_wonom_form_type',
            'meta_value' => $type,
        ));
        $url = add_query_arg('wonom_form_type', $type, $base_url);
        $links[$type] = '<a href="' . esc_url($url) . '"' . ($current === $type ? ' class="current"' : '') . '>'
            . esc_html($label) . ' <span class="count">(' . number_format_i18n($count_query->found_posts) . ')</span></a>';
    }

    return $links;
}
add_filter('views_edit-wonom_submission', 'wonom_submission_admin_views');

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
        'email_status' => 'Trạng thái gửi email',
        'email_sent_at' => 'Thời gian gửi email',
        'zalo_status' => 'Trạng thái gửi Zalo',
        'zalo_sent_at' => 'Thời gian gửi Zalo',
        'zalo_message_id' => 'Zalo Message ID',
        'zalo_attempts' => 'Số lần gửi Zalo',
        'zalo_error' => 'Lỗi Zalo gần nhất',
        'source_url' => 'Trang gửi yêu cầu',
    );
    echo '<table class="widefat striped"><tbody>';
    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, '_wonom_' . $key, true);
        if ('' === (string) $value) continue;
        echo '<tr><th style="width:180px">' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
    }
    echo '</tbody></table>';

    $current_status = get_post_meta($post->ID, '_wonom_status', true) ?: 'new';
    wp_nonce_field('wonom_save_submission_status', 'wonom_submission_status_nonce');
    echo '<p><label for="wonom-submission-status"><strong>Trạng thái xử lý</strong></label></p>';
    echo '<select id="wonom-submission-status" name="wonom_submission_status" style="min-width:220px">';
    foreach (wonom_submission_status_labels() as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($current_status, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';

    $submission_form_type = (string) get_post_meta($post->ID, '_wonom_form_type', true);
    if (
        function_exists('wonom_zalo_is_enabled')
        && wonom_zalo_is_enabled()
        && in_array($submission_form_type, array('booking', 'tour'), true)
    ) {
        $resend_url = wp_nonce_url(
            add_query_arg(array(
                'action' => 'wonom_resend_zalo',
                'post_id' => $post->ID,
            ), admin_url('admin-post.php')),
            'wonom_resend_zalo_' . $post->ID
        );
        echo '<p><a class="button" href="' . esc_url($resend_url) . '">Gửi lại thông báo Zalo</a></p>';
    }
}

function wonom_submission_save_status($post_id)
{
    if (
        empty($_POST['wonom_submission_status_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wonom_submission_status_nonce'])), 'wonom_save_submission_status')
        || !current_user_can('edit_post', $post_id)
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    ) {
        return;
    }

    $status = isset($_POST['wonom_submission_status'])
        ? sanitize_key(wp_unslash($_POST['wonom_submission_status']))
        : '';
    if (isset(wonom_submission_status_labels()[$status])) {
        update_post_meta($post_id, '_wonom_status', $status);
    }
}
add_action('save_post_wonom_submission', 'wonom_submission_save_status');

function wonom_submission_admin_styles()
{
    $screen = get_current_screen();
    if (!$screen || 'wonom_submission' !== $screen->post_type) return;
    echo '<style>
        .wonom-status,.wonom-mail-sent,.wonom-mail-failed,.wonom-zalo{display:inline-block;padding:3px 8px;border-radius:999px;font-weight:600}
        .wonom-status-new{background:#e8f1ff;color:#145db2}.wonom-status-contacted{background:#fff4d6;color:#8a5a00}
        .wonom-status-completed,.wonom-mail-sent,.wonom-zalo-sent{background:#e7f7ed;color:#176b3a}
        .wonom-zalo-queued,.wonom-zalo-retrying{background:#fff4d6;color:#8a5a00}
        .wonom-status-cancelled,.wonom-mail-failed,.wonom-zalo-failed{background:#fdebec;color:#a51d2d}
    </style>';
}
add_action('admin_head', 'wonom_submission_admin_styles');
