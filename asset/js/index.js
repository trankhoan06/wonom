$(document).ready(function () {
    const parseRem = (input) => {
        return (input / 10) * parseFloat($("html").css("font-size"));
    };

    // Hide the fixed header while scrolling down and reveal it when scrolling up.
    if (window.gsap) {
        const header = document.querySelector(".header");
        const pageSections = Array.from(document.querySelectorAll(".pa_section"));
        let lastScrollY = window.scrollY;
        let headerHidden = false;

        const syncHeaderSection = () => {
            pageSections.forEach((section) => section.classList.remove("has-header"));
            if (headerHidden || !pageSections.length) return;

            const viewportMiddle = window.scrollY + (window.innerHeight / 2);
            const activeSection = pageSections.reduce((nearest, section) => {
                const sectionMiddle = section.offsetTop + (section.offsetHeight / 2);
                const nearestMiddle = nearest.offsetTop + (nearest.offsetHeight / 2);
                return Math.abs(sectionMiddle - viewportMiddle) < Math.abs(nearestMiddle - viewportMiddle)
                    ? section
                    : nearest;
            }, pageSections[0]);

            activeSection.classList.add("has-header");
        };

        const setHeaderVisibility = (hidden) => {
            if (!header) return;

            if (hidden === headerHidden) {
                syncHeaderSection();
                return;
            }

            headerHidden = hidden;

            gsap.to(header, {
                yPercent: hidden ? -110 : 0,
                duration: 0.35,
                ease: hidden ? "power2.in" : "power2.out",
                overwrite: "auto"
            });

            syncHeaderSection();
        };

        window.addEventListener("scroll", function () {
            const currentScrollY = Math.max(window.scrollY, 0);
            const scrollDistance = currentScrollY - lastScrollY;

            if (currentScrollY <= 10 || document.querySelector(".header_menu.active")) {
                setHeaderVisibility(false);
                lastScrollY = currentScrollY;
                return;
            }

            // Ignore tiny movements to keep the header from flickering on a trackpad.
            if (Math.abs(scrollDistance) < 6) return;

            setHeaderVisibility(scrollDistance > 0);
            lastScrollY = currentScrollY;
        }, { passive: true });

        syncHeaderSection();
    }

    // Full-page navigation: one wheel/key gesture moves exactly one section.
    // Kept desktop-only because the tablet/mobile layout uses natural heights.
    if (window.gsap && window.ScrollToPlugin) {
        gsap.registerPlugin(ScrollToPlugin);

        const fullpageMatchMedia = gsap.matchMedia();

        fullpageMatchMedia.add("(min-width: 992px)", function () {
            const sections = gsap.utils.toArray(".pa_section");
            const html = document.documentElement;
            const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
            let currentIndex = 0;
            let isAnimating = false;
            let touchStartY = 0;
            let scrollTween = null;
            let wheelGestureActive = false;
            let wheelIdleTimer = null;
            let wheelDelta = 0;

            const popupIsOpen = () => document.querySelector(
                ".popup_tour.active, .popup_form.active, .popup_member.active"
            );

            const nearestSectionIndex = () => {
                const viewportMiddle = window.scrollY + (window.innerHeight / 2);
                let nearest = 0;
                let nearestDistance = Infinity;

                sections.forEach((section, index) => {
                    const middle = section.offsetTop + (section.offsetHeight / 2);
                    const distance = Math.abs(middle - viewportMiddle);
                    if (distance < nearestDistance) {
                        nearest = index;
                        nearestDistance = distance;
                    }
                });

                return nearest;
            };

            const updateHash = (section) => {
                const nextHash = section.id ? `#${section.id}` : window.location.pathname;
                window.history.replaceState(null, "", nextHash);
            };

            const goToSection = (nextIndex) => {
                if (isAnimating || popupIsOpen()) return;

                const clampedIndex = gsap.utils.clamp(0, sections.length - 1, nextIndex);
                if (clampedIndex === currentIndex) return;

                const nextSection = sections[clampedIndex];
                isAnimating = true;

                scrollTween = gsap.to(window, {
                    scrollTo: { y: nextSection, autoKill: false },
                    duration: reduceMotion ? 0.2 : 0.78,
                    ease: "power3.inOut",
                    onComplete: function () {
                        currentIndex = clampedIndex;
                        isAnimating = false;
                        scrollTween = null;
                        updateHash(nextSection);
                    }
                });
            };

            const onWheel = (event) => {
                if (popupIsOpen()) return;
                event.preventDefault();

                // Trackpads often emit many very small deltas. Accumulate them
                // so a light gesture is enough, while still allowing only one
                // section change until that gesture has fully ended.
                const deltaMultiplier = event.deltaMode === 1 ? 16 : (event.deltaMode === 2 ? window.innerHeight : 1);
                wheelDelta += event.deltaY * deltaMultiplier;

                window.clearTimeout(wheelIdleTimer);
                wheelIdleTimer = window.setTimeout(function () {
                    wheelGestureActive = false;
                    wheelDelta = 0;
                }, 140);

                if (Math.abs(wheelDelta) < 3) return;
                if (wheelGestureActive) return;
                wheelGestureActive = true;
                const direction = wheelDelta > 0 ? 1 : -1;
                goToSection(currentIndex + direction);
            };

            const onKeydown = (event) => {
                if (event.repeat || popupIsOpen() || /INPUT|TEXTAREA|SELECT/.test(event.target.tagName)) return;

                const nextKeys = ["ArrowDown", "PageDown", "Space"];
                const previousKeys = ["ArrowUp", "PageUp"];

                if (nextKeys.includes(event.code) || nextKeys.includes(event.key)) {
                    event.preventDefault();
                    goToSection(currentIndex + 1);
                } else if (previousKeys.includes(event.code) || previousKeys.includes(event.key)) {
                    event.preventDefault();
                    goToSection(currentIndex - 1);
                } else if (event.key === "Home") {
                    event.preventDefault();
                    goToSection(0);
                } else if (event.key === "End") {
                    event.preventDefault();
                    goToSection(sections.length - 1);
                }
            };

            const onTouchStart = (event) => {
                touchStartY = event.changedTouches[0].clientY;
            };

            const onTouchEnd = (event) => {
                if (popupIsOpen()) return;
                const distance = touchStartY - event.changedTouches[0].clientY;
                if (Math.abs(distance) < 50) return;
                goToSection(currentIndex + (distance > 0 ? 1 : -1));
            };

            const onNavClick = (event) => {
                const href = event.currentTarget.getAttribute("href");
                if (href === "#") return;
                const targetId = href === "/#top" || href === "#top" ? null : href.slice(1);
                const nextIndex = targetId
                    ? sections.findIndex((section) => section.id === targetId)
                    : 0;

                if (nextIndex < 0) return;
                event.preventDefault();
                goToSection(nextIndex);
            };

            const onScroll = () => {
                if (!isAnimating) currentIndex = nearestSectionIndex();
            };

            html.classList.add("fullpage-scroll");
            currentIndex = nearestSectionIndex();

            window.addEventListener("wheel", onWheel, { passive: false });
            window.addEventListener("keydown", onKeydown);
            window.addEventListener("touchstart", onTouchStart, { passive: true });
            window.addEventListener("touchend", onTouchEnd, { passive: true });
            window.addEventListener("scroll", onScroll, { passive: true });

            const navLinks = document.querySelectorAll('.header-nav a[href^="#"], a[href="/#top"], a[href="#top"]');
            navLinks.forEach((link) => link.addEventListener("click", onNavClick));

            return function () {
                if (scrollTween) scrollTween.kill();
                window.clearTimeout(wheelIdleTimer);
                html.classList.remove("fullpage-scroll");
                window.removeEventListener("wheel", onWheel);
                window.removeEventListener("keydown", onKeydown);
                window.removeEventListener("touchstart", onTouchStart);
                window.removeEventListener("touchend", onTouchEnd);
                window.removeEventListener("scroll", onScroll);
                navLinks.forEach((link) => link.removeEventListener("click", onNavClick));
            };
        });
    }
    $('#langSelectorBtn').on('click', function (e) {
        e.stopPropagation();
        $('#langWrapper').toggleClass('active');
    });

    // Toggle hamburger menu
    $('.header_menu').on('click', function (e) {
        $(this).toggleClass('active');
        $('.header-nav').toggleClass('active');
        $('.header_overlay').toggleClass('active');
    });

    // Close hamburger menu when clicking overlay
    $('.header_overlay').on('click', function () {
        $('.header_menu').removeClass('active');
        $('.header-nav').removeClass('active');
        $(this).removeClass('active');
    });

    // Tab logic cho phần Khám Phá 5 Tầng (Explore)
    $('.home_explore_sidebar_tab_item').on('click', function () {
        if ($(this).hasClass('active')) return;

        var oldIndex = $('.home_explore_sidebar_tab_item.active').index();
        var newIndex = $(this).index();
        var targetTab = $(this).attr('data-tab');

        // Đổi trạng thái tab sidebar
        $('.home_explore_sidebar_tab_item').removeClass('active');
        $(this).addClass('active');

        // Đổi trạng thái nội dung (wrap)
        $('.home_explore_content_tab').each(function (index) {
            if (index < newIndex) {
                // Các tab bên trên -> thêm remove
                $(this).removeClass('active').addClass('remove');
            } else if (index === newIndex) {
                // Tab được chọn -> thêm active
                $(this).removeClass('remove').addClass('active');
            } else {
                // Các tab bên dưới -> bỏ hết remove và active
                $(this).removeClass('active remove');
            }
        });
    });

    // Khởi tạo tab đầu tiên nếu chưa có
    if ($('.home_explore_content_tab.active').length === 0) {
        $('.home_explore_content_tab[data-tab="tab1f"]').addClass('active');
    }


    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#langWrapper').length) {
            $('#langWrapper').removeClass('active');
        }
    });
    const heroImageTransition = window.WebGLImageTransition
        ? new WebGLImageTransition({
            container: document.querySelector('.home_banner'),
            images: Array.from(document.querySelectorAll('.home_banner_image_item img')).map((image) => image.currentSrc || image.src),
            duration: 850,
        })
        : null;

    const syncHomeBannerImage = (bannerSwiper) => {
        $('.home_banner_image_item').each(function (index) {
            $(this).toggleClass('active', index === bannerSwiper.realIndex);
        });
        if (heroImageTransition) heroImageTransition.goTo(bannerSwiper.realIndex);
    };

    const homeBannerTextLines = new WeakMap();

    if (window.SplitText) {
        gsap.registerPlugin(SplitText);

        $('.home_banner_item .swiper-slide-txt').each(function (index, element) {
            SplitText.create(element, {
                type: 'lines',
                mask: 'lines',
                linesClass: 'home_banner_text_line',
                autoSplit: true,
                onSplit: function (split) {
                    homeBannerTextLines.set(element, split.lines);

                    const slide = element.closest('.home_banner_item');
                    const isActive = slide.classList.contains('swiper-slide-active') || (!swiper && index === 0);
                    gsap.set(split.lines, { yPercent: isActive ? 0 : 100 });
                },
            });
        });
    }

    const animateHomeBannerText = (bannerSwiper) => {
        if (!window.SplitText) return;

        const activeSlide = bannerSwiper.slides[bannerSwiper.activeIndex];
        const previousSlide = bannerSwiper.slides[bannerSwiper.previousIndex];
        if (!activeSlide || !previousSlide || activeSlide === previousSlide) return;

        const activeText = activeSlide.querySelector('.swiper-slide-txt');
        const previousText = previousSlide.querySelector('.swiper-slide-txt');
        const activeLines = activeText
            ? homeBannerTextLines.get(activeText) || Array.from(activeText.querySelectorAll('.home_banner_text_line'))
            : [];
        const previousLines = previousText
            ? homeBannerTextLines.get(previousText) || Array.from(previousText.querySelectorAll('.home_banner_text_line'))
            : [];

        gsap.killTweensOf([...activeLines, ...previousLines]);
        gsap.timeline()
            .set(activeLines, { yPercent: 100 }, 0)
            .to(previousLines, {
                yPercent: -100,
                duration: 0.65,
                stagger: 0.07,
                ease: 'power3.inOut',
            }, 0)
            .to(activeLines, {
                yPercent: 0,
                duration: 0.75,
                stagger: 0.07,
                ease: 'power3.out',
            }, 0.14);
    };

    var swiper = new Swiper('.mySwiper', {
        loop: true,
        effect: 'fade',
        fadeEffect: {
            crossFade: true,
        },
        speed: 850,
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
        on: {
            init: syncHomeBannerImage,
            slideChange: syncHomeBannerImage,
            slideChangeTransitionStart: animateHomeBannerText,
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
