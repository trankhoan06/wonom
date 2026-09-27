<?php
/**
 * Template Name: Case Study
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
<main class="main" data-namespace="caseStudy">
    <section class="casestudy_hero" data-init>
        <div class="container">
            <div class="casestudy_hero_inner grid">
                <h1 class="casestudy_hero_title heading h1 h4_mb cl_linear">
                    <?php echo nl2br(esc_html(tr_posts_field('cs_hero_title'))); ?>
                </h1>
                <div class="casestudy_hero_des txt_18"><?php echo nl2br(esc_html(tr_posts_field('cs_hero_desc'))); ?>
                </div>
            </div>
            <div class="casestudy_hero_bg svg_full">
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
    <section class="casestudy_content" data-init>
        <div class="container">
            <div class="home_case_content_list right_full left_full">
                <?php
                $args = array(
                    'post_type' => 'work',
                    'post_status' => 'publish',
                    'posts_per_page' => 6,
                    'paged' => 1
                );
                $cs_query = new WP_Query($args);
                if ($cs_query->have_posts()):
                    while ($cs_query->have_posts()):
                        $cs_query->the_post();
                        get_template_part('template-parts/content', 'case-study');
                    endwhile;
                    wp_reset_postdata();
                endif; ?>
            </div>
            <?php
            $max_pages = isset($cs_query) ? $cs_query->max_num_pages : 1;
            ?>
            <a href="#" id="load-more-case-study" data-page="1" data-max="<?php echo $max_pages; ?>"
                class="home_case_seeview button_hover hover_txt txt_uppercase txt_14 txt_medium cl_be" <?php if ($max_pages <= 1)
                    echo 'style="display:none;"'; ?>>
                <div class="hover_txt_grid">
                    <?php $seemore = esc_html(tr_posts_field('cs_see_more_text') ?: 'SEE MORE'); ?>
                    <span class="init"><?php echo $seemore; ?></span>
                    <span class="active"><?php echo $seemore; ?></span>
                </div>
            </a>
        </div>
    </section>
    <section class="about_cta">
        <div class="about_cta_img img_full">
            <?php $cta_img = wp_get_attachment_image_url((tr_posts_field('cs_cta_img') ?? ''), 'full') ?: get_template_directory_uri() . '/images/img_cta.webp'; ?>
            <img src="<?php echo esc_url($cta_img); ?>" alt="">
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
            <a href="<?php echo esc_url((tr_posts_field('cs_cta_link') ?? '')); ?>"
                class=" about_cta_inner_content h2 h5_mb heading ">
                <span class="cl_linear">
                    <?php echo nl2br(esc_html((tr_posts_field('cs_cta_text1') ?? ''))); ?>
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
                        <span class=" cl_linear">
                            <?php echo esc_html((tr_posts_field('cs_cta_text2') ?? '')); ?>
                        </span>
                        <span class="cl_red middle">
                            <?php echo esc_html((tr_posts_field('cs_cta_text3') ?? '')); ?>
                        </span>
                    </div>

                </div>
            </a>
        </div>
    </section>
</main>

<?php get_footer(); ?>