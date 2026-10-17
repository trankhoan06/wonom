$(document).ready(function () {
    const deferredScripts = new Map();
    const loadScriptOnce = (src) => {
        if (!src) return Promise.reject(new Error('Missing script URL'));
        if (deferredScripts.has(src)) return deferredScripts.get(src);

        const request = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-wonom-src="' + src + '"]');
            if (existing) {
                if (existing.dataset.loaded === 'true') resolve();
                else {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                }
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.dataset.wonomSrc = src;
            script.addEventListener('load', () => {
                script.dataset.loaded = 'true';
                resolve();
            }, { once: true });
            script.addEventListener('error', reject, { once: true });
            document.head.appendChild(script);
        });

        deferredScripts.set(src, request);
        return request;
    };

    const runWhenIdle = (callback, timeout = 2500) => {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(callback, { timeout });
        } else {
            window.setTimeout(callback, Math.min(timeout, 1200));
        }
    };

    const popupRootSelector = '.popup_tour, .popup_form, .popup_member, .workshop_detail_popup';

    const watchPopupImage = (image) => {
        if (!image || !image.matches('img')) return;

        image.dataset.wonomPopupImage = 'pending';
        image.dataset.wonomImageReady = 'false';

        const reveal = () => {
            if (image.complete && image.naturalWidth > 0) {
                image.dataset.wonomImageReady = 'true';
                image.dataset.wonomPopupImage = 'loaded';
            }
        };

        image.addEventListener('load', reveal, { once: true });
        image.addEventListener('error', () => {
            image.dataset.wonomPopupImage = 'error';
        }, { once: true });

        const deferredSrcset = image.getAttribute('data-wonom-srcset');
        const deferredSrc = image.getAttribute('data-wonom-src');
        if (deferredSrcset) {
            image.setAttribute('srcset', deferredSrcset);
            image.removeAttribute('data-wonom-srcset');
        }
        if (!image.getAttribute('src') && deferredSrc) {
            image.setAttribute('src', deferredSrc);
        }
        if (image.getAttribute('src')) {
            image.removeAttribute('data-wonom-src');
        }

        reveal();
    };

    const hydratePopupImages = (root = document) => {
        if (root.matches && root.matches('img')) {
            watchPopupImage(root);
            return;
        }
        root.querySelectorAll?.('img').forEach(watchPopupImage);
    };

    const setPopupImageSource = (image, src) => {
        if (!image || !src) return;
        image.dataset.wonomPopupImage = 'pending';
        image.dataset.wonomImageReady = 'false';
        image.removeAttribute('data-wonom-src');
        image.removeAttribute('srcset');
        image.removeAttribute('data-wonom-srcset');
        image.setAttribute('src', src);
        watchPopupImage(image);
    };

    const popupObserver = new MutationObserver((records) => {
        records.forEach((record) => {
            if (record.type === 'attributes') {
                const popup = record.target;
                if (popup.matches(popupRootSelector) && popup.classList.contains('active')) {
                    hydratePopupImages(popup);
                }
                return;
            }

            record.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return;
                const popup = node.closest?.(popupRootSelector);
                if (popup && popup.classList.contains('active')) hydratePopupImages(node);
            });
        });
    });
    popupObserver.observe(document.body, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['class']
    });

    const hydrateDeferredPopupImages = () => runWhenIdle(() => {
        document.querySelectorAll(popupRootSelector).forEach(hydratePopupImages);
    }, 1200);

    const finishPopupImageDeferral = () => {
        document.documentElement.classList.add('wonom-page-loaded');
        hydrateDeferredPopupImages();
    };

    if (document.readyState === 'complete') finishPopupImageDeferral();
    else window.addEventListener('load', finishPopupImageDeferral, { once: true });

    const parseRem = (input) => {
        return (input / 10) * parseFloat($("html").css("font-size"));
    };

    const pageScroller = document.querySelector('.wonom-page-scroll');
    const mobilePageScrollMedia = window.matchMedia('(max-width: 991px)');
    const usesPageScroller = () => Boolean(pageScroller && mobilePageScrollMedia.matches);
    const getPageScrollTop = () => usesPageScroller() ? pageScroller.scrollTop : window.scrollY;
    const getPageViewportHeight = () => usesPageScroller() ? pageScroller.clientHeight : window.innerHeight;
    const getElementPageTop = (element) => {
        if (!element) return 0;
        if (!usesPageScroller()) return element.getBoundingClientRect().top + window.scrollY;

        return element.getBoundingClientRect().top
            - pageScroller.getBoundingClientRect().top
            + pageScroller.scrollTop;
    };
    const scrollPageTo = (options) => {
        if (usesPageScroller()) pageScroller.scrollTo(options);
        else window.scrollTo(options);
    };
    const addPageScrollListener = (callback) => {
        window.addEventListener('scroll', callback, { passive: true });
        if (pageScroller) pageScroller.addEventListener('scroll', callback, { passive: true });
    };

    const resolveWonomAsset = (path) => {
        if (typeof path !== 'string' || !path.startsWith('/asset/')) return path;
        const assetBase = window.wonomHome?.assetUrl || '/asset/';
        return assetBase + path.slice('/asset/'.length);
    };

    const customCursor = document.querySelector('.site_cursor');
    const customCursorCompanions = customCursor?.querySelector('.site_cursor_companions');
    const customCursorMedia = window.matchMedia('(hover: hover) and (pointer: fine)');
    let customCursorFrame = 0;
    let customCursorTargetX = -100;
    let customCursorTargetY = -100;
    let customCursorX = -100;
    let customCursorY = -100;
    let customCursorStarted = false;

    const syncCustomCursorAvailability = () => {
        document.documentElement.classList.toggle('has-custom-cursor', customCursorMedia.matches && Boolean(customCursor));
        if (!customCursorMedia.matches && customCursor) customCursor.classList.remove('is-visible', 'is-interactive');
    };

    const renderCustomCursor = () => {
        if (!customCursor || !customCursorMedia.matches) {
            customCursorFrame = 0;
            return;
        }

        customCursorX += (customCursorTargetX - customCursorX) * .22;
        customCursorY += (customCursorTargetY - customCursorY) * .22;
        if (customCursorCompanions) {
            customCursorCompanions.style.transform = 'translate3d(' + (customCursorX - customCursorTargetX) + 'px, ' + (customCursorY - customCursorTargetY) + 'px, 0)';
        }

        if (Math.abs(customCursorTargetX - customCursorX) > .05 || Math.abs(customCursorTargetY - customCursorY) > .05) {
            customCursorFrame = window.requestAnimationFrame(renderCustomCursor);
        } else {
            customCursorX = customCursorTargetX;
            customCursorY = customCursorTargetY;
            if (customCursorCompanions) customCursorCompanions.style.transform = 'translate3d(0, 0, 0)';
            customCursorFrame = 0;
        }
    };

    window.addEventListener('pointermove', function (event) {
        if (!customCursor || !customCursorMedia.matches) return;

        customCursorTargetX = event.clientX;
        customCursorTargetY = event.clientY;
        customCursor.style.transform = 'translate3d(' + customCursorTargetX + 'px, ' + customCursorTargetY + 'px, 0)';
        if (!customCursorStarted) {
            customCursorX = customCursorTargetX;
            customCursorY = customCursorTargetY;
            customCursorStarted = true;
        }
        customCursor.classList.add('is-visible');
        customCursor.classList.toggle(
            'is-interactive',
            event.target instanceof Element && Boolean(event.target.closest('a, button, .btn, .workshop_card, input:not([type="hidden"]), textarea, select, [contenteditable="true"], [data-explore-tab], [data-footer-href], .footer_bot_right_txt, .footer_bot_right, .popup_tour_policy_card_item'))
        );

        if (!customCursorFrame) customCursorFrame = window.requestAnimationFrame(renderCustomCursor);
    }, { passive: true });

    document.addEventListener('mouseleave', function () {
        if (customCursor) customCursor.classList.remove('is-visible', 'is-interactive');
    });

    customCursorMedia.addEventListener('change', syncCustomCursorAvailability);
    syncCustomCursorAvailability();

    // Keep the page behind an open popup fixed while allowing each popup's
    // own scrollable content to keep working.
    const popupSelector = '.popup_tour.is-mounted, .popup_form.active, .popup_member.active, .workshop_detail_popup.active';
    const body = document.body;
    let pageScrollLocked = false;
    let lockedScrollY = 0;
    let previousBodyStyle = null;
    let previousPageScrollerStyle = null;
    let lockedScrollTarget = null;
    let pageScrollUnlockTimer = 0;

    const lockPageScroll = () => {
        window.clearTimeout(pageScrollUnlockTimer);
        pageScrollUnlockTimer = 0;
        if (pageScrollLocked) return;

        lockedScrollY = getPageScrollTop();

        if (usesPageScroller()) {
            previousPageScrollerStyle = {
                overflow: pageScroller.style.overflow,
                overflowX: pageScroller.style.overflowX,
                overflowY: pageScroller.style.overflowY,
                overscrollBehavior: pageScroller.style.overscrollBehavior,
                touchAction: pageScroller.style.touchAction
            };
            lockedScrollTarget = pageScroller;
            pageScroller.style.overflow = 'hidden';
            pageScroller.style.overscrollBehavior = 'none';
            pageScroller.style.touchAction = 'none';
            pageScrollLocked = true;
            return;
        }

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
        lockedScrollTarget = body;
        pageScrollLocked = true;
    };

    const unlockPageScroll = () => {
        if (!pageScrollLocked) return;

        if (lockedScrollTarget === pageScroller && previousPageScrollerStyle) {
            Object.keys(previousPageScrollerStyle).forEach((property) => {
                pageScroller.style[property] = previousPageScrollerStyle[property];
            });
            pageScroller.scrollTop = lockedScrollY;
        } else if (previousBodyStyle) {
            Object.keys(previousBodyStyle).forEach((property) => {
                body.style[property] = previousBodyStyle[property];
            });
            window.scrollTo(0, lockedScrollY);
        }

        lockedScrollTarget = null;
        previousPageScrollerStyle = null;
        pageScrollLocked = false;
        syncExploreMobileFloorOnScroll();
    };

    const syncPageScrollLock = () => {
        if (document.querySelector(popupSelector)) {
            lockPageScroll();
        } else if (pageScrollLocked && !pageScrollUnlockTimer) {
            // Keep the page fixed until the longest popup exit transition ends.
            // Unlocking immediately makes a fixed popup appear to slide inside
            // the current full-page section instead of leaving the viewport.
            pageScrollUnlockTimer = window.setTimeout(() => {
                pageScrollUnlockTimer = 0;
                if (!document.querySelector(popupSelector)) unlockPageScroll();
            }, 650);
        }
    };

    const popupStateObserver = new MutationObserver(syncPageScrollLock);
    document.querySelectorAll('.popup_tour, .popup_form, .popup_member, .workshop_detail_popup').forEach((popup) => {
        popupStateObserver.observe(popup, { attributes: true, attributeFilter: ['class'] });
    });
    syncPageScrollLock();

    // Keep translated full-screen popups out of the render tree while closed.
    // Mount first, allow one painted frame at the off-screen position, then
    // activate on the following frame so Safari can animate the transform.
    const slidingPopupStates = new WeakMap();

    function getSlidingPopupState(popup) {
        if (!slidingPopupStates.has(popup)) {
            slidingPopupStates.set(popup, {
                mountFrame: 0,
                activateFrame: 0,
                unmountFrame: 0,
                unmountTimer: 0,
                closeHandler: null
            });
        }
        return slidingPopupStates.get(popup);
    }

    function openSlidingPopup($popup) {
        $popup.each(function () {
            var popup = this;
            var state = getSlidingPopupState(popup);

            window.clearTimeout(state.unmountTimer);
            window.cancelAnimationFrame(state.mountFrame);
            window.cancelAnimationFrame(state.activateFrame);
            window.cancelAnimationFrame(state.unmountFrame);
            if (state.closeHandler) popup.removeEventListener('transitionend', state.closeHandler);
            state.unmountTimer = 0;
            state.unmountFrame = 0;
            state.closeHandler = null;
            popup.classList.add('is-mounted');
            popup.setAttribute('aria-hidden', 'false');

            state.mountFrame = window.requestAnimationFrame(function () {
                state.mountFrame = 0;
                state.activateFrame = window.requestAnimationFrame(function () {
                    state.activateFrame = 0;
                    if (popup.classList.contains('is-mounted')) popup.classList.add('active');
                });
            });
        });
    }

    function closeSlidingPopup($popup) {
        $popup.each(function () {
            var popup = this;
            var state = getSlidingPopupState(popup);

            window.cancelAnimationFrame(state.mountFrame);
            window.cancelAnimationFrame(state.activateFrame);
            window.cancelAnimationFrame(state.unmountFrame);
            window.clearTimeout(state.unmountTimer);
            if (state.closeHandler) popup.removeEventListener('transitionend', state.closeHandler);
            state.mountFrame = 0;
            state.activateFrame = 0;
            state.unmountFrame = 0;
            popup.classList.remove('active');
            popup.setAttribute('aria-hidden', 'true');

            state.closeHandler = function (event) {
                if (event && (event.target !== popup || event.propertyName !== 'transform')) return;

                popup.removeEventListener('transitionend', state.closeHandler);
                state.closeHandler = null;
                window.clearTimeout(state.unmountTimer);
                state.unmountTimer = 0;

                var hasOtherOpenPopup = Array.from(document.querySelectorAll(popupSelector)).some(function (candidate) {
                    return candidate !== popup;
                });
                if (!hasOtherOpenPopup) {
                    window.clearTimeout(pageScrollUnlockTimer);
                    pageScrollUnlockTimer = 0;
                    unlockPageScroll();
                }

                state.unmountFrame = window.requestAnimationFrame(function () {
                    state.unmountFrame = 0;
                    if (!popup.classList.contains('active')) popup.classList.remove('is-mounted');
                });
            };

            popup.addEventListener('transitionend', state.closeHandler);
            state.unmountTimer = window.setTimeout(state.closeHandler, 700);
        });
    }

    // Keep the fixed header visible and reserve room for it in the active section.
    if (window.gsap) {
        const header = document.querySelector(".header");
        const pageSections = Array.from(document.querySelectorAll(".pa_section"));
        const desktopMedia = window.matchMedia("(min-width: 992px)");
        let sectionTransitioning = false;

        const syncHeaderSection = () => {
            // Keep the current section layout stable until the full-page
            // transition has actually reached the destination section.
            if (sectionTransitioning) return;

            pageSections.forEach((section) => section.classList.remove("has-header"));
            if (!desktopMedia.matches || !pageSections.length) return;

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
            const destinationHasHeader = desktopMedia.matches;

            // Prepare the destination's final layout before it moves into the
            // viewport. The current section keeps its class until commit.
            if (destinationSection) {
                destinationSection.classList.toggle('has-header', destinationHasHeader);
            }
        });

        window.addEventListener('wonom:section-transition-end', function () {
            sectionTransitioning = false;
            syncHeaderSection();
        });

        desktopMedia.addEventListener("change", syncHeaderSection);

        if (header) gsap.set(header, { yPercent: 0 });
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
                ".popup_tour.is-mounted, .popup_form.active, .popup_member.active, .workshop_detail_popup.active"
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

            const onSectionRequest = (event) => {
                const targetId = event.detail && event.detail.id;
                const nextIndex = sections.findIndex((section) => section.id === targetId);
                if (nextIndex < 0) return;

                event.preventDefault();
                goToSection(nextIndex);
            };

            html.classList.add("fullpage-scroll");
            currentIndex = nearestSectionIndex();

            window.addEventListener("wheel", onWheel, { passive: false });
            window.addEventListener("keydown", onKeydown);
            window.addEventListener("touchstart", onTouchStart, { passive: true });
            window.addEventListener("touchend", onTouchEnd, { passive: true });
            window.addEventListener("scroll", onScroll, { passive: true });
            window.addEventListener("wonom:go-to-section", onSectionRequest);

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
                window.removeEventListener("wonom:go-to-section", onSectionRequest);
                navLinks.forEach((link) => link.removeEventListener("click", onNavClick));
            };
        });
    }
    $('#langSelectorBtn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#langWrapper').toggleClass('active');
    });

    // Keep the header navigation in sync with the section currently in view.
    const headerNavItems = Array.from(document.querySelectorAll('.header-nav .nav-item[href^="#"]'));
    const headerSections = headerNavItems.map(function (item) {
        const id = item.getAttribute('href').slice(1);
        return { id: id, item: item, section: document.getElementById(id) };
    }).filter(function (entry) {
        return entry.id && entry.section;
    });
    let headerScrollFrame = null;
    let currentHeaderActiveId = null;
    let headerViewportWidth = window.innerWidth;

    function setActiveHeaderItem(activeId) {
        if (currentHeaderActiveId === activeId) return;
        currentHeaderActiveId = activeId;
        headerSections.forEach(function (entry) {
            const isActive = entry.id === activeId;
            entry.item.classList.toggle('active', isActive);
            if (isActive) entry.item.setAttribute('aria-current', 'location');
            else entry.item.removeAttribute('aria-current');
        });
    }

    function syncActiveHeaderItem() {
        headerScrollFrame = null;
        const header = document.querySelector('.header');
        const headerHeight = header ? header.getBoundingClientRect().height : 0;
        const probeY = Math.max(headerHeight + 1, window.innerHeight * 0.35);
        let activeId = '';

        headerSections.forEach(function (entry) {
            const rect = entry.section.getBoundingClientRect();
            if (rect.top <= probeY && rect.bottom > probeY) {
                activeId = entry.id;
            }
        });

        setActiveHeaderItem(activeId);
    }

    function requestHeaderScrollSync() {
        if (headerScrollFrame !== null) return;
        headerScrollFrame = window.requestAnimationFrame(syncActiveHeaderItem);
    }

    function requestHeaderResizeSync() {
        if (Math.abs(window.innerWidth - headerViewportWidth) <= 2) return;
        headerViewportWidth = window.innerWidth;
        requestHeaderScrollSync();
    }

    headerNavItems.forEach(function (item) {
        item.addEventListener('click', function (event) {
            const id = item.getAttribute('href').slice(1);
            const target = document.getElementById(id);
            if (!target) return;

            setActiveHeaderItem(id);

            // Desktop navigation is handled by the full-page GSAP controller.
            if (window.matchMedia('(min-width: 992px)').matches) return;

            event.preventDefault();
            const header = document.querySelector('.header');
            const headerOffset = header ? header.getBoundingClientRect().height : 0;
            const targetTop = getElementPageTop(target) - headerOffset;
            scrollPageTo({
                top: Math.max(0, targetTop),
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });
            window.history.replaceState(null, '', '#' + id);
        });
    });

    addPageScrollListener(requestHeaderScrollSync);
    window.addEventListener('resize', requestHeaderResizeSync, { passive: true });
    window.addEventListener('load', requestHeaderScrollSync);
    window.addEventListener('wonom:section-transition-end', requestHeaderScrollSync);
    requestHeaderScrollSync();

    // Toggle hamburger menu
    $('.header_menu').on('click', function (e) {
        $(this).toggleClass('active');
        $('.header-nav').toggleClass('active');
        $('.header_overlay').toggleClass('active');
        $('body').toggleClass('mobile-menu-open', $(this).hasClass('active'));
    });

    // Close hamburger menu when clicking overlay
    $('.header_overlay').on('click', function () {
        $('.header_menu').removeClass('active');
        $('.header-nav').removeClass('active');
        $(this).removeClass('active');
        $('body').removeClass('mobile-menu-open');
    });

    // Close the mobile menu before navigating to the selected section.
    $('.header-nav .nav-item').on('click', function () {
        if (!window.matchMedia('(max-width: 991px)').matches) return;

        $('.header_menu').removeClass('active');
        $('.header-nav').removeClass('active');
        $('.header_overlay').removeClass('active');
        $('body').removeClass('mobile-menu-open');
    });

    // Tab logic cho phần Khám Phá 5 Tầng (Explore)
    let exploreTabTransitionTimer = null;
    const exploreMobileMedia = window.matchMedia('(max-width: 991px)');
    const $exploreContentTabs = $('.home_explore_content_tab');
    const $exploreMobileTabs = $('.home_explore_content_subtitle_wrap');
    const $exploreFloorSelector = $('.home_explore_sidebar_bottom_inner');
    const $exploreFloorControls = $exploreFloorSelector.find('.home_explore_floor_control');
    const $exploreMobileFloorItems = $('.home_explore_mobile_floor_item');
    const exploreMobileFloorSections = Array.from(document.querySelectorAll('.home_explore_floor_section[data-floor-section]'));
    const exploreMobileFloorNav = document.querySelector('.home_explore_mobile_floor_nav');
    const exploreSection = document.getElementById('explore');
    const exploreHeader = document.querySelector('header');
    let exploreMobileFloorScrollFrame = null;
    let exploreActivationLine = 16;
    let exploreMetricWidth = 0;
    let exploreSectionTop = 0;
    let exploreSectionBottom = 0;
    let exploreFloorSectionTops = [];
    let exploreFloorNavVisible = null;

    // Keep the fixed floor navigation outside transformed/clipped page sections.
    if (exploreMobileFloorNav && exploreMobileFloorNav.parentElement !== document.body) {
        document.body.appendChild(exploreMobileFloorNav);
    }

    function measureExploreMobileMetrics(force) {
        if (!force && Math.abs(window.innerWidth - exploreMetricWidth) <= 2) return;
        exploreMetricWidth = window.innerWidth;
        exploreActivationLine = (exploreHeader ? exploreHeader.offsetHeight : 0) + 16;
        if (exploreSection) {
            exploreSectionTop = getElementPageTop(exploreSection);
            exploreSectionBottom = exploreSectionTop + exploreSection.offsetHeight;
        }
        exploreFloorSectionTops = exploreMobileFloorSections.map(function (section) {
            return getElementPageTop(section);
        });
        requestExploreMobileFloorSync();
    }

    function syncExploreMobileFloorItems(targetTab) {
        $exploreMobileFloorItems.each(function () {
            var isActive = $(this).attr('data-explore-tab') === targetTab;
            $(this).toggleClass('active', isActive).attr('aria-current', isActive ? 'true' : 'false');
        });
    }

    function setExploreFloorControl($control, targetIndex) {
        var isDisabled = targetIndex < 0 || targetIndex >= $exploreContentTabs.length;
        var floorLabel = isDisabled ? '' : (targetIndex + 1) + 'F';

        $control
            .toggleClass('is-disabled', isDisabled)
            .prop('disabled', isDisabled)
            .attr('data-tab', isDisabled ? '' : 'tab' + floorLabel.toLowerCase())
            .attr('aria-label', isDisabled ? '' : (((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.go_to_floor) || 'Đi đến tầng {number}').replace('{number}', targetIndex + 1)));
        $control.find('.home_explore_floor_control_label').text(floorLabel);
    }

    function syncExploreFloorSelector(floorIndex) {
        var floorLabel = (floorIndex + 1) + 'F';
        var targetTab = 'tab' + floorLabel.toLowerCase();

        $exploreFloorSelector.attr('data-current-floor', floorIndex + 1);
        $('.home_explore_sidebar_bottom_item.item1').text(floorLabel);
        setExploreFloorControl($exploreFloorSelector.find('.item2'), floorIndex - 1);
        setExploreFloorControl($exploreFloorSelector.find('.item3'), floorIndex + 1);
        syncExploreMobileFloorItems(targetTab);
    }

    function syncExploreMobileFloorOnScroll() {
        exploreMobileFloorScrollFrame = null;
        if (!exploreMobileMedia.matches || !exploreMobileFloorSections.length) {
            exploreFloorNavVisible = false;
            if (exploreMobileFloorNav) exploreMobileFloorNav.classList.remove('is-visible');
            document.body.classList.remove('explore-floor-nav-visible');
            return;
        }

        // Body is temporarily fixed while a popup is open/closing. Its scroll
        // coordinates are not the real page position, so preserve the current
        // floor-nav state until the page has been restored.
        if (pageScrollLocked) return;

        var floorNav = exploreMobileFloorNav;
        var viewportTop = getPageScrollTop();
        var viewportBottom = viewportTop + getPageViewportHeight();
        var navIsVisible = Boolean(exploreSection && exploreSectionTop < viewportBottom && exploreSectionBottom > viewportBottom);
        var activationPosition = viewportTop + exploreActivationLine;
        var activeIndex = 0;

        exploreFloorSectionTops.forEach(function (sectionTop, index) {
            if (sectionTop <= activationPosition) activeIndex = index;
        });

        if (floorNav && exploreFloorNavVisible !== navIsVisible) {
            exploreFloorNavVisible = navIsVisible;
            floorNav.classList.toggle('is-visible', navIsVisible);
            document.body.classList.toggle('explore-floor-nav-visible', navIsVisible);
        }
        if (Number($exploreFloorSelector.attr('data-current-floor')) !== activeIndex + 1) {
            syncExploreFloorSelector(activeIndex);
        }
    }

    function requestExploreMobileFloorSync() {
        if (exploreMobileFloorScrollFrame !== null) return;
        exploreMobileFloorScrollFrame = window.requestAnimationFrame(syncExploreMobileFloorOnScroll);
    }

    function setExploreTabState(targetTab, animate) {
        var $targetContent = $exploreContentTabs.filter('[data-tab="' + targetTab + '"]');
        var oldIndex = $exploreContentTabs.index($exploreContentTabs.filter('.active').first());
        var newIndex = $exploreContentTabs.index($targetContent);
        if (newIndex < 0) return;

        $exploreContentTabs.not($targetContent).find('.wonom-lazy-media').each(function () {
            resetWonomLazyVideo(this);
        });

        var $transitionTabs = $targetContent;
        if (oldIndex >= 0) $transitionTabs = $transitionTabs.add($exploreContentTabs.eq(oldIndex));

        window.clearTimeout(exploreTabTransitionTimer);
        $exploreContentTabs.removeClass('is-transitioning');
        if (animate) $transitionTabs.addClass('is-transitioning');

        // Đổi trạng thái tab sidebar
        $('.home_explore_sidebar_tab_item').removeClass('active');
        $('.home_explore_sidebar_tab_item[data-tab="' + targetTab + '"]').addClass('active');
        syncExploreFloorSelector(newIndex);

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
        var targetTab = $(this).attr('data-tab');
        var floorMatch = targetTab && targetTab.match(/^tab([1-5])f$/);
        if (floorMatch) playPianoNote(Number(floorMatch[1]) - 1);
        if ($(this).hasClass('active')) return;
        setExploreTabState(targetTab, true);
    });

    $exploreFloorControls.on('click', function () {
        var targetTab = $(this).attr('data-tab');
        if (targetTab) setExploreTabState(targetTab, true);
    });

    $('[data-explore-tab]').on('click keydown', function (event) {
        if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();

        var targetTab = $(this).attr('data-explore-tab');
        var targetIndex = $exploreContentTabs.index($exploreContentTabs.filter('[data-tab="' + targetTab + '"]'));
        if (targetIndex < 0) return;

        if (exploreMobileMedia.matches) {
            $('.home_explore_sidebar_tab_item').removeClass('active');
            $('.home_explore_sidebar_tab_item[data-tab="' + targetTab + '"]').addClass('active');
            syncExploreFloorSelector(targetIndex);

            var targetFloor = document.querySelector('.home_explore_floor_section[data-floor-section="' + targetTab + '"]');
            var mobileHeader = document.querySelector('header');
            var headerOffset = mobileHeader ? mobileHeader.getBoundingClientRect().height : 0;
            var targetTop = targetFloor
                ? getElementPageTop(targetFloor) - headerOffset
                : getElementPageTop(document.getElementById('explore'));

            window.history.replaceState(null, '', '#explore');
            scrollPageTo({ top: targetTop, behavior: 'smooth' });
            return;
        }

        setExploreTabState(targetTab, true);

        var sectionRequest = new CustomEvent('wonom:go-to-section', {
            cancelable: true,
            detail: { id: 'explore' }
        });
        var handledByFullpage = !window.dispatchEvent(sectionRequest);

        if (!handledByFullpage) {
            document.getElementById('explore').scrollIntoView({ behavior: 'smooth', block: 'start' });
            window.history.replaceState(null, '', '#explore');
        }
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
    addPageScrollListener(requestExploreMobileFloorSync);
    window.addEventListener('resize', function () {
        measureExploreMobileMetrics(false);
    }, { passive: true });
    window.addEventListener('load', function () {
        measureExploreMobileMetrics(true);
    });
    if (exploreSection && 'ResizeObserver' in window) {
        new ResizeObserver(function () {
            measureExploreMobileMetrics(true);
        }).observe(exploreSection);
    }
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function () {
            measureExploreMobileMetrics(true);
        });
    }
    measureExploreMobileMetrics(true);
    requestExploreMobileFloorSync();


    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#langWrapper').length) {
            $('#langWrapper').removeClass('active');
        }
    });
    let heroImageTransition = null;
    const initHeroImageTransition = () => {
        if (heroImageTransition || !window.WebGLImageTransition) return;
        heroImageTransition = new WebGLImageTransition({
            container: document.querySelector('.home_banner'),
            images: Array.from(document.querySelectorAll('.home_banner_image_item img')).map((image) => image.currentSrc || image.src),
            duration: 1100,
        });
    };

    // WebGL is visual enhancement only. Let the LCP image and controls render
    // first, then initialize GPU effects during idle time on capable devices.
    if (window.matchMedia('(min-width: 768px) and (prefers-reduced-motion: no-preference)').matches) {
        runWhenIdle(() => {
            loadScriptOnce(window.wonomHome?.webglTransitionUrl)
                .then(initHeroImageTransition)
                .catch(() => { });
        });
    }

    // Card hover shaders are desktop-only and do not belong on the critical
    // path. They self-initialize after their script has loaded.
    if (window.matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)').matches) {
        runWhenIdle(() => {
            loadScriptOnce(window.wonomHome?.imageHoverUrl).catch(() => { });
        }, 3500);
    }

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
        spaceBetween: parseRem(16),
        breakpoints: {
            992: {
                spaceBetween: parseRem(24),
            },
        },
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
            },
            slideChange: function (swiper) {
                swiper.slides.forEach(function (slide, index) {
                    if (index !== swiper.activeIndex) resetWonomLazyVideo(slide);
                });
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

    var homeTourData = [];
    var homeTourDataNode = document.getElementById('wonom-tour-data');
    var activeHomeTourIndex = 0;

    if (homeTourDataNode) {
        try {
            homeTourData = JSON.parse(homeTourDataNode.textContent) || [];
        } catch (error) {
            homeTourData = [];
        }
    }

    function updateHomeTourCard(index) {
        var tour = homeTourData[index];
        if (!tour) return;
        activeHomeTourIndex = index;
        $('.home_tour_card_title')
            .text(tour.title || '')
            .attr('aria-label', 'Xem chi tiết ' + (tour.title || 'tour'));
    }

    var homeDetailRequestIds = { tour: 0, event: 0 };
    var homeDetailControllers = { tour: null, event: null };

    function requestHomeDetail(type, index, $popup) {
        var config = window.wonomHome || {};
        if (!config.ajaxUrl || !config.detailNonce || !window.fetch) {
            return Promise.reject(new Error('AJAX detail is unavailable.'));
        }

        homeDetailRequestIds[type] += 1;
        var requestId = homeDetailRequestIds[type];
        if (homeDetailControllers[type]) homeDetailControllers[type].abort();
        var controller = window.AbortController ? new AbortController() : null;
        homeDetailControllers[type] = controller;
        $popup.addClass('is-detail-loading').attr('aria-busy', 'true');

        var body = new URLSearchParams();
        body.set('action', 'wonom_get_home_detail');
        body.set('nonce', config.detailNonce);
        body.set('lang', config.language || 'vi');
        body.set('detail_type', type);
        body.set('detail_index', String(index));

        return fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            if (!response.ok) throw new Error('Detail request failed.');
            return response.json();
        }).then(function (response) {
            if (!response || !response.success || !response.data) {
                throw new Error('Invalid detail response.');
            }
            if (requestId !== homeDetailRequestIds[type]) {
                throw new DOMException('Stale detail response.', 'AbortError');
            }
            return response.data;
        }).finally(function () {
            if (requestId === homeDetailRequestIds[type]) {
                $popup.removeClass('is-detail-loading').removeAttr('aria-busy');
            }
        });
    }

    function renderHomeTourPopup(index, responseData) {
        var tour = responseData && responseData.detail ? responseData.detail : homeTourData[index];
        var $popup = $('.popup_tour.tour');
        if (!tour || !$popup.length) return;

        activeHomeTourIndex = index;
        $popup.find('.popup_tour_sidebar_card_title, .popup_tour_content_title').text(tour.title || '');
        var $metaItems = $popup.find('.popup_tour_sidebar_card_meta').empty();
        (Array.isArray(tour.metaItems) ? tour.metaItems : []).forEach(function (metaItem) {
            if (!metaItem || (!metaItem.label && !metaItem.content)) return;
            var $metaItem = $('<div>', { class: 'popup_tour_sidebar_card_meta_item' });
            if (metaItem.label) {
                $('<div>', {
                    class: 'popup_tour_sidebar_card_meta_label txt_13 cl_meta',
                    text: metaItem.label
                }).appendTo($metaItem);
            }
            if (metaItem.content) {
                $('<div>', {
                    class: 'popup_tour_sidebar_card_meta_content txt_bold',
                    text: metaItem.content
                }).appendTo($metaItem);
            }
            $metaItem.appendTo($metaItems);
        });
        var isMobile = window.innerWidth <= 767;
        var popupDesktopSrc = tour.popupImage || tour.image || '';
        var $popupImgWrap = $popup.find('.popup_tour_content_img');
        var $popupImg = $popupImgWrap.find('img');
        var $popupSource = $popupImgWrap.find('source');

        if (tour.popupImageMobile) {
            if (!$popupSource.length && $popupImg.parent('picture').length) {
                $popupSource = $('<source media="(max-width: 767px)">').prependTo($popupImg.parent('picture'));
            } else if (!$popupSource.length) {
                $popupImg.wrap('<picture style="display:block;width:100%;height:100%;"></picture>');
                $popupSource = $('<source media="(max-width: 767px)">').prependTo($popupImg.parent('picture'));
            }
            $popupSource.attr('srcset', tour.popupImageMobile);
        } else if ($popupSource.length) {
            $popupSource.remove();
        }

        $popupImg.attr({
            src: isMobile && tour.popupImageMobile ? tour.popupImageMobile : popupDesktopSrc,
            alt: tour.popupImageAlt || tour.title || ''
        });

        var contentSections = [
            [tour.introTitle, tour.introContent],
            [tour.itineraryTitle, tour.itineraryContent],
            [tour.notesTitle, tour.notesContent],
            [tour.policyTitle, tour.policyContent]
        ];
        var $titles = $popup.find('.popup_tour_content_txt > .popup_tour_content_subtitle');
        var $bodies = $popup.find('.popup_tour_content_txt > .popup_tour_content_des');
        contentSections.forEach(function (section, sectionIndex) {
            $titles.eq(sectionIndex).text(section[0] || '');
            $bodies.eq(sectionIndex).html(section[1] || '');
        });
        $popup.find('.tour_detail_form [name="tour_name"]').val(tour.title || '');

        var suggestions = responseData && Array.isArray(responseData.suggestions)
            ? responseData.suggestions
            : homeTourData.map(function (suggestion, suggestionIndex) {
                return { index: suggestionIndex, image: suggestion.image, imageAlt: suggestion.imageAlt, title: suggestion.title };
            }).filter(function (suggestion) { return suggestion.index !== index; });
        var $suggestions = $popup.find('.popup_tour_sidebar_suggest').empty();
        suggestions.forEach(function (suggestion) {
            var $button = $('<button>', {
                class: 'popup_tour_sidebar_suggest_item',
                type: 'button',
                'data-tour-index': suggestion.index
            });
            $('<span>', { class: 'popup_tour_sidebar_suggest_item_img img_full' })
                .append($('<img>', {
                    src: suggestion.image || '',
                    alt: suggestion.imageAlt || suggestion.title || '',
                    loading: 'lazy',
                    decoding: 'async',
                    'data-wonom-popup-image': 'pending'
                }))
                .appendTo($button);
            $('<span>', { class: 'popup_tour_sidebar_suggest_item_title txt_bold txt_16 cl_dark_brown' })
                .text(suggestion.title || '')
                .appendTo($button);
            $suggestions.append($button);
        });
        $popup.find('.popup_tour_content').scrollTop(0);
    }

    function loadHomeTourPopup(index) {
        var $popup = $('.popup_tour.tour');
        return requestHomeDetail('tour', index, $popup)
            .then(function (responseData) {
                renderHomeTourPopup(responseData.index, responseData);
            })
            .catch(function (error) {
                if (error && 'AbortError' === error.name) return;
                renderHomeTourPopup(index);
            });
    }

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
                updateHomeTourCard(swiper.realIndex);
                if (homeTourImageTransition) {
                    homeTourImageTransition.goTo(swiper.realIndex);
                }
            },
        },
    });

    class Marquee {
        constructor(list, duration = 300, direction = 'left') {
            this.list = $(list);
            this.duration = duration;
            this.direction = direction;
            this.isPlaying = false;
            if (!this.list.find('.marquee-group').length) {
                this.list.children('.home_space_right_card_item').wrapAll('<div class="marquee-group"></div>');
            }
            this.item = this.list.find('.marquee-group');
            this.setup();
        }
        setup() {
            if (!this.list.length || !this.item.length) return;
            const isVertical = window.innerWidth >= 992;

            this.list.css({
                'display': 'flex',
                'flex-wrap': 'nowrap',
                'overflow': 'hidden',
                'flex-direction': isVertical ? 'column' : 'row',
                'gap': window.innerWidth <= 767 ? '1.8rem' : '2.4rem'
            });
            this.item.css({
                'display': 'flex',
                'flex-direction': isVertical ? 'column' : 'row',
                'flex-shrink': '0',
                'gap': window.innerWidth <= 767 ? '1.8rem' : '2.4rem'
            });

            let itemSize = isVertical ? this.item[0].scrollHeight : this.item[0].scrollWidth;
            let windowSize = isVertical ? $(window).height() : $(window).width();

            if (!itemSize || itemSize <= 0 || !windowSize || windowSize <= 0) {
                setTimeout(() => this.setup(), 200);
                return;
            }

            const cloneAmount = Math.max(2, Math.ceil(windowSize / itemSize) + 1);

            let itemClone = this.item.clone().removeClass('anim marquee-left marquee-right marquee-up marquee-down').css('animation-duration', '');
            this.list.empty();

            for (let i = 0; i < cloneAmount; i++) {
                let html = itemClone.clone();
                const marqueeSpeed = window.innerWidth <= 767 ? this.duration * 0.65 : this.duration;
                const animDuration = (itemSize / marqueeSpeed).toFixed(3);
                html.css({
                    'flex-shrink': '0',
                    'display': 'flex',
                    'animation-duration': `${animDuration}s`,
                    'animation-timing-function': 'linear',
                    'animation-iteration-count': 'infinite'
                });

                if (this.direction === 'left') {
                    html.addClass(isVertical ? 'marquee-up' : 'marquee-left');
                } else {
                    html.addClass(isVertical ? 'marquee-down' : 'marquee-right');
                }
                this.list.append(html);
            }
            this.play();
        }
        play() {
            this.list.find('.marquee-left, .marquee-right, .marquee-up, .marquee-down').addClass('anim');
            this.setPlaying(this.isPlaying);
        }
        setPlaying(shouldPlay) {
            this.isPlaying = shouldPlay;
            this.list.find('.marquee-left, .marquee-right, .marquee-up, .marquee-down').css({
                'animation-play-state': shouldPlay ? 'running' : 'paused',
                'will-change': shouldPlay ? 'transform' : 'auto'
            });
        }
    }

    const libraryMarquees = [
        new Marquee('.home_space_right_card.card1 .home_space_right_card_wrap', 145, 'left'),
        new Marquee('.home_space_right_card.card2 .home_space_right_card_wrap', 145, 'right')
    ];
    const librarySection = document.getElementById('library');
    const setLibraryMarqueePlayback = (shouldPlay) => {
        libraryMarquees.forEach((marquee) => marquee.setPlaying(shouldPlay));
    };

    if (librarySection && 'IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            setLibraryMarqueePlayback(entries.some((entry) => entry.isIntersecting));
        }, { rootMargin: '50% 0px' }).observe(librarySection);
    } else {
        setLibraryMarqueePlayback(true);
    }


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

    // pauseAutoplayOutsideViewport removed for CSS marquee

    const globalTopButton = document.querySelector('.global_btn_top.btn');
    if (globalTopButton) {
        let globalTopButtonVisible = null;
        const syncGlobalTopButton = () => {
            const isVisible = getPageScrollTop() > 10;
            if (globalTopButtonVisible === isVisible) return;
            globalTopButtonVisible = isVisible;
            globalTopButton.classList.toggle('is-visible', isVisible);
            globalTopButton.setAttribute('aria-hidden', String(!isVisible));
        };

        addPageScrollListener(syncGlobalTopButton);
        globalTopButton.addEventListener('click', function (event) {
            if (!usesPageScroller()) return;
            event.preventDefault();
            scrollPageTo({
                top: 0,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
            });
            window.history.replaceState(null, '', window.location.pathname + window.location.search + '#top');
        });
        syncGlobalTopButton();
    }

    // Make the hero piano react to the horizontal mouse position.
    const hero = document.querySelector('.home_banner');
    const piano = hero && hero.querySelector('.home_piano');
    const pianoItems = piano ? Array.from(piano.querySelectorAll('.home_piano_item')) : [];
    const pianoPointerMedia = window.matchMedia('(hover: hover) and (pointer: fine)');
    const pianoReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const pianoAssetBase = window.wonomHome?.assetUrl || '/asset/';
    const pianoFrequencies = [261.63, 293.66, 329.63, 349.23, 392];
    const pianoSoundFiles = [
        'audio/piano-c4.wav',
        'audio/piano-d4.wav',
        'audio/piano-e4.wav',
        'audio/piano-f4.wav',
        'audio/piano-g4.wav'
    ];
    const pianoAudioNotice = document.querySelector('[data-piano-audio-notice]');
    const pianoAudioNoticeClose = pianoAudioNotice?.querySelector('[data-piano-audio-notice-close]');
    const pianoSounds = new Map();
    const getPianoFallbackSound = (index) => {
        if (!pianoSoundFiles[index]) return null;
        if (!pianoSounds.has(index)) {
            const audio = new Audio(pianoAssetBase + pianoSoundFiles[index]);
            audio.preload = 'auto';
            pianoSounds.set(index, audio);
        }
        return pianoSounds.get(index);
    };
    let pianoAudioContext = null;

    const showPianoAudioNotice = () => {
        if (!pianoAudioNotice || !pianoPointerMedia.matches) return;
        pianoAudioNotice.classList.add('is-visible');
        pianoAudioNotice.setAttribute('aria-hidden', 'false');
    };

    const hidePianoAudioNotice = () => {
        if (!pianoAudioNotice) return;
        pianoAudioNotice.classList.remove('is-visible');
        pianoAudioNotice.setAttribute('aria-hidden', 'true');
    };

    pianoAudioNoticeClose?.addEventListener('click', hidePianoAudioNotice);

    const getPianoAudioContext = () => {
        if (!pianoAudioContext) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) pianoAudioContext = new AudioContextClass();
        }
        return pianoAudioContext;
    };

    const synthesizePianoNote = (context, frequency) => {
        const startTime = context.currentTime;
        const fundamental = context.createOscillator();
        const fundamentalGain = context.createGain();
        const harmonic = context.createOscillator();
        const harmonicGain = context.createGain();

        fundamental.type = 'triangle';
        fundamental.frequency.setValueAtTime(frequency, startTime);
        fundamentalGain.gain.setValueAtTime(.4, startTime);
        fundamentalGain.gain.exponentialRampToValueAtTime(.001, startTime + 1.2);

        harmonic.type = 'sine';
        harmonic.frequency.setValueAtTime(frequency * 2, startTime);
        harmonicGain.gain.setValueAtTime(.12, startTime);
        harmonicGain.gain.exponentialRampToValueAtTime(.001, startTime + .8);

        fundamental.connect(fundamentalGain).connect(context.destination);
        harmonic.connect(harmonicGain).connect(context.destination);
        fundamental.start(startTime);
        fundamental.stop(startTime + 1.2);
        harmonic.start(startTime);
        harmonic.stop(startTime + .8);
    };

    const playPianoNote = (index) => {
        const context = getPianoAudioContext();
        const frequency = pianoFrequencies[index];
        if (!context || !frequency) return;

        const play = () => synthesizePianoNote(context, frequency);
        const playFallback = () => {
            const fallbackSound = getPianoFallbackSound(index);
            if (!fallbackSound) return;
            fallbackSound.currentTime = 0;
            fallbackSound.play().catch(function () { });
        };

        if (context.state !== 'running') {
            context.resume().then(function () {
                if (context.state === 'running') play();
                else playFallback();
            }).catch(playFallback);
        } else {
            play();
        }
    };

    const unlockPianoAudio = () => {
        const context = getPianoAudioContext();
        if (!context) return Promise.resolve(false);
        if (context.state === 'running') {
            hidePianoAudioNotice();
            return Promise.resolve(true);
        }
        return context.resume().then(function () {
            const unlocked = context.state === 'running';
            if (unlocked) hidePianoAudioNotice();
            return unlocked;
        }).catch(function () {
            return false;
        });
    };

    const preparePianoAudio = () => {
        pianoSoundFiles.forEach(function (_, index) {
            const audio = getPianoFallbackSound(index);
            if (audio) audio.load();
        });
        unlockPianoAudio();
    };

    const checkPianoAudioAfterLoad = () => {
        preparePianoAudio();
        window.setTimeout(function () {
            const context = getPianoAudioContext();
            if (context && context.state !== 'running') showPianoAudioNotice();
        }, 400);
    };

    if (document.readyState === 'complete') checkPianoAudioAfterLoad();
    else window.addEventListener('load', checkPianoAudioAfterLoad, { once: true });

    const handlePianoAudioActivation = () => {
        unlockPianoAudio().then(function (unlocked) {
            if (!unlocked) return;
            document.removeEventListener('pointerdown', handlePianoAudioActivation, true);
            document.removeEventListener('keydown', handlePianoAudioActivation, true);
        });
    };

    document.addEventListener('pointerdown', handlePianoAudioActivation, true);
    document.addEventListener('keydown', handlePianoAudioActivation, true);

    if (hero && piano && pianoItems.length) {
        let pianoFrame = 0;
        let pointerClientX = 0;
        let pianoBaseHeight = 0;
        let pianoMaxHeight = 0;

        const measurePiano = () => {
            pianoItems.forEach((item) => item.style.removeProperty('--piano-height'));
            pianoBaseHeight = parseFloat(window.getComputedStyle(pianoItems[0]).height) || 24;
            pianoMaxHeight = Math.max(
                pianoBaseHeight,
                Math.min(parseRem(64), window.innerHeight * 0.064)
            );
        };

        const resetPiano = () => {
            if (pianoFrame) window.cancelAnimationFrame(pianoFrame);
            pianoFrame = 0;
            pianoItems.forEach((item) => item.style.removeProperty('--piano-height'));
        };

        const renderPiano = () => {
            pianoFrame = 0;
            const bounds = piano.getBoundingClientRect();
            const itemWidth = bounds.width / pianoItems.length;
            const pointerX = Math.max(0, Math.min(bounds.width, pointerClientX - bounds.left));
            const influenceRadius = 2.2;

            pianoItems.forEach((item, index) => {
                const itemCenter = (index + 0.5) * itemWidth;
                const distance = Math.abs(pointerX - itemCenter) / itemWidth;
                const normalizedDistance = Math.min(distance / influenceRadius, 1);
                const influence = (1 + Math.cos(Math.PI * normalizedDistance)) / 2;
                const height = pianoBaseHeight + ((pianoMaxHeight - pianoBaseHeight) * influence);

                item.style.setProperty('--piano-height', height.toFixed(2) + 'px');
            });
        };

        const handlePianoPointerMove = (event) => {
            if (!pianoPointerMedia.matches || pianoReducedMotion.matches) return;
            pointerClientX = event.clientX;
            if (!pianoFrame) pianoFrame = window.requestAnimationFrame(renderPiano);
        };

        pianoItems.forEach(function (item, index) {
            item.addEventListener('mouseenter', function () {
                if (!pianoPointerMedia.matches) return;
                playPianoNote(index);
            });
            item.addEventListener('click', function () {
                playPianoNote(index);
            });
        });

        measurePiano();
        hero.addEventListener('pointermove', handlePianoPointerMove, { passive: true });
        hero.addEventListener('pointerleave', resetPiano);
        window.addEventListener('resize', function () {
            resetPiano();
            measurePiano();
        }, { passive: true });
        pianoPointerMedia.addEventListener('change', resetPiano);
        pianoReducedMotion.addEventListener('change', resetPiano);
    }

    $('.global_btn_list_item').hover(
        function () {
            var text = $(this).attr('data-label') || '';
            var $txtEl = null;
            if ($(this).hasClass('item1')) {
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold txt_uppercase"></div>').text(text);
                $(this).append($txtEl);
            } else if ($(this).hasClass('item2')) {
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold cl_cream txt_uppercase"></div>').text(text);
                $(this).append($txtEl);
            } else if ($(this).hasClass('item3')) {
                $(this).find('.global_btn_list_item_txt').remove();
                $txtEl = $('<div class="global_btn_list_item_txt txt_14 txt_extrabold cl_cream txt_uppercase"></div>').text(text);
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
    $('.home_tour_card_detail_see, .home_tour_card_title').on('click keydown', function (event) {
        if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();
        var $tourPopup = $('.popup_tour.tour');
        openSlidingPopup($tourPopup);
        loadHomeTourPopup(activeHomeTourIndex);
    });

    $('.popup_tour.tour').on('click', '.popup_tour_sidebar_suggest_item[data-tour-index]', function () {
        var tourIndex = Number($(this).attr('data-tour-index'));
        if (!Number.isFinite(tourIndex)) return;
        loadHomeTourPopup(tourIndex);
        updateHomeTourCard(tourIndex);
        if (homeTourSwiper) homeTourSwiper.slideTo(tourIndex);
    });

    $('.tour .popup_tour_close').click(function () {
        closeSlidingPopup($('.popup_tour.tour'));
    });

    $('.popup_tour.tour .popup_tour_sidebar_card_button').click(function () {
        var $tourPopup = $(this).closest('.popup_tour.tour');
        var $content = $tourPopup.find('.popup_tour_content');
        var $form = $tourPopup.find('.tour_detail_form');
        var $scrollContainer = $content;
        var tourName = $tourPopup.find('.popup_tour_content_title').first().text().replace(/\s+/g, ' ').trim();

        if (!$content.length || !$form.length) return;

        if ($content.css('overflow-y') === 'visible') {
            $scrollContainer = $tourPopup;
        }

        $form.find('[name="tour_name"]').val(tourName);
        $scrollContainer.stop().animate({
            scrollTop: $scrollContainer.scrollTop() + $form.offset().top - $scrollContainer.offset().top
        }, 500);
    });

    $('.popup_tour.tour .tour_detail_form').on('submit', function (event) {
        // Giữ dữ liệu form để kết nối API/Zalo ở bước backend.
        event.preventDefault();
    });

    var eventPopupData = [];
    var eventPopupDataNode = document.getElementById('wonom-event-data');
    if (eventPopupDataNode) {
        try {
            eventPopupData = JSON.parse(eventPopupDataNode.textContent) || [];
        } catch (error) {
            eventPopupData = [];
        }
    }

    function renderEventPopupList(activeIndex, responseSuggestions) {
        var $list = $('.popup_tour.event .popup_tour_sidebar_suggest').empty();
        var suggestions = Array.isArray(responseSuggestions)
            ? responseSuggestions
            : eventPopupData.map(function (item, index) {
                return { index: index, image: item.image, imageAlt: item.imageAlt, title: item.title };
            }).filter(function (item) { return item.index !== activeIndex; });
        suggestions.forEach(function (item) {
            var $button = $('<button>', {
                class: 'popup_tour_sidebar_suggest_item',
                type: 'button',
                'data-event-index': item.index
            });
            $('<span>', { class: 'popup_tour_sidebar_suggest_item_img img_full' })
                .append($('<img>', {
                    src: item.image || '',
                    alt: item.imageAlt || item.title || '',
                    loading: 'lazy',
                    decoding: 'async',
                    'data-wonom-popup-image': 'pending'
                }))
                .appendTo($button);
            $('<span>', { class: 'popup_tour_sidebar_suggest_item_title txt_bold txt_16 cl_dark_brown' })
                .text(item.title || '')
                .appendTo($button);
            $list.append($button);
        });
    }

    function showEventPopupItem(index, responseData) {
        var item = responseData && responseData.detail
            ? responseData.detail
            : (eventPopupData[index] || eventPopupData[0]);
        var $popup = $('.popup_tour.event');
        if (!item) return;

        $popup.find('.event_popup_sidebar_card_title').text(item.title);
        $popup.find('.event_popup_apply').text(item.apply);
        $popup.find('.event_popup_category').text(item.category);
        $popup.find('.event_popup_condition').text(item.condition);
        var eventImage = $popup.find('.event_popup_content_img img').get(0);
        setPopupImageSource(eventImage, item.popupImage || item.image || '');
        if (eventImage) eventImage.alt = item.popupImageAlt || item.title || '';
        $popup.find('.event_popup_content_title').text(item.title);
        $popup.find('.event_popup_content_subtitle').text(item.subtitle);
        $popup.find('.popup_tour_content_des').html(item.content || '');
        renderEventPopupList(index, responseData ? responseData.suggestions : null);
        $popup.find('.popup_tour_content').scrollTop(0);
    }

    function loadHomeEventPopup(index) {
        var $popup = $('.popup_tour.event');
        return requestHomeDetail('event', index, $popup)
            .then(function (responseData) {
                showEventPopupItem(responseData.index, responseData);
            })
            .catch(function (error) {
                if (error && 'AbortError' === error.name) return;
                showEventPopupItem(index);
            });
    }

    renderEventPopupList(0);

    $('.home_event_card_item').click(function () {
        var index = Number($(this).attr('data-event-index'));
        openSlidingPopup($('.popup_tour.event'));
        loadHomeEventPopup(index);
    });

    $('.home_event_card_item').keydown(function (event) {
        if ('Enter' !== event.key && ' ' !== event.key) return;
        event.preventDefault();
        $(this).trigger('click');
    });

    $('.popup_tour.event').on('click', '[data-event-index]', function () {
        loadHomeEventPopup(Number($(this).attr('data-event-index')));
    });

    $('.event .popup_tour_close').click(function () {
        closeSlidingPopup($('.popup_tour.event'));
    });
    $('.footer_bot_right_txt, .footer_bot_right').on('click keydown', function (event) {
        if ('keydown' === event.type && 'Enter' !== event.key && ' ' !== event.key) return;
        event.preventDefault();
        openSlidingPopup($('.popup_tour.policy'));
    });

    $('[data-footer-href]').on('click keydown', function (event) {
        if ('keydown' === event.type && 'Enter' !== event.key && ' ' !== event.key) return;
        event.preventDefault();
        var href = $(this).attr('data-footer-href');
        if (!href) return;
        if (href.indexOf('tel:') === 0) window.location.href = href;
        else window.open(href, '_blank', 'noopener,noreferrer');
    });

    $('.policy .popup_tour_close').click(function () {
        closeSlidingPopup($('.popup_tour.policy'));
    });

    $('.popup_tour.policy').on('click', '.popup_tour_policy_card_item', function () {
        var targetIndex = $(this).attr('data-policy-target');
        var $popup = $('.popup_tour.policy');
        $popup.find('.popup_tour_policy_card_item').removeClass('is-active');
        $(this).addClass('is-active');
        var $target = $popup.find('[data-policy-section="' + targetIndex + '"]');
        var $content = $popup.find('.popup_tour_content');
        if ($target.length) {
            if (window.innerWidth <= 991) {
                var popupScroll = $popup.scrollTop();
                var targetOffset = $target.offset().top - $popup.offset().top + popupScroll;
                var headHeight = $popup.find('.popup_tour_head').outerHeight() || 60;
                $popup.stop().animate({ scrollTop: targetOffset - headHeight - 16 }, 300);
            } else if ($content.length) {
                var currentScroll = $content.scrollTop();
                var targetOffset = $target.position().top;
                $content.stop().animate({ scrollTop: currentScroll + targetOffset - 10 }, 300);
            }
        }
    });
    var restaurantPageFlip = null;
    var restaurantMenuPages = [];
    var activeExplorePopup = null;
    var exploreGalleryMobileMedia = window.matchMedia('(max-width: 767px)');
    var defaultExplorePopupData = {
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

    var explorePopupData = Object.assign(
        {},
        defaultExplorePopupData,
        window.wonomHome && window.wonomHome.explorePopupData ? window.wonomHome.explorePopupData : {}
    );

    function escapeExploreHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    Object.keys(explorePopupData).forEach(function (key) {
        var config = explorePopupData[key];
        config.menuPages = (config.menuPages || []).map(resolveWonomAsset);
        config.galleryImages = (config.galleryImages || []).map(function (image) {
            if (typeof image === 'string') return resolveWonomAsset(image);
            return Object.assign({}, image, { src: resolveWonomAsset(image.src) });
        });
    });

    function renderExplorePopupTabs(config) {
        var tabs;

        if (config.galleryOnly) {
            tabs = [];
        } else if (config.galleryFilters) {
            tabs = config.galleryFilters.map(function (filter, index) {
                return '<button class="restaurant_popup_tab gallery_filter_tab btn bg_white' + (index === 0 ? ' active' : '') + '"' +
                    ' type="button" role="tab" aria-selected="' + (index === 0 ? 'true' : 'false') + '"' +
                    ' data-gallery-filter="' + escapeExploreHtml(filter.value) + '"><span class="btn-inner txt_13 txt_extrabold">' + escapeExploreHtml(filter.label) + '</span></button>';
            });
        } else {
            tabs = [
                '<button class="restaurant_popup_tab btn bg_white" type="button" role="tab" aria-selected="false" data-restaurant-view="menu">' +
                '<span class="btn-inner restaurant_popup_menu_label txt_14 txt_extrabold">' + escapeExploreHtml(config.menuLabel) + '</span></button>',
                '<button class="restaurant_popup_tab btn bg_white" type="button" role="tab" aria-selected="false" data-restaurant-view="gallery">' +
                '<span class="btn-inner txt_14 txt_extrabold">' + escapeExploreHtml(((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.photo_gallery) || 'THƯ VIỆN ẢNH')) + '</span></button>'
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
                ' data-menu-page="' + index + '" aria-label="' + escapeExploreHtml((((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.page) || 'Trang {number}').replace('{number}', pageNumber))) + '">' +
                '<span class="restaurant_menu_thumb_img"><img data-wonom-popup-image="pending" loading="lazy" decoding="async" src="' + escapeExploreHtml(src) + '" alt="' + escapeExploreHtml((((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.floor_page_alt) || 'Nội dung tầng - trang {number}').replace('{number}', pageNumber))) + '"></span>' +
                '<span class="restaurant_menu_thumb_number txt_14 txt_bold">' + pageNumber + '</span></button>'
            );
        });
    }

    function renderExploreGallery(images) {
        var columnCount = exploreGalleryMobileMedia.matches ? 2 : 4;
        var columns = Array.from({ length: columnCount }, function () { return []; });

        images.forEach(function (item, index) {
            var src = typeof item === 'string' ? item : item.src;
            var type = typeof item === 'string' ? 'image' : (item.type || 'image');
            var category = typeof item === 'string' ? 'all' : item.category;
            var alt = typeof item === 'string' ? '' : (item.alt || '');
            var tag = typeof item === 'string' ? '' : (item.tag || '');
            var thumbnail = typeof item === 'string' ? src : (item.thumbnail || '');
            var media;
            var tagMarkup = tag
                ? '<div class="popup_tour_seeall_list_item_img_tag txt_14 txt_bold">' + escapeExploreHtml(tag) + '</div>'
                : '';

            if (type === 'video' || type === 'youtube') {
                media = (thumbnail
                    ? '<img class="wonom-lazy-video-thumbnail" data-wonom-popup-image="pending" loading="lazy" decoding="async" src="' + escapeExploreHtml(thumbnail) + '" alt="' + escapeExploreHtml(alt) + '">'
                    : '') +
                    '<button class="wonom-lazy-video-play" type="button" data-wonom-video-play="true" data-video-type="' + escapeExploreHtml(type) + '" data-video-src="' + escapeExploreHtml(src) + '" aria-label="' + escapeExploreHtml(alt || 'Phát video') + '"><span aria-hidden="true"></span></button>';
            } else {
                media = '<img data-wonom-popup-image="pending" loading="lazy" decoding="async" src="' + escapeExploreHtml(src) + '" alt="' + escapeExploreHtml(alt) + '">';
            }

            columns[index % columns.length].push(
                '<div class="popup_tour_seeall_list_item_img img-hover is-' + escapeExploreHtml(type) + '" data-gallery-category="' + escapeExploreHtml(category || 'all') + '">' +
                '<div class="popup_tour_seeall_list_item_img_inner img_abs wonom-lazy-media">' +
                '<div class="popup_tour_seeall_list_item_img_block"></div>' +
                media + '</div>' +
                tagMarkup + '</div>'
            );
        });

        $('.restaurant_gallery .popup_tour_seeall_list').html(columns.map(function (items) {
            return '<div class="popup_tour_seeall_list_item">' + items.join('') + '</div>';
        }).join(''));

        var activeFilter = $('.restaurant_popup_tab.gallery_filter_tab.active').attr('data-gallery-filter') || 'all';
        $('.restaurant_gallery [data-gallery-category]').each(function () {
            var categories = ($(this).attr('data-gallery-category') || '').split(/\s+/);
            var shouldShow = activeFilter === 'all' || categories.indexOf(activeFilter) !== -1;
            $(this).toggle(shouldShow);
        });
    }

    function resetWonomLazyVideo(container) {
        if (!container) return;
        container.querySelectorAll('.wonom-lazy-video-player').forEach(function (player) {
            if (player.tagName === 'VIDEO') player.pause();
            player.remove();
        });
        container.classList.remove('is-playing');
    }

    function stopExploreGalleryMedia() {
        $('.restaurant_gallery .wonom-lazy-media').each(function () {
            resetWonomLazyVideo(this);
        });
    }

    $(document).on('click', '[data-wonom-video-play]', function () {
        var button = this;
        var container = button.closest('.wonom-lazy-media');
        var type = button.getAttribute('data-video-type');
        var src = button.getAttribute('data-video-src');
        var player;
        if (!container || !src || container.classList.contains('is-playing')) return;

        if (type === 'youtube') {
            player = document.createElement('iframe');
            player.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
            player.title = button.getAttribute('aria-label') || 'YouTube video';
            player.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            player.referrerPolicy = 'strict-origin-when-cross-origin';
            player.allowFullscreen = true;
        } else {
            player = document.createElement('video');
            player.src = src;
            player.controls = true;
            player.playsInline = true;
            player.autoplay = true;
            player.preload = 'metadata';
        }

        player.className = 'wonom-lazy-video-player';
        container.classList.add('is-playing');
        container.appendChild(player);
        if (player.tagName === 'VIDEO') player.play().catch(function () { });
    });

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
            image.dataset.wonomPopupImage = 'pending';
            image.loading = 'lazy';
            image.decoding = 'async';
            image.src = src;
            image.alt = (((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.floor_page_alt) || 'Nội dung tầng - trang {number}').replace('{number}', index + 1));
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
            bookElement.innerHTML = '<img class="restaurant_flipbook_fallback" data-wonom-popup-image="pending" decoding="async" src="' + escapeExploreHtml(restaurantMenuPages[0]) + '" alt="' + escapeExploreHtml((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.floor_content) || 'Nội dung tầng') + '">';
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

    function loadRestaurantFlipbook() {
        if (window.St && window.St.PageFlip) {
            initRestaurantFlipbook();
            return;
        }

        loadScriptOnce(window.wonomHome?.pageFlipUrl)
            .then(initRestaurantFlipbook)
            .catch(() => { });
    }

    function syncRestaurantPopupLayout($restaurant) {
        var restaurant = $restaurant && $restaurant[0];
        var head = restaurant && restaurant.querySelector('.popup_tour_head');
        if (!restaurant || !head) return;

        var headHeight = head.getBoundingClientRect().height;
        restaurant.style.setProperty('--restaurant-popup-head-height', headHeight + 'px');
    }

    function openRestaurantPopup(view, popupKey) {
        if (!applyExplorePopupData(popupKey)) return;

        var $restaurant = $('.popup_tour.restaurant');
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

        openSlidingPopup($restaurant);
        syncRestaurantPopupLayout($restaurant);
        window.requestAnimationFrame(function () {
            syncRestaurantPopupLayout($restaurant);
        });

        if (view === 'menu') {
            requestAnimationFrame(function () {
                requestAnimationFrame(loadRestaurantFlipbook);
            });
        }
    }

    $('[data-explore-popup]').click(function () {
        openRestaurantPopup($(this).attr('data-explore-view'), $(this).attr('data-explore-popup'));
    });

    var restaurantLayoutFrame;
    $(window).on('resize orientationchange', function () {
        var $restaurant = $('.popup_tour.restaurant.active');
        if (!$restaurant.length) return;

        window.cancelAnimationFrame(restaurantLayoutFrame);
        restaurantLayoutFrame = window.requestAnimationFrame(function () {
            syncRestaurantPopupLayout($restaurant);
        });
    });

    $('.restaurant_popup_tabs').on('click', '[data-restaurant-view]', function () {
        var view = $(this).data('restaurant-view');
        var $restaurant = $(this).closest('.popup_tour.restaurant');

        $restaurant.find('.restaurant_popup_tab').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $restaurant.find('.restaurant_popup_panel').removeClass('active');
        $restaurant.find('[data-restaurant-panel="' + view + '"]').addClass('active');

        if (view === 'menu') {
            stopExploreGalleryMedia();
            requestAnimationFrame(function () {
                requestAnimationFrame(loadRestaurantFlipbook);
            });
        }
    });

    $('.restaurant_popup_tabs').on('click', '[data-gallery-filter]', function () {
        var filter = $(this).attr('data-gallery-filter');

        $('.restaurant_popup_tab').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $('.restaurant_gallery [data-gallery-category]').each(function () {
            var categories = ($(this).attr('data-gallery-category') || '').split(/\s+/);
            var shouldShow = filter === 'all' || categories.indexOf(filter) !== -1;
            if (!shouldShow) resetWonomLazyVideo(this.querySelector('.wonom-lazy-media'));
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
        stopExploreGalleryMedia();
        closeSlidingPopup($('.popup_tour.restaurant'));
    });
    $('[data-booking-trigger]').click(function (event) {
        event.preventDefault();

        var $trigger = $(this);
        var $popup = $('.popup_form_booking');
        var $form = $popup.find('.popup_form_booking_form');
        var bookingType = $trigger.attr('data-booking-type');
        var subtitle = $trigger.attr('data-booking-subtitle');
        var productName = ($trigger.attr('data-booking-product-name') || '').replace(/\s+/g, ' ').trim();
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
        $form.find('[name="product_name"]').val(productName);
        $form.find('[data-booking-product-display]').text(productName);
        $form.find('.popup_form_booking_product_group').prop('hidden', !productName);
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
            var categories = ($(this).attr('data-workshop-category') || '').split(/\s+/);
            var shouldShow = category === 'all' || categories.indexOf(category) !== -1;
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
        setPopupImageSource(
            document.querySelector('.workshop_detail_main_image > img'),
            workshopDetailImages[workshopDetailImageIndex]
        );
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
                '<button class="workshop_detail_thumb swiper-slide' + (index === 0 ? ' active' : '') + '" type="button" data-detail-image="' + index + '" aria-label="' + escapeExploreHtml((((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.view_image) || 'Xem ảnh {number}').replace('{number}', index + 1))) + '">' +
                '<img data-wonom-popup-image="pending" loading="lazy" decoding="async" src="' + escapeExploreHtml(src) + '" alt="' + escapeExploreHtml((((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.workshop_image) || 'Ảnh workshop {number}').replace('{number}', index + 1))) + '"></button>'
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
                observeParents: true,
            });
        } else {
            workshopDetailThumbSwiper.update();
            workshopDetailThumbSwiper.slideTo(0, 0);
        }
    }

    function openWorkshopDetail($card) {
        var productId = $card.attr('data-product-id');
        var productName = $card.find('.workshop_card_title').text().replace(/\s+/g, ' ').trim();
        var mainImage = $card.find('.workshop_card_image img').attr('src');
        var configuredDetailImages = [];

        try {
            configuredDetailImages = JSON.parse($card.attr('data-detail-images') || '[]');
        } catch (error) {
            configuredDetailImages = [];
        }

        destroyWorkshopDetailImageTransition();

        workshopDetailImages = configuredDetailImages.length
            ? [mainImage].concat(configuredDetailImages).map(resolveWonomAsset)
            : [
                mainImage,
                '/asset/img/store.jpg',
                '/asset/img/store.jpg',
                '/asset/img/img_popup.webp',
                '/asset/img/store.jpg',
                '/asset/img/store.jpg'
            ].map(resolveWonomAsset);
        workshopDetailImageIndex = 0;
        $('.workshop_detail_tag').text($card.find('.workshop_card_tag').text());
        $('.workshop_detail_title').text(productName);
        $('.workshop_detail_description').text($card.find('.workshop_card_description').text());
        $('.workshop_detail_price').text($card.find('.workshop_card_price').text());
        $('.workshop_detail_section').eq(0).find('.workshop_detail_section_content')
            .html($card.attr('data-detail-content') || '');
        $('.workshop_detail_section').eq(1).find('.workshop_detail_section_content')
            .html($card.attr('data-detail-includes') || '');
        if ($card.attr('data-detail-duration')) {
            $('.workshop_detail_price_row .cl_meta').text('/ ' + $card.attr('data-detail-duration'));
        }
        const uiStrings = (window.wonomHome && window.wonomHome.i18n) || {};
        $('.workshop_detail_booking').text(uiStrings.book_via_zalo || 'ĐẶT LỊCH ONLINE');
        $('.workshop_detail_note').text(uiStrings.zalo_note || 'Yêu cầu của bạn đã được ghi nhận. Nhân viên sẽ sớm liên hệ xác nhận.');
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
    }

    $('.workshop_card').each(function () {
        var $card = $(this);
        var productName = $card.find('.workshop_card_title').text().replace(/\s+/g, ' ').trim();
        $card.attr({
            role: 'button',
            tabindex: '0',
            'aria-label': ((window.wonomHome && window.wonomHome.i18n && window.wonomHome.i18n.view_details) || 'Xem chi tiết') + ' ' + productName
        });
    }).on('click', function () {
        openWorkshopDetail($(this));
    }).on('keydown', function (event) {
        if (event.target !== this || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        openWorkshopDetail($(this));
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
        $('.popup_member_confirm').addClass('is-visible').attr('aria-hidden', 'false');
        window.setTimeout(function () {
            $('.popup_member_confirm_cancel').trigger('focus');
        }, 0);
    });
    $('[data-member-confirm-cancel]').click(function () {
        $('.popup_member_confirm').removeClass('is-visible').attr('aria-hidden', 'true');
        $('.popup_member_back').trigger('focus');
    });
    $('[data-member-confirm-leave]').click(function () {
        $('.popup_member_confirm').removeClass('is-visible').attr('aria-hidden', 'true');
        $('.popup_member').removeClass('active').attr('aria-hidden', 'true');
    });
    $(document).keydown(function (event) {
        if (event.key === 'Escape' && $('.popup_member_confirm').hasClass('is-visible')) {
            event.preventDefault();
            $('.popup_member_confirm').removeClass('is-visible').attr('aria-hidden', 'true');
            $('.popup_member_back').trigger('focus');
        }
    });
    $('.header_button_member').click(function (event) {
        event.preventDefault();
        $('.popup_member_confirm').removeClass('is-visible').attr('aria-hidden', 'true');
        $('.popup_member').addClass('active').attr('aria-hidden', 'false');
    });
});
