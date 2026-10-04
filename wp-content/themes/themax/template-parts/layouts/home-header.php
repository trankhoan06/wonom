<?php
if (!defined('ABSPATH')) exit;

$wonom_language_icons = array(
    'vi' => 'icon_vietnam.svg',
    'en' => 'icon_eng.svg',
    'ko' => 'icon_korea.svg',
);
$wonom_languages = function_exists('pll_the_languages')
    ? (array) pll_the_languages(array('raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0))
    : array();
$wonom_current_language = function_exists('pll_current_language') ? pll_current_language('slug') : 'vi';
$wonom_current_language = $wonom_current_language ?: (function_exists('pll_default_language') ? pll_default_language('slug') : 'vi');
$wonom_home_url = function_exists('pll_home_url') ? pll_home_url($wonom_current_language) : home_url('/');

if (!$wonom_languages) {
    $wonom_languages = array(
        'vi' => array('slug' => 'vi', 'name' => 'Tiếng Việt', 'url' => home_url('/'), 'current_lang' => true),
        'en' => array('slug' => 'en', 'name' => 'English', 'url' => home_url('/'), 'current_lang' => false),
        'ko' => array('slug' => 'ko', 'name' => '한국어', 'url' => home_url('/'), 'current_lang' => false),
    );
}

$wonom_active_language = reset($wonom_languages);
foreach ($wonom_languages as $wonom_language_candidate) {
    if (!empty($wonom_language_candidate['current_lang']) || (isset($wonom_language_candidate['slug']) && $wonom_language_candidate['slug'] === $wonom_current_language)) {
        $wonom_active_language = $wonom_language_candidate;
        break;
    }
}
$wonom_active_slug = isset($wonom_active_language['slug']) ? $wonom_active_language['slug'] : 'vi';
$wonom_active_icon = isset($wonom_language_icons[$wonom_active_slug])
    ? $wonom_language_icons[$wonom_active_slug]
    : 'icon_vietnam.svg';
?>
<header class="header bg_cream">
        <div class="header_overlay tablet"></div>
        <div class="container header-container">
            <a href="<?php echo esc_url($wonom_home_url); ?>" class="header-logo img_height">
                <img src="/asset/img/logo.webp" alt="Wonom Logo">
            </a>
            <nav class="header-nav">
                <a href="#introduce" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">GIỚI THIỆU</a>
                <a href="#experience" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">TRẢI NGHIỆM</a>
                <a href="#explore" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">KHÁM PHÁ 5 TẦNG</a>
                <a href="#tour" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">THAM QUAN</a>
                <a href="#event" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">SỰ KIỆN</a>
                <a href="#library" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">THƯ VIỆN ẢNH</a>
                <a href="#contact" class="nav-item h6_mb txt_15 txt_extrabold cl_dark_brown">LIÊN HỆ</a>
                <div class="header_nav_wrap">
                    <a href="#" class="header_button btn tablet txt_14 txt_extrabold txt-btn-red txt_bold"
                        data-booking-trigger data-booking-type="general" data-booking-floor=""
                        data-booking-venue="WONOM" data-booking-title="LIÊN HỆ TƯ VẤN NGAY" data-booking-subtitle=""
                        data-booking-submit="GỬI YÊU CẦU ĐẶT LỊCH">
                        <div class="btn-inner">ĐẶT CHỖ</div>
                    </a>
                    <a href="#" class="header_button_member tablet btn txt_14 txt_extrabold txt-btn-yellow txt_bold">
                        <div class="btn-inner">THÀNH VIÊN</div>
                    </a>
                </div>

                <div class="header_logo_mb tablet img_full">
                    <img src="/asset/img/icon_menu.webp" alt="">
                </div>
                <div class="header_logo_bg_mb tablet img_full">
                    <img src="/asset/img/header_bg_mb.png" alt="">
                </div>
            </nav>
            <div class="header-actions">
                <div class="lang-selector-wrapper " id="langWrapper">
                    <a href="#" class=" btn img_full" id="langSelectorBtn">
                        <div class="btn-inner lang-selector img_full">
                            <img src="<?php echo esc_url('/asset/img/' . $wonom_active_icon); ?>" alt="<?php echo esc_attr(strtoupper($wonom_active_slug)); ?>">
                            <svg class="lang-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L5 5L9 1" stroke="#1E1413" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>
                    </a>
                    <div class="lang-dropdown">
                        <?php $wonom_language_index = 0; ?>
                        <?php foreach ($wonom_languages as $wonom_language) : ?>
                            <?php
                            $wonom_language_slug = isset($wonom_language['slug']) ? $wonom_language['slug'] : '';
                            $wonom_language_name = isset($wonom_language['name']) ? $wonom_language['name'] : strtoupper($wonom_language_slug);
                            $wonom_language_url = !empty($wonom_language['url'])
                                ? $wonom_language['url']
                                : (function_exists('pll_home_url') ? pll_home_url($wonom_language_slug) : home_url('/'));
                            $wonom_language_icon = isset($wonom_language_icons[$wonom_language_slug])
                                ? $wonom_language_icons[$wonom_language_slug]
                                : 'icon_vietnam.svg';
                            $wonom_is_current_language = !empty($wonom_language['current_lang']) || $wonom_language_slug === $wonom_current_language;
                            ?>
                            <?php if ($wonom_language_index > 0) : ?>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($wonom_language_url); ?>"
                                class="lang-item<?php echo $wonom_is_current_language ? ' is-current' : ''; ?>"
                                lang="<?php echo esc_attr($wonom_language_slug); ?>"
                                <?php echo $wonom_is_current_language ? 'aria-current="page"' : ''; ?>>
                                <img src="<?php echo esc_url('/asset/img/' . $wonom_language_icon); ?>" alt="<?php echo esc_attr($wonom_language_name); ?>">
                                <span class="txt_15 txt_extrabold cl_dark_brown"><?php echo esc_html($wonom_language_name); ?></span>
                                <?php if ($wonom_is_current_language) : ?>
                                    <svg class="icon-check" width="16" height="12" viewBox="0 0 16 12" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 6L5.5 10.5L15 1" stroke="#E41F2F" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                <?php endif; ?>
                            </a>
                            <?php $wonom_language_index++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="#" class="header_button btn desktop txt_14 txt_extrabold txt-btn-red txt_bold"
                    data-booking-trigger data-booking-type="general" data-booking-floor="" data-booking-venue="WONOM"
                    data-booking-title="LIÊN HỆ TƯ VẤN NGAY" data-booking-subtitle=""
                    data-booking-submit="GỬI YÊU CẦU ĐẶT LỊCH">
                    <div class="btn-inner">ĐẶT CHỖ</div>
                </a>
                <a href="#" class="header_button_member desktop btn txt_14 txt_extrabold txt-btn-yellow txt_bold">
                    <div class="btn-inner">THÀNH VIÊN</div>
                </a>
                <div class="header_menu tablet btn">
                    <div class="header_menu_inner btn-inner">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </header>
