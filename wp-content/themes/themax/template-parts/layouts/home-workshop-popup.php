<?php
if (!defined('ABSPATH')) exit;
?>
<section class="workshop_detail_popup" aria-hidden="true">
        <div class="workshop_detail_overlay"></div>
        <div class="workshop_detail_dialog" role="dialog" aria-modal="true" aria-labelledby="workshop-detail-title">
            <div class="workshop_detail_media">
                <div class="workshop_detail_main_image">
                    <img src="/asset/img/store.jpg" alt="Chi tiết workshop">
                    <button class="workshop_detail_arrow workshop_detail_prev btn bg_white" type="button"
                        aria-label="Ảnh trước">
                        <span class="btn-inner">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.10744 9.99998L15.8926 9.99998" stroke="#460700" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10.5889 15.3034L15.8922 10.0001L10.5889 4.69678" stroke="#460700"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                    <button class="workshop_detail_arrow workshop_detail_next btn bg_white" type="button"
                        aria-label="Ảnh tiếp theo">
                        <span class="btn-inner">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.10744 9.99998L15.8926 9.99998" stroke="#460700" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M10.5889 15.3034L15.8922 10.0001L10.5889 4.69678" stroke="#460700"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                </div>
                <div class="workshop_detail_thumbs swiper">
                    <div class="workshop_detail_thumbs_inner swiper-wrapper"></div>
                    <div class="workshop_detail_thumbs_scrollbar swiper-scrollbar"></div>
                </div>
            </div>
            <div class="workshop_detail_info">
                <button class="workshop_detail_close popup_tour_close btn" type="button"
                    aria-label="Đóng chi tiết workshop">
                    <span class="popup_tour_close_inner btn-inner svg_full">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.043 4.54289C18.4335 4.15237 19.0666 4.15237 19.4571 4.54289C19.8475 4.93342 19.8476 5.56646 19.4571 5.95696L13.4141 11.9999L19.4571 18.0429C19.8475 18.4334 19.8476 19.0665 19.4571 19.457C19.0666 19.8475 18.4335 19.8474 18.043 19.457L12 13.414L5.95708 19.457C5.56658 19.8475 4.93355 19.8474 4.54302 19.457C4.15249 19.0664 4.15249 18.4334 4.54302 18.0429L10.586 11.9999L4.54302 5.95696C4.15249 5.56643 4.15249 4.93342 4.54302 4.54289C4.93354 4.15237 5.56655 4.15237 5.95708 4.54289L12 10.5859L18.043 4.54289Z"
                                fill="#460700" />
                        </svg>
                    </span>
                </button>
                <div class="workshop_detail_body txt_14" custom-scroll>
                    <div class="workshop_detail_tag home_explore_content_tag_item txt_14 txt_bold workshop-detail-tag">#Nón</div>
                    <h2 id="workshop-detail-title" class="workshop_detail_title heading h4">VẼ NÓN LÁ VIỆT NAM</h2>
                    <p class="workshop_detail_description cl_meta">Nét đẹp Việt Nam qua từng nét cọ trên chiếc nón lá
                        thủ công.</p>
                    <div class="workshop_detail_divider"></div>
                    <div class="workshop_detail_section ">
                        <h3 class="txt_13 txt_extrabold cl_red">WORKSHOP TẠI WONOM</h3>
                        <ul>
                            <li><strong>Sáng tạo không giới hạn:</strong> Biến những hạt gạo trắng, gạo nâu, gạo nhuộm
                                màu thành những bức tranh sống động.</li>
                            <li><strong>Phù hợp mọi lứa tuổi:</strong> Từ trẻ em đến người lớn đều có thể tham gia và
                                tận hưởng trọn vẹn niềm vui.</li>
                            <li><strong>Kết nối &amp; gắn kết:</strong> Hoạt động lý tưởng cho nhóm bạn, gia đình, công
                                ty hoặc trường học.</li>
                        </ul>
                    </div>
                    <div class="workshop_detail_section">
                        <h3 class="txt_13 txt_extrabold cl_red">DỤNG CỤ BAO GỒM</h3>
                        <ul>
                            <li><strong>Nón lá, màu vẽ, cọ, nước trà...</strong></li>
                        </ul>
                    </div>
                </div>
                <div class="workshop_detail_footer">
                    <div class="workshop_detail_price_row">
                        <span class="txt_14 txt_extrabold">GIÁ SẢN PHẨM</span>
                        <span><strong class="workshop_detail_price heading h6 cl_red">59,000đ</strong> <span
                                class="cl_meta txt_15">/ 60-90 phút</span></span>
                    </div>
                    <button class="workshop_detail_booking popup_form_btn cl_cream txt_extrabold btn txt_16"
                        type="button" data-booking-trigger data-booking-type="workshop" data-booking-floor="3F"
                        data-booking-venue="WORKSHOP" data-booking-title="ĐẶT LỊCH WORKSHOP"
                        data-booking-subtitle="3F - WORKSHOP" data-booking-submit="GỬI YÊU CẦU ĐẶT LỊCH">ĐẶT LỊCH QUA
                        ZALO</button>
                    <div class="workshop_detail_note txt_14 txt_bold txt_center">Bạn sẽ được chuyển sang Zalo để xác
                        nhận lịch với nhân viên.</div>
                </div>
            </div>
        </div>
    </section>
