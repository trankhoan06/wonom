$(document).ready(function () {
    const parseRem = (input) => {
        return (input / 10) * parseFloat($("html").css("font-size"));
    };

    // Keep the page behind an open popup fixed while allowing each popup's
    // own scrollable content to keep working.
    const popupSelector = '.popup_tour.active, .popup_form.active, .popup_member.active, .workshop_detail_popup.active';
    const body = document.body;
    let pageScrollLocked = false;
    let lockedScrollY = 0;
    let previousBodyStyle = null;

    const lockPageScroll = () => {
        if (pageScrollLocked) return;

        lockedScrollY = window.scrollY;
        previousBodyStyle = {
            position: body.style.position,
            top: body.style.top,
            left: body.style.left,
            right: body.style.right,
            width: body.style.width,
            overflow: body.style.overflow
        };

        body.style.position = 'fixed';
        body.style.top = `-${lockedScrollY}px`;
        body.style.left = '0';
        body.style.right = '0';
        body.style.width = '100%';
        body.style.overflow = 'hidden';
        pageScrollLocked = true;
    };

    const unlockPageScroll = () => {
        if (!pageScrollLocked) return;

        Object.keys(previousBodyStyle).forEach((property) => {
            body.style[property] = previousBodyStyle[property];
        });
        pageScrollLocked = false;
        window.scrollTo(0, lockedScrollY);
    };

    const syncPageScrollLock = () => {
        if (document.querySelector(popupSelector)) {
            lockPageScroll();
        } else {
            unlockPageScroll();
        }
    };

    const popupStateObserver = new MutationObserver(syncPageScrollLock);
    document.querySelectorAll('.popup_tour, .popup_form, .popup_member, .workshop_detail_popup').forEach((popup) => {
        popupStateObserver.observe(popup, { attributes: true, attributeFilter: ['class'] });
    });
    syncPageScrollLock();

    // Hide the fixed header while scrolling down and reveal it when scrolling up.
    if (window.gsap) {
        const header = document.querySelector(".header");
        const pageSections = Array.from(document.querySelectorAll(".pa_section"));
        const desktopMedia = window.matchMedia("(min-width: 992px)");
        let lastScrollY = window.scrollY;
        let headerHidden = false;
        let sectionTransitioning = false;

        const syncHeaderSection = () => {
            // Keep the current section layout stable until the full-page
            // transition has actually reached the destination section.
            if (sectionTransitioning) return;

            pageSections.forEach((section) => section.classList.remove("has-header"));
            if (!desktopMedia.matches || headerHidden || !pageSections.length) return;

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

        window.addEventListener('wonom:section-transition-start', function (event) {
            sectionTransitioning = true;

            const detail = event.detail || {};
            const destinationSection = pageSections[detail.nextIndex];
            const destinationHasHeader = desktopMedia.matches && Boolean(detail.destinationHasHeader);

            // Prepare the destination's final layout before it moves into the
            // viewport. The current section keeps its class until commit.
            if (destinationSection) {
                destinationSection.classList.toggle('has-header', destinationHasHeader);
            }

            setHeaderVisibility(!destinationHasHeader);
        });

        window.addEventListener('wonom:section-transition-end', function () {
            sectionTransitioning = false;
            syncHeaderSection();
        });

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

        desktopMedia.addEventListener("change", syncHeaderSection);

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
            let nestedWheelElement = null;
            let touchScrollElement = null;
            let touchScrollStartTop = 0;

            const getCustomScrollElement = (target) => {
                return target instanceof Element ? target.closest("[custom-scroll]") : null;
            };

            const getScrollLimit = (element) => Math.max(element.scrollHeight - element.clientHeight, 0);

            const canScrollElement = (element, deltaY, scrollTop = element.scrollTop) => {
                const scrollLimit = getScrollLimit(element);
                if (scrollLimit <= 1 || deltaY === 0) return false;

                return deltaY > 0
                    ? scrollTop < scrollLimit - 1
                    : scrollTop > 1;
            };

            const resetWheelGestureAfterIdle = () => {
                window.clearTimeout(wheelIdleTimer);
                wheelIdleTimer = window.setTimeout(function () {
                    wheelGestureActive = false;
                    nestedWheelElement = null;
                    wheelDelta = 0;
                }, 140);
            };

            const popupIsOpen = () => document.querySelector(
                ".popup_tour.active, .popup_form.active, .popup_member.active, .workshop_detail_popup.active"
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
                const destinationHasHeader = clampedIndex < currentIndex
                    || Boolean(document.querySelector('.header_menu.active'));
                isAnimating = true;
                window.dispatchEvent(new CustomEvent('wonom:section-transition-start', {
                    detail: {
                        nextIndex: clampedIndex,
                        destinationHasHeader: destinationHasHeader
                    }
                }));

                scrollTween = gsap.to(window, {
                    scrollTo: { y: nextSection, autoKill: false },
                    duration: reduceMotion ? 0.2 : 0.78,
                    ease: "power3.inOut",
                    onComplete: function () {
                        currentIndex = clampedIndex;
                        isAnimating = false;
                        scrollTween = null;
                        updateHash(nextSection);
                        window.dispatchEvent(new CustomEvent('wonom:section-transition-end'));
                    }
                });
            };

            const onWheel = (event) => {
                if (popupIsOpen()) return;

                const customScrollElement = getCustomScrollElement(event.target);

                // Let an overflowing custom-scroll consume the whole wheel
                // gesture. Keeping it locked until the gesture ends prevents
                // trackpad momentum from unexpectedly changing sections when
                // the inner element reaches its boundary.
                if (customScrollElement && (
                    canScrollElement(customScrollElement, event.deltaY)
                    || nestedWheelElement === customScrollElement
                )) {
                    nestedWheelElement = customScrollElement;
                    wheelGestureActive = false;
                    wheelDelta = 0;
                    resetWheelGestureAfterIdle();
                    return;
                }

                event.preventDefault();

                // Trackpads often emit many very small deltas. Accumulate them
                // so a light gesture is enough, while still allowing only one
                // section change until that gesture has fully ended.
                const deltaMultiplier = event.deltaMode === 1 ? 16 : (event.deltaMode === 2 ? window.innerHeight : 1);
                wheelDelta += event.deltaY * deltaMultiplier;

                resetWheelGestureAfterIdle();

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
                const movesForward = nextKeys.includes(event.code) || nextKeys.includes(event.key);
                const movesBackward = previousKeys.includes(event.code) || previousKeys.includes(event.key);
                const customScrollElement = getCustomScrollElement(event.target);

                if (customScrollElement && (
                    (movesForward && canScrollElement(customScrollElement, 1))
                    || (movesBackward && canScrollElement(customScrollElement, -1))
                )) return;

                if (movesForward) {
                    event.preventDefault();
                    goToSection(currentIndex + 1);
                } else if (movesBackward) {
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
                touchScrollElement = getCustomScrollElement(event.target);
                touchScrollStartTop = touchScrollElement ? touchScrollElement.scrollTop : 0;
            };

            const onTouchEnd = (event) => {
                if (popupIsOpen()) return;
                const distance = touchStartY - event.changedTouches[0].clientY;
                if (Math.abs(distance) < 50) return;

                // If this swipe started while the nested element could scroll
                // in that direction, it belongs to that element even when the
                // swipe itself reaches the boundary.
                if (touchScrollElement && canScrollElement(
                    touchScrollElement,
                    distance,
                    touchScrollStartTop
                )) return;

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
                const transitionWasActive = isAnimating;
                if (scrollTween) scrollTween.kill();
                if (transitionWasActive) {
                    window.dispatchEvent(new CustomEvent('wonom:section-transition-end'));
                }
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
    let exploreTabTransitionTimer = null;
    const exploreMobileMedia = window.matchMedia('(max-width: 991px)');
    const $exploreContentTabs = $('.home_explore_content_tab');
    const $exploreMobileTabs = $('.home_explore_content_subtitle_wrap');

    function setExploreTabState(targetTab, animate) {
        var $targetContent = $exploreContentTabs.filter('[data-tab="' + targetTab + '"]');
        var oldIndex = $exploreContentTabs.index($exploreContentTabs.filter('.active').first());
        var newIndex = $exploreContentTabs.index($targetContent);
        if (newIndex < 0) return;

        var $transitionTabs = $targetContent;
        if (oldIndex >= 0) $transitionTabs = $transitionTabs.add($exploreContentTabs.eq(oldIndex));

        window.clearTimeout(exploreTabTransitionTimer);
        $exploreContentTabs.removeClass('is-transitioning');
        if (animate) $transitionTabs.addClass('is-transitioning');

        // Đổi trạng thái tab sidebar
        $('.home_explore_sidebar_tab_item').removeClass('active');
        $('.home_explore_sidebar_tab_item[data-tab="' + targetTab + '"]').addClass('active');

        // Đổi trạng thái nội dung (wrap)
        $exploreContentTabs.each(function (index) {
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

        if (animate) {
            exploreTabTransitionTimer = window.setTimeout(function () {
                $transitionTabs.removeClass('is-transitioning');
            }, 650);
        }
    }

    $('.home_explore_sidebar_tab_item').on('click', function () {
        if ($(this).hasClass('active')) return;
        setExploreTabState($(this).attr('data-tab'), true);
    });

    $exploreMobileTabs.on('click', function () {
        if (!exploreMobileMedia.matches) return;

        var $trigger = $(this);
        var targetTab = $trigger.attr('data-tab');
        var $targetContent = $exploreContentTabs.filter('[data-tab="' + targetTab + '"]');
        var isOpen = $trigger.hasClass('active');

        $targetContent.stop(true, true);

        if (isOpen) {
            $targetContent.slideUp(450, function () {
                $targetContent.removeClass('active remove');
            });
            $trigger.removeClass('active').attr('aria-expanded', 'false');
            return;
        }

        $('.home_explore_sidebar_tab_item').removeClass('active');
        $('.home_explore_sidebar_tab_item[data-tab="' + targetTab + '"]').addClass('active');
        $targetContent.removeClass('remove').addClass('active');
        $trigger.addClass('active').attr('aria-expanded', 'true');
        $targetContent.hide().slideDown(450);
    });

    // Khởi tạo tab đầu tiên nếu chưa có
    if ($exploreContentTabs.filter('.active').length === 0) {
        $exploreContentTabs.filter('[data-tab="tab1f"]').addClass('active');
    }

    function syncExploreMobileAccordion() {
        window.clearTimeout(exploreTabTransitionTimer);
        $exploreContentTabs.stop(true, true).removeAttr('style').removeClass('is-transitioning');

        var targetTab = $('.home_explore_sidebar_tab_item.active').attr('data-tab')
            || $exploreContentTabs.filter('.active').first().attr('data-tab')
            || 'tab1f';

        if (exploreMobileMedia.matches) {
            $exploreContentTabs.removeClass('remove').addClass('active').show();
            $exploreMobileTabs.addClass('active').attr('aria-expanded', 'true');
        } else {
            setExploreTabState(targetTab, false);
            $exploreMobileTabs.removeClass('active').attr('aria-expanded', 'false');
        }
    }

    exploreMobileMedia.addEventListener('change', syncExploreMobileAccordion);
    syncExploreMobileAccordion();


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
            duration: 1100,
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
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.home_banner_button_next',
            prevEl: '.home_banner_button_prev',
        },
        pagination: {
            el: '.home_banner_pagination',
            clickable: true,
        },
        on: {
            init: syncHomeBannerImage,
            slideChange: syncHomeBannerImage,
            slideChangeTransitionStart: animateHomeBannerText,
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
        slidesPerView: 1.07,
        spaceBetween: parseRem(24),
        breakpoints: {
            992: {
                slidesPerView: 3,
                spaceBetween: parseRem(24),
            },
            768: {
                slidesPerView: 2,
                spaceBetween: parseRem(24),
            },
        },
        navigation: {
            nextEl: '.home_event_card_item_button_next',
            prevEl: '.home_event_card_item_button_prev',
        },
    });

    var homeTourThumbs = new Swiper('.home_tour_card_slide', {
        slidesPerView: 'auto',
        spaceBetween: parseRem(12),
        freeMode: true,
        watchSlidesProgress: true,
        watchOverflow: true,
        slideToClickedSlide: true,
        observer: true,
        observeParents: true,
        grabCursor: true,
    });

    var homeTourImageTransition = window.WebGLImageTransition
        ? new WebGLImageTransition({
            container: document.querySelector('.home_tour_main'),
            images: Array.from(document.querySelectorAll('.home_tour_main_item img')).map(function (image) {
                return image.currentSrc || image.src;
            }),
            duration: 1100,
        })
        : null;

    var homeTourSwiper = new Swiper('.home_tour_main', {
        speed: 850,
        effect: 'fade',
        fadeEffect: {
            crossFade: true,
        },
        navigation: {
            nextEl: '.home_tour_card_detail_button_inner_next',
            prevEl: '.home_tour_card_detail_button_inner_prev',
        },
        thumbs: {
            swiper: homeTourThumbs,
        },
        on: {
            slideChange: function (swiper) {
                if (homeTourImageTransition) {
                    homeTourImageTransition.goTo(swiper.realIndex);
                }
            },
        },
    });

    var swiper4 = new Swiper('.home_space_right_card.card1', {
        direction: 'horizontal',
        slidesPerView: 1.2,
        spaceBetween: parseRem(24),
        mousewheel: false,
        loop: true,
        speed: 5000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },
        breakpoints: {
            768: {
                slidesPerView: 2.2,
                spaceBetween: parseRem(24),
            },
            992: {
                direction: 'vertical',
                slidesPerView: 2,
                spaceBetween: parseRem(24),
            },
        },
    });
    var swiper5 = new Swiper('.home_space_right_card.card2', {
        direction: 'horizontal',
        slidesPerView: 1.2,
        spaceBetween: parseRem(16),
        mousewheel: false,
        loop: true,
        speed: 5000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            reverseDirection: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2.2,
                spaceBetween: parseRem(24),
            },
            992: {
                direction: 'vertical',
                slidesPerView: 2,
                spaceBetween: parseRem(24),
            },
        },
    });

    function pauseAutoplayOutsideViewport(swiperInstance, selector) {
        var element = document.querySelector(selector);
        if (!element || !swiperInstance || !swiperInstance.autoplay || !('IntersectionObserver' in window)) return;

        var isInViewport = false;
        var syncAutoplay = function () {
            if (isInViewport && !document.hidden) {
                swiperInstance.autoplay.start();
            } else {
                swiperInstance.autoplay.stop();
            }
        };
        var observer = new IntersectionObserver(function (entries) {
            isInViewport = Boolean(entries[0] && entries[0].isIntersecting);
            syncAutoplay();
        }, { rootMargin: '150px 0px' });

        observer.observe(element);
        document.addEventListener('visibilitychange', syncAutoplay);
    }

    pauseAutoplayOutsideViewport(swiper4, '.home_space_right_card.card1');
    pauseAutoplayOutsideViewport(swiper5, '.home_space_right_card.card2');

    const globalTopButton = document.querySelector('.global_btn_top.btn');
    if (globalTopButton) {
        const syncGlobalTopButton = () => {
            const isVisible = window.scrollY > 10;
            globalTopButton.classList.toggle('is-visible', isVisible);
            globalTopButton.setAttribute('aria-hidden', String(!isVisible));
        };

        window.addEventListener('scroll', syncGlobalTopButton, { passive: true });
        syncGlobalTopButton();
    }

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

    $('.tour .popup_tour_close').click(function () {
        $('.popup_tour.tour').removeClass('active');
    });

    var eventPopupData = [
        {
            title: 'GIẢM 20% BAN NGÀY TRONG TUẦN',
            apply: 'Thường xuyên',
            category: 'Discount',
            condition: 'Là thành viên Wonom',
            image: '/asset/img/store.jpg',
            subtitle: 'Giảm giá trải nghiệm mọi tầng'
        },
        {
            title: 'PHÒNG CHỤP HANBOK THEO MÙA',
            apply: 'Theo mùa',
            category: 'Trải nghiệm',
            condition: 'Áp dụng theo lịch chương trình',
            image: '/asset/img/store.jpg',
            subtitle: 'Lưu giữ khoảnh khắc trong không gian đậm chất Hàn Quốc'
        },
        {
            title: 'ĐÊM ROOFTOP DISCO',
            apply: 'Cuối tuần',
            category: 'Rooftop',
            condition: 'Áp dụng tại tầng 5',
            image: '/asset/img/store.jpg',
            subtitle: 'Đêm nhạc và cocktail trên rooftop Wonom'
        },
        {
            title: 'PHÒNG CHỤP HANBOK THEO MÙA',
            apply: 'Theo mùa',
            category: 'Trải nghiệm',
            condition: 'Áp dụng theo lịch chương trình',
            image: '/asset/img/store.jpg',
            subtitle: 'Lưu giữ khoảnh khắc trong không gian đậm chất Hàn Quốc'
        }
    ];

    function renderEventPopupList() {
        $('.popup_tour.event .popup_tour_sidebar_suggest').html(eventPopupData.map(function (item, index) {
            return '<button class="popup_tour_sidebar_suggest_item" type="button" data-event-index="' + index + '">' +
                '<span class="popup_tour_sidebar_suggest_item_img img_full"><img src="' + item.image + '" alt=""></span>' +
                '<span class="popup_tour_sidebar_suggest_item_title txt_bold">' + item.title + '</span></button>';
        }).join(''));
    }

    function showEventPopupItem(index) {
        var item = eventPopupData[index] || eventPopupData[0];
        var $popup = $('.popup_tour.event');

        $popup.find('.event_popup_sidebar_card_title').text(item.title);
        $popup.find('.event_popup_apply').text(item.apply);
        $popup.find('.event_popup_category').text(item.category);
        $popup.find('.event_popup_condition').text(item.condition);
        $popup.find('.event_popup_content_img img').attr('src', item.image).attr('alt', item.title);
        $popup.find('.event_popup_content_title').text(item.title);
        $popup.find('.event_popup_content_subtitle').text(item.subtitle);
        $popup.find('[data-event-index]').removeClass('active')
            .filter('[data-event-index="' + index + '"]').addClass('active');
    }

    renderEventPopupList();

    $('.home_event_card_item').click(function () {
        var index = $(this).index();
        showEventPopupItem(index);
        $('.popup_tour.event').addClass('active').attr('aria-hidden', 'false');
    });

    $('.popup_tour.event').on('click', '[data-event-index]', function () {
        showEventPopupItem(Number($(this).attr('data-event-index')));
    });

    $('.event .popup_tour_close').click(function () {
        $('.popup_tour.event').removeClass('active').attr('aria-hidden', 'true');
    });
    $('.footer_bot_right_txt').click(function () {
        $('.popup_tour.policy').addClass('active');
    });

    $('.policy .popup_tour_close').click(function () {
        $('.popup_tour.policy').removeClass('active');
    });
    var restaurantPageFlip = null;
    var restaurantMenuPages = [];
    var activeExplorePopup = null;
    var exploreGalleryMobileMedia = window.matchMedia('(max-width: 767px)');
    var explorePopupData = {
        '1f': {
            title: '1F · NHÀ HÀNG MUJIGE',
            menuLabel: 'THỰC ĐƠN',
            menuPages: [
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp'
            ],
            galleryImages: [
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg'
            ]
        },
        '2f': {
            title: '2F · TRANG PHỤC TRUYỀN THỐNG',
            menuLabel: 'BẢNG GIÁ',
            menuPages: [
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp'
            ],
            galleryImages: [
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/store.jpg', '/asset/img/store.jpg', '/asset/img/store.jpg'
            ]
        },
        '3f': {
            title: '3F · WORKSHOP',
            menuLabel: '',
            menuPages: [],
            galleryFilters: [
                { value: 'all', label: 'TẤT CẢ' },
                { value: 'hat', label: 'NÓN' },
                { value: 'traditional', label: 'ÁO TRUYỀN THỐNG' },
                { value: 'accessories', label: 'PHỤ KIỆN' }
            ],
            galleryImages: [
                { src: '/asset/img/store.jpg', category: 'hat' },
                { src: '/asset/img/store.jpg', category: 'traditional' },
                { src: '/asset/img/store.jpg', category: 'accessories' },
                { src: '/asset/img/store.jpg', category: 'hat' },
                { src: '/asset/img/store.jpg', category: 'traditional' },
                { src: '/asset/img/store.jpg', category: 'accessories' },
                { src: '/asset/img/store.jpg', category: 'hat' },
                { src: '/asset/img/store.jpg', category: 'traditional' },
                { src: '/asset/img/store.jpg', category: 'accessories' },
                { src: '/asset/img/store.jpg', category: 'hat' },
                { src: '/asset/img/store.jpg', category: 'traditional' },
                { src: '/asset/img/store.jpg', category: 'accessories' }
            ]
        },
        '4f': {
            title: '4F · STRESS ROOM',
            menuLabel: 'BẢNG GIÁ',
            menuPages: [
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp'
            ],
            galleryImages: [
                '/asset/img/img_popup.webp', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/img_popup.webp', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/img_popup.webp', '/asset/img/store.jpg', '/asset/img/store.jpg',
                '/asset/img/img_popup.webp', '/asset/img/store.jpg', '/asset/img/store.jpg'
            ]
        },
        '5f': {
            title: '5F · ROOFTOP DISCO',
            menuLabel: 'MENU',
            menuPages: [
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp',
                '/asset/img/menu1.webp', '/asset/img/menu2.webp'
            ],
            galleryImages: [
                '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp', '/asset/img/store.jpg',
                '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp', '/asset/img/store.jpg',
                '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp', '/asset/img/store.jpg',
                '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp', '/asset/img/store.jpg'
            ]
        },
        'library': {
            title: 'THƯ VIỆN ẢNH',
            galleryOnly: true,
            menuLabel: '',
            menuPages: [],
            galleryImages: [
                '/asset/img/home_banner.webp', '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp',
                '/asset/img/store.jpg', '/asset/img/img_popup.webp', '/asset/img/home_banner.webp',
                '/asset/img/home-hero2.webp', '/asset/img/home-hero3.webp', '/asset/img/store.jpg',
                '/asset/img/img_popup.webp', '/asset/img/home_banner.webp', '/asset/img/store.jpg'
            ]
        }
    };

    function renderExplorePopupTabs(config) {
        var tabs;

        if (config.galleryOnly) {
            tabs = [];
        } else if (config.galleryFilters) {
            tabs = config.galleryFilters.map(function (filter, index) {
                return '<button class="restaurant_popup_tab gallery_filter_tab btn bg_white' + (index === 0 ? ' active' : '') + '"' +
                    ' type="button" role="tab" aria-selected="' + (index === 0 ? 'true' : 'false') + '"' +
                    ' data-gallery-filter="' + filter.value + '"><span class="btn-inner txt_13 txt_extrabold">' + filter.label + '</span></button>';
            });
        } else {
            tabs = [
                '<button class="restaurant_popup_tab btn bg_white" type="button" role="tab" aria-selected="false" data-restaurant-view="menu">' +
                '<span class="btn-inner restaurant_popup_menu_label txt_14 txt_extrabold">' + config.menuLabel + '</span></button>',
                '<button class="restaurant_popup_tab btn bg_white" type="button" role="tab" aria-selected="false" data-restaurant-view="gallery">' +
                '<span class="btn-inner txt_14 txt_extrabold">THƯ VIỆN ẢNH</span></button>'
            ];
        }

        $('.restaurant_popup_tabs').html(tabs.join(''));
    }

    function renderExploreMenuThumbs() {
        var $thumbs = $('.restaurant_menu_thumbs').empty();

        restaurantMenuPages.forEach(function (src, index) {
            var pageNumber = index + 1;
            $thumbs.append(
                '<button class="restaurant_menu_thumb' + (index < 2 ? ' active' : '') + '" type="button"' +
                ' data-menu-page="' + index + '" aria-label="Trang ' + pageNumber + '">' +
                '<span class="restaurant_menu_thumb_img"><img src="' + src + '" alt="Nội dung tầng - trang ' + pageNumber + '"></span>' +
                '<span class="restaurant_menu_thumb_number txt_14 txt_bold">' + pageNumber + '</span></button>'
            );
        });
    }

    function renderExploreGallery(images) {
        var columnCount = exploreGalleryMobileMedia.matches ? 2 : 4;
        var columns = Array.from({ length: columnCount }, function () { return []; });

        images.forEach(function (item, index) {
            var src = typeof item === 'string' ? item : item.src;
            var category = typeof item === 'string' ? 'all' : item.category;
            columns[index % columns.length].push(
                '<div class="popup_tour_seeall_list_item_img" data-gallery-category="' + category + '">' +
                '<div class="popup_tour_seeall_list_item_img_inner img_abs">' +
                '<div class="popup_tour_seeall_list_item_img_block"></div>' +
                '<img src="' + src + '" alt="Hình ảnh không gian tầng"></div>' +
                '<div class="popup_tour_seeall_list_item_img_tag txt_14 txt_bold">tag</div></div>'
            );
        });

        $('.restaurant_gallery .popup_tour_seeall_list').html(columns.map(function (items) {
            return '<div class="popup_tour_seeall_list_item">' + items.join('') + '</div>';
        }).join(''));

        var activeFilter = $('.restaurant_popup_tab.gallery_filter_tab.active').attr('data-gallery-filter') || 'all';
        $('.restaurant_gallery [data-gallery-category]').each(function () {
            var shouldShow = activeFilter === 'all' || $(this).attr('data-gallery-category') === activeFilter;
            $(this).toggle(shouldShow);
        });
    }

    exploreGalleryMobileMedia.addEventListener('change', function () {
        var config = explorePopupData[activeExplorePopup];
        if (config) renderExploreGallery(config.galleryImages);
    });

    function createExploreFlipbookPages() {
        return restaurantMenuPages.map(function (src, index) {
            var page = document.createElement('div');
            var image = document.createElement('img');

            page.className = 'restaurant_flipbook_page';
            page.setAttribute('data-density', 'soft');
            image.src = src;
            image.alt = 'Nội dung tầng - trang ' + (index + 1);
            image.draggable = false;
            page.appendChild(image);
            return page;
        });
    }

    function applyExplorePopupData(key) {
        var config = explorePopupData[key];
        if (!config || activeExplorePopup === key) return Boolean(config);

        activeExplorePopup = key;
        restaurantMenuPages = config.menuPages.slice();
        $('.popup_tour.restaurant .explore_popup_title').text(config.title);
        renderExplorePopupTabs(config);
        renderExploreMenuThumbs();
        renderExploreGallery(config.galleryImages);

        if (restaurantMenuPages.length && restaurantPageFlip) {
            restaurantPageFlip.updateFromHtml(createExploreFlipbookPages());
            restaurantPageFlip.turnToPage(0);
            updateRestaurantMenuPage(0);
        } else if (restaurantMenuPages.length) {
            $('.restaurant_menu_page_count').text('1–2 / ' + restaurantMenuPages.length);
            var bookElement = document.getElementById('restaurant-flipbook');
            if (!bookElement) return false;
            bookElement.innerHTML = '<img class="restaurant_flipbook_fallback" src="' + restaurantMenuPages[0] + '" alt="Nội dung tầng">';
        }

        return true;
    }

    function updateRestaurantMenuPage(pageIndex) {
        var currentPage = pageIndex + 1;
        var activePages = [pageIndex];
        var orientation = restaurantPageFlip ? restaurantPageFlip.getOrientation() : null;
        var isTwoPageView = orientation === 'landscape' || orientation === 1;

        if (isTwoPageView && pageIndex + 1 < restaurantMenuPages.length) {
            activePages.push(pageIndex + 1);
        }

        $('.restaurant_menu_thumb').removeClass('active');
        activePages.forEach(function (index) {
            $('.restaurant_menu_thumb[data-menu-page="' + index + '"]').addClass('active');
        });

        var pageLabel = activePages.length > 1
            ? currentPage + '–' + (activePages[activePages.length - 1] + 1)
            : currentPage;
        $('.restaurant_menu_page_count').text(pageLabel + ' / ' + restaurantMenuPages.length);

        var activeThumb = document.querySelector('.restaurant_menu_thumb.active');
        if (activeThumb) activeThumb.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function initRestaurantFlipbook() {
        if (restaurantPageFlip || !window.St || !window.St.PageFlip) return;

        var bookElement = document.getElementById('restaurant-flipbook');
        if (!bookElement) return;
        bookElement.innerHTML = '';

        createExploreFlipbookPages().forEach(function (page) {
            bookElement.appendChild(page);
        });

        restaurantPageFlip = new St.PageFlip(bookElement, {
            width: 424,
            height: 594,
            size: 'stretch',
            minWidth: 212,
            maxWidth: 530,
            minHeight: 297,
            maxHeight: 735,
            drawShadow: true,
            flippingTime: 900,
            usePortrait: true,
            startZIndex: 0,
            autoSize: true,
            maxShadowOpacity: 0.35,
            showCover: false,
            mobileScrollSupport: false
        });

        restaurantPageFlip.on('flip', function (event) {
            updateRestaurantMenuPage(event.data);
        });
        restaurantPageFlip.on('init', function (event) {
            updateRestaurantMenuPage(event.data.page);
        });
        restaurantPageFlip.on('changeOrientation', function () {
            updateRestaurantMenuPage(restaurantPageFlip.getCurrentPageIndex());
        });
        restaurantPageFlip.loadFromHTML(bookElement.querySelectorAll('.restaurant_flipbook_page'));
    }

    function openRestaurantPopup(view, popupKey) {
        if (!applyExplorePopupData(popupKey)) return;

        var $restaurant = $('.popup_tour.restaurant');
        $restaurant.addClass('active');
        $restaurant.find('.restaurant_popup_tab').removeClass('active').attr('aria-selected', 'false');
        var $activeViewTab = $restaurant.find('[data-restaurant-view="' + view + '"]');
        if ($activeViewTab.length) {
            $activeViewTab.addClass('active').attr('aria-selected', 'true');
        } else {
            $restaurant.find('[data-gallery-filter="all"]').addClass('active').attr('aria-selected', 'true');
            $restaurant.find('[data-gallery-category]').show();
        }
        $restaurant.find('.restaurant_popup_panel').removeClass('active');
        $restaurant.find('[data-restaurant-panel="' + view + '"]').addClass('active');

        if (view === 'menu') {
            requestAnimationFrame(function () {
                requestAnimationFrame(initRestaurantFlipbook);
            });
        }
    }

    $('[data-explore-popup]').click(function () {
        openRestaurantPopup($(this).attr('data-explore-view'), $(this).attr('data-explore-popup'));
    });

    $('.restaurant_popup_tabs').on('click', '[data-restaurant-view]', function () {
        var view = $(this).data('restaurant-view');
        var $restaurant = $(this).closest('.popup_tour.restaurant');

        $restaurant.find('.restaurant_popup_tab').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $restaurant.find('.restaurant_popup_panel').removeClass('active');
        $restaurant.find('[data-restaurant-panel="' + view + '"]').addClass('active');

        if (view === 'menu') {
            requestAnimationFrame(function () {
                requestAnimationFrame(initRestaurantFlipbook);
            });
        }
    });

    $('.restaurant_popup_tabs').on('click', '[data-gallery-filter]', function () {
        var filter = $(this).attr('data-gallery-filter');

        $('.restaurant_popup_tab').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $('.restaurant_gallery [data-gallery-category]').each(function () {
            var shouldShow = filter === 'all' || $(this).attr('data-gallery-category') === filter;
            $(this).toggle(shouldShow);
        });
    });

    $('.restaurant_menu_thumbs').on('click', '.restaurant_menu_thumb', function () {
        if (!restaurantPageFlip) return;
        restaurantPageFlip.flip(Number($(this).data('menu-page')), 'top');
    });

    $('.restaurant_menu_prev').click(function () {
        if (restaurantPageFlip) restaurantPageFlip.flipPrev('top');
    });

    $('.restaurant_menu_next').click(function () {
        if (restaurantPageFlip) restaurantPageFlip.flipNext('top');
    });

    $('.restaurant .popup_tour_close').click(function () {
        $('.popup_tour.restaurant').removeClass('active');
    });
    $('[data-booking-trigger]').click(function (event) {
        event.preventDefault();

        var $trigger = $(this);
        var $popup = $('.popup_form_booking');
        var $form = $popup.find('.popup_form_booking_form');
        var bookingType = $trigger.attr('data-booking-type');
        var subtitle = $trigger.attr('data-booking-subtitle');
        var showFloorSelect = bookingType === 'general';

        $popup.find('.popup_form_booking_title').text($trigger.attr('data-booking-title'));
        $popup.find('.popup_form_booking_subtitle').text(subtitle).toggle(Boolean(subtitle));
        $popup.find('.popup_form_booking_submit').text($trigger.attr('data-booking-submit'));
        $popup.toggleClass('show-floor-select', showFloorSelect);
        $popup.find('[data-booking-floor-select]').val('');
        $form.find('[name="booking_type"]').val(bookingType);
        $form.find('[name="floor"]').val($trigger.attr('data-booking-floor'));
        $form.find('[name="venue"]').val($trigger.attr('data-booking-venue'));
        $form.find('[name="product_id"]').val($trigger.attr('data-booking-product-id') || '');
        $form.find('[name="product_name"]').val($trigger.attr('data-booking-product-name') || '');
        $form.attr('data-booking-type', $trigger.attr('data-booking-type'));
        $form.attr('data-booking-floor', $trigger.attr('data-booking-floor'));
        $form.attr('data-booking-venue', $trigger.attr('data-booking-venue'));
        $popup.addClass('active').attr('aria-hidden', 'false');
    });

    $('[data-booking-floor-select]').change(function () {
        $('.popup_form_booking_form [name="floor"]').val($(this).val());
    });

    $('.workshop_filter').click(function () {
        var category = $(this).attr('data-workshop-filter');

        $('.workshop_filter').removeClass('active');
        $(this).addClass('active');
        $('.workshop_card').each(function () {
            var shouldShow = category === 'all' || $(this).attr('data-workshop-category') === category;
            $(this).prop('hidden', !shouldShow);
        });
    });

    var workshopDetailImages = [];
    var workshopDetailImageIndex = 0;
    var workshopDetailThumbSwiper = null;
    var workshopDetailImageTransition = null;

    function destroyWorkshopDetailImageTransition() {
        if (workshopDetailImageTransition) {
            workshopDetailImageTransition.destroy();
            workshopDetailImageTransition = null;
        }

        $('.workshop_detail_main_image').removeClass('webgl-transition-ready');
    }

    function initWorkshopDetailImageTransition() {
        var container = document.querySelector('.workshop_detail_main_image');

        destroyWorkshopDetailImageTransition();
        if (!window.WebGLImageTransition || !container || !workshopDetailImages.length) return;

        workshopDetailImageTransition = new WebGLImageTransition({
            container: container,
            images: workshopDetailImages,
            initialIndex: workshopDetailImageIndex,
            duration: 1100
        });
    }

    function showWorkshopDetailImage(index) {
        if (!workshopDetailImages.length) return;

        workshopDetailImageIndex = (index + workshopDetailImages.length) % workshopDetailImages.length;
        $('.workshop_detail_main_image > img').attr('src', workshopDetailImages[workshopDetailImageIndex]);
        $('.workshop_detail_thumb').removeClass('active')
            .filter('[data-detail-image="' + workshopDetailImageIndex + '"]').addClass('active');
        if (workshopDetailThumbSwiper) {
            workshopDetailThumbSwiper.slideTo(workshopDetailImageIndex);
        }
        if (workshopDetailImageTransition) {
            workshopDetailImageTransition.goTo(workshopDetailImageIndex);
        }
    }

    function renderWorkshopDetailThumbs() {
        var $thumbs = $('.workshop_detail_thumbs_inner').empty();

        workshopDetailImages.forEach(function (src, index) {
            $thumbs.append(
                '<button class="workshop_detail_thumb swiper-slide' + (index === 0 ? ' active' : '') + '" type="button" data-detail-image="' + index + '" aria-label="Xem ảnh ' + (index + 1) + '">' +
                '<img src="' + src + '" alt="Ảnh workshop ' + (index + 1) + '"></button>'
            );
        });

        if (!workshopDetailThumbSwiper) {
            workshopDetailThumbSwiper = new Swiper('.workshop_detail_thumbs', {
                slidesPerView: 'auto',
                spaceBetween: 10,
                freeMode: true,
                grabCursor: true,
                watchOverflow: true,
                observer: true,
                observeParents: true
            });
        } else {
            workshopDetailThumbSwiper.update();
            workshopDetailThumbSwiper.slideTo(0, 0);
        }
    }

    $('.workshop_card_action').click(function () {
        var $card = $(this).closest('.workshop_card');
        var productId = $card.attr('data-product-id');
        var productName = $card.find('.workshop_card_title').text().trim();
        var mainImage = $card.find('.workshop_card_image img').attr('src');

        destroyWorkshopDetailImageTransition();

        workshopDetailImages = [
            mainImage,
            '/asset/img/store.jpg',
            '/asset/img/store.jpg',
            '/asset/img/img_popup.webp',
            '/asset/img/store.jpg',
            '/asset/img/store.jpg'
        ];
        workshopDetailImageIndex = 0;
        $('.workshop_detail_tag').text($card.find('.workshop_card_tag').text());
        $('.workshop_detail_title').text(productName);
        $('.workshop_detail_description').text($card.find('.workshop_card_description').text());
        $('.workshop_detail_price').text($card.find('.workshop_card_price').text());
        $('.workshop_detail_booking')
            .attr('data-booking-product-id', productId)
            .attr('data-booking-product-name', productName);
        renderWorkshopDetailThumbs();
        showWorkshopDetailImage(0);
        $('.workshop_detail_popup').addClass('active').attr('aria-hidden', 'false');
        requestAnimationFrame(function () {
            if (workshopDetailThumbSwiper) {
                workshopDetailThumbSwiper.update();
                workshopDetailThumbSwiper.slideTo(0, 0);
            }
            initWorkshopDetailImageTransition();
        });
    });

    $('.workshop_detail_thumbs').on('click', '.workshop_detail_thumb', function () {
        showWorkshopDetailImage(Number($(this).attr('data-detail-image')));
    });

    $('.workshop_detail_prev').click(function () {
        showWorkshopDetailImage(workshopDetailImageIndex - 1);
    });

    $('.workshop_detail_next').click(function () {
        showWorkshopDetailImage(workshopDetailImageIndex + 1);
    });

    $('.workshop_detail_close, .workshop_detail_overlay, .workshop_detail_booking').click(function () {
        $('.workshop_detail_popup').removeClass('active').attr('aria-hidden', 'true');
        destroyWorkshopDetailImageTransition();
    });

    $(document).keydown(function (event) {
        if (event.key === 'Escape' && $('.workshop_detail_popup').hasClass('active')) {
            $('.workshop_detail_popup').removeClass('active').attr('aria-hidden', 'true');
            destroyWorkshopDetailImageTransition();
        }
    });

    $('.popup_form_booking_form').on('submit', function (event) {
        // Chờ kết nối API/Zalo ở bước backend; giữ nguyên toàn bộ metadata trong form.
        event.preventDefault();
    });

    $('.popup_form .popup_tour_close').click(function () {
        $(this).closest('.popup_form').removeClass('active').attr('aria-hidden', 'true');
    });
    $('.popup_form_overlay').click(function () {
        $(this).closest('.popup_form').removeClass('active').attr('aria-hidden', 'true');
    });

    // popup_member close
    $('.popup_member_back').click(function () {
        $('.popup_member').removeClass('active');
    });
    $('.header_button_member').click(function () {
        $('.popup_member').addClass('active');
    });
});
