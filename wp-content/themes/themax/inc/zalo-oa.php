<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Zalo OA configuration.
 *
 * Production secrets may be defined in wp-config.php with these constants:
 * WONOM_ZALO_APP_ID, WONOM_ZALO_APP_SECRET, WONOM_ZALO_ACCESS_TOKEN,
 * WONOM_ZALO_REFRESH_TOKEN, WONOM_ZALO_GROUP_ID and
 * WONOM_ZALO_OA_SECRET_KEY.
 */
function wonom_zalo_option($key, $constant = '')
{
    if ($constant && defined($constant) && '' !== (string) constant($constant)) {
        return (string) constant($constant);
    }

    return function_exists('tr_options_field')
        ? trim((string) tr_options_field('tr_theme_options.' . $key))
        : '';
}

function wonom_zalo_is_enabled()
{
    if (defined('WONOM_ZALO_ENABLED')) {
        return (bool) WONOM_ZALO_ENABLED;
    }

    $value = function_exists('tr_options_field')
        ? tr_options_field('tr_theme_options.zalo_enabled')
        : false;

    return !empty($value);
}

function wonom_zalo_config()
{
    return array(
        'enabled' => wonom_zalo_is_enabled(),
        'app_id' => wonom_zalo_option('zalo_app_id', 'WONOM_ZALO_APP_ID'),
        'app_secret' => wonom_zalo_option('zalo_app_secret', 'WONOM_ZALO_APP_SECRET'),
        'oa_secret_key' => wonom_zalo_option('zalo_oa_secret_key', 'WONOM_ZALO_OA_SECRET_KEY'),
        'group_id' => wonom_zalo_option('zalo_group_id', 'WONOM_ZALO_GROUP_ID'),
        'group_name' => wonom_zalo_option('zalo_group_name'),
        'recipient_user_id' => wonom_zalo_option('zalo_recipient_user_id', 'WONOM_ZALO_RECIPIENT_USER_ID'),
        'recipient_name' => wonom_zalo_option('zalo_recipient_name'),
        'recipient_phone' => wonom_zalo_option('zalo_recipient_phone'),
        'access_token' => wonom_zalo_option('zalo_access_token', 'WONOM_ZALO_ACCESS_TOKEN'),
        'refresh_token' => wonom_zalo_option('zalo_refresh_token', 'WONOM_ZALO_REFRESH_TOKEN'),
    );
}

/**
 * Save Zalo settings without relying on TypeRocket's JSON rewrite endpoint.
 */
function wonom_zalo_handle_save_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_save_zalo_settings');

    $options = get_option('tr_theme_options', array());
    if (!is_array($options)) {
        $options = array();
    }

    $old_app_id = isset($options['zalo_app_id']) ? (string) $options['zalo_app_id'] : '';
    $old_group_id = isset($options['zalo_group_id']) ? (string) $options['zalo_group_id'] : '';
    $app_id = isset($_POST['zalo_app_id'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_app_id']))
        : '';
    $recipient_user_id = isset($_POST['zalo_recipient_user_id'])
        ? preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['zalo_recipient_user_id']))
        : '';

    $options['zalo_enabled'] = !empty($_POST['zalo_enabled']) ? 1 : 0;
    $options['zalo_app_id'] = $app_id;
    $group_id = isset($_POST['zalo_group_id'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_group_id']))
        : '';
    $options['zalo_group_id'] = $group_id;
    $options['zalo_group_name'] = $group_id === $old_group_id && isset($_POST['zalo_group_name'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_group_name']))
        : '';
    $options['zalo_recipient_user_id'] = $recipient_user_id;
    $options['zalo_recipient_name'] = isset($_POST['zalo_recipient_name'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_recipient_name']))
        : '';
    $options['zalo_recipient_phone'] = isset($_POST['zalo_recipient_phone'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_recipient_phone']))
        : '';

    $secret_changed = false;
    if (!empty($_POST['zalo_app_secret'])) {
        $options['zalo_app_secret'] = sanitize_text_field(wp_unslash($_POST['zalo_app_secret']));
        $secret_changed = true;
    }
    if (!empty($_POST['zalo_oa_secret_key'])) {
        $options['zalo_oa_secret_key'] = sanitize_text_field(wp_unslash($_POST['zalo_oa_secret_key']));
    }

    $seed_tokens_changed = false;
    foreach (array('zalo_access_token', 'zalo_refresh_token') as $token_key) {
        if (!empty($_POST[$token_key])) {
            $options[$token_key] = sanitize_text_field(wp_unslash($_POST[$token_key]));
            $seed_tokens_changed = true;
        }
    }

    update_option('tr_theme_options', $options);

    if ($old_app_id !== $app_id || $secret_changed || $seed_tokens_changed) {
        delete_option('wonom_zalo_runtime_tokens');
        delete_option('wonom_zalo_oa_id');
    }

    wp_safe_redirect(add_query_arg(array(
        'page' => 'theme_options',
        'wonom_zalo_saved' => 'success',
    ), admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_save_zalo_settings', 'wonom_zalo_handle_save_settings');

function wonom_zalo_configured()
{
    $config = wonom_zalo_config();
    $tokens = wonom_zalo_tokens();

    return $config['enabled']
        && '' !== $config['app_id']
        && '' !== $config['app_secret']
        && '' !== $config['group_id']
        && ('' !== $tokens['access_token'] || '' !== $tokens['refresh_token']);
}

function wonom_zalo_token_source_hash($config)
{
    return hash('sha256', implode('|', array(
        $config['app_id'],
        $config['access_token'],
        $config['refresh_token'],
    )));
}

function wonom_zalo_tokens()
{
    $config = wonom_zalo_config();
    $source_hash = wonom_zalo_token_source_hash($config);
    $stored = get_option('wonom_zalo_runtime_tokens', array());

    if (is_array($stored) && isset($stored['source_hash']) && hash_equals($source_hash, (string) $stored['source_hash'])) {
        return array(
            'access_token' => isset($stored['access_token']) ? (string) $stored['access_token'] : '',
            'refresh_token' => isset($stored['refresh_token']) ? (string) $stored['refresh_token'] : '',
            'expires_at' => isset($stored['expires_at']) ? (int) $stored['expires_at'] : 0,
            'source_hash' => $source_hash,
        );
    }

    return array(
        'access_token' => $config['access_token'],
        'refresh_token' => $config['refresh_token'],
        'expires_at' => 0,
        'source_hash' => $source_hash,
    );
}

function wonom_zalo_refresh_access_token()
{
    $config = wonom_zalo_config();
    $tokens = wonom_zalo_tokens();

    if (!$config['app_id'] || !$config['app_secret'] || !$tokens['refresh_token']) {
        return new WP_Error('zalo_missing_refresh_config', 'Thiếu App ID, Secret Key hoặc Refresh Token của Zalo OA.');
    }

    if (get_transient('wonom_zalo_refresh_lock')) {
        return new WP_Error('zalo_refresh_locked', 'Một tiến trình khác đang làm mới Zalo token.');
    }
    set_transient('wonom_zalo_refresh_lock', 1, 30);

    $response = wp_remote_post('https://oauth.zaloapp.com/v4/oa/access_token', array(
        'timeout' => 15,
        'headers' => array(
            'secret_key' => $config['app_secret'],
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => array(
            'app_id' => $config['app_id'],
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokens['refresh_token'],
        ),
    ));
    delete_transient('wonom_zalo_refresh_lock');

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (200 !== (int) wp_remote_retrieve_response_code($response) || !is_array($body) || empty($body['access_token'])) {
        $message = is_array($body) && !empty($body['error_name'])
            ? $body['error_name']
            : (is_array($body) && !empty($body['message']) ? $body['message'] : 'Không thể làm mới Zalo OA token.');
        return new WP_Error('zalo_refresh_failed', sanitize_text_field((string) $message), $body);
    }

    $expires_in = !empty($body['expires_in']) ? max(300, absint($body['expires_in'])) : DAY_IN_SECONDS;
    $new_tokens = array(
        'access_token' => sanitize_text_field((string) $body['access_token']),
        'refresh_token' => !empty($body['refresh_token'])
            ? sanitize_text_field((string) $body['refresh_token'])
            : $tokens['refresh_token'],
        'expires_at' => time() + $expires_in,
        'source_hash' => wonom_zalo_token_source_hash($config),
    );
    update_option('wonom_zalo_runtime_tokens', $new_tokens, false);

    return $new_tokens['access_token'];
}

function wonom_zalo_oauth_callback_url()
{
    return admin_url('admin-post.php?action=wonom_zalo_oauth_callback');
}

function wonom_zalo_base64url_encode($value)
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function wonom_zalo_store_oauth_tokens($body)
{
    if (!is_array($body) || empty($body['access_token']) || empty($body['refresh_token'])) {
        return new WP_Error('zalo_oauth_invalid_response', 'Zalo không trả về đầy đủ Access Token và Refresh Token.');
    }

    $config = wonom_zalo_config();
    $expires_in = !empty($body['expires_in']) ? max(300, absint($body['expires_in'])) : DAY_IN_SECONDS;
    update_option('wonom_zalo_runtime_tokens', array(
        'access_token' => sanitize_text_field((string) $body['access_token']),
        'refresh_token' => sanitize_text_field((string) $body['refresh_token']),
        'expires_at' => time() + $expires_in,
        'source_hash' => wonom_zalo_token_source_hash($config),
    ), false);

    if (!empty($body['oa_id'])) {
        update_option('wonom_zalo_oa_id', sanitize_text_field((string) $body['oa_id']), false);
    }

    return true;
}

function wonom_zalo_oauth_error_redirect($message)
{
    wp_safe_redirect(add_query_arg(array(
        'page' => 'theme_options',
        'wonom_zalo_oauth' => 'error',
        'wonom_zalo_error' => sanitize_text_field((string) $message),
    ), admin_url('themes.php')));
    exit;
}

function wonom_zalo_handle_oauth_start()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_zalo_oauth_start');

    $config = wonom_zalo_config();
    if (!$config['app_id'] || !$config['app_secret']) {
        wonom_zalo_oauth_error_redirect('Hãy lưu Zalo App ID và App Secret trước khi kết nối OA.');
    }

    $verifier = wonom_zalo_base64url_encode(random_bytes(48));
    $challenge = wonom_zalo_base64url_encode(hash('sha256', $verifier, true));
    $state = bin2hex(random_bytes(20));
    set_transient('wonom_zalo_oauth_' . $state, array(
        'verifier' => $verifier,
        'created_by' => get_current_user_id(),
    ), 15 * MINUTE_IN_SECONDS);

    $permission_url = add_query_arg(array(
        'app_id' => $config['app_id'],
        'redirect_uri' => wonom_zalo_oauth_callback_url(),
        'code_challenge' => $challenge,
        'state' => $state,
    ), 'https://oauth.zaloapp.com/v4/oa/permission');

    wp_redirect($permission_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
    exit;
}
add_action('admin_post_wonom_zalo_oauth_start', 'wonom_zalo_handle_oauth_start');

function wonom_zalo_handle_oauth_callback()
{
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    $oa_id = isset($_GET['oa_id']) ? sanitize_text_field(wp_unslash($_GET['oa_id'])) : '';
    $pending = $state ? get_transient('wonom_zalo_oauth_' . $state) : false;

    if (!$state || !$code || !is_array($pending) || empty($pending['verifier'])) {
        wonom_zalo_oauth_error_redirect('Yêu cầu kết nối OA không hợp lệ hoặc đã hết hạn. Vui lòng thực hiện lại.');
    }
    delete_transient('wonom_zalo_oauth_' . $state);

    $config = wonom_zalo_config();
    $response = wp_remote_post('https://oauth.zaloapp.com/v4/oa/access_token', array(
        'timeout' => 15,
        'headers' => array(
            'secret_key' => $config['app_secret'],
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body' => array(
            'app_id' => $config['app_id'],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'code_verifier' => $pending['verifier'],
        ),
    ));

    if (is_wp_error($response)) {
        wonom_zalo_oauth_error_redirect($response->get_error_message());
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($oa_id && is_array($body)) {
        $body['oa_id'] = $oa_id;
    }
    $stored = wonom_zalo_store_oauth_tokens($body);
    if (is_wp_error($stored)) {
        $message = is_array($body) && !empty($body['error_name'])
            ? $body['error_name']
            : (is_array($body) && !empty($body['message']) ? $body['message'] : $stored->get_error_message());
        wonom_zalo_oauth_error_redirect($message);
    }

    wp_safe_redirect(add_query_arg(array(
        'page' => 'theme_options',
        'wonom_zalo_oauth' => 'success',
    ), admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_zalo_oauth_callback', 'wonom_zalo_handle_oauth_callback');
add_action('admin_post_nopriv_wonom_zalo_oauth_callback', 'wonom_zalo_handle_oauth_callback');

function wonom_zalo_webhook_url()
{
    return admin_url('admin-post.php?action=wonom_zalo_webhook');
}

/**
 * Validate a webhook with the OA Secret Key configured in Zalo Developers.
 */
function wonom_zalo_webhook_signature_is_valid($raw_body, $payload)
{
    $config = wonom_zalo_config();
    if ('' === $config['oa_secret_key'] || empty($payload['app_id']) || empty($payload['timestamp'])) {
        return false;
    }

    if ('' !== $config['app_id'] && (string) $payload['app_id'] !== (string) $config['app_id']) {
        return false;
    }

    $signature = isset($_SERVER['HTTP_X_ZEVENT_SIGNATURE'])
        ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_ZEVENT_SIGNATURE']))
        : '';
    $signature = preg_replace('/^mac=/i', '', trim($signature));
    if (!preg_match('/^[a-f0-9]{64}$/i', $signature)) {
        return false;
    }

    $expected = hash(
        'sha256',
        (string) $payload['app_id'] . $raw_body . (string) $payload['timestamp'] . $config['oa_secret_key']
    );

    return hash_equals(strtolower($expected), strtolower($signature));
}

function wonom_zalo_webhook_users()
{
    $users = get_option('wonom_zalo_webhook_users', array());
    return is_array($users) ? $users : array();
}

/**
 * Receive user_send_* events and retain a short admin-only list of candidate
 * recipients. Nothing is selected automatically, so a customer cannot replace
 * the configured notification recipient by messaging the OA.
 */
function wonom_zalo_handle_webhook()
{
    if ('POST' !== strtoupper(isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : '')) {
        wp_send_json(array('status' => 'ready'));
    }

    $raw_body = file_get_contents('php://input');
    $payload = json_decode($raw_body, true);
    if (!is_array($payload) || !wonom_zalo_webhook_signature_is_valid($raw_body, $payload)) {
        // Zalo probes a new webhook URL before enabling it. Return 200 so the
        // connectivity check succeeds, but never persist an unsigned event.
        wp_send_json(array('status' => 'ready'));
    }

    $event_name = isset($payload['event_name']) ? sanitize_key($payload['event_name']) : '';
    $user_id = isset($payload['sender']['id'])
        ? preg_replace('/[^0-9]/', '', (string) $payload['sender']['id'])
        : '';
    if (0 === strpos($event_name, 'user_send_') && '' !== $user_id) {
        $message = '';
        if (!empty($payload['message']['text'])) {
            $message = sanitize_text_field((string) $payload['message']['text']);
        } elseif (!empty($payload['message']['attachments'])) {
            $message = '[' . sanitize_text_field($event_name) . ']';
        }

        $name = '';
        foreach (array('display_name', 'name') as $name_key) {
            if (!empty($payload['sender'][$name_key])) {
                $name = sanitize_text_field((string) $payload['sender'][$name_key]);
                break;
            }
        }

        $users = wonom_zalo_webhook_users();
        $users[$user_id] = array(
            'user_id' => $user_id,
            'name' => $name,
            'message' => function_exists('mb_substr') ? mb_substr($message, 0, 160) : substr($message, 0, 160),
            'received_at' => time(),
        );
        uasort($users, static function ($left, $right) {
            return (int) $right['received_at'] <=> (int) $left['received_at'];
        });
        $users = array_slice($users, 0, 20, true);
        update_option('wonom_zalo_webhook_users', $users, false);
    }

    wp_send_json(array('status' => 'ok'));
}
add_action('admin_post_wonom_zalo_webhook', 'wonom_zalo_handle_webhook');
add_action('admin_post_nopriv_wonom_zalo_webhook', 'wonom_zalo_handle_webhook');

function wonom_zalo_access_token($force_refresh = false)
{
    $tokens = wonom_zalo_tokens();
    $expires_soon = $tokens['expires_at'] && $tokens['expires_at'] <= time() + 300;

    if ($force_refresh || !$tokens['access_token'] || $expires_soon) {
        return wonom_zalo_refresh_access_token();
    }

    return $tokens['access_token'];
}

function wonom_zalo_response_requires_refresh($http_code, $body)
{
    $error = is_array($body) && isset($body['error']) ? (int) $body['error'] : 0;

    return 401 === (int) $http_code || in_array($error, array(-124, -201, -216, -217), true);
}

/**
 * Build the authentication headers required by Zalo OpenAPI.
 *
 * Apps with enhanced security enabled require appsecret_proof in addition to
 * the OA access token. Zalo calculates it as an HMAC-SHA256 of the access
 * token, using the app secret as the signing key.
 */
function wonom_zalo_api_headers($access_token, $headers = array())
{
    $config = wonom_zalo_config();
    $auth_headers = array(
        'access_token' => (string) $access_token,
    );

    if ('' !== $config['app_secret']) {
        $auth_headers['appsecret_proof'] = hash_hmac(
            'sha256',
            (string) $access_token,
            $config['app_secret']
        );
    }

    return array_merge($auth_headers, $headers);
}

function wonom_zalo_send_group_message($message, $allow_token_retry = true)
{
    if (!wonom_zalo_configured()) {
        return new WP_Error('zalo_not_configured', 'Cấu hình Zalo OA chưa đầy đủ hoặc chưa được bật.');
    }

    $config = wonom_zalo_config();
    $access_token = wonom_zalo_access_token();
    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $response = wp_remote_post('https://openapi.zalo.me/v3.0/oa/group/message', array(
        'timeout' => 15,
        'headers' => wonom_zalo_api_headers($access_token, array(
            'Content-Type' => 'application/json; charset=utf-8',
        )),
        'body' => wp_json_encode(array(
            'recipient' => array('group_id' => $config['group_id']),
            'message' => array('text' => (string) $message),
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($allow_token_retry && wonom_zalo_response_requires_refresh($http_code, $body)) {
        $refreshed = wonom_zalo_access_token(true);
        if (is_wp_error($refreshed)) {
            return $refreshed;
        }
        return wonom_zalo_send_group_message($message, false);
    }

    if (200 !== $http_code || !is_array($body) || !isset($body['error']) || 0 !== (int) $body['error']) {
        $message = is_array($body) && !empty($body['message'])
            ? $body['message']
            : 'Zalo OA không chấp nhận yêu cầu gửi tin.';
        return new WP_Error('zalo_send_failed', sanitize_text_field((string) $message), array(
            'http_code' => $http_code,
            'response' => $body,
        ));
    }

    return $body;
}

/**
 * Send a one-to-one consultation message from the OA to the configured admin.
 */
function wonom_zalo_send_admin_message($message, $allow_token_retry = true)
{
    if (!wonom_zalo_configured()) {
        return new WP_Error('zalo_not_configured', 'Cấu hình Zalo OA hoặc người nhận thông báo chưa đầy đủ.');
    }

    $config = wonom_zalo_config();
    $access_token = wonom_zalo_access_token();
    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $response = wp_remote_post('https://openapi.zalo.me/v3.0/oa/message/cs', array(
        'timeout' => 15,
        'headers' => wonom_zalo_api_headers($access_token, array(
            'Content-Type' => 'application/json; charset=utf-8',
        )),
        'body' => wp_json_encode(array(
            'recipient' => array('user_id' => $config['recipient_user_id']),
            'message' => array('text' => (string) $message),
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($allow_token_retry && wonom_zalo_response_requires_refresh($http_code, $body)) {
        $refreshed = wonom_zalo_access_token(true);
        if (is_wp_error($refreshed)) {
            return $refreshed;
        }
        return wonom_zalo_send_admin_message($message, false);
    }

    if (200 !== $http_code || !is_array($body) || !isset($body['error']) || 0 !== (int) $body['error']) {
        $error_message = is_array($body) && !empty($body['message'])
            ? $body['message']
            : 'Zalo OA không chấp nhận yêu cầu gửi tin 1-1.';
        return new WP_Error('zalo_send_failed', sanitize_text_field((string) $error_message), array(
            'http_code' => $http_code,
            'response' => $body,
        ));
    }

    return $body;
}

/**
 * Return users from the OA's latest one-to-one conversations.
 */
function wonom_zalo_get_recent_users($allow_token_retry = true)
{
    $access_token = wonom_zalo_access_token();
    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $url = add_query_arg('data', wp_json_encode(array(
        'offset' => 0,
        'count' => 20,
    )), 'https://openapi.zalo.me/v2.0/oa/listrecentchat');
    $response = wp_remote_get($url, array(
        'timeout' => 15,
        'headers' => wonom_zalo_api_headers($access_token),
    ));
    if (is_wp_error($response)) {
        return $response;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($allow_token_retry && wonom_zalo_response_requires_refresh($http_code, $body)) {
        $refreshed = wonom_zalo_access_token(true);
        if (is_wp_error($refreshed)) {
            return $refreshed;
        }
        return wonom_zalo_get_recent_users(false);
    }

    if (200 !== $http_code || !is_array($body) || !isset($body['error']) || 0 !== (int) $body['error']) {
        $error_message = is_array($body) && !empty($body['message'])
            ? $body['message']
            : 'Không thể lấy danh sách hội thoại gần đây từ Zalo OA.';
        return new WP_Error('zalo_recent_users_failed', sanitize_text_field((string) $error_message));
    }

    $users = array();
    $messages = !empty($body['data']) && is_array($body['data']) ? $body['data'] : array();
    foreach ($messages as $chat) {
        if (!is_array($chat)) {
            continue;
        }

        $from_user = isset($chat['src']) && 1 === (int) $chat['src'];
        $user_id = $from_user
            ? (isset($chat['from_id']) ? (string) $chat['from_id'] : '')
            : (isset($chat['to_id']) ? (string) $chat['to_id'] : '');
        $user_id = preg_replace('/[^0-9]/', '', $user_id);
        if ('' === $user_id) {
            continue;
        }

        $name_key = $from_user ? 'from_display_name' : 'to_display_name';
        $avatar_key = $from_user ? 'from_avatar' : 'to_avatar';
        $time = isset($chat['time']) ? (int) $chat['time'] : 0;
        if (!isset($users[$user_id]) || $time > $users[$user_id]['time']) {
            $users[$user_id] = array(
                'user_id' => $user_id,
                'name' => isset($chat[$name_key]) ? sanitize_text_field((string) $chat[$name_key]) : '',
                'avatar' => isset($chat[$avatar_key]) ? esc_url_raw((string) $chat[$avatar_key]) : '',
                'message' => isset($chat['message']) ? sanitize_text_field((string) $chat['message']) : '',
                'time' => $time,
            );
        }
    }

    uasort($users, static function ($left, $right) {
        return $right['time'] <=> $left['time'];
    });

    return array_values($users);
}

function wonom_zalo_handle_load_recent_users()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_zalo_load_recent_users');

    $users = wonom_zalo_get_recent_users();
    $args = array('page' => 'theme_options');
    if (is_wp_error($users)) {
        $args['wonom_zalo_users'] = 'error';
        $args['wonom_zalo_error'] = $users->get_error_message();
    } else {
        set_transient('wonom_zalo_users_' . get_current_user_id(), $users, 10 * MINUTE_IN_SECONDS);
        $args['wonom_zalo_users'] = 'success';
    }

    wp_safe_redirect(add_query_arg($args, admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_zalo_load_recent_users', 'wonom_zalo_handle_load_recent_users');

function wonom_zalo_handle_select_recipient()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_zalo_select_recipient');

    $user_id = isset($_POST['zalo_recipient_user_id'])
        ? preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['zalo_recipient_user_id']))
        : '';
    if ('' === $user_id) {
        wp_safe_redirect(add_query_arg(array(
            'page' => 'theme_options',
            'wonom_zalo_users' => 'error',
            'wonom_zalo_error' => 'Zalo User ID không hợp lệ.',
        ), admin_url('themes.php')));
        exit;
    }

    $options = get_option('tr_theme_options', array());
    $options = is_array($options) ? $options : array();
    $options['zalo_recipient_user_id'] = $user_id;
    $options['zalo_recipient_name'] = isset($_POST['zalo_recipient_name'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_recipient_name']))
        : '';
    update_option('tr_theme_options', $options);

    wp_safe_redirect(add_query_arg(array(
        'page' => 'theme_options',
        'wonom_zalo_recipient_saved' => 'success',
    ), admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_zalo_select_recipient', 'wonom_zalo_handle_select_recipient');

function wonom_zalo_get_groups($allow_token_retry = true)
{
    $access_token = wonom_zalo_access_token();
    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $response = wp_remote_get(add_query_arg(array(
        'offset' => 0,
        'count' => 10,
    ), 'https://openapi.zalo.me/v3.0/oa/group/getgroupsofoa'), array(
        'timeout' => 15,
        'headers' => wonom_zalo_api_headers($access_token),
    ));
    if (is_wp_error($response)) {
        return $response;
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if ($allow_token_retry && wonom_zalo_response_requires_refresh($http_code, $body)) {
        $refreshed = wonom_zalo_access_token(true);
        if (is_wp_error($refreshed)) {
            return $refreshed;
        }
        return wonom_zalo_get_groups(false);
    }

    if (200 !== $http_code || !is_array($body) || !isset($body['error']) || 0 !== (int) $body['error']) {
        $message = is_array($body) && !empty($body['message'])
            ? $body['message']
            : 'Không thể lấy danh sách nhóm GMF từ Zalo.';
        return new WP_Error('zalo_groups_failed', sanitize_text_field((string) $message));
    }

    $data = isset($body['data']) && is_array($body['data']) ? $body['data'] : array();
    if (isset($data['groups']) && is_array($data['groups'])) {
        return $data['groups'];
    }
    if (isset($data['items']) && is_array($data['items'])) {
        return $data['items'];
    }

    return $data && array_keys($data) === range(0, count($data) - 1) ? $data : array();
}

function wonom_zalo_handle_load_groups()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_zalo_load_groups');

    $groups = wonom_zalo_get_groups();
    $args = array('page' => 'theme_options');
    if (is_wp_error($groups)) {
        $args['wonom_zalo_groups'] = 'error';
        $args['wonom_zalo_error'] = $groups->get_error_message();
    } else {
        set_transient('wonom_zalo_groups_' . get_current_user_id(), $groups, 10 * MINUTE_IN_SECONDS);
        $args['wonom_zalo_groups'] = 'success';
    }

    wp_safe_redirect(add_query_arg($args, admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_zalo_load_groups', 'wonom_zalo_handle_load_groups');

function wonom_zalo_handle_select_group()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_zalo_select_group');

    $group_id = isset($_POST['zalo_group_id'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_group_id']))
        : '';
    if ('' === $group_id) {
        wp_safe_redirect(add_query_arg(array(
            'page' => 'theme_options',
            'wonom_zalo_groups' => 'error',
            'wonom_zalo_error' => 'Group ID không hợp lệ.',
        ), admin_url('themes.php')));
        exit;
    }

    $options = get_option('tr_theme_options', array());
    $options = is_array($options) ? $options : array();
    $options['zalo_group_id'] = $group_id;
    $options['zalo_group_name'] = isset($_POST['zalo_group_name'])
        ? sanitize_text_field(wp_unslash($_POST['zalo_group_name']))
        : '';
    update_option('tr_theme_options', $options);

    wp_safe_redirect(add_query_arg(array(
        'page' => 'theme_options',
        'wonom_zalo_group_saved' => 'success',
    ), admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_zalo_select_group', 'wonom_zalo_handle_select_group');

function wonom_zalo_submission_message($post_id)
{
    $type = (string) get_post_meta($post_id, '_wonom_form_type', true);

    if ('membership' === $type) {
        $lines = array(
            '[WONOM] THÀNH VIÊN MỚI',
            'Họ tên: ' . trim((string) get_post_meta($post_id, '_wonom_name', true)),
            'Mã thành viên: ' . trim((string) get_post_meta($post_id, '_wonom_member_number', true)),
            'Số điện thoại: ' . trim((string) get_post_meta($post_id, '_wonom_phone', true)),
            'Email/Gmail: ' . trim((string) get_post_meta($post_id, '_wonom_email', true)),
            'Đăng ký lúc: ' . get_the_date('d/m/Y H:i', $post_id),
        );
        $edit_url = get_edit_post_link($post_id, '');
        if ($edit_url) {
            $lines[] = 'Xem thành viên: ' . $edit_url;
        }

        $message = implode("\n", $lines);
        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($message, 0, 1900, '…', 'UTF-8');
        }

        return substr($message, 0, 1900);
    }

    $type_labels = function_exists('wonom_submission_type_labels') ? wonom_submission_type_labels() : array();
    $labels = array(
        'name' => 'Khách hàng',
        'phone' => 'Điện thoại',
        'date' => 'Ngày đến',
        'time' => 'Thời gian',
        'guests' => 'Số người',
        'booking_type' => 'Loại đặt chỗ',
        'floor' => 'Tầng',
        'venue' => 'Khu vực',
        'product_name' => 'Sản phẩm workshop',
        'tour_name' => 'Tour',
    );
    $lines = array(
        '[WONOM] YÊU CẦU MỚI',
        'Loại: ' . (isset($type_labels[$type]) ? $type_labels[$type] : $type),
        'Mã yêu cầu: #' . absint($post_id),
    );

    foreach ($labels as $key => $label) {
        $value = trim((string) get_post_meta($post_id, '_wonom_' . $key, true));
        if ('' !== $value) {
            $lines[] = $label . ': ' . $value;
        }
    }

    $lines[] = 'Gửi lúc: ' . get_the_date('d/m/Y H:i', $post_id);
    $edit_url = get_edit_post_link($post_id, '');
    if ($edit_url) {
        $lines[] = 'Xem chi tiết: ' . $edit_url;
    }

    $message = implode("\n", $lines);
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($message, 0, 1900, '…', 'UTF-8');
    }

    return substr($message, 0, 1900);
}

function wonom_zalo_queue_submission($post_id, $attempt = 0)
{
    $post_id = absint($post_id);
    if (!$post_id || !wonom_zalo_is_enabled()) {
        return false;
    }
    $form_type = (string) get_post_meta($post_id, '_wonom_form_type', true);
    if (!in_array($form_type, array('booking', 'tour', 'membership'), true)) {
        return false;
    }

    $args = array($post_id, absint($attempt));
    if (!wp_next_scheduled('wonom_zalo_send_submission', $args)) {
        $scheduled = wp_schedule_single_event(time(), 'wonom_zalo_send_submission', $args);
        if (!$scheduled) {
            update_post_meta($post_id, '_wonom_zalo_status', 'failed');
            update_post_meta($post_id, '_wonom_zalo_error', 'Không thể tạo lịch gửi Zalo.');
            return false;
        }
    }

    update_post_meta($post_id, '_wonom_zalo_status', 'queued');
    update_post_meta($post_id, '_wonom_zalo_error', '');
    if (function_exists('spawn_cron')) {
        spawn_cron(time());
    }
    return true;
}

function wonom_zalo_process_submission($post_id, $attempt = 0)
{
    $post_id = absint($post_id);
    $attempt = absint($attempt);
    if (!$post_id || !in_array(get_post_type($post_id), array('wonom_submission', 'wonom_member'), true)) {
        return;
    }
    if ('sent' === get_post_meta($post_id, '_wonom_zalo_status', true)) {
        return;
    }

    $result = wonom_zalo_send_group_message(wonom_zalo_submission_message($post_id));
    if (!is_wp_error($result)) {
        update_post_meta($post_id, '_wonom_zalo_status', 'sent');
        update_post_meta($post_id, '_wonom_zalo_sent_at', current_time('mysql'));
        update_post_meta($post_id, '_wonom_zalo_error', '');
        if (!empty($result['data']['message_id'])) {
            update_post_meta($post_id, '_wonom_zalo_message_id', sanitize_text_field((string) $result['data']['message_id']));
        }
        return;
    }

    update_post_meta($post_id, '_wonom_zalo_error', $result->get_error_message());
    update_post_meta($post_id, '_wonom_zalo_attempts', $attempt + 1);
    if ($attempt < 2) {
        $delay = 0 === $attempt ? MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
        wp_schedule_single_event(time() + $delay, 'wonom_zalo_send_submission', array($post_id, $attempt + 1));
        update_post_meta($post_id, '_wonom_zalo_status', 'retrying');
    } else {
        update_post_meta($post_id, '_wonom_zalo_status', 'failed');
    }
}
add_action('wonom_zalo_send_submission', 'wonom_zalo_process_submission', 10, 2);

function wonom_zalo_handle_test()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_test_zalo');

    $result = wonom_zalo_send_group_message(
        '[WONOM] Kiểm tra kết nối Zalo OA' . "\n" .
        'Website đã gửi thành công vào nhóm GMF lúc ' . current_time('d/m/Y H:i:s') . '.'
    );
    $args = array(
        'page' => 'theme_options',
        'wonom_zalo_test' => is_wp_error($result) ? 'error' : 'success',
    );
    if (is_wp_error($result)) {
        $args['wonom_zalo_error'] = $result->get_error_message();
    }

    wp_safe_redirect(add_query_arg($args, admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_test_zalo', 'wonom_zalo_handle_test');

function wonom_zalo_handle_resend()
{
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_resend_zalo_' . $post_id);

    delete_post_meta($post_id, '_wonom_zalo_status');
    $queued = wonom_zalo_queue_submission($post_id);
    wp_safe_redirect(add_query_arg('wonom_zalo_queued', $queued ? '1' : '0', get_edit_post_link($post_id, '')));
    exit;
}
add_action('admin_post_wonom_resend_zalo', 'wonom_zalo_handle_resend');
