<?php

if (!defined('ABSPATH')) exit;

function wonom_register_workshop_category_taxonomy()
{
    register_taxonomy('wonom_workshop_category', array('wonom_workshop'), array(
        'labels' => array(
            'name' => 'Danh mục Workshop',
            'singular_name' => 'Danh mục Workshop',
            'menu_name' => 'Danh mục Workshop',
            'all_items' => 'Tất cả danh mục',
            'edit_item' => 'Sửa danh mục',
            'view_item' => 'Xem danh mục',
            'update_item' => 'Cập nhật danh mục',
            'add_new_item' => 'Thêm danh mục Workshop',
            'new_item_name' => 'Tên danh mục mới',
            'search_items' => 'Tìm danh mục',
            'not_found' => 'Không tìm thấy danh mục',
        ),
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => false,
        'hierarchical' => true,
        'rewrite' => false,
    ));
}
add_action('init', 'wonom_register_workshop_category_taxonomy', 5);

function wonom_register_workshop_product_post_type()
{
    $labels = array(
        'name' => 'Sản phẩm Workshop',
        'singular_name' => 'Sản phẩm Workshop',
        'menu_name' => 'Sản phẩm Workshop',
        'add_new' => 'Thêm sản phẩm',
        'add_new_item' => 'Thêm sản phẩm Workshop',
        'edit_item' => 'Sửa sản phẩm Workshop',
        'new_item' => 'Sản phẩm Workshop mới',
        'view_item' => 'Xem sản phẩm Workshop',
        'search_items' => 'Tìm sản phẩm Workshop',
        'not_found' => 'Không tìm thấy sản phẩm',
        'not_found_in_trash' => 'Không có sản phẩm trong thùng rác',
        'all_items' => 'Tất cả sản phẩm',
    );

    $post_type = tr_post_type('Wonom Workshop', 'Wonom Workshops', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-hammer',
        'supports' => array('title', 'page-attributes'),
        'taxonomies' => array('wonom_workshop_category'),
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ));
    $post_type->setTitlePlaceholder('Nhập tên sản phẩm');
    $post_type->forceDisableGutenberg();

    tr_meta_box('Thông tin sản phẩm Workshop', 'wonom_workshop', array(
        'priority' => 'high',
        'callback' => 'wonom_render_workshop_product_fields',
    ));
}
wonom_register_workshop_product_post_type();

function wonom_render_workshop_product_fields()
{
    global $post;

    $form = tr_form();

    echo '<h3>Thông tin hiển thị trong danh sách sản phẩm</h3>';
    echo $form->image('workshop_image')->setLabel('Hình card sản phẩm');
    echo $form->textarea('workshop_description')->setLabel('Mô tả ngắn trên card');
    echo $form->text('workshop_price')->setLabel('Giá sản phẩm');

    echo '<hr><h3>Popup Product Detail</h3>';
    echo $form->gallery('workshop_detail_images')->setLabel('Gallery ảnh chi tiết');

    wonom_render_workshop_richtext_editor(
        $post,
        'workshop_detail_content',
        'Nội dung giới thiệu chi tiết',
        15
    );
    wonom_render_workshop_richtext_editor(
        $post,
        'workshop_detail_includes',
        'Dụng cụ bao gồm',
        12
    );

    echo $form->text('workshop_detail_duration')->setLabel('Thời lượng')->setDefault('60-90 phút');
}

function wonom_render_workshop_richtext_editor($post, $meta_key, $label, $rows = 8)
{
    $editor_id = 'wonom_' . $meta_key . '_editor';
    $content = get_post_meta($post->ID, $meta_key, true);

    echo '<div class="wonom-workshop-richtext-field">';
    echo '<div class="wonom-workshop-richtext-title">' . esc_html($label) . '</div>';
    wp_editor((string) $content, $editor_id, array(
        'textarea_name' => 'tr[' . $meta_key . ']',
        'textarea_rows' => (int) $rows,
        'editor_height' => max(320, (int) $rows * 24),
        'media_buttons' => false,
        'teeny' => false,
        'quicktags' => true,
        'drag_drop_upload' => true,
        'tinymce' => array(
            'wpautop' => true,
            'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wonom_media,wp_more,fullscreen,wp_adv',
            'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
        ),
    ));
    echo '</div>';
}

function wonom_workshop_tinymce_plugins($plugins)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->post_type, array('wonom_workshop', 'wonom_tour', 'wonom_event'), true)) {
        return $plugins;
    }

    $script_path = get_template_directory() . '/asset/js/admin-workshop-tinymce.js';
    $script_version = file_exists($script_path) ? filemtime($script_path) : null;
    $plugins['wonom_media'] = add_query_arg(
        'ver',
        $script_version,
        get_template_directory_uri() . '/asset/js/admin-workshop-tinymce.js'
    );
    return $plugins;
}
add_filter('mce_external_plugins', 'wonom_workshop_tinymce_plugins');

function wonom_workshop_editor_admin_assets()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->post_type, array('wonom_workshop', 'wonom_tour', 'wonom_event'), true)) {
        return;
    }

    $style_path = get_template_directory() . '/asset/css/admin-workshop-editor.css';
    wp_enqueue_style(
        'wonom-workshop-editor',
        get_template_directory_uri() . '/asset/css/admin-workshop-editor.css',
        array(),
        file_exists($style_path) ? filemtime($style_path) : null
    );
}
add_action('admin_enqueue_scripts', 'wonom_workshop_editor_admin_assets');

function wonom_get_workshop_products()
{
    $posts = get_posts(array(
        'post_type' => 'wonom_workshop',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'),
        'suppress_filters' => false,
        'lang' => function_exists('wonom_home_current_language') ? wonom_home_current_language() : '',
    ));

    $products = array();
    foreach ($posts as $workshop_post) {
        $category_terms = wp_get_post_terms($workshop_post->ID, 'wonom_workshop_category');
        $category_terms = is_wp_error($category_terms) ? array() : $category_terms;
        $category_slugs = wp_list_pluck($category_terms, 'slug');
        $products[] = array(
            'id' => $workshop_post->ID,
            'title' => get_the_title($workshop_post),
            'image' => get_post_meta($workshop_post->ID, 'workshop_image', true),
            'category' => $category_slugs ? sanitize_key($category_slugs[0]) : 'all',
            'categories' => array_map('sanitize_key', $category_slugs),
            'description' => (string) get_post_meta($workshop_post->ID, 'workshop_description', true),
            'price' => (string) get_post_meta($workshop_post->ID, 'workshop_price', true),
            'detail_images' => get_post_meta($workshop_post->ID, 'workshop_detail_images', true),
            'detail_content' => (string) get_post_meta($workshop_post->ID, 'workshop_detail_content', true),
            'detail_includes' => (string) get_post_meta($workshop_post->ID, 'workshop_detail_includes', true),
            'detail_duration' => (string) get_post_meta($workshop_post->ID, 'workshop_detail_duration', true),
        );
    }

    if ($products) {
        return $products;
    }

    // Keep the old homepage repeater as a non-destructive fallback until it is migrated.
    if (get_option('wonom_workshop_products_migrated')) {
        return array();
    }
    $legacy_products = tr_options_field('wonom_home_options.home_explore_floor_3_workshops');
    return is_array($legacy_products) ? $legacy_products : array();
}

function wonom_get_used_workshop_categories()
{
    $terms = get_terms(array(
        'taxonomy' => 'wonom_workshop_category',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
        'lang' => function_exists('wonom_home_current_language') ? wonom_home_current_language() : '',
    ));
    if (!is_wp_error($terms) && $terms) {
        return array_map(static function ($term) {
            return array('slug' => $term->slug, 'label' => $term->name);
        }, $terms);
    }

    if (!get_option('wonom_workshop_categories_migrated_v2') && function_exists('wonom_get_home_workshop_categories')) {
        return wonom_get_home_workshop_categories();
    }

    return array();
}

function wonom_migrate_legacy_workshop_products()
{
    if (get_option('wonom_workshop_products_migrated')) {
        return;
    }

    $existing = get_posts(array(
        'post_type' => 'wonom_workshop',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));
    if ($existing) {
        update_option('wonom_workshop_products_migrated', 1, false);
        return;
    }

    $legacy_products = tr_options_field('wonom_home_options.home_explore_floor_3_workshops');
    if (!is_array($legacy_products) || !$legacy_products) {
        update_option('wonom_workshop_products_migrated', 1, false);
        return;
    }

    $meta_map = array(
        'image' => 'workshop_image',
        'category' => 'workshop_category',
        'description' => 'workshop_description',
        'price' => 'workshop_price',
        'detail_images' => 'workshop_detail_images',
        'detail_content' => 'workshop_detail_content',
        'detail_includes' => 'workshop_detail_includes',
        'detail_duration' => 'workshop_detail_duration',
    );

    foreach ($legacy_products as $index => $legacy_product) {
        if (!is_array($legacy_product) || empty($legacy_product['title'])) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_type' => 'wonom_workshop',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($legacy_product['title']),
            'menu_order' => (int) $index,
        ), true);
        if (is_wp_error($post_id)) {
            continue;
        }

        foreach ($meta_map as $legacy_key => $meta_key) {
            if (array_key_exists($legacy_key, $legacy_product)) {
                update_post_meta($post_id, $meta_key, wp_slash($legacy_product[$legacy_key]));
            }
        }
    }

    update_option('wonom_workshop_products_migrated', 1, false);
}
add_action('admin_init', 'wonom_migrate_legacy_workshop_products', 20);

function wonom_migrate_workshop_categories()
{
    if (get_option('wonom_workshop_categories_migrated_v2')) {
        return;
    }

    $legacy_categories = tr_options_field('wonom_home_options.home_explore_floor_3_categories');
    if (!is_array($legacy_categories) || !$legacy_categories) {
        $legacy_categories = array(
            array('slug' => 'hat', 'label' => 'NÓN'),
            array('slug' => 'traditional', 'label' => 'ÁO TRUYỀN THỐNG'),
            array('slug' => 'accessories', 'label' => 'PHỤ KIỆN'),
        );
    }

    $category_labels = array(
        'hat' => 'NÓN',
        'traditional' => 'ÁO TRUYỀN THỐNG',
        'accessories' => 'PHỤ KIỆN',
    );
    foreach ($legacy_categories as $category) {
        if (!empty($category['slug']) && !empty($category['label'])) {
            $category_labels[sanitize_title($category['slug'])] = sanitize_text_field($category['label']);
        }
    }

    foreach ($legacy_categories as $category) {
        $slug = isset($category['slug']) ? sanitize_title($category['slug']) : '';
        $label = isset($category['label']) ? sanitize_text_field($category['label']) : '';
        if (!$slug || !$label || term_exists($slug, 'wonom_workshop_category')) {
            continue;
        }
        wp_insert_term($label, 'wonom_workshop_category', array('slug' => $slug));
    }

    $product_ids = get_posts(array(
        'post_type' => 'wonom_workshop',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));
    foreach ($product_ids as $product_id) {
        $legacy_slug = sanitize_title((string) get_post_meta($product_id, 'workshop_category', true));
        if (!$legacy_slug) {
            continue;
        }
        if (!term_exists($legacy_slug, 'wonom_workshop_category')) {
            $legacy_label = isset($category_labels[$legacy_slug])
                ? $category_labels[$legacy_slug]
                : ucwords(str_replace('-', ' ', $legacy_slug));
            wp_insert_term($legacy_label, 'wonom_workshop_category', array('slug' => $legacy_slug));
        }
        wp_set_object_terms($product_id, array($legacy_slug), 'wonom_workshop_category', false);
    }

    update_option('wonom_workshop_categories_migrated', 1, false);
    update_option('wonom_workshop_categories_migrated_v2', 1, false);
}
add_action('admin_init', 'wonom_migrate_workshop_categories', 30);

function wonom_workshop_product_columns($columns)
{
    return array(
        'cb' => isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox">',
        'workshop_image' => 'Hình ảnh',
        'title' => 'Tên sản phẩm',
        'workshop_category' => 'Nhóm sản phẩm',
        'workshop_price' => 'Giá',
        'date' => isset($columns['date']) ? $columns['date'] : 'Ngày',
    );
}
add_filter('manage_wonom_workshop_posts_columns', 'wonom_workshop_product_columns');

function wonom_workshop_product_column_content($column, $post_id)
{
    if ('workshop_image' === $column) {
        $image_id = (int) get_post_meta($post_id, 'workshop_image', true);
        echo $image_id ? wp_get_attachment_image($image_id, array(70, 70)) : '—';
    } elseif ('workshop_category' === $column) {
        $terms = get_the_terms($post_id, 'wonom_workshop_category');
        echo !is_wp_error($terms) && $terms ? esc_html(implode(', ', wp_list_pluck($terms, 'name'))) : '—';
    } elseif ('workshop_price' === $column) {
        echo esc_html((string) get_post_meta($post_id, 'workshop_price', true));
    }
}
add_action('manage_wonom_workshop_posts_custom_column', 'wonom_workshop_product_column_content', 10, 2);
