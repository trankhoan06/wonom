<?php wp_footer(); ?>
<footer>
    <div class="footer">
        <div class="container">
            <div class="footer_top">
                <div class="grid">
                    <div class="footer_top_menu">
                        <?php
                        $locations = get_nav_menu_locations();
                        $has_footer_menu = false;
                        if ( isset( $locations['footer_menu'] ) ) {
                            $menu = get_term( $locations['footer_menu'], 'nav_menu' );
                            if ( $menu && ! is_wp_error( $menu ) ) {
                                $menu_items = wp_get_nav_menu_items( $menu->term_id );
                                if ( $menu_items ) {
                                    $has_footer_menu = true;
                                    foreach ( $menu_items as $menu_item ) {
                                        $title = esc_html( $menu_item->title );
                                        $url = esc_url( $menu_item->url );
                                        $target = ! empty( $menu_item->target ) ? ' target="' . esc_attr( $menu_item->target ) . '"' : '';
                                        echo '<a href="' . $url . '"' . $target . ' class="footer_top_menu_item heading h6 cl_be hover_txt">';
                                        echo '<div class="hover_txt_grid">';
                                        echo '<span class="init">' . $title . '</span>';
                                        echo '<span class="active">' . $title . '</span>';
                                        echo '</div>';
                                        echo '</a>';
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="footer_top_email h1 heading h3_mb"><?php echo esc_html(tr_options_field('tr_theme_options.footer_email') ?: 'hello@wonom.vn'); ?></div>
                </div>
            </div>
            <?php
            $lang_suffix = (function_exists('pll_current_language') && pll_current_language() == 'vi') ? '_vi' : '';
            $footer_vn_title = tr_options_field('tr_theme_options.footer_vn_title' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_vn_title');
            $footer_vn_address1 = tr_options_field('tr_theme_options.footer_vn_address1' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_vn_address1');
            $footer_vn_address2 = tr_options_field('tr_theme_options.footer_vn_address2' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_vn_address2');
            $footer_us_title = tr_options_field('tr_theme_options.footer_us_title' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_us_title');
            $footer_us_address1 = tr_options_field('tr_theme_options.footer_us_address1' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_us_address1');
            $footer_us_address2 = tr_options_field('tr_theme_options.footer_us_address2' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_us_address2');
            $footer_text_policy = tr_options_field('tr_theme_options.footer_text_policy' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_text_policy');
            $footer_link_policy = tr_options_field('tr_theme_options.footer_link_policy' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_link_policy');
            $footer_copyright = tr_options_field('tr_theme_options.footer_copyright' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_copyright');
            ?>
            <div class="footer_content">
                <div class="grid">
                    <div class="footer_content_address address1">
                        <div class="footer_content_address_title txt_16 txt_medium"><?php echo esc_html($footer_vn_title ?: 'VN OFFICE'); ?></div>
                        <div class="footer_content_address_txt txt_18"><?php echo esc_html($footer_vn_address1 ?: '77 Hoa Lan, Cau Kieu Ward,'); ?></div>
                        <div class="footer_content_address_txt txt_18"><?php echo esc_html($footer_vn_address2 ?: 'Ho Chi Minh City, Vietnam'); ?></div>
                        <div class="footer_content_address_tel txt_18"><?php echo esc_html(tr_options_field('tr_theme_options.footer_vn_tel') ?: '0929 100 990'); ?></div>
                    </div>
                    <div class="footer_content_address address2">
                        <div class="footer_content_address_title txt_16 txt_medium"><?php echo esc_html($footer_us_title ?: 'US OFFICE'); ?></div>
                        <div class="footer_content_address_txt txt_18"><?php echo esc_html($footer_us_address1 ?: '3975 Fair Ridge Dr, Fairfax,'); ?></div>
                        <div class="footer_content_address_txt txt_18"><?php echo esc_html($footer_us_address2 ?: 'VA 22033, Washington DC, USA'); ?></div>
                        <div class="footer_content_address_tel txt_18"><?php echo esc_html(tr_options_field('tr_theme_options.footer_us_tel') ?: '703-981-8080'); ?></div>
                    </div>
                    <?php
                    $f_title = tr_options_field('tr_theme_options.footer_form_title' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_form_title') ?: 'GET IN TOUCH WITH US';
                    $f_name = tr_options_field('tr_theme_options.footer_form_name' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_form_name') ?: 'Your name*';
                    $f_phone = tr_options_field('tr_theme_options.footer_form_phone' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_form_phone') ?: 'Phone number*';
                    $f_msg = tr_options_field('tr_theme_options.footer_form_message' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_form_message') ?: 'Your Message';
                    $f_submit = tr_options_field('tr_theme_options.footer_form_submit' . $lang_suffix) ?: tr_options_field('tr_theme_options.footer_form_submit') ?: 'submit';
                    ?>
                    <div class="footer_content_form">
                        <div class="footer_form_title txt_16 txt_uppercase heading cl_be txt_16 txt_medium"><?php echo wp_kses_post(nl2br($f_title)); ?></div>
                        <form class="footer_form" novalidate>
                            <div class="footer_form_row">
                                <input type="text" name="footer_name" placeholder="<?php echo esc_attr($f_name); ?>" class="footer_form_input" required>
                                <input type="tel" name="footer_phone" placeholder="<?php echo esc_attr($f_phone); ?>" class="footer_form_input" required>
                            </div>
                            <div class="footer_form_row">
                                <input type="text" name="footer_message" placeholder="<?php echo esc_attr($f_msg); ?>" class="footer_form_input">
                            </div>

                            <div class="footer_form_row">
                                <button type="submit"
                                    class="btn_submit button_hover hover_txt txt_14 txt_uppercase cl_be">
                                    <div class="hover_txt_grid">
                                        <span class="init"><?php echo esc_html($f_submit); ?></span>
                                        <span class="active"><?php echo esc_html($f_submit); ?></span>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="footer_bot">
                <div class="grid">
                    <div class="footer_bot_logo svg_full">
                        <div style="position: relative; width: 100%;">
                            <?php
                            $footer_vid_url = tr_options_field('tr_theme_options.footer_video_button') ?: get_template_directory_uri() . '/images/video_button.mp4';
                            $mask_svg = '<svg width="641" height="117" viewBox="0 0 641 117" xmlns="http://www.w3.org/2000/svg"><path d="M435.777 0L393.388 95.7474V0H357.202L320.5 54.3431L283.798 0H247.612V116.967H283.798V55.8958L306.543 91.0894H320.5H334.457L357.202 55.8958V116.967H384.083H393.388H424.921L429.573 105.063L455.42 38.8165L486.436 116.967H526.757L475.581 0H435.777Z"/><path d="M602.23 63.1416L580.002 93.6773L597.06 116.967H641L602.23 63.1416Z"/><path d="M640.483 0H596.544L580.002 22.7724L601.713 53.308L640.483 0Z"/><path d="M160.25 0V15.0091V51.2378H102.353V0H87.3621H51.1766H36.7024H0V15.0091H36.7024V116.967H51.1766V15.0091H87.3621V51.2378V65.7293V116.967H102.353V65.7293H160.25V101.958V116.967H175.241H233.655V101.958H175.241V65.7293H233.655V51.2378H175.241V15.0091H233.655V0H175.241H160.25Z"/></svg>';
                            $mask_url = "data:image/svg+xml;base64," . base64_encode($mask_svg);
                            ?>
                            <video data-src="<?php echo esc_url($footer_vid_url); ?>"
                                poster="/wp-content/uploads/2026/07/poster.jpg" preload="none"
                                loop muted playsinline class="lazy-footer-video"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; pointer-events: none; -webkit-mask-image: url('<?php echo $mask_url; ?>'); mask-image: url('<?php echo $mask_url; ?>'); -webkit-mask-size: 100% 100%; mask-size: 100% 100%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat;"></video>
    
                            <svg width="641" height="117" viewBox="0 0 641 117" fill="none"
                                xmlns="http://www.w3.org/2000/svg" style="position: relative; z-index: 1; pointer-events: none; width: 100%; height: auto; display: block;">
                                <!-- Red decoration -->
                                <path d="M556.223 0H511.766L554.155 57.966L511.249 116.967H555.706L598.612 57.966L556.223 0Z" fill="#E62636" />
                            </svg>
                        </div>
                    </div>
                    <div class="footer_bot_contact">
                        <div class="footer_bot_contact_social">
                            <?php $fb_link = tr_options_field('tr_theme_options.footer_social_fb'); ?>
                            <a href="<?php echo esc_url($fb_link ?: '#'); ?>" class="footer_bot_contact_social_item svg_full ic_fb" <?php if($fb_link) echo 'target="_blank"'; ?>>
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1042_105)">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626"
                                            fill="transparent" />
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
                            <?php $zl_link = tr_options_field('tr_theme_options.footer_social_zl'); ?>
                            <a href="<?php echo esc_url($zl_link ?: '#'); ?>" class="footer_bot_contact_social_item svg_full ic_zalo" <?php if($zl_link) echo 'target="_blank"'; ?>>
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_749_3641)">
                                        <rect width="48" height="48" fill="transparent" stroke="#262626" />
                                        <path
                                            d="M16.9904 18H7.2654V20.0853H14.0141L7.3601 28.3317C7.1516 28.635 7 28.9194 7 29.5639V30.0947H16.1752C16.6302 30.0947 17.0094 29.7156 17.0094 29.2606V28.1421H9.9194L16.1752 20.2938C16.27 20.1801 16.4406 19.9716 16.5165 19.8768L16.5544 19.8199C16.9146 19.2891 16.9904 18.8341 16.9904 18.2844V18Z"
                                            fill="currentColor" />
                                        <path
                                            d="M29.3689 30.0947H30.7528V18H28.6675V29.3933C28.6675 29.7725 28.9708 30.0947 29.3689 30.0947Z"
                                            fill="currentColor" />
                                        <path
                                            d="M22.2413 20.6924C19.6252 20.6924 17.502 22.8156 17.502 25.4317C17.502 28.0478 19.6252 30.171 22.2413 30.171C24.8574 30.171 26.9806 28.0478 26.9806 25.4317C26.9996 22.8156 24.8764 20.6924 22.2413 20.6924ZM22.2413 28.2184C20.7058 28.2184 19.4546 26.9672 19.4546 25.4317C19.4546 23.8962 20.7058 22.645 22.2413 22.645C23.7768 22.645 25.028 23.8962 25.028 25.4317C25.028 26.9672 23.7958 28.2184 22.2413 28.2184Z"
                                            fill="currentColor" />
                                        <path
                                            d="M36.9139 20.6162C34.2788 20.6162 32.1367 22.7584 32.1367 25.3934C32.1367 28.0285 34.2788 30.1707 36.9139 30.1707C39.5489 30.1707 41.6911 28.0285 41.6911 25.3934C41.6911 22.7584 39.5489 20.6162 36.9139 20.6162ZM36.9139 28.2181C35.3594 28.2181 34.1082 26.9669 34.1082 25.4124C34.1082 23.8579 35.3594 22.6067 36.9139 22.6067C38.4684 22.6067 39.7196 23.8579 39.7196 25.4124C39.7196 26.9669 38.4684 28.2181 36.9139 28.2181Z"
                                            fill="currentColor" />
                                        <path
                                            d="M25.8834 30.0944H27.0019V20.957H25.0493V29.2793C25.0493 29.7153 25.4284 30.0944 25.8834 30.0944Z"
                                            fill="currentColor" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_749_3641">
                                            <rect width="48" height="48" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>


                            </a>
                            <?php $li_link = tr_options_field('tr_theme_options.footer_social_li'); ?>
                            <a href="<?php echo esc_url($li_link ?: '#'); ?>" class="footer_bot_contact_social_item svg_full ic_linkin" <?php if($li_link) echo 'target="_blank"'; ?>>
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1042_121)">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626"
                                            fill="transparent" />
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
                            <?php $pi_link = tr_options_field('tr_theme_options.footer_social_pi'); ?>
                            <a href="<?php echo esc_url($pi_link ?: '#'); ?>" class="footer_bot_contact_social_item svg_full ic_pret" <?php if($pi_link) echo 'target="_blank"'; ?>>
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1042_128)">
                                        <rect x="0.5" y="0.5" width="47" height="47" stroke="#262626"
                                            fill="transparent" />
                                        <path
                                            d="M24 14C18.4766 14 14 18.4766 14 24C14 28.2383 16.6367 31.8555 20.3555 33.3125C20.2695 32.5195 20.1875 31.3086 20.3906 30.4453C20.5742 29.6641 21.5625 25.4766 21.5625 25.4766C21.5625 25.4766 21.2617 24.8789 21.2617 23.9922C21.2617 22.6016 22.0664 21.5625 23.0703 21.5625C23.9219 21.5625 24.3359 22.2031 24.3359 22.9727C24.3359 23.832 23.7891 25.1133 23.5078 26.3008C23.2734 27.2969 24.0078 28.1094 24.9883 28.1094C26.7656 28.1094 28.1328 26.2344 28.1328 23.5312C28.1328 21.1367 26.4141 19.4609 23.957 19.4609C21.1133 19.4609 19.4414 21.5937 19.4414 23.8008C19.4414 24.6602 19.7734 25.582 20.1875 26.082C20.2695 26.1797 20.2812 26.2695 20.2578 26.3672C20.1836 26.6836 20.0117 27.3633 19.9805 27.5C19.9375 27.6836 19.8359 27.7227 19.6445 27.6328C18.3945 27.0508 17.6133 25.2266 17.6133 23.7578C17.6133 20.6016 19.9063 17.707 24.2188 17.707C27.6875 17.707 30.3828 20.1797 30.3828 23.4844C30.3828 26.9297 28.2109 29.7031 25.1953 29.7031C24.1836 29.7031 23.2305 29.1758 22.9023 28.5547C22.9023 28.5547 22.4023 30.4648 22.2812 30.9336C22.0547 31.8008 21.4453 32.8906 21.0391 33.5547C21.9766 33.8438 22.9688 34 24 34C29.5234 34 34 29.5234 34 24C34 18.4766 29.5234 14 24 14Z"
                                            fill="#BEBEBE" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1042_128">
                                            <rect width="48" height="48" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>


                            </a>
                        </div>
                        <div class="footer_bot_contact_copyright_wrap">
                            <a href="<?php echo esc_url($footer_link_policy ?: '#'); ?>" class="footer_bot_contact_policy txt_14"><?php echo esc_html($footer_text_policy ?: 'Privacy Policy'); ?></a>
                            <div class="footer_bot_contact_copyright txt_14">
                                <?php echo esc_html($footer_copyright ?: '© 2026 Wonom. All Rights Reserved.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<a class="btn_top" href="#top">
    <div class="btn_top_inner svg_full">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_1159_1231)">
                <path d="M5 12L12 5L19 12" stroke="#BEBEBE" stroke-width="1.5" stroke-linejoin="round" />
                <path d="M12 19V5" stroke="#BEBEBE" stroke-width="1.5" stroke-linejoin="round" />
            </g>
            <defs>
                <clipPath id="clip0_1159_1231">
                    <rect width="24" height="24" fill="#BEBEBE" />
                </clipPath>
            </defs>
        </svg>

    </div>
</a>
</body>

</html>
<?php wp_footer(); ?>
</body>

</html>
