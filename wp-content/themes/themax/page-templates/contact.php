<?php
/**
 * Template Name: Contact
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
<main class="main" data-namespace="contact">
    <section class="contact_hero" data-init>
        <div class="contact_hero_bg svg_full">
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
        <div class="contact_hero_vecto svg_full">
            <svg width="499" height="650" viewBox="0 0 499 650" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M256.17 -5.5L497.487 322.034L253.223 655.426H0.986328L245.036 322.33L245.254 322.033L245.036 321.737L3.93652 -5.5H256.17Z"
                    fill="url(#paint0_linear_733_7398)" stroke="url(#paint1_linear_733_7398)" />
                <defs>
                    <linearGradient id="paint0_linear_733_7398" x1="498.109" y1="324.963" x2="0" y2="324.963"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F32B3B" stop-opacity="0.1" />
                        <stop offset="0.680138" stop-color="#F32B3B" stop-opacity="0" />
                    </linearGradient>
                    <linearGradient id="paint1_linear_733_7398" x1="498.109" y1="324.963" x2="0" y2="324.963"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#E62636" stop-opacity="0.7" />
                        <stop offset="0.607911" stop-color="#101010" stop-opacity="0" />
                    </linearGradient>
                </defs>
            </svg>

        </div>
        <div class="container grid">
            <div class="contact_hero_content">
                <h1 class="contact_hero_content_title cl_linear h1 h4_mb heading">
                    <?php echo wp_kses_post(tr_posts_field('contact_hero_title')); ?></h1>
                <div class="contact_hero_content_subtitle cl_eb txt_18">
                    <?php echo wp_kses_post(tr_posts_field('contact_hero_subtitle')); ?></div>
            </div>
            <div class="contact_hero_content_info">
                <div class="contact_hero_content_info_name txt_medium txt_18">
                    <?php echo esc_html(tr_posts_field('contact_hero_name')); ?></div>
                <div class="contact_hero_content_info_tel txt_16">
                    <?php 
                    $hero_tel = tr_posts_field('contact_hero_tel'); 
                    $hero_tel_clean = preg_replace('/[^0-9+]/', '', $hero_tel);
                    ?>
                    <a href="tel:<?php echo esc_attr($hero_tel_clean); ?>" style="color: inherit; text-decoration: none; white-space: nowrap;"><?php echo esc_html($hero_tel); ?></a>
                </div>
            </div>
            <div class="contact_hero_form">
                <?php $form_shortcode = tr_posts_field('contact_form_shortcode');
                if ($form_shortcode):
                    echo do_shortcode($form_shortcode);
                else:
                    ?>
                    <?php
                    $contact_name = tr_posts_field('contact_form_name') ?: 'Your name';
                    $contact_email = tr_posts_field('contact_form_email') ?: 'Email address';
                    $contact_phone = tr_posts_field('contact_form_phone') ?: 'Phone number';
                    $contact_company = tr_posts_field('contact_form_company') ?: 'Company name';
                    $contact_advice = tr_posts_field('contact_form_advice') ?: 'What area do you need advice on?';
                    $contact_submit = tr_posts_field('contact_form_submit') ?: 'SUBMIT';

                    $err_c_name = tr_posts_field('contact_err_name') ?: 'Please enter your name';
                    $err_c_email = tr_posts_field('contact_err_email') ?: 'Please enter a valid email';
                    $err_c_phone = tr_posts_field('contact_err_phone') ?: 'Please enter your phone number';
                    ?>
                    <form class="contact_form" novalidate>
                        <div class="contact_form_row">
                            <input type="text" name="contact_name" placeholder="<?php echo esc_attr($contact_name); ?>*" class="contact_form_input"
                                required>
                            <span class="error_msg"><?php echo esc_html($err_c_name); ?></span>
                        </div>
                        <div class="contact_form_row col-2">
                            <div class="contact_form_col">
                                <input type="email" name="contact_email" placeholder="<?php echo esc_attr($contact_email); ?>*"
                                    class="contact_form_input" required>
                                <span class="error_msg"><?php echo esc_html($err_c_email); ?></span>
                            </div>
                            <div class="contact_form_col">
                                <input type="tel" name="contact_phone" placeholder="<?php echo esc_attr($contact_phone); ?>*"
                                    class="contact_form_input" required>
                                <span class="error_msg"><?php echo esc_html($err_c_phone); ?></span>
                            </div>
                        </div>
                        <div class="contact_form_row">
                            <input type="text" name="contact_company" placeholder="<?php echo esc_attr($contact_company); ?>" class="contact_form_input">
                        </div>
                        <div class="contact_form_row">
                            <input type="text" name="contact_advice" placeholder="<?php echo esc_attr($contact_advice); ?>"
                                class="contact_form_input">
                        </div>

                        <div class="contact_form_row submit_row">
                            <button type="submit" class="btn_submit button_hover hover_txt txt_14 txt_uppercase cl_be">
                                <div class="hover_txt_grid">
                                    <span class="init"><?php echo esc_html($contact_submit); ?></span>
                                    <span class="active"><?php echo esc_html($contact_submit); ?></span>
                                </div>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section class="contact_img">
        <?php
        $cimg_desk = wp_get_attachment_image_url(tr_posts_field('contact_img_desktop'), 'full') ?: get_template_directory_uri() . '/images/img_contact.webp';
        ?>
        <div id="parallax" class="contact_img_inner" style="background-image: url('<?php echo esc_url($cimg_desk); ?>');">
        </div>
    </section>
</main>

<?php get_footer(); ?>