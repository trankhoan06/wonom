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
    $emails = array();

    foreach ($candidates as $candidate) {
        $email = sanitize_email($candidate);
        if ($email && is_email($email)) {
            $emails[] = $email;
        }
    }

    if (!$emails) {
        $emails[] = get_option('admin_email');
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

    $labels = wonom_submission_type_labels();
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
    $mail_sent = wp_mail(
        wonom_submission_notification_emails(),
        sprintf('[WONOM] Yêu cầu mới từ %s', $name),
        implode("\n", $mail_lines),
        array('Content-Type: text/plain; charset=UTF-8')
    );
    update_post_meta($post_id, '_wonom_email_status', $mail_sent ? 'sent' : 'failed');
    update_post_meta($post_id, '_wonom_email_sent_at', $mail_sent ? current_time('mysql') : '');

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
        'wonom_email' => 'Email',
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
        .wonom-status,.wonom-mail-sent,.wonom-mail-failed{display:inline-block;padding:3px 8px;border-radius:999px;font-weight:600}
        .wonom-status-new{background:#e8f1ff;color:#145db2}.wonom-status-contacted{background:#fff4d6;color:#8a5a00}
        .wonom-status-completed,.wonom-mail-sent{background:#e7f7ed;color:#176b3a}
        .wonom-status-cancelled,.wonom-mail-failed{background:#fdebec;color:#a51d2d}
    </style>';
}
add_action('admin_head', 'wonom_submission_admin_styles');
