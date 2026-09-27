<?php
/**
 * Template Name: Service
 * Description:
 *
 * Tip:
 *
 * @package WordPress
 * @subpackage chloe_pallete
 * @since chloe_pallete 1.0
 */

get_header();

?>
<main class="main" data-namespace="service">
    <section class="service_hero" data-init>
        <div class="container grid">
            <h1 class="service_hero_left heading h1 h4_mb cl_linear">
                <?php echo wp_kses_post((tr_posts_field('service_hero_title') ?? '')); ?>
            </h1>
            <div class="service_hero_right">
                <div class="service_hero_right_txt txt_18">
                    <?php echo nl2br(esc_html((tr_posts_field('service_hero_desc') ?? ''))); ?>
                </div>
                <a href="#service"
                    class="service_hero_right_discover txt_medium hover_txt txt_16 cl_eb">
                    <div class="hover_txt_grid">
                        <?php $btn_txt = esc_html((tr_posts_field('service_hero_btn_text') ?? '')); ?>
                        <span class="init"><?php echo $btn_txt; ?></span>
                        <span class="active"><?php echo $btn_txt; ?></span>
                    </div>
                    <div class="service_hero_right_discover_icon_wrap">
                        <div class="service_hero_right_discover_icon svg_full">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1293_1768)">
                                    <path d="M6 18.0007H18.0007V5.99998" stroke="#929292" stroke-width="1.5"
                                        stroke-linejoin="round" />
                                    <path d="M6 5.99998L18.0007 18.0007" stroke="#929292" stroke-width="1.5"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1293_1768">
                                        <rect width="24" height="24" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>

                        </div>
                        <div class="service_hero_right_discover_icon active svg_full">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1293_1768)">
                                    <path d="M6 18.0007H18.0007V5.99998" stroke="#E62636" stroke-width="1.5"
                                        stroke-linejoin="round" />
                                    <path d="M6 5.99998L18.0007 18.0007" stroke="#E62636" stroke-width="1.5"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1293_1768">
                                        <rect width="24" height="24" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>

                        </div>
                    </div>
                </a>
            </div>
            <div class="service_hero_bg svg_full">
                <svg width="883" height="806" viewBox="0 0 883 806" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.5" filter="url(#filter0_d_368_2651)">
                        <path
                            d="M288.603 0.5L556.76 395.47L285.328 797.5H4.94043L276.185 395.749L276.374 395.469L276.185 395.188L8.21777 0.5H288.603Z"
                            fill="url(#paint0_radial_368_2651)" stroke="black" />
                        <path
                            d="M680.564 588.973L493.875 313.896L682.845 33.9004L877.79 33.9004L689.008 313.617L688.818 313.897L689.009 314.178L875.506 588.973L680.564 588.973Z"
                            fill="url(#paint1_radial_368_2651)" stroke="black" />
                    </g>
                    <defs>
                        <filter id="filter0_d_368_2651" x="0" y="0" width="882.73" height="806"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="4" />
                            <feGaussianBlur stdDeviation="2" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_368_2651" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_368_2651"
                                result="shape" />
                        </filter>
                        <radialGradient id="paint0_radial_368_2651" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                            gradientTransform="translate(280.682 399) rotate(90) scale(399 276.682)">
                            <stop stop-color="#EB4250" />
                            <stop offset="1" stop-color="#0D0D0D" />
                        </radialGradient>
                        <radialGradient id="paint1_radial_368_2651" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                            gradientTransform="translate(686.001 311.436) rotate(-90) scale(278.036 192.73)">
                            <stop stop-color="#0D0D0D" stop-opacity="0" />
                            <stop offset="1" stop-color="#A9000E" />
                        </radialGradient>
                    </defs>
                </svg>

            </div>
        </div>
    </section>
    <section class="service_img">
        <?php
        $simg_desk = wp_get_attachment_image_url((tr_posts_field('service_img_desktop') ?? ''), 'full') ?: get_template_directory_uri() . '/images/bg_service.webp';
        $simg_mob = wp_get_attachment_image_url((tr_posts_field('service_img_mobile') ?? ''), 'full') ?: get_template_directory_uri() . '/images/img_service_mb.webp';
        ?>
        <div id="parallax" class="service_img_content " style="background-image: url('<?php echo esc_url($simg_desk); ?>');">
        </div>
    </section>
    <section class="home_services" data-section="white" id="service">
        <div class="home_services_top">
            <div class="home_services_top_bg left middle svg_full">
                <svg width="253" height="448" viewBox="0 0 253 448" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M45.0781 -59.5L252.379 221.487L42.5449 507.5H-174.013L35.6045 221.782L35.8213 221.486L35.6035 221.189L-171.478 -59.5H45.0781Z"
                        stroke="url(#paint0_linear_1363_5698)" />
                    <defs>
                        <linearGradient id="paint0_linear_1363_5698" x1="253" y1="224" x2="-175" y2="224"
                            gradientUnits="userSpaceOnUse">
                            <stop stop-color="white" stop-opacity="0.5" />
                            <stop offset="0.392803" stop-color="white" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>

            </div>
            <div class="home_services_top_bg right svg_full">
                <svg width="253" height="448" viewBox="0 0 253 448" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M207.922 -59.5L0.621094 221.487L210.455 507.5H427.013L217.396 221.782L217.179 221.486L217.396 221.189L424.478 -59.5H207.922Z"
                        stroke="url(#paint0_linear_1363_5700)" />
                    <defs>
                        <linearGradient id="paint0_linear_1363_5700" x1="0" y1="224" x2="428" y2="224"
                            gradientUnits="userSpaceOnUse">
                            <stop stop-color="white" stop-opacity="0.5" />
                            <stop offset="0.392803" stop-color="white" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                </svg>


            </div>
            <div class="container">
                <div class="home_services_sub txt_16 txt_medium block_arrow cl_fb">
                    <?php echo esc_html((tr_posts_field('service_main_subtitle') ?? '')); ?>
                </div>
                <h2 class="heading h2 h4_mb home_services_title txt_center cl_white">
                    <?php echo nl2br(esc_html((tr_posts_field('service_main_title') ?? ''))); ?>
                </h2>
                <div class=" home_services_des cl_fb heading h6 txt_uppercase txt_center">
                    <?php echo esc_html((tr_posts_field('service_main_desc') ?? '')); ?>
                </div>
            </div>
        </div>
        <div class="home_services_cms_wrap">
            <div class="home_services_cms">
                <div class="home_services_progress"></div>
                <div class="home_services_list">
                    <?php
                    $services = (tr_posts_field('home_services') ?? '');
                    if (is_array($services) && !empty($services)):
                        $colors = ['', 'bg_red', '']; // based on current design (empty, bg_red, empty)
                        foreach ($services as $index => $srv):
                            $color_class = $colors[$index % 3];
                            ?>
                            <div class="home_services_item <?php echo $color_class; ?>">
                                <div class="home_services_item_inner">
                                    <div class="container grid">
                                        <div class="home_services_content pa_container">
                                            <div class="home_services_item_bg middle svg_full">
                                                <svg width="418" height="556" viewBox="0 0 418 556" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M214.93 0.5L417.38 275.54L212.457 555.5H0.985352L205.693 275.835L205.91 275.539L205.692 275.243L3.46289 0.5H214.93Z" fill="url(#paint0_linear_1953_118)" stroke="url(#paint1_linear_1953_118)"/>
                                                <defs>
                                                <linearGradient id="paint0_linear_1953_118" x1="418" y1="278" x2="0" y2="278" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#F32B3B" stop-opacity="0.1"/>
                                                <stop offset="0.501395" stop-color="#F32B3B" stop-opacity="0"/>
                                                </linearGradient>
                                                <linearGradient id="paint1_linear_1953_118" x1="418" y1="278" x2="0" y2="278" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#E62636" stop-opacity="0.7"/>
                                                <stop offset="0.532815" stop-color="#101010" stop-opacity="0"/>
                                                </linearGradient>
                                                </defs>
                                                </svg>

                                            </div>
                                            <div class="home_services_content_top">
                                                <h3 class="home_services_content_title heading h2 h4_mb cl_linear">
                                                    <?php echo esc_html($srv['title'] ?? ''); ?>
                                                </h3>
                                                <div class="heading h6 home_services_content_sub">
                                                    <?php echo esc_html($srv['desc'] ?? ''); ?>
                                                </div>
                                                <div class="home_services_content_bottom_des heading h5 cl_eb">
                                                    <?php echo esc_html($srv['subtitle'] ?? ''); ?>
                                                </div>
                                            </div>
                                            <div class="home_services_content_bottom">

                                                <div class="home_services_content_bottom_list">
                                                    <?php
                                                    $service_list = $srv['list'] ?? [];
                                                    if (is_array($service_list)):
                                                        foreach ($service_list as $sl):
                                                            ?>
                                                            <div class="home_services_content_bottom_list_item h6 heading">
                                                                <svg width="9" height="13" viewBox="0 0 9 13" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M4.63313 0H0.0532536L4.42012 6.44248L0 13H4.57988L9 6.44248L4.63313 0Z"
                                                                        fill="#929292" />
                                                                </svg>
                                                                <?php echo esc_html($sl['item'] ?? ''); ?>
                                                            </div>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="home_services_img img_fullfill right_full">
                                            <?php
                                            $img_id = $srv['image'] ?? 0;
                                            $fallback_img = get_template_directory_uri() . '/images/home_service' . ($index == 0 ? '' : ($index + 1)) . '.jpg';
                                            $img_url = wp_get_attachment_image_url($img_id, 'full') ?: $fallback_img;
                                            ?>
                                            <img src="<?php echo esc_url($img_url); ?>" alt="" loading="lazy">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach;
                    else:
                        // Fallback static data if TypeRocket fields are empty
                        ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

    </section>
    <section class="about_cta">
        <div class="about_cta_img img_full">
            <?php $cta_img = wp_get_attachment_image_url((tr_posts_field('about_cta_img') ?? ''), 'full') ?: get_template_directory_uri() . '/images/img_cta.webp'; ?>
            <img src="<?php echo esc_url($cta_img); ?>" alt="" loading="lazy">
        </div>
        <div class="about_cta_inner_bg">
            <svg class="about_cta_bg_red" width="100" height="273" viewBox="0 0 100 273" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_1742_4330)">
                    <path
                        d="M-141.577 -191.969H-395.053L-153.367 136.065L-398 469.957H-144.525L100.109 136.065L-141.577 -191.969Z"
                        fill="url(#paint0_radial_1742_4330)" />
                </g>
                <defs>
                    <radialGradient id="paint0_radial_1742_4330" cx="0" cy="0" r="1"
                        gradientTransform="matrix(-249.054 330.963 -249.071 -187.403 100.109 138.994)"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#E62636" />
                        <stop offset="0.504728" stop-color="#E62636" stop-opacity="0.05" />
                    </radialGradient>
                    <clipPath id="clip0_1742_4330">
                        <rect width="100" height="273" fill="white" />
                    </clipPath>
                </defs>
            </svg>

            <svg class="about_cta_bg_top" width="243" height="171" viewBox="0 0 243 171" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_cta_top)">
                    <path d="M338.383 -130.719L117.811 170.278L-5.68018 -2.09668L88.4595 -130.719H338.383Z"
                        stroke="url(#paint0_cta_top)" stroke-width="0.5" />
                </g>
                <defs>
                    <linearGradient id="paint0_cta_top" x1="199.4" y1="-130.969" x2="47.985" y2="74.231"
                        gradientUnits="userSpaceOnUse">
                        <stop offset="0.6" stop-color="white" stop-opacity="0" />
                        <stop offset="1" stop-color="white" stop-opacity="0.5" />
                    </linearGradient>
                    <clipPath id="clip0_cta_top">
                        <rect width="243" height="171" fill="white" />
                    </clipPath>
                </defs>
            </svg>
            <svg class="about_cta_bg_bot" width="248" height="174" viewBox="0 0 248 174" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_cta_bot)">
                    <path d="M340.779 304.706H90.8535L-6.23242 173.155L120.201 0.775391L340.779 304.706Z"
                        stroke="url(#paint0_cta_bot)" stroke-width="0.5" />
                </g>
                <defs>
                    <linearGradient id="paint0_cta_bot" x1="50.4634" y1="93.9684" x2="204.36" y2="304.96"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="white" stop-opacity="0.5" />
                        <stop offset="0.396285" stop-color="white" stop-opacity="0" />
                    </linearGradient>
                    <clipPath id="clip0_cta_bot">
                        <rect width="248" height="175" fill="white" />
                    </clipPath>
                </defs>
            </svg>
        </div>
        <div class="about_cta_inner_overlay"></div>
        <div class="container grid">
            <a href="<?php echo esc_url((tr_posts_field('about_cta_link') ?? '')); ?>"
                class=" about_cta_inner_content h2 h5_mb heading ">
                <span class="cl_linear">
                    <?php echo nl2br(esc_html((tr_posts_field('about_cta_text1') ?? ''))); ?>
                </span>
                <div class="about_cta_link">
                    <div class="about_cta_inner_content_icon svg_full">
                        <svg width="21" height="30" viewBox="0 0 21 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.0342 0.000106148L0.121454 0L10.0872 14.8672L-7.74663e-05 29.9999L10.9127 30L21 14.8673L11.0342 0.000106148Z"
                                fill="#E62636" />
                        </svg>
                    </div>
                    <div class="about_cta_link_txt">
                        <span class="cl_linear">
                            <?php echo esc_html((tr_posts_field('about_cta_text2') ?? '')); ?>
                        </span>
                        <span class="cl_red middle">
                            <?php echo esc_html((tr_posts_field('about_cta_text3') ?? '')); ?>
                        </span>
                    </div>

                </div>
            </a>
        </div>
    </section>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var btn = document.querySelector('a[href="#service"]');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector('#service');
                if (target) {
                    if (typeof _ !== 'undefined' && typeof _.scrollTo === 'function') {
                        _.scrollTo(target, { offset: 0 });
                    } else if (typeof jQuery !== 'undefined') {
                        jQuery('html, body').animate({
                            scrollTop: jQuery(target).offset().top
                        }, 1200, 'swing');
                    } else {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        }
    });
    </script>
</main>

<?php get_footer(); ?>