<?php

$image_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';

$get_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$resolve_image = static function ($image, $fallback) {
    $image_url = '';

    if (is_numeric($image)) {
        $image_url = wp_get_attachment_image_url((int) $image, 'full');
    } elseif (is_array($image)) {
        $attachment_id = isset($image['id']) ? (int) $image['id'] : 0;
        $image_url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'full') : '';
        if (!$image_url && !empty($image['url'])) {
            $image_url = $image['url'];
        }
    } elseif (is_string($image)) {
        $image_url = $image;
    }

    return $image_url ?: $fallback;
};

$eyebrow = $get_option('home_intro_eyebrow', 'CÂU CHUYỆN CỦA WONOM!');
$title = $get_option('home_intro_title', "Văn hóa trở nên\nrộng lớn hơn khi\nđược sẻ chia.");
$description = $get_option('home_intro_description', 'Không gian chạm và tận hưởng văn hóa đời thường Hàn Quốc ngay tại Sài Gòn, mở ra nhịp cầu kết nối văn hóa hai quốc gia.');
$caption = $get_option('home_intro_caption', 'Tổ hợp 5 tầng trải nghiệm Ẩm thực, Hanbok, Workshop thủ công và giải trí Hàn Quốc độc đáo.');
$content = tr_options_field('wonom_home_options.home_intro_content');

if (!$content) {
    $content = sprintf(
        '<p>%s</p><p><strong>%s</strong></p>',
        esc_html($description),
        esc_html($caption)
    );
}

$intro_images = array(
    array(
        'url' => $resolve_image($get_option('home_intro_image_1'), $image_asset_url . 'store.jpg'),
        'alt' => $get_option('home_intro_image_1_alt', 'Không gian Wonom'),
        'pin' => 'RedPin.svg',
    ),
    array(
        'url' => $resolve_image($get_option('home_intro_image_2'), $image_asset_url . 'intro2.webp'),
        'alt' => $get_option('home_intro_image_2_alt', 'Trải nghiệm tại Wonom'),
        'pin' => 'YellowPin.svg',
    ),
    array(
        'url' => $resolve_image($get_option('home_intro_image_3'), $image_asset_url . 'intro3.webp'),
        'alt' => $get_option('home_intro_image_3_alt', 'Văn hóa Hàn Quốc tại Wonom'),
        'pin' => 'BluePin.svg',
    ),
);
?>
<section id="introduce" class="home_intro relative pa_section">
    <div class="container">
        <div class="home_intro_inner grid">
            <div class="home_intro_left">
                <div class="home_intro_left_title txt_title"><?php echo esc_html($eyebrow); ?></div>
                <h2 class="home_intro_left_content heading h2 h4_mb"><?php echo nl2br(esc_html($title)); ?></h2>
            </div>
            <div class="home_intro_right">
                <div class="home_intro_right_content home_intro_richtext txt_18"><?php echo wp_kses_post($content); ?></div>
            </div>
        </div>
        <div class="home_intro_arrow img_full"><img src="<?php echo esc_url($image_asset_url . 'intro_arrow.svg'); ?>" alt=""></div>
        <div class="home_intro_icon1 img_full absolute"><img src="<?php echo esc_url($image_asset_url . 'intro_icon1.webp'); ?>" alt=""></div>
        <div class="home_intro_icon2 img_full absolute"><img src="<?php echo esc_url($image_asset_url . 'intro_icon2.webp'); ?>" alt=""></div>
        <div class="home_intro_icon img_full absolute"><img src="<?php echo esc_url($image_asset_url . 'intro_icon.webp'); ?>" alt=""></div>
        <div class="home_intro_image_wrap absolute">
            <?php foreach ($intro_images as $index => $intro_image) : ?>
                <div class="home_intro_image<?php echo esc_attr($index + 1); ?> home_intro_image_item relative">
                    <div class="home_intro_pin img_full"><img src="<?php echo esc_url($image_asset_url . $intro_image['pin']); ?>" alt=""></div>
                    <div class="home_intro_image_inner img_full">
                        <img src="<?php echo esc_url($intro_image['url']); ?>" alt="<?php echo esc_attr($intro_image['alt']); ?>" loading="lazy">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="home_intro_bottom_bar absolute img_fullfill">
        <img src="<?php echo esc_url($image_asset_url . 'intro_bg_bottom.jpg'); ?>" alt="">
    </div>
    <div class="home_intro_bg absolute img_full">
        <img src="<?php echo esc_url($image_asset_url . 'intro_bg.png'); ?>" alt="">
    </div>
</section>
