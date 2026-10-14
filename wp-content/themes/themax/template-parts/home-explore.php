<?php

ob_start();
get_template_part('template-parts/layouts/home', 'explore');
$explore_markup = ob_get_clean();

if (!$explore_markup || !class_exists('DOMDocument')) {
    echo $explore_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$explore_defaults = array(
    1 => array('nav' => 'Nhà hàng', 'subtitle' => '1F - NHÀ HÀNG MUJIGE', 'title' => "Ẩm thực fusion\nHàn-Việt", 'description' => 'Thưởng thức shabu-shabu, bulgogi và kimbap dưới bức họa pop-art khổng lồ.', 'tags' => '#Không gian, #Ẩm thực, #Hàn Quốc', 'meta_1_label' => 'GIỜ MỞ CỬA:', 'meta_1_value' => 'Hằng ngày từ 11:00 đến 23:00', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM THỰC ĐƠN', 'booking' => 'ĐẶT BÀN GIỮ CHỖ'),
    2 => array('nav' => 'Culture Studio', 'subtitle' => '2F - TRANG PHỤC TRUYỀN THỐNG', 'title' => 'CHO THUÊ HANBOK VÁY + STUDIO CHỤP ẢNH', 'description' => 'Thuê hanbok, váy và tự chụp những bức ảnh nghệ thuật với ánh sáng chuyên nghiệp.', 'tags' => '#Trải nghiệm hanbok, #Váy, #Không gian, #Selfie', 'meta_1_label' => 'GIỜ THUÊ:', 'meta_1_value' => '60 phút/người', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH THUÊ'),
    3 => array('nav' => 'Workshop', 'subtitle' => '3F - WORKSHOP', 'title' => "XƯỞNG LÀM ĐỒ\nTHỦ CÔNG Ở WONOM", 'description' => 'Tự tay tạo nên món quà mang dấu ấn riêng. Trải nghiệm lớp học thủ công, thỏa sức sáng tạo và mang về tác phẩm do chính bạn hoàn thành.', 'tags' => '#Lớp thủ công, #Quà lưu niệm, #Đồ handmade', 'meta_1_label' => 'THỜI GIAN', 'meta_1_value' => '60 phút', 'meta_2_label' => 'SỐ LƯỢNG', 'meta_2_value' => '200+ Mẫu', 'primary' => 'KHÁM PHÁ NGAY', 'booking' => 'ĐẶT LỊCH HẸN'),
    4 => array('nav' => 'Stress Room', 'subtitle' => '4F - STRESS ROOM', 'title' => "PHÒNG GIẢI TỎA\nCƠN GIẬN", 'description' => 'Phòng giải tỏa căng thẳng bằng cách đập phá đồ vật trong không gian riêng biệt, trang bị đầy đủ đồ bảo hộ an toàn.', 'tags' => '#Đồ bảo hộ, #Theo suất, #Ném chai bia', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH HẸN'),
    5 => array('nav' => 'Rooftop Disco', 'subtitle' => '5F - ROOFTOP DISCO', 'title' => "QUẦY BAR MIRROR BALL\n· LOUNGE VỀ ĐÊM", 'description' => 'Tận hưởng những ly cocktail đầy cảm hứng dưới ánh đèn neon và mirror ball sôi động, thư giãn và ngắm nhìn thành phố về đêm.', 'tags' => '#Soju-cocktail, #Chill, #View thành phố, #Nhạc', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM MENU', 'booking' => 'ĐẶT CHỖ NHANH'),
);

$explore_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$explore_saved_options = get_option('wonom_home_options', array());
$explore_saved_options = is_array($explore_saved_options) ? $explore_saved_options : array();
$explore_optional_text = static function ($field, $fallback = '') use ($explore_saved_options) {
    if (array_key_exists($field, $explore_saved_options)) {
        return is_scalar($explore_saved_options[$field]) ? trim((string) $explore_saved_options[$field]) : '';
    }
    return $fallback;
};

$explore_image_url = static function ($image) {
    if (is_numeric($image)) {
        return wp_get_attachment_image_url((int) $image, 'full') ?: '';
    }
    if (is_array($image)) {
        $attachment_id = isset($image['id']) ? (int) $image['id'] : 0;
        $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'full') : '';
        return $url ?: (isset($image['url']) ? $image['url'] : '');
    }
    return is_string($image) ? $image : '';
};

$explore_dom = new DOMDocument('1.0', 'UTF-8');
$previous_libxml_state = libxml_use_internal_errors(true);
$explore_dom->loadHTML(
    '<?xml encoding="utf-8" ?><div id="wonom-explore-root">' . $explore_markup . '</div>',
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
);
libxml_clear_errors();
libxml_use_internal_errors($previous_libxml_state);
$explore_xpath = new DOMXPath($explore_dom);

$explore_first = static function ($query, $context = null) use ($explore_xpath) {
    $nodes = $explore_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};

$explore_set_text = static function ($node, $text) {
    if ($node) {
        $node->nodeValue = $text;
    }
};

$explore_set_html = static function ($node, $html) use ($explore_dom) {
    if (!$node) {
        return;
    }

    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }

    $fragment_dom = new DOMDocument('1.0', 'UTF-8');
    $previous_state = libxml_use_internal_errors(true);
    $fragment_dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="wonom-fragment">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_state);

    $fragment_root = $fragment_dom->getElementById('wonom-fragment');
    if (!$fragment_root) {
        return;
    }

    foreach (iterator_to_array($fragment_root->childNodes) as $child) {
        $node->appendChild($explore_dom->importNode($child, true));
    }
};

$class_query = static function ($class_name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $class_name . ' ")';
};

$explore_popup_data = function_exists('wonom_get_home_explore_popup_data')
    ? wonom_get_home_explore_popup_data()
    : array();

$sidebar_title = $explore_first('//*[@id="explore"]//*[contains(concat(" ", normalize-space(@class), " "), " home_explore_sidebar_title ")]');
$explore_set_text($sidebar_title, $explore_option('home_explore_sidebar_title', 'Hãy chọn tầng.. bạn muốn đến!!!'));

foreach ($explore_defaults as $floor => $defaults) {
    $prefix = 'home_explore_floor_' . $floor . '_';
    $tab_key = 'tab' . $floor . 'f';
    $nav_title = $explore_option($prefix . 'nav_title', $defaults['nav']);
    $subtitle = $explore_option($prefix . 'subtitle', $defaults['subtitle']);
    $title = $explore_option($prefix . 'title', $defaults['title']);
    $description = $explore_option($prefix . 'description', $defaults['description']);
    $tags_value = $explore_option($prefix . 'tags', $defaults['tags']);

    $sidebar_item = $explore_first('//*[@data-tab="' . $tab_key . '" and ' . $class_query('home_explore_sidebar_tab_item') . ']');
    if ($sidebar_item) {
        $explore_set_text($explore_first('.//*[' . $class_query('home_explore_list_card_item_content_num') . ']', $sidebar_item), $floor . 'F');
        $explore_set_text($explore_first('.//*[' . $class_query('home_explore_list_card_item_content_txt') . ']', $sidebar_item), $nav_title);
    }

    $floor_section = $explore_first('//*[@data-floor-section="' . $tab_key . '"]');
    if (!$floor_section) {
        continue;
    }

    $subtitle_nodes = $explore_xpath->query('.//*[' . $class_query('home_explore_content_subtitle') . ']', $floor_section);
    if ($subtitle_nodes) {
        foreach ($subtitle_nodes as $subtitle_node) {
            $explore_set_text($subtitle_node, $subtitle);
        }
    }

    $explore_set_html(
        $explore_first('.//*[' . $class_query('home_explore_content_title') . ']', $floor_section),
        nl2br(esc_html($title))
    );
    $explore_set_html(
        $explore_first('.//*[' . $class_query('home_explore_content_des') . ']', $floor_section),
        wp_kses_post($description)
    );

    $tags_wrap = $explore_first('.//*[' . $class_query('home_explore_content_tag') . ']', $floor_section);
    if ($tags_wrap) {
        while ($tags_wrap->firstChild) {
            $tags_wrap->removeChild($tags_wrap->firstChild);
        }
        $tags = preg_split('/[\r\n,]+/', $tags_value);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ('' === $tag) {
                continue;
            }
            $tag_node = $explore_dom->createElement('div');
            $tag_node->setAttribute('class', 'home_explore_content_tag_item txt_14 txt_bold');
            $tag_node->appendChild($explore_dom->createTextNode($tag));
            $tags_wrap->appendChild($tag_node);
        }
    }

    $meta_wrap = $explore_first('.//*[' . $class_query('workshop_meta') . ']', $floor_section);
    $single_meta_label = $explore_first('.//*[' . $class_query('home_explore_content_time') . ']', $floor_section);
    $single_meta_value = $explore_first('.//*[' . $class_query('home_explore_content_txttime') . ']', $floor_section);
    if (!$meta_wrap && $single_meta_label && $single_meta_label->parentNode) {
        $meta_wrap = $explore_dom->createElement('div');
        $meta_wrap->setAttribute('class', 'workshop_meta');
        $single_meta_label->parentNode->insertBefore($meta_wrap, $single_meta_label);
        $single_meta_label->parentNode->removeChild($single_meta_label);
        if ($single_meta_value && $single_meta_value->parentNode) {
            $single_meta_value->parentNode->removeChild($single_meta_value);
        }
    }

    if ($meta_wrap) {
        while ($meta_wrap->firstChild) {
            $meta_wrap->removeChild($meta_wrap->firstChild);
        }

        for ($meta_index = 1; $meta_index <= 2; $meta_index++) {
            $meta_label = $explore_optional_text($prefix . 'meta_' . $meta_index . '_label', $defaults['meta_' . $meta_index . '_label']);
            $meta_value = $explore_optional_text($prefix . 'meta_' . $meta_index . '_value', $defaults['meta_' . $meta_index . '_value']);
            if ('' === $meta_label && '' === $meta_value) {
                continue;
            }

            $meta_item = $explore_dom->createElement('div');
            $meta_item->setAttribute('class', 'workshop_meta_item');
            if ('' !== $meta_label) {
                $label_node = $explore_dom->createElement('div');
                $label_node->setAttribute('class', 'txt_13 cl_meta');
                $label_node->appendChild($explore_dom->createTextNode($meta_label));
                $meta_item->appendChild($label_node);
            }
            if ('' !== $meta_value) {
                $value_node = $explore_dom->createElement('div');
                $value_node->setAttribute('class', 'txt_bold');
                $value_node->appendChild($explore_dom->createTextNode($meta_value));
                $meta_item->appendChild($value_node);
            }
            $meta_wrap->appendChild($meta_item);
        }

        if (!$meta_wrap->hasChildNodes() && $meta_wrap->parentNode) {
            $meta_wrap->parentNode->removeChild($meta_wrap);
        }
    }

    $primary_button = $explore_first('.//*[' . $class_query('home_explore_content_button_see') . ']', $floor_section);
    $booking_button = $explore_first('.//*[' . $class_query('home_explore_content_button_book') . ']', $floor_section);
    $explore_set_text($explore_first('.//*[' . $class_query('btn-inner') . ']', $primary_button), $explore_option($prefix . 'primary_button', $defaults['primary']));
    $explore_set_text($explore_first('.//*[' . $class_query('btn-inner') . ']', $booking_button), $explore_option($prefix . 'booking_button', $defaults['booking']));
    if ($booking_button) {
        $booking_title_defaults = array(1 => 'ĐẶT BÀN ONLINE', 2 => 'ĐẶT LỊCH THUÊ', 3 => 'ĐẶT LỊCH WORKSHOP', 4 => 'ĐẶT LỊCH HẸN', 5 => 'ĐẶT CHỖ ONLINE');
        $booking_submit_defaults = array(1 => 'GỬI YÊU CẦU ĐẶT LỊCH', 2 => 'GỬI YÊU CẦU ĐẶT LỊCH', 3 => 'GỬI YÊU CẦU ĐẶT LỊCH', 4 => 'GỬI YÊU CẦU ĐẶT LỊCH', 5 => 'GỬI YÊU CẦU ĐẶT CHỖ');
        $booking_button->setAttribute('data-booking-title', $explore_option($prefix . 'booking_title', $booking_title_defaults[$floor]));
        $booking_button->setAttribute('data-booking-subtitle', $subtitle);
        $booking_button->setAttribute('data-booking-submit', $explore_option($prefix . 'booking_submit', $booking_submit_defaults[$floor]));
        $booking_button->setAttribute('data-booking-venue', $nav_title);
    }

    if (3 === $floor) {
        $product_filters_wrap = $explore_first('.//*[' . $class_query('workshop_catalog_filters') . ']', $floor_section);
        if ($product_filters_wrap) {
            while ($product_filters_wrap->firstChild) {
                $product_filters_wrap->removeChild($product_filters_wrap->firstChild);
            }
            $product_filters = array_merge(
                array(array('slug' => 'all', 'label' => 'TẤT CẢ')),
                function_exists('wonom_get_used_workshop_categories')
                    ? wonom_get_used_workshop_categories()
                    : wonom_get_home_workshop_categories()
            );
            foreach ($product_filters as $filter_index => $filter) {
                $filter_button = $explore_dom->createElement('button');
                $filter_button->setAttribute('class', 'workshop_filter btn txt_14 txt_bold' . (0 === $filter_index ? ' active' : ''));
                $filter_button->setAttribute('type', 'button');
                $filter_button->setAttribute('data-workshop-filter', $filter['slug']);
                $filter_inner = $explore_dom->createElement('span');
                $filter_inner->setAttribute('class', 'btn-inner');
                $filter_inner->appendChild($explore_dom->createTextNode($filter['label']));
                $filter_button->appendChild($filter_inner);
                $product_filters_wrap->appendChild($filter_button);
            }
        }

        $workshop_category_labels = array();
        $used_workshop_categories = function_exists('wonom_get_used_workshop_categories')
            ? wonom_get_used_workshop_categories()
            : wonom_get_home_workshop_categories();
        foreach ($used_workshop_categories as $workshop_category) {
            $workshop_category_labels[$workshop_category['slug']] = $workshop_category['label'];
        }

        $workshops = function_exists('wonom_get_workshop_products')
            ? wonom_get_workshop_products()
            : $explore_option($prefix . 'workshops', array());
        $workshop_list = $explore_first('.//*[' . $class_query('workshop_catalog_list') . ']', $floor_section);
        $prototype = $workshop_list ? $explore_first('.//*[' . $class_query('workshop_card') . ']', $workshop_list) : null;

        if (is_array($workshops) && $workshops && $workshop_list && $prototype) {
            while ($workshop_list->firstChild) {
                $workshop_list->removeChild($workshop_list->firstChild);
            }
            foreach ($workshops as $index => $workshop) {
                $card = $prototype->cloneNode(true);
                $category_slugs = !empty($workshop['categories']) && is_array($workshop['categories'])
                    ? array_values(array_filter(array_map('sanitize_key', $workshop['categories'])))
                    : array(!empty($workshop['category']) ? sanitize_key($workshop['category']) : 'all');
                $category = $category_slugs ? $category_slugs[0] : 'all';
                $workshop_tag = isset($workshop_category_labels[$category])
                    ? '#' . ltrim($workshop_category_labels[$category], '#')
                    : (!empty($workshop['tag']) ? sanitize_text_field($workshop['tag']) : '#' . $category);
                $workshop_title = isset($workshop['title']) ? $workshop['title'] : '';
                $product_id = !empty($workshop['id']) ? (int) $workshop['id'] : ($index + 1);
                $card->setAttribute('data-product-id', 'workshop-' . $category . '-' . $product_id);
                $card->setAttribute('data-workshop-category', implode(' ', $category_slugs));
                $detail_images = array();
                if (!empty($workshop['detail_images']) && is_array($workshop['detail_images'])) {
                    foreach ($workshop['detail_images'] as $detail_image) {
                        $detail_image_url = $explore_image_url($detail_image);
                        if ($detail_image_url) {
                            $detail_images[] = $detail_image_url;
                        }
                    }
                }
                $card->setAttribute('data-detail-images', wp_json_encode($detail_images));
                $card->setAttribute('data-detail-content', isset($workshop['detail_content']) ? wp_kses_post($workshop['detail_content']) : '');
                $card->setAttribute('data-detail-includes', isset($workshop['detail_includes']) ? wp_kses_post($workshop['detail_includes']) : '');
                $card->setAttribute('data-detail-duration', isset($workshop['detail_duration']) ? sanitize_text_field($workshop['detail_duration']) : '60-90 phút');
                $image_node = $explore_first('.//*[' . $class_query('workshop_card_image') . ']//img', $card);
                $image_url = $explore_image_url(isset($workshop['image']) ? $workshop['image'] : '');
                if ($image_node && $image_url) {
                    $image_node->setAttribute('src', $image_url);
                    $image_node->setAttribute('alt', $workshop_title ?: 'Workshop thủ công tại Wonom');
                }
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_tag') . ']', $card), $workshop_tag);
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_title') . ']', $card), $workshop_title);
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_description') . ']', $card), isset($workshop['description']) ? $workshop['description'] : '');
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_price') . ']', $card), isset($workshop['price']) ? $workshop['price'] : '');
                $workshop_list->appendChild($card);
            }
        }
    } else {
        $gallery_wrap = $explore_first('.//*[' . $class_query('home_explore_list_inner') . ']', $floor_section);
        $popup_gallery_rows = $explore_option($prefix . 'popup_gallery', array());
        $uses_popup_gallery_for_slider = is_array($popup_gallery_rows) && !empty($popup_gallery_rows);
        $slider_media = array();

        if ($uses_popup_gallery_for_slider && !empty($explore_popup_data[$floor . 'f']['galleryImages'])) {
            foreach ($explore_popup_data[$floor . 'f']['galleryImages'] as $media) {
                if (!empty($media['showInSlider'])) {
                    $slider_media[] = $media;
                }
            }
        }

        if ($uses_popup_gallery_for_slider && $gallery_wrap) {
            while ($gallery_wrap->firstChild) {
                $gallery_wrap->removeChild($gallery_wrap->firstChild);
            }

            foreach ($slider_media as $gallery_index => $media) {
                $media_type = isset($media['type']) ? sanitize_key($media['type']) : 'image';
                $media_url = isset($media['src']) ? esc_url_raw($media['src']) : '';
                $thumbnail_url = isset($media['thumbnail']) ? esc_url_raw($media['thumbnail']) : '';
                if (!$media_url) {
                    continue;
                }

                $slide = $explore_dom->createElement('div');
                $slide->setAttribute('class', 'home_explore_list_item swiper-slide img_abs wonom-lazy-media');
                $media_alt = $nav_title . ' · Wonom · ' . ($gallery_index + 1);

                if ('image' === $media_type) {
                    $image = $explore_dom->createElement('img');
                    $image->setAttribute('src', $media_url);
                    $image->setAttribute('alt', $media_alt);
                    $image->setAttribute('loading', 'lazy');
                    $slide->appendChild($image);
                } else {
                    if ($thumbnail_url) {
                        $thumbnail = $explore_dom->createElement('img');
                        $thumbnail->setAttribute('class', 'wonom-lazy-video-thumbnail');
                        $thumbnail->setAttribute('src', $thumbnail_url);
                        $thumbnail->setAttribute('alt', $media_alt);
                        $thumbnail->setAttribute('loading', 'lazy');
                        $slide->appendChild($thumbnail);
                    }
                    $play_button = $explore_dom->createElement('button');
                    $play_button->setAttribute('class', 'wonom-lazy-video-play');
                    $play_button->setAttribute('type', 'button');
                    $play_button->setAttribute('data-wonom-video-play', 'true');
                    $play_button->setAttribute('data-video-type', $media_type);
                    $play_button->setAttribute('data-video-src', $media_url);
                    $play_button->setAttribute('aria-label', 'Phát video ' . $media_alt);
                    $play_icon = $explore_dom->createElement('span');
                    $play_icon->setAttribute('aria-hidden', 'true');
                    $play_button->appendChild($play_icon);
                    $slide->appendChild($play_button);
                }
                $gallery_wrap->appendChild($slide);
            }
        } elseif ($gallery_wrap) {
            // Keep legacy slider data as a migration fallback until popup media is configured.
            $gallery = $explore_option($prefix . 'gallery', array());

            if (is_array($gallery) && $gallery) {
                while ($gallery_wrap->firstChild) {
                    $gallery_wrap->removeChild($gallery_wrap->firstChild);
                }
                foreach ($gallery as $gallery_index => $gallery_image) {
                    $image_url = $explore_image_url(isset($gallery_image['image']) ? $gallery_image['image'] : '');
                    if (!$image_url) {
                        continue;
                    }
                    $slide = $explore_dom->createElement('div');
                    $slide->setAttribute('class', 'home_explore_list_item swiper-slide img_abs');
                    $image = $explore_dom->createElement('img');
                    $image->setAttribute('src', $image_url);
                    $image->setAttribute('alt', $nav_title . ' · Wonom · ' . ($gallery_index + 1));
                    $image->setAttribute('loading', 'lazy');
                    $slide->appendChild($image);
                    $gallery_wrap->appendChild($slide);
                }
            }
        }
    }
}

$explore_section = $explore_dom->getElementById('explore');
$explore_output = $explore_section ? $explore_dom->saveHTML($explore_section) : $explore_markup;
$explore_mobile_floor_nav = $explore_first('//*[' . $class_query('home_explore_mobile_floor_nav') . ']');
if ($explore_section && $explore_mobile_floor_nav) {
    $explore_output .= $explore_dom->saveHTML($explore_mobile_floor_nav);
}
$explore_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$explore_output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $explore_asset_url, $explore_output);

echo $explore_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
