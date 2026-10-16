<?php

/**
 * Dedicated TypeRocket settings screen for the Wonom homepage.
 */

function wonom_get_homepage_language()
{
    if (isset($_GET['lang'])) {
        $requested_language = sanitize_key(wp_unslash($_GET['lang']));
        $available_languages = function_exists('pll_languages_list')
            ? (array) pll_languages_list(array('fields' => 'slug'))
            : array('vi', 'en', 'ko');
        if (!$available_languages) {
            $available_languages = array('vi', 'en', 'ko');
        }

        if (in_array($requested_language, $available_languages, true)) {
            return $requested_language;
        }
    }

    if (isset($_POST['lang'])) {
        $posted_language = sanitize_key(wp_unslash($_POST['lang']));
        $available_languages = function_exists('pll_languages_list')
            ? (array) pll_languages_list(array('fields' => 'slug'))
            : array('vi', 'en', 'ko');
        if (!$available_languages) {
            $available_languages = array('vi', 'en', 'ko');
        }

        if (in_array($posted_language, $available_languages, true)) {
            return $posted_language;
        }
    }

    if (function_exists('pll_current_language')) {
        $current_language = pll_current_language('slug');
        if ($current_language) {
            return sanitize_key($current_language);
        }
    }

    if (function_exists('pll_default_language')) {
        $default_language = pll_default_language('slug');
        if ($default_language) {
            return sanitize_key($default_language);
        }
    }

    return 'vi';
}

function wonom_get_homepage_default_language()
{
    if (function_exists('pll_default_language')) {
        $default_language = pll_default_language('slug');
        if ($default_language) {
            return sanitize_key($default_language);
        }
    }

    return 'vi';
}

function wonom_is_shared_homepage_field($key)
{
    $key = sanitize_key($key);

    // Plural keys such as home_library_images are containers; recurse into
    // them so tags can still be translated while images/icons/videos stay shared.
    return 'image' === $key
        || 'icon' === $key
        || 'video' === $key
        || 'thumbnail' === $key
        || (bool) preg_match('/(?:^|_)(?:image|icon|video|youtube|thumbnail)(?:_|$)/', $key);
}

function wonom_remove_shared_homepage_values($values)
{
    if (!is_array($values)) {
        return $values;
    }

    foreach ($values as $key => $value) {
        if (!is_int($key) && wonom_is_shared_homepage_field($key)) {
            unset($values[$key]);
            continue;
        }

        if (is_array($value)) {
            $values[$key] = wonom_remove_shared_homepage_values($value);
        }
    }

    return $values;
}

function wonom_restore_shared_homepage_values($values, $shared_values)
{
    if (!is_array($shared_values)) {
        return $values;
    }
    if (!is_array($values)) {
        $values = array();
    }

    foreach ($shared_values as $key => $shared_value) {
        if (!is_int($key) && wonom_is_shared_homepage_field($key)) {
            $values[$key] = $shared_value;
            continue;
        }

        if (is_array($shared_value)) {
            $current_value = isset($values[$key]) ? $values[$key] : array();
            $values[$key] = wonom_restore_shared_homepage_values($current_value, $shared_value);
        }
    }

    return $values;
}

function wonom_filter_homepage_options_by_language($default_options)
{
    $default_options = is_array($default_options) ? $default_options : array();
    $language = wonom_get_homepage_language();

    if ($language === wonom_get_homepage_default_language()) {
        return $default_options;
    }

    $translated_options = get_option('wonom_home_options_' . $language, array());
    if (!is_array($translated_options) || !$translated_options) {
        return $default_options;
    }

    $merged_options = array_replace_recursive($default_options, $translated_options);

    return wonom_restore_shared_homepage_values($merged_options, $default_options);
}
add_filter('option_wonom_home_options', 'wonom_filter_homepage_options_by_language');

function wonom_enqueue_global_richtext_admin_assets()
{
    if (!isset($_GET['page']) || 'wonom-homepage-settings' !== sanitize_key(wp_unslash($_GET['page']))) {
        return;
    }

    $theme_path = get_template_directory();
    $theme_url = get_template_directory_uri();
    $fullscreen_path = $theme_path . '/typerocket/wordpress/assets/typerocket/js/lib/redactor/fullscreen.js';
    $richtext_script_path = $theme_path . '/asset/js/admin-global-richtext.js';
    $richtext_style_path = $theme_path . '/asset/css/admin-global-richtext.css';

    wp_enqueue_media();
    wp_enqueue_script(
        'wonom-redactor-fullscreen',
        $theme_url . '/typerocket/wordpress/assets/typerocket/js/lib/redactor/fullscreen.js',
        array('jquery', 'typerocket-editor'),
        file_exists($fullscreen_path) ? filemtime($fullscreen_path) : null,
        true
    );
    wp_enqueue_script(
        'wonom-admin-global-richtext',
        $theme_url . '/asset/js/admin-global-richtext.js',
        array('jquery', 'typerocket-scripts-global', 'wonom-redactor-fullscreen'),
        file_exists($richtext_script_path) ? filemtime($richtext_script_path) : null,
        true
    );
    wp_enqueue_style(
        'wonom-admin-global-richtext',
        $theme_url . '/asset/css/admin-global-richtext.css',
        array('typerocket-styles-redactor'),
        file_exists($richtext_style_path) ? filemtime($richtext_style_path) : null
    );
}
add_action('admin_enqueue_scripts', 'wonom_enqueue_global_richtext_admin_assets');

function wonom_register_homepage_settings_menu()
{
    add_menu_page(
        'Cài đặt trang chủ',
        'Trang chủ',
        'manage_options',
        'wonom-homepage-settings',
        'wonom_render_homepage_settings',
        'dashicons-admin-home',
        24
    );
}
add_action('admin_menu', 'wonom_register_homepage_settings_menu');

function wonom_sanitize_homepage_settings($value)
{
    if (is_array($value)) {
        $sanitized = array();
        foreach ($value as $key => $item) {
            $sanitized[sanitize_key($key)] = wonom_sanitize_homepage_settings($item);
        }
        return $sanitized;
    }

    return is_string($value) ? wp_kses_post($value) : $value;
}

function wonom_get_home_image_url($field, $fallback = '')
{
    $image = tr_options_field('wonom_home_options.' . $field);

    if (is_numeric($image)) {
        return wp_get_attachment_image_url((int) $image, 'full') ?: $fallback;
    }
    if (is_array($image)) {
        $attachment_id = isset($image['id']) ? (int) $image['id'] : 0;
        $image_url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'full') : '';
        if (!$image_url && !empty($image['url'])) {
            $image_url = esc_url_raw($image['url']);
        }
        return $image_url ?: $fallback;
    }
    if (is_string($image) && $image !== '') {
        return esc_url_raw($image);
    }

    return $fallback;
}

function wonom_handle_homepage_settings_save()
{
    if ('POST' !== strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '')) {
        return;
    }
    if (!isset($_GET['page']) || 'wonom-homepage-settings' !== $_GET['page']) {
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }

    check_admin_referer('wonom_save_homepage_settings', 'wonom_homepage_nonce');

    $submitted = isset($_POST['tr']['wonom_home_options'])
        ? wp_unslash($_POST['tr']['wonom_home_options'])
        : array();
    $language = wonom_get_homepage_language();
    $default_language = wonom_get_homepage_default_language();
    $option_name = 'wonom_home_options';
    $sanitized = wonom_sanitize_homepage_settings($submitted);

    if ($language !== $default_language) {
        $option_name .= '_' . $language;
        $sanitized = wonom_remove_shared_homepage_values($sanitized);
    }

    update_option($option_name, $sanitized);
    // Change the homepage fragment-cache key immediately so saved Admin data
    // is visible on the next frontend request instead of waiting five minutes.
    update_option('wonom_home_cache_generation', (string) microtime(true), false);

    wp_safe_redirect(add_query_arg(
        array(
            'page' => 'wonom-homepage-settings',
            'lang' => $language,
            'settings-updated' => 'true',
        ),
        admin_url('admin.php')
    ));
    exit;
}
add_action('admin_init', 'wonom_handle_homepage_settings_save');

function wonom_get_home_workshop_categories()
{
    $default_categories = array(
        array('slug' => 'hat', 'label' => 'NÓN'),
        array('slug' => 'traditional', 'label' => 'ÁO TRUYỀN THỐNG'),
        array('slug' => 'accessories', 'label' => 'PHỤ KIỆN'),
    );
    $terms = get_terms(array(
        'taxonomy' => 'wonom_workshop_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ));
    if (!is_wp_error($terms) && $terms) {
        return array_map(static function ($term) {
            return array('slug' => $term->slug, 'label' => $term->name);
        }, $terms);
    }

    // Legacy fallback before the one-time taxonomy migration runs in admin.
    $rows = tr_options_field('wonom_home_options.home_explore_floor_3_categories');
    if (!is_array($rows) || !$rows) {
        return $default_categories;
    }

    $categories = array();
    foreach ($rows as $row) {
        $slug = isset($row['slug']) ? sanitize_key($row['slug']) : '';
        $label = isset($row['label']) ? sanitize_text_field($row['label']) : '';
        if (!$slug || !$label) {
            continue;
        }
        $categories[$slug] = array('slug' => $slug, 'label' => $label);
    }

    return $categories ? array_values($categories) : $default_categories;
}

function wonom_get_home_explore_popup_data()
{
    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';
    $popup_defaults = array(
        1 => array('title' => '1F · NHÀ HÀNG MUJIGE', 'menu_label' => 'THỰC ĐƠN', 'menu' => array('menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp'), 'gallery' => array_fill(0, 12, 'store.jpg')),
        2 => array('title' => '2F · TRANG PHỤC TRUYỀN THỐNG', 'menu_label' => 'BẢNG GIÁ', 'menu' => array('menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp'), 'gallery' => array_fill(0, 12, 'store.jpg')),
        3 => array('title' => '3F · WORKSHOP', 'menu_label' => '', 'menu' => array(), 'gallery' => array_fill(0, 12, 'store.jpg')),
        4 => array('title' => '4F · STRESS ROOM', 'menu_label' => 'BẢNG GIÁ', 'menu' => array('menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp'), 'gallery' => array('img_popup.webp', 'store.jpg', 'store.jpg', 'img_popup.webp', 'store.jpg', 'store.jpg', 'img_popup.webp', 'store.jpg', 'store.jpg', 'img_popup.webp', 'store.jpg', 'store.jpg')),
        5 => array('title' => '5F · ROOFTOP DISCO', 'menu_label' => 'MENU', 'menu' => array('menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp', 'menu1.webp', 'menu2.webp'), 'gallery' => array('home-hero2.webp', 'home-hero3.webp', 'store.jpg', 'home-hero2.webp', 'home-hero3.webp', 'store.jpg', 'home-hero2.webp', 'home-hero3.webp', 'store.jpg', 'home-hero2.webp', 'home-hero3.webp', 'store.jpg')),
    );
    $resolve_image = static function ($image) {
        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, 'full') ?: '';
        }
        if (is_array($image)) {
            $attachment_id = isset($image['id']) ? (int) $image['id'] : 0;
            $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'full') : '';
            return $url ?: (isset($image['url']) ? esc_url_raw($image['url']) : '');
        }
        return is_string($image) ? esc_url_raw($image) : '';
    };
    $resolve_file = static function ($file) {
        if (is_numeric($file)) {
            return wp_get_attachment_url((int) $file) ?: '';
        }
        if (is_array($file)) {
            $attachment_id = isset($file['id']) ? (int) $file['id'] : 0;
            $url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
            return $url ?: (isset($file['url']) ? esc_url_raw($file['url']) : '');
        }
        return is_string($file) ? esc_url_raw($file) : '';
    };
    $resolve_youtube_embed = static function ($url) {
        $url = esc_url_raw($url);
        if (!$url) {
            return '';
        }

        $parts = wp_parse_url($url);
        $host = isset($parts['host']) ? strtolower(preg_replace('/^www\./', '', $parts['host'])) : '';
        $path = isset($parts['path']) ? trim($parts['path'], '/') : '';
        $video_id = '';

        if ('youtu.be' === $host) {
            $video_id = strtok($path, '/');
        } elseif (in_array($host, array('youtube.com', 'm.youtube.com', 'music.youtube.com'), true)) {
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                $video_id = isset($query['v']) ? $query['v'] : '';
            }
            if (!$video_id && preg_match('#^(?:embed|shorts|live)/([^/]+)#', $path, $matches)) {
                $video_id = $matches[1];
            }
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $video_id)
            ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id)
            : '';
    };
    $popup_data = array();

    foreach ($popup_defaults as $floor => $defaults) {
        $prefix = 'home_explore_floor_' . $floor . '_popup_';
        $title = tr_options_field('wonom_home_options.' . $prefix . 'title');
        $menu_label = tr_options_field('wonom_home_options.' . $prefix . 'menu_label');
        $menu_rows = tr_options_field('wonom_home_options.' . $prefix . 'menu_pages');
        $gallery_rows = tr_options_field('wonom_home_options.' . $prefix . 'gallery');
        $menu_pages = array();
        $gallery_images = array();

        if (is_array($menu_rows)) {
            foreach ($menu_rows as $row) {
                $url = $resolve_image(isset($row['image']) ? $row['image'] : '');
                if ($url) {
                    $menu_pages[] = $url;
                }
            }
        }
        if (!$menu_pages) {
            foreach ($defaults['menu'] as $filename) {
                $menu_pages[] = $asset_url . $filename;
            }
        }

        if (3 === $floor && function_exists('wonom_get_workshop_products')) {
            $workshop_category_labels = array();
            foreach (wonom_get_home_workshop_categories() as $workshop_category) {
                $workshop_category_labels[$workshop_category['slug']] = $workshop_category['label'];
            }
            foreach (wonom_get_workshop_products() as $workshop_product) {
                $category_slugs = !empty($workshop_product['categories']) && is_array($workshop_product['categories'])
                    ? array_values(array_filter(array_map('sanitize_key', $workshop_product['categories'])))
                    : array(!empty($workshop_product['category']) ? sanitize_key($workshop_product['category']) : 'all');
                $primary_category = $category_slugs ? $category_slugs[0] : 'all';
                $tag = isset($workshop_category_labels[$primary_category])
                    ? '#' . ltrim($workshop_category_labels[$primary_category], '#')
                    : '';
                $product_title = isset($workshop_product['title']) ? sanitize_text_field($workshop_product['title']) : 'Workshop';
                $gallery_images[] = array(
                    'type' => 'image',
                    'src' => $resolve_image(isset($workshop_product['image']) ? $workshop_product['image'] : '') ?: $asset_url . 'store.jpg',
                    'thumbnail' => '',
                    'showInSlider' => false,
                    'alt' => $product_title . ' · Wonom',
                    'tag' => $tag,
                    'category' => implode(' ', $category_slugs),
                );
            }
        } elseif (is_array($gallery_rows)) {
            foreach ($gallery_rows as $row) {
                $image_url = $resolve_image(isset($row['image']) ? $row['image'] : '');
                $video_url = $resolve_file(isset($row['video']) ? $row['video'] : '');
                $youtube_url = $resolve_youtube_embed(isset($row['youtube_url']) ? $row['youtube_url'] : '');

                if ($youtube_url) {
                    $media_type = 'youtube';
                    $url = $youtube_url;
                } elseif ($video_url) {
                    $media_type = 'video';
                    $url = $video_url;
                } elseif ($image_url) {
                    $media_type = 'image';
                    $url = $image_url;
                } else {
                    continue;
                }

                $category = isset($row['category']) ? sanitize_key($row['category']) : 'all';
                $tag = isset($row['tag']) ? sanitize_text_field($row['tag']) : '';
                $gallery_images[] = array(
                    'type' => $media_type,
                    'src' => $url,
                    'thumbnail' => $image_url,
                    'showInSlider' => !empty($row['show_in_slider']),
                    'alt' => trim(sanitize_text_field($defaults['title']) . ' · Wonom' . ($tag ? ' · ' . ltrim($tag, '#') : '')),
                    'tag' => $tag,
                    'category' => $category,
                );
            }
        }
        if (!$gallery_images && 3 !== $floor) {
            foreach ($defaults['gallery'] as $gallery_index => $filename) {
                $category = 'all';
                $tag = 'tag';
                $gallery_images[] = array(
                    'type' => 'image',
                    'src' => $asset_url . $filename,
                    'thumbnail' => $asset_url . $filename,
                    'showInSlider' => false,
                    'alt' => trim(sanitize_text_field($defaults['title']) . ' · Wonom' . ($tag ? ' · ' . ltrim($tag, '#') : '')),
                    'tag' => $tag,
                    'category' => $category,
                );
            }
        }

        $floor_data = array(
            'title' => $title ?: $defaults['title'],
            'menuLabel' => $menu_label ?: $defaults['menu_label'],
            'menuPages' => $menu_pages,
            'galleryImages' => $gallery_images,
        );

        if (3 === $floor) {
            $filters = array(array('value' => 'all', 'label' => 'TẤT CẢ'));
            $workshop_categories = function_exists('wonom_get_used_workshop_categories')
                ? wonom_get_used_workshop_categories()
                : wonom_get_home_workshop_categories();
            foreach ($workshop_categories as $category) {
                $filters[] = array('value' => $category['slug'], 'label' => $category['label']);
            }
            $floor_data['galleryFilters'] = $filters;
        }

        $popup_data[$floor . 'f'] = $floor_data;
    }

    if (function_exists('wonom_get_home_library_images')) {
        $popup_data['library'] = array(
            'title' => 'THƯ VIỆN ẢNH',
            'galleryOnly' => true,
            'menuLabel' => '',
            'menuPages' => array(),
            'galleryImages' => wonom_get_home_library_images(),
        );
    }

    return $popup_data;
}

function wonom_render_homepage_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $language = wonom_get_homepage_language();
    $default_language = wonom_get_homepage_default_language();
    $model = new \TypeRocket\Models\WPOption();
    $form = tr_form('option', 'create', null, $model)
        ->setGroup('wonom_home_options');

    $global_icon_settings = function () use ($form) {
        echo '<h2>Icon chung</h2>';
        echo '<p>Quản lý logo và các icon dùng ngoài những section nội dung chính.</p>';
        echo '<h3>Header</h3>';
        echo $form->image('home_header_logo_image')->setLabel('Logo header');
        echo '<h3>Nút liên hệ nổi</h3>';
        echo '<h4>Nút thành viên</h4>';
        echo $form->row(
            $form->image('home_global_member_icon')->setLabel('Icon thành viên'),
            $form->text('home_global_member_text')->setLabel('Nội dung'),
            $form->text('home_global_member_link')->setLabel('Đường dẫn')
        );
        echo '<h4>Nút điện thoại</h4>';
        echo $form->row(
            $form->image('home_global_phone_icon')->setLabel('Icon điện thoại'),
            $form->text('home_global_phone_text')->setLabel('Nội dung'),
            $form->text('home_global_phone_link')->setLabel('Đường dẫn')
        );
        echo '<h4>Nút Zalo</h4>';
        echo $form->row(
            $form->image('home_global_zalo_icon')->setLabel('Icon Zalo'),
            $form->text('home_global_zalo_text')->setLabel('Nội dung'),
            $form->text('home_global_zalo_link')->setLabel('Đường dẫn')
        );
        echo '<h3>Popup thành viên</h3>';
        echo $form->image('home_member_popup_icon')->setLabel('Icon trang trí thành viên');
    };

    $header_settings = function () use ($form) {
        echo '<h2>Nội dung Header</h2>';
        echo '<p>Quản lý nội dung menu và hai nút hành động trên Header cho cả desktop và mobile.</p>';
        echo '<h3>Menu điều hướng</h3>';
        echo $form->row(
            $form->text('home_header_nav_introduce')->setLabel('Giới thiệu'),
            $form->text('home_header_nav_experience')->setLabel('Trải nghiệm')
        );
        echo $form->row(
            $form->text('home_header_nav_explore')->setLabel('Khám phá'),
            $form->text('home_header_nav_tour')->setLabel('Tham quan')
        );
        echo $form->row(
            $form->text('home_header_nav_event')->setLabel('Sự kiện'),
            $form->text('home_header_nav_library')->setLabel('Thư viện'),
            $form->text('home_header_nav_contact')->setLabel('Liên hệ')
        );
        echo '<h3>Nút hành động</h3>';
        echo $form->row(
            $form->text('home_header_booking_text')->setLabel('Nút đặt chỗ'),
            $form->text('home_header_member_text')->setLabel('Nút thành viên')
        );
    };

    $hero_settings = function () use ($form) {
        echo '<h2>Hero</h2>';
        echo '<p>Quản lý các slide trong Hero. Thứ tự trong repeater cũng là thứ tự hiển thị ngoài website.</p>';

        echo $form->repeater('home_hero_slides')
            ->setLabel('Hero Slides')
            ->setFields(array(
                $form->image('image')->setLabel('Ảnh nền'),
                $form->image('mobile_image')
                    ->setLabel('Ảnh nền mobile')
                    ->setHelp('Dùng cho màn hình rộng tối đa 991px. Để trống để dùng ảnh nền desktop.'),
                $form->textarea('title')->setLabel('Tiêu đề'),
            ))
            ->setLimit(6);
    };

    $introduce_settings = function () use ($form) {
        echo '<h2>Giới thiệu</h2>';
        echo '<p>Quản lý nội dung section Giới thiệu trên trang chủ. Ba hình ảnh tương ứng với ba card từ trái sang phải.</p>';

        echo $form->text('home_intro_eyebrow')
            ->setLabel('Tiêu đề nhỏ');

        echo $form->textarea('home_intro_title')
            ->setLabel('Tiêu đề chính');

        echo $form->editor('home_intro_content')
            ->setLabel('Nội dung');

        echo '<h3>Hình ảnh các card</h3>';
        echo $form->image('home_intro_image_1')->setLabel('Hình card 1');
        echo $form->image('home_intro_image_2')->setLabel('Hình card 2');
        echo $form->image('home_intro_image_3')->setLabel('Hình card 3');

        echo '<h3>Icon con trang trí</h3>';
        echo $form->row(
            $form->image('home_intro_decor_icon_1')->setLabel('Con trang trí 1'),
            $form->image('home_intro_decor_icon_2')->setLabel('Con trang trí 2'),
            $form->image('home_intro_decor_icon_3')->setLabel('Con trang trí 3')
        );
    };

    $experience_settings = function () use ($form) {
        echo '<h2>Trải nghiệm</h2>';
        echo '<p>Quản lý 5 card tầng. Vị trí và liên kết tới từng tab Khám phá được giữ cố định.</p>';

        echo $form->image('home_experience_middle_image')->setLabel('Hình Wonom ở giữa');

        echo $form->text('home_experience_marquee_text')
            ->setLabel('Nội dung chữ chạy');

        for ($floor = 1; $floor <= 5; $floor++) {
            echo '<hr><h3>Card tầng ' . esc_html($floor) . '</h3>';
            echo $form->row(
                $form->text('home_experience_floor_' . $floor . '_label')
                    ->setLabel('Tên tầng'),
                $form->text('home_experience_floor_' . $floor . '_title')
                    ->setLabel('Tiêu đề')
            );
            echo $form->image('home_experience_floor_' . $floor . '_image')->setLabel('Hình ảnh');
        }
    };

    $explore_floor_defaults = array(
        1 => array('nav' => 'Nhà hàng', 'subtitle' => '1F - NHÀ HÀNG MUJIGE', 'title' => "Ẩm thực fusion\nHàn-Việt", 'description' => 'Thưởng thức shabu-shabu, bulgogi và kimbap dưới bức họa pop-art khổng lồ.', 'tags' => '#Không gian, #Ẩm thực, #Hàn Quốc', 'meta_1_label' => 'GIỜ MỞ CỬA:', 'meta_1_value' => 'Hằng ngày từ 11:00 đến 23:00', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM THỰC ĐƠN', 'booking' => 'ĐẶT BÀN GIỮ CHỖ'),
        2 => array('nav' => 'Culture Studio', 'subtitle' => '2F - TRANG PHỤC TRUYỀN THỐNG', 'title' => 'CHO THUÊ HANBOK VÁY + STUDIO CHỤP ẢNH', 'description' => 'Thuê hanbok, váy và tự chụp những bức ảnh nghệ thuật với ánh sáng chuyên nghiệp.', 'tags' => '#Trải nghiệm hanbok, #Váy, #Không gian, #Selfie', 'meta_1_label' => 'GIỜ THUÊ:', 'meta_1_value' => '60 phút/người', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH THUÊ'),
        3 => array('nav' => 'Workshop', 'subtitle' => '3F - WORKSHOP', 'title' => "XƯỞNG LÀM ĐỒ\nTHỦ CÔNG Ở WONOM", 'description' => 'Tự tay tạo nên món quà mang dấu ấn riêng. Trải nghiệm lớp học thủ công, thỏa sức sáng tạo và mang về tác phẩm do chính bạn hoàn thành.', 'tags' => '#Lớp thủ công, #Quà lưu niệm, #Đồ handmade', 'meta_1_label' => 'THỜI GIAN', 'meta_1_value' => '60 phút', 'meta_2_label' => 'SỐ LƯỢNG', 'meta_2_value' => '200+ Mẫu', 'primary' => 'KHÁM PHÁ NGAY', 'booking' => 'ĐẶT LỊCH HẸN'),
        4 => array('nav' => 'Stress Room', 'subtitle' => '4F - STRESS ROOM', 'title' => "PHÒNG GIẢI TỎA\nCƠN GIẬN", 'description' => 'Phòng giải tỏa căng thẳng bằng cách đập phá đồ vật trong không gian riêng biệt, trang bị đầy đủ đồ bảo hộ an toàn.', 'tags' => '#Đồ bảo hộ, #Theo suất, #Ném chai bia', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH HẸN'),
        5 => array('nav' => 'Rooftop Disco', 'subtitle' => '5F - ROOFTOP DISCO', 'title' => "QUẦY BAR MIRROR BALL\n· LOUNGE VỀ ĐÊM", 'description' => 'Tận hưởng những ly cocktail đầy cảm hứng dưới ánh đèn neon và mirror ball sôi động, thư giãn và ngắm nhìn thành phố về đêm.', 'tags' => '#Soju-cocktail, #Chill, #View thành phố, #Nhạc', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM MENU', 'booking' => 'ĐẶT CHỖ NHANH'),
    );

    $explore_settings = function () use ($form, $explore_floor_defaults) {
        echo '<h2>Khám phá 5 tầng</h2>';
        echo $form->text('home_explore_sidebar_title')
            ->setLabel('Tiêu đề thanh chọn tầng');

        $floor_contents = array();

        foreach ($explore_floor_defaults as $floor => $defaults) {
            ob_start();
            (static function () use ($form, $floor, $defaults) {
                $prefix = 'home_explore_floor_' . $floor . '_';

                echo '<h3>Nội dung tầng ' . esc_html($floor) . 'F</h3>';
                echo '<div class="wonom-floor-content-tabs" data-wonom-floor-content-tabs>';
                echo '<div class="wonom-floor-content-tabs__nav" role="tablist" aria-label="Nhóm nội dung tầng ' . esc_attr($floor) . 'F">';
                echo '<button type="button" class="wonom-floor-content-tabs__button is-active" role="tab" aria-selected="true" data-wonom-floor-content-tab="content">Nội dung</button>';
                echo '<button type="button" class="wonom-floor-content-tabs__button" role="tab" aria-selected="false" data-wonom-floor-content-tab="popup">Popup content</button>';
                echo '</div>';
                echo '<div class="wonom-floor-content-tabs__panel is-active" role="tabpanel" data-wonom-floor-content-panel="content">';
                echo $form->row(
                    $form->text($prefix . 'nav_title')->setLabel('Tên trên menu tầng'),
                    $form->text($prefix . 'subtitle')->setLabel('Tiêu đề sticky')
                );
                echo $form->textarea($prefix . 'title')->setLabel('Tiêu đề chính');
                echo $form->editor($prefix . 'description')->setLabel('Mô tả');
                echo $form->textarea($prefix . 'tags')
                    ->setLabel('Tags (phân cách bằng dấu phẩy hoặc xuống dòng)');
                echo $form->row(
                    $form->text($prefix . 'meta_1_label')->setLabel('Nhãn thông tin 1'),
                    $form->text($prefix . 'meta_1_value')->setLabel('Nội dung thông tin 1')
                );
                echo $form->row(
                    $form->text($prefix . 'meta_2_label')->setLabel('Nhãn thông tin 2'),
                    $form->text($prefix . 'meta_2_value')->setLabel('Nội dung thông tin 2')
                );

                echo $form->row(
                    $form->text($prefix . 'primary_button')->setLabel('Nút xem thông tin'),
                    $form->text($prefix . 'booking_button')->setLabel('Nút đặt lịch')
                );

                if (3 === $floor) {
                    echo '<hr><h3>Danh mục Workshop</h3>';
                    echo '<p>Danh mục và sản phẩm được quản lý trực tiếp trong post type Workshop để dùng chung cho bộ lọc.</p>';
                    echo '<p><a class="button" href="' . esc_url(admin_url('edit-tags.php?taxonomy=wonom_workshop_category&post_type=wonom_workshop')) . '">Quản lý danh mục Workshop</a> ';
                    echo '<a class="button" href="' . esc_url(admin_url('edit.php?post_type=wonom_workshop')) . '">Quản lý sản phẩm Workshop</a></p>';
                }

                echo '</div>';
                echo '<div class="wonom-floor-content-tabs__panel" role="tabpanel" data-wonom-floor-content-panel="popup">';
                echo '<h3>Popup tầng ' . esc_html($floor) . 'F</h3>';
                if (3 === $floor) {
                    echo $form->text($prefix . 'popup_title')->setLabel('Tiêu đề popup');
                } else {
                    echo $form->row(
                        $form->text($prefix . 'popup_title')->setLabel('Tiêu đề popup'),
                        $form->text($prefix . 'popup_menu_label')->setLabel('Tên tab menu/bảng giá')
                    );
                }

                if (3 !== $floor) {
                    echo $form->repeater($prefix . 'popup_menu_pages')
                        ->setLabel('Các trang menu/bảng giá trong popup')
                        ->setFields(array(
                            $form->image('image')->setLabel('Hình trang'),
                        ));
                }

                if (3 === $floor) {
                    echo '<p>Danh sách hình ảnh popup được tự động lấy từ ảnh card của các sản phẩm Workshop đã publish, theo đúng thứ tự sản phẩm.</p>';
                } else {
                    $popup_gallery_fields = array(
                        $form->image('image')
                            ->setLabel('Hình ảnh')
                            ->setHelp('Nếu có video, hình này được dùng làm thumbnail. Nếu không có video, hình này hiển thị như ảnh bình thường.'),
                        $form->file('video', array(), array('type' => 'video', 'button' => 'Chọn / tải video lên'))
                            ->setLabel('Video tải lên'),
                        $form->text('youtube_url')
                            ->setLabel('Link YouTube')
                            ->setHelp('Hỗ trợ youtube.com, youtu.be, YouTube Shorts và YouTube Live.'),
                        $form->checkbox('show_in_slider')
                            ->setLabel('Hiển thị ở slider')
                            ->setText('Hiển thị media này trong slider của tầng'),
                    );
                    $popup_gallery_fields[] = $form->text('tag')->setLabel('Tag hiển thị');
                    echo $form->repeater($prefix . 'popup_gallery')
                        ->setLabel('Thư viện ảnh popup')
                        ->setFields($popup_gallery_fields);
                }

                echo '<h4>Popup đặt lịch</h4>';
                echo $form->row(
                    $form->text($prefix . 'booking_title')->setLabel('Tiêu đề popup đặt lịch'),
                    $form->text($prefix . 'booking_submit')->setLabel('Nội dung nút gửi')
                );
                echo '</div>';
                echo '</div>';
            })();
            $floor_contents[$floor] = ob_get_clean();
        }

        echo '<div class="wonom-floor-tabs">';
        echo '<div class="wonom-floor-tabs__nav" role="tablist" aria-label="Cài đặt từng tầng">';
        foreach ($floor_contents as $floor => $floor_content) {
            $active_class = 1 === $floor ? ' is-active' : '';
            echo '<button type="button" class="wonom-floor-tabs__button' . esc_attr($active_class) . '" role="tab" aria-selected="' . (1 === $floor ? 'true' : 'false') . '" aria-controls="wonom-floor-panel-' . esc_attr($floor) . '" data-wonom-floor-tab="' . esc_attr($floor) . '">' . esc_html($floor . 'F') . '</button>';
        }
        echo '</div>';
        echo '<div class="wonom-floor-tabs__content">';
        foreach ($floor_contents as $floor => $floor_content) {
            $active_class = 1 === $floor ? ' is-active' : '';
            echo '<div id="wonom-floor-panel-' . esc_attr($floor) . '" class="wonom-floor-tabs__panel' . esc_attr($active_class) . '" role="tabpanel" data-wonom-floor-panel="' . esc_attr($floor) . '">';
            echo $floor_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '<style>
            .wonom-floor-tabs{display:grid;grid-template-columns:150px minmax(0,1fr);margin-top:20px;border:1px solid #ddd;background:#fff;min-height:360px}
            .wonom-floor-tabs__nav{padding:12px 0;border-right:1px solid #ddd;background:#f6f7f7}
            .wonom-floor-tabs__button{display:block;width:100%;padding:10px 16px;border:0;border-left:3px solid transparent;background:transparent;color:#2271b1;text-align:left;cursor:pointer}
            .wonom-floor-tabs__button:hover,.wonom-floor-tabs__button:focus{background:#fff;color:#135e96;outline:0;box-shadow:none}
            .wonom-floor-tabs__button.is-active{border-left-color:#2271b1;background:#fff;color:#1d2327;font-weight:600}
            .wonom-floor-tabs__content{min-width:0;padding:12px}
            .wonom-floor-tabs__panel{display:none}
            .wonom-floor-tabs__panel.is-active{display:block}
            .wonom-floor-content-tabs__nav{display:flex;gap:4px;margin:0 0 18px;border-bottom:1px solid #c3c4c7}
            .wonom-floor-content-tabs__button{margin:0 0 -1px;padding:10px 18px;border:1px solid transparent;border-bottom-color:#c3c4c7;background:transparent;color:#2271b1;font-weight:600;cursor:pointer}
            .wonom-floor-content-tabs__button:hover,.wonom-floor-content-tabs__button:focus{color:#135e96;outline:0;box-shadow:none}
            .wonom-floor-content-tabs__button.is-active{border-color:#c3c4c7;border-bottom-color:#fff;background:#fff;color:#1d2327}
            .wonom-floor-content-tabs__panel{display:none}
            .wonom-floor-content-tabs__panel.is-active{display:block}
            @media(max-width:782px){.wonom-floor-tabs{grid-template-columns:1fr}.wonom-floor-tabs__nav{display:flex;overflow-x:auto;border-right:0;border-bottom:1px solid #ddd}.wonom-floor-tabs__button{width:auto;min-width:64px;border-left:0;border-bottom:3px solid transparent;text-align:center}.wonom-floor-tabs__button.is-active{border-bottom-color:#2271b1}}
        </style>';
        echo '<script>
            (function(){
                var root = document.currentScript.previousElementSibling.previousElementSibling;
                if (!root || !root.classList.contains("wonom-floor-tabs")) return;

                root.addEventListener("click", function(event){
                    var contentTab = event.target.closest("[data-wonom-floor-content-tab]");
                    if (contentTab && root.contains(contentTab)) {
                        var contentTabs = contentTab.closest("[data-wonom-floor-content-tabs]");
                        var contentName = contentTab.getAttribute("data-wonom-floor-content-tab");
                        contentTabs.querySelectorAll("[data-wonom-floor-content-tab]").forEach(function(item){
                            var active = item === contentTab;
                            item.classList.toggle("is-active", active);
                            item.setAttribute("aria-selected", active ? "true" : "false");
                        });
                        contentTabs.querySelectorAll("[data-wonom-floor-content-panel]").forEach(function(panel){
                            panel.classList.toggle("is-active", panel.getAttribute("data-wonom-floor-content-panel") === contentName);
                        });
                        window.dispatchEvent(new Event("resize"));
                        return;
                    }
                    var button = event.target.closest("[data-wonom-floor-tab]");
                    if (!button || !root.contains(button)) {
                        return;
                    }
                    var floor = button.getAttribute("data-wonom-floor-tab");
                    root.querySelectorAll("[data-wonom-floor-tab]").forEach(function(item){
                        var active = item === button;
                        item.classList.toggle("is-active", active);
                        item.setAttribute("aria-selected", active ? "true" : "false");
                    });
                    root.querySelectorAll("[data-wonom-floor-panel]").forEach(function(panel){
                        panel.classList.toggle("is-active", panel.getAttribute("data-wonom-floor-panel") === floor);
                    });
                    window.dispatchEvent(new Event("resize"));
                });
            })();
        </script>';
    };

    $tour_settings = function () use ($form) {
        echo '<h2>Tour tham quan</h2>';
        echo $form->text('home_tour_detail_button_text')
            ->setLabel('Chữ nút xem chi tiết')
            ->setAttribute('placeholder', 'XEM CHI TIẾT');

        $tours_admin_url = admin_url('edit.php?post_type=wonom_tour');
        echo '<div style="margin:20px 0;padding:16px 20px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:4px;">';
        echo '<p style="margin:0 0 6px;font-size:14px;font-weight:600;color:#1d2327;">Danh sách Tour được quản lý độc lập tại menu Tour</p>';
        echo '<p style="margin:0 0 12px;font-size:13px;color:#50575e;">Các tour tham quan được quản lý độc lập để thuận tiện tạo mới, chỉnh sửa chi tiết, sắp xếp thứ tự và dịch đa ngôn ngữ.</p>';
        echo '<a href="' . esc_url($tours_admin_url) . '" class="button button-primary" style="margin-top:4px;">Đi đến Quản lý Tour &rarr;</a>';
        echo '</div>';
    };

    $event_settings = function () use ($form) {
        echo '<h2>Sự kiện đang diễn ra</h2>';
        echo $form->row(
            $form->text('home_event_subtitle')->setLabel('Tiêu đề nhỏ'),
            $form->textarea('home_event_title')->setLabel('Tiêu đề chính')
        );
        $events_admin_url = admin_url('edit.php?post_type=wonom_event');
        echo '<div style="margin:20px 0;padding:16px 20px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:4px;">';
        echo '<p style="margin:0 0 6px;font-size:14px;font-weight:600;color:#1d2327;">Danh sách sự kiện được quản lý độc lập tại menu Sự kiện</p>';
        echo '<p style="margin:0 0 12px;font-size:13px;color:#50575e;">Các sự kiện hiện đã được chuyển ra ngoài như Tour để thuận tiện tạo mới, chỉnh sửa chi tiết, sắp xếp thứ tự và dịch đa ngôn ngữ.</p>';
        echo '<a href="' . esc_url($events_admin_url) . '" class="button button-primary" style="margin-top:4px;">Đi đến Quản lý Sự kiện &rarr;</a>';
        echo '</div>';
        echo '<h3>Icon trang trí</h3>';
        echo $form->row(
            $form->image('home_event_decor_icon_1')->setLabel('Icon trang trí 1'),
            $form->image('home_event_decor_icon_2')->setLabel('Icon trang trí 2')
        );
    };

    $library_settings = function () use ($form) {
        echo '<h2>Không gian / Thư viện ảnh</h2>';
        echo $form->text('home_library_subtitle')->setLabel('Tiêu đề nhỏ');
        echo $form->textarea('home_library_title')->setLabel('Tiêu đề chính');
        echo $form->textarea('home_library_description')->setLabel('Mô tả');
        echo $form->repeater('home_library_images')
            ->setLabel('Danh sách hình ảnh')
            ->setHeadline('tag')
            ->setFields(array(
                $form->image('image')->setLabel('Hình ảnh'),
                $form->text('tag')->setLabel('Tag trên hình'),
            ));
        echo '<h3>Icon trang trí</h3>';
        echo $form->image('home_library_decor_icon')->setLabel('Icon trang trí thư viện');
    };

    $footer_settings = function () use ($form) {
        echo '<h2>Liên hệ / Footer</h2>';
        echo $form->row(
            $form->text('home_footer_subtitle')->setLabel('Tiêu đề nhỏ'),
            $form->textarea('home_footer_title')->setLabel('Tiêu đề chính')
        );
        echo $form->textarea('home_footer_address')->setLabel('Địa chỉ');
        echo $form->row(
            $form->text('home_footer_hours')->setLabel('Giờ mở cửa'),
            $form->text('home_footer_email')->setLabel('Email')
        );

        echo '<hr><h3>Hotline và Zalo</h3>';
        echo $form->text('home_footer_hotline_title')->setLabel('Tiêu đề');
        echo $form->textarea('home_footer_hotline_description')->setLabel('Mô tả');
        echo $form->row(
            $form->text('home_footer_phone')->setLabel('Số điện thoại'),
            $form->text('home_footer_zalo_url')->setLabel('Link Zalo')->setAttribute('type', 'url')
        );
        echo $form->text('home_footer_location_line')->setLabel('Dòng địa điểm cuối footer');

        echo '<hr><h3>Mạng xã hội</h3>';
        echo $form->row(
            $form->text('home_footer_facebook_url')->setLabel('Facebook URL')->setAttribute('type', 'url'),
            $form->text('home_footer_instagram_url')->setLabel('Instagram URL')->setAttribute('type', 'url'),
            $form->text('home_footer_tiktok_url')->setLabel('TikTok URL')->setAttribute('type', 'url')
        );

        echo '<hr><h3>Nội dung cuối Footer</h3>';
        echo $form->text('home_footer_social_label')
            ->setLabel('Nội dung trước các icon mạng xã hội');

        echo '<hr><h3>Icon Footer</h3>';
        echo $form->row(
            $form->image('home_footer_phone_icon')->setLabel('Icon điện thoại lớn'),
            $form->image('home_footer_decor_icon')->setLabel('Icon trang trí khu vực liên hệ')
        );
        echo $form->row(
            $form->image('home_footer_policy_icon')->setLabel('Icon chính sách'),
            $form->image('home_footer_bottom_icon')->setLabel('Icon trang trí cuối trang')
        );
        echo '<h4>Icon thông tin</h4>';
        echo $form->row(
            $form->image('home_footer_address_icon')->setLabel('Icon địa chỉ'),
            $form->image('home_footer_hours_icon')->setLabel('Icon giờ mở cửa'),
            $form->image('home_footer_email_icon')->setLabel('Icon email')
        );
        echo '<h4>Icon mạng xã hội</h4>';
        echo $form->row(
            $form->image('home_footer_facebook_icon')->setLabel('Icon Facebook'),
            $form->image('home_footer_instagram_icon')->setLabel('Icon Instagram'),
            $form->image('home_footer_tiktok_icon')->setLabel('Icon TikTok')
        );
    };

    $policy_settings = function () use ($form) {
        echo '<h2>Chính sách & Điều khoản</h2>';
        echo '<p style="color:#50575e;margin-bottom:20px;">Cấu hình nội dung cho popup Chính sách & Điều khoản (khi người dùng nhấn nút chính sách ở chân trang).</p>';
        echo $form->text('home_footer_policy_text')
            ->setLabel('Chữ hiển thị ở nút mở chính sách (Footer)')
            ->setAttribute('placeholder', 'CHÍNH SÁCH & ĐIỀU KHOẢN');
        echo $form->text('home_footer_policy_popup_title')
            ->setLabel('Tiêu đề popup chính sách')
            ->setAttribute('placeholder', 'CHÍNH SÁCH & ĐIỀU KHOẢN');
        echo $form->repeater('home_footer_policy_items')
            ->setLabel('Danh sách các chính sách & điều khoản')
            ->setHeadline('nav_title')
            ->setFields(array(
                $form->text('nav_title')->setLabel('Tên mục hiển thị ở cột bên trái (ví dụ: Chính sách bảo mật)'),
                $form->text('title')->setLabel('Tiêu đề chính sách (ví dụ: CHÍNH SÁCH BẢO MẬT)'),
                $form->text('subtitle')->setLabel('Phụ đề / Mô tả ngắn'),
                $form->textarea('content')
                    ->setLabel('Nội dung chi tiết chính sách')
                    ->setAttribute('rows', 8)
                    ->setHelp('Tự động định dạng đoạn văn bản theo các dòng xuống hàng, có thể dùng thẻ HTML (&lt;b&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;...).'),
            ));
    };

    $booking_settings = function () use ($form) {
        echo '<h2>Cài đặt Form Đặt chỗ (Popup Booking)</h2>';
        echo '<p style="color:#50575e;margin-bottom:20px;">Tùy chỉnh tiêu đề, ảnh nền, các nhãn hiển thị và thông tin hotline trên form đặt chỗ.</p>';

        echo '<h3>Header & Thông tin chung</h3>';
        echo $form->row(
            $form->text('home_booking_title')
                ->setLabel('Tiêu đề mặc định của form')
                ->setAttribute('placeholder', 'ĐẶT CHỖ ONLINE'),
            $form->text('home_booking_subtitle')
                ->setLabel('Phụ đề mặc định')
                ->setAttribute('placeholder', 'WONOM')
        );
        echo $form->image('home_booking_header_image')
            ->setLabel('Ảnh nền header popup')
            ->setHelp('Ảnh nền banner phía trên của form popup (mặc định nếu để trống sẽ dùng asset/img/ex_bg_bot.png).');

        echo '<h3>Nhãn & Placeholder các trường nhập</h3>';
        echo $form->row(
            $form->text('home_booking_label_name')->setLabel('Nhãn trường "Họ và tên"')->setAttribute('placeholder', 'HỌ VÀ TÊN'),
            $form->text('home_booking_placeholder_name')->setLabel('Gợi ý (placeholder) Họ và tên')->setAttribute('placeholder', 'Nhập tên của bạn')
        );
        echo $form->row(
            $form->text('home_booking_label_phone')->setLabel('Nhãn trường "Số điện thoại"')->setAttribute('placeholder', 'SỐ ĐIỆN THOẠI'),
            $form->text('home_booking_placeholder_phone')->setLabel('Gợi ý (placeholder) Số điện thoại')->setAttribute('placeholder', 'Nhập số điện thoại')
        );
        echo $form->row(
            $form->text('home_booking_label_floor')->setLabel('Nhãn trường "Chọn tầng"')->setAttribute('placeholder', 'BẠN MUỐN ĐẾN TẦNG NÀO?'),
            $form->text('home_booking_placeholder_floor')->setLabel('Mục chọn mặc định tầng')->setAttribute('placeholder', 'Chọn tầng')
        );
        echo $form->row(
            $form->text('home_booking_label_guests')->setLabel('Nhãn trường "Số người"')->setAttribute('placeholder', 'SỐ NGƯỜI'),
            $form->text('home_booking_placeholder_guests')->setLabel('Gợi ý (placeholder) Số người')->setAttribute('placeholder', 'Chọn số người đến')
        );
        echo $form->row(
            $form->text('home_booking_label_date')->setLabel('Nhãn trường "Chọn ngày"')->setAttribute('placeholder', 'CHỌN NGÀY'),
            $form->text('home_booking_label_time')->setLabel('Nhãn trường "Thời gian đến"')->setAttribute('placeholder', 'THỜI GIAN ĐẾN')
        );

        echo '<h3>Nút gửi & Hotline hỗ trợ</h3>';
        echo $form->text('home_booking_submit_text')
            ->setLabel('Chữ trên nút gửi yêu cầu')
            ->setAttribute('placeholder', 'GỬI YÊU CẦU ĐẶT CHỖ');
        echo $form->textarea('home_booking_note_text')
            ->setLabel('Ghi chú xác nhận dưới nút gửi')
            ->setAttribute('rows', 3)
            ->setAttribute('placeholder', 'Yêu cầu của bạn đã được ghi nhận. Nhân viên sẽ sớm liên hệ xác nhận.');
        echo $form->row(
            $form->text('home_booking_support_divider')
                ->setLabel('Chữ phân cách hỗ trợ')
                ->setAttribute('placeholder', 'HOẶC GỌI HỖ TRỢ'),
            $form->text('home_booking_hotline')
                ->setLabel('Số điện thoại Hotline hỗ trợ')
                ->setAttribute('placeholder', '0968 487 096')
        );
    };

    echo '<div class="wrap">';
    echo '<h1>Wonom · Cài đặt trang chủ</h1>';
    echo '<p>Toàn bộ nội dung cấu hình của homepage sẽ được quản lý tập trung tại đây.</p>';
    if (function_exists('pll_languages_list')) {
        $language_slugs = (array) pll_languages_list(array('fields' => 'slug'));
        $language_names = (array) pll_languages_list(array('fields' => 'name'));
    } else {
        $language_slugs = array('vi', 'en', 'ko');
        $language_names = array('Tiếng Việt', 'English', '한국어');
    }
    if (!$language_slugs) {
        $language_slugs = array('vi', 'en', 'ko');
        $language_names = array('Tiếng Việt', 'English', '한국어');
    }

    echo '<nav class="nav-tab-wrapper" aria-label="Ngôn ngữ trang chủ">';
    foreach ($language_slugs as $index => $language_slug) {
        $language_slug = sanitize_key($language_slug);
        $language_name = isset($language_names[$index]) ? $language_names[$index] : strtoupper($language_slug);
        $language_url = add_query_arg(
            array('page' => 'wonom-homepage-settings', 'lang' => $language_slug),
            admin_url('admin.php')
        );
        $active_class = $language_slug === $language ? ' nav-tab-active' : '';
        echo '<a class="nav-tab' . esc_attr($active_class) . '" href="' . esc_url($language_url) . '">' . esc_html($language_name) . '</a>';
    }
    echo '</nav>';
    echo '<p><strong>Ngôn ngữ đang sửa:</strong> ' . esc_html(strtoupper($language)) . '. ';
    echo $language === $default_language
        ? 'Hình ảnh được quản lý tại ngôn ngữ gốc này.'
        : 'Hình ảnh được dùng chung từ ngôn ngữ gốc; các trường chữ được lưu riêng.';
    echo '</p>';
    echo '<div class="typerocket-container">';
    if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) {
        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt trang chủ.</p></div>';
    }
    echo '<form method="post" action="' . esc_url(add_query_arg(
        array('page' => 'wonom-homepage-settings', 'lang' => $language),
        admin_url('admin.php')
    )) . '">';
    wp_nonce_field('wonom_save_homepage_settings', 'wonom_homepage_nonce');

    tr_tabs()
        ->setSidebar($form->submit('Lưu thay đổi'))
        ->addTab('Header', $header_settings)
        ->addTab('Icon chung', $global_icon_settings)
        ->addTab('Hero', $hero_settings)
        ->addTab('Giới thiệu', $introduce_settings)
        ->addTab('Trải nghiệm', $experience_settings)
        ->addTab('Khám phá', $explore_settings)
        ->addTab('Tour', $tour_settings)
        ->addTab('Sự kiện', $event_settings)
        ->addTab('Thư viện', $library_settings)
        ->addTab('Liên hệ', $footer_settings)
        ->addTab('Chính sách', $policy_settings)
        ->addTab('Đặt chỗ', $booking_settings)
        ->render('box');

    echo '</form>';
    echo '</div>';
    echo '</div>';
}
