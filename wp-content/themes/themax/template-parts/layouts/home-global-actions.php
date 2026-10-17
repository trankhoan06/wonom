<?php
if (!defined('ABSPATH')) exit;
$global_action_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};
$global_actions = array(
    array(
        'class' => 'item1',
        'icon' => wonom_get_home_image_url('home_global_member_icon', '/asset/img/icon_web.webp'),
        'text' => $global_action_option('home_global_member_text', 'THAM QUAN TOUR'),
        'link' => $global_action_option('home_global_member_link', '#tour'),
        'text_class' => '',
    ),
    array(
        'class' => 'item2',
        'icon' => wonom_get_home_image_url('home_global_phone_icon', '/asset/img/icon_tel.svg'),
        'text' => $global_action_option('home_global_phone_text', 'ĐẶT CHỖ'),
        'link' => $global_action_option('home_global_phone_link', '#contact'),
        'text_class' => ' cl_cream',
    ),
    array(
        'class' => 'item3',
        'icon' => wonom_get_home_image_url('home_global_zalo_icon', '/asset/img/icon_zal.svg'),
        'text' => $global_action_option('home_global_zalo_text', '0968 487 096'),
        'link' => 'https://zalo.me/1291446489467973585',
        'text_class' => ' cl_cream',
    ),
);
?>
<section class="global_btn">
        <a href="/#top" class="global_btn_top svg_full btn">
            <div class="global_btn_top_inner btn-inner">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.99998 15.8926L9.99998 4.10744" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M15.3034 9.41064L10.0001 4.10734L4.69678 9.41064" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

        </a>
        <div class="global_btn_list">
            <?php foreach ($global_actions as $global_action) : ?>
                <a class="global_btn_list_item <?php echo esc_attr($global_action['class']); ?>"
                    href="<?php echo esc_url($global_action['link']); ?>"
                    <?php if ('item3' === $global_action['class']) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                    data-label="<?php echo esc_attr($global_action['text']); ?>"
                    aria-label="<?php echo esc_attr($global_action['text']); ?>">
                    <div class="global_btn_list_item_icon img_full">
                        <img src="<?php echo esc_url($global_action['icon']); ?>" alt="" loading="lazy" decoding="async">
                    </div>
                    <div class="global_btn_list_item_txt txt_14 txt_extrabold<?php echo esc_attr($global_action['text_class']); ?> txt_uppercase"></div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
