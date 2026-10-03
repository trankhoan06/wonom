<?php
if (!defined('ABSPATH')) exit;
?>
<section class="popup_form popup_form_booking" aria-hidden="true">
        <div class="popup_form_overlay"></div>
        <div class="popup_form_inner">
            <div class="popup_form_content">
                <div class="popup_form_head">
                    <div class="popup_tour_img img_full">
                        <img src="/asset/img/ex_bg_bot.png" alt="">
                    </div>
                    <div class="popup_form_booking_heading">
                        <div class="popup_tour_title popup_form_booking_title heading h4 h5_mb cl_cream">ĐẶT CHỖ ONLINE
                        </div>
                        <div class="popup_form_booking_subtitle txt_14 txt_extrabold cl_red">WONOM</div>
                    </div>
                    <button class="popup_tour_close btn" type="button" aria-label="Đóng form đặt chỗ">
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
                    <input type="hidden" name="booking_type" value="general">
                    <input type="hidden" name="floor" value="">
                    <input type="hidden" name="venue" value="WONOM">
                    <input type="hidden" name="product_id" value="">
                    <input type="hidden" name="product_name" value="">
                    <div class="popup_form_row">
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-name">HỌ VÀ
                                TÊN</label>
                            <input id="booking-name" name="name" type="text" class="popup_form_input"
                                placeholder="Nhập tên của bạn" autocomplete="name">
                        </div>
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-phone">SỐ ĐIỆN
                                THOẠI</label>
                            <input id="booking-phone" name="phone" type="tel" class="popup_form_input"
                                placeholder="Nhập số điện thoại" autocomplete="tel">
                        </div>
                    </div>
                    <div class="popup_form_row popup_form_booking_details_row">
                        <div class="popup_form_group popup_form_booking_floor_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-floor">BẠN MUỐN
                                ĐẾN TẦNG NÀO?</label>
                            <div class="popup_form_input_wrap relative">
                                <select id="booking-floor" class="popup_form_input popup_form_select"
                                    data-booking-floor-select>
                                    <option value="" disabled selected>Chọn tầng</option>
                                    <option value="1F">Tầng 1 - Nhà hàng Mujige</option>
                                    <option value="2F">Tầng 2 - Trang phục truyền thống</option>
                                    <option value="3F">Tầng 3 - Workshop</option>
                                    <option value="4F">Tầng 4 - Stress Room</option>
                                    <option value="5F">Tầng 5 - Rooftop Disco</option>
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
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-guests">SỐ
                                NGƯỜI</label>
                            <div class="popup_form_input_wrap relative">
                                <select id="booking-guests" name="guests" class="popup_form_input popup_form_select">
                                    <option value="" disabled selected>Chọn số người đến</option>
                                    <option value="1">1 người</option>
                                    <option value="2">2 người</option>
                                    <option value="3">3 người</option>
                                    <option value="4">4 người</option>
                                    <option value="5+">5+ người</option>
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
                    </div>
                    <div class="popup_form_row">
                        <div class="popup_form_group">
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-date">CHỌN
                                NGÀY</label>
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
                            <label class="popup_form_label txt_15 txt_extrabold cl_text" for="booking-time">THỜI GIAN
                                ĐẾN</label>
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
                            type="submit">GỬI YÊU CẦU ĐẶT CHỖ</button>
                        <div class="popup_form_note txt_13 txt_bold cl_text txt_center">Bạn sẽ được chuyển sang Zalo để
                            xác nhận lịch với nhân viên.</div>
                        <div class="popup_form_divider txt_13 txt_bold cl_dark_brown">HOẶC GỌI HỖ TRỢ</div>
                        <a class="popup_form_btn btn2 cl_cream txt_18 txt_extrabold btn" href="tel:0968487096">0968 487
                            096</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
