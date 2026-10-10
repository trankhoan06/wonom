<?php

/**
 * Fixed homepage interface strings.
 *
 * Marketing content remains editable in Homepage Settings. This dictionary is
 * intentionally limited to navigation, controls, forms, labels and notices.
 */

function wonom_home_current_language()
{
    if (isset($_POST['lang'])) {
        $posted_language = sanitize_key(wp_unslash($_POST['lang']));
        if (in_array($posted_language, array('vi', 'en', 'ko'), true)) {
            return $posted_language;
        }
    }

    if (function_exists('pll_current_language')) {
        $language = pll_current_language('slug');
        if ($language) {
            return sanitize_key($language);
        }
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $language = strtolower(substr((string) $locale, 0, 2));

    return in_array($language, array('vi', 'en', 'ko'), true) ? $language : 'vi';
}

function wonom_home_ui_dictionary()
{
    static $dictionary = null;
    if (null !== $dictionary) {
        return $dictionary;
    }

    $dictionary = array(
        'nav_intro' => array('vi' => 'GIỚI THIỆU', 'en' => 'ABOUT', 'ko' => '소개'),
        'nav_experience' => array('vi' => 'TRẢI NGHIỆM', 'en' => 'EXPERIENCES', 'ko' => '체험'),
        'nav_explore' => array('vi' => 'KHÁM PHÁ 5 TẦNG', 'en' => 'EXPLORE 5 FLOORS', 'ko' => '5개 층 둘러보기'),
        'nav_tour' => array('vi' => 'THAM QUAN', 'en' => 'TOURS', 'ko' => '투어'),
        'nav_event' => array('vi' => 'SỰ KIỆN', 'en' => 'EVENTS', 'ko' => '이벤트'),
        'photo_gallery' => array('vi' => 'THƯ VIỆN ẢNH', 'en' => 'PHOTO GALLERY', 'ko' => '포토 갤러리'),
        'nav_contact' => array('vi' => 'LIÊN HỆ', 'en' => 'CONTACT', 'ko' => '문의'),
        'contact_now' => array('vi' => 'LIÊN HỆ TƯ VẤN NGAY', 'en' => 'CONTACT US NOW', 'ko' => '지금 상담 문의'),
        'book_now' => array('vi' => 'ĐẶT CHỖ', 'en' => 'BOOK NOW', 'ko' => '예약하기'),
        'membership' => array('vi' => 'THÀNH VIÊN', 'en' => 'MEMBERSHIP', 'ko' => '멤버십'),
        'send_booking' => array('vi' => 'GỬI YÊU CẦU ĐẶT LỊCH', 'en' => 'SEND BOOKING REQUEST', 'ko' => '예약 요청 보내기'),
        'send_reservation' => array('vi' => 'GỬI YÊU CẦU ĐẶT CHỖ', 'en' => 'SEND RESERVATION REQUEST', 'ko' => '좌석 예약 요청 보내기'),
        'online_booking' => array('vi' => 'ĐẶT CHỖ ONLINE', 'en' => 'ONLINE RESERVATION', 'ko' => '온라인 예약'),
        'close_booking' => array('vi' => 'Đóng form đặt chỗ', 'en' => 'Close reservation form', 'ko' => '예약 양식 닫기'),
        'full_name' => array('vi' => 'HỌ VÀ TÊN', 'en' => 'FULL NAME', 'ko' => '성함'),
        'enter_name' => array('vi' => 'Nhập tên của bạn', 'en' => 'Enter your name', 'ko' => '이름을 입력하세요'),
        'phone' => array('vi' => 'SỐ ĐIỆN THOẠI', 'en' => 'PHONE NUMBER', 'ko' => '전화번호'),
        'enter_phone' => array('vi' => 'Nhập số điện thoại', 'en' => 'Enter your phone number', 'ko' => '전화번호를 입력하세요'),
        'which_floor' => array('vi' => 'BẠN MUỐN ĐẾN TẦNG NÀO?', 'en' => 'WHICH FLOOR WOULD YOU LIKE TO VISIT?', 'ko' => '어느 층을 방문하시겠어요?'),
        'select_floor' => array('vi' => 'Chọn tầng', 'en' => 'Select a floor', 'ko' => '층 선택'),
        'floor_1' => array('vi' => 'Tầng 1 - Nhà hàng Mujige', 'en' => '1F - Mujige Restaurant', 'ko' => '1층 - 무지개 레스토랑'),
        'floor_2' => array('vi' => 'Tầng 2 - Trang phục truyền thống', 'en' => '2F - Traditional Costume Studio', 'ko' => '2층 - 전통 의상 스튜디오'),
        'floor_3' => array('vi' => 'Tầng 3 - Workshop', 'en' => '3F - Workshop', 'ko' => '3층 - 공방'),
        'floor_4' => array('vi' => 'Tầng 4 - Stress Room', 'en' => '4F - Stress Room', 'ko' => '4층 - 스트레스 룸'),
        'floor_5' => array('vi' => 'Tầng 5 - Rooftop Disco', 'en' => '5F - Rooftop Disco', 'ko' => '5층 - 루프탑 디스코'),
        'guest_count' => array('vi' => 'SỐ NGƯỜI', 'en' => 'NUMBER OF GUESTS', 'ko' => '방문 인원'),
        'select_guests' => array('vi' => 'Chọn số người đến', 'en' => 'Select number of guests', 'ko' => '방문 인원 선택'),
        'one_guest' => array('vi' => '1 người', 'en' => '1 guest', 'ko' => '1명'),
        'two_guests' => array('vi' => '2 người', 'en' => '2 guests', 'ko' => '2명'),
        'three_guests' => array('vi' => '3 người', 'en' => '3 guests', 'ko' => '3명'),
        'four_guests' => array('vi' => '4 người', 'en' => '4 guests', 'ko' => '4명'),
        'five_guests' => array('vi' => '5+ người', 'en' => '5+ guests', 'ko' => '5명 이상'),
        'select_date' => array('vi' => 'CHỌN NGÀY', 'en' => 'SELECT DATE', 'ko' => '날짜 선택'),
        'arrival_time' => array('vi' => 'THỜI GIAN ĐẾN', 'en' => 'ARRIVAL TIME', 'ko' => '방문 시간'),
        'or_call' => array('vi' => 'HOẶC GỌI HỖ TRỢ', 'en' => 'OR CALL FOR SUPPORT', 'ko' => '또는 전화 문의'),
        'register_member' => array('vi' => 'ĐĂNG KÝ THÀNH VIÊN', 'en' => 'JOIN WONOM', 'ko' => '회원 가입'),
        'member_intro_before' => array('vi' => 'Trở thành thành viên WONOM để nhận', 'en' => 'Become a WONOM member and enjoy', 'ko' => 'WONOM 회원이 되어'),
        'member_discount' => array('vi' => 'ưu đãi giảm giá', 'en' => 'exclusive discounts', 'ko' => '특별 할인 혜택을 받아보세요'),
        'member_intro_after' => array('vi' => 'khi tới trải nghiệm. Chỉ cần số điện thoại – xuất trình tại quầy khi thanh toán.', 'en' => 'during your visit. Simply provide your phone number at the counter when paying.', 'ko' => '방문 시 혜택을 누리세요. 결제할 때 카운터에서 전화번호만 제시하면 됩니다.'),
        'consent_before' => array('vi' => 'Tôi đồng ý cho', 'en' => 'I agree that', 'ko' => '회원 혜택 안내를 위해'),
        'consent_after' => array('vi' => 'lưu số điện thoại để nhận ưu đãi thành viên (theo Nghị định 13/2023 về bảo vệ dữ liệu cá nhân).', 'en' => 'may save my phone number to send member offers (in accordance with Decree 13/2023 on personal data protection).', 'ko' => '이 전화번호를 저장하는 데 동의합니다(개인정보 보호에 관한 시행령 13/2023 준수).'),
        'existing_member' => array('vi' => 'Đã là thành viên Wonom!', 'en' => 'Already a Wonom member?', 'ko' => '이미 Wonom 회원이신가요?'),
        'find_qr' => array('vi' => 'Tìm lại thẻ QR', 'en' => 'Retrieve your QR card', 'ko' => 'QR 카드 찾기'),
        'back_home' => array('vi' => 'Về Trang chủ', 'en' => 'Back to Home', 'ko' => '홈으로'),
        'visit' => array('vi' => 'GHÉ THĂM', 'en' => 'VISIT US', 'ko' => '오시는 길'),
        'call_hotline' => array('vi' => 'GỌI HOTLINE TƯ VẤN', 'en' => 'CALL OUR HOTLINE', 'ko' => '전화 상담'),
        'or' => array('vi' => 'HOẶC', 'en' => 'OR', 'ko' => '또는'),
        'zalo_booking' => array('vi' => 'GỌI ZALO ĐẶT BÀN NGAY!!!', 'en' => 'BOOK NOW VIA ZALO', 'ko' => 'ZALO로 지금 예약하기'),
        'follow_us' => array('vi' => 'Theo dõi Wonom trên:', 'en' => 'Follow Wonom on:', 'ko' => 'Wonom 팔로우:'),
        'policies' => array('vi' => 'CHÍNH SÁCH & ĐIỀU KHOẢN', 'en' => 'POLICIES & TERMS', 'ko' => '정책 및 이용약관'),
        'privacy' => array('vi' => 'Chính sách bảo mật', 'en' => 'Privacy Policy', 'ko' => '개인정보 처리방침'),
        'audio_notice' => array('vi' => 'Nhấn vào trang để bật âm thanh piano', 'en' => 'Click anywhere to enable piano sound', 'ko' => '화면을 눌러 피아노 소리를 켜세요'),
        'close_notice' => array('vi' => 'Đóng thông báo', 'en' => 'Close notification', 'ko' => '알림 닫기'),
        'view_all_images' => array('vi' => 'XEM TẤT CẢ HÌNH ẢNH', 'en' => 'VIEW ALL PHOTOS', 'ko' => '모든 사진 보기'),
        'view_menu' => array('vi' => 'XEM THỰC ĐƠN', 'en' => 'VIEW MENU', 'ko' => '메뉴 보기'),
        'price_list' => array('vi' => 'BẢNG GIÁ', 'en' => 'PRICE LIST', 'ko' => '가격표'),
        'view_price_list' => array('vi' => 'XEM BẢNG GIÁ', 'en' => 'VIEW PRICES', 'ko' => '가격 보기'),
        'reserve_table' => array('vi' => 'ĐẶT BÀN GIỮ CHỖ', 'en' => 'RESERVE A TABLE', 'ko' => '테이블 예약'),
        'book_rental' => array('vi' => 'ĐẶT LỊCH THUÊ', 'en' => 'BOOK A RENTAL', 'ko' => '대여 예약'),
        'explore_now' => array('vi' => 'KHÁM PHÁ NGAY', 'en' => 'EXPLORE NOW', 'ko' => '지금 둘러보기'),
        'book_appointment' => array('vi' => 'ĐẶT LỊCH HẸN', 'en' => 'BOOK AN APPOINTMENT', 'ko' => '방문 예약'),
        'quick_booking' => array('vi' => 'ĐẶT CHỖ NHANH', 'en' => 'BOOK NOW', 'ko' => '빠른 예약'),
        'online_table_booking' => array('vi' => 'ĐẶT BÀN ONLINE', 'en' => 'BOOK A TABLE ONLINE', 'ko' => '온라인 테이블 예약'),
        'workshop_booking' => array('vi' => 'ĐẶT LỊCH WORKSHOP', 'en' => 'BOOK A WORKSHOP', 'ko' => '공방 예약'),
        'opening_hours' => array('vi' => 'GIỜ MỞ CỬA:', 'en' => 'OPENING HOURS:', 'ko' => '운영 시간:'),
        'rental_duration' => array('vi' => 'GIỜ THUÊ:', 'en' => 'RENTAL DURATION:', 'ko' => '대여 시간:'),
        'format' => array('vi' => 'HÌNH THỨC', 'en' => 'FORMAT', 'ko' => '이용 형태'),
        'rules' => array('vi' => 'QUY ĐỊNH', 'en' => 'RULES', 'ko' => '이용 규정'),
        'quantity' => array('vi' => 'SỐ LƯỢNG', 'en' => 'QUANTITY', 'ko' => '수량'),
        'current_floor' => array('vi' => 'Bạn đang ở tầng...', 'en' => 'You are currently on floor...', 'ko' => '현재 위치한 층...'),
        'all' => array('vi' => 'TẤT CẢ', 'en' => 'ALL', 'ko' => '전체'),
        'tour_title' => array('vi' => 'TOUR THAM QUAN', 'en' => 'TOURS', 'ko' => '투어'),
        'tour_intro' => array('vi' => 'GIỚI THIỆU TOUR', 'en' => 'TOUR OVERVIEW', 'ko' => '투어 소개'),
        'itinerary' => array('vi' => 'LỊCH TRÌNH TRẢI NGHIỆM', 'en' => 'TOUR ITINERARY', 'ko' => '체험 일정'),
        'important_information' => array('vi' => 'THÔNG TIN CẦN LƯU Ý', 'en' => 'IMPORTANT INFORMATION', 'ko' => '유의 사항'),
        'booking_policy' => array('vi' => 'CHÍNH SÁCH ĐẶT LỊCH', 'en' => 'BOOKING POLICY', 'ko' => '예약 정책'),
        'time' => array('vi' => 'THỜI GIAN', 'en' => 'DURATION', 'ko' => '소요 시간'),
        'cost' => array('vi' => 'CHI PHÍ', 'en' => 'PRICE', 'ko' => '가격'),
        'book_trip' => array('vi' => 'ĐẶT LỊCH CHUYẾN ĐI', 'en' => 'BOOK THIS TOUR', 'ko' => '투어 예약'),
        'other_tours' => array('vi' => 'GỢI Ý CÁC TOUR KHÁC', 'en' => 'OTHER RECOMMENDED TOURS', 'ko' => '다른 추천 투어'),
        'zalo_note' => array('vi' => 'Yêu cầu của bạn đã được ghi nhận. Nhân viên sẽ sớm liên hệ xác nhận.', 'en' => 'Your request has been received. Our team will contact you shortly to confirm.', 'ko' => '요청이 접수되었습니다. 담당자가 곧 연락하여 확인해 드리겠습니다.'),
        'ongoing_events' => array('vi' => 'SỰ KIỆN ĐANG DIỄN RA', 'en' => 'CURRENT EVENTS', 'ko' => '진행 중인 이벤트'),
        'apply' => array('vi' => 'ÁP DỤNG', 'en' => 'AVAILABILITY', 'ko' => '적용 기간'),
        'category' => array('vi' => 'DANH MỤC', 'en' => 'CATEGORY', 'ko' => '카테고리'),
        'condition' => array('vi' => 'ĐIỀU KIỆN', 'en' => 'CONDITIONS', 'ko' => '조건'),
        'other_events' => array('vi' => 'GỢI Ý CÁC SỰ KIỆN KHÁC', 'en' => 'OTHER EVENTS', 'ko' => '다른 이벤트'),
        'menu' => array('vi' => 'THỰC ĐƠN', 'en' => 'MENU', 'ko' => '메뉴'),
        'floor_content' => array('vi' => 'Nội dung tầng', 'en' => 'Floor content', 'ko' => '층 콘텐츠'),
        'menu_pages' => array('vi' => 'Danh sách trang thực đơn', 'en' => 'Menu pages', 'ko' => '메뉴 페이지 목록'),
        'previous_menu_page' => array('vi' => 'Trang thực đơn trước', 'en' => 'Previous menu page', 'ko' => '이전 메뉴 페이지'),
        'next_menu_page' => array('vi' => 'Trang thực đơn tiếp theo', 'en' => 'Next menu page', 'ko' => '다음 메뉴 페이지'),
        'workshop_at_wonom' => array('vi' => 'WORKSHOP TẠI WONOM', 'en' => 'WORKSHOP AT WONOM', 'ko' => 'WONOM 공방'),
        'tools_included' => array('vi' => 'DỤNG CỤ BAO GỒM', 'en' => 'MATERIALS INCLUDED', 'ko' => '포함 재료'),
        'product_price' => array('vi' => 'GIÁ SẢN PHẨM', 'en' => 'PRICE', 'ko' => '가격'),
        'book_via_zalo' => array('vi' => 'ĐẶT LỊCH ONLINE', 'en' => 'BOOK ONLINE', 'ko' => '온라인 예약'),
        'previous_image' => array('vi' => 'Ảnh trước', 'en' => 'Previous image', 'ko' => '이전 이미지'),
        'next_image' => array('vi' => 'Ảnh tiếp theo', 'en' => 'Next image', 'ko' => '다음 이미지'),
        'close_workshop' => array('vi' => 'Đóng chi tiết workshop', 'en' => 'Close workshop details', 'ko' => '공방 상세 닫기'),
        'invalid_name' => array('vi' => 'Vui lòng nhập họ tên hợp lệ.', 'en' => 'Please enter a valid full name.', 'ko' => '올바른 성함을 입력해 주세요.'),
        'invalid_phone' => array('vi' => 'Vui lòng nhập đúng số điện thoại Việt Nam.', 'en' => 'Please enter a valid Vietnamese phone number.', 'ko' => '올바른 베트남 전화번호를 입력해 주세요.'),
        'required_guests' => array('vi' => 'Vui lòng chọn số người.', 'en' => 'Please select the number of guests.', 'ko' => '방문 인원을 선택해 주세요.'),
        'invalid_date' => array('vi' => 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.', 'en' => 'Please select a valid date that is not earlier than today.', 'ko' => '오늘 이후의 올바른 날짜를 선택해 주세요.'),
        'invalid_time' => array('vi' => 'Vui lòng nhập thời gian theo định dạng HH:mm.', 'en' => 'Please enter the time in HH:mm format.', 'ko' => '시간을 HH:mm 형식으로 입력해 주세요.'),
        'invalid_time_short' => array('vi' => 'Nhập thời gian theo định dạng HH:mm.', 'en' => 'Enter the time in HH:mm format.', 'ko' => '시간을 HH:mm 형식으로 입력해 주세요.'),
        'required_floor' => array('vi' => 'Vui lòng chọn tầng bạn muốn đến.', 'en' => 'Please select the floor you would like to visit.', 'ko' => '방문할 층을 선택해 주세요.'),
        'required_consent' => array('vi' => 'Bạn cần đồng ý điều khoản để đăng ký.', 'en' => 'You must accept the terms to register.', 'ko' => '가입하려면 약관에 동의해 주세요.'),
        'check_fields' => array('vi' => 'Vui lòng kiểm tra lại các thông tin đã nhập.', 'en' => 'Please review the information you entered.', 'ko' => '입력한 정보를 다시 확인해 주세요.'),
        'check_information' => array('vi' => 'Vui lòng kiểm tra lại các thông tin.', 'en' => 'Please review your information.', 'ko' => '입력 정보를 다시 확인해 주세요.'),
        'submitting' => array('vi' => 'ĐANG GỬI...', 'en' => 'SENDING...', 'ko' => '전송 중...'),
        'cannot_send' => array('vi' => 'Không thể gửi yêu cầu. Vui lòng thử lại.', 'en' => 'Unable to send your request. Please try again.', 'ko' => '요청을 보내지 못했습니다. 다시 시도해 주세요.'),
        'cannot_connect' => array('vi' => 'Không thể kết nối máy chủ. Vui lòng thử lại.', 'en' => 'Unable to connect to the server. Please try again.', 'ko' => '서버에 연결할 수 없습니다. 다시 시도해 주세요.'),
        'expired' => array('vi' => 'Phiên gửi yêu cầu đã hết hạn. Vui lòng tải lại trang.', 'en' => 'Your session has expired. Please reload the page.', 'ko' => '세션이 만료되었습니다. 페이지를 새로고침해 주세요.'),
        'request_received' => array('vi' => 'Yêu cầu của bạn đã được ghi nhận.', 'en' => 'Your request has been received.', 'ko' => '요청이 접수되었습니다.'),
        'rate_limited' => array('vi' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.', 'en' => 'You have sent too many requests. Please try again later.', 'ko' => '요청을 너무 많이 보냈습니다. 잠시 후 다시 시도해 주세요.'),
        'invalid_form' => array('vi' => 'Loại biểu mẫu không hợp lệ.', 'en' => 'Invalid form type.', 'ko' => '올바르지 않은 양식 유형입니다.'),
        'invalid_content' => array('vi' => 'Loại nội dung không hợp lệ.', 'en' => 'Invalid content type.', 'ko' => '올바르지 않은 콘텐츠 유형입니다.'),
        'content_not_found' => array('vi' => 'Không tìm thấy nội dung.', 'en' => 'Content not found.', 'ko' => '콘텐츠를 찾을 수 없습니다.'),
        'save_failed' => array('vi' => 'Chưa thể lưu yêu cầu. Vui lòng thử lại.', 'en' => 'Unable to save your request. Please try again.', 'ko' => '요청을 저장하지 못했습니다. 다시 시도해 주세요.'),
        'success' => array('vi' => 'Gửi yêu cầu thành công! WONOM sẽ liên hệ với bạn trong thời gian sớm nhất.', 'en' => 'Request sent successfully! WONOM will contact you shortly.', 'ko' => '요청이 전송되었습니다! WONOM에서 곧 연락드리겠습니다.'),
        'view_details' => array('vi' => 'Xem chi tiết', 'en' => 'View details', 'ko' => '상세 보기'),
        'go_to_floor' => array('vi' => 'Đi đến tầng {number}', 'en' => 'Go to floor {number}', 'ko' => '{number}층으로 이동'),
        'tour_hover' => array('vi' => 'tham quan tour', 'en' => 'explore tours', 'ko' => '투어 보기'),
        'booking_short' => array('vi' => 'đặt chỗ', 'en' => 'book now', 'ko' => '예약하기'),
        'page' => array('vi' => 'Trang {number}', 'en' => 'Page {number}', 'ko' => '{number}페이지'),
        'floor_page_alt' => array('vi' => 'Nội dung tầng - trang {number}', 'en' => 'Floor content - page {number}', 'ko' => '층 콘텐츠 - {number}페이지'),
        'view_image' => array('vi' => 'Xem ảnh {number}', 'en' => 'View image {number}', 'ko' => '{number}번 이미지 보기'),
        'workshop_image' => array('vi' => 'Ảnh workshop {number}', 'en' => 'Workshop image {number}', 'ko' => '공방 이미지 {number}'),
    );

    return $dictionary;
}

function wonom_home_t($key, $fallback = '')
{
    $dictionary = wonom_home_ui_dictionary();
    $language = wonom_home_current_language();

    if (isset($dictionary[$key][$language])) {
        return $dictionary[$key][$language];
    }
    if (isset($dictionary[$key]['vi'])) {
        return $dictionary[$key]['vi'];
    }

    return $fallback;
}

function wonom_home_ui_js_strings()
{
    $keys = array(
        'invalid_name', 'invalid_phone', 'required_guests', 'invalid_date',
        'invalid_time_short', 'required_floor', 'required_consent', 'check_fields',
        'submitting', 'cannot_send', 'cannot_connect', 'book_via_zalo',
        'zalo_note', 'view_details', 'photo_gallery', 'go_to_floor', 'tour_hover',
        'booking_short', 'page', 'floor_page_alt', 'floor_content', 'view_image',
        'workshop_image',
    );
    $strings = array();
    foreach ($keys as $key) {
        $strings[$key] = wonom_home_t($key);
    }

    return $strings;
}

function wonom_translate_homepage_markup($html)
{
    $language = wonom_home_current_language();
    if ('vi' === $language || !is_string($html) || '' === $html) {
        return $html;
    }

    $translations = array();
    foreach (wonom_home_ui_dictionary() as $values) {
        if (!empty($values['vi']) && isset($values[$language])) {
            $translations[$values['vi']] = $values[$language];
        }
    }

    $normalize = static function ($value) {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    };

    $html = preg_replace_callback('/>([^<>]+)</u', static function ($matches) use ($translations, $normalize) {
        $source = $normalize($matches[1]);
        if ('' === $source || !isset($translations[$source])) {
            return $matches[0];
        }
        return '>' . esc_html($translations[$source]) . '<';
    }, $html);

    $attributes = 'placeholder|aria-label|title|data-booking-title|data-booking-subtitle|data-booking-submit';
    $html = preg_replace_callback('/\b(' . $attributes . ')=("|\')(.*?)\2/isu', static function ($matches) use ($translations, $normalize) {
        $source = $normalize($matches[3]);
        if ('' === $source || !isset($translations[$source])) {
            return $matches[0];
        }
        return $matches[1] . '=' . $matches[2] . esc_attr($translations[$source]) . $matches[2];
    }, $html);

    return $html;
}
