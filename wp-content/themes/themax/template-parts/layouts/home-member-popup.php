<?php
if (!defined('ABSPATH')) exit;
$member_zalo_url = esc_url((string) tr_options_field('tr_theme_options.home_footer_zalo_url')) ?: 'https://zalo.me/';
?>
<section class="popup_member" aria-hidden="true" data-member-state="register">
    <button class="popup_member_back btn txt_14 txt_extrabold cl_text" type="button">
        <span class="btn-inner">
            <img src="/asset/img/icon_back.svg" alt=""> Về Trang chủ
        </span>
    </button>

    <div class="popup_member_inner">
        <div class="popup_member_overlay bg_cream">
            <div class="popup_member_bg img_full absolute"><img src="/asset/img/intro_bg.png" alt=""></div>
            <div class="popup_member_bg1 img_full absolute"><img src="/asset/img/sign_team.webp" alt=""></div>
        </div>

        <div class="popup_member_content_wrap">
            <header class="popup_member_header">
                <div class="popup_member_subtitle txt_title">WONOM - MEMBERSHIP</div>
                <h2 class="popup_member_title heading h3 cl_ffce56" data-member-title>ĐĂNG KÝ THÀNH VIÊN</h2>
            </header>

            <div class="popup_member_content relative">
                <div class="popup_member_panel is-active" data-member-panel="register">
                    <form class="popup_member_form popup_member_register" novalidate>
                        <p class="popup_member_lead">Đăng ký ngay để nhận ưu đãi khi trải nghiệm và xuất trình thẻ thành viên tại quầy thanh toán.</p>
                        <div class="wonom-form-trap" aria-hidden="true">
                            <label for="membership-website">Để trống trường này</label>
                            <input id="membership-website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="popup_member_fields_row">
                            <label class="popup_form_group">
                                <span class="popup_form_label txt_15 txt_extrabold">HỌ VÀ TÊN</span>
                                <input name="name" type="text" class="popup_form_input" placeholder="Nhập tên của bạn" maxlength="80" autocomplete="name">
                            </label>
                            <label class="popup_form_group">
                                <span class="popup_form_label txt_15 txt_extrabold">SỐ ĐIỆN THOẠI</span>
                                <input name="phone" type="tel" class="popup_form_input" placeholder="Nhập số điện thoại" maxlength="20" autocomplete="tel">
                            </label>
                        </div>
                        <label class="popup_form_group">
                            <span class="popup_form_label txt_15 txt_extrabold">EMAIL</span>
                            <input name="email" type="email" class="popup_form_input" placeholder="Nhập địa chỉ email" maxlength="190" autocomplete="email">
                        </label>
                        <div class="popup_member_checkbox_wrap">
                            <label class="popup_member_checkbox_label">
                                <input name="consent" type="checkbox" class="popup_member_checkbox" value="1">
                                <span class="popup_member_checkbox_custom"></span>
                                <span class="popup_member_checkbox_txt txt_13 cl_text">Tôi đồng ý với <strong>Điều khoản &amp; Điều kiện</strong> và đã đọc <strong>Chính sách bảo mật</strong> của Wonom Culture Complex.</span>
                            </label>
                        </div>
                        <button class="popup_member_btn bg_ffce56 cl_text txt_extrabold btn" type="submit">NHẬN MÃ XÁC NHẬN</button>
                        <p class="popup_member_hint">Mã xác nhận sẽ được gửi đến email của bạn.</p>
                    </form>
                    <div class="popup_member_switch">Bạn đã là thành viên? <button type="button" data-member-go="lookup">Tra cứu thẻ</button></div>
                </div>

                <div class="popup_member_panel" data-member-panel="verify">
                    <form class="popup_member_form popup_member_verify" novalidate>
                        <p class="popup_member_lead">Nhập mã xác nhận gồm 6 chữ số đã được gửi đến email <strong data-member-masked-email></strong> để tiếp tục.</p>
                        <div class="popup_member_otp" data-member-otp>
                            <?php for ($i = 0; $i < 6; $i++) : ?>
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Chữ số xác nhận <?php echo esc_attr($i + 1); ?>">
                            <?php endfor; ?>
                        </div>
                        <p class="popup_member_resend">Chưa nhận được mã? <button type="button" data-member-resend disabled>Gửi lại sau <span data-member-countdown>60</span> giây</button></p>
                        <button class="popup_member_btn bg_ffce56 cl_text txt_extrabold btn" type="submit" disabled data-member-confirm>XÁC NHẬN &amp; HOÀN TẤT</button>
                    </form>
                    <div class="popup_member_switch">Email không chính xác? <button type="button" data-member-go="register">Thay đổi Email</button></div>
                </div>

                <div class="popup_member_panel" data-member-panel="lookup">
                    <form class="popup_member_form popup_member_lookup" novalidate>
                        <p class="popup_member_lead">Nhập địa chỉ Email đã đăng ký để nhận mã xác nhận và truy cập lại thẻ thành viên WONOM của bạn.</p>
                        <label class="popup_form_group">
                            <span class="popup_form_label txt_15 txt_extrabold">EMAIL ĐÃ ĐĂNG KÝ</span>
                            <input name="email" type="email" class="popup_form_input" placeholder="Nhập địa chỉ email" maxlength="190" autocomplete="email">
                        </label>
                        <button class="popup_member_btn bg_ffce56 cl_text txt_extrabold btn" type="submit">NHẬN MÃ XÁC NHẬN</button>
                        <p class="popup_member_hint">Mã xác nhận sẽ được gửi đến email đã đăng ký.</p>
                    </form>
                    <div class="popup_member_switch">Chưa là thành viên? <button type="button" data-member-go="register">Đăng ký ngay</button></div>
                </div>

                <div class="popup_member_panel" data-member-panel="card">
                    <div class="popup_member_form popup_member_card_panel">
                        <p class="popup_member_lead">Xuất trình thẻ thành viên và theo dõi <strong>Zalo OA Wonom Culture Complex</strong> khi thanh toán để được nhận ưu đãi áp dụng tại thời điểm trực tiếp.</p>
                        <div class="popup_member_card" data-member-card>
                            <img class="popup_member_card_art" data-member-card-image src="<?php echo esc_url(get_template_directory_uri() . '/asset/img/card.webp'); ?>" alt="Mẫu thẻ thành viên WONOM">
                            <div class="popup_member_card_identity">
                                <strong data-member-name>THÀNH VIÊN WONOM</strong>
                                <span data-member-number>WN-000000</span>
                            </div>
                        </div>
                        <button class="popup_member_btn bg_ffce56 cl_text txt_extrabold btn" type="button" data-member-download>LƯU THẺ THÀNH VIÊN <span aria-hidden="true">⇩</span></button>
                    </div>
                    <div class="popup_member_switch">Chưa theo dõi Zalo OA Wonom? <a href="<?php echo esc_url($member_zalo_url); ?>" target="_blank" rel="noopener">Theo dõi ngay</a></div>
                </div>
            </div>

            <div class="popup_member_icon absolute img_full">
                <img src="<?php echo esc_url(wonom_get_home_image_url('home_member_popup_icon', '/asset/img/logo_team.webp')); ?>" alt="">
            </div>
        </div>

        <div class="popup_member_icon_right  img_full">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/asset/img/logo_team_right.webp'); ?>" alt="">
        </div>
    </div>

    <div class="popup_member_toast" role="status" aria-live="polite">
        <span class="popup_member_toast_icon">✓</span>
        <span><strong>ĐĂNG KÝ THÀNH CÔNG</strong><small>Chúc mừng bạn đã trở thành Wonom-er!</small></span>
        <button type="button" aria-label="Đóng thông báo">×</button>
    </div>
    <div class="popup_member_bg_bot"></div>
</section>
