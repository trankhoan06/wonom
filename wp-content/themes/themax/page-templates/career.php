<?php
/**
 * Template Name: Career
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
<main class="main" data-namespace="career">
    <section class="service_hero" data-init>
        <div class="container grid">
            <h1 class="service_hero_left heading h1 h4_mb cl_linear"><?php echo tr_posts_field('career_hero_title'); ?>
            </h1>
            <div class="service_hero_right">
                <div class="service_hero_right_txt txt_18"><?php echo tr_posts_field('career_hero_desc'); ?></div>
            </div>
            <a href="#career"
                class="service_hero_right_discover hover_txt txt_16 cl_eb">
                <div class="hover_txt_grid">
                    <?php $hero_btn = esc_html(tr_posts_field('career_hero_btn_text')); ?>
                    <span class="init"><?php echo $hero_btn; ?></span>
                    <span class="active"><?php echo $hero_btn; ?></span>
                </div>
                <div class="service_hero_right_discover_icon_wrap">
                    <div class="service_hero_right_discover_icon svg_full">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
    <section class="career_img" data-init>
        <?php
        $img_desk = wp_get_attachment_image_url(tr_posts_field('career_img_desktop'), 'full') ?: get_template_directory_uri() . '/images/image_career.webp';
        ?>
        <div id="parallax" class="career_img_inner" style="background-image: url('<?php echo esc_url($img_desk); ?>');">
        </div>
    </section>
    <section class="career_why" data-init>
        <div class="container">
            <div class="career_why_info grid">
                <div class="career_why_info_left">
                    <div class="career_why_info_subtitle block_arrow">
                        <?php echo esc_html(tr_posts_field('career_why_subtitle')); ?>
                    </div>
                </div>
                <div class="career_why_info_content">
                    <div class="career_why_info_content_title heading h2 h4_mb cl_linear">
                        <?php echo nl2br(esc_html(tr_posts_field('career_why_title'))); ?>
                    </div>
                    <div class="career_why_info_content_subtitle heading h6 cl_red txt_uppercase">
                        <?php echo esc_html(tr_posts_field('career_why_desc')); ?>
                    </div>
                    <div class="career_why_info_content_card">
                        <?php
                        $why_list = tr_posts_field('career_why_list');
                        if (is_array($why_list) && !empty($why_list)):
                            foreach ($why_list as $item):
                                $icon = wp_get_attachment_image_url($item['icon'], 'full') ?: get_template_directory_uri() . '/images/icon-careers-why.svg';
                                ?>
                                <div class="career_why_info_content_card_item item_line">
                                    <div class="career_why_info_content_card_item_icon img_full">
                                        <img src="<?php echo esc_url($icon); ?>" alt="">
                                    </div>
                                    <div class="career_why_info_content_card_item_title item_line_title cl_linear heading h5">
                                        <?php echo esc_html($item['title'] ?? ''); ?>
                                    </div>
                                    <div class="career_why_info_content_card_item_des txt_14">
                                        <?php echo esc_html($item['desc'] ?? ''); ?>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                <div class="career_why_img_bg desktop img_full">
                    <?php $why_bg = wp_get_attachment_image_url(tr_posts_field('career_why_bg'), 'full') ?: get_template_directory_uri() . '/images/bg_why.webp'; ?>
                    <img src="<?php echo esc_url($why_bg); ?>" alt="">
                </div>
            </div>
            <div class="career_why_img">
                <div class="career_why_img_inner img_full">
                    <?php
                    $ch_desk = wp_get_attachment_image_url(tr_posts_field('career_why_choose_desktop'), 'full') ?: get_template_directory_uri() . '/images/career_choose.webp';
                    $ch_mob = wp_get_attachment_image_url(tr_posts_field('career_why_choose_mobile'), 'full') ?: get_template_directory_uri() . '/images/career_choose_mb.webp';
                    ?>
                    <img class="middle" src="<?php echo esc_url($ch_desk); ?>" alt="">
                    <img class="mobile" src="<?php echo esc_url($ch_mob); ?>" alt="">
                </div>
                <div class="career_why_img_overlay"></div>
                <div class="career_why_img_txt heading h4 h5_mb cl_linear">
                    <?php echo esc_html(tr_posts_field('career_why_bottom_text')); ?>
                </div>
            </div>
            <div class="career_why_list left_full right_full" id="career">
                <div class="career_why_list_title cl_gray grid middle">
                    <div class="career_why_list_item_position"><?php echo esc_html(tr_posts_field('career_list_col1') ?: 'POSITION'); ?></div>
                    <div class="career_why_list_item_level"><?php echo esc_html(tr_posts_field('career_list_col2') ?: 'LEVEL'); ?></div>
                    <div class="career_why_list_item_quality"><?php echo esc_html(tr_posts_field('career_list_col3') ?: 'QUANTITY'); ?></div>
                    <div class="career_why_list_item_deadline"><?php echo esc_html(tr_posts_field('career_list_col4') ?: 'DEADLINE'); ?></div>
                </div>
                <?php
                $args = array(
                    'post_type' => 'career',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'order' => 'DESC',
                    'orderby' => 'date'
                );
                $career_query = new WP_Query($args);
                if ($career_query->have_posts()) :
                    while ($career_query->have_posts()) : $career_query->the_post();
                        $job_info = tr_posts_field('job_info', get_the_ID());
                        $level = '';
                        $quantity = '';
                        $deadline = '';
                        if (is_array($job_info) && !empty($job_info)) {
                            $job_info_values = array_values($job_info);
                            $level = isset($job_info_values[1]['value']) ? $job_info_values[1]['value'] : '';
                            $quantity = isset($job_info_values[2]['value']) ? $job_info_values[2]['value'] : '';
                            $deadline = isset($job_info_values[3]['value']) ? $job_info_values[3]['value'] : '';
                        }
                ?>
                <a href="<?php the_permalink(); ?>" class="career_why_list_item grid item_line">
                    <div class="career_why_list_item_position item_line_title heaeding h5 cl_linear"><?php the_title(); ?></div>
                    <div class="career_why_list_item_level cl_eb txt_16"><?php echo esc_html($level); ?></div>
                    <div class="career_why_list_item_quality cl_eb txt_16"><?php echo esc_html($quantity); ?></div>
                    <div class="career_why_list_item_deadline cl_eb txt_16"><?php echo esc_html($deadline); ?></div>
                    <div class="home_case_content_item_txt_icon middle">
                        <div class="home_case_content_item_txt_icon_wrap svg_full"><svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1164_690)"><path d="M12 12H36.0003V36.0003" stroke="#929292" stroke-width="2" stroke-linejoin="round" /><path d="M12 36.0003L36.0003 12" stroke="#929292" stroke-width="2" stroke-linejoin="round" /></g><defs><clipPath id="clip0_1164_690"><rect width="48" height="48" fill="white" /></clipPath></defs></svg></div>
                        <div class="home_case_content_item_txt_icon_wrap svg_full active"><svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_1164_690)"><path d="M12 12H36.0003V36.0003" stroke="#F32B3B" stroke-width="2" stroke-linejoin="round" /><path d="M12 36.0003L36.0003 12" stroke="#F32B3B" stroke-width="2" stroke-linejoin="round" /></g><defs><clipPath id="clip0_1164_690"><rect width="48" height="48" fill="white" /></clipPath></defs></svg></div>
                    </div>
                </a>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>
    <section class="about_cta">
        <div class="about_cta_img img_full">
            <?php $cta_img = wp_get_attachment_image_url(tr_posts_field('career_cta_img'), 'full') ?: get_template_directory_uri() . '/images/img_cta.webp'; ?>
            <img src="<?php echo esc_url($cta_img); ?>" alt="">
        </div>
        <div class="about_cta_inner_bg">
            <svg class="about_cta_bg_red" width="100" height="273" viewBox="0 0 100 273" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_cta_red)">
                    <path
                        d="M-141.577 -191.969H-395.053L-153.367 136.065L-398 469.957H-144.525L100.109 136.065L-141.577 -191.969Z"
                        fill="url(#paint0_cta_red)" />
                </g>
                <defs>
                    <radialGradient id="paint0_cta_red" cx="0" cy="0" r="1"
                        gradientTransform="matrix(-249.054 330.963 -249.071 -187.403 100.109 138.994)"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#E62636" stop-opacity="0.7" />
                        <stop offset="0.504728" stop-color="#E62636" stop-opacity="0.05" />
                    </radialGradient>
                    <clipPath id="clip0_cta_red">
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
        <div class="container">
            <div class=" about_cta_inner_content">
                <div class="about_cta_inner_content_title grid">
                    <div class="about_cta_inner_content_title_txt heading h2 cl_linear h5_mb">
                        <?php echo nl2br(esc_html(tr_posts_field('career_cta_title'))); ?>
                    </div>
                    <div class="about_cta_inner_content_des txt_18">
                        <?php echo nl2br(esc_html(tr_posts_field('career_cta_desc'))); ?>
                    </div>
                    <button 
                        class="about_cta_inner_content_title_button hover_txt button_hover txt_uppercase txt_14 cl_be">
                        <div class="hover_txt_grid">
                            <?php $cta_btn_txt = esc_html(tr_posts_field('career_cta_btn_text')); ?>
                            <span class="init"><?php echo $cta_btn_txt; ?></span>
                            <span class="active"><?php echo $cta_btn_txt; ?></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Career Popup Modal -->
    <?php
    $is_vi = function_exists('pll_current_language') && pll_current_language() === 'vi';
    $pop_name = tr_options_field($is_vi ? 'career_popup_name_vi' : 'career_popup_name') ?: ($is_vi ? 'Họ và tên' : 'Your name');
    $pop_email = tr_options_field($is_vi ? 'career_popup_email_vi' : 'career_popup_email') ?: ($is_vi ? 'Địa chỉ Email' : 'Email address');
    $pop_phone = tr_options_field($is_vi ? 'career_popup_phone_vi' : 'career_popup_phone') ?: ($is_vi ? 'Số điện thoại' : 'Phone number');
    $pop_upload_btn = tr_options_field($is_vi ? 'career_popup_upload_btn_vi' : 'career_popup_upload_btn') ?: ($is_vi ? 'Tải CV lên' : 'Upload CV');
    $pop_upload_note = tr_options_field($is_vi ? 'career_popup_upload_note_vi' : 'career_popup_upload_note') ?: ($is_vi ? 'Tải lên file PDF, PPT, PPTX, DOC, DOCX, JPG, PNG (tối đa 5 MB)' : 'Upload PDF, PPT, PPTX, DOC, DOCX, JPG, PNG files (maximum 5 MB)');
    $pop_portfolio = tr_options_field($is_vi ? 'career_popup_portfolio_vi' : 'career_popup_portfolio') ?: 'Link Portfolio';
    $pop_job_ph = tr_options_field($is_vi ? 'career_popup_job_placeholder_vi' : 'career_popup_job_placeholder') ?: ($is_vi ? 'Vui lòng chọn vị trí ứng tuyển' : 'Please select the title job');
    $pop_intro = tr_options_field($is_vi ? 'career_popup_intro_vi' : 'career_popup_intro') ?: ($is_vi ? 'Giới thiệu ngắn gọn về bản thân' : 'A brief introduction about myself');
    $pop_submit = tr_options_field($is_vi ? 'career_popup_submit_vi' : 'career_popup_submit') ?: ($is_vi ? 'NỘP ĐƠN ỨNG TUYỂN' : 'SUBMIT JOB APPLICATION');
    ?>
    <div class="career_popup_overlay" id="careerPopupOverlay">
        <div class="career_popup_content">
            <button class="career_popup_close" id="careerPopupClose">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
            </button>
            <form class="career_popup_form" action="" method="POST" enctype="multipart/form-data">
                <div class="career_popup_form_row">
                    <input class="txt_16" type="text" name="your_name" placeholder="<?php echo esc_attr($pop_name); ?>" required>
                </div>
                <div class="career_popup_form_row col-2">
                    <input class="txt_16" type="email" name="email_address" placeholder="<?php echo esc_attr($pop_email); ?>" required>
                    <input class="txt_16" type="tel" name="phone_number" placeholder="<?php echo esc_attr($pop_phone); ?>" required>
                </div>
                <div class="career_popup_form_row file_upload_row">
                    <label class="career_popup_upload_btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                        <?php echo esc_html($pop_upload_btn); ?>
                        <input type="file" name="upload_cv" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.png" hidden>
                    </label>
                    <div class="career_popup_upload_note"><?php echo esc_html($pop_upload_note); ?></div>
                </div>
                <div class="career_popup_form_row">
                    <input class="txt_16" type="url" name="link_portfolio" placeholder="<?php echo esc_attr($pop_portfolio); ?>">
                </div>
                <div class="career_popup_form_row select_row">
                    <select class="txt_16" name="title_job" required>
                        <option value="" disabled selected><?php echo esc_html($pop_job_ph); ?></option>
                        <?php 
                        $career_posts = new WP_Query(array(
                            'post_type' => 'career',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                        ));
                        if ($career_posts->have_posts()) : 
                            while ($career_posts->have_posts()) : $career_posts->the_post(); 
                        ?>
                        <option value="<?php echo esc_attr(get_the_title()); ?>"><?php echo esc_html(get_the_title()); ?></option>
                        <?php 
                            endwhile; 
                            wp_reset_postdata(); 
                        endif; 
                        ?>
                    </select>
                    <svg class="select_icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M6 9l6 6 6-6"/></svg>
                </div>
                <div class="career_popup_form_row">
                    <input class="txt_16" type="text" name="introduction" placeholder="<?php echo esc_attr($pop_intro); ?>">
                </div>
                <div class="career_popup_form_submit">
                    <button type="submit" class="career_popup_submit_btn  hover_txt button_hover">
                        <div class="hover_txt_grid">
                            <span class="init"><?php echo esc_html($pop_submit); ?></span>
                            <span class="active"><?php echo esc_html($pop_submit); ?></span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var btn = document.querySelector('a[href="#career"]');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector('#career');
                if (target) {
                    if (typeof _ !== 'undefined' && typeof _.scrollTo === 'function') {
                        _.scrollTo(target, { offset: 0 });
                    } else if (typeof jQuery !== 'undefined') {
                        jQuery('html, body').animate({
                            scrollTop: jQuery(target).offset().top
                        }, 1200, 'swing');
                    } else {
                        target.scrollIntoView({ behavior: 'smooth' });\}
                }
            });
        }
    });
    </script>
</main>

<?php get_footer(); ?>