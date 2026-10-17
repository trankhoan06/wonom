<?php
if (!defined('ABSPATH')) exit;

$booking_bg = get_template_directory_uri() . '/asset/img/ex_bg_bot.png';
$booking_title = tr_options_field('wonom_home_options.home_booking_title') ?: wonom_home_t('online_booking');
$booking_subtitle = 'WONOM';

$label_name = tr_options_field('wonom_home_options.home_booking_label_name') ?: wonom_home_t('full_name');
$placeholder_name = tr_options_field('wonom_home_options.home_booking_placeholder_name') ?: wonom_home_t('enter_name');

$label_phone = tr_options_field('wonom_home_options.home_booking_label_phone') ?: wonom_home_t('phone');
$placeholder_phone = tr_options_field('wonom_home_options.home_booking_placeholder_phone') ?: wonom_home_t('enter_phone');

$label_floor = tr_options_field('wonom_home_options.home_booking_label_floor') ?: wonom_home_t('which_floor');
$placeholder_floor = tr_options_field('wonom_home_options.home_booking_placeholder_floor') ?: wonom_home_t('select_floor');

$label_guests = tr_options_field('wonom_home_options.home_booking_label_guests') ?: wonom_home_t('guest_count');
$placeholder_guests = tr_options_field('wonom_home_options.home_booking_placeholder_guests') ?: wonom_home_t('select_guests');

$label_date = tr_options_field('wonom_home_options.home_booking_label_date') ?: wonom_home_t('select_date');
$label_time = tr_options_field('wonom_home_options.home_booking_label_time') ?: wonom_home_t('arrival_time');

$submit_text = tr_options_field('wonom_home_options.home_booking_submit_text') ?: wonom_home_t('send_reservation');
$note_text = tr_options_field('wonom_home_options.home_booking_note_text') ?: wonom_home_t('zalo_note');
$divider_text = tr_options_field('wonom_home_options.home_booking_support_divider') ?: wonom_home_t('or_call');
$hotline = tr_options_field('wonom_home_options.home_booking_hotline') ?: '0968 487 096';
$hotline_tel = preg_replace('/[^0-9+]/', '', $hotline);
?>
<section class="popup_form popup_form_booking" aria-hidden="true">
        <div class="popup_form_overlay"></div>
        <div class="popup_form_inner">
            <div class="popup_form_content">
                <div class="popup_form_head">
                    <div class="popup_tour_img img_full">
                        <img src="<?php echo esc_url($booking_bg); ?>" alt="">
                    </div>
                    <div class="popup_form_booking_heading">
                        <div class="popup_tour_title popup_form_booking_title heading h4 h5_mb cl_cream"><?php echo esc_html($booking_title); ?></div>
                        <div class="popup_form_booking_subtitle txt_14 txt_extrabold cl_red"><?php echo esc_html($booking_subtitle); ?></div>
                    </div>
                    <button class="popup_tour_close btn" type="button" aria-label="<?php echo esc_attr(wonom_home_t('close_booking')); ?>">
                        <span class="popup_tour_close_inner btn-inner svg_full">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18.043 4.54289C18.4335 4.15237 19.0666 4.15237 19.4571 4.54289C19.8475 4.93342 19.8476 5.56646 19.4571 5.95696L13.4141 11.9999L19.4571 18.0429C19.8475 18.4334 19.8476 19.0665 19.4571 19.457C19.0666 19.8475 18.4335 19.8474 18.043 19.457L12 13.414L5.95708 19.457C5.56658 19.8475 4.93355 19.8474 4.54302 19.457C4.15249 19.0664 4.15249 18.4334 4.54302 18.0429L10.586 11.9999L4.54302 5.95696C4.15249 5.56643 4.15249 4.93342 4.54302 4.54289C4.93354 4.15237 5.56655 4.15237 5.95708 4.54289L12 10.5859L18.043 4.54289Z"
                                    fill="#460700" />
                            </svg>
                        </span>
                    </button>
                </div>
                <form class="popup_form_body popup_form_booking_form" action="#" method="post">
                    <div class="wonom-form-trap" aria-hidden="true">
                        <label for="booking-website">Để trống trường này</label>
                        <input id="booking-website" name="website" type="text" value="" tabindex="-1"
                            autocomplete="off">
                    </div>
                    <input type="hidden" name="booking_type" value="general">
                    <input type="hidden" name="floor" value="">
                    <input type="hidden" name="venue" value="WONOM">
                    <input type="hidden" name="product_id" value="">
                    <div class="popup_form_group popup_form_booking_product_group" hidden>
                        <div class="popup_form_label txt_15 txt_extrabold cl_text"><?php echo esc_html(wonom_home_t('format')); ?></div>
                        <input name="product_name" type="hidden" value="">
                        <div class="popup_form_booking_product_display" data-booking-product-display></div>
                    </div>
                    <div class="popup_form_row">
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-name"><?php echo esc_html($label_name); ?></label>
                            <input id="booking-name" name="name" type="text" class="popup_form_input"
                                placeholder="<?php echo esc_attr($placeholder_name); ?>" autocomplete="name" maxlength="80">
                        </div>
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-phone"><?php echo esc_html($label_phone); ?></label>
                            <input id="booking-phone" name="phone" type="tel" class="popup_form_input"
                                placeholder="<?php echo esc_attr($placeholder_phone); ?>" autocomplete="tel" maxlength="20">
                        </div>
                    </div>
                    <div class="popup_form_row popup_form_booking_details_row">
                        <div class="popup_form_group popup_form_booking_floor_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-floor"><?php echo esc_html($label_floor); ?></label>
                            <div class="popup_form_input_wrap relative">
                                <select id="booking-floor" class="popup_form_input popup_form_select"
                                    data-booking-floor-select>
                                    <option value="" disabled selected><?php echo esc_html($placeholder_floor); ?></option>
                                    <option value="1F"><?php echo esc_html(wonom_home_t('floor_1')); ?></option>
                                    <option value="2F"><?php echo esc_html(wonom_home_t('floor_2')); ?></option>
                                    <option value="3F"><?php echo esc_html(wonom_home_t('floor_3')); ?></option>
                                    <option value="4F"><?php echo esc_html(wonom_home_t('floor_4')); ?></option>
                                    <option value="5F"><?php echo esc_html(wonom_home_t('floor_5')); ?></option>
                                </select>
                                <span class="popup_form_input_icon absolute svg_full" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 7.5L10 12.5L15 7.5" stroke="#460700" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-guests"><?php echo esc_html($label_guests); ?></label>
                            <div class="popup_form_input_wrap relative">
                                <input id="booking-guests" name="guests" type="number" min="1" max="999" step="1"
                                    inputmode="numeric" class="popup_form_input" placeholder="<?php echo esc_attr($placeholder_guests); ?>"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="popup_form_row popup_form_datetime_row">
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-date"><?php echo esc_html($label_date); ?></label>
                            <div class="popup_form_input_wrap relative">
                                <input id="booking-date" name="date" type="date" class="popup_form_input">
                                <span class="popup_form_input_icon absolute svg_full" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z"
                                            stroke="#460700" stroke-width="2" />
                                        <path d="M16 2V6M8 2V6M3 10H21" stroke="#460700" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-time"><?php echo esc_html($label_time); ?></label>
                            <div class="popup_form_input_wrap relative">
                                <input id="booking-time" name="time" type="time" class="popup_form_input">
                                <span class="popup_form_input_icon absolute svg_full" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="9" stroke="#460700" stroke-width="2" />
                                        <path d="M12 7V12L15 14" stroke="#460700" stroke-width="2"
                                            stroke-linecap="round" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="popup_form_actions">
                        <button class="popup_form_btn popup_form_booking_submit cl_cream txt_extrabold btn"
                            type="submit"><?php echo esc_html($submit_text); ?></button>
                        <div class="popup_form_note txt_13 txt_bold cl_text txt_center"><?php echo esc_html($note_text); ?></div>
                        <div class="popup_form_divider txt_13 txt_bold cl_dark_brown"><?php echo esc_html($divider_text); ?></div>
                        <a class="popup_form_btn btn2 cl_cream txt_18 txt_extrabold btn" href="tel:<?php echo esc_attr($hotline_tel); ?>"><?php echo esc_html($hotline); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </section>
