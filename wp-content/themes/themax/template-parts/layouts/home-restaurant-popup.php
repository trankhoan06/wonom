<?php
if (!defined('ABSPATH')) exit;
?>
<section class="popup_tour restaurant seemore">
        <div class="popup_tour_head">
            <div class="popup_tour_title explore_popup_title heading h4 h6_mb cl_cream">1F · NHÀ HÀNG MUJIGE</div>
            <div class="popup_tour_img img_full">
                <img src="/asset/img/ex_bg_bot.png" alt="">
            </div>
            <div class="restaurant_popup_tabs" role="tablist" aria-label="Nội dung tầng">
                <button class="restaurant_popup_tab btn bg_white" type="button" role="tab" aria-selected="false"
                    data-restaurant-view="menu">
                    <span class="btn-inner restaurant_popup_menu_label txt_14 txt_extrabold">THỰC ĐƠN</span>
                </button>
                <button class="restaurant_popup_tab btn bg_white active" type="button" role="tab" aria-selected="true"
                    data-restaurant-view="gallery">
                    <span class="btn-inner txt_14 txt_extrabold">THƯ VIỆN ẢNH</span>
                </button>
            </div>
            <div class="popup_tour_close btn">
                <div class="popup_tour_close_inner btn-inner svg_full">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18.043 4.54289C18.4335 4.15237 19.0666 4.15237 19.4571 4.54289C19.8475 4.93342 19.8476 5.56646 19.4571 5.95696L13.4141 11.9999L19.4571 18.0429C19.8475 18.4334 19.8476 19.0665 19.4571 19.457C19.0666 19.8475 18.4335 19.8474 18.043 19.457L12 13.414L5.95708 19.457C5.56658 19.8475 4.93355 19.8474 4.54302 19.457C4.15249 19.0664 4.15249 18.4334 4.54302 18.0429L10.586 11.9999L4.54302 5.95696C4.15249 5.56643 4.15249 4.93342 4.54302 4.54289C4.93354 4.15237 5.56655 4.15237 5.95708 4.54289L12 10.5859L18.043 4.54289Z"
                            fill="#460700" />
                    </svg>

                </div>
            </div>
        </div>
        <div class="popup_tour_content restaurant_popup_panel restaurant_gallery active" data-restaurant-panel="gallery"
            custom-scroll>
            <div class="container">
                <div class="popup_tour_seeall__inner">
                    <div class="popup_tour_seeall_list"></div>
                </div>
            </div>
        </div>
        <div class="popup_tour_content restaurant_popup_panel restaurant_menu" data-restaurant-panel="menu">
            <div class="restaurant_menu_viewer_bg img_full">
                <img src="./asset/img/pattern.webp" alt="">
            </div>
            <div class="restaurant_menu_sidebar" aria-label="Danh sách trang thực đơn">
                <div class="restaurant_menu_thumbs"></div>
            </div>
            <div class="restaurant_menu_viewer">
                <button class="restaurant_menu_arrow restaurant_menu_prev btn bg_white" type="button"
                    aria-label="Trang thực đơn trước">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path d="M4.10744 9.99998L15.8926 9.99998" stroke="#460700" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.5889 15.3033L15.8922 10L10.5889 4.69672" stroke="#460700" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="restaurant_flipbook_wrap">
                    <div id="restaurant-flipbook" class="restaurant_flipbook">
                        <img class="restaurant_flipbook_fallback" src="/asset/img/menu1.webp"
                            alt="Thực đơn nhà hàng Mujige">
                    </div>
                    <div class="restaurant_menu_page_count txt_14 txt_bold" aria-live="polite">1 / 10</div>
                </div>
                <button class="restaurant_menu_arrow restaurant_menu_next btn bg_white" type="button"
                    aria-label="Trang thực đơn tiếp theo">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path d="M4.10744 9.99998L15.8926 9.99998" stroke="#460700" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.5889 15.3033L15.8922 10L10.5889 4.69672" stroke="#460700" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="home_piano" aria-hidden="true">
            <div class="home_piano_item item1"></div>
            <div class="home_piano_item item2"></div>
            <div class="home_piano_item item3"></div>
            <div class="home_piano_item item4"></div>
            <div class="home_piano_item item5"></div>
        </div>
    </section>
