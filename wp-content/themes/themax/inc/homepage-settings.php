<?php

/**
 * Dedicated TypeRocket settings screen for the Wonom homepage.
 */

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
    update_option('wonom_home_options', wonom_sanitize_homepage_settings($submitted));
    // Change the homepage fragment-cache key immediately so saved Admin data
    // is visible on the next frontend request instead of waiting five minutes.
    update_option('wonom_home_cache_generation', (string) microtime(true), false);

    wp_safe_redirect(add_query_arg(
        array('page' => 'wonom-homepage-settings', 'settings-updated' => 'true'),
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

        if (is_array($gallery_rows)) {
            foreach ($gallery_rows as $row) {
                $url = $resolve_image(isset($row['image']) ? $row['image'] : '');
                if (!$url) {
                    continue;
                }
                $gallery_images[] = array(
                    'src' => $url,
                    'alt' => isset($row['alt']) ? sanitize_text_field($row['alt']) : '',
                    'tag' => isset($row['tag']) ? sanitize_text_field($row['tag']) : '',
                    'category' => isset($row['category']) ? sanitize_key($row['category']) : 'all',
                );
            }
        }
        if (!$gallery_images) {
            foreach ($defaults['gallery'] as $gallery_index => $filename) {
                $category = 'all';
                $tag = 'tag';
                if (3 === $floor) {
                    $workshop_categories = wonom_get_home_workshop_categories();
                    $fallback_category = $workshop_categories[$gallery_index % count($workshop_categories)];
                    $category = $fallback_category['slug'];
                    $tag = '#' . $fallback_category['label'];
                }
                $gallery_images[] = array('src' => $asset_url . $filename, 'alt' => '', 'tag' => $tag, 'category' => $category);
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
            foreach (wonom_get_home_workshop_categories() as $category) {
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

    $model = new \TypeRocket\Models\WPOption();
    $form = tr_form('option', 'create', null, $model)
        ->setGroup('wonom_home_options');

    $hero_settings = function () use ($form) {
        echo '<h2>Hero</h2>';
        echo '<p>Quản lý các slide trong Hero. Thứ tự trong repeater cũng là thứ tự hiển thị ngoài website.</p>';

        echo $form->repeater('home_hero_slides')
            ->setLabel('Hero Slides')
            ->setFields(array(
                $form->image('image')->setLabel('Ảnh nền'),
                $form->textarea('title')->setLabel('Tiêu đề'),
                $form->text('alt')->setLabel('Alt text của ảnh'),
            ))
            ->setLimit(6);
    };

    $legacy_intro_description = tr_options_field('wonom_home_options.home_intro_description');
    $legacy_intro_caption = tr_options_field('wonom_home_options.home_intro_caption');
    $intro_content_default = sprintf(
        '<p>%s</p><p><strong>%s</strong></p>',
        esc_html($legacy_intro_description ?: 'Không gian chạm và tận hưởng văn hóa đời thường Hàn Quốc ngay tại Sài Gòn, mở ra nhịp cầu kết nối văn hóa hai quốc gia.'),
        esc_html($legacy_intro_caption ?: 'Tổ hợp 5 tầng trải nghiệm Ẩm thực, Hanbok, Workshop thủ công và giải trí Hàn Quốc độc đáo.')
    );

    $introduce_settings = function () use ($form, $intro_content_default) {
        echo '<h2>Giới thiệu</h2>';
        echo '<p>Quản lý nội dung section Giới thiệu trên trang chủ. Ba hình ảnh tương ứng với ba card từ trái sang phải.</p>';

        echo $form->text('home_intro_eyebrow')
            ->setLabel('Tiêu đề nhỏ')
            ->setDefault('CÂU CHUYỆN CỦA WONOM!');

        echo $form->textarea('home_intro_title')
            ->setLabel('Tiêu đề chính')
            ->setDefault("Văn hóa trở nên\nrộng lớn hơn khi\nđược sẻ chia.");

        echo $form->editor('home_intro_content')
            ->setLabel('Nội dung')
            ->setDefault($intro_content_default);

        echo '<h3>Hình ảnh các card</h3>';
        echo $form->row(
            $form->image('home_intro_image_1')->setLabel('Hình card 1'),
            $form->text('home_intro_image_1_alt')->setLabel('Alt text card 1')
        );
        echo $form->row(
            $form->image('home_intro_image_2')->setLabel('Hình card 2'),
            $form->text('home_intro_image_2_alt')->setLabel('Alt text card 2')
        );
        echo $form->row(
            $form->image('home_intro_image_3')->setLabel('Hình card 3'),
            $form->text('home_intro_image_3_alt')->setLabel('Alt text card 3')
        );
    };

    $experience_settings = function () use ($form) {
        echo '<h2>Trải nghiệm</h2>';
        echo '<p>Quản lý 5 card tầng. Vị trí và liên kết tới từng tab Khám phá được giữ cố định.</p>';

        echo $form->row(
            $form->image('home_experience_middle_image')->setLabel('Hình Wonom ở giữa'),
            $form->text('home_experience_middle_image_alt')->setLabel('Alt text hình giữa')
        );

        echo $form->text('home_experience_marquee_text')
            ->setLabel('Nội dung chữ chạy')
            ->setDefault('trải nghiệm wonom 5 sao!!!');

        for ($floor = 1; $floor <= 5; $floor++) {
            echo '<hr><h3>Card tầng ' . esc_html($floor) . '</h3>';
            echo $form->row(
                $form->text('home_experience_floor_' . $floor . '_label')
                    ->setLabel('Tên tầng')
                    ->setDefault($floor . 'F'),
                $form->text('home_experience_floor_' . $floor . '_title')
                    ->setLabel('Tiêu đề')
                    ->setDefault('Nhà hàng Mujige')
            );
            echo $form->row(
                $form->image('home_experience_floor_' . $floor . '_image')->setLabel('Hình ảnh'),
                $form->text('home_experience_floor_' . $floor . '_image_alt')->setLabel('Alt text hình ảnh')
            );
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
            ->setLabel('Tiêu đề thanh chọn tầng')
            ->setDefault('Hãy chọn tầng.. bạn muốn đến!!!');

        $floor_contents = array();

        foreach ($explore_floor_defaults as $floor => $defaults) {
            ob_start();
            (static function () use ($form, $floor, $defaults) {
                $prefix = 'home_explore_floor_' . $floor . '_';

                echo '<h3>Nội dung tầng ' . esc_html($floor) . 'F</h3>';
                echo $form->row(
                    $form->text($prefix . 'nav_title')->setLabel('Tên trên menu tầng')->setDefault($defaults['nav']),
                    $form->text($prefix . 'subtitle')->setLabel('Tiêu đề sticky')->setDefault($defaults['subtitle'])
                );
                echo $form->textarea($prefix . 'title')->setLabel('Tiêu đề chính')->setDefault($defaults['title']);
                echo $form->editor($prefix . 'description')->setLabel('Mô tả')->setDefault($defaults['description']);
                echo $form->textarea($prefix . 'tags')
                    ->setLabel('Tags (phân cách bằng dấu phẩy hoặc xuống dòng)')
                    ->setDefault($defaults['tags']);
                echo $form->row(
                    $form->text($prefix . 'meta_1_label')->setLabel('Nhãn thông tin 1')->setDefault($defaults['meta_1_label']),
                    $form->text($prefix . 'meta_1_value')->setLabel('Nội dung thông tin 1')->setDefault($defaults['meta_1_value'])
                );

                if ($floor >= 3) {
                    echo $form->row(
                        $form->text($prefix . 'meta_2_label')->setLabel('Nhãn thông tin 2')->setDefault($defaults['meta_2_label']),
                        $form->text($prefix . 'meta_2_value')->setLabel('Nội dung thông tin 2')->setDefault($defaults['meta_2_value'])
                    );
                }

                echo $form->row(
                    $form->text($prefix . 'primary_button')->setLabel('Nút xem thông tin')->setDefault($defaults['primary']),
                    $form->text($prefix . 'booking_button')->setLabel('Nút đặt lịch')->setDefault($defaults['booking'])
                );

                if (3 === $floor) {
                    echo '<hr><h3>Danh mục và sản phẩm Workshop</h3>';
                    echo '<p>Tạo danh mục một lần tại đây. Mã danh mục này được dùng chung cho bộ lọc sản phẩm và gallery popup.</p>';
                    echo $form->repeater('home_explore_floor_3_categories')
                        ->setLabel('Danh mục Workshop 3F')
                        ->setHeadline('label')
                        ->setFields(array(
                            $form->text('label')->setLabel('Tên hiển thị, ví dụ: NÓN'),
                            $form->text('slug')->setLabel('Mã danh mục, ví dụ: hat'),
                        ));
                    echo '<datalist id="wonom-workshop-category-options">';
                    foreach (wonom_get_home_workshop_categories() as $category) {
                        echo '<option value="' . esc_attr($category['slug']) . '">' . esc_html($category['label']) . '</option>';
                    }
                    echo '</datalist>';
                    echo $form->repeater($prefix . 'workshops')
                        ->setLabel('Danh sách sản phẩm và popup Product Detail')
                        ->setHeadline('title')
                        ->setFields(array(
                            $form->rowText('<h4>Thông tin hiển thị trên danh sách sản phẩm</h4>'),
                            $form->image('image')->setLabel('Hình card sản phẩm'),
                            $form->text('image_alt')->setLabel('Alt text'),
                            $form->text('category')->setLabel('Nhóm sản phẩm / Tag trên hình')->setAttribute('list', 'wonom-workshop-category-options'),
                            $form->text('title')->setLabel('Tên sản phẩm'),
                            $form->textarea('description')->setLabel('Mô tả ngắn trên card'),
                            $form->text('price')->setLabel('Giá sản phẩm'),
                            $form->rowText('<hr><h4>Popup Product Detail</h4>'),
                            $form->gallery('detail_images')->setLabel('Gallery ảnh chi tiết'),
                            $form->editor('detail_content')->setLabel('Nội dung giới thiệu chi tiết'),
                            $form->editor('detail_includes')->setLabel('Dụng cụ bao gồm'),
                            $form->text('detail_duration')->setLabel('Thời lượng')->setDefault('60-90 phút'),
                        ));
                }

                echo '<hr><h3>Popup tầng ' . esc_html($floor) . 'F</h3>';
                if (3 === $floor) {
                    echo $form->text($prefix . 'popup_title')->setLabel('Tiêu đề popup')->setDefault(str_replace(' - ', ' · ', $defaults['subtitle']));
                } else {
                    echo $form->row(
                        $form->text($prefix . 'popup_title')->setLabel('Tiêu đề popup')->setDefault(str_replace(' - ', ' · ', $defaults['subtitle'])),
                        $form->text($prefix . 'popup_menu_label')->setLabel('Tên tab menu/bảng giá')->setDefault($floor === 1 ? 'THỰC ĐƠN' : ($floor === 5 ? 'MENU' : 'BẢNG GIÁ'))
                    );
                }

                if (3 !== $floor) {
                    echo $form->repeater($prefix . 'popup_menu_pages')
                        ->setLabel('Các trang menu/bảng giá trong popup')
                        ->setFields(array(
                            $form->image('image')->setLabel('Hình trang'),
                        ));
                }

                $popup_gallery_fields = array(
                    $form->image('image')->setLabel('Hình ảnh'),
                    $form->text('alt')->setLabel('Alt text'),
                    $form->text('tag')->setLabel('Tag hiển thị'),
                );
                if (3 === $floor) {
                    $popup_gallery_fields[] = $form->text('category')
                        ->setLabel('Nhóm hiển thị trong bộ lọc')
                        ->setAttribute('list', 'wonom-workshop-category-options');
                }
                echo $form->repeater($prefix . 'popup_gallery')
                    ->setLabel('Thư viện ảnh popup')
                    ->setFields($popup_gallery_fields);

                $booking_popup_titles = array(1 => 'ĐẶT BÀN ONLINE', 2 => 'ĐẶT LỊCH THUÊ', 3 => 'ĐẶT LỊCH WORKSHOP', 4 => 'ĐẶT LỊCH HẸN', 5 => 'ĐẶT CHỖ ONLINE');
                $booking_popup_submits = array(1 => 'GỬI YÊU CẦU ĐẶT LỊCH', 2 => 'GỬI YÊU CẦU ĐẶT LỊCH', 3 => 'GỬI YÊU CẦU ĐẶT LỊCH', 4 => 'GỬI YÊU CẦU ĐẶT LỊCH', 5 => 'GỬI YÊU CẦU ĐẶT CHỖ');
                echo '<h4>Popup đặt lịch</h4>';
                echo $form->row(
                    $form->text($prefix . 'booking_title')->setLabel('Tiêu đề popup đặt lịch')->setDefault($booking_popup_titles[$floor]),
                    $form->text($prefix . 'booking_submit')->setLabel('Nội dung nút gửi')->setDefault($booking_popup_submits[$floor])
                );

                if (3 !== $floor) {
                    echo $form->repeater($prefix . 'gallery')
                        ->setLabel('Hình ảnh slider')
                        ->setFields(array(
                            $form->image('image')->setLabel('Hình ảnh'),
                            $form->text('alt')->setLabel('Alt text'),
                        ));
                }
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
            @media(max-width:782px){.wonom-floor-tabs{grid-template-columns:1fr}.wonom-floor-tabs__nav{display:flex;overflow-x:auto;border-right:0;border-bottom:1px solid #ddd}.wonom-floor-tabs__button{width:auto;min-width:64px;border-left:0;border-bottom:3px solid transparent;text-align:center}.wonom-floor-tabs__button.is-active{border-bottom-color:#2271b1}}
        </style>';
        echo '<script>
            (function(){
                var root = document.currentScript.previousElementSibling.previousElementSibling;
                if (!root || !root.classList.contains("wonom-floor-tabs")) return;
                var categoryList = root.querySelector("#wonom-workshop-category-options");
                var defaultCategories = categoryList ? Array.from(categoryList.options).map(function(option){
                    return { slug: option.value, label: option.textContent };
                }) : [];

                function syncWorkshopCategories(){
                    if (!categoryList) return;
                    var categories = [];
                    root.querySelectorAll("input[name*=\"[home_explore_floor_3_categories]\"][name$=\"[slug]\"]").forEach(function(slugInput){
                        var group = slugInput.closest(".tr-repeater-group");
                        var labelInput = group ? group.querySelector("input[name$=\"[label]\"]") : null;
                        var slug = slugInput.value.trim().toLowerCase().replace(/[^a-z0-9_-]+/g, "-").replace(/^-+|-+$/g, "");
                        if (slug) categories.push({ slug: slug, label: labelInput && labelInput.value.trim() ? labelInput.value.trim() : slug });
                    });
                    if (!categories.length) categories = defaultCategories;
                    categoryList.replaceChildren();
                    categories.forEach(function(category){
                        var option = document.createElement("option");
                        option.value = category.slug;
                        option.textContent = category.label;
                        categoryList.appendChild(option);
                    });
                }

                function makeCategorySlug(value){
                    return value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
                }

                root.addEventListener("click", function(event){
                    var button = event.target.closest("[data-wonom-floor-tab]");
                    if (!button || !root.contains(button)) {
                        if (event.target.closest(".tr-repeater .add, .tr-repeater .remove")) {
                            window.setTimeout(syncWorkshopCategories, 50);
                        }
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
                root.addEventListener("input", function(event){
                    if (event.target.matches("input[name*=\"[home_explore_floor_3_categories]\"]")) {
                        if (event.target.name.endsWith("[label]")) {
                            var group = event.target.closest(".tr-repeater-group");
                            var slugInput = group ? group.querySelector("input[name$=\"[slug]\"]") : null;
                            if (slugInput && (!slugInput.value || slugInput.dataset.autoCategorySlug === "true")) {
                                slugInput.value = makeCategorySlug(event.target.value);
                                slugInput.dataset.autoCategorySlug = "true";
                            }
                        } else if (event.target.name.endsWith("[slug]")) {
                            event.target.dataset.autoCategorySlug = "false";
                        }
                        syncWorkshopCategories();
                    }
                });
                syncWorkshopCategories();
            })();
        </script>';
    };

    $tour_settings = function () use ($form) {
        echo '<h2>Tour tham quan</h2>';
        echo '<p>Tạo nhiều tour. Mỗi tour có hình hiển thị trên slider và toàn bộ nội dung popup chi tiết riêng.</p>';

        echo $form->repeater('home_tours')
            ->setLabel('Danh sách Tour')
            ->setHeadline('title')
            ->setFields(array(
                $form->rowText('<h4>Thông tin hiển thị trên slider</h4>'),
                $form->image('image')->setLabel('Hình đại diện Tour'),
                $form->text('image_alt')->setLabel('Alt text hình đại diện'),
                $form->text('title')->setLabel('Tên tour'),
                $form->row(
                    $form->text('duration')->setLabel('Thời gian')->setDefault('120 phút'),
                    $form->text('price')->setLabel('Chi phí')->setDefault('2,000,000đ/người')
                ),
                $form->rowText('<hr><h4>Popup chi tiết Tour</h4>'),
                $form->image('popup_image')->setLabel('Hình trong popup'),
                $form->text('popup_image_alt')->setLabel('Alt text hình trong popup'),
                $form->text('intro_title')->setLabel('Tiêu đề giới thiệu')->setDefault('Giới thiệu tour'),
                $form->editor('intro_content')->setLabel('Nội dung giới thiệu'),
                $form->text('itinerary_title')->setLabel('Tiêu đề lịch trình')->setDefault('LỊCH TRÌNH TRẢI NGHIỆM'),
                $form->editor('itinerary_content')->setLabel('Nội dung lịch trình'),
                $form->text('notes_title')->setLabel('Tiêu đề lưu ý')->setDefault('THÔNG TIN CẦN LƯU Ý'),
                $form->editor('notes_content')->setLabel('Nội dung lưu ý'),
                $form->text('policy_title')->setLabel('Tiêu đề chính sách')->setDefault('CHÍNH SÁCH ĐẶT LỊCH'),
                $form->editor('policy_content')->setLabel('Nội dung chính sách'),
            ));

    };

    $event_settings = function () use ($form) {
        echo '<h2>Sự kiện đang diễn ra</h2>';
        echo $form->row(
            $form->text('home_event_subtitle')->setLabel('Tiêu đề nhỏ')->setDefault('ĐANG DIỄN RA'),
            $form->textarea('home_event_title')->setLabel('Tiêu đề chính')->setDefault("Sự kiện đang diễn ra &\nchương trình theo mùa")
        );
        echo $form->repeater('home_events')
            ->setLabel('Danh sách sự kiện')
            ->setHeadline('title')
            ->setFields(array(
                $form->rowText('<h4>Thông tin hiển thị trên card</h4>'),
                $form->image('image')->setLabel('Hình card'),
                $form->text('image_alt')->setLabel('Alt text hình card'),
                $form->text('apply')->setLabel('Thời gian áp dụng, ví dụ: THƯỜNG XUYÊN'),
                $form->text('title')->setLabel('Tên sự kiện'),
                $form->textarea('subtitle')->setLabel('Mô tả ngắn'),
                $form->text('category')->setLabel('Danh mục, ví dụ: ROOFTOP'),
                $form->rowText('<hr><h4>Popup chi tiết sự kiện</h4>'),
                $form->text('condition')->setLabel('Điều kiện áp dụng'),
                $form->editor('content')->setLabel('Nội dung chi tiết'),
            ));
    };

    $library_settings = function () use ($form) {
        echo '<h2>Không gian / Thư viện ảnh</h2>';
        echo $form->text('home_library_subtitle')->setLabel('Tiêu đề nhỏ')->setDefault('BÊN TRONG WONOM THẾ NÀO!');
        echo $form->textarea('home_library_title')->setLabel('Tiêu đề chính')->setDefault('Những khoảnh khắc của không gian.');
        echo $form->textarea('home_library_description')->setLabel('Mô tả')->setDefault('Không gian chạm và tận hưởng văn hóa đời thường Hàn Quốc ngay tại Sài Gòn, mở ra nhịp cầu kết nối văn hóa hai quốc gia.');
        echo $form->repeater('home_library_images')
            ->setLabel('Danh sách hình ảnh')
            ->setHeadline('tag')
            ->setFields(array(
                $form->image('image')->setLabel('Hình ảnh'),
                $form->text('image_alt')->setLabel('Alt text'),
                $form->text('tag')->setLabel('Tag trên hình'),
            ));
    };

    $footer_settings = function () use ($form) {
        echo '<h2>Liên hệ / Footer</h2>';
        echo $form->row(
            $form->text('home_footer_subtitle')->setLabel('Tiêu đề nhỏ')->setDefault('GHÉ THĂM'),
            $form->textarea('home_footer_title')->setLabel('Tiêu đề chính')->setDefault("Hẹn gặp bạn\ntại WONOM - HCMC")
        );
        echo $form->textarea('home_footer_address')->setLabel('Địa chỉ')->setDefault('40A Trần Cao Vân, Phường Xuân Hòa, TP Hồ Chí Minh, Việt Nam');
        echo $form->row(
            $form->text('home_footer_hours')->setLabel('Giờ mở cửa')->setDefault('Hằng ngày 11:00 – 23:00 & Rooftop đến 01:00'),
            $form->text('home_footer_email')->setLabel('Email')->setDefault('support@wonom.vn')
        );

        echo '<hr><h3>Hotline và Zalo</h3>';
        echo $form->text('home_footer_hotline_title')->setLabel('Tiêu đề')->setDefault('GỌI HOTLINE TƯ VẤN');
        echo $form->textarea('home_footer_hotline_description')->setLabel('Mô tả')->setDefault('Gọi Hotline hoặc liên hệ Zalo để giữ chỗ nhanh nhất ngay!!!');
        echo $form->row(
            $form->text('home_footer_phone')->setLabel('Số điện thoại')->setDefault('0968 487 096'),
            $form->text('home_footer_zalo_url')->setLabel('Link Zalo')->setAttribute('type', 'url')
        );
        echo $form->text('home_footer_location_line')->setLabel('Dòng địa điểm cuối footer')->setDefault('안녕! Ban Ga Wo · Ho Chi Minh City, Vietnam');

        echo '<hr><h3>Mạng xã hội</h3>';
        echo $form->row(
            $form->text('home_footer_facebook_url')->setLabel('Facebook URL')->setAttribute('type', 'url'),
            $form->text('home_footer_instagram_url')->setLabel('Instagram URL')->setAttribute('type', 'url'),
            $form->text('home_footer_tiktok_url')->setLabel('TikTok URL')->setAttribute('type', 'url')
        );
    };

    echo '<div class="wrap">';
    echo '<h1>Wonom · Cài đặt trang chủ</h1>';
    echo '<p>Toàn bộ nội dung cấu hình của homepage sẽ được quản lý tập trung tại đây.</p>';
    echo '<div class="typerocket-container">';
    if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) {
        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt trang chủ.</p></div>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=wonom-homepage-settings')) . '">';
    wp_nonce_field('wonom_save_homepage_settings', 'wonom_homepage_nonce');

    tr_tabs()
        ->setSidebar($form->submit('Lưu thay đổi'))
        ->addTab('Hero', $hero_settings)
        ->addTab('Giới thiệu', $introduce_settings)
        ->addTab('Trải nghiệm', $experience_settings)
        ->addTab('Khám phá', $explore_settings)
        ->addTab('Tour', $tour_settings)
        ->addTab('Sự kiện', $event_settings)
        ->addTab('Thư viện', $library_settings)
        ->addTab('Liên hệ', $footer_settings)
        ->render('box');

    echo '</form>';
    echo '</div>';
    echo '</div>';
}
