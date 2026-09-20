$(document).ready(function () {
    const parseRem = (input) => {
        return (input / 10) * parseFloat($("html").css("font-size"));
    };
    $('#langSelectorBtn').on('click', function (e) {
        e.stopPropagation();
        $('#langWrapper').toggleClass('active');
    });

    // Toggle hamburger menu
    $('.header_menu').on('click', function (e) {
        $(this).toggleClass('active');
        $('.header-nav').toggleClass('active');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#langWrapper').length) {
            $('#langWrapper').removeClass('active');
        }
    });
    var swiper = new Swiper('.mySwiper', {
        loop: true,
        // autoplay: {
        //     delay: 3000,
        //     disableOnInteraction: false,
        // },
        navigation: {
            nextEl: '.home_banner_button_next',
            prevEl: '.home_banner_button_prev',
        },
        pagination: {
            el: '.swiper-pagination',
        },
    });
    var swiper1 = new Swiper('.home_experience_inner', {
        slidesPerView: 'auto',
        spaceBetween: parseRem(48),
        loop: true,
        speed: 3000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },
    });
    var swiper2 = new Swiper('.home_explore_list', {
        slidesPerView: 'auto',
        spaceBetween: parseRem(24),
        on: {
            init: function (swiper) {
                if (swiper.slides.length > 0) {
                    var offset = swiper.wrapperEl.clientWidth - swiper.slides[0].clientWidth;
                    swiper.params.slidesOffsetAfter = offset;
                    swiper.update();
                }
            },
            resize: function (swiper) {
                if (swiper.slides.length > 0) {
                    var offset = swiper.wrapperEl.clientWidth - swiper.slides[0].clientWidth;
                    swiper.params.slidesOffsetAfter = offset;
                    swiper.update();
                }
            }
        },
        navigation: {
            nextEl: '.home_explore_list_button_next',
            prevEl: '.home_explore_list_button_prev',
        },
    });

    var swiper3 = new Swiper('.home_event_card', {
        slidesPerView: 3,
        spaceBetween: parseRem(24),
        navigation: {
            nextEl: '.home_event_card_item_button_next',
            prevEl: '.home_event_card_item_button_prev',
        },
    });

    var swiper4 = new Swiper('.home_space_right_card.card1', {
        direction: 'vertical',
        slidesPerView: 1.8,
        spaceBetween: parseRem(24),
        mousewheel: false,
        loop: true,
        speed: 5000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },
    });
    var swiper5 = new Swiper('.home_space_right_card.card2', {
        direction: 'vertical',
        slidesPerView: 1.8,
        spaceBetween: parseRem(24),
        mousewheel: false,
        loop: true,
        speed: 5000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            reverseDirection: true,
        },
    });

    $('.global_btn_list_item').hover(
        function () {
            var text = '';
            var $txtEl = null;
            if ($(this).hasClass('item1')) {
                text = 'tham quan tour';
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold txt_uppercase">' + text + '</div>');
                $(this).append($txtEl);
            } else if ($(this).hasClass('item2')) {
                text = 'đặt chỗ';
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold cl_cream txt_uppercase">' + text + '</div>');
                $(this).append($txtEl);
            } else if ($(this).hasClass('item3')) {
                text = '0968 487 096';
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold cl_cream txt_uppercase">' + text + '</div>');
                $(this).append($txtEl);
            }

            // Dùng setTimeout cực ngắn để CSS kịp nhận thẻ mới trước khi thêm class active (kích hoạt mượt mà)
            if ($txtEl) {
                setTimeout(function () {
                    $txtEl.addClass('active');
                }, 10);
            }
        },
        function () {
            var $txtEl = $(this).find('.global_btn_list_item_txt');
            $txtEl.removeClass('active');
            // Chờ 300ms (bằng thời gian transition) rồi mới xóa thẻ
            setTimeout(function () {
                $txtEl.remove();
            }, 300);
        }
    );

    // Xử lý đóng/mở popup_tour.tour
    $('.home_tour_card_detail_see ').click(function () {
        $('.popup_tour.tour').addClass('active');
    });

    $('.popup_tour_close').click(function () {
        $('.popup_tour.tour').removeClass('active');
    });
    $('.footer_bot_right_txt').click(function () {
        $('.popup_tour.policy').addClass('active');
    });

    $('.policy .popup_tour_close').click(function () {
        $('.popup_tour.policy').removeClass('active');
    });
    $('.home_space_left_button ').click(function () {
        $('.popup_tour.space').addClass('active');
    });

    $('.space .popup_tour_close').click(function () {
        $('.popup_tour.space').removeClass('active');
    });
    $('.home_explore_seeall').click(function () {
        $('.popup_tour.restaurant').addClass('active');
    });

    $('.restaurant .popup_tour_close').click(function () {
        $('.popup_tour.restaurant').removeClass('active');
    });
    $('.header_button').click(function () {
        $('.popup_form').addClass('active');
    });

    $('.popup_form .popup_tour_close').click(function () {
        $('.popup_form').removeClass('active');
    });
    $('.popup_form_overlay').click(function () {
        $('.popup_form').removeClass('active');
    });

    // popup_member close
    $('.popup_member_back').click(function () {
        $('.popup_member').removeClass('active');
    });
    $('.header_button_member').click(function () {
        $('.popup_member').addClass('active');
    });
});
