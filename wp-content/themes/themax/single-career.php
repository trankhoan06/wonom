<?php
/**
 * Template Name: Career Detail
 * Template Post Type: career, page
 */
get_header();
?>
<main class="main" data-namespace="careerDetail">
    <?php
    $is_vi = function_exists('pll_current_language') && pll_current_language() === 'vi';
    $pop_title = tr_options_field($is_vi ? 'career_popup_title_text_vi' : 'career_popup_title_text') ?: ($is_vi ? 'NôP đƠN ỨNG TUYểN Vị TRí NàY' : 'SUBMIT YOUR APPLICATION FOR THIS POSITION');
    $pop_name = tr_options_field($is_vi ? 'career_popup_name_vi' : 'career_popup_name') ?: ($is_vi ? 'Họ và tên' : 'Your name');
    $pop_email = tr_options_field($is_vi ? 'career_popup_email_vi' : 'career_popup_email') ?: ($is_vi ? 'Địa chỉ Email' : 'Email address');
    $pop_phone = tr_options_field($is_vi ? 'career_popup_phone_vi' : 'career_popup_phone') ?: ($is_vi ? 'Số điện thoại' : 'Phone number');
    $pop_upload_btn = tr_options_field($is_vi ? 'career_popup_upload_btn_vi' : 'career_popup_upload_btn') ?: ($is_vi ? 'Tải CV lên' : 'Upload CV');
    $pop_upload_note = tr_options_field($is_vi ? 'career_popup_upload_note_vi' : 'career_popup_upload_note') ?: ($is_vi ? 'Tải lên file PDF, PPT, PPTX, DOC, DOCX, JPG, PNG (tối đa 5 MB)' : 'Upload PDF, PPT, PPTX, DOC, DOCX, JPG, PNG files (maximum 5 MB)');
    $pop_portfolio = tr_options_field($is_vi ? 'career_popup_portfolio_vi' : 'career_popup_portfolio') ?: 'Link Portfolio';
    $pop_job_ph = tr_options_field($is_vi ? 'career_popup_job_placeholder_vi' : 'career_popup_job_placeholder') ?: ($is_vi ? 'Vui lòng chọn vị trí ứng tuyển' : 'Please select the title job');
    $pop_intro = tr_options_field($is_vi ? 'career_popup_intro_vi' : 'career_popup_intro') ?: ($is_vi ? 'Giới thiệu ngắn gọn về bản thân' : 'A brief introduction about myself');
    $pop_submit = tr_options_field($is_vi ? 'career_popup_submit_vi' : 'career_popup_submit') ?: ($is_vi ? 'NỘP ĐƠN ỨNG TUYỂN' : 'SUBMIT JOB APPLICATION');

    $err_name = $is_vi ? 'Vui lòng nhập tên của bạn' : 'Please enter your name';
    $err_email = $is_vi ? 'Vui lòng nhập địa chỉ email hợp lệ' : 'Please enter a valid email';
    $err_phone = $is_vi ? 'Vui lòng nhập số điện thoại' : 'Please enter your phone number';
    $err_cv = $is_vi ? 'Vui lòng tải lên CV của bạn' : 'Please upload your CV';
    ?>
    <section class="careerdetail_info" data-init>
        <div class="careerdetail_info_bg svg_full">
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
                    <filter id="filter0_d_368_2651" x="0" y="0" width="882.73" height="806" filterUnits="userSpaceOnUse"
                        color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                            result="hardAlpha" />
                        <feOffset dy="4" />
                        <feGaussianBlur stdDeviation="2" />
                        <feComposite in2="hardAlpha" operator="out" />
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_368_2651" />
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_368_2651" result="shape" />
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
        <div class="container">
            <div class="careerdetail_info_inner grid">
                <div class="careerdetail_info_content">
                    <h1 class="careerdetail_info_content_title heading h2 h4_mb cl_linear"><?php the_title(); ?></h1>
                    <div class="careerdetail_info_content_job">
                        <?php
                        $job_info = tr_posts_field('job_info');
                        if (is_array($job_info) && !empty($job_info)):
                            foreach ($job_info as $info):
                                ?>
                                <div class="careerdetail_info_content_job_item">
                                    <div class="careerdetail_info_content_job_item_name txt_uppercase txt_medium cl_gray">
                                        <?php echo esc_html($info['label'] ?? ''); ?>
                                    </div>
                                    <div class="careerdetail_info_content_job_item_txt  cl_eb txt_16">
                                        <?php echo esc_html($info['value'] ?? ''); ?>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <div class="careerdetail_info_content_des">
                        <?php
                        $job_desc = tr_posts_field('job_description');
                        if (is_array($job_desc) && !empty($job_desc)):
                            foreach ($job_desc as $section):
                                ?>
                                <div class="careerdetail_info_content_des_item cl_eb">
                                    <div class="careerdetail_info_content_des_item_title h5 h6_mb heading">
                                        <?php echo esc_html($section['title'] ?? ''); ?>
                                    </div>
                                    <div class="careerdetail_info_content_des_item_inner">
                                        <?php
                                        $content = $section['content'] ?? '';
                                        $lines = explode("\n", $content);
                                        foreach ($lines as $line) {
                                            $line = trim($line);
                                            if (!empty($line)) {
                                                echo '<div class="careerdetail_info_content_des_item_txt txt_16">' . esc_html($line) . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <div class="careerdetail_info_content_form">
                        <div class="careerdetail_info_content_form_title heading h5 h6_mb cl_red txt_uppercase"><?php echo esc_attr($pop_title); ?>
                        </div>
                        <form class="careerdetail_form" novalidate>
                            <input type="hidden" name="job_id" value="<?php echo get_the_ID(); ?>">
                            <div class="careerdetail_form_row">
                                <input type="text" name="career_name" placeholder="<?php echo esc_attr($pop_name); ?>"
                                    class="careerdetail_form_input" required>
                                <span class="error_msg"><?php echo esc_html($err_name); ?></span>
                            </div>
                            <div class="careerdetail_form_row col-2">
                                <div class="careerdetail_form_col">
                                    <input type="email" name="career_email" placeholder="<?php echo esc_attr($pop_email); ?>*"
                                        class="careerdetail_form_input" required>
                                    <span class="error_msg"><?php echo esc_html($err_email); ?></span>
                                </div>
                                <div class="careerdetail_form_col">
                                    <input type="tel" name="career_phone" placeholder="<?php echo esc_attr($pop_phone); ?>*"
                                        class="careerdetail_form_input" required>
                                    <span class="error_msg"><?php echo esc_html($err_phone); ?></span>
                                </div>
                            </div>
                            <div class="careerdetail_form_row careerdetail_form_upload">
                                <label class="careerdetail_form_upload_btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <?php echo esc_attr($pop_upload_btn); ?>
                                    <input type="file" name="career_cv" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.png"
                                        style="display:none;" required>
                                </label>
                                <span class="careerdetail_form_upload_txt"><?php echo esc_html($pop_upload_note); ?></span>
                                <span class="error_msg" style="bottom: -2rem; left: 0;"><?php echo esc_html($err_cv); ?></span>
                            </div>
                            <div class="careerdetail_form_row">
                                <input type="text" name="career_portfolio" placeholder="<?php echo esc_attr($pop_portfolio); ?>"
                                    class="careerdetail_form_input">
                            </div>
                            <div class="careerdetail_form_row">
                                <input type="text" name="career_intro" placeholder="<?php echo esc_attr($pop_intro); ?>"
                                    class="careerdetail_form_input">
                            </div>

                            <div class="careerdetail_form_row submit_row">
                                <button type="submit"
                                    class="btn_submit button_hover hover_txt txt_14 txt_uppercase cl_be">
                                    <div class="hover_txt_grid">
                                        <span class="init"><?php echo esc_attr($pop_submit); ?></span>
                                        <span class="active"><?php echo esc_attr($pop_submit); ?></span>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="careerdetail_info_other">
                    <div class="careerdetail_info_other_inner">
                        <div class="careerdetail_info_other_subtitle txt_uppercase block_arrow txt_16">OTHER JOBS
                        </div>
                        <div class="careerdetail_info_other_list">
                            <?php
                            $current_id = get_the_ID();
                            $args = array(
                                'post_type' => 'career',
                                'posts_per_page' => -1,
                                'post_status' => 'publish',
                                'post__not_in' => array($current_id),
                                'order' => 'DESC',
                                'orderby' => 'date'
                            );
                            $query = new WP_Query($args);
                            if ($query->have_posts()):
                                while ($query->have_posts()):
                                    $query->the_post();
                                    ?>
                                    <a href="<?php the_permalink(); ?>" class="career_why_list_item item_line">
                                        <div class="career_why_list_item_position item_line_title txt_18 cl_linear">
                                            <?php the_title(); ?>
                                        </div>
                                        <div class="home_case_content_item_txt_icon">
                                            <div class="home_case_content_item_txt_icon_wrap svg_full"><svg width="48"
                                                    height="48" viewBox="0 0 48 48" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_1164_690)">
                                                        <path d="M12 12H36.0003V36.0003" stroke="#929292" stroke-width="2"
                                                            stroke-linejoin="round" />
                                                        <path d="M12 36.0003L36.0003 12" stroke="#929292" stroke-width="2"
                                                            stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_1164_690">
                                                            <rect width="48" height="48" fill="white" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </div>
                                            <div class="home_case_content_item_txt_icon_wrap svg_full active"><svg width="48"
                                                    height="48" viewBox="0 0 48 48" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_1164_690)">
                                                        <path d="M12 12H36.0003V36.0003" stroke="#F32B3B" stroke-width="2"
                                                            stroke-linejoin="round" />
                                                        <path d="M12 36.0003L36.0003 12" stroke="#F32B3B" stroke-width="2"
                                                            stroke-linejoin="round" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_1164_690">
                                                            <rect width="48" height="48" fill="white" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            endif;
                            ?>
                        </div>
                    </div>
                    <div class="careerdetail_info_other_share">
                        <div class="careerdetail_info_other_subtitle txt_uppercase block_arrow txt_16">SHARE</div>
                        <div class="careerdetail_info_other_share_social">
                            <a href="#" class="careerdetail_info_other_share_social_icon svg_full btn-copy-link"
                                data-url="<?php echo esc_url(get_permalink()); ?>" title="Copy Link">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626" />
                                    <path
                                        d="M25.4134 29.6568L23.9992 31.0711C23.0615 32.0087 21.7897 32.5355 20.4636 32.5355C19.1376 32.5355 17.8658 32.0087 16.9281 31.0711C15.9904 30.1334 15.4636 28.8616 15.4636 27.5355C15.4636 26.2094 15.9904 24.9377 16.9281 24L18.3423 22.5858"
                                        stroke="#BEBEBE" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M22.5859 18.3431L24.0002 16.9289C24.9378 15.9912 26.2096 15.4645 27.5357 15.4645C28.8618 15.4645 30.1335 15.9912 31.0712 16.9289C32.0089 17.8666 32.5357 19.1384 32.5357 20.4645C32.5357 21.7905 32.0089 23.0623 31.0712 24L29.657 25.4142"
                                        stroke="#BEBEBE" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M21.1699 26.8284L26.8268 21.1715" stroke="#BEBEBE" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <a href="#" class="careerdetail_info_other_share_social_icon svg_full btn-share-fb"
                                data-url="<?php echo esc_url(get_permalink()); ?>"
                                data-title="<?php echo esc_attr(get_the_title()); ?>">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1042_105)">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626" />
                                        <path
                                            d="M28.1836 25.125L28.6758 21.8906H25.5469V19.7812C25.5469 18.8672 25.9688 18.0234 27.375 18.0234H28.8164V15.2461C28.8164 15.2461 27.5156 15 26.2852 15C23.7188 15 22.0312 16.582 22.0312 19.3945V21.8906H19.1484V25.125H22.0312V33H25.5469V25.125H28.1836Z"
                                            fill="#BEBEBE" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1042_105">
                                            <rect width="48" height="48" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                            <a href="#" class="careerdetail_info_other_share_social_icon svg_full btn-share-in"
                                data-url="<?php echo esc_url(get_permalink()); ?>"
                                data-title="<?php echo esc_attr(get_the_title()); ?>">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1042_121)">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626" />
                                        <path
                                            d="M19.6406 30.75V20.2383H16.3711V30.75H19.6406ZM17.9883 18.832C19.043 18.832 19.8867 17.9531 19.8867 16.8984C19.8867 15.8789 19.043 15.0352 17.9883 15.0352C16.9688 15.0352 16.125 15.8789 16.125 16.8984C16.125 17.9531 16.9688 18.832 17.9883 18.832ZM31.8398 30.75H31.875V24.9844C31.875 22.1719 31.2422 19.9922 27.9375 19.9922C26.3555 19.9922 25.3008 20.8711 24.8438 21.6797H24.8086V20.2383H21.6797V30.75H24.9492V25.5469C24.9492 24.1758 25.1953 22.875 26.8828 22.875C28.5703 22.875 28.6055 24.4219 28.6055 25.6523V30.75H31.8398Z"
                                            fill="#BEBEBE" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1042_121">
                                            <rect width="48" height="48" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    $lang_suffix = (function_exists('pll_current_language') && pll_current_language() == 'vi') ? '_vi' : '';
    $cta_img_val = tr_options_field('tr_theme_options.career_cta_img' . $lang_suffix);
    if (empty($cta_img_val))
        $cta_img_val = tr_options_field('tr_theme_options.career_cta_img');
    $cta_img = wp_get_attachment_image_url(($cta_img_val ?? ''), 'full') ?: get_template_directory_uri() . '/images/img_cta.webp';
    $cta_link = tr_options_field('tr_theme_options.career_cta_link' . $lang_suffix) ?: tr_options_field('tr_theme_options.career_cta_link');
    $cta_title = tr_options_field('tr_theme_options.career_cta_title' . $lang_suffix) ?: tr_options_field('tr_theme_options.career_cta_title');
    $cta_des = tr_options_field('tr_theme_options.career_cta_des' . $lang_suffix) ?: tr_options_field('tr_theme_options.career_cta_des');
    $cta_btn_text = tr_options_field('tr_theme_options.career_cta_btn_text' . $lang_suffix) ?: tr_options_field('tr_theme_options.career_cta_btn_text');
    ?>
    <section class="about_cta">
        <div class="about_cta_img img_full">
            <img src="<?php echo esc_url($cta_img); ?>" alt="" loading="lazy">
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
                        <?php echo nl2br(esc_html($cta_title ?? '')); ?>
                    </div>
                    <div class="about_cta_inner_content_des txt_18">
                        <?php echo nl2br(esc_html($cta_des ?? '')); ?>
                    </div>
                    <button
                        class="about_cta_inner_content_title_button hover_txt button_hover txt_uppercase txt_14 cl_be">
                        <div class="hover_txt_grid">
                            <?php $cta_btn_txt_val = esc_html($cta_btn_text ?? ''); ?>
                            <span class="init"><?php echo $cta_btn_txt_val; ?></span>
                            <span class="active"><?php echo $cta_btn_txt_val; ?></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </section>
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
</main>
<?php get_footer(); ?>