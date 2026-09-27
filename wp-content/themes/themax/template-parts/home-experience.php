<?php

$experience_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';

$experience_get_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$experience_resolve_image = static function ($image, $fallback) {
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

$experience_cards = array();

for ($floor = 1; $floor <= 5; $floor++) {
    $experience_cards[] = array(
        'floor' => $floor,
        'label' => $experience_get_option('home_experience_floor_' . $floor . '_label', $floor . 'F'),
        'title' => $experience_get_option('home_experience_floor_' . $floor . '_title', 'Nhà hàng Mujige'),
        'image_url' => $experience_resolve_image(
            $experience_get_option('home_experience_floor_' . $floor . '_image'),
            $experience_asset_url . 'ex_content.webp'
        ),
        'image_alt' => $experience_get_option('home_experience_floor_' . $floor . '_image_alt', 'Khám phá tầng ' . $floor),
    );
}

$experience_middle_image = $experience_resolve_image(
    $experience_get_option('home_experience_middle_image'),
    $experience_asset_url . 'ex_content1.webp'
);
$experience_middle_image_alt = $experience_get_option('home_experience_middle_image_alt', 'Wonom Culture Complex');
$experience_marquee_text = $experience_get_option('home_experience_marquee_text', 'trải nghiệm wonom 5 sao!!!');

$render_experience_card = static function ($card) {
    ?>
    <div class="home_experience_list_card_item relative img-hover"
        data-explore-tab="tab<?php echo esc_attr($card['floor']); ?>f"
        role="link" tabindex="0"
        aria-label="<?php echo esc_attr('Khám phá ' . $card['label'] . ' · ' . $card['title']); ?>">
        <div class="home_experience_list_card_item_img img_abs">
            <img src="<?php echo esc_url($card['image_url']); ?>" alt="<?php echo esc_attr($card['image_alt']); ?>" loading="lazy">
        </div>
        <div class="home_experience_list_card_item_content tab<?php echo esc_attr($card['floor']); ?> absolute">
            <div class="home_experience_list_card_item_content_wrap">
                <div class="home_experience_list_card_item_content_num txt_bold cl_dark_brown"><?php echo esc_html($card['label']); ?></div>
                <div class="home_experience_list_card_item_content_txt txt_extrabold cl_dark_brown"><?php echo esc_html($card['title']); ?></div>
            </div>
            <div class="home_experience_list_card_item_content_icon svg_full svg_rotate">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <g opacity="0.4">
                        <path d="M7 7L17 17" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 16.9998L17 16.9998L17 7.99976" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>
        </div>
    </div>
    <?php
};
?>
<section id="experience" class="home_experience pa_section">
    <div class="home_experience_top img_fullfill desktop absolute">
        <img src="<?php echo esc_url($experience_asset_url . 'intro_bg_bottom.jpg'); ?>" alt="">
    </div>
    <div class="container">
        <div class="home_experience_list">
            <div class="home_experience_list_card grid">
                <?php foreach (array_slice($experience_cards, 0, 3) as $experience_card) : ?>
                    <?php $render_experience_card($experience_card); ?>
                <?php endforeach; ?>

                <div class="home_experience_list_card_item middle">
                    <div class="home_experience_list_card_item_img_dif img_full">
                        <img src="<?php echo esc_url($experience_middle_image); ?>" alt="<?php echo esc_attr($experience_middle_image_alt); ?>" loading="lazy">
                    </div>
                </div>

                <?php foreach (array_slice($experience_cards, 3) as $experience_card) : ?>
                    <?php $render_experience_card($experience_card); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="home_experience_inner">
        <div class="home_experience_bg img_full">
            <img src="<?php echo esc_url($experience_asset_url . 'ex_bg_bot.png'); ?>" alt="">
        </div>
        <div class="home_experience_marquee">
            <div class="home_experience_wrap">
                <?php for ($group = 0; $group < 2; $group++) : ?>
                    <div class="home_experience_group"<?php echo 1 === $group ? ' aria-hidden="true"' : ''; ?>>
                        <?php for ($item = 0; $item < 4; $item++) : ?>
                            <div class="home_experience_item heading h2 h2_mb"><?php echo esc_html($experience_marquee_text); ?></div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>
