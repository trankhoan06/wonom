<?php

$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';
$homepage_options = get_option('wonom_home_options', array());
$slides = is_array($homepage_options) && isset($homepage_options['home_hero_slides'])
    ? $homepage_options['home_hero_slides']
    : array();

$default_slides = array(
    array(
        'image_url' => $asset_url . 'home_banner.webp',
        'title' => "KHÔNG GIAN VĂN HOÁ HÀN QUỐC CÓ MẶT TẠI TP.HCM",
        'alt' => 'Không gian văn hóa Hàn Quốc Wonom',
    ),
    array(
        'image_url' => $asset_url . 'home-hero2.webp',
        'title' => "TRẢI NGHIỆM KHÔNG GIAN HÀN QUỐC NGAY GIỮA LÒNG TP.HCM",
        'alt' => 'Trải nghiệm không gian Hàn Quốc tại Wonom',
    ),
    array(
        'image_url' => $asset_url . 'home-hero3.webp',
        'title' => "MỘT GÓC VĂN HÓA HÀN QUỐC GIỮA LÒNG SÀI GÒN",
        'alt' => 'Văn hóa Hàn Quốc giữa lòng Sài Gòn',
    ),
);

if (is_array($slides) && $slides) {
    $configured_slides = array();

    foreach ($slides as $slide) {
        $image = isset($slide['image']) ? $slide['image'] : '';
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

        $title = isset($slide['title']) ? trim($slide['title']) : '';
        if (!$image_url || !$title) {
            continue;
        }

        $configured_slides[] = array(
            'image_url' => $image_url,
            'title' => $title,
            'alt' => isset($slide['alt']) && $slide['alt'] !== '' ? $slide['alt'] : $title,
        );
    }

    if ($configured_slides) {
        $slides = $configured_slides;
    } else {
        $slides = $default_slides;
    }
} else {
    $slides = $default_slides;
}

// front-page.php renders this partial before wp_head(), allowing the actual
// LCP asset (including an admin-configured image) to be preloaded precisely.
$GLOBALS['wonom_home_lcp_image'] = isset($slides[0]['image_url']) ? $slides[0]['image_url'] : '';
?>
<section class="home_banner pa_section">
    <div class="home_banner_image_list" aria-hidden="true">
        <?php foreach ($slides as $index => $slide) : ?>
            <div class="home_banner_image_item img_fullfill<?php echo 0 === $index ? ' active' : ''; ?>">
                <img src="<?php echo esc_url($slide['image_url']); ?>"
                    alt="<?php echo esc_attr($slide['alt']); ?>"
                    <?php echo 0 === $index ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="home_banner_inner swiper mySwiper">
        <div class="swiper-wrapper home_banner_wrap">
            <?php foreach ($slides as $slide) : ?>
                <div class="swiper-slide home_banner_item">
                    <div class="home_banner_overlay"></div>
                    <div class="swiper-slide-txt heading txt_uppercase txt_center absolute h1 cl_white h4_mb">
                        <?php echo nl2br(esc_html($slide['title'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="home_banner_button svg_full desktop btn bg_white home_banner_button_next">
            <div class="btn-inner">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.10744 9.99998L15.8926 9.99998" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M10.5892 15.3034L15.8925 10.0001L10.5892 4.69678" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <div class="home_banner_button svg_full desktop btn bg_white home_banner_button_prev">
            <div class="btn-inner">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15.8926 9.99998L4.10744 9.99998" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9.41076 15.3034L4.10747 10.0001L9.41077 4.69678" stroke="#460700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <div class="swiper-pagination home_banner_pagination"></div>
    </div>

    <div class="home_piano">
        <div class="home_piano_item item1"></div>
        <div class="home_piano_item item2"></div>
        <div class="home_piano_item item3"></div>
        <div class="home_piano_item item4"></div>
        <div class="home_piano_item item5"></div>
    </div>
</section>
