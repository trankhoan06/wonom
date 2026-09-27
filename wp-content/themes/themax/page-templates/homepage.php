<?php
/**
 * Template Name: HomePage
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
<main class="main" data-namespace="home">
    <section class="home_hero" data-init>
        <div class="home_hero_inner">
            <?php
            $home_banner_video = tr_posts_field('home_banner_video');
            $video_url = $home_banner_video ? wp_get_attachment_url($home_banner_video) : '';
            $home_banner_poster = tr_posts_field('home_banner_poster');
            $poster_url = $home_banner_poster ? wp_get_attachment_image_url($home_banner_poster, 'full') : '/wp-content/uploads/2026/07/poster.jpg';
            ?>
            <?php if ($video_url): 
                $webm_url = preg_replace('/\.mp4$/i', '.webm', $video_url);
            ?>
                <video poster="<?php echo esc_url($poster_url); ?>" preload="none"
                    autoplay loop muted playsinline class="lazy-home-video"
                    style="position: relative; z-index: 1;">
                    <source src="<?php echo esc_url($webm_url); ?>" type="video/webm">
                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                </video>
                <!-- <video src="<?php echo get_template_directory_uri(); ?>/images/video_button.mp4" autoplay loop muted playsinline preload="auto" style="max-width: 100%; height: auto;"></video> -->
                <button class="home_hero_mute_btn" type="button" aria-label="Mute/Unmute Video" style="pointer-events: auto;">
                    <span class="icon-unmute" style="display: none;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.3015 7.3606C15.9898 7.87684 16.5485 8.54626 16.9332 9.31584C17.318 10.0854 17.5184 10.934 17.5184 11.7944C17.5184 12.6548 17.318 13.5034 16.9332 14.273C16.5485 15.0426 15.9898 15.712 15.3015 16.2283" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.2943 4.03516C19.4515 4.97031 20.3848 6.15245 21.026 7.49501C21.6672 8.83757 22 10.3065 22 11.7944C22 13.2822 21.6672 14.7511 21.026 16.0937C20.3848 17.4363 19.4515 18.6184 18.2943 19.5536" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.32536 15.1197H3.10845C2.81447 15.1197 2.53253 15.0029 2.32466 14.795C2.11678 14.5871 2 14.3052 2 14.0112V9.57738C2 9.28339 2.11678 9.00145 2.32466 8.79358C2.53253 8.5857 2.81447 8.46892 3.10845 8.46892H5.32536L9.20495 3.48086C9.30184 3.29268 9.46283 3.14536 9.65885 3.06551C9.85487 2.98567 10.073 2.97857 10.2738 3.04551C10.4746 3.11244 10.6448 3.24898 10.7537 3.43047C10.8626 3.61196 10.903 3.82641 10.8676 4.03509V19.5535C10.903 19.7622 10.8626 19.9766 10.7537 20.1581C10.6448 20.3396 10.4746 20.4761 10.2738 20.5431C10.073 20.61 9.85487 20.6029 9.65885 20.5231C9.46283 20.4432 9.30184 20.2959 9.20495 20.1077L5.32536 15.1197Z" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="icon-mute">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.3015 7.54224C16.131 8.16435 16.7694 9.00667 17.144 9.97347C17.5187 10.9403 17.6146 11.9928 17.4209 13.0114M15.8946 15.8956C15.7093 16.0809 15.5111 16.2527 15.3015 16.4099" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.2944 4.21704C20.0734 5.65476 21.3043 7.66016 21.781 9.89731C22.2576 12.1344 21.951 14.4674 20.9125 16.5055M19.0548 19.0538C18.8131 19.2939 18.5593 19.5214 18.2944 19.7355" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.72726 4.27684L9.205 3.66275C9.30189 3.47456 9.46288 3.32724 9.65891 3.2474C9.85493 3.16755 10.073 3.16046 10.2738 3.22739C10.4746 3.29432 10.6449 3.43087 10.7538 3.61236C10.8627 3.79384 10.9031 4.0083 10.8677 4.21698V6.43391M10.8677 10.8678V19.7355C10.9031 19.9441 10.8627 20.1586 10.7538 20.3401C10.6449 20.5216 10.4746 20.6581 10.2738 20.7251C10.073 20.792 9.85493 20.7849 9.65891 20.705C9.46288 20.6252 9.30189 20.4779 9.205 20.2897L5.32539 15.3016H3.10846C2.81448 15.3016 2.53254 15.1848 2.32466 14.977C2.11678 14.7691 2 14.4871 2 14.1931V9.7593C2 9.46531 2.11678 9.18337 2.32466 8.9755C2.53254 8.76762 2.81448 8.65083 3.10846 8.65083H5.32539L6.75974 6.80635" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 2L21.9523 21.9523" stroke="#EBEBEB" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            <?php endif; ?>

        </div>
        <div class="home_hero_overlay" data-init>
            <div class="home_hero_overlay_txt txt_16">Scroll down</div>
            <div class="home_hero_overlay_icon img_full">
                <img src="<?php echo get_template_directory_uri(); ?>/images/Arrows_down.svg" alt="">
            </div>
        </div>
    </section>
    <div class="home_intro_wrap" data-init>
        <div class="home_intro_block"></div>
        <div class="home_intro_main">
            <section class="home_intro">
                <div class="container">
                    <div class="home_intro_inner">
                        <?php
                        $home_intro_text_1 = tr_posts_field('home_intro_text_1');
                        $home_intro_text_2 = tr_posts_field('home_intro_text_2');
                        $home_intro_text_3 = tr_posts_field('home_intro_text_3');
                        $home_intro_subtext = tr_posts_field('home_intro_subtext');
                        ?>
                        <?php if ($home_intro_text_1): ?>
                            <div class="home_intro_line">
                                <div class="home_intro_txt heading h0 h1_mb cl_linear">
                                    <?php echo wp_kses_post($home_intro_text_1); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($home_intro_text_2): ?>
                            <div class="home_intro_line">
                                <div class="home_intro_txt heading h0 h1_mb cl_linear item_translate">
                                    <?php echo wp_kses_post($home_intro_text_2); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($home_intro_text_3): ?>
                            <div class="home_intro_line">
                                <div class="home_intro_txt heading h0 h1_mb cl_red">
                                    <?php echo wp_kses_post($home_intro_text_3); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($home_intro_subtext): ?>
                            <div class="home_intro_subtxt txt_18 cl_eb">
                                <div class="home_intro_subtxt_inner"><?php echo wp_kses_post(nl2br($home_intro_subtext)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <div class="home_intro_img_cms ">
                <?php
                $intro_imgs = tr_posts_field('home_intro_images');
                $img_urls = [];

                if (!empty($intro_imgs)) {
                    $img_ids = is_string($intro_imgs) ? explode(',', $intro_imgs) : (is_array($intro_imgs) ? $intro_imgs : []);
                    foreach ($img_ids as $id) {
                        if ($id) {
                            $url = wp_get_attachment_image_url($id, 'full');
                            if ($url) {
                                $img_urls[] = $url;
                            }
                        }
                    }
                }

                $columns = array_fill(0, 4, []);
                $total = count($img_urls);

                if ($total > 0) {
                    $base = floor($total / 4);
                    $remainder = $total % 4;
                    $offset = 0;

                    for ($i = 0; $i < 4; $i++) {
                        $length = $base + ($i < $remainder ? 1 : 0);
                        $columns[$i] = array_slice($img_urls, $offset, $length);
                        $offset += $length;
                    }
                }

                foreach ($columns as $col_urls) {
                    echo '<div class="home_intro_img_list">';
                    foreach ($col_urls as $url) {
                        echo '<div class="home_intro_img_item img_fullfill">';
                        echo '<img src="data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'></svg>" data-src="' . esc_url($url) . '" class="lazyload" alt="">';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <div class="home_specialize_wrap" data-section="black">
        <section class="home_specialize">
            <?php
            $specialize_bg = tr_posts_field('specialize_bg');
            $specialize_bg_url = $specialize_bg ? wp_get_attachment_image_url($specialize_bg, 'full') : '';
            $specialize_icon_top_url = get_template_directory_uri() . '/images/ic_sol3.svg';
            $specialize_icon_center_url = get_template_directory_uri() . '/images/ic_sol.svg';
            $specialize_icon_bottom_url = get_template_directory_uri() . '/images/ic_sol2.svg';
            $specialize_icon_top_mb_url = get_template_directory_uri() . '/images/ic_sol3_mb.svg';
            $specialize_icon_bottom_mb_url = get_template_directory_uri() . '/images/ic_sol2_mb.svg';
            $specialize_title = tr_posts_field('specialize_title');
            $specialize_subtitle = tr_posts_field('specialize_subtitle');
            ?>
            <div class="home_specialize_bg img_full">
                <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="<?php echo esc_url($specialize_bg_url); ?>" class="lazyload" alt="">
            </div>
            <div class="home_specialize_bg_color">
                <div class="home_specialize_bg_color_inner"></div>
                <div class="home_specialize_bg_deco">
                    <div class="home_specialize_bg_deco_item top img_height">
                        <img class="middle" src="<?php echo esc_url($specialize_icon_top_url); ?>" alt=""
                            loading="lazy">
                        <img class="mobile" src="<?php echo esc_url($specialize_icon_top_mb_url); ?>" alt=""
                            loading="lazy">
                    </div>
                    <div class="home_specialize_bg_deco_item center img_full">
                        <img src="<?php echo esc_url($specialize_icon_center_url); ?>" alt="" loading="lazy">
                    </div>
                    <div class="home_specialize_bg_deco_item bottom img_height">
                        <img class="middle" src="<?php echo esc_url($specialize_icon_bottom_url); ?>" alt=""
                            loading="lazy">
                        <img class="mobile" src="<?php echo esc_url($specialize_icon_bottom_mb_url); ?>" alt=""
                            loading="lazy">
                    </div>
                </div>
            </div>
            <div class="container grid">
                <div class="home_specialize_inner">
                    <div class="home_specialize_title_wrap">
                        <?php
                        $specialize_title_1 = tr_posts_field('specialize_title_1');
                        $specialize_red_texts = tr_posts_field('specialize_red_texts');
                        $specialize_title_2 = tr_posts_field('specialize_title_2');
                        ?>
                        <?php if ($specialize_title_1): ?>
                            <div class="home_specialize_inner_txt cl_black heading h1 h4_mb">
                                <?php echo wp_kses_post($specialize_title_1); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (is_array($specialize_red_texts) && !empty($specialize_red_texts)): ?>
                            <div class="home_specialize_inner_txt main cl_red heading h1 h4_mb">
                                <?php foreach ($specialize_red_texts as $red_text): ?>
                                    <span><?php echo esc_html($red_text['text']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($specialize_title_2): ?>
                            <div class="home_specialize_inner_txt cl_black heading h1 h4_mb">
                                <?php echo wp_kses_post(nl2br($specialize_title_2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="home_specialize_subtxt txt_18 txt_medium cl_7d">
                    <div class="home_specialize_subtxt_txt"><?php echo wp_kses_post($specialize_subtitle); ?></div>
                </div>
            </div>
        </section>
        <div class="home_specialize_block"></div>
    </div>
    <section class="home_services" data-section="white">
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
                <?php
                $home_services_subtitle = tr_posts_field('home_services_subtitle');
                $home_services_title = tr_posts_field('home_services_title');
                $home_services_desc = tr_posts_field('home_services_desc');
                ?>
                <?php if ($home_services_subtitle): ?>
                    <div class="home_services_sub txt_16 txt_medium block_arrow cl_fb">
                        <?php echo esc_html($home_services_subtitle); ?>
                    </div>
                <?php endif; ?>
                <?php if ($home_services_title): ?>
                    <h2 class="heading h2 h4_mb home_services_title txt_center cl_white">
                        <?php echo wp_kses_post(nl2br($home_services_title)); ?>
                    </h2>
                <?php endif; ?>
                <?php if ($home_services_desc): ?>
                    <div class=" home_services_des cl_fb heading h6 txt_uppercase txt_center">
                        <?php echo wp_kses_post(nl2br($home_services_desc)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="home_services_cms_wrap">
            <div class="home_services_cms">
                <div class="home_services_progress"></div>
                <div class="home_services_list">
                    <?php
                    $services = tr_posts_field('home_services');
                    if (is_array($services) && !empty($services)):
                        $i = 0;
                        foreach ($services as $srv):
                            $color_class = ($i % 2 !== 0) ? 'bg_red' : '';
                            $i++;
                            ?>
                            <div class="home_services_item <?php echo $color_class; ?>">
                                <div class="home_services_item_inner">
                                    <div class="container grid">
                                        <div class="home_services_content pa_container">
                                            <div class="home_services_content_top">
                                                <h3 class="home_services_content_title heading h1 h4_mb cl_linear">
                                                    <?php echo esc_html($srv['title'] ?? ''); ?>
                                                </h3>
                                                <div class="heading h6 home_services_content_sub">
                                                    <?php echo esc_html($srv['description'] ?? ''); ?>
                                                </div>
                                            </div>
                                            <div class="home_services_content_bottom">
                                                <div class="home_services_content_bottom_des heading h4 cl_eb">
                                                    <?php echo esc_html($srv['bottom_des'] ?? ''); ?>
                                                </div>
                                                <div class="home_services_content_bottom_list">
                                                    <?php
                                                    $service_list = $srv['service_list'] ?? [];
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
                                            $img_url = wp_get_attachment_image_url($img_id, 'full');
                                            ?>
                                            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="<?php echo esc_url($img_url); ?>" class="lazyload" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>

    </section>
    <section class="home_case pa_container">
        <div class="container">
            <?php
            $home_case_subtitle = tr_posts_field('home_case_subtitle');
            $home_case_title = tr_posts_field('home_case_title');
            ?>
            <?php if ($home_case_subtitle): ?>
                <div class="home_case_subtitle txt_16 txt_medium block_arrow"><?php echo esc_html($home_case_subtitle); ?>
                </div>
            <?php endif; ?>
            <?php if ($home_case_title): ?>
                <div class="home_case_title heading h2 txt_center cl_linear h4_mb"><?php echo esc_html($home_case_title); ?>
                </div>
            <?php endif; ?>
            <div class="home_case_content_list right_full left_full">
                <?php
                $selected_works = tr_posts_field('home_case_works');
                $work_ids = [];
                if (!empty($selected_works)) {
                    foreach ($selected_works as $item) {
                        if (!empty($item['work_id'])) {
                            $work_ids[] = $item['work_id'];
                        }
                    }
                }

                if (!empty($work_ids)) {
                    $args = array(
                        'post_type' => 'work',
                        'post__in' => $work_ids,
                        'orderby' => 'post__in',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'ignore_sticky_posts' => true
                    );
                    $query = new WP_Query($args);
                    if ($query->have_posts()):
                        while ($query->have_posts()):
                            $query->the_post();
                            $categories = get_the_category();
                            $category_name = !empty($categories) ? $categories[0]->name : '';
                            $img_url = get_the_post_thumbnail_url(get_the_ID(), 'full') ?: get_template_directory_uri() . '/images/case.webp';
                            $post_subtitle = tr_posts_field('info_client_content', get_the_ID());
                            
                            // Fallback to old repeater data if new field is empty
                            if (empty($post_subtitle)) {
                                $old_info_list = tr_posts_field('info_list', get_the_ID()) ?: [];
                                if (is_array($old_info_list)) {
                                    foreach ($old_info_list as $info) {
                                        $t = strtolower(trim($info['title'] ?? ''));
                                        if (strpos($t, 'client') !== false) {
                                            $post_subtitle = $info['content'] ?? '';
                                            break;
                                        }
                                    }
                                }
                            }
                            if (empty($post_subtitle)) {
                                $post_subtitle = get_the_title();
                            }
                            ?>
                            <a href="<?php the_permalink(); ?>" class="home_case_content_item">
                                <div class="home_case_content_item_des">
                                    <div class="home_case_content_item_des_txt txt_uppercase txt_15 txt_medium">
                                        <?php echo esc_html($post_subtitle); ?>
                                    </div>
                                    <div class="home_case_content_item_des_txt txt_15 txt_medium">
                                        <?php echo esc_html($category_name); ?>
                                    </div>
                                </div>
                                <div class="home_case_content_item_img_outer">
                                    <div class="home_case_content_item_img img_full">
                                        <?php if ($img_url): ?>
                                            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="<?php echo esc_url($img_url); ?>" class="lazyload" alt="">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="home_case_content_item_txt">
                                    <div class="home_case_content_item_txt_title heading cl_be h4 h5_mb"><?php the_title(); ?></div>
                                    <div class="home_case_content_item_txt_icon">
                                        <div class="home_case_content_item_txt_icon_wrap svg_full">
                                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
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
                                        <div class="home_case_content_item_txt_icon_wrap svg_full active">
                                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
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
                                </div>
                            </a>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                }
                ?>
            </div>
            <?php
            $home_case_btn_text = tr_posts_field('home_case_btn_text');
            $home_case_btn_link = tr_posts_field('home_case_btn_link');
            ?>
            <?php if ($home_case_btn_text && $home_case_btn_link): ?>
                <a href="<?php echo esc_url($home_case_btn_link); ?>"
                    class="home_case_seeview button_hover hover_txt cl_be txt_uppercase txt_14 txt_medium">
                    <div class="hover_txt_grid">
                        <span class="init"><?php echo esc_html($home_case_btn_text); ?></span>
                        <span class="active"><?php echo esc_html($home_case_btn_text); ?></span>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </section>
    <section class="home_clients pa_container">
        <div class="home_clients_pattern img_full">
            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="<?php echo get_template_directory_uri(); ?>/images/partent_client.png" class="lazyload" alt="">
        </div>
        <div class="container">
            <?php
            $home_clients_subtitle = tr_posts_field('home_clients_subtitle');
            $home_clients_title = tr_posts_field('home_clients_title');
            
            $lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
            $lang_suffix = ($lang === 'vi') ? '_vi' : '';
            
            $home_clients_tab1_name = tr_options_field('tr_client_options.home_clients_tab1_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab1_name');
            $home_clients_tab2_name = tr_options_field('tr_client_options.home_clients_tab2_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab2_name');
            $home_clients_tab3_name = tr_options_field('tr_client_options.home_clients_tab3_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab3_name');
            $home_clients_tab4_name = tr_options_field('tr_client_options.home_clients_tab4_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab4_name');
            $home_clients_tab5_name = tr_options_field('tr_client_options.home_clients_tab5_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab5_name');
            $home_clients_tab6_name = tr_options_field('tr_client_options.home_clients_tab6_name' . $lang_suffix) ?: tr_options_field('tr_client_options.home_clients_tab6_name');

            if ($lang === 'vi') {
                $home_clients_btn_text = tr_options_field('tr_client_options.home_clients_btn_text_vi') ?: tr_options_field('tr_client_options.home_clients_btn_text');
                $home_clients_btn_link = tr_options_field('tr_client_options.home_clients_btn_link_vi') ?: tr_options_field('tr_client_options.home_clients_btn_link');
            } else {
                $home_clients_btn_text = tr_options_field('tr_client_options.home_clients_btn_text');
                $home_clients_btn_link = tr_options_field('tr_client_options.home_clients_btn_link');
            }
            ?>
            <div class="home_clients_bg img_full">
                <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="./images/pattern-blur.svg" class="lazyload" alt="">
            </div>
            <div class="home_clients_subtitle block_arrow"><?php echo esc_html($home_clients_subtitle); ?></div>
            <div class="home_clients_title heading h2 h4_mb cl_linear txt_center">
                <?php echo wp_kses_post(nl2br($home_clients_title)); ?>
            </div>

            <?php if ($home_clients_tab1_name || $home_clients_tab2_name || $home_clients_tab3_name || $home_clients_tab4_name || $home_clients_tab5_name || $home_clients_tab6_name): ?>
                <div class="home_clients_tab">
                    <?php if ($home_clients_tab1_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium active" data-tabs="tab1">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab1_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab1_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($home_clients_tab2_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium" data-tabs="tab2">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab2_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab2_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($home_clients_tab3_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium" data-tabs="tab3">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab3_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab3_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($home_clients_tab4_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium" data-tabs="tab4">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab4_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab4_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($home_clients_tab5_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium" data-tabs="tab5">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab5_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab5_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($home_clients_tab6_name): ?>
                        <div class="home_clients_tab_item hover_txt txt_16 txt_medium" data-tabs="tab6">
                            <div class="hover_txt_grid">
                                <span class="init"><?php echo esc_html($home_clients_tab6_name); ?></span>
                                <span class="active"><?php echo esc_html($home_clients_tab6_name); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="home_clients_content">
                <?php
                $tabs = ['tab1' => 'home_clients_tab1', 'tab2' => 'home_clients_tab2', 'tab3' => 'home_clients_tab3', 'tab4' => 'home_clients_tab4', 'tab5' => 'home_clients_tab5', 'tab6' => 'home_clients_tab6'];
                foreach ($tabs as $tab_id => $field_name):
                    $clients = tr_options_field('tr_client_options.' . $field_name);
                    ?>
                    <div class="home_clients_content_item" data-tabs="<?php echo $tab_id; ?>">
                        <?php
                        if (is_array($clients) && !empty($clients)):
                            foreach ($clients as $c):
                                ?>
                                <div class="home_clients_content_item_img">
                                    <div class="home_clients_content_item_inner img_full">
                                        <?php $img_url = wp_get_attachment_image_url($c['logo'] ?? 0, 'full'); ?>
                                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'></svg>" data-src="<?php echo esc_url($img_url); ?>" class="lazyload" alt="">
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($home_clients_btn_text && $home_clients_btn_link): ?>
                <a href="<?php echo esc_url($home_clients_btn_link); ?>"
                    class="home_clients_button button_hover txt_uppercase hover_txt txt_14 txt_medium">
                    <div class="hover_txt_grid">
                        <span class="init"><?php echo esc_html($home_clients_btn_text); ?></span>
                        <span class="active"><?php echo esc_html($home_clients_btn_text); ?></span>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </section>
</main>


<?php get_footer(); ?>