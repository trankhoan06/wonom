<?php
/**
 * Template Name: Case Study Detail
 * Template Post Type: work, page
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
<?php
if (have_posts()):
    while (have_posts()):
        the_post();
        $hero_bg = wp_get_attachment_image_url(tr_posts_field('hero_bg'), 'full');
        $hero_logo = wp_get_attachment_image_url(tr_posts_field('hero_logo'), 'full');
        $hero_logo_mobile_width = tr_posts_field('hero_logo_mobile_width');
        $hero_subtitle = tr_posts_field('hero_subtitle');
        $info_list = tr_posts_field('info_list') ?: [];
        $blog_items = tr_posts_field('blog_items') ?: [];
        $cta_img = tr_options_field('tr_theme_options.about_cta_img');
        $cta_link = tr_options_field('tr_theme_options.about_cta_link');
        $cta_text1 = tr_options_field('tr_theme_options.about_cta_text1');
        $cta_text2 = tr_options_field('tr_theme_options.about_cta_text2');
        $cta_text3 = tr_options_field('tr_theme_options.about_cta_text3');
        ?>
        <main class="main" data-namespace="caseStudyDetail">
            <?php if (!empty($hero_logo_mobile_width)): ?>
                <style>
                    @media only screen and (max-width: 767px) {
                        .casestudydetail_hero_content_img,
                        .home_case_content_view_logo {
                            <?php 
                            $mobile_width_val = floatval($hero_logo_mobile_width);
                            $mobile_width_rem = ($mobile_width_val > 0) ? ($mobile_width_val / 10) . 'rem' : esc_attr($hero_logo_mobile_width);
                            ?>
                            width: <?php echo $mobile_width_rem; ?> !important;
                        }
                    }
                </style>
            <?php endif; ?>
            <section class="casestudydetail_hero">
                <?php if ($hero_bg): ?>
                    <div class="casestudydetail_img img_full">
                        <img src="<?php echo esc_url($hero_bg); ?>" alt="">
                    </div>
                <?php endif; ?>
                <div class="casestudydetail_hero_overlay"></div>
                <div class="container">
                    <div class="casestudydetail_hero_content" data-init>
                        <?php if ($hero_logo): ?>
                            <div class="casestudydetail_hero_content_img img_full">
                                <img src="<?php echo esc_url($hero_logo); ?>" alt="">
                            </div>
                        <?php endif; ?>
                        <?php if ($hero_subtitle): ?>
                            <div class="casestudydetail_hero_content_txt txt_24 txt_medium txt_uppercase txt_center cl_eb">
                                <?php echo esc_html($hero_subtitle); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <section class="casestudydetail_content">
                <div class="casestudydetail_content_info">
                    <div class="container grid">
                        <?php 
                        $client_title = tr_posts_field('info_client_title') ?: 'Client';
                        $client_content = tr_posts_field('info_client_content');
                        
                        $year_title = tr_posts_field('info_year_title') ?: 'Year';
                        $year_content = tr_posts_field('info_year_content');
                        
                        $link_title = tr_posts_field('info_link_title') ?: 'Link';
                        $link_content = tr_posts_field('info_link_content');

                        // Fallback to old repeater data if new fields are empty
                        if (empty($client_content) && empty($year_content) && empty($link_content)) {
                            $old_info_list = tr_posts_field('info_list') ?: [];
                            if (is_array($old_info_list)) {
                                foreach ($old_info_list as $info) {
                                    $t = strtolower(trim($info['title'] ?? ''));
                                    if (strpos($t, 'client') !== false) {
                                        $client_content = $info['content'] ?? '';
                                    } elseif (strpos($t, 'year') !== false) {
                                        $year_content = $info['content'] ?? '';
                                    } elseif (strpos($t, 'link') !== false) {
                                        $link_content = $info['content'] ?? '';
                                    }
                                }
                            }
                        }

                        $categories = get_the_category();
                        $category_name = !empty($categories) ? $categories[0]->name : '';
                        $category_title = 'Category';

                        $new_info_list = [];
                        if ($client_content) {
                            $new_info_list[] = ['title' => $client_title, 'content' => $client_content];
                        }
                        if ($year_content) {
                            $new_info_list[] = ['title' => $year_title, 'content' => $year_content];
                        }
                        if ($category_name) {
                            $new_info_list[] = ['title' => $category_title, 'content' => $category_name];
                        }
                        if ($link_content) {
                            $new_info_list[] = ['title' => $link_title, 'content' => $link_content];
                        }
                        ?>
                        <?php if (!empty($new_info_list)):
                            $info_count = 1;
                            foreach ($new_info_list as $info): ?>
                                <div class="casestudydetail_content_info_item item<?php echo $info_count; ?>">
                                    <div class="casestudydetail_content_info_item_titlle txt_14 txt_uppercase">
                                        <?php echo esc_html($info['title'] ?? ''); ?>
                                    </div>
                                    <div class="casestudydetail_content_info_item_content txt_16">
                                        <?php echo esc_html($info['content'] ?? ''); ?>
                                    </div>
                                </div>
                            <?php $info_count++; endforeach; endif; ?>
                    </div>
                </div>
                <div class="container">
                    <div class="casestudydetail_content_inner grid">
                        <div class="casestudydetail_content_blog_wrap">
                            <div class="casestudydetail_content_blog">
                                <div class="casestudydetail_content_blog_inner">
                                    <?php if (!empty($blog_items)):
                                        foreach ($blog_items as $item):
                                            $images_data = $item['image'] ?? '';
                                            $image_ids = [];
                                            if (is_array($images_data)) {
                                                $image_ids = $images_data;
                                            } elseif (!empty($images_data)) {
                                                $image_ids = explode(',', $images_data);
                                            }
                                            ?>
                                            <div class="casestudydetail_content_blog_item">
                                                <div class="casestudydetail_content_blog_item_info">
                                                    <div class="casestudydetail_content_blog_item_info_title h3 heading cl_eb">
                                                        <?php echo esc_html($item['title']); ?>
                                                    </div>
                                                    <div class="casestudydetail_content_blog_item_info_content">
                                                        <div class="casestudydetail_content_blog_item_info_content_subtitle heading h5 cl_eb">
                                                            <?php echo nl2br(esc_html($item['subtitle'])); ?>
                                                        </div>
                                                        <div class="casestudydetail_content_blog_item_info_content_des txt_16">
                                                            <?php echo nl2br(esc_html($item['description'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="casestudydetail_content_blog_item_img">
                                                    <?php if (!empty($image_ids)):
                                                        $is_multiple = count($image_ids) > 1;
                                                        $grid_class = $is_multiple ? ' grid-2-col' : '';
                                                        ?>
                                                        <?php
                                                        $inline_style = 'flex: 1;';
                                                        if (!empty($item['is_vertical'])) {
                                                            $inline_style .= 'display: flex !important; flex-direction:column;';
                                                        }
                                                        if (isset($item['spacing']) && $item['spacing'] !== '') {
                                                            $spacing_val = (float) $item['spacing'];
                                                            $inline_style .= ' gap: ' . ($spacing_val / 10) . 'rem;';
                                                        }
                                                        ?>
                                                        <div class="casestudydetail_content_blog_item_img_inner img_full<?php echo $grid_class; ?>"
                                                            style="<?php echo esc_attr($inline_style); ?>">
                                                            <?php foreach ($image_ids as $img_id):
                                                                $img_url = wp_get_attachment_image_url($img_id, 'full');
                                                                if ($img_url):
                                                                    ?>
                                                                    <div class="casestudydetail_content_blog_item_img_wrap img_full">
                                                                        <img src="<?php echo esc_url($img_url); ?>" alt="" loading="lazy">
                                                                    </div>
                                                                <?php endif; endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="casestudydetail_content_social">
                                <a href="#" class="casestudydetail_content_blog_social_icon svg_full btn-copy-link"
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
                                <a href="#" class="casestudydetail_content_blog_social_icon svg_full btn-share-fb"
                                    data-url="<?php echo urlencode(get_permalink()); ?>" title="Share on Facebook">
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626" />
                                        <path
                                            d="M28.1836 25.125L28.6758 21.8906H25.5469V19.7812C25.5469 18.8672 25.9688 18.0234 27.375 18.0234H28.8164V15.2461C28.8164 15.2461 27.5156 15 26.2852 15C23.7188 15 22.0312 16.582 22.0312 19.3945V21.8906H19.1484V25.125H22.0312V33H25.5469V25.125H28.1836Z"
                                            fill="#BEBEBE" />
                                    </svg>
                                </a>
                                <a href="#" class="casestudydetail_content_blog_social_icon svg_full btn-share-in"
                                    data-url="<?php echo urlencode(get_permalink()); ?>"
                                    data-title="<?php echo urlencode(get_the_title()); ?>" title="Share on LinkedIn">
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626" />
                                        <path
                                            d="M19.6406 30.75V20.2383H16.3711V30.75H19.6406ZM17.9883 18.832C19.043 18.832 19.8867 17.9531 19.8867 16.8984C19.8867 15.8789 19.043 15.0352 17.9883 15.0352C16.9688 15.0352 16.125 15.8789 16.125 16.8984C16.125 17.9531 16.9688 18.832 17.9883 18.832ZM31.8398 30.75H31.875V24.9844C31.875 22.1719 31.2422 19.9922 27.9375 19.9922C26.3555 19.9922 25.3008 20.8711 24.8438 21.6797H24.8086V20.2383H21.6797V30.75H24.9492V25.5469C24.9492 24.1758 25.1953 22.875 26.8828 22.875C28.5703 22.875 28.6055 24.4219 28.6055 25.6523V30.75H31.8398Z"
                                            fill="#BEBEBE" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="home_case_content_view">
                            <a target="_blank" href="<?php echo esc_url(tr_posts_field('view_link') ?: '#'); ?>" class="home_case_content_view_logo img_full">
                                <img src="<?php echo esc_url($hero_logo); ?>" alt="">
                            </a>
                            <a target="_blank" href="<?php echo esc_url(tr_posts_field('view_link') ?: '#'); ?>" class="home_case_content_view_link hover_txt txt_16 txt_medium txt_uppercase cl_eb">
                                 <div class="hover_txt_grid">
                                    <?php $hero_btn = esc_html(tr_posts_field('view_text'))?: 'VIEW LIVE WEBSITE'; ?>
                                    <span class="init"><?php echo $hero_btn; ?></span>
                                    <span class="active"><?php echo $hero_btn; ?></span>
                                </div>
                                <div class="service_hero_right_discover_icon_wrap">
                                    <div class="service_hero_right_discover_icon svg_full">
                                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_1164_690)">
                                                <path d="M12 12H36.0003V36.0003" stroke="#929292" stroke-width="2" stroke-linejoin="round"></path>
                                                <path d="M12 36.0003L36.0003 12" stroke="#929292" stroke-width="2" stroke-linejoin="round"></path>
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_1164_690">
                                                    <rect width="48" height="48" fill="white"></rect>
                                                </clipPath>
                                            </defs>
                                        </svg>

                                    </div>
                                    <div class="service_hero_right_discover_icon active svg_full">
                                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_1164_690)">
                                                        <path d="M12 12H36.0003V36.0003" stroke="#E62636" stroke-width="2" stroke-linejoin="round"></path>
                                                        <path d="M12 36.0003L36.0003 12" stroke="#E62636" stroke-width="2" stroke-linejoin="round"></path>
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_1164_690">
                                                            <rect width="48" height="48" fill="white"></rect>
                                                        </clipPath>
                                                    </defs>
                                                </svg>

                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="home_case_content_list right_full left_full">
                            <?php
                            $prev_post = get_previous_post();
                            if (empty($prev_post)) {
                                $newest = get_posts(array('post_type' => 'work', 'posts_per_page' => 1, 'order' => 'DESC', 'orderby' => 'date'));
                                if (!empty($newest))
                                    $prev_post = $newest[0];
                            }

                            $next_post = get_next_post();
                            if (empty($next_post)) {
                                $oldest = get_posts(array('post_type' => 'work', 'posts_per_page' => 1, 'order' => 'ASC', 'orderby' => 'date'));
                                if (!empty($oldest))
                                    $next_post = $oldest[0];
                            }

                            $items = array();
                            if (!empty($prev_post)) {
                                $items[] = array('label' => 'PREVIOUS PROJECT', 'work' => $prev_post);
                            }
                            if (!empty($next_post)) {
                                $items[] = array('label' => 'NEXT PROJECT', 'work' => $next_post);
                            }

                            foreach ($items as $item_data):
                                $p = $item_data['work'];
                                $label = $item_data['label'];
                                $link = get_permalink($p->ID);
                                $title = get_the_title($p->ID);
                                $bg_id = tr_posts_field('hero_bg', $p->ID);
                                if ($bg_id) {
                                    $img_url = wp_get_attachment_image_url($bg_id, 'full');
                                } else {
                                    $img_url = get_the_post_thumbnail_url($p->ID, 'full') ?: get_template_directory_uri() . '/images/case.webp';
                                }
                                ?>
                                <a href="<?php echo esc_url($link); ?>" class="home_case_content_item"
                                    style="display:block; text-decoration:none;">
                                    <div class="home_case_content_item_des">
                                        <div class="home_case_content_item_des_txt txt_15 txt_medium">
                                            <?php echo esc_html($label); ?>
                                        </div>
                                    </div>
                                    <div class="home_case_content_item_img_outer">
                                        <div class="home_case_content_item_img img_full">
                                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                                        </div>
                                    </div>
                                    <div class="home_case_content_item_txt">
                                        <div class="home_case_content_item_txt_title heading cl_be h4 h5_mb">
                                            <?php echo esc_html($title); ?>
                                        </div>
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
                            <?php endforeach; ?>
                        </div>
                        <a href="/work" class="casestudydetail_content_view">
                            <div class="casestudydetail_content_view_button button_hover txt_14 txt_medium hover_txt">
                                <div class="hover_txt_grid">
                                    <span class="init"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="2.5" y="2.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="11.5" y="2.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="2.5" y="11.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="11.5" y="11.5" width="6" height="6" stroke="#EBEBEB" />
                                        </svg>
                                        ALL PROJECTS</span>
                                    <span class="active"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="2.5" y="2.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="11.5" y="2.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="2.5" y="11.5" width="6" height="6" stroke="#EBEBEB" />
                                            <rect x="11.5" y="11.5" width="6" height="6" stroke="#EBEBEB" />
                                        </svg>
                                        ALL PROJECTS</span>
                                </div>
                            </div>
                            </a>
                    </div>
                </div>

            </section>
            <?php 
    $lang_suffix = (function_exists('pll_current_language') && pll_current_language() == 'vi') ? '_vi' : '';
    $cta_img_val = tr_options_field('tr_theme_options.about_cta_img' . $lang_suffix);
    if (empty($cta_img_val)) $cta_img_val = tr_options_field('tr_theme_options.about_cta_img');
    $cta_img = wp_get_attachment_image_url(($cta_img_val ?? ''), 'full') ?: get_template_directory_uri() . '/images/img_cta.webp';
    $cta_link = tr_options_field('tr_theme_options.about_cta_link' . $lang_suffix) ?: tr_options_field('tr_theme_options.about_cta_link');
    $cta_text1 = tr_options_field('tr_theme_options.about_cta_text1' . $lang_suffix) ?: tr_options_field('tr_theme_options.about_cta_text1');
    $cta_text2 = tr_options_field('tr_theme_options.about_cta_text2' . $lang_suffix) ?: tr_options_field('tr_theme_options.about_cta_text2');
    $cta_text3 = tr_options_field('tr_theme_options.about_cta_text3' . $lang_suffix) ?: tr_options_field('tr_theme_options.about_cta_text3');
    ?>
    <section class="about_cta">
        <div class="about_cta_img img_full">
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
            <a href="<?php echo esc_url($cta_link ?? ''); ?>"
                class=" about_cta_inner_content h2 h5_mb heading ">
                <span class="cl_linear">
                    <?php echo nl2br(esc_html($cta_text1 ?? '')); ?>
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
                            <?php echo esc_html($cta_text2 ?? ''); ?>
                        </span>
                        <span class="cl_red middle">
                            <?php echo esc_html($cta_text3 ?? ''); ?>
                        </span>
                    </div>

                </div>
            </a>
        </div>
    </section>
        </main>
    <?php endwhile; endif; ?>
<?php get_footer(); ?>