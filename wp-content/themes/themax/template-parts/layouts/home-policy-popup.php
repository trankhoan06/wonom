<?php
if (!defined('ABSPATH')) exit;

$policy_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$policy_popup_title = $policy_option('home_footer_policy_popup_title', 'CHÍNH SÁCH & ĐIỀU KHOẢN');
$raw_items = $policy_option('home_footer_policy_items', array());

$policy_items = array();
if (is_array($raw_items) && $raw_items) {
    foreach ($raw_items as $raw_item) {
        if (!is_array($raw_item)) continue;
        $nav_title = isset($raw_item['nav_title']) ? trim((string) $raw_item['nav_title']) : '';
        $title = isset($raw_item['title']) ? trim((string) $raw_item['title']) : '';
        $subtitle = isset($raw_item['subtitle']) ? trim((string) $raw_item['subtitle']) : '';
        $content = isset($raw_item['content']) ? trim((string) $raw_item['content']) : '';
        if ('' === $nav_title && '' === $title && '' === $content) continue;
        $policy_items[] = array(
            'nav_title' => $nav_title ?: $title ?: 'Chính sách',
            'title' => $title ?: $nav_title ?: 'CHÍNH SÁCH',
            'subtitle' => $subtitle,
            'content' => $content,
        );
    }
}

if (!$policy_items) {
    $policy_items = array(
        array(
            'nav_title' => 'Chính sách bảo mật',
            'title' => 'CHÍNH SÁCH BẢO MẬT',
            'subtitle' => 'Lorem ipsum dolor sit amet consectetur.',
            'content' => "Lorem ipsum dolor sit amet consectetur. Eget gravida amet leo morbi sed. Sit integer fames id tempus elementum sed erat. Risus lectus justo vitae mauris egestas magna dictum commodo sit.\n\nLorem ipsum dolor sit amet consectetur. Eget gravida amet leo morbi sed. Sit integer fames id tempus elementum sed erat. Risus lectus justo vitae mauris egestas magna dictum commodo sit.",
        ),
        array(
            'nav_title' => 'Chính sách bảo mật',
            'title' => 'CHÍNH SÁCH BẢO MẬT',
            'subtitle' => 'Lorem ipsum dolor sit amet consectetur.',
            'content' => "Lorem ipsum dolor sit amet consectetur. Eget gravida amet leo morbi sed. Sit integer fames id tempus elementum sed erat. Risus lectus justo vitae mauris egestas magna dictum commodo sit.\n\nLorem ipsum dolor sit amet consectetur. Eget gravida amet leo morbi sed. Sit integer fames id tempus elementum sed erat. Risus lectus justo vitae mauris egestas magna dictum commodo sit.",
        ),
    );
}

$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
?>
<section class="popup_tour policy">
    <div class="popup_tour_head">
        <div class="popup_tour_title heading h4 cl_cream h5_mb"><?php echo esc_html($policy_popup_title); ?></div>
        <div class="popup_tour_img img_full">
            <img src="<?php echo esc_url($asset_url . 'img/ex_bg_bot.png'); ?>" alt="">
        </div>
        <div class="popup_tour_close btn">
            <div class="popup_tour_close_inner btn-inner svg_full">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M18.043 4.54289C18.4335 4.15237 19.0666 4.15237 19.4571 4.54289C19.8475 4.93342 19.8476 5.56646 19.4571 5.95696L13.4141 11.9999L19.4571 18.0429C19.8475 18.4334 19.8476 19.0665 19.4571 19.457C19.0666 19.8475 18.4335 19.8474 18.043 19.457L12 13.414L5.95708 19.457C5.56658 19.8475 4.93355 19.8474 4.54302 19.457C4.15249 19.0664 4.15249 18.4334 4.54302 18.0429L10.586 11.9999L4.54302 5.95696C4.15249 5.56643 4.15249 4.93342 4.54302 4.54289C4.93354 4.15237 5.56655 4.15237 5.95708 4.54289L12 10.5859L18.043 4.54289Z"
                        fill="#460700" />
                </svg>
            </div>
        </div>
    </div>
    <div class="container grid">
        <div class="popup_tour_sidebar">
            <div class="popup_tour_sidebar_card">
                <?php foreach ($policy_items as $index => $item) : ?>
                    <div class="popup_tour_policy_card_item<?php echo 0 === $index ? ' is-active' : ''; ?>" data-policy-target="<?php echo (int) $index; ?>" role="button" tabindex="0">
                        <div class="popup_tour_policy_card_item_txt txt_18 txt_bold"><?php echo esc_html($item['nav_title']); ?></div>
                        <div class="popup_tour_policy_card_item_icon svg_full">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g opacity="0.4">
                                    <path d="M7 7L17 17" stroke="#460700" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M8 17L17 17L17 8" stroke="#460700" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </g>
                            </svg>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="popup_tour_content">
            <?php foreach ($policy_items as $index => $item) : ?>
                <div class="popup_tour_content_txt" data-policy-section="<?php echo (int) $index; ?>">
                    <div class="popup_tour_content_title h4 heading"><?php echo esc_html($item['title']); ?></div>
                    <?php if (!empty($item['subtitle'])) : ?>
                        <div class="popup_tour_content_subtitle txt_18 txt_extrabold"><?php echo esc_html($item['subtitle']); ?></div>
                    <?php endif; ?>
                    <div class="popup_tour_content_des">
                        <?php echo wpautop(wp_kses_post($item['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="home_piano" aria-hidden="true">
        <div class="home_piano_item item1"></div>
        <div class="home_piano_item item2"></div>
        <div class="home_piano_item item3"></div>
        <div class="home_piano_item item4"></div>
        <div class="home_piano_item item5"></div>
    </div>
</section>
