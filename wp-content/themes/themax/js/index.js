const mainScript = () => {
    // ── Animation Utilities from animation.js ──
    const parseRem = (input) => {
        return (input / 10) * parseFloat(getComputedStyle(document.querySelector('html')).fontSize)
    }
    const playVideoSafely = (video) => {
        if (!video) return;
        const events = ['touchstart', 'mousedown', 'keydown', 'scroll'];
        const tryPlay = () => {
            const playPromise = video.play();
            if (playPromise !== undefined && typeof playPromise.then === 'function') {
                playPromise
                    .then(() => {
                        cleanup();
                    })
                    .catch(err => {
                        console.log("Play failed on interaction:", err);
                    });
            } else {
                cleanup();
            }
        };
        const cleanup = () => {
            events.forEach(event => {
                document.removeEventListener(event, tryPlay, { passive: true });
            });
        };
        const playPromise = video.play();
        if (playPromise !== undefined && typeof playPromise.then === 'function') {
            playPromise
                .then(() => {
                    // Play succeeded immediately
                })
                .catch(err => {
                    console.log("Autoplay blocked, adding interaction listeners for video:", video);
                    events.forEach(event => {
                        document.addEventListener(event, tryPlay, { passive: true });
                    });
                });
        } else {
            console.log("Video play did not return a promise.");
        }
    };
    function getScreenType() {
        const width = window.innerWidth;
        let type = width > 991 ? 'dsk' : window.innerWidth > 767 ? 'tb' : 'mb';
        let size = width;
        const isMobile = width <= 767;
        const isTablet = width > 767 && width <= 991;
        const isDesktop = width > 991;
        return { type, size, isMobile, isDesktop, isTablet };
    }
    function convertHyphenDOM(el) {
        el.childNodes.forEach((node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                node.nodeValue = node.nodeValue.replace(/-/g, '‑');
            }
        });
    }
    class MasterTimeline {
        constructor({ triggerInit, timeline, tweenArr, stagger = .15, scrollTrigger, allowMobile = true }) {
            this.allowMobile = allowMobile;
            if (getScreenType().isMobile && !this.allowMobile) return;
            this.timeline = timeline;
            this.triggerInit = triggerInit;
            this.scrollTrigger = scrollTrigger;
            this.tweenArr = tweenArr;
            this.stagger = stagger;

            // Defer all animation initialization and setup until the element is within 100vh of the viewport
            this.deferTrigger = ScrollTrigger.create({
                trigger: this.triggerInit,
                start: 'top bottom+=100vh',
                once: true,
                onEnter: () => {
                    document.fonts.ready.then(() => {
                        if (this.tweenArr) {
                            this.tweenArr.forEach((item) => item.init?.());
                        }
                        this.setup();
                    });
                }
            });
        }
        setup() {
            if (!this.timeline) {
                this.timeline = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.triggerInit,
                        start: 'top top+=70%',
                        end: '+=100%',
                        scrub: false,
                        once: true,
                        ...this.scrollTrigger
                    }
                })
            };
            if (this.tweenArr) {
                this.tweenArr.forEach((item) => {
                    if (item.animation) {
                        this.timeline.add(item.animation, item.delay || `<=${this.stagger}` || "<=.1");
                    }
                });
            }
        }
        destroy() {
            if (this.deferTrigger) this.deferTrigger.kill();
            if (this.timeline) this.timeline.kill();
            if (this.tweenArr) {
                this.tweenArr.forEach((item) => item.destroy?.());
            }
        }
    }
    const useSplitPretext = ({ selector, type, isMask }) => {
        if (selector) {
            const elements = (typeof selector === 'string') ? document.querySelectorAll(selector) : (selector.jquery || typeof selector.length === 'number' ? Array.from(selector) : [selector]);
            elements.forEach(el => {
                if (el && el.innerHTML && !el.hasAttribute('data-nfc-normalized')) {
                    el.innerHTML = el.innerHTML.normalize("NFC");
                    el.setAttribute('data-nfc-normalized', 'true');
                }
            });
        }
        const textSplit = SplitText.create(selector, { type: type });
        return {
            elements: textSplit[type] || textSplit.lines,
            revert: () => textSplit.revert()
        };
    };

    class MaskTextColor {
        constructor({ el, delay, isDisableRevert, ...props }) {
            const target = el && el.jquery ? el.get(0) : el;
            if (!target || target.textContent === '') return;
            this.DOM = { el: target };
            this.delay = delay;
            document.fonts.ready.then(() => {
                // ── Capture rendered height BEFORE split (respects line-clamp) ──
                const clampedHeight = this.DOM.el.offsetHeight;
                const isClamped = this.DOM.el.scrollHeight > clampedHeight + 1;

                gsap.set(this.DOM.el, { width: this.DOM.el.offsetWidth + 5 });
                const result = useSplitPretext({ selector: this.DOM.el, type: 'lines', isMask: false });
                if (!result) return;

                const lines = result.elements;

                // ── Lock height if element was clamped (split destroys -webkit-line-clamp) ──
                if (isClamped) {
                    gsap.set(this.DOM.el, { height: clampedHeight, overflow: 'hidden' });
                }

                lines.forEach(line => {
                    line.classList.add('split-line-p');
                    const color = window.getComputedStyle(line.parentElement).color;
                    gsap.set(line, { '--color-final': color });
                });

                gsap.set(lines, { '--bg-progress': '30' });

                this.animation = gsap.to(lines, {
                    '--bg-progress': '100',
                    stagger: 0.1,
                    duration: 1.2,
                    ease: 'power1.inOut',
                    onComplete: () => {
                        if (!isDisableRevert) {
                            result.revert();
                            if (isClamped) {
                                gsap.set(this.DOM.el, { clearProps: 'height,overflow' });
                            }
                        }
                    },
                    ...props
                });
            });
        }
        init() {
        }
    }
    class RevealTextReset {
        constructor({ el, color, delay, isFast = false, isHighlight = false, ...props }) {
            this.DOM = { el: el };
            this.color = color;
            this.textSplit = [];
            this.delay = delay;
            this.isHighlight = isHighlight
            this.isFast = isFast;

            if (this.DOM.el && this.DOM.el.innerHTML && !this.DOM.el.hasAttribute('data-nfc-normalized')) {
                this.DOM.el.innerHTML = this.DOM.el.innerHTML.normalize("NFC");
                this.DOM.el.setAttribute('data-nfc-normalized', 'true');
            }
            this.textSplit = SplitText.create(this.DOM.el, { type: 'lines, words' });
            this.isColorDefault = this.color === 'white' || this.color === 'black';
            this.fromColor = !this.isColorDefault ? 'rgba(255,255,255, 0)' : this.color == 'white' ? 'rgba(255,255,255, 0)' : 'rgba(29,29,29, 0)';
            this.toColor = !this.isColorDefault ? this.color : this.color == 'white' ? 'rgba(255,255,255, 1)' : 'rgba(29,29,29, 1)';

            if (this.isHighlight) {
                this.animation = gsap.timeline({
                    onComplete: () => {
                        this.reset();
                    },
                    ...props
                });

                this.textSplit.words.forEach((word, idx) => {
                    let toColor = word.closest('.txt-highlight') ? '#FF6B30' : this.toColor;
                    this.animation.to(word, {
                        keyframes: {
                            color: [this.fromColor, '#FF6B30', toColor],
                            easeEach: 'power2.in',
                            ease: 'power1.out',
                        },
                        duration: isFast ? 0.8 : 1
                    }, idx * (isFast ? 0.03 : 0.08))
                });
            }
            else {
                this.animation = gsap.to(this.textSplit.words, {
                    keyframes: {
                        color: [this.fromColor, '#FF6B30', this.toColor],
                        easeEach: 'power2.in',
                        ease: 'power1.out',
                    },
                    duration: isFast ? 0.8 : 1,
                    stagger: isFast ? 0.03 : 0.08,
                    onComplete: () => {
                        this.reset();
                    },
                    ...props
                })
            }
        }
        init() {
            if (getScreenType().isMobile) {
                this.fromColor = !this.isColorDefault ? 'rgba(255,255,255, .1)' : this.color == 'white' ? 'rgba(255,255,255, .1)' : 'rgba(29,29,29, .1)';
                this.reset()
            }
            gsap.set(this.textSplit.words, { color: this.fromColor });
        }
        reset() {
            let isReset = true;
            let isInit = getScreenType().isMobile ? true : false;

            let tlText = gsap.timeline();
            let tl = gsap.timeline({
                scrollTrigger: {
                    trigger: this.DOM.el,
                    start: 'top top+=65%',
                    end: 'bottom top+=65%',
                    onEnter: () => {
                        if (isReset && isInit) {
                            isReset = false;
                            if (this.isHighlight) {
                                this.textSplit.words.forEach((word, idx) => {
                                    let toColor = word.closest('.txt-highlight') ? '#FF6B30' : this.toColor;
                                    tlText.to(word, {
                                        keyframes: {
                                            color: [this.fromColor, '#FF6B30', toColor],
                                            easeEach: 'power2.in',
                                            ease: 'power1.out',
                                        },
                                        duration: this.isFast ? 0.8 : 1
                                    }, idx * (this.isFast ? 0.03 : 0.08))
                                });
                            }
                            else {
                                gsap.to(this.textSplit.words, {
                                    keyframes: {
                                        color: [this.fromColor, '#FF6B30', this.toColor],
                                        easeEach: 'power2.in',
                                        ease: 'power1.out',
                                    },
                                    overwrite: true,
                                    duration: this.isFast ? .8 : 1,
                                    stagger: this.isFast ? .03 : .08,
                                })
                            }
                        }
                    },
                }
            })
            let resetTL = gsap.timeline({
                scrollTrigger: {
                    trigger: this.DOM.el,
                    start: 'top bottom',
                    end: 'bottom top',
                    onLeaveBack: () => {
                        if (!isInit) {
                            this.fromColor = !this.isColorDefault ? 'rgba(255,255,255, .1)' : this.color == 'white' ? 'rgba(255,255,255, .1)' : 'rgba(29,29,29, .1)';
                        }
                        isInit = true;

                        if (!isReset) isReset = true;
                        gsap.set(this.textSplit.words, { color: this.fromColor, overwrite: true })
                    },
                }
            })
        }
    }
    class FadeSplitText {
        constructor({ el, delay, headingType, splitType, duration, stagger, isDisableRevert, isDisableAnim, forceSplitOnMobile, ...props }) {
            if (!el || el.textContent === '') return;
            this.DOM = { el: el };
            this.delay = delay;
            this.textSplit = null;
            this.splitType = splitType || 'words';
            this.headingType = headingType || 'false';
            this.duration = duration || .8;
            this.stagger = stagger || .02;
            this.isDisableRevert = isDisableRevert;
            this.isDisableAnim = isDisableAnim;
            this.forceSplitOnMobile = forceSplitOnMobile;
            this.props = props;
        }
        init() {
            const screen = getScreenType();
            if (screen.isMobile && !this.forceSplitOnMobile) {
                if (this.isDisableAnim) {
                    gsap.set(this.DOM.el, { opacity: 0, display: 'none' });
                    return;
                }
                this.isFallback = true;
                this.animation = gsap.fromTo(this.DOM.el,
                    { opacity: 0, y: parseRem(32) },
                    {
                        opacity: 1,
                        y: 0,
                        duration: this.duration,
                        ease: 'power3.out',
                        clearProps: this.isDisableRevert ? '' : 'all',
                        ...this.props
                    }
                );
                gsap.set(this.DOM.el, { opacity: 0, y: parseRem(32) });
                return;
            }
            if (screen.isTablet && !this.forceSplitOnMobile) {
                if (this.isDisableAnim) {
                    gsap.set(this.DOM.el, { opacity: 0, display: 'none' });
                    return;
                }
                this.isFallback = true;
                this.animation = gsap.fromTo(this.DOM.el,
                    { opacity: 0, y: parseRem(45) },
                    {
                        opacity: 1,
                        y: 0,
                        duration: this.duration,
                        ease: 'power2.out',
                        clearProps: this.isDisableRevert ? '' : 'all',
                        ...this.props
                    }
                );
                gsap.set(this.DOM.el, { opacity: 0, y: parseRem(32) });
                return;
            }

            if (this.DOM.el && this.DOM.el.innerHTML && !this.DOM.el.hasAttribute('data-nfc-normalized')) {
                this.DOM.el.innerHTML = this.DOM.el.innerHTML.normalize("NFC");
                this.DOM.el.setAttribute('data-nfc-normalized', 'true');
            }

            if (this.animation) return; // avoid duplicate initialization

            let animation;
            this.textSplit = SplitText.create(this.DOM.el, {
                type: this.splitType === 'chars' ? "lines words chars" : (this.splitType === 'words' ? "lines words" : 'lines'),
                mask: "lines",
                linesClass: this.headingType ? 'bp-line heading-line' : 'bp-line',
                autoSplit: true,
                onSplit: (self) => {
                    const computedStyle = window.getComputedStyle(self.elements[0]);
                    const bgImage = computedStyle.backgroundImage;
                    const hasGradient = bgImage && bgImage !== 'none' && bgImage.includes('gradient');

                    // Force red color explicitly on red text nodes before processing gradients
                    const redNodes = self.elements[0].querySelectorAll('.txt_red, .cl_red, .cl_main');
                    redNodes.forEach(node => {
                        node.style.setProperty('-webkit-text-fill-color', '#E62636', 'important');
                        node.style.setProperty('color', '#E62636', 'important');
                    });

                    if (hasGradient) {
                        const parentRect = self.elements[0].getBoundingClientRect();
                        const parentWidth = parentRect.width;
                        self[this.splitType].forEach(child => {
                            if (child.classList.contains('txt_red') || child.classList.contains('cl_red') || child.classList.contains('cl_main') || child.classList.contains('cl_linear_red') || child.querySelector('.txt_red, .cl_red, .cl_main, .cl_linear_red') || child.closest('.txt_red, .cl_red, .cl_main, .cl_linear_red')) {
                                return;
                            }
                            const childRect = child.getBoundingClientRect();
                            const offsetX = childRect.left - parentRect.left;
                            child.style.backgroundImage = bgImage;
                            child.style.backgroundSize = `${parentWidth}px 100%`;
                            child.style.backgroundPosition = `-${offsetX}px 0px`;
                            child.style.webkitBackgroundClip = 'text';
                            child.style.webkitTextFillColor = 'transparent';
                            child.style.backgroundClip = 'text';
                        });
                        self.elements[0].style.backgroundImage = 'none';
                        self.elements[0].style.webkitTextFillColor = 'initial';
                    }
                    if (this.isDisableAnim) {
                        gsap.set(self[this.splitType], { autoAlpha: 1, yPercent: 105 });
                        return;
                    }
                    gsap.set(self[this.splitType], { autoAlpha: 0, yPercent: 105 });
                    const hasTranslate = self.elements[0].classList.contains('item_translate');
                    if (hasTranslate) {
                        gsap.set(self.elements[0], { x: 0 });
                        const tl = gsap.timeline();
                        tl.to(self[this.splitType], {
                            autoAlpha: 1,
                            yPercent: 2,
                            stagger: this.stagger,
                            duration: this.duration,
                            ease: 'power2.out',
                            ...this.props
                        });
                        tl.to(self.elements[0], {
                            x: viewport.w > 992 ? 100 : 32,
                            duration: 0.6,
                            ease: 'power2.out',
                            onComplete: () => {
                                if (!this.isDisableRevert) {
                                    self.revert();
                                    convertHyphenDOM(self.elements[0]);
                                }
                            }
                        }, ">-=0.2");
                        animation = tl;
                    } else {
                        animation = gsap.to(self[this.splitType], {
                            autoAlpha: 1,
                            yPercent: 2,
                            stagger: this.stagger,
                            duration: this.duration,
                            ease: 'power2.out',
                            onComplete: () => {
                                if (!this.isDisableRevert) {
                                    self.revert();
                                    convertHyphenDOM(self.elements[0]);
                                }
                            },
                            ...this.props
                        });
                    }
                }
            });
            this.animation = animation;
        }
        play() {
            return this.animation ? this.animation.play() : null;
        }
        destroy() {
            if (this.animation) this.animation.kill();
            if (this.textSplit) this.textSplit.revert();
        }
    }

    class TextTypewriter {
        constructor({ el, delay, ...props }) {
            this.DOM = { el: el };
            this.delay = delay;
            document.fonts.ready.then(() => {
                gsap.set(this.DOM.el, { height: this.DOM.el.offsetHeight });
                this.animation = gsap.from(this.DOM.el, {
                    text: {
                        value: "",
                        speed: 3,
                        ...props
                    },
                    clearProps: 'all',
                });;
            })
        }
        init() {
        }
        stop() {
        }
        destroy() {
            this.animation.kill();
        }
    }
    class FadeIn {
        constructor({ el, type, delay, isDisableRevert, from, to, ...props }) {
            this.DOM = { el: el };
            this.type = type || 'default';
            this.delay = delay;
            this.isDisableRevert = isDisableRevert;
            this.from = from;
            this.to = to;
            this.props = props;
            this.options = {
                bottom: {
                    set: { opacity: 0, y: parseRem(100), ...from },
                    to: { opacity: 1, y: 0, ...to }
                },
                top: {
                    set: { opacity: 0, y: parseRem(-100), ...from },
                    to: { opacity: 1, y: 0, ...to }
                },
                left: {
                    set: { opacity: 0, x: parseRem(-100), ...from },
                    to: { opacity: 1, x: 0, ...to },
                },
                right: {
                    set: { opacity: 0, x: parseRem(100), ...from },
                    to: { opacity: 1, x: 0, ...to }
                },
                none: {
                    set: { opacity: 0, ...from },
                    to: { opacity: 1, ...to }
                },
                default: {
                    set: { opacity: 0, y: parseRem(32), ...from },
                    to: { opacity: 1, y: 0, ...to }
                }
            };
        }
        init() {
            if (!this.DOM.el) return;
            if (this.animation) return; // avoid duplicate initialization

            gsap.set(this.DOM.el, { ...this.options[this.type]?.set || this.options.default.set });

            this.animation = gsap.fromTo(this.DOM.el,
                { ...this.options[this.type]?.set || this.options.default.set },
                {
                    ...this.options[this.type]?.to || this.options.default.to,
                    duration: 1,
                    ease: 'power3',
                    clearProps: 'all',
                    ...this.props
                });
        }
        play() {
            return this.animation ? this.animation.play() : null;
        }
        destroy() {
            if (this.animation) this.animation.kill();
        }
    }
    class ScaleDash {
        constructor({ el, type, isCenter, delay, isDisableRevert, ...props }) {
            this.DOM = { el: el };
            this.type = type || 'default';
            this.delay = delay;
            this.isCenter = isCenter;
            this.isDisableRevert = isDisableRevert;
            this.props = props;
        }
        init() {
            if (!this.DOM?.el || getScreenType().isMobile) return;
            if (this.animation) return; // avoid duplicate initialization

            this.widthItem = this.DOM.el.offsetWidth || 0;
            this.heightItem = this.DOM.el.offsetHeight || 0;
            this.options = {
                top: {
                    set: { height: 0, transformOrigin: this.isCenter ? 'center center' : 'top left' },
                    to: { height: this.heightItem }
                },
                bottom: {
                    set: { height: 0, transformOrigin: this.isCenter ? 'center center' : 'bottom left' },
                    to: { height: this.heightItem }
                },
                left: {
                    set: { width: 0, transformOrigin: this.isCenter ? 'center center' : 'top left' },
                    to: { width: this.widthItem }
                },
                right: {
                    set: { width: 0, transformOrigin: this.isCenter ? 'center center' : 'top right' },
                    to: { width: this.widthItem }
                },
                default: {
                    set: { height: 0, transformOrigin: this.isCenter ? 'center center' : 'top left' },
                    to: { height: this.heightItem }
                }
            };

            gsap.set(this.DOM.el, { ...this.options[this.type]?.set || this.options.default.set });

            this.animation = gsap.fromTo(this.DOM.el,
                { ...this.options[this.type]?.set || this.options.default.set },
                {
                    ...this.options[this.type]?.to || this.options.default.to,
                    duration: 1.2,
                    ease: 'power1.out',
                    clearProps: this.isDisableRevert ? '' : 'all',
                    ...this.props
                });
        }
        play() {
            return this.animation ? this.animation.play() : null;
        }
        destroy() {
            if (this.animation) this.animation.kill();
        }
    }
    class ScaleLine {
        constructor({ el, type, isCenter, delay, isDisableRevert, ...props }) {
            if (!el) return;

            this.DOM = { el: el };
            this.type = type || 'default';
            this.delay = delay;
            this.isCenter = isCenter;
            this.isDisableRevert = isDisableRevert;
            this.props = props;
            this.options = {
                top: {
                    set: { scaleY: 0, transformOrigin: isCenter ? 'center center' : 'top left' },
                    to: { scaleY: 1 }
                },
                left: {
                    set: { scaleX: 0, transformOrigin: isCenter ? 'center center' : 'top left' },
                    to: { scaleX: 1 }
                },
                right: {
                    set: { scaleX: 0, transformOrigin: isCenter ? 'center center' : 'top right' },
                    to: { scaleX: 1 }
                },
                bottom: {
                    set: { scaleX: 0, transformOrigin: isCenter ? 'center center' : 'bottom right' },
                    to: { scaleX: 1 }
                },
                default: {
                    set: { scaleX: 0, transformOrigin: isCenter ? 'center center' : 'top left' },
                    to: { scaleX: 1 }
                }
            };
        }
        init() {
            if (!this.DOM?.el || getScreenType().isMobile) return;
            if (this.animation) return; // avoid duplicate initialization

            gsap.set(this.DOM.el, { ...this.options[this.type]?.set || this.options.default.set });

            this.animation = gsap.fromTo(this.DOM.el,
                { ...this.options[this.type]?.set || this.options.default.set },
                {
                    ...this.options[this.type]?.to || this.options.default.to,
                    duration: 1.2,
                    ease: 'none',
                    clearProps: this.isDisableRevert ? '' : 'all',
                    ...this.props
                });
        }
        play() {
            return this.animation ? this.animation.play() : null;
        }
        destroy() {
            if (this.animation) this.animation.kill();
        }
    }
    class ScaleInset {
        constructor({ el, delay, duration, isDisableRevert, onComplete }) {
            this.DOM = { el: el };
            this.delay = delay;
            this.duration = duration;
            this.isDisableRevert = isDisableRevert;
            this.onComplete = onComplete;
        }
        init() {
            if (!this.DOM.el) return;
            if (this.animation) return; // avoid duplicate initialization

            const isMobile = getScreenType().isMobile;
            const startScale = isMobile ? 1 : 1.08;
            const startY = isMobile ? 16 : 24;

            gsap.set(this.DOM.el, { scale: startScale, autoAlpha: 0, y: startY });

            const d = this.duration || 1.4;
            const tl = gsap.timeline();

            tl.to(this.DOM.el, {
                scale: 1,
                autoAlpha: 1,
                y: 0,
                duration: d,
                ease: 'expo.out',
                clearProps: this.isDisableRevert ? '' : 'all',
            }, 0);

            if (this.onComplete) tl.eventCallback('onComplete', this.onComplete);
            this.animation = tl;
        }
        play() {
            return this.animation ? this.animation.play() : null;
        }
        destroy() {
            if (this.animation) this.animation.kill();
        }
    }

    // ───────────────────────────────────────────

    $.easing.exponentialEaseOut = function (t) {
        return Math.min(1, 1.001 - Math.pow(2, -10 * t));
    };
    const convertHyphen = (field) => {
        return field.replace(/-/g, '‑')
    }
    $.fn.hasAttr = function (name) {
        return this.attr(name) !== undefined;
    };
    if (typeof SplitText !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger, SplitText);
    } else {
        gsap.registerPlugin(ScrollTrigger);
    }
    const childSelect = (parent) => {
        return (child) => (child ? $(parent).find(child) : parent);
    };
    function activeItem(elArr, index) {
        elArr.forEach((el, idx) => {
            $(el).removeClass("active").eq(index).addClass("active");
        });
    }
    const xSetter = (el) => gsap.quickSetter(el, "x", "px");
    const ySetter = (el) => gsap.quickSetter(el, "y", "px");
    const xGetter = (el) => gsap.getProperty(el, "x");
    const yGetter = (el) => gsap.getProperty(el, "y");

    const viewport = {
        get w() {
            return window.innerWidth;
        },
        get h() {
            return window.innerHeight;
        },
    };

    let cachedSvh100 = null;
    let cachedHtmlFontSize = null;
    const getHtmlFontSize = () => {
        if (cachedHtmlFontSize !== null) return cachedHtmlFontSize;
        const htmlEl = document.documentElement;
        cachedHtmlFontSize = parseFloat(window.getComputedStyle(htmlEl).fontSize) || 16;
        return cachedHtmlFontSize;
    };

    const getSvh100 = () => {
        if (cachedSvh100 != null) return cachedSvh100;
        const el = document.createElement("div");
        el.style.cssText =
            "position:fixed;top:0;left:0;height:100svh;width:0;pointer-events:none;visibility:hidden;";
        document.body.appendChild(el);
        cachedSvh100 = el.getBoundingClientRect().height;
        document.body.removeChild(el);
        return cachedSvh100;
    };
    window.addEventListener("resize", () => {
        cachedSvh100 = null;
        cachedHtmlFontSize = null;
    });
    const cvUnit = (val, unit) => {
        let result;
        switch (true) {
            case unit === "vw":
                result = window.innerWidth * (val / 100);
                break;
            case unit === "vh":
                result =
                    (window.innerWidth <= 767 ? getSvh100() : window.innerHeight) *
                    (val / 100);
                break;
            case unit === "svh":
                result = getSvh100() * (val / 100);
                break;
            case unit === "rem":
                result = (val / 10) * getHtmlFontSize();
                break;
            default:
                break;
        }
        return result;
    };
    const isInViewport = (el, orientation = "vertical") => {
        if (!el) return;
        const rect = el.getBoundingClientRect();
        if (orientation == "horizontal") {
            return rect.left <= window.innerWidth && rect.right >= 0;
        } else {
            return rect.top <= window.innerHeight && rect.bottom >= 0;
        }
    };
    const debounce = (func, timeout = 300) => {
        let timer;

        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, timeout);
        };
    };
    const isTouchDevice = () => {
        return (('ontouchstart' in window) ||
            (navigator.maxTouchPoints > 0) ||
            (navigator.msMaxTouchPoints > 0));
    }
    const closestEdge = (x, y, w, h) => {
        const topEdgeDist = distMetric(x, y, w / 2, 0);
        const bottomEdgeDist = distMetric(x, y, w / 2, h);
        const min = Math.min(topEdgeDist, bottomEdgeDist);
        return min === topEdgeDist ? "top" : "bottom";
    };
    const distMetric = (x, y, x2, y2) => {
        var xDiff = x - x2;
        var yDiff = y - y2;
        return xDiff * xDiff + yDiff * yDiff;
    };
    const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const lerp = (a, b, t) => (1 - t) * a + t * b;

    class ImageBinder {
        static SVG_NS = "http://www.w3.org/2000/svg";

        constructor(container, options = {}) {
            this.container = container;
            this.blindCount = options.blindCount ?? viewport.w > 767 ? 24 : 12;
            this.layerSelector = options.layerSelector || ".layer";
            this.onLayoutComplete = options.onLayoutComplete || null;
            this.duration = options.duration || .015
            this.blindsSets = [];
            this._resizeHandler = null;
        }

        createBlinds(groupId) {
            const g = document.getElementById(groupId);
            if (!g) return null;
            g.innerHTML = "";

            const width = this.container.offsetWidth;
            const height = this.container.offsetHeight;
            const vbHeight = (height / width) * 100;
            const h = vbHeight / this.blindCount;
            const blinds = [];
            let currentY = 0;

            for (let i = 0; i < this.blindCount; i++) {
                const centerY = vbHeight - (currentY + h / 2);

                const rectTop = document.createElementNS(ImageBinder.SVG_NS, "rect");
                const rectBottom = document.createElementNS(ImageBinder.SVG_NS, "rect");

                [rectTop, rectBottom].forEach((r) => {
                    r.setAttribute("x", 0);
                    r.setAttribute("width", 100);
                    r.setAttribute("height", 0);
                    r.setAttribute("fill", "white");
                    r.setAttribute("shape-rendering", "crispEdges");
                });

                rectTop.setAttribute("y", centerY);
                rectBottom.setAttribute("y", centerY);

                g.appendChild(rectTop);
                g.appendChild(rectBottom);

                blinds.push({
                    top: rectTop,
                    bottom: rectBottom,
                    y: centerY,
                    h: h / 2,
                });
                currentY += h;
            }
            return blinds;
        }

        updateLayout(container) {
            const el = container || this.container;
            if (!el) return;

            const width = el.offsetWidth;
            const height = el.offsetHeight;
            console.log(width, height)
            const vbWidth = 100;
            const vbHeight = (height / width) * 100;

            const layers = el.querySelectorAll(this.layerSelector);
            this.blindsSets = [];

            layers.forEach((svg, i) => {
                svg.setAttribute("viewBox", `0 0 ${vbWidth} ${vbHeight}`);

                const maskEl = svg.querySelector("mask");
                const maskId = `ib-mask-${i}`;
                const blindsId = `ib-blinds-${i}`;

                if (maskEl) {
                    maskEl.id = maskId;
                    const maskRect = maskEl.querySelector("rect");
                    if (maskRect) {
                        maskRect.setAttribute("width", vbWidth);
                        maskRect.setAttribute("height", vbHeight);
                    }
                }

                const img = svg.querySelector("image");
                if (img) {
                    img.setAttribute("width", vbWidth);
                    img.setAttribute("height", vbHeight);
                    img.setAttribute("mask", `url(#${maskId})`);
                }

                const blindG = svg.querySelector('g[id^="blinds"], g[id^="ib-blinds"]');
                if (blindG) {
                    blindG.id = blindsId;
                    const blinds = this.createBlinds(blindsId);
                    if (blinds) this.blindsSets.push(blinds);
                }
            });

            this.onLayoutComplete?.(this.blindsSets);
        }

        openBlinds(blinds) {
            const set = blinds || this.blindsSets[0];
            if (!set?.length) return gsap.timeline({
                scrollTrigger: {
                    trigger: 'body',
                    start: 'top bottom',
                    end: 'bottom top',
                }
            });

            return gsap.timeline({
                scrollTrigger: {
                    trigger: 'body',
                    start: 'top bottom',
                    end: 'bottom+=100% top',
                }
            }).to(
                set.flatMap((b) => [b.top, b.bottom]),
                {
                    attr: {
                        y: (i) => {
                            const b = set[Math.floor(i / 2)];
                            return i % 2 === 0 ? b.y - b.h : b.y;
                        },
                        height: (i) => {
                            const b = set[Math.floor(i / 2)];
                            return b.h + 0.01;
                        },
                    },
                    ease: "none",
                    stagger: { each: this.duration, from: "start" },
                }
            );
        }

        closeBlinds(blinds) {
            const set = blinds || this.blindsSets[0];
            if (!set?.length) return;
            set.forEach((b) => {
                [b.top, b.bottom].forEach((r) => {
                    r.setAttribute("y", b.y);
                    r.setAttribute("height", 0);
                });
            });
        }

        init() {
            this.updateLayout(this.container);
        }

        destroy() {
            this.blindsSets = [];
            this.container = null;
        }
    }

    const distance = (x1, y1, x2, y2) => Math.hypot(x2 - x1, y2 - y1);
    const normalize = (mousePos, maxDis) => (mousePos / maxDis - 0.5) * 2;
    const getAllScrollTrigger = (fn) => {
        let triggers = ScrollTrigger.getAll();
        triggers.forEach((trigger) => {
            if (fn === "refresh") {
                if (trigger.progress === 0) {
                    trigger[fn]?.();
                }
            } else {
                trigger[fn]?.();
            }
        });
    };
    function documentHeightObserver(action, data, callback) {
        let resizeObserver;
        let debounceTimer;
        let observerEl = data?.next.container;
        let previousHeight = observerEl?.scrollHeight;

        function onRefresh() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const currentHeight = observerEl.scrollHeight;

                if (currentHeight !== previousHeight) {
                    if (smoothScroll.lenis) {
                        smoothScroll.lenis.resize();
                        ScrollTrigger.getAll().forEach((trigger) => {
                            if (trigger.progress === 0 || trigger.vars?.scrub) {
                                trigger.refresh();
                            }
                        });
                    }
                    if (callback) {
                        callback();
                    }
                    previousHeight = currentHeight;
                }
            }, 200);
        }

        if (action === "init") {
            if (!observerEl) return;
            resizeObserver = new ResizeObserver(onRefresh);
            resizeObserver.observe(observerEl);
        } else if (action === "disconnect") {
            if (resizeObserver) {
                resizeObserver.disconnect();
            }
        }
    }
    function resetScroll(data) {
        if (window.location.hash !== "") {
            if ($(window.location.hash).length >= 1) {
                if (viewport.w > 991 && !isTouchDevice()) {
                    setTimeout(() => {
                        $("html").animate(
                            { scrollTop: $(window.location.hash).offset().top - 100 },
                            1200,
                        );
                        setTimeout(() => {
                            $("html").animate(
                                {
                                    scrollTop: $(window.location.hash).offset().top - 100,
                                },
                                1200,
                            );
                        }, 300);
                    }, 1000);
                } else {
                    setTimeout(() => {
                        const $scroller = $(".body-inner").length ? $(".body-inner") : $("html, body");
                        $scroller.animate(
                            {
                                scrollTop: $(window.location.hash).offset().top,
                            },
                            1200,
                            "exponentialEaseOut",
                        );
                    }, 500);
                }
            } else {
                scrollTop();
            }
        } else if (window.location.search !== "") {
            let searchObj = JSON.parse(
                '{"' +
                decodeURI(location.search.substring(1))
                    .replace(/"/g, '\\"')
                    .replace(/&/g, '","')
                    .replace(/=/g, '":"') +
                '"}',
            );
            if (searchObj.sc) {
                if ($(`#${searchObj.sc}`).length >= 1) {
                    let target = `#${searchObj.sc}`;
                    if (viewport.w > 991 && !isTouchDevice()) {
                        setTimeout(() => {
                            smoothScroll.scrollTo(`#${searchObj.sc}`, {
                                offset: -100,
                            });
                        }, 500);
                    } else {
                        const $scroller = $(".body-inner").length ? $(".body-inner") : $("html, body");
                        $scroller.animate(
                            {
                                scrollTop: $(target).offset().top,
                            },
                            1200,
                            "exponentialEaseOut",
                        );
                    }
                    barba.history.add(
                        `${window.location.pathname + target}`,
                        "barba",
                        "replace",
                    );
                } else {
                    scrollTop();
                }
            }
        } else {
            scrollTop();
        }
    }
    function scrollTop(onComplete) {
        if ("scrollRestoration" in history) {
            history.scrollRestoration = "manual";
        }
        window.scrollTo(0, 0);
        smoothScroll.scrollToTop({
            onComplete: () => {
                onComplete?.();
                getAllScrollTrigger("refresh");
            },
        });
    }
    class Marquee {
        constructor(list, duration = 40) {
            this.list = list;
            this.duration = duration;
        }
        setup(isReverse) {
            console.log()
            let itemClone = this.list.find('[data-marquee="item"]').clone();
            let itemWidth = this.list.find('[data-marquee="item"]').width();
            const cloneAmount = Math.ceil(viewport.w / itemWidth) + 1;
            this.list.html("");
            new Array(cloneAmount).fill().forEach(() => {
                let html = itemClone.clone();
                html.css(
                    "animation-duration",
                    `${Math.ceil(itemWidth / this.duration)}s`,
                );
                if (isReverse) {
                    html.css("animation-direction", "reverse");
                }
                html.addClass("anim-marquee");
                this.list.append(html);
            });
        }
    }
    class SmoothScroll {
        constructor() {
            this.lenis = null;
            this.nativeScrollHandler = null;
            this.scroller = {
                scrollX: window.scrollX,
                scrollY: window.scrollY,
                velocity: 0,
                direction: 0,
            };
            this.scrollKeys = { ArrowUp: 1, ArrowDown: 1, Space: 1, PageUp: 1, PageDown: 1, End: 1, Home: 1 };
            this.preventDefault = (e) => {
                if (e.cancelable) {
                    e.preventDefault();
                }
            };
            this.preventDefaultForScrollKeys = (e) => {
                if (this.scrollKeys[e.key] || this.scrollKeys[e.code]) {
                    if (e.cancelable) {
                        e.preventDefault();
                    }
                    return false;
                }
            };
        }

        get scroll() {
            return this.scroller.scrollY;
        }

        get direction() {
            return this.scroller.direction;
        }

        init(data) {
            this.reInit(data);

            $.easing.lenisEase = function (t) {
                return Math.min(1, 1.001 - Math.pow(2, -10 * t));
            };

            this.tickerCb = (time) => {
                if (this.lenis) {
                    this.lenis.raf(time * 1000);
                }
            };
            gsap.ticker.add(this.tickerCb);
            gsap.ticker.lagSmoothing(0);
        }

        reInit(data) {
            if (this.lenis) {
                this.lenis.destroy();
                this.lenis = null;
            }
            if (this.nativeScrollHandler) {
                window.removeEventListener("scroll", this.nativeScrollHandler);
                this.nativeScrollHandler = null;
            }

            let namespace = data
                ? data.next.namespace
                : $('[data-barba="container"]').attr("data-barba-namespace");

            if (viewport.w > 991 && !isTouchDevice()) {
                this.lenis = new Lenis({
                    content: document.documentElement,
                    wrapper: document.documentElement,
                });

                // Đồng bộ scroll event
                this.lenis.on("scroll", ScrollTrigger.update);

                this.lenis.on("scroll", (e) => {
                    this.updateOnScroll(e);
                });
            } else {
                // On mobile/tablet/touch, use native scrolling. Reset ScrollTrigger defaults to window.
                ScrollTrigger.defaults({
                    scroller: window,
                });
                ScrollTrigger.config({ ignoreMobileResize: true });

                let ticking = false;
                this.nativeScrollHandler = () => {
                    if (!ticking) {
                        window.requestAnimationFrame(() => {
                            const scrollY = window.scrollY || document.documentElement.scrollTop;
                            const direction = scrollY > this.scroller.scrollY ? 1 : (scrollY < this.scroller.scrollY ? -1 : 0);
                            this.updateOnScroll({
                                scroll: scrollY,
                                velocity: 0,
                                direction: direction
                            });
                            ticking = false;
                        });
                        ticking = true;
                    }
                };

                window.addEventListener("scroll", this.nativeScrollHandler, { passive: true });

                // Trigger initial update
                const initialScrollY = window.scrollY || document.documentElement.scrollTop;
                this.updateOnScroll({
                    scroll: initialScrollY,
                    velocity: 0,
                    direction: 0
                });
            }
        }
        reachedThreshold(threshold) {
            if (!threshold) return false;
            const dist = distance(
                this.scroller.scrollX,
                this.scroller.scrollY,
                this.lastScroller.scrollX,
                this.lastScroller.scrollY,
            );

            if (dist > threshold) {
                this.lastScroller = { ...this.scroller };
                return true;
            }
            return false;
        }

        updateOnScroll(e) {
            this.scroller.scrollX = e.scroll;
            this.scroller.scrollY = e.scroll;
            this.scroller.velocity = e.velocity;
            this.scroller.direction = e.direction;

            if (header) {
                header.updateOnScroll(this);
            }
        }

        start() {
            if (this.lenis) {
                this.lenis.start();
            }
            this.enableScroll();
            if (viewport.w <= 991 || isTouchDevice()) {
                $("body").css("overflow", "initial");
            }
        }

        stop() {
            if (this.lenis) {
                this.lenis.stop();
            }
            this.disableScroll();
            if (viewport.w <= 991 || isTouchDevice()) {
                $("body").css("overflow", "hidden");
            }
        }

        disableScroll() {
            this.enableScroll();
            window.addEventListener('wheel', this.preventDefault, { passive: false });
            window.addEventListener('touchmove', this.preventDefault, { passive: false });
            document.addEventListener('touchmove', this.preventDefault, { passive: false });
            window.addEventListener('keydown', this.preventDefaultForScrollKeys, { passive: false });
        }

        enableScroll() {
            window.removeEventListener('wheel', this.preventDefault, { passive: false });
            window.removeEventListener('touchmove', this.preventDefault, { passive: false });
            document.removeEventListener('touchmove', this.preventDefault, { passive: false });
            window.removeEventListener('keydown', this.preventDefaultForScrollKeys, { passive: false });
        }

        scrollTo(target, options = {}) {
            if (this.lenis) {
                this.lenis.scrollTo(target, options);
            }
        }

        scrollToPosition(target) {
            if (viewport.w <= 991 || isTouchDevice()) {
                const bodyInner = document.querySelector(".body-inner");
                if (bodyInner) {
                    bodyInner.scrollTop = target;
                } else {
                    window.scrollTo(0, target);
                }
            } else {
                window.scrollTo(0, target);
            }
            ScrollTrigger.update();
        }

        scrollToTop(options = {}) {
            if (this.lenis) {
                this.lenis.scrollTo("top", {
                    duration: 0.0001,
                    immediate: true,
                    lock: true,
                    ...options,
                });
            }
        }

        destroy() {
            if (this.tickerCb) {
                gsap.ticker.remove(this.tickerCb);
            }
            if (this.lenis) {
                this.lenis.destroy();
                this.lenis = null;
            }
            if (this.nativeScrollHandler) {
                window.removeEventListener("scroll", this.nativeScrollHandler);
                this.nativeScrollHandler = null;
            }
        }
    }
    const smoothScroll = new SmoothScroll();
    const reinitializeWebflow = (data) => {
        if (!window.Webflow) return;
        console.log("reinitializeWebflow");
        try {
            window.Webflow.destroy();
            window.Webflow.ready();
            const ix2 = window.Webflow.require("ix2");
            if (ix2 && typeof ix2.init === "function") {
                ix2.init();
            }
            const forms = window.Webflow.require("forms");
            if (forms && typeof forms.ready === "function") {
                forms.ready();
            }
            ["slider", "tabs", "dropdown", "navbar"].forEach((module) => {
                try {
                    const mod = window.Webflow.require(module);
                    if (mod && typeof mod.ready === "function") {
                        mod.ready();
                    }
                } catch (e) { }
            });
            if (window.Webflow.redraw) {
                window.Webflow.redraw.up();
            }

            if (data) {
                let parser = new DOMParser();
                let dom = parser.parseFromString(data.next.html, "text/html");
                let webflowPageId = $(dom).find("html").attr("data-wf-page");
                $("html").attr("data-wf-page", webflowPageId);
            }
        } catch (e) {
            console.warn("Webflow reinit failed:", e);
        }
    };
    class Mouse {
        constructor() {
            this.mousePos = { x: 0, y: 0 };
            this.cacheMousePos = { ...this.mousePos };
            this.lastMousePos = { ...this.mousePos };
            this.currentSection = null;
            this.normalizeMousePos = {
                current: { x: 0.5, y: 0.5 },
                target: { x: 0.5, y: 0.5 },
            };
            this.cursorRaf = null;
            this.isUpdating = false;
            this.init();

            // Add mouse move event listener only if not on touch device
            if (!isTouchDevice()) {
                window.addEventListener("mousemove", (e) => {
                    this.mousePos = this.getPointerPos(e);
                });
            }
            window.addEventListener("resize", () => {
                if (viewport.w > 991 && !isTouchDevice()) {
                    if (!$(".cursor").hasClass("active")) {
                        $(".cursor").addClass("active");
                        this.updateHtml();
                    }
                    if (!this.isUpdating) {
                        this.isUpdating = true;
                        requestAnimationFrame(this.update.bind(this));
                    }
                } else {
                    $(".cursor").removeClass("active");
                    this.isUpdating = false;
                }
            });
        }

        init() {
            if (viewport.w > 991 && !isTouchDevice()) {
                setTimeout(() => {
                    this.updateHtml();
                }, 200);
                $(".cursor").addClass("active");
                this.isUpdating = true;
                requestAnimationFrame(this.update.bind(this));
            }
        }
        updateHtml() {
            $('[data-cursor="bg"]').each((idx, el) => {
                if ($(el).find(".bg-dot").length) return;
                let elInner = $(el).find('[data-cursor="inner"]');
                let bg = "--cl-" + ($(el).attr("data-bg") || "white");
                $(el).find('.txt, .heading, [data-hover="arr-ic-main"]').css({
                    position: "relative",
                    "z-index": "2",
                });
                $(el).find(".ic-embed:not(.ic-arr-main):not(.ic-arr-clone)").css({
                    position: "relative",
                    "z-index": "2",
                });
                let btnDot = $(document.createElement("div")).addClass("bg-dot");
                let btnDotInner = $(document.createElement("div"))
                    .addClass("bg-dot-inner")
                    .css("background-color", `var(${bg})`);
                btnDot.append(btnDotInner);
                if (elInner.length) {
                    elInner.append(btnDot);
                } else {
                    $(el).append(btnDot);
                }
            });
        }
        getSectionAtCursor(clientX, clientY) {
            const el = document.elementFromPoint(clientX, clientY);
            if (!el) return null;
            const $el = $(el);
            const section = $el.closest("[data-section]");
            const mode = $el.closest("[data-mode]");
            return section.length ? section : (mode.length ? mode : null);
        }
        update() {
            if (viewport.w <= 991 || isTouchDevice()) {
                this.isUpdating = false;
                this.cursorRaf = null;
                return;
            }
            this.isUpdating = true;
            const section = this.getSectionAtCursor(this.mousePos.x, this.mousePos.y);
            this.currentSection = section?.attr("data-section") || section?.attr("data-mode") || null;
            if (viewport.w > 991) {
                if (this.currentSection)
                    $(".cursor").attr("data-color", this.currentSection);
                else $(".cursor").removeAttr("data-color");
            }
            this.cacheMousePos.x = lerp(this.cacheMousePos.x, this.mousePos.x, 0.1);
            this.cacheMousePos.y = lerp(this.cacheMousePos.y, this.mousePos.y, 0.1);

            this.normalizeMousePos.target.x = this.mousePos.x / window.innerWidth;
            this.normalizeMousePos.target.y = this.mousePos.y / window.innerHeight;

            if (!this.cursorRaf) {
                this.cursorRaf = requestAnimationFrame(this.lerpCursorPos.bind(this));
            }
            // this.toggleCursor();
            if (this.isUpdating) {
                requestAnimationFrame(this.update.bind(this));
            }
        }

        getPointerPos(ev) {
            if (ev.touches) {
                return {
                    x: ev.touches[0].clientX,
                    y: ev.touches[0].clientY,
                };
            }
            return {
                x: ev.clientX,
                y: ev.clientY,
            };
        }

        lerpCursorPos = () => {
            if (viewport.w <= 991 || isTouchDevice()) {
                if (this.cursorRaf) {
                    cancelAnimationFrame(this.cursorRaf);
                    this.cursorRaf = null;
                }
                return;
            }
            this.normalizeMousePos.current.x = lerp(
                this.normalizeMousePos.current.x,
                this.normalizeMousePos.target.x,
                0.1,
            );
            this.normalizeMousePos.current.y = lerp(
                this.normalizeMousePos.current.y,
                this.normalizeMousePos.target.y,
                0.1,
            );

            const delta = distance(
                this.normalizeMousePos.target.x,
                this.normalizeMousePos.current.x,
                this.normalizeMousePos.target.y,
                this.normalizeMousePos.current.y,
            );

            if (delta < 0.001 && this.cursorRaf) {
                cancelAnimationFrame(this.cursorRaf);
                this.cursorRaf = null;
                this.resetCursor();
                return;
            } else {
                this.cursorRaf = requestAnimationFrame(this.lerpCursorPos.bind(this));
                this.toggleCursor();
            }
        };

        reachedThreshold(threshold) {
            if (!threshold) return false;
            const dist = distance(
                this.mousePos.x,
                this.mousePos.y,
                this.lastMousePos.x,
                this.lastMousePos.y,
            );
            if (dist > threshold) {
                this.lastMousePos = { ...this.mousePos };
                return true;
            }
            return false;
        }
        toggleCursor() {
            let gotBtnSize = false;
            const hoverElements = $("[data-cursor]:hover");
            const cursor = $(".cursor");
            const cursorInner = $(".cursor-inner");

            xSetter(cursorInner)(
                this.normalizeMousePos.current.x * window.innerWidth,
            );
            ySetter(cursorInner)(
                this.normalizeMousePos.current.y * window.innerHeight,
            );

            // Get the last hovered element's cursor type (topmost element)
            const type = $(hoverElements[hoverElements.length - 1]).attr(
                "data-cursor",
            );
            switch (type) {
                case "drag":
                    cursor.removeClass("hidden");
                    cursor.addClass("on-drag");
                    break;
                case "control":
                    cursor.removeClass("hidden");
                    cursor.addClass("on-control");
                    break;
                case "hidden":
                    cursor.addClass("hidden");
                    break;
                case "bg":
                    let targetBg;
                    targetBg = $('[data-cursor="bg"]:hover');
                    if ($("[data-cursor]:hover").hasClass("sm-menu")) {
                        gsap.set("html", {
                            "--cursor-width": `${targetBg.get(0).getBoundingClientRect().width * 1.6}px`,
                            "--cursor-height": `${targetBg.get(0).getBoundingClientRect().height * 1.3}px`,
                        });
                    } else {
                        gsap.set("html", {
                            "--cursor-width": `${targetBg.get(0).getBoundingClientRect().width * 1.4}px`,
                            "--cursor-height": `${targetBg.get(0).getBoundingClientRect().height * 1.4}px`,
                        });
                    }
                    cursor.addClass("hidden");
                    this.targetX =
                        targetBg.get(0).getBoundingClientRect().left +
                        targetBg.get(0).getBoundingClientRect().width / 2;
                    this.targetY =
                        targetBg.get(0).getBoundingClientRect().top +
                        targetBg.get(0).getBoundingClientRect().height / 2;
                    const bgDotEl = targetBg.find(".bg-dot").get(0);
                    if (!bgDotEl) break;
                    const rect = targetBg.get(0).getBoundingClientRect();
                    const targetX = this.mousePos.x - rect.left;
                    const targetY = this.mousePos.y - rect.top;
                    let bgDotX = gotBtnSize ? xGetter(bgDotEl) : targetX;
                    let bgDotY = gotBtnSize ? yGetter(bgDotEl) : targetY;
                    if (!gotBtnSize) gotBtnSize = true;
                    xSetter(bgDotEl)(lerp(bgDotX, targetX, 0.24));
                    ySetter(bgDotEl)(lerp(bgDotY, targetY, 0.24));
                    break;
                case "txtLink":
                    $(".cursor-inner").addClass("on-hover-sm");
                    let targetEl;
                    if (
                        $("[data-cursor]:hover").attr("data-cursor-txtLink") == "parent"
                    ) {
                        targetEl = $("[data-cursor]:hover").parent();
                    } else if (
                        $("[data-cursor]:hover").attr("data-cursor-txtLink") == "child"
                    ) {
                        targetEl = $("[data-cursor]:hover").find(
                            "[data-cursor-txtLink-child]",
                        );
                    } else {
                        targetEl = $("[data-cursor]:hover");
                    }

                    this.mousePos.x =
                        targetEl.get(0).getBoundingClientRect().left -
                        $(".cursor-inner").width() / 2 -
                        cvUnit(8, "rem");
                    this.mousePos.y =
                        targetEl.get(0).getBoundingClientRect().top +
                        targetEl.get(0).getBoundingClientRect().height / 2;
                    $(".cursor-inner").addClass("on-hover-sm");
                    break;
                default:
                    this.resetCursor();
                    break;
            }
        }

        resetCursor() {
            const cursor = $(".cursor");
            // Reset cursor styles
            cursor.removeClass("on-drag");
            cursor.removeClass("hidden");
            cursor.removeClass("on-control");
        }
    }
    const mouse = {
        init() { },
        updateHtml() { }
    };
    class Loader {
        constructor() {
            this.isLoaded =
                sessionStorage.getItem("isLoaded") === "true" ? true : false;
            this.tlLoadDone = null;
            this.tlLoadMaster = null;
            this.tlLoading = null;
            this.tlCount = null;
            this.tlSlide = null;
            this.tlFirstLoad = null;
            this.tlMove = null;
            this.el = document.querySelector(".loading");
        }
        init(data) {
            if (!this.el) {
                this.devMode(data);
                return;
            }
            this.tlLoading = gsap.timeline({
                paused: true,
                onComplete: () => {
                    this.el.classList.add("done");
                },
            });
            this.tlFirstLoad = gsap.timeline({ paused: true });
            this.tlCount = gsap.timeline({ paused: true });
            this.tlSlide = gsap.timeline({ paused: true });
            this.tlMove = gsap.timeline({
                paused: true,
            });
            this.tlLoadMaster = gsap.timeline({
                paused: true,
                delay: this.isLoaded ? 0 : 1,
                duration: 1,
                onStart: () => {
                    this.onceSetup(data);
                },
                onComplete: () => {
                    this.oncePlay(data);
                },
            });
        }
        play(data) {
            if (!this.el) {
                return;
            }
            this.tlLoadMaster.play();
        }
        devMode(data) {
            this.onceSetup(data);
            this.oncePlay(data);
            $(".loader").remove();
        }
        onceSetup(data) {
            $('body').addClass('over-inherit');
            smoothScroll.stop();
            globalHooks.triggerOnceSetup(data);
        }
        oncePlay(data) {
            $('body').removeClass('over-inherit');
            smoothScroll.start();
            globalHooks.triggerOncePlay(data);
            $(".loader").css("pointer-events", "none");
            sessionStorage.setItem("isLoaded", true);
        }
    }
    const loader = new Loader();

    class GlobalChange {
        constructor() {
            this.namespace = null;
        }
        init(data) {
            this.namespace = data.next.namespace;
            this.refreshOnBreakpoint();
            this.goToTop();
        }
        update(data) {
            header.update(data);
        }
        goToTop() {
            $(document).on("click", ".footer-nav-item, .btn_top", (e) => {
                e.preventDefault();
                if (typeof smoothScroll !== "undefined" && smoothScroll.lenis) {
                    smoothScroll.scrollTo(0);
                } else {
                    window.scrollTo({ top: 0, behavior: "smooth" });
                }
                $('.header').removeClass('on-hide');
            });
        }
        refreshOnBreakpoint() {
            const breakpoints = [767, 991];
            const initialViewportWidth =
                viewport.w || document.documentElement.clientWidth;
            const breakpoint =
                breakpoints.find((bp) => initialViewportWidth < bp) ||
                breakpoints[breakpoints.length - 1];
            window.addEventListener(
                "resize",
                debounce(function () {
                    const newViewportWidth =
                        viewport.w || document.documentElement.clientWidth;
                    if (
                        (initialViewportWidth < breakpoint &&
                            newViewportWidth >= breakpoint) ||
                        (initialViewportWidth >= breakpoint &&
                            newViewportWidth < breakpoint)
                    ) {
                        location.reload();
                    }
                }),
            );
        }
    }
    const globalChange = new GlobalChange();
    class GlobalHooks {
        constructor() { }
        triggerEvent(eventName, data) {
            const event = new CustomEvent(eventName, { detail: data });
            data.next.container.dispatchEvent(event);
        }
        triggerOnceSetup(data) {
            console.log("Global Hooks: onceSetup");
            this.triggerEvent("onceSetup", data);
        }
        triggerOncePlay(data) {
            console.log("Global Hooks: oncePlay");
            this.triggerEvent("oncePlay", data);
            requestAnimationFrame(
                () => window.scrollY === 0 && window.scrollTo(0, 1),
            );
        }
        triggerEnterSetup(data) {
            console.log("Global Hooks: enterSetup");
            mouse.init();
            this.triggerEvent("enterSetup", data);
            // Rebuild mouse cursor bindings for newly loaded Barba container
            if (
                typeof mouse !== "undefined" &&
                typeof mouse.updateHtml === "function"
            ) {
                mouse.updateHtml();
            }
            requestAnimationFrame(
                () => window.scrollY === 0 && window.scrollTo(0, 1),
            );
        }
        triggerEnterPlay(data) {
            console.log("Global Hooks: enterPlay");
            this.triggerEvent("enterPlay", data);
        }
    }
    const globalHooks = new GlobalHooks();
    class TriggerSetup {
        constructor() {
            this.tlTrigger = null;
            this.once = true;
        }
        setTrigger(triggerEl, onTrigger) {
            this.tlTrigger = gsap.timeline({
                scrollTrigger: {
                    trigger: triggerEl,
                    start: "clamp(top bottom+=100%)",
                    onEnter: () => {
                        if (this.once) {
                            this.once = false;
                            this.onTrigger();
                        }
                    },
                    onEnterBack: () => {
                        if (this.once) {
                            this.once = false;
                            onTrigger();
                        }
                    },
                },
            });
        }
        cleanTrigger() {
            if (this.isPlayed) {
                this.isPlayed = false;
            }
            if (!this.once) {
                this.once = true;
            }
            if (this.tlTrigger) {
                this.tlTrigger.kill();
                this.tlTrigger = null;
            }
        }
    }

    class Header {
        constructor() {
            this.el = null;
            this.$el = null;
            this.isOpen = false;
            this.listDependent = [];
            this.height = 80;
        }
        init(data) {
            this.el = document.querySelector(".header");
            if (!this.el) return;
            this.$el = $(this.el);
            this.height = this.el.offsetHeight || 80;

            window.addEventListener("resize", () => {
                if (this.el) {
                    this.height = this.el.offsetHeight || 80;
                }
            });

            gsap.fromTo('.header .container', { yPercent: -100, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, clearProps: 'all' });
            const menuBtn = this.el.querySelector(".header_menu_inner");
            const menuNav = this.el.querySelector(".header_menu_nav");

            if (menuBtn && menuNav) {
                menuBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    menuBtn.classList.toggle("active");
                    menuNav.classList.toggle("active");
                    this.isOpen = menuBtn.classList.contains("active");

                    if (this.isOpen) {
                        document.body.classList.add("menu-active");
                        if (typeof smoothScroll !== "undefined") smoothScroll.stop();
                    } else {
                        document.body.classList.remove("menu-active");
                        if (typeof smoothScroll !== "undefined") smoothScroll.start();
                    }
                });

                document.addEventListener("click", (e) => {
                    if (this.isOpen && !menuNav.contains(e.target) && !menuBtn.contains(e.target)) {
                        menuBtn.classList.remove("active");
                        menuNav.classList.remove("active");
                        this.isOpen = false;

                        document.body.classList.remove("menu-active");
                        if (typeof smoothScroll !== "undefined") smoothScroll.start();
                    }
                });
            }
        }
        update(data) {
            this.updateOnScroll(smoothScroll);
        }
        onHideDependent() {
            if (this.listDependent.length === 0) return;
            let heightHeader = this.height;
            if (!this.$el.hasClass('on-hide')) {
                this.listDependent.forEach((item) => {
                    $(item).css('top', heightHeader);
                });
            } else {
                this.listDependent.forEach((item) => {
                    $(item).css('top', 0);
                });
            }
        }
        registerDependent(dependentEl) {
            this.listDependent.push(dependentEl);
        }
        unregisterDependent(dependentEl) {
            if (this.listDependent.includes(dependentEl)) {
                this.listDependent = this.listDependent.filter((item) => item !== dependentEl);
            }
        }
        getCurrentSection(attribute, offset) {
            let sections = $(attribute);
            if (sections.length === 0) return null;

            let matchedSection = null;
            if (offset === undefined) {
                offset = cvUnit(25, "rem");
            }
            const headerHeight = this.height;

            for (let i = 0; i < sections.length; i++) {
                let rect = sections[i].getBoundingClientRect();
                if (
                    rect.top < headerHeight + offset &&
                    rect.bottom -
                    headerHeight * 0.5 -
                    offset >
                    0
                ) {
                    matchedSection = sections[i];
                }
            }
            return matchedSection ? $(matchedSection) : null;
        }
        updateOnScroll(inst) {
            if (!this.$el) return;
            this.toggleHide(inst);
            this.toggleScroll(inst);
            this.toggleMode();
            this.onHideDependent();
        }
        toggleScroll(inst) {
            const isMobile = window.innerWidth <= 991;
            const threshold = isMobile ? 0 : this.height * 2;
            if (inst.scroll > threshold)
                this.$el.addClass("on-scroll");
            else this.$el.removeClass("on-scroll");
        }
        toggleHide(inst) {
            const isMobile = window.innerWidth <= 991;
            const threshold = isMobile ? 0 : this.height * 3;
            if (inst.scroll <= threshold) {
                this.$el.removeClass("on-hide");
            } else {
                if (inst.direction == 1) {
                    this.$el.addClass("on-hide");
                } else if (inst.direction == -1) {
                    this.$el.removeClass("on-hide");
                }
            }
        }
        toggleMode() {
            let mode = this.getCurrentSection("[data-section]")?.attr("data-section");
            const currentClasses = this.$el.attr("class") || "";
            const onModeClasses = currentClasses
                .split(" ")
                .filter(
                    (cls) =>
                        cls.startsWith("on-") &&
                        cls !== "on-scroll" &&
                        cls !== "on-hide"
                );
            onModeClasses.forEach((cls) => {
                this.$el.removeClass(cls);
            });

            if (mode) {
                this.$el.attr("data-mode", mode);
            } else {
                this.$el.removeAttr("data-mode");
            }
        }
    }
    const header = new Header();

    class Footer extends TriggerSetup {
        constructor() {
            super();
            this.el = null;
            this.topTl = null;
            this.topMaster = null;
            this.contentTl = null;
            this.contentMaster = null;
            this.botTl = null;
            this.botMaster = null;
            this.menuItemsFade = null;
            this.emailFade = null;
            this.addressFade = null;
            this.formFade = null;
            this.botFade = null;
            this.videoObserver = null;
        }
        init(data) {
            this.el = document.querySelector("footer");
            if (!this.el) return;
            super.setTrigger(this.el, this.onTrigger.bind(this));
        }
        onTrigger() {
            this.setup();
            this.animFade();
        }
        setup() {
            this.topEl = this.el.querySelector('.footer_top');
            this.contentEl = this.el.querySelector('.footer_content');
            this.botEl = this.el.querySelector('.footer_bot');

            this.menuItems = this.el.querySelectorAll('.footer_top_menu_item');
            this.email = this.el.querySelector('.footer_top_email');
            this.addresses = this.el.querySelectorAll('.footer_content_address');
            this.form = this.el.querySelector('.footer_content_form');
            this.bot = this.el.querySelector('.footer_bot');
            this.formEl = this.el.querySelector('.footer_form');

            if (this.formEl) {
                this.handleSubmit = (e) => {
                    e.preventDefault();

                    let isValid = true;
                    const inputs = this.formEl.querySelectorAll('input[required]');
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            isValid = false;
                            input.style.borderColor = 'red';
                        } else {
                            input.style.borderColor = '';
                        }
                    });

                    if (isValid) {
                        const submitBtnInit = this.formEl.querySelector('.btn_submit .init');
                        let originalText = "SUBMIT";
                        if (submitBtnInit) {
                            originalText = submitBtnInit.innerText;
                            submitBtnInit.innerText = "SENDING...";
                        }

                        const submitForm = (token = '') => {
                            const formData = new FormData(this.formEl);
                            formData.append('action', 'submit_footer_form');
                            if (token) {
                                formData.append('g-recaptcha-response', token);
                            }
                            const ajaxUrl = typeof ajax_obj !== 'undefined' ? ajax_obj.ajax_url : '/wp-admin/admin-ajax.php';

                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (submitBtnInit) {
                                        submitBtnInit.innerText = originalText;
                                    }
                                    if (data.status === 1) {
                                        this.formEl.style.display = 'none';
                                        const successMsg = document.createElement('div');
                                        successMsg.className = 'txt_18 cl_be';
                                        successMsg.style.marginTop = '20px';
                                        successMsg.innerHTML = '<p>Thank you for getting in touch! We will get back to you soon.</p>';
                                        this.formEl.parentNode.appendChild(successMsg);
                                    } else {
                                        alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    if (submitBtnInit) {
                                        submitBtnInit.innerText = originalText;
                                    }
                                    alert('Có lỗi xảy ra, vui lòng thử lại sau.');
                                });
                        };

                        const siteKey = typeof caseStudyAjax !== 'undefined' ? caseStudyAjax.recaptchaSiteKey : '';
                        if (typeof grecaptcha !== 'undefined' && siteKey) {
                            grecaptcha.ready(() => {
                                grecaptcha.execute(siteKey, { action: 'submit_footer_form' })
                                    .then(token => {
                                        submitForm(token);
                                    })
                                    .catch(err => {
                                        console.error('reCAPTCHA error:', err);
                                        submitForm();
                                    });
                            });
                        } else {
                            submitForm();
                        }
                    }
                };
                this.formEl.addEventListener('submit', this.handleSubmit);
            }

            if (this.menuItems.length > 0) {
                this.menuItemsFade = new FadeIn({ el: this.menuItems, isDisableRevert: true, stagger: 0.1 });
            }
            if (this.email) {
                this.emailFade = new FadeIn({ el: this.email, type: 'bottom', isDisableRevert: true });
            }
            if (this.addresses.length > 0) {
                this.addressFade = new FadeIn({ el: this.addresses, type: 'bottom', isDisableRevert: true, stagger: 0.1 });
            }
            if (this.form) {
                this.formFade = new FadeIn({ el: this.form, type: 'bottom', isDisableRevert: true });
            }
            if (this.bot) {
                this.botFade = new FadeIn({ el: this.bot, type: 'bottom', isDisableRevert: true });
            }

            // Lazy load footer video when scroll is near (within 2000px / ~200vh)
            const lazyVideo = this.el.querySelector('.lazy-footer-video');
            if (lazyVideo) {
                if ('IntersectionObserver' in window) {
                    this.videoObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const dataSrc = lazyVideo.getAttribute('data-src');
                                if (dataSrc) {
                                    lazyVideo.setAttribute('src', dataSrc);
                                    lazyVideo.load();
                                    playVideoSafely(lazyVideo);
                                }
                                observer.unobserve(lazyVideo);
                            }
                        });
                    }, {
                        rootMargin: '2000px 0px 2000px 0px' // Load when video is within 2000px of viewport
                    });
                    this.videoObserver.observe(lazyVideo);
                } else {
                    // Fallback if IntersectionObserver is not supported
                    const dataSrc = lazyVideo.getAttribute('data-src');
                    if (dataSrc) {
                        lazyVideo.setAttribute('src', dataSrc);
                    }
                }
            }
        }
        animFade() {
            // Footer Top
            ScrollTrigger.refresh();
            if (this.topEl) {
                const topTweens = [];
                if (this.menuItemsFade) topTweens.push(this.menuItemsFade);
                if (this.emailFade) topTweens.push(this.emailFade);
                if (topTweens.length > 0) {
                    this.topMaster = new MasterTimeline({
                        triggerInit: this.topEl,
                        scrollTrigger: {
                            start: 'top top+=75%',
                            once: true
                        },
                        tweenArr: topTweens
                    });
                }
            }

            // Footer Content
            if (this.contentEl) {
                const contentTweens = [];
                if (this.addressFade) contentTweens.push(this.addressFade);
                if (this.formFade) contentTweens.push(this.formFade);
                if (contentTweens.length > 0) {
                    this.contentMaster = new MasterTimeline({
                        triggerInit: this.contentEl,
                        scrollTrigger: {
                            start: 'top top+=75%',
                            once: true
                        },
                        tweenArr: contentTweens
                    });
                }
            }

            // Footer Bottom
            if (this.botEl) {
                const botTweens = [];
                if (this.botFade) botTweens.push(this.botFade);
                if (botTweens.length > 0) {
                    this.botMaster = new MasterTimeline({
                        triggerInit: this.botEl,
                        scrollTrigger: {
                            start: 'top top+=90%',
                            once: true
                        },
                        tweenArr: botTweens
                    });
                }
            }
        }
        destroy() {
            super.cleanTrigger();
            if (this.topMaster) {
                this.topMaster.destroy();
                this.topMaster = null;
            }
            if (this.contentMaster) {
                this.contentMaster.destroy();
                this.contentMaster = null;
            }
            if (this.botMaster) {
                this.botMaster.destroy();
                this.botMaster = null;
            }
            if (this.menuItemsFade) {
                this.menuItemsFade.destroy();
                this.menuItemsFade = null;
            }
            if (this.emailFade) {
                this.emailFade.destroy();
                this.emailFade = null;
            }
            if (this.addressFade) {
                this.addressFade.destroy();
                this.addressFade = null;
            }
            if (this.formFade) {
                this.formFade.destroy();
                this.formFade = null;
            }
            if (this.botFade) {
                this.botFade.destroy();
                this.botFade = null;
            }
            if (this.formEl) {
                this.formEl.removeEventListener('submit', this.handleSubmit);
                this.formEl = null;
            }
            if (this.videoObserver) {
                this.videoObserver.disconnect();
                this.videoObserver = null;
            }
        }
    }
    const footer = new Footer();

    class Cta {
        constructor() {
            this.el = null;
            this.fadeTl = null;
            this.master = null;
            this.imgFade = null;
            this.overlayFade = null;
            this.contentSplit = null;
            this.iconFade = null;
            this.bgDecorTl = null;
            this.bgDecorMaster = null;
            this.bgTopFade = null;
            this.bgBotFade = null;
            this.bgRedFade = null;
        }
        init(data) {
            this.el = document.querySelector('.about_cta');
            if (!this.el) return;
            this.setup();
            this.animFade();
            this.animBgDecorators();
            this.animHover();
        }
        setup() {
            this.img = this.el.querySelector('.about_cta_img');
            this.content = this.el.querySelector('.about_cta_inner_content');
            this.icon = this.el.querySelector('.about_cta_inner_content_icon');
            this.link = this.el.querySelector('.about_cta_link');
            this.bgTop = this.el.querySelector('.about_cta_bg_top');
            this.bgBot = this.el.querySelector('.about_cta_bg_bot');
            this.bgRed = this.el.querySelector('.about_cta_bg_red');

            if (this.img) {
                this.imgFade = new FadeIn({ el: this.img, type: 'none', isDisableRevert: true, duration: 1.4 });
            }
            if (this.content) {
                this.contentSplit = new FadeIn({ el: this.content, type: 'bottom', isDisableRevert: true, duration: 1.0 });
            }
            if (this.bgTop) {
                this.bgTopFade = new FadeIn({ el: this.bgTop, type: 'top', isDisableRevert: true, duration: 1.0, ease: 'power2.out' });
            }
            if (this.bgBot) {
                this.bgBotFade = new FadeIn({ el: this.bgBot, type: 'bottom', isDisableRevert: true, duration: 1.0, ease: 'power2.out' });
            }
            if (this.bgRed) {
                this.bgRedFade = new FadeIn({ el: this.bgRed, type: 'left', isDisableRevert: true, duration: 1.0, ease: 'power2.out' });
            }
        }

        animHover() {
            if (!this.content || !this.icon || !this.link) return;

            this.content.addEventListener('mouseenter', () => {
                const linkWidth = this.link.offsetWidth + 6;
                // Subtract icon width if we just want to move it to the right edge.
                // The user specifically said "bằng width của about_cta_link" so:
                this.icon.style.transform = `translateX(${linkWidth}px)`;
            });

            this.content.addEventListener('mouseleave', () => {
                this.icon.style.transform = `translateX(0)`;
            });
        }

        animFade() {
            const tweenArr = [];
            if (this.imgFade) tweenArr.push(this.imgFade);
            if (this.contentSplit) tweenArr.push(this.contentSplit);
            this.master = new MasterTimeline({
                triggerInit: this.el,
                scrollTrigger: {
                    start: 'top top+=70%',
                    once: true
                },
                tweenArr,
                stagger: 0.2
            });
        }
        animBgDecorators() {
            const tweenArr = [];
            if (this.bgTopFade) tweenArr.push(this.bgTopFade);
            if (this.bgBotFade) tweenArr.push(this.bgBotFade);
            if (this.bgRedFade) tweenArr.push(this.bgRedFade);
            if (!tweenArr.length) return;

            const trigger = this.bgRed || this.bgTop || this.el;
            this.bgDecorMaster = new MasterTimeline({
                triggerInit: trigger,
                scrollTrigger: {
                    trigger: trigger,
                    start: 'top top+=80%',
                    once: false,
                    toggleActions: 'play reverse play reverse'
                },
                tweenArr,
                stagger: 0
            });
        }
        destroy() {
            if (this.master) { this.master.destroy(); this.master = null; }
            if (this.bgDecorMaster) { this.bgDecorMaster.destroy(); this.bgDecorMaster = null; }
            [this.imgFade, this.contentSplit, this.iconFade, this.bgTopFade, this.bgBotFade, this.bgRedFade]
                .forEach(a => a?.destroy?.());
            this.master = this.bgDecorMaster = null;
            this.imgFade = this.contentSplit = this.iconFade = this.bgTopFade = this.bgBotFade = this.bgRedFade = null;
        }
    }
    const cta = new Cta();

    class ButtonTop {
        constructor() {
            this.el = null;
            this.triggerST = null;
        }
        init() {
            this.el = document.querySelector(".btn_top");
            if (!this.el) return;

            const firstSection = document.querySelector('main section') || document.querySelector('.home_hero');
            if (firstSection) {
                this.triggerST = ScrollTrigger.create({
                    trigger: firstSection,
                    start: "bottom top",
                    onEnter: () => this.el.classList.add("active"),
                    onLeaveBack: () => this.el.classList.remove("active")
                });
            } else {
                this.triggerST = ScrollTrigger.create({
                    start: "top+=300 top",
                    onUpdate: (self) => {
                        if (self.scroll() > 300) {
                            this.el.classList.add("active");
                        } else {
                            this.el.classList.remove("active");
                        }
                    }
                });
            }
        }
        destroy() {
            if (this.triggerST) {
                this.triggerST.kill();
                this.triggerST = null;
            }
        }
    }
    const buttonTop = new ButtonTop();

    const HomePage = {
        Hero: class {
            constructor() {
                this.el = null;
                this.tlFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.home_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
                this.animScrub();

                // Play hero entrance animation immediately
                if (this.tlFade) this.tlFade.play();

                // Lazy load banner video
                const lazyVideo = this.el.querySelector('.lazy-home-video');
                if (lazyVideo) {
                    const dataSrc = lazyVideo.getAttribute('data-src');
                    if (dataSrc) {
                        lazyVideo.setAttribute('src', dataSrc);
                        lazyVideo.load();
                        playVideoSafely(lazyVideo);
                    }
                }
            }
            setup() {
                const video = this.el.querySelector('.lazy-home-video');
                const muteBtn = this.el.querySelector('.home_hero_mute_btn');
                if (video && muteBtn) {
                    const newMuteBtn = muteBtn.cloneNode(true);
                    muteBtn.parentNode.replaceChild(newMuteBtn, muteBtn);
                    
                    newMuteBtn.addEventListener('click', function() {
                        if (video.muted) {
                            video.muted = false;
                            newMuteBtn.querySelector('.icon-mute').style.display = 'none';
                            newMuteBtn.querySelector('.icon-unmute').style.display = 'block';
                        } else {
                            video.muted = true;
                            newMuteBtn.querySelector('.icon-unmute').style.display = 'none';
                            newMuteBtn.querySelector('.icon-mute').style.display = 'block';
                        }
                    });
                }
            }
            animFade() {
                this.tlFade = gsap.timeline({ paused: true });
                new MasterTimeline({
                    timeline: this.tlFade,
                    triggerInit: this.el,
                    tweenArr: [
                        new FadeIn({ el: this.el.querySelector('.home_hero_inner'), type: 'none' }),
                        new FadeSplitText({ el: this.el.querySelector('.home_hero_overlay_txt') }),
                        new FadeIn({ el: this.el.querySelector('.home_hero_overlay_icon'), type: 'none' }),
                    ]
                })
            }
            animScrub() {
            }
            destroy() {
                if (this.tlFade) {
                    this.tlFade.kill();
                    this.tlFade = null;
                }
                if (this.entranceTl) {
                    this.entranceTl.kill();
                    this.entranceTl = null;
                }
            }
        },
        Intro: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.introTl = null;
                this.introImgTl = null;
                this.tlFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.home_intro_wrap');
                if (!this.el) return;
                if (viewport.w <= 767) {
                    this.onTrigger();
                } else {
                    super.setTrigger(this.el, this.onTrigger.bind(this));
                }
            }
            onTrigger() {
                this.setup();
                this.animFade();
                this.animScrub();
            }
            setup() {
            }
            animFade() {
                this.tlFade = gsap.timeline({
                    scrollTrigger: {
                        trigger: '.home_intro_inner',
                        start: 'top top+=75%',
                    }
                });

                new MasterTimeline({
                    timeline: this.tlFade,
                    triggerInit: this.el,
                    tweenArr: [
                        ...Array.from($(this.el).find('.home_intro_txt')).flatMap((item) => [
                            new FadeSplitText({ el: $(item).get(0), splitType: 'chars', isDisableRevert: true }),
                        ]),
                        new FadeSplitText({ el: this.el.querySelector('.home_intro_subtxt_inner'), isDisableRevert: true })
                    ]
                })
            }
            animScrub() {
                // ── home_intro_main: horizontal scroll panel ──
                this.introTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: '.home_intro_wrap',
                        start: viewport.w > 767 ? 'top+=5% top' : 'top+=2% top',
                        end: () => viewport.w > 767 ? `bottom bottom` : `bottom+=${viewport.h / 2} bottom`,
                        scrub: true,
                        invalidateOnRefresh: true,
                    }
                });
                if (viewport.w >= 992) {
                    this.introTl.to('.home_intro_main', {
                        x: () => -viewport.w * .95,
                        ease: 'none',
                    });

                    this.introTl
                        .to('.home_intro_img_list:nth-child(1)', {
                            x: '-=90%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(2)', {
                            x: '+=55%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(3)', {
                            x: '-=45%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(4)', {
                            x: '+=80%',
                            ease: 'none',
                        }, 0);
                }
                else if (viewport.w > 767) {
                    this.introTl.to('.home_intro_main', {
                        x: () => -viewport.w * 1.8,
                        ease: 'none',
                    });

                    this.introTl
                        .to('.home_intro_img_list:nth-child(1)', {
                            x: '-=90%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(2)', {
                            x: '+=45%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(3)', {
                            x: '-=40%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(4)', {
                            x: '+=70%',
                            ease: 'none',
                        }, 0);
                }
                else {
                    this.introTl
                        .to('.home_intro_img_list:nth-child(1)', {
                            x: '-125%',
                            ease: 'none',
                        }, 0)
                        .to('.home_intro_img_list:nth-child(2)', {
                            x: '125%',
                            ease: 'none',
                        }, 0.05)
                        .to('.home_intro_img_list:nth-child(3)', {
                            x: '-125%',
                            ease: 'none',
                        }, 0.1)
                        .to('.home_intro_img_list:nth-child(4)', {
                            x: '125%',
                            ease: 'none',
                        }, 0.15);
                }

                // Helper trigger to control exit/entry locking and animation without scrub conflicts
                if (viewport.w > 767) {
                    this.boundaryTrigger = ScrollTrigger.create({
                        trigger: '.home_intro_wrap',
                        start: 'top+=5% top',
                        end: () => `bottom bottom`,
                        onLeave: () => {
                            smoothScroll.stop();
                            if (this.boundaryTrigger) {
                                smoothScroll.scrollToPosition(this.boundaryTrigger.end + 5);
                            }
                            if (this.introTl && this.introTl.scrollTrigger) {
                                this.introTl.scrollTrigger.disable(false);
                            }

                            gsap.timeline({
                                onComplete: () => {
                                    smoothScroll.start();
                                }
                            })
                                .to('.home_intro_img_list:nth-child(1)', { x: '-180%', duration: 1.6, ease: 'power2.inOut', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(2)', { x: '220%', duration: 1.6, ease: 'power2.inOut', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(3)', { x: '-180%', duration: 1.6, ease: 'power2.inOut', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(4)', { x: '240%', duration: 1.6, ease: 'power2.inOut', overwrite: 'auto' }, 0);
                        },
                        onEnterBack: () => {
                            smoothScroll.stop();
                            if (this.boundaryTrigger) {
                                smoothScroll.scrollToPosition(this.boundaryTrigger.end - 5);
                            }
                            if (this.introTl && this.introTl.scrollTrigger) {
                                this.introTl.scrollTrigger.disable(false);
                            }

                            let targetX1, targetX2, targetX3, targetX4;
                            if (viewport.w >= 992) {
                                targetX1 = '-30%';
                                targetX2 = '70%';
                                targetX3 = '-20%';
                                targetX4 = '90%';
                            } else if (viewport.w > 767) {
                                targetX1 = '-30%';
                                targetX2 = '45%';
                                targetX3 = '-15%';
                                targetX4 = '80%';
                            } else {
                                targetX1 = '-40%';
                                targetX2 = '35%';
                                targetX3 = '-25%';
                                targetX4 = '35%';
                            }

                            gsap.timeline({
                                onComplete: () => {
                                    if (this.introTl && this.introTl.scrollTrigger) {
                                        this.introTl.scrollTrigger.enable(false);
                                    }
                                    smoothScroll.start();
                                }
                            })
                                .to('.home_intro_img_list:nth-child(1)', { x: targetX1, duration: 1, ease: 'power2.out', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(2)', { x: targetX2, duration: 1, ease: 'power2.out', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(3)', { x: targetX3, duration: 1, ease: 'power2.out', overwrite: 'auto' }, 0)
                                .to('.home_intro_img_list:nth-child(4)', { x: targetX4, duration: 1, ease: 'power2.out', overwrite: 'auto' }, 0);
                        }
                    });
                }
            }
            destroy() {
                super.cleanTrigger();
                if (this.introTl) this.introTl.kill();
                if (this.boundaryTrigger) this.boundaryTrigger.kill();
                gsap.killTweensOf('.home_intro_img_list');
                smoothScroll.start();
            }
        },
        Clients: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.tabClickHandler = null;
                this.fadeTl = null;
                this.master = null;
                this.contentTl = null;
                this.contentMaster = null;
                this.subFade = null;
                this.titleSplit = null;
                this.tabItemsFade = null;
                this.logosFade = null;
                this.btnFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.home_clients');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
                this.animScrub();
                this.interact();
            }
            setup() {

                this.subtitle = this.el.querySelector('.home_clients_subtitle');
                this.title = this.el.querySelector('.home_clients_title');
                this.tabItems = this.el.querySelectorAll('.home_clients_tab_item');
                this.logos = this.el.querySelectorAll('.home_clients_content_item_img');
                this.btn = this.el.querySelector('.home_clients_button');

                if (this.subtitle) {
                    this.subFade = new FadeIn({ el: this.subtitle, type: 'bottom', isDisableRevert: true });
                }
                if (this.title) {
                    this.titleSplit = new FadeSplitText({ el: this.title, splitType: 'chars' });
                }
                if (this.tabItems.length > 0) {
                    this.tabItemsFade = new FadeIn({ el: this.tabItems, type: 'bottom', isDisableRevert: true, stagger: 0.1 });
                }
                if (this.logos.length > 0) {
                    this.logosFade = new FadeIn({ el: this.logos, type: 'bottom', isDisableRevert: true, stagger: 0.05 });
                }
                if (this.btn) {
                    this.btnFade = new FadeIn({ el: this.btn, type: 'bottom', isDisableRevert: true, delay: 1 });
                }

                this.pattern = this.el.querySelector('.home_clients_pattern');
                if (this.pattern) {
                    gsap.set(this.pattern, { opacity: 0 });
                }
            }
            interact() {
                this.tabClickHandler = function () {
                    if ($(this).hasClass('active')) return;

                    $('.home_clients_tab_item').removeClass('active');
                    $(this).addClass('active');

                    let tabId = $(this).attr('data-tabs');

                    let tabContainer = $('.home_clients_tab');
                    if (tabContainer.length) {
                        let itemRect = $(this)[0].getBoundingClientRect();
                        let containerRect = tabContainer[0].getBoundingClientRect();
                        let relativeLeft = itemRect.left - containerRect.left;
                        let targetScroll = tabContainer[0].scrollLeft + relativeLeft;

                        tabContainer.animate({
                            scrollLeft: targetScroll
                        }, 400);
                    }

                    $('.home_clients_content_item').hide();

                    const $target = $('.home_clients_content_item[data-tabs="' + tabId + '"]');
                    $target.css('display', 'flex');

                    const targetLogos = $target.find('.home_clients_content_item_img').get();
                    if (targetLogos.length > 0) {
                        gsap.fromTo(targetLogos,
                            { opacity: 0, y: 15 },
                            {
                                opacity: 1,
                                y: 0,
                                duration: 0.6,
                                ease: 'power2.out',
                                stagger: 0.03,
                                clearProps: 'all'
                            }
                        );
                    }
                    ScrollTrigger.refresh();
                };

                $('.home_clients_tab_item').on('click', this.tabClickHandler);
            }
            animFade() {
                // 1. Header Timeline (subtitle, title, tab items)
                this.fadeTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.subtitle || this.el,
                        start: 'top top+=75%',
                        once: true
                    }
                });

                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.tabItemsFade) tweenArr.push(this.tabItemsFade);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.subtitle || this.el,
                    tweenArr: tweenArr
                });

                // 2. Content Timeline (logo grid + button)
                const contentArea = this.el.querySelector('.home_clients_content');
                if (contentArea && this.logosFade) {
                    this.contentTl = gsap.timeline({
                        scrollTrigger: {
                            trigger: contentArea,
                            start: 'top top+=75%',
                            once: true
                        }
                    });

                    const contentTweenArr = [this.logosFade];
                    if (this.btnFade) contentTweenArr.push(this.btnFade);

                    this.contentMaster = new MasterTimeline({
                        timeline: this.contentTl,
                        triggerInit: contentArea,
                        tweenArr: contentTweenArr
                    });
                }
            }
            animScrub() {
                if (viewport.w <= 767) return; // Skip on mobile
                if (!this.pattern) return;

                this.scrubTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.el,
                        start: 'top top+=75%',
                        toggleActions: 'play reset play reset',
                        invalidateOnRefresh: true
                    }
                });

                this.scrubTl.fromTo(this.pattern,
                    { opacity: 0, xPercent: -50 },
                    { opacity: 1, xPercent: 0, duration: 1.2, ease: 'power2.out' }
                );
            }
            destroy() {
                super.cleanTrigger();
                if (this.tabClickHandler) {
                    $('.home_clients_tab_item').off('click', this.tabClickHandler);
                }
                if (this.fadeTl) {
                    this.fadeTl.kill();
                    this.fadeTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.contentTl) {
                    this.contentTl.kill();
                    this.contentTl = null;
                }
                if (this.contentMaster) {
                    this.contentMaster.destroy();
                    this.contentMaster = null;
                }
                if (this.subFade) {
                    this.subFade.destroy();
                    this.subFade = null;
                }
                if (this.titleSplit) {
                    this.titleSplit.destroy();
                    this.titleSplit = null;
                }
                if (this.tabItemsFade) {
                    this.tabItemsFade.destroy();
                    this.tabItemsFade = null;
                }
                if (this.logosFade) {
                    this.logosFade.destroy();
                    this.logosFade = null;
                }
                if (this.btnFade) {
                    this.btnFade.destroy();
                    this.btnFade = null;
                }
            }
        },
        Services: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.servicesTl = null;
            }
            trigger(data) {
                this.el = document.querySelector('.home_services');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
                this.animScrub();
            }
            setup() {
                gsap.registerPlugin(ScrollTrigger);

                const cmsWrap = this.el.querySelector('.home_services_cms_wrap');
                const items = this.el.querySelectorAll('.home_services_item');
                const itemCount = items.length;
                if (cmsWrap && itemCount > 0) {
                    cmsWrap.style.height = `${itemCount * 100}dvh`;
                }

                items.forEach((item, index) => {
                    // Dynamic z-index (higher item = higher z-index)
                    item.style.zIndex = index + 1;
                });

                // Query the elements for the top title split & fadein
                this.sub = this.el.querySelector('.home_services_sub');
                this.title = this.el.querySelector('.home_services_title');
                this.desc = this.el.querySelector('.home_services_des');
                this.bgItems = this.el.querySelectorAll('.home_services_top_bg');

                // Create FadeSplitText
                if (this.sub) {
                    this.subFade = new FadeIn({ el: this.sub, type: 'bottom', isDisableRevert: true });
                }
                if (this.title) {
                    this.fadeSplitTitle = new FadeSplitText({ el: this.title, splitType: 'chars' });
                }
                if (this.desc) {
                    this.descFade = new FadeSplitText({ el: this.desc, splitType: 'chars' });
                }

                // Create FadeIn for top background SVGs
                if (this.bgItems.length > 0) {
                    this.bgFade = new FadeIn({
                        el: this.bgItems,
                        type: 'none',
                        isDisableRevert: true,
                        stagger: 0.2,
                        duration: 1.2,
                        ease: 'power2.out',
                        from: { scale: 0.95 },
                        to: { scale: 1 },
                        delay: 0
                    });
                }

                // Setup for the first item inside cms wrap
                if (items.length > 0) {
                    this.firstItemInner = items[0].querySelector('.home_services_item_inner');
                    if (this.firstItemInner) {
                        const firstTitle = this.firstItemInner.querySelector('.home_services_content_title');
                        this.firstSub = this.firstItemInner.querySelector('.home_services_content_sub');
                        this.firstDes = this.firstItemInner.querySelector('.home_services_content_bottom_des');
                        this.firstListItems = this.firstItemInner.querySelectorAll('.home_services_content_bottom_list_item');
                        this.firstImg = this.firstItemInner.querySelector('.home_services_img img');

                        // Initialize split text for the first slide's title
                        if (firstTitle) {
                            this.firstTitleSplit = new FadeSplitText({ el: firstTitle, splitType: 'chars' });
                        }
                        if (this.firstImg) {
                            this.firstImgAnim = new ScaleInset({ el: this.firstImg, isDisableRevert: true });
                        }
                        if (this.firstSub) {
                            this.firstSubFade = new FadeIn({
                                el: this.firstSub,
                                type: 'bottom',
                                isDisableRevert: true,
                            });
                        }
                        if (this.firstDes) {
                            this.firstDesFade = new FadeIn({
                                el: this.firstDes,
                                type: 'bottom',
                                isDisableRevert: true,
                            });
                        }
                        if (this.firstListItems.length > 0) {
                            this.firstListItemsFade = new FadeIn({
                                el: this.firstListItems,
                                type: 'bottom',
                                isDisableRevert: true,
                                stagger: 0.08,
                            });
                        }
                    }
                }
            }
            animFade() {
                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.fadeSplitTitle) tweenArr.push(this.fadeSplitTitle);
                if (this.descFade) tweenArr.push(this.descFade);
                if (this.bgFade) tweenArr.push(this.bgFade);

                const topTrigger = this.el.querySelector('.home_services_top') || this.el;
                this.master = new MasterTimeline({
                    triggerInit: topTrigger,
                    scrollTrigger: {
                        start: 'top top+=80%',
                        once: true,
                    },
                    tweenArr: tweenArr
                });

                // 2. Separate scroll-trigger timeline for the first item content
                const cmsWrap = this.el.querySelector('.home_services_cms_wrap');
                if (this.firstItemInner && cmsWrap) {
                    const firstTweenArr = [];
                    if (this.firstTitleSplit) firstTweenArr.push(this.firstTitleSplit);
                    if (this.firstImgAnim) firstTweenArr.push(this.firstImgAnim);
                    if (this.firstSubFade) firstTweenArr.push(this.firstSubFade);
                    if (this.firstDesFade) firstTweenArr.push(this.firstDesFade);
                    if (this.firstListItemsFade) firstTweenArr.push(this.firstListItemsFade);

                    this.firstItemMaster = new MasterTimeline({
                        triggerInit: cmsWrap,
                        scrollTrigger: {
                            start: 'top top+=65%',
                            once: true
                        },
                        tweenArr: firstTweenArr
                    });
                }
            }
            animScrub() {
                const cmsWrap = this.el.querySelector('.home_services_cms_wrap');
                if (!cmsWrap) return;
                const items = cmsWrap.querySelectorAll('.home_services_item');
                if (items.length === 0) return;

                this.servicesTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: cmsWrap,
                        start: 'top+=6% top',
                        end: 'bottom bottom',
                        scrub: true,
                        invalidateOnRefresh: true,
                        onUpdate: (self) => {
                            gsap.set('.home_services_progress', { width: `${self.progress * 100}%` });
                        }
                    }
                });

                items.forEach((item, index) => {
                    // Animating slides 2, 3, etc.
                    if (index > 0) {
                        this.servicesTl.to(item, {
                            width: '100vw',
                            ease: 'none',
                            onStart: () => {
                                const img = item.querySelector('img.lazyload');
                                if (img && img.dataset.src) {
                                    img.src = img.dataset.src;
                                    img.classList.remove('lazyload');
                                }
                            }
                        });
                    }
                });
            }
            destroy() {
                super.cleanTrigger();
                if (this.servicesTl) {
                    this.servicesTl.kill();
                    this.servicesTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.subFade) {
                    this.subFade.destroy();
                    this.subFade = null;
                }
                if (this.fadeSplitTitle) {
                    this.fadeSplitTitle.destroy();
                    this.fadeSplitTitle = null;
                }
                if (this.bgFade) {
                    this.bgFade.destroy();
                    this.bgFade = null;
                }

                // Clean up first item animations
                if (this.firstItemMaster) {
                    this.firstItemMaster.destroy();
                    this.firstItemMaster = null;
                }
                if (this.firstTitleSplit) {
                    this.firstTitleSplit.destroy();
                    this.firstTitleSplit = null;
                }
                if (this.firstImgAnim) {
                    this.firstImgAnim.destroy();
                    this.firstImgAnim = null;
                }
                if (this.firstSubFade) {
                    this.firstSubFade.destroy();
                    this.firstSubFade = null;
                }
                if (this.firstDesFade) {
                    this.firstDesFade.destroy();
                    this.firstDesFade = null;
                }
                if (this.firstListItemsFade) {
                    this.firstListItemsFade.destroy();
                    this.firstListItemsFade = null;
                }
                this.firstItemInner = null;
            }
        },
        Specialize: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.spans = [];
                this.splits = [];
                this.timer = null;
                this.currentIndex = 0;
                this.isAnimating = false;
                this.originalHTMLs = [];
            }
            trigger(data) {
                this.el = document.querySelector('.home_specialize_wrap');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
                viewport.w > 767 && this.animScrub();
            }

            setup() {
                this.spans = Array.from(this.el.querySelectorAll('.home_specialize_inner_txt.main span'));

                this.splits = [];
                this.originalHTMLs = [];
                this.currentIndex = 0;
                this.isAnimating = false;

                // Query the static line elements and subtext element
                this.line1 = this.el.querySelector('.home_specialize_inner_txt:nth-child(1)');
                this.line3 = this.el.querySelector('.home_specialize_inner_txt:nth-child(3)');
                this.subtxt = this.el.querySelector('.home_specialize_subtxt_txt');

                // Store original HTML of static elements
                if (this.line1) this.originalLine1HTML = this.line1.innerHTML;
                if (this.line3) this.originalLine3HTML = this.line3.innerHTML;
                if (this.subtxt) this.originalSubtxtHTML = this.subtxt.innerHTML;

                // Create FadeSplitText for static lines
                if (this.line1) {
                    this.fadeSplit1 = new FadeSplitText({ el: this.line1, splitType: 'chars' });
                }
                if (this.line3) {
                    this.fadeSplit3 = new FadeSplitText({ el: this.line3, splitType: 'chars', delay: '<=0.2' });
                }
                if (this.subtxt) {
                    this.fadeSplitSub = new FadeSplitText({ el: this.subtxt, splitType: 'chars', delay: '<=0.2' });
                }

                // Hide all spans initially, split their characters and align gradient backgrounds using FadeSplitText
                this.spans.forEach((span, index) => {
                    this.originalHTMLs.push(span.innerHTML);
                    span.style.display = 'inline-block';

                    if (index === 0) {
                        this.fadeSplitActive = new FadeSplitText({ el: span, splitType: 'chars', isDisableRevert: true, delay: '<=0.2', forceSplitOnMobile: true });
                        this.fadeSplitActive.init();
                        this.splits.push(this.fadeSplitActive);
                    } else {
                        const inactiveSplit = new FadeSplitText({ el: span, splitType: 'chars', isDisableRevert: true, isDisableAnim: true, forceSplitOnMobile: true });
                        inactiveSplit.init();
                        this.splits.push(inactiveSplit);
                    }

                    // Hide inactive spans initially to prevent ghosting
                    gsap.set(span, { opacity: index === 0 ? 1 : 0 });
                });

                // Query background decorative elements
                this.topDeco = this.el.querySelector('.home_specialize_bg_deco_item.top');
                this.centerDeco = this.el.querySelector('.home_specialize_bg_deco_item.center');
                this.bottomDeco = this.el.querySelector('.home_specialize_bg_deco_item.bottom');

                // Hide deco elements initially for fade entry
                if (this.topDeco) gsap.set(this.topDeco, { opacity: 0, yPercent: -40 });
                if (this.centerDeco) gsap.set(this.centerDeco, { opacity: 0, xPercent: -80 });
                if (this.bottomDeco) gsap.set(this.bottomDeco, { opacity: 0, yPercent: 40 });
            }
            animFade() {
                this.fadeTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.el,
                        start: 'top top+=70%',
                        once: true,
                    },
                    onComplete: () => {
                        this.startLoop();
                    }
                });

                // Fade in decorative items along with text fade
                if (this.topDeco) {
                    this.fadeTl.to(this.topDeco, { opacity: 1, yPercent: 0, duration: 1.2, ease: 'power2.out' }, 0);
                }
                if (this.centerDeco) {
                    this.fadeTl.to(this.centerDeco, { opacity: 1, xPercent: 0, duration: 1.2, ease: 'power2.out' }, 0.2);
                }
                if (this.bottomDeco) {
                    this.fadeTl.to(this.bottomDeco, { opacity: 1, yPercent: 0, duration: 1.2, ease: 'power2.out' }, 0.4);
                }

                const tweenArr = [];
                if (this.fadeSplit1) tweenArr.push(this.fadeSplit1);
                if (this.fadeSplitActive) tweenArr.push(this.fadeSplitActive);
                if (this.fadeSplit3) tweenArr.push(this.fadeSplit3);
                if (this.fadeSplitSub) tweenArr.push(this.fadeSplitSub);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.el,
                    tweenArr: tweenArr
                });
            }
            animScrub() {
                // Find parallax and rotating elements inside the sticky section
                const topDeco = this.el.querySelector('.home_specialize_bg_deco_item.top');
                const centerDeco = this.el.querySelector('.home_specialize_bg_deco_item.center');
                const bottomDeco = this.el.querySelector('.home_specialize_bg_deco_item.bottom');
                const bgColor = this.el.querySelector('.home_specialize_bg_color_inner');
                const bgImg = this.el.querySelector('.home_specialize_bg img');

                // Create a scroll-scrubbed timeline tied to the sticky scroll container
                this.scrubTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.el,
                        start: 'top+=10% top',
                        end: `bottom-=${viewport.h * 2} bottom`,
                        scrub: true,
                        invalidateOnRefresh: true
                    }
                });

                if (topDeco) {
                    this.scrubTl.to(topDeco, {
                        autoAlpha: 0,
                        yPercent: -4,
                        ease: 'none'
                    }, 0);
                }
                if (bottomDeco) {
                    this.scrubTl.to(bottomDeco, {
                        autoAlpha: 0,
                        yPercent: 4,
                        ease: 'none'
                    }, 0);
                }
                if (centerDeco) {
                    this.scrubTl.to(centerDeco, {
                        autoAlpha: 0,
                        xPercent: -6,
                        ease: 'none'
                    }, 0);
                }
                if (bgColor) {
                    this.scrubTl.to(bgColor, {
                        scaleX: () => {
                            const vw = window.innerWidth;
                            const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
                            const paddingContainerStr = getComputedStyle(document.documentElement).getPropertyValue('--padding-container').trim() || '6.5rem';
                            const paddingValue = parseFloat(paddingContainerStr);
                            const gap = paddingContainerStr.endsWith('rem') ? paddingValue * rootFontSize : paddingValue;
                            return (vw - 2 * gap) / vw;
                        },
                        scaleY: () => {
                            const vh = window.innerHeight;
                            const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
                            const paddingContainerStr = getComputedStyle(document.documentElement).getPropertyValue('--padding-container').trim() || '6.5rem';
                            const paddingValue = parseFloat(paddingContainerStr);
                            const gap = paddingContainerStr.endsWith('rem') ? paddingValue * rootFontSize : paddingValue;
                            return (vh - 2 * gap) / vh;
                        },
                        ease: 'none',
                        transformOrigin: "center center",
                    }, 0.4);
                }
                if (bgImg) {
                    this.scrubTl.to(bgImg, {
                        scale: 1.3,
                        ease: 'none',
                        transformOrigin: "center center",
                    }, 0.4);
                }
            }
            startLoop() {
                if (!this.spans || this.spans.length === 0) return;
                if (this.timer) {
                    clearInterval(this.timer);
                }

                // Initial container height setup for auto-expand logic
                const container = this.spans[0].parentElement;
                gsap.set(container, { height: this.spans[0].offsetHeight });

                // Unified autoplay for both desktop and mobile
                this.timer = setInterval(() => {
                    const nextIndex = (this.currentIndex + 1) % this.spans.length;
                    this.goTo(nextIndex);
                }, 3000);
            }

            goTo(targetIndex) {
                if (this.currentIndex === targetIndex) return;

                const currentSplit = this.splits[this.currentIndex];
                const nextSplit = this.splits[targetIndex];

                if (!currentSplit || !nextSplit || !currentSplit.textSplit || !nextSplit.textSplit) return;

                const oldIndex = this.currentIndex;
                this.currentIndex = targetIndex;

                // Ensure all other splits are hidden to prevent ghosting
                this.splits.forEach((split, idx) => {
                    if (idx !== oldIndex && idx !== targetIndex && split.textSplit) {
                        gsap.killTweensOf(split.textSplit.chars);
                        gsap.set(split.textSplit.chars, { yPercent: 105 });
                    }
                });

                gsap.killTweensOf(currentSplit.textSplit.chars);
                gsap.killTweensOf(nextSplit.textSplit.chars);
                gsap.killTweensOf(this.spans[oldIndex]);
                gsap.killTweensOf(this.spans[targetIndex]);

                // Determine scroll direction for animation (handle wrap around for desktop auto-loop)
                const isScrollingDown = targetIndex > oldIndex || (oldIndex === this.spans.length - 1 && targetIndex === 0);
                const startY = isScrollingDown ? 105 : -105;
                const endY = isScrollingDown ? -105 : 105;

                gsap.set(nextSplit.textSplit.chars, { yPercent: startY });
                gsap.set(this.spans[targetIndex], { opacity: 1 });

                const tl = gsap.timeline();

                const container = this.spans[0].parentElement;
                const targetHeight = this.spans[targetIndex].offsetHeight;
                tl.to(container, {
                    height: targetHeight,
                    duration: 0.6,
                    ease: 'power2.inOut'
                }, 0);

                tl.to(currentSplit.textSplit.chars, {
                    yPercent: endY,
                    duration: 0.6,
                    ease: 'power2.inOut',
                    stagger: 0.01,
                    onComplete: () => {
                        gsap.set(this.spans[oldIndex], { opacity: 0 });
                    }
                }, 0);

                tl.to(nextSplit.textSplit.chars, {
                    yPercent: 2,
                    duration: 0.6,
                    ease: 'power2.inOut',
                    stagger: 0.01
                }, 0.1);
            }
            next() {
                if (this.isAnimating) return;
                this.isAnimating = true;

                const nextIndex = (this.currentIndex + 1) % this.spans.length;
                const currentSplit = this.splits[this.currentIndex];
                const nextSplit = this.splits[nextIndex];

                // Fallback for mobile/tablet where text splitting is disabled
                if (!currentSplit || !nextSplit || !currentSplit.textSplit || !nextSplit.textSplit) {
                    const currentSpan = this.spans[this.currentIndex];
                    const nextSpan = this.spans[nextIndex];

                    if (currentSpan && nextSpan) {
                        // Clear the global timer to take manual control of timing on mobile
                        if (this.timer) {
                            clearInterval(this.timer);
                            clearTimeout(this.timer);
                            this.timer = null;
                        }

                        // Multi-line typing effect on mobile (string manipulation)
                        gsap.set([currentSpan, nextSpan], { clearProps: 'overflow,whiteSpace,borderRight,width' });
                        gsap.set(nextSpan, { display: 'inline-block', opacity: 1 });

                        const textErasing = currentSpan.getAttribute('data-text') || currentSpan.innerText;
                        if (!currentSpan.hasAttribute('data-text')) currentSpan.setAttribute('data-text', textErasing);

                        const textTyping = nextSpan.getAttribute('data-text') || nextSpan.innerText;
                        if (!nextSpan.hasAttribute('data-text')) nextSpan.setAttribute('data-text', textTyping);

                        currentSpan.innerHTML = textErasing + '<span class="cursor" style="border-right: 0.05em solid currentColor;"></span>';
                        nextSpan.innerHTML = '<span class="cursor" style="border-right: 0.05em solid currentColor;"></span>';
                        gsap.set(nextSpan, { display: 'none' });

                        const lenErase = textErasing.length;
                        const lenType = textTyping.length;

                        const durationErase = Math.max(0.3, lenErase * 0.04); // 40ms per character, min 0.3s
                        const durationType = Math.max(1.0, lenType * 0.12); // 120ms per character

                        const obj = { erase: lenErase, type: 0 };

                        const tl = gsap.timeline({
                            onComplete: () => {
                                this.currentIndex = nextIndex;
                                this.isAnimating = false;

                                // Blinking cursor during the 3s rest after typing
                                const cursor = nextSpan.querySelector('.cursor');
                                if (cursor) {
                                    gsap.fromTo(cursor, { opacity: 1 }, { opacity: 0, duration: 0.4, repeat: -1, yoyo: true, ease: 'steps(1)' });
                                }

                                // Schedule next word after 3s (desktop only)
                                if (viewport.w > 767) {
                                    this.timer = setTimeout(() => {
                                        this.next();
                                    }, 3000);
                                }
                            }
                        });

                        // Erase current text
                        tl.to(obj, {
                            erase: 0,
                            duration: durationErase,
                            ease: 'none',
                            onUpdate: () => {
                                currentSpan.innerHTML = textErasing.substring(0, Math.floor(obj.erase)) + '<span class="cursor" style="border-right: 0.05em solid currentColor;"></span>';
                            },
                            onComplete: () => {
                                gsap.set(currentSpan, { display: 'none', opacity: 0 });
                                gsap.set(nextSpan, { display: 'inline-block' });
                                nextSpan.innerHTML = '<span class="cursor" style="border-right: 0.05em solid currentColor;"></span>';
                            }
                        });
                        // Type next text immediately (no delay between erase and type)
                        tl.to(obj, {
                            type: lenType,
                            duration: durationType,
                            ease: 'none',
                            onUpdate: () => {
                                nextSpan.innerHTML = textTyping.substring(0, Math.floor(obj.type)) + '<span class="cursor" style="border-right: 0.05em solid currentColor;"></span>';
                            }
                        });
                    } else {
                        this.currentIndex = nextIndex;
                        this.isAnimating = false;
                    }
                    return;
                }

                // Ensure next characters are prepared at yPercent: 105 before animating
                gsap.set(nextSplit.textSplit.chars, { yPercent: 105 });

                const tl = gsap.timeline({
                    onComplete: () => {
                        this.currentIndex = nextIndex;
                        this.isAnimating = false;
                    }
                });

                // 1. Current active characters: animate to -105% (exit)
                tl.to(currentSplit.textSplit.chars, {
                    yPercent: -105,
                    duration: 0.8,
                    ease: 'power2.inOut',
                    stagger: 0.02
                }, 0);

                // 2. Next active characters: animate to 2% (entrance)
                tl.to(nextSplit.textSplit.chars, {
                    yPercent: 2,
                    duration: 0.8,
                    ease: 'power2.inOut',
                    stagger: 0.02
                }, 0.15);
            }
            destroy() {
                super.cleanTrigger();
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.fadeSplit1) {
                    this.fadeSplit1.destroy();
                    this.fadeSplit1 = null;
                }
                if (this.fadeSplit3) {
                    this.fadeSplit3.destroy();
                    this.fadeSplit3 = null;
                }
                if (this.fadeSplitSub) {
                    this.fadeSplitSub.destroy();
                    this.fadeSplitSub = null;
                }
                if (this.scrubTl) {
                    this.scrubTl.kill();
                    this.scrubTl = null;
                }
                if (this.line1 && this.originalLine1HTML !== undefined) {
                    this.line1.innerHTML = this.originalLine1HTML;
                }
                if (this.line3 && this.originalLine3HTML !== undefined) {
                    this.line3.innerHTML = this.originalLine3HTML;
                }
                if (this.subtxt && this.originalSubtxtHTML !== undefined) {
                    this.subtxt.innerHTML = this.originalSubtxtHTML;
                }
                if (this.splits) {
                    this.splits.forEach(split => {
                        if (split) split.destroy();
                    });
                    this.splits = [];
                }
                if (this.spans && this.originalHTMLs) {
                    this.spans.forEach((span, index) => {
                        if (this.originalHTMLs[index] !== undefined) {
                            span.innerHTML = this.originalHTMLs[index];
                        }
                    });
                    this.originalHTMLs = [];
                }
            }
        },
        Case: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.listTl = null;
                this.listMaster = null;
                this.subFade = null;
                this.titleSplit = null;
                this.itemsFade = null;
                this.seeviewFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.home_case');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
            }
            setup() {
                this.subtitle = this.el.querySelector('.home_case_subtitle');
                this.title = this.el.querySelector('.home_case_title');
                this.items = this.el.querySelectorAll('.home_case_content_item');
                this.seeview = this.el.querySelector('.home_case_seeview');

                if (this.subtitle) {
                    this.subFade = new FadeIn({ el: this.subtitle, type: 'bottom', isDisableRevert: true });
                }
                if (this.title) {
                    this.titleSplit = new FadeSplitText({ el: this.title });
                }

                const isMobile = getScreenType().isMobile;
                if (this.items.length > 0) {
                    if (isMobile) {
                        this.itemsFade = [];
                        this.items.forEach(item => {
                            this.itemsFade.push(new FadeIn({
                                el: item,
                                type: 'bottom',
                                isDisableRevert: true
                            }));
                        });
                    } else {
                        this.itemsFade = new FadeIn({
                            el: this.items,
                            type: 'bottom',
                            isDisableRevert: true,
                            stagger: 0.1
                        });
                    }
                }
                if (this.seeview) {
                    this.seeviewFade = new FadeIn({
                        el: this.seeview,
                        type: 'bottom',
                        isDisableRevert: true,
                        delay: isMobile ? 0 : 1,
                    });
                }
            }
            animFade() {
                // 1. Trigger timeline for subtitle & title
                this.fadeTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.subtitle || this.el,
                        start: 'top top+=75%',
                        once: true
                    }
                });

                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.subtitle || this.el,
                    tweenArr: tweenArr
                });

                // 2. Trigger timeline for content list
                const isMobile = getScreenType().isMobile;
                if (isMobile) {
                    this.listMasters = [];
                    if (this.itemsFade && this.itemsFade.length > 0) {
                        this.itemsFade.forEach((itemFade, index) => {
                            const itemEl = this.items[index];
                            this.listMasters.push(new MasterTimeline({
                                triggerInit: itemEl,
                                scrollTrigger: {
                                    start: "top bottom-=10%",
                                    once: true
                                },
                                tweenArr: [itemFade]
                            }));
                        });
                    }
                    if (this.seeviewFade) {
                        this.seeviewMaster = new MasterTimeline({
                            triggerInit: this.seeview,
                            scrollTrigger: {
                                start: "top bottom-=10%",
                                once: true
                            },
                            tweenArr: [this.seeviewFade]
                        });
                    }
                } else {
                    const contentList = this.el.querySelector('.home_case_content_list');
                    if (contentList && this.itemsFade) {
                        this.listTl = gsap.timeline({
                            scrollTrigger: {
                                trigger: contentList,
                                start: 'top top+=75%',
                                once: true
                            }
                        });

                        const listTweenArr = [this.itemsFade];
                        if (this.seeviewFade) listTweenArr.push(this.seeviewFade);

                        this.listMaster = new MasterTimeline({
                            timeline: this.listTl,
                            triggerInit: contentList,
                            tweenArr: listTweenArr
                        });
                    }
                }
            }
            destroy() {
                super.cleanTrigger();
                if (this.fadeTl) {
                    this.fadeTl.kill();
                    this.fadeTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.listTl) {
                    this.listTl.kill();
                    this.listTl = null;
                }
                if (this.listMaster) {
                    this.listMaster.destroy();
                    this.listMaster = null;
                }
                if (this.listMasters) {
                    this.listMasters.forEach(m => m.destroy());
                    this.listMasters = null;
                }
                if (this.seeviewMaster) {
                    this.seeviewMaster.destroy();
                    this.seeviewMaster = null;
                }
                if (this.subFade) {
                    this.subFade.destroy();
                    this.subFade = null;
                }
                if (this.titleSplit) {
                    this.titleSplit.destroy();
                    this.titleSplit = null;
                }
                if (this.itemsFade) {
                    if (Array.isArray(this.itemsFade)) {
                        this.itemsFade.forEach(item => item.destroy());
                    } else {
                        this.itemsFade.destroy();
                    }
                    this.itemsFade = null;
                }
                if (this.seeviewFade) {
                    this.seeviewFade.destroy();
                    this.seeviewFade = null;
                }
            }
        }
    };

    const OurClientPage = {
        Hero: class {
            constructor() {
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.titleSplit = null;
                this.descFade = null;
                this.subFade = null;
                this.bgFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.casestudy_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                this.title = this.el.querySelector('.casestudy_hero_title');
                this.desc = this.el.querySelector('.casestudy_hero_des');
                this.sub = this.el.querySelector('.casestudy_hero_subtitle');
                this.bg = this.el.querySelector('.casestudy_hero_bg');

                if (this.title) {
                    this.titleSplit = new FadeSplitText({
                        el: this.title,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.2,
                        stagger: 0.05,
                        ease: 'power3.out'
                    });
                }
                if (this.desc) {
                    this.descFade = new FadeSplitText({
                        el: this.desc,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.2,
                        stagger: 0.05,
                        ease: 'power3.out'
                    });
                }
                if (this.sub) {
                    this.subFade = new FadeSplitText({
                        el: this.sub,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.2,
                        stagger: 0.05,
                        ease: 'power3.out'
                    });
                }
                if (this.bg) {
                    this.bgFade = new FadeIn({
                        el: this.bg,
                        type: 'none',
                        from: { scale: 0.8, opacity: 0 },
                        to: { scale: 1, opacity: 1 },
                        isDisableRevert: true,
                        duration: 2.0,
                        ease: 'power2.out'
                    });
                }
            }
            animFade() {
                this.fadeTl = gsap.timeline();
                const tweenArr = [];
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.descFade) tweenArr.push(this.descFade);
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.bgFade) tweenArr.push(this.bgFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({
                        timeline: this.fadeTl,
                        triggerInit: this.el,
                        tweenArr: tweenArr,
                        stagger: 0.15
                    });
                }
            }
        },
        Clients: class {
            constructor() {
                this.tabClickHandler = null;
                this.observer = null;
                this.scrubTl = null;
            }
            trigger(data) {
                let container = data && data.next && data.next.container ? data.next.container : document;
                this.el = container.querySelector('.home_clients');
                if (!this.el) return;
                this.setup();
                this.animFade();
                this.animScrub();
                this.interact();
            }
            setup() {
                this.tabContainer = this.el.querySelector('.home_clients_tab');
                if (this.tabContainer) {
                    this.tabContainerFade = new FadeIn({
                        el: this.tabContainer,
                        type: 'bottom',
                        isDisableRevert: true,
                        duration: 0.8
                    });
                }

                this.tabItems = this.el.querySelectorAll('.home_clients_tab_item');
                if (this.tabItems.length) {
                    this.tabItemsFade = new FadeIn({
                        el: this.tabItems,
                        type: 'bottom',
                        isDisableRevert: true,
                        duration: 0.8,
                        stagger: 0.05
                    });
                }

                let activeTab = $(this.el).find('.home_clients_tab_item.active').first();
                let tabId = activeTab.attr('data-tabs') || 'tab1';
                let activeContent = $(this.el).find('.home_clients_content_item[data-tabs="' + tabId + '"]')[0];

                if (activeContent) {
                    const items = activeContent.querySelectorAll('.home_clients_content_item_img');
                    if (items.length) {
                        this.contentFade = new FadeIn({
                            el: items,
                            type: 'bottom',
                            isDisableRevert: true,
                            duration: 0.8,
                            stagger: 0.1
                        });
                    }
                }

                const imgs = this.el.querySelectorAll('.home_clients_content_item_img');
                if (imgs.length) {
                    gsap.set(imgs, { opacity: 0 }); // Hide initially to prevent flash before FadeIn
                }
            }
            interact() {
                let _thisEl = this.el;
                let _this = this;

                this.tabClickHandler = function () {
                    let clicked = $(this);
                    if (clicked.hasClass('active')) return;

                    $(_thisEl).find('.home_clients_tab_item').removeClass('active');

                    let tabId = clicked.attr('data-tabs');
                    $(_thisEl).find('.home_clients_tab_item[data-tabs="' + tabId + '"]').addClass('active');

                    let tabContainer = $(_thisEl).find('.home_clients_tab');
                    if (tabContainer.length) {
                        let itemRect = clicked[0].getBoundingClientRect();
                        let containerRect = tabContainer[0].getBoundingClientRect();
                        let relativeLeft = itemRect.left - containerRect.left;
                        // To account for any padding on the container, we can optionally subtract it, 
                        // but usually aligning to the left edge of the bounding rect is exactly what is needed.
                        let targetScroll = tabContainer[0].scrollLeft + relativeLeft;

                        tabContainer.animate({
                            scrollLeft: targetScroll
                        }, 400);
                    }

                    $(_thisEl).find('.home_clients_content_item').hide();
                    let targetContent = $(_thisEl).find('.home_clients_content_item[data-tabs="' + tabId + '"]');
                    targetContent.css('display', 'flex');

                    const items = targetContent.find('.home_clients_content_item_img').get();
                    if (items.length > 0) {
                        gsap.fromTo(items,
                            { opacity: 0, y: 15 },
                            {
                                opacity: 1,
                                y: 0,
                                duration: 0.6,
                                ease: 'power2.out',
                                stagger: 0.05
                            }
                        );
                    }
                    ScrollTrigger.refresh();
                };

                $(_thisEl).find('.home_clients_tab_item').on('click', this.tabClickHandler);
            }
            animFade() {
                this.fadeTl = gsap.timeline({
                    scrollTrigger: { trigger: this.el, start: 'top top+=70%', once: true }
                });

                const tweenArr = [];
                if (this.tabContainerFade) tweenArr.push(this.tabContainerFade);
                if (this.tabItemsFade) tweenArr.push(this.tabItemsFade);
                if (this.contentFade) tweenArr.push(this.contentFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({
                        timeline: this.fadeTl,
                        triggerInit: this.el,
                        tweenArr: tweenArr,
                        stagger: 0.15
                    });
                }
            }
            animScrub() {
            }
            destroy() {
                if (this.tabClickHandler && this.el) {
                    $(this.el).find('.home_clients_tab_item').off('click', this.tabClickHandler);
                }

                if (this.scrubTl) {
                    this.scrubTl.kill();
                    this.scrubTl = null;
                }
            }
        }
    };

    const AboutUsPage = {
        ParallaxBg: class {
            constructor() {
                this.parallax = null;
                this.onScroll = this.onScroll.bind(this);
            }
            trigger() {
                this.parallax = document.getElementById("parallax");
                if (this.parallax) {
                    window.addEventListener("scroll", this.onScroll);
                }
            }
            onScroll() {
                let rect = this.parallax.getBoundingClientRect();
                if (rect.top <= 0) {
                    let offset = -rect.top;
                    this.parallax.style.backgroundPositionY = (offset * 0.5) + "px";
                } else {
                    this.parallax.style.backgroundPositionY = "0px";
                }
            }
            destroy() {
                if (this.parallax) {
                    window.removeEventListener("scroll", this.onScroll);
                    this.parallax = null;
                }
            }
        },
        Hero: class {
            constructor() {
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.imgFade = null;
                this.txtSplit = null;
                this.rightFade = null;
                this.bgFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.about_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();

                // Lazy load About video
                const lazyVideo = this.el.querySelector('.lazy-about-video');
                if (lazyVideo) {
                    setTimeout(() => {
                        const dataSrc = lazyVideo.getAttribute('data-src');
                        if (dataSrc) {
                            lazyVideo.setAttribute('src', dataSrc);
                            lazyVideo.load();
                            playVideoSafely(lazyVideo);
                        }
                    }, 300);
                }
            }
            setup() {
                this.img = this.el.querySelector('.about_hero_img');
                this.txt = this.el.querySelector('.about_hero_content_txt');
                this.rightNames = this.el.querySelectorAll('.about_hero_content_right_name');
                this.bg = this.el.querySelector('.about_hero_bg');

                if (this.img) {
                    this.imgFade = new FadeIn({
                        el: this.img,
                        type: 'bottom',
                        from: { scale: 0.95 },
                        to: { scale: 1 },
                        isDisableRevert: true,
                        duration: 1.4,
                        ease: 'power3.out'
                    });
                }
                if (this.txt) {
                    this.txtSplit = new FadeSplitText({
                        el: this.txt,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.2,
                        stagger: 0.01,
                        ease: 'power3.out'
                    });
                }
                if (this.rightNames.length > 0) {
                    this.rightFade = new FadeIn({
                        el: this.rightNames,
                        type: 'bottom',
                        stagger: 0.15,
                        isDisableRevert: true,
                        duration: 1.2,
                        ease: 'power3.out'
                    });
                }
                if (this.bg) {
                    this.bgFade = new FadeIn({
                        el: this.bg,
                        type: 'none',
                        from: { scale: 0.8, opacity: 0 },
                        to: { scale: 1, opacity: 0.7 },
                        isDisableRevert: true,
                        duration: 2.0,
                        ease: 'power2.out',
                        clearProps: 'none'
                    });
                }
            }
            animFade() {
                this.fadeTl = gsap.timeline();

                const tweenArr = [];
                if (this.bgFade) tweenArr.push(this.bgFade);
                if (this.imgFade) tweenArr.push(this.imgFade);
                if (this.txtSplit) tweenArr.push(this.txtSplit);
                if (this.rightFade) tweenArr.push(this.rightFade);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.el,
                    tweenArr: tweenArr,
                    stagger: 0.25
                });
            }
            destroy() {
                if (this.fadeTl) {
                    this.fadeTl.kill();
                    this.fadeTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.imgFade) {
                    this.imgFade.destroy();
                    this.imgFade = null;
                }
                if (this.txtSplit) {
                    this.txtSplit.destroy();
                    this.txtSplit = null;
                }
                if (this.rightFade) {
                    this.rightFade.destroy();
                    this.rightFade = null;
                }
                if (this.bgFade) {
                    this.bgFade.destroy();
                    this.bgFade = null;
                }
            }
        },
        Company: class {
            constructor() {
                this.el = null;
            }
            trigger(data) {
                this.el = document.querySelector('.about_company_inner.middle');
                if (!this.el) return;
                if (window.innerWidth > 767) {
                    setTimeout(() => {
                        const bg = this.el.getAttribute('data-bg');
                        if (bg) {
                            this.el.style.backgroundImage = `url('${bg}')`;
                        }
                    }, 300);
                }
            }
        },
        Impressive: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.subFade = null;
                this.imgFade = null;
                this.titleSplit = null;
                this.desFade = null;
                this.listItemsFade = null;
                this.numAnims = [];
            }
            trigger(data) {
                this.el = document.querySelector('.about_impressive');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
            }
            setup() {
                this.sub = this.el.querySelector('.about_impressive_left_subtitle');
                this.img = this.el.querySelector('.about_impressive_left_img');
                this.title = this.el.querySelector('.about_impressive_right_title');
                this.des = this.el.querySelector('.about_impressive_right_des');
                this.items = this.el.querySelectorAll('.about_impressive_right_list_item');

                if (this.sub) {
                    this.subFade = new FadeIn({
                        el: this.sub,
                        type: 'bottom',
                        isDisableRevert: true,
                    });
                }

                if (this.title) {
                    this.titleSplit = new FadeSplitText({
                        el: this.title,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.02,
                    });
                }
                if (this.des) {
                    this.desFade = new FadeSplitText({
                        el: this.des,
                        splitType: 'words',
                        isDisableRevert: true,
                        delay: 1,
                        duration: 1.0,
                        stagger: 0.02,

                    });
                }

                this.numAnims = [];
                const numberElements = Array.from(this.el.querySelectorAll('.about_impressive_right_list_item_num'));
                numberElements.forEach((el) => {
                    const text = el.textContent.trim();
                    const match = text.match(/^([\d,]+)(.*)$/);
                    if (match) {
                        const targetVal = parseFloat(match[1].replace(/,/g, ''));
                        const suffix = match[2];
                        const obj = { val: 0 };

                        el.textContent = '0' + suffix;

                        const tween = gsap.to(obj, {
                            val: targetVal,
                            duration: 1.5,
                            ease: 'power2.out',
                            paused: true,
                            onUpdate: () => {
                                el.textContent = Math.floor(obj.val) + suffix;
                            }
                        });
                        this.numAnims.push(tween);
                    }
                });

                if (this.items.length > 0) {
                    this.listItemsFade = new FadeIn({
                        el: this.items,
                        type: 'bottom',
                        stagger: 0.1,
                        isDisableRevert: true,
                        delay: 1,
                        onStart: () => {
                            this.numAnims.forEach((tween, index) => {
                                gsap.delayedCall(index * 0.1, () => tween.play());
                            });
                        }
                    });
                }
            }
            animFade() {
                this.fadeTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.el,
                        start: 'top top+=70%',
                        once: true
                    }
                });

                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.desFade) tweenArr.push(this.desFade);
                if (this.listItemsFade) tweenArr.push(this.listItemsFade);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.el,
                    tweenArr: tweenArr,
                    stagger: 0.3
                });

                if (getScreenType().isMobile) {
                    this.mobileNumTrigger = ScrollTrigger.create({
                        trigger: this.el,
                        start: 'top bottom-=20%',
                        once: true,
                        onEnter: () => {
                            this.numAnims.forEach((tween, index) => {
                                gsap.delayedCall(index * 0.1, () => tween.play());
                            });
                        }
                    });
                }

                const imgEl = this.img?.querySelector('img');
                if (imgEl) {
                    this.imgTl = gsap.timeline({ paused: true });
                    this.imgTl.fromTo(imgEl,
                        { opacity: 0, x: -100 },
                        { opacity: 1, x: 0, duration: 1.2, ease: 'power2.out' }
                    );

                    const isMobile = getScreenType().isMobile;

                    this.imgPlayTrigger = ScrollTrigger.create({
                        trigger: this.el,
                        start: 'top bottom-=20%',
                        once: isMobile,
                        onEnter: () => this.imgTl?.play(),
                        onEnterBack: isMobile ? null : () => this.imgTl?.play()
                    });

                    if (!isMobile) {
                        this.imgResetTrigger = ScrollTrigger.create({
                            trigger: this.el,
                            start: 'top bottom',
                            end: 'bottom top',
                            onLeave: () => {
                                this.imgTl?.progress(0).pause();
                            },
                            onLeaveBack: () => {
                                this.imgTl?.progress(0).pause();
                            }
                        });
                    }
                }
            }
            destroy() {
                super.cleanTrigger();
                if (this.fadeTl) {
                    this.fadeTl.kill();
                    this.fadeTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.mobileNumTrigger) {
                    this.mobileNumTrigger.kill();
                    this.mobileNumTrigger = null;
                }
                if (this.imgTl) {
                    this.imgTl.kill();
                    this.imgTl = null;
                }
                if (this.imgPlayTrigger) {
                    this.imgPlayTrigger.kill();
                    this.imgPlayTrigger = null;
                }
                if (this.imgResetTrigger) {
                    this.imgResetTrigger.kill();
                    this.imgResetTrigger = null;
                }
                if (this.subFade) {
                    this.subFade.destroy();
                    this.subFade = null;
                }
                if (this.imgFade) {
                    this.imgFade.destroy();
                    this.imgFade = null;
                }
                if (this.titleSplit) {
                    this.titleSplit.destroy();
                    this.titleSplit = null;
                }
                if (this.desFade) {
                    this.desFade.destroy();
                    this.desFade = null;
                }
                if (this.listItemsFade) {
                    this.listItemsFade.destroy();
                    this.listItemsFade = null;
                }
                if (this.numAnims) {
                    this.numAnims.forEach(tween => tween.kill());
                    this.numAnims = [];
                }
            }
        },
        Best: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.subFade = null;
                this.titleSplit = null;
                this.leftFade = null;
                this.desFade = null;
                this.capFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.about_best');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animFade();
            }
            setup() {
                this.sub = this.el.querySelector('.about_best_subtitle');
                this.title = this.el.querySelector('.about_best_title');
                this.left = this.el.querySelector('.about_best_left');
                this.des = this.el.querySelector('.about_best_right_des');
                this.cap = this.el.querySelector('.about_best_right_cap');

                if (this.sub) {
                    this.subFade = new FadeIn({
                        el: this.sub,
                        type: 'bottom',
                        isDisableRevert: true,
                    });
                }
                if (this.title) {
                    this.titleSplit = new FadeSplitText({
                        el: this.title,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.02,
                    });
                }
                if (this.left) {
                    this.leftFade = new FadeIn({
                        el: this.left,
                        type: 'left',
                        isDisableRevert: true,
                        duration: 1.2,
                        ease: 'power2.out'
                    });
                }
                if (this.des) {
                    this.desFade = new FadeSplitText({
                        el: this.des,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.015,
                    });
                }
                if (this.cap) {
                    this.capFade = new FadeSplitText({
                        el: this.cap,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.015,
                    });
                }
            }
            animFade() {
                this.fadeTl = gsap.timeline({
                    scrollTrigger: {
                        trigger: this.el,
                        start: 'top top+=70%',
                        once: true
                    }
                });

                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.leftFade) tweenArr.push(this.leftFade);

                this.master = new MasterTimeline({
                    timeline: this.fadeTl,
                    triggerInit: this.el,
                    tweenArr: tweenArr,
                    stagger: 0.3
                });

                if (this.des) {
                    this.desCapTl = gsap.timeline({
                        scrollTrigger: {
                            trigger: this.des,
                            start: 'top top+=85%',
                            once: true
                        }
                    });
                    const desCapTweenArr = [];
                    if (this.desFade) desCapTweenArr.push(this.desFade);
                    if (this.capFade) desCapTweenArr.push(this.capFade);

                    this.desCapMaster = new MasterTimeline({
                        timeline: this.desCapTl,
                        triggerInit: this.des,
                        tweenArr: desCapTweenArr,
                        stagger: 0.2
                    });
                }
            }
            destroy() {
                super.cleanTrigger();
                if (this.fadeTl) {
                    this.fadeTl.kill();
                    this.fadeTl = null;
                }
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                if (this.desCapTl) {
                    this.desCapTl.kill();
                    this.desCapTl = null;
                }
                if (this.desCapMaster) {
                    this.desCapMaster.destroy();
                    this.desCapMaster = null;
                }
                if (this.subFade) {
                    this.subFade.destroy();
                    this.subFade = null;
                }
                if (this.titleSplit) {
                    this.titleSplit.destroy();
                    this.titleSplit = null;
                }
                if (this.leftFade) {
                    this.leftFade.destroy();
                    this.leftFade = null;
                }
                if (this.desFade) {
                    this.desFade.destroy();
                    this.desFade = null;
                }
                if (this.capFade) {
                    this.capFade.destroy();
                    this.capFade = null;
                }
            }
        },
        Team: class extends TriggerSetup {
            constructor() {
                super();
                this.el = null;
                // header group
                this.headerTl = null;
                this.headerMaster = null;
                this.subFade = null;
                this.titleSplit = null;
                this.desFade = null;
                // items
                this.itemTls = [];
                this.itemMasters = [];
                // card
                this.cardTl = null;
                this.cardMaster = null;
                this.cardImgFade = null;
                this.cardContentFade = null;
                this.cardTitleSplit = null;
                this.cardDesFade = null;
                this.cardBotFade = null;
                // bg decorators
                this.bgTl = null;
                this.bgMaster = null;
                this.bgRightTl = null;
                this.bgRightMaster = null;
                this.bgLeftFade = null;
                this.bgRightFade = null;
                // horizontal scroll
                this.ctx = null;
                this.horizontalTl = null;
                this.itemsFadeTl = null;
            }
            trigger(data) {
                this.el = document.querySelector('.about_team');
                if (!this.el) return;
                super.setTrigger(this.el, this.onTrigger.bind(this));
            }
            onTrigger() {
                this.setup();
                this.animHeader();
                this.animItems();
                this.animCard();
                this.animBg();
            }
            setup() {
                this.sub = this.el.querySelector('.about_team_subtitle');
                this.title = this.el.querySelector('.about_team_title');
                this.des = this.el.querySelector('.about_team_des');
                this.items = Array.from(this.el.querySelectorAll('.about_team_content_item_wrap, .about_team_content_inner > .about_team_content_item'));
                this.card = this.el.querySelector('.about_team_card');
                this.bgLeft = this.el.querySelector('.about_team_content_bgleft');
                this.bgRight = this.el.querySelector('.about_team_content_bgright');

                // ── header group ──
                if (this.sub) {
                    this.subFade = new FadeIn({ el: this.sub, type: 'bottom', isDisableRevert: true });
                }
                if (this.title) {
                    this.titleSplit = new FadeSplitText({
                        el: this.title,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.02,
                    });
                }
                if (this.des) {
                    this.desFade = new FadeSplitText({
                        el: this.des,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.0,
                        stagger: 0.015,
                    });
                }

                // ── card ──
                const cardImg = this.card?.querySelector('.about_team_card_img_wrap');
                const cardContent = this.card?.querySelector('.about_team_card_swiper .about_team_card_slide:first-child .about_team_card_item_content') || this.card?.querySelector('.about_team_card_item_content');
                // img và content là siblings → không có parent-child opacity conflict
                if (cardImg) {
                    this.cardImgFade = new ScaleInset({ el: cardImg, isDisableRevert: true });
                }
                if (cardContent) {
                    this.cardContentFade = new FadeIn({ el: cardContent, type: 'bottom', isDisableRevert: true, duration: 0.8 });
                    this.cardTitleSplit = new FadeIn({
                        el: cardContent.querySelector('.about_team_card_item_content_title'),
                        type: 'bottom',
                        isDisableRevert: true,
                        duration: 0.8
                    });
                    this.cardDesFade = new FadeSplitText({
                        el: cardContent.querySelector('.about_team_card_item_content_des'),
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 0.9,
                        stagger: 0.01,
                    });
                    this.cardBotFade = new FadeIn({ el: this.card.querySelector('.about_team_card_item_content_bot'), type: 'bottom', isDisableRevert: true, duration: 0.8 });
                }

                // Initialize Swiper
                this.initSwiper();
            }
            initSwiper() {
                const swiperEl = this.card?.querySelector('.about_team_card_swiper');
                const imgWrap = this.card?.querySelector('.about_team_card_img_wrap');
                if (!swiperEl || !imgWrap) return;

                const imgItems = Array.from(imgWrap.querySelectorAll('.about_team_card_img_item'));
                const textItems = Array.from(swiperEl.querySelectorAll('.about_team_card_slide'));
                if (textItems.length === 0) return;

                // Measure and set fixed width on resize
                const updateImgWidth = () => {
                    const wrapWidth = imgWrap.getBoundingClientRect().width;
                    imgWrap.style.setProperty('--card-img-width', `${wrapWidth}px`);
                };
                updateImgWidth();
                window.addEventListener('resize', updateImgWidth);
                this._destroyWidthListener = () => {
                    window.removeEventListener('resize', updateImgWidth);
                };

                // Initialize image state
                imgItems.forEach((item, idx) => {
                    if (idx === 0) {
                        gsap.set(item, { width: '100%', zIndex: 2 });
                    } else {
                        gsap.set(item, { width: '0%', zIndex: 1 });
                    }
                });

                // Initialize active class states for slides
                textItems.forEach((item, idx) => {
                    if (idx === 0) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                let lastIndex = 0;
                this.currentIndex = 0;

                const prevBtn = this.card.querySelector('.about_team_btn_prev');
                const nextBtn = this.card.querySelector('.about_team_btn_next');

                const updateButtons = (index) => {
                    if (index === 0) {
                        prevBtn?.classList.add('swiper-button-disabled');
                    } else {
                        prevBtn?.classList.remove('swiper-button-disabled');
                    }
                    if (index === textItems.length - 1) {
                        nextBtn?.classList.add('swiper-button-disabled');
                    } else {
                        nextBtn?.classList.remove('swiper-button-disabled');
                    }
                };

                updateButtons(0);

                const goToSlide = (activeIndex) => {
                    if (activeIndex === lastIndex || activeIndex < 0 || activeIndex >= textItems.length) return;

                    const isNext = activeIndex > lastIndex;

                    // Update page indicator
                    const curIdx = activeIndex + 1;
                    const curEl = this.card.querySelector('.about_team_card_item_content_bot_page_cur');
                    if (curEl) {
                        curEl.textContent = (curIdx < 10 ? '0' + curIdx : curIdx) + ' /';
                    }

                    // Kill previous timeline to prevent collision
                    if (this.textTl) {
                        this.textTl.kill();
                        this.textTl = null;
                    }

                    const oldText = textItems[lastIndex];
                    const newText = textItems[activeIndex];

                    if (oldText && newText) {
                        // Ensure oldText is absolute and newText is relative and active during transition
                        gsap.set(oldText, { position: 'absolute', opacity: 1, zIndex: 1 });
                        gsap.set(newText, { position: 'relative', opacity: 0, zIndex: 2 });
                        oldText.classList.add('active');
                        newText.classList.add('active');

                        this.textTl = gsap.timeline({
                            onComplete: () => {
                                // Normalize classes and clear inline style overrides
                                oldText.classList.remove('active');
                                gsap.set(oldText, { clearProps: 'position,opacity,zIndex' });
                                gsap.set(newText, { clearProps: 'position,opacity,zIndex' });
                                this.textTl = null;
                            }
                        });

                        // Fade out old, fade in new
                        this.textTl.to(oldText, {
                            opacity: 0,
                            duration: 0.3,
                            ease: 'power2.inOut'
                        }, 0);

                        this.textTl.to(newText, {
                            opacity: 1,
                            duration: 0.4,
                            ease: 'power2.out'
                        }, 0.1);
                    } else {
                        textItems.forEach((item, idx) => {
                            if (idx === activeIndex) {
                                item.classList.add('active');
                            } else {
                                item.classList.remove('active');
                            }
                        });
                    }

                    // Trigger image reveal
                    const newImg = imgItems[activeIndex];
                    const oldImg = imgItems[lastIndex];
                    const innerImg = newImg?.querySelector('img');

                    if (newImg && oldImg) {
                        imgItems.forEach((item, idx) => {
                            if (idx === activeIndex) {
                                gsap.set(item, { zIndex: 3 });
                            } else if (idx === lastIndex) {
                                gsap.set(item, { zIndex: 2 });
                            } else {
                                gsap.set(item, { zIndex: 1 });
                            }
                        });

                        // Configure alignment based on direction
                        if (isNext) {
                            // Reveal from Left to Right
                            gsap.set(newImg, { left: 0, right: 'auto' });
                            if (innerImg) {
                                gsap.set(innerImg, { left: 0, right: 'auto' });
                            }
                        } else {
                            // Reveal from Right to Left
                            gsap.set(newImg, { left: 'auto', right: 0 });
                            if (innerImg) {
                                gsap.set(innerImg, { left: 'auto', right: 0 });
                            }
                        }

                        gsap.fromTo(newImg,
                            { width: '0%' },
                            {
                                width: '100%',
                                duration: 0.8,
                                ease: 'power2.inOut',
                                onComplete: () => {
                                    // After reveal completes, normalize to left-anchored for responsive stability
                                    gsap.set(newImg, { left: 0, right: 'auto', width: '100%' });
                                    if (innerImg) {
                                        gsap.set(innerImg, { left: 0, right: 'auto' });
                                    }
                                    imgItems.forEach((item, idx) => {
                                        if (idx !== activeIndex) {
                                            gsap.set(item, { width: '0%', zIndex: 1 });
                                        }
                                    });
                                }
                            }
                        );
                    }

                    updateButtons(activeIndex);
                    lastIndex = activeIndex;
                    this.currentIndex = activeIndex;
                };

                this._nextHandler = () => {
                    if (this.currentIndex < textItems.length - 1) {
                        goToSlide(this.currentIndex + 1);
                    }
                };

                this._prevHandler = () => {
                    if (this.currentIndex > 0) {
                        goToSlide(this.currentIndex - 1);
                    }
                };

                nextBtn?.addEventListener('click', this._nextHandler);
                prevBtn?.addEventListener('click', this._prevHandler);

                this._destroySliderListeners = () => {
                    nextBtn?.removeEventListener('click', this._nextHandler);
                    prevBtn?.removeEventListener('click', this._prevHandler);
                };
            }
            animHeader() {
                this.headerTl = gsap.timeline({
                    scrollTrigger: { trigger: this.sub || this.title || this.des, start: 'top top+=70%', once: true }
                });
                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.desFade) tweenArr.push(this.desFade);
                this.headerMaster = new MasterTimeline({
                    timeline: this.headerTl,
                    triggerInit: this.sub || this.title || this.des,
                    tweenArr,
                    stagger: 0.3
                });
            }
            animItems() {
                this.ctx = gsap.matchMedia();

                this.ctx.add({
                    isDesktop: "(min-width: 992px)",
                    isMobile: "(max-width: 991px)"
                }, (context) => {
                    let { isDesktop, isMobile } = context.conditions;

                    if (isDesktop) {
                        const innerWrap = this.el.querySelector('.about_team_inner');
                        const wrapper = this.el.querySelector('.about_team_content');
                        const inner = this.el.querySelector('.about_team_content_inner');
                        if (innerWrap && wrapper && inner) {
                            gsap.set(inner, { x: 0 });

                            this.horizontalTl = gsap.timeline({
                                scrollTrigger: {
                                    trigger: '.about_team_inner',
                                    scrub: 1,
                                    start: "top+=5% top",
                                    end: "bottom-=5% bottom",
                                    invalidateOnRefresh: true,
                                }
                            });

                            this.horizontalTl.to(inner, {
                                x: () => -(inner.scrollWidth - wrapper.clientWidth),
                                ease: "none"
                            });

                            // Stagger fade-in the cards when wrapper enters view
                            const mainContent = this.el.querySelector('.about_team_content_main');
                            this.itemsFadeTl = gsap.timeline({
                                scrollTrigger: {
                                    trigger: mainContent || wrapper,
                                    start: viewport.w > 991 ? "top top+=80%" : "top top+=40%",
                                    once: true
                                }
                            });

                            const cards = this.el.querySelectorAll('.about_team_content_item_wrap');
                            this.itemsFadeTl.fromTo(cards,
                                { opacity: 0, y: 50 },
                                { opacity: 1, y: 0, duration: 0.8, stagger: 0.1, ease: "power2.out" }
                            );
                        }
                    }

                    if (isMobile) {
                        this.itemTls = [];
                        this.itemMasters = [];
                        this.items.forEach((item) => {
                            gsap.fromTo(item,
                                { opacity: 0, y: 32 },
                                {
                                    opacity: 1,
                                    y: 0,
                                    duration: 0.8,
                                    ease: 'power3.out',
                                    scrollTrigger: {
                                        trigger: item,
                                        start: 'top bottom-=10%',
                                        once: true
                                    }
                                }
                            );

                            const innerItem = item.classList.contains('about_team_content_item') ? item : item.querySelector('.about_team_content_item');
                            if (innerItem) {
                                // Use native scroll listener for perfect sticky detection immune to GSAP layout shifts
                                const toggleStickyClass = () => {
                                    const rectTop = item.getBoundingClientRect().top;
                                    const stickyTop = parseFloat(window.getComputedStyle(item).top);
                                    // If rectTop <= stickyTop (with 1px buffer for subpixel rendering), it is sticking or past it
                                    if (rectTop <= stickyTop + 1) {
                                        innerItem.classList.add('is-stuck');
                                    } else {
                                        innerItem.classList.remove('is-stuck');
                                    }
                                };
                                window.addEventListener('scroll', toggleStickyClass, { passive: true });
                                window.addEventListener('resize', toggleStickyClass, { passive: true });
                                toggleStickyClass(); // Initial check
                            }
                        });
                    }
                });
            }
            animCard() {
                if (!this.card) return;
                this.cardTl = gsap.timeline({
                    scrollTrigger: { trigger: this.card, start: 'top top+=70%', once: true }
                });
                // img (ScaleInset) + content block (FadeIn) chạy song song — là siblings, không conflict
                // Sau khi content block fade in xong mới chạy các phần tử bên trong
                const tweenArr = [];
                if (this.cardImgFade) tweenArr.push(this.cardImgFade);
                if (this.cardContentFade) tweenArr.push(this.cardContentFade);
                if (this.cardTitleSplit) tweenArr.push(this.cardTitleSplit);
                if (this.cardDesFade) tweenArr.push(this.cardDesFade);
                if (this.cardBotFade) tweenArr.push(this.cardBotFade);
                this.cardMaster = new MasterTimeline({
                    timeline: this.cardTl,
                    triggerInit: this.card,
                    tweenArr,
                    stagger: 0.25
                });
            }
            animBg() {
                const mainContent = this.el.querySelector('.about_team_content_main');
                const bgImg = this.bgLeft?.querySelector('img');
                if (bgImg && mainContent) {
                    this.bgTl = gsap.timeline({ paused: true });
                    this.bgTl.fromTo(bgImg,
                        { opacity: 0, x: -100 },
                        { opacity: 1, x: 0, duration: 1.2, ease: 'power2.out' }
                    );

                    this.bgPlayTrigger = ScrollTrigger.create({
                        trigger: mainContent,
                        start: 'top bottom-=20%',
                        onEnter: () => this.bgTl?.play(),
                        onEnterBack: () => this.bgTl?.play()
                    });

                    this.bgResetTrigger = ScrollTrigger.create({
                        trigger: this.el,
                        start: 'top bottom',
                        end: 'bottom top',
                        onLeave: () => {
                            this.bgTl?.progress(0).pause();
                        },
                        onLeaveBack: () => {
                            this.bgTl?.progress(0).pause();
                        }
                    });
                }
            }

            destroy() {
                super.cleanTrigger();
                if (this.ctx) {
                    this.ctx.revert();
                    this.ctx = null;
                }
                if (this.bgPlayTrigger) {
                    this.bgPlayTrigger.kill();
                    this.bgPlayTrigger = null;
                }
                if (this.bgResetTrigger) {
                    this.bgResetTrigger.kill();
                    this.bgResetTrigger = null;
                }
                if (this._destroySliderListeners) {
                    this._destroySliderListeners();
                    this._destroySliderListeners = null;
                }
                if (this.textTl) {
                    this.textTl.kill();
                    this.textTl = null;
                }
                if (this.splits) {
                    this.splits.forEach(split => {
                        if (split) split.revert();
                    });
                    this.splits = [];
                }
                if (this._destroyWidthListener) {
                    this._destroyWidthListener();
                    this._destroyWidthListener = null;
                }
                [this.headerTl, this.cardTl, this.bgTl, this.bgRightTl, this.horizontalTl, this.itemsFadeTl, ...this.itemTls].forEach(tl => tl?.kill());
                [this.headerMaster, this.cardMaster, this.bgMaster, this.bgRightMaster, ...this.itemMasters].forEach(m => m?.destroy());
                [this.subFade, this.titleSplit, this.desFade, this.cardImgFade, this.cardContentFade, this.cardTitleSplit, this.cardDesFade, this.cardBotFade, this.bgLeftFade, this.bgRightFade]
                    .forEach(a => a?.destroy?.());
                this.headerTl = this.cardTl = this.bgTl = this.bgRightTl = this.horizontalTl = this.itemsFadeTl = null;
                this.headerMaster = this.cardMaster = this.bgMaster = this.bgRightMaster = null;
                this.subFade = this.titleSplit = this.desFade = this.cardImgFade = this.cardContentFade = this.cardTitleSplit = this.cardDesFade = this.cardBotFade = this.bgLeftFade = this.bgRightFade = null;
                this.itemTls = [];
                this.itemMasters = [];
            }
        }
    };
    const CareerPage = {
        ParallaxBg: class {
            constructor() {
                this.parallax = null;
                this.onScroll = this.onScroll.bind(this);
            }
            trigger() {
                this.parallax = document.getElementById("parallax");
                if (this.parallax) {
                    window.addEventListener("scroll", this.onScroll);
                }
            }
            onScroll() {
                let rect = this.parallax.getBoundingClientRect();
                if (rect.top <= 0) {
                    let offset = -rect.top;
                    this.parallax.style.backgroundPositionY = (offset * 0.5) + "px";
                } else {
                    this.parallax.style.backgroundPositionY = "0px";
                }
            }
            destroy() {
                if (this.parallax) {
                    window.removeEventListener("scroll", this.onScroll);
                    this.parallax = null;
                }
            }
        },
        Hero: class {
            constructor() {
                this.el = null;
                this.fadeTl = null;
                this.master = null;
                this.titleSplit = null;
                this.descFade = null;
                this.btnFade = null;
                this.bgFade = null;
            }
            trigger(data) {
                this.el = document.querySelector('.service_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                this.title = this.el.querySelector('.service_hero_left');
                this.desc = this.el.querySelector('.service_hero_right_txt');
                this.btn = this.el.querySelector('.service_hero_right_discover');
                this.bg = this.el.querySelector('.service_hero_bg');
                this.careerImg = document.querySelector('.career_img');

                if (this.title) {
                    this.titleSplit = new FadeSplitText({
                        el: this.title,
                        splitType: 'words',
                        isDisableRevert: true,
                        duration: 1.2,
                        stagger: 0.05,
                        ease: 'power3.out'
                    });
                }
                if (this.desc) {
                    this.descFade = new FadeIn({
                        el: this.desc,
                        type: 'bottom',
                        isDisableRevert: true,
                        duration: 1.2,
                        ease: 'power3.out'
                    });
                }
                if (this.btn) {
                    this.btnFade = new FadeIn({
                        el: this.btn,
                        type: 'bottom',
                        isDisableRevert: true,
                        duration: 1.2,
                        ease: 'power3.out'
                    });
                }
                if (this.bg) {
                    this.bgFade = new FadeIn({
                        el: this.bg,
                        type: 'none',
                        from: { scale: 0.8, opacity: 0 },
                        to: { scale: 1, opacity: 1 },
                        isDisableRevert: true,
                        duration: 2.0,
                        ease: 'power2.out'
                    });
                }
                if (this.careerImg) {
                    this.careerImgFade = new FadeIn({
                        el: this.careerImg,
                        type: 'none',
                        isDisableRevert: true,
                        duration: 2.0,
                        ease: 'power2.out'
                    });
                }
            }
            animFade() {
                this.fadeTl = gsap.timeline();
                const tweenArr = [];
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.descFade) tweenArr.push(this.descFade);
                if (this.btnFade) tweenArr.push(this.btnFade);
                if (this.bgFade) tweenArr.push(this.bgFade);
                if (this.careerImgFade) tweenArr.push(this.careerImgFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({
                        timeline: this.fadeTl,
                        triggerInit: this.el,
                        tweenArr: tweenArr,
                        stagger: 0.15
                    });
                }
            }
        },
        Why: class {
            constructor() {
                this.el = null;
                this.infoTl = null;
                this.cardTl = null;
                this.bgTl = null;

                this.infoMaster = null;
                this.cardMaster = null;

                this.leftSubFade = null;
                this.titleFade = null;
                this.contentSubFade = null;
                this.cardTweens = [];
                this.bgImgFade = null;

                this.centerImgTl = null;
                this.listTl = null;
                this.centerImgMaster = null;
                this.listMaster = null;
                this.centerImgFade = null;
                this.centerImgTxtFade = null;
                this.listTitleFade = null;
                this.listItemFade = null;
            }
            trigger(data) {
                let container = data && data.next && data.next.container ? data.next.container : document;
                this.el = container.querySelector('.career_why');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const leftSub = this.el.querySelector('.career_why_info_subtitle');
                const title = this.el.querySelector('.career_why_info_content_title');
                const contentSub = this.el.querySelector('.career_why_info_content_subtitle');
                const cards = this.el.querySelectorAll('.career_why_info_content_card_item');
                const bgImg = this.el.querySelector('.career_why_img_bg');
                const centerImg = this.el.querySelector('.career_why_img_inner');
                const centerImgTxt = this.el.querySelector('.career_why_img_txt');
                const listTitle = this.el.querySelector('.career_why_list_title');
                const listItems = this.el.querySelectorAll('.career_why_list_item');

                if (leftSub) {
                    this.leftSubFade = new FadeIn({ el: leftSub, type: 'bottom', isDisableRevert: true, duration: 1.0 });
                }
                if (title) {
                    this.titleFade = new FadeSplitText({ el: title, splitType: 'words', isDisableRevert: true, duration: 1.0, stagger: 0.05 });
                }
                if (contentSub) {
                    this.contentSubFade = new FadeSplitText({ el: contentSub, splitType: 'words', isDisableRevert: true, duration: 1.0, delay: .6 });
                }
                if (cards.length) {
                    cards.forEach((card, index) => {
                        const icon = card.querySelector('.career_why_info_content_card_item_icon');
                        const title = card.querySelector('.career_why_info_content_card_item_title');
                        const desc = card.querySelector('.career_why_info_content_card_item_des');

                        const baseTime = index * 0.15;

                        if (icon) {
                            const anim = new FadeIn({ el: icon, type: 'bottom', isDisableRevert: true, duration: 0.8 });
                            anim.delay = baseTime || 0.001; // Avoid falsy 0
                            this.cardTweens.push(anim);
                        }
                        if (title) {
                            const anim = new FadeSplitText({ el: title, splitType: 'words', isDisableRevert: true, duration: 0.8, stagger: 0.02 });
                            anim.delay = baseTime + 0.1;
                            this.cardTweens.push(anim);
                        }
                        if (desc) {
                            const anim = new FadeSplitText({ el: desc, splitType: 'words', isDisableRevert: true, duration: 0.8, stagger: 0.01 });
                            anim.delay = baseTime + 0.2;
                            this.cardTweens.push(anim);
                        }
                    });
                }
                if (bgImg) {
                    this.bgImgFade = new FadeIn({
                        el: bgImg,
                        type: 'left',
                        isDisableRevert: true,
                        duration: 1.2,
                        clearProps: 'none' // To allow toggle reversing without CSS jumping
                    });
                }
                if (centerImg) {
                    this.centerImgFade = new FadeIn({ el: centerImg, type: 'bottom', isDisableRevert: true, duration: 1.0 });
                }
                if (centerImgTxt) {
                    this.centerImgTxtFade = new FadeSplitText({ el: centerImgTxt, splitType: 'words', isDisableRevert: true, duration: 1.0, stagger: 0.05 });
                }
                if (listTitle) {
                    this.listTitleFade = new FadeIn({ el: listTitle, type: 'bottom', isDisableRevert: true, duration: 1.0 });
                }
                if (listItems.length) {
                    this.listItemFade = new FadeIn({ el: listItems, type: 'bottom', isDisableRevert: true, duration: 1.0, stagger: 0.1 });
                }
            }
            animFade() {
                // 1. Info Timeline
                this.infoTl = gsap.timeline({
                    scrollTrigger: { trigger: this.el, start: 'top top+=75%', once: true }
                });
                const infoArr = [];
                if (this.leftSubFade) infoArr.push(this.leftSubFade);
                if (this.titleFade) infoArr.push(this.titleFade);
                if (this.contentSubFade) infoArr.push(this.contentSubFade);

                if (infoArr.length) {
                    this.infoMaster = new MasterTimeline({
                        timeline: this.infoTl,
                        triggerInit: this.el,
                        tweenArr: infoArr,
                        stagger: 0.15
                    });
                }

                // 2. Card Timeline
                const cardContainer = this.el.querySelector('.career_why_info_content_card');
                if (cardContainer && this.cardTweens.length) {
                    this.cardTl = gsap.timeline({
                        scrollTrigger: { trigger: cardContainer, start: 'top top+=75%', once: true }
                    });
                    this.cardMaster = new MasterTimeline({
                        timeline: this.cardTl,
                        triggerInit: cardContainer,
                        tweenArr: this.cardTweens,
                        stagger: 0
                    });
                }

                // 3. BG Image Toggle Timeline
                const bgImg = this.el.querySelector('.career_why_img_bg');
                if (viewport.w > 767 && bgImg && this.bgImgFade) {
                    this.bgImgFade.init();
                    this.bgTl = gsap.timeline({
                        scrollTrigger: {
                            trigger: bgImg,
                            start: 'top top+=80%',
                            end: 'bottom top+=20%',
                            toggleActions: 'play reverse play reverse'
                        }
                    });
                    this.bgTl.add(this.bgImgFade.animation, 0);
                }

                // 4. Center Image Timeline
                const centerImgContainer = this.el.querySelector('.career_why_img');
                if (centerImgContainer) {
                    this.centerImgTl = gsap.timeline({
                        scrollTrigger: { trigger: centerImgContainer, start: 'top top+=75%', once: true }
                    });
                    const centerImgArr = [];
                    if (this.centerImgFade) centerImgArr.push(this.centerImgFade);
                    if (this.centerImgTxtFade) centerImgArr.push(this.centerImgTxtFade);

                    if (centerImgArr.length) {
                        this.centerImgMaster = new MasterTimeline({
                            timeline: this.centerImgTl,
                            triggerInit: centerImgContainer,
                            tweenArr: centerImgArr,
                            stagger: 0.15
                        });
                    }
                }

                // 5. List Timeline
                const listContainer = this.el.querySelector('.career_why_list');
                if (listContainer) {
                    this.listTl = gsap.timeline({
                        scrollTrigger: { trigger: listContainer, start: 'top top+=75%', once: true }
                    });
                    const listArr = [];
                    if (this.listTitleFade) listArr.push(this.listTitleFade);
                    if (this.listItemFade) listArr.push(this.listItemFade);

                    if (listArr.length) {
                        this.listMaster = new MasterTimeline({
                            timeline: this.listTl,
                            triggerInit: listContainer,
                            tweenArr: listArr,
                            stagger: 0.15
                        });
                    }
                }
            }
            destroy() {
                if (this.infoTl) this.infoTl.kill();
                if (this.cardTl) this.cardTl.kill();
                if (this.bgTl) this.bgTl.kill();
                if (this.centerImgTl) this.centerImgTl.kill();
                if (this.listTl) this.listTl.kill();

                if (this.infoMaster) this.infoMaster.destroy();
                if (this.cardMaster) this.cardMaster.destroy();
                if (this.centerImgMaster) this.centerImgMaster.destroy();
                if (this.listMaster) this.listMaster.destroy();

                [this.leftSubFade, this.titleFade, this.contentSubFade, ...this.cardTweens, this.bgImgFade, this.centerImgFade, this.centerImgTxtFade, this.listTitleFade, this.listItemFade].forEach(a => a?.destroy?.());
            }
        },
        Popup: class {
            constructor() {
                this.overlay = null;
                this.closeBtn = null;
                this.openBtn = null;
                this.fileInput = null;
                this.formEl = null;
                this.handleOpen = this.handleOpen.bind(this);
                this.handleClose = this.handleClose.bind(this);
                this.handleOutsideClick = this.handleOutsideClick.bind(this);
                this.handleFileChange = this.handleFileChange.bind(this);
                this.handleSubmit = this.handleSubmit.bind(this);
            }
            trigger(data) {
                this.overlay = document.getElementById('careerPopupOverlay');
                this.closeBtn = document.getElementById('careerPopupClose');
                this.openBtn = document.querySelector('.about_cta_inner_content_title_button');
                this.fileInput = document.querySelector('input[name="upload_cv"]');
                this.formEl = document.querySelector('.career_popup_form');

                if (this.openBtn && this.overlay && this.closeBtn) {
                    this.openBtn.addEventListener('click', this.handleOpen);
                    this.closeBtn.addEventListener('click', this.handleClose);
                    this.overlay.addEventListener('click', this.handleOutsideClick);
                }

                if (this.fileInput) {
                    this.fileInput.addEventListener('change', this.handleFileChange);
                }

                if (this.formEl) {
                    this.formEl.addEventListener('submit', this.handleSubmit);
                }
            }
            handleOpen(e) {
                e.preventDefault();
                this.overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            handleClose(e) {
                e.preventDefault();
                this.overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            handleOutsideClick(e) {
                if (e.target === this.overlay) {
                    this.overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
            handleFileChange(e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    const uploadNote = document.querySelector('.career_popup_upload_note');
                    if (uploadNote) {
                        uploadNote.textContent = fileName;
                    }
                }
            }
            handleSubmit(e) {
                e.preventDefault();
                let isValid = true;
                const inputs = this.formEl.querySelectorAll('input[required], select[required]');
                inputs.forEach(input => {
                    if (input.type === 'file') {
                        if (input.files.length === 0) isValid = false;
                    } else if (!input.value.trim()) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc.');
                    return;
                }

                const submitBtnInit = this.formEl.querySelector('.career_popup_submit_btn .init');
                const submitBtnActive = this.formEl.querySelector('.career_popup_submit_btn .active');
                const originalText = submitBtnInit ? submitBtnInit.innerText : '';

                const updateBtnText = (text) => {
                    if (submitBtnInit) submitBtnInit.innerText = text;
                    if (submitBtnActive) submitBtnActive.innerText = text;
                };

                updateBtnText("SENDING...");

                const submitForm = (token = '') => {
                    const formData = new FormData(this.formEl);
                    formData.append('action', 'submit_career_popup_application');
                    if (token) {
                        formData.append('g-recaptcha-response', token);
                    }

                    const ajaxUrl = (typeof caseStudyAjax !== 'undefined' && caseStudyAjax.ajaxurl) ? caseStudyAjax.ajaxurl : '/wp-admin/admin-ajax.php';

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 1) {
                                updateBtnText("SENT SUCCESSFULLY");
                                this.formEl.style.display = 'none';
                                const successMsg = document.createElement('div');
                                successMsg.className = 'form_success_message';
                                successMsg.style.textAlign = 'center';
                                successMsg.innerHTML = '<h4 style="color:#F32B3B; margin-bottom:15px; font-size: 2rem;">Ứng tuyển thành công!</h4><p style="font-size: 1.6rem;">Cảm ơn bạn đã gửi hồ sơ. Chúng tôi đã nhận được thông tin và sẽ sớm liên hệ lại với bạn.</p>';
                                this.formEl.parentNode.insertBefore(successMsg, this.formEl);
                                this.formEl.reset();
                            } else {
                                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                                updateBtnText("SEND FAILED");
                                setTimeout(() => { updateBtnText(originalText); }, 3000);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            updateBtnText("ERROR");
                            setTimeout(() => { updateBtnText(originalText); }, 3000);
                        });
                };

                const siteKey = typeof caseStudyAjax !== 'undefined' ? caseStudyAjax.recaptchaSiteKey : '';
                if (typeof grecaptcha !== 'undefined' && siteKey) {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(siteKey, { action: 'submit_career_popup_application' })
                            .then(token => {
                                submitForm(token);
                            })
                            .catch(err => {
                                console.error('reCAPTCHA error:', err);
                                submitForm();
                            });
                    });
                } else {
                    submitForm();
                }
            }
            destroy() {
                if (this.openBtn && this.overlay && this.closeBtn) {
                    this.openBtn.removeEventListener('click', this.handleOpen);
                    this.closeBtn.removeEventListener('click', this.handleClose);
                    this.overlay.removeEventListener('click', this.handleOutsideClick);
                }
                if (this.fileInput) {
                    this.fileInput.removeEventListener('change', this.handleFileChange);
                }
                if (this.formEl) {
                    this.formEl.removeEventListener('submit', this.handleSubmit);
                }
            }
        }
    };
    const CareerDetailPage = {
        Animation: class {
            constructor() {
                this.el = null;
                this.titleFade = null;
                this.jobBlockFade = null;
                this.jobItemFade = null;
                this.descItemFade = null;
                this.formFade = null;
                this.otherFade = null;
                this.bgFade = null;
            }
            trigger() {
                this.el = document.querySelector('.careerdetail_info');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const title = this.el.querySelector('.careerdetail_info_content_title');
                const jobBlock = this.el.querySelector('.careerdetail_info_content_job');
                const jobItems = this.el.querySelectorAll('.careerdetail_info_content_job_item');
                const descItems = this.el.querySelectorAll('.careerdetail_info_content_des_item');
                const formBlock = this.el.querySelector('.careerdetail_info_content_form');
                const otherSidebar = this.el.querySelector('.careerdetail_info_other');
                const bgSVG = this.el.querySelector('.careerdetail_info_bg');

                if (title) this.titleFade = new FadeSplitText({ el: title, splitType: 'words', isDisableRevert: true, duration: 1.0 });
                if (jobBlock) this.jobBlockFade = new FadeIn({ el: jobBlock, type: 'bottom', isDisableRevert: true, duration: 0.8 });
                if (jobItems.length) this.jobItemFade = new FadeIn({ el: jobItems, type: 'bottom', isDisableRevert: true, duration: 0.8, stagger: 0.1 });
                if (descItems.length) this.descItemFade = new FadeIn({ el: descItems, type: 'bottom', isDisableRevert: true, duration: 1.0, stagger: 0.1 });
                if (formBlock) this.formFade = new FadeIn({ el: formBlock, type: 'bottom', isDisableRevert: true, duration: 1.0 });
                if (otherSidebar) this.otherFade = new FadeIn({ el: otherSidebar, type: 'bottom', isDisableRevert: true, duration: 1.0, delay: 0.2 });
                if (bgSVG) this.bgFade = new FadeIn({ el: bgSVG, type: 'left', isDisableRevert: true, duration: 1.2 });
            }
            animFade() {
                this.tl = gsap.timeline({ scrollTrigger: { trigger: this.el, start: 'top top+=85%', once: true } });
                if (this.bgFade) this.tl.add(this.bgFade.play(), 0);
                if (this.titleFade) this.tl.add(this.titleFade.play(), 0.1);
                if (this.jobBlockFade) this.tl.add(this.jobBlockFade.play(), 0.2);
                if (this.jobItemFade) this.tl.add(this.jobItemFade.play(), 0.3);
                if (this.descItemFade) this.tl.add(this.descItemFade.play(), 0.5);
                if (this.otherFade) this.tl.add(this.otherFade.play(), 0.3);

                const formBlock = this.el.querySelector('.careerdetail_info_content_form');
                if (formBlock && this.formFade) {
                    this.formTl = gsap.timeline({ scrollTrigger: { trigger: formBlock, start: 'top top+=85%', once: true } });
                    this.formTl.add(this.formFade.play(), 0);
                }
            }
            destroy() {
                if (this.tl) this.tl.kill();
                if (this.formTl) this.formTl.kill();
                [this.titleFade, this.jobBlockFade, this.jobItemFade, this.descItemFade, this.formFade, this.otherFade, this.bgFade].forEach(a => a?.destroy?.());
            }
        },
        SocialShare: class {
            constructor() {
                this.fbBtns = null;
                this.twBtns = null;
                this.inBtns = null;

                this.handleShareFB = this.handleShareFB.bind(this);
                this.handleShareTW = this.handleShareTW.bind(this);
                this.handleShareIN = this.handleShareIN.bind(this);
            }

            trigger() {
                this.fbBtns = document.querySelectorAll('.btn-share-fb');
                this.twBtns = document.querySelectorAll('.btn-share-tw');
                this.inBtns = document.querySelectorAll('.btn-share-in');

                if (this.fbBtns) this.fbBtns.forEach(btn => btn.addEventListener('click', this.handleShareFB));
                if (this.twBtns) this.twBtns.forEach(btn => btn.addEventListener('click', this.handleShareTW));
                if (this.inBtns) this.inBtns.forEach(btn => btn.addEventListener('click', this.handleShareIN));
            }

            handleShareFB(e) {
                e.preventDefault();
                const url = e.currentTarget.dataset.url;
                if (url) window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
            }

            handleShareTW(e) {
                e.preventDefault();
                const url = e.currentTarget.dataset.url;
                const title = e.currentTarget.dataset.title;
                if (url) window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
            }

            handleShareIN(e) {
                e.preventDefault();
                const url = e.currentTarget.dataset.url;
                const title = e.currentTarget.dataset.title;
                if (url) window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`, '_blank', 'width=600,height=400');
            }

            destroy() {
                if (this.fbBtns) this.fbBtns.forEach(btn => btn.removeEventListener('click', this.handleShareFB));
                if (this.twBtns) this.twBtns.forEach(btn => btn.removeEventListener('click', this.handleShareTW));
                if (this.inBtns) this.inBtns.forEach(btn => btn.removeEventListener('click', this.handleShareIN));
                this.fbBtns = null;
                this.twBtns = null;
                this.inBtns = null;
            }
        },
        FormValidator: class {
            constructor() {
                this.formEl = null;
            }
            trigger() {
                this.formEl = document.querySelector('.careerdetail_form');
                if (!this.formEl) return;

                this.inputs = this.formEl.querySelectorAll('input[required]');

                this.handleInput = (e) => {
                    const parent = e.target.closest('.careerdetail_form_col') || e.target.closest('.careerdetail_form_row');
                    if (parent) parent.classList.remove('has-error');
                };

                this.inputs.forEach(input => {
                    input.addEventListener('input', this.handleInput);
                    input.addEventListener('change', this.handleInput);
                });

                const fileInput = this.formEl.querySelector('input[type="file"][name="career_cv"]');
                if (fileInput) {
                    const fileText = this.formEl.querySelector('.careerdetail_form_upload_txt');
                    if (fileText) {
                        const originalFileText = fileText.innerText;
                        fileInput.addEventListener('change', (e) => {
                            if (e.target.files.length > 0) {
                                fileText.innerText = e.target.files[0].name;
                                fileText.style.color = '#F32B3B'; // Optional: highlight selected file name
                            } else {
                                fileText.innerText = originalFileText;
                                fileText.style.color = '';
                            }
                        });
                        // Reset text when form resets
                        this.formEl.addEventListener('reset', () => {
                            fileText.innerText = originalFileText;
                            fileText.style.color = '';
                        });
                    }
                }

                this.handleSubmit = (e) => {
                    e.preventDefault();
                    let isValid = true;

                    this.inputs.forEach(input => {
                        const parent = input.closest('.careerdetail_form_col') || input.closest('.careerdetail_form_row');
                        const type = input.getAttribute('type');
                        let isInputValid = true;

                        if (type === 'file') {
                            if (input.files.length === 0) isInputValid = false;
                        } else {
                            const value = input.value.trim();
                            if (value === '') {
                                isInputValid = false;
                            } else if (type === 'email') {
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(value)) isInputValid = false;
                            }
                        }

                        if (!isInputValid) {
                            parent.classList.add('has-error');
                            isValid = false;
                        } else {
                            parent.classList.remove('has-error');
                        }
                    });

                    if (isValid) {
                        const submitBtnInit = this.formEl.querySelector('.btn_submit .init');
                        const submitBtnActive = this.formEl.querySelector('.btn_submit .active');
                        if (submitBtnInit) {
                            const originalText = submitBtnInit.innerText;

                            const updateBtnText = (text) => {
                                submitBtnInit.innerText = text;
                                if (submitBtnActive) submitBtnActive.innerText = text;
                            };

                            updateBtnText("SENDING...");

                            const submitForm = (token = '') => {
                                const formData = new FormData(this.formEl);
                                formData.append('action', 'submit_career_application');
                                if (token) {
                                    formData.append('g-recaptcha-response', token);
                                }

                                const ajaxUrl = (typeof caseStudyAjax !== 'undefined' && caseStudyAjax.ajaxurl) ? caseStudyAjax.ajaxurl : '/wp-admin/admin-ajax.php';

                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 1) {
                                            updateBtnText("SENT SUCCESSFULLY");
                                            this.formEl.style.display = 'none';
                                            const successMsg = document.createElement('div');
                                            successMsg.className = 'form_success_message';
                                            successMsg.innerHTML = '<h4 style="color:#F32B3B; margin-bottom:15px;">Ứng tuyển thành công!</h4><p>Cảm ơn bạn đã gửi hồ sơ. Chúng tôi đã gửi một email xác nhận đến địa chỉ hòm thư của bạn. Bộ phận Tuyển dụng sẽ sớm liên hệ lại nếu hồ sơ phù hợp.</p>';
                                            this.formEl.parentNode.insertBefore(successMsg, this.formEl);
                                            this.formEl.reset();
                                        } else {
                                            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                                            updateBtnText("SEND FAILED");
                                            setTimeout(() => { updateBtnText(originalText); }, 3000);
                                        }
                                    })
                                    .catch(err => {
                                        updateBtnText("ERROR");
                                        setTimeout(() => { updateBtnText(originalText); }, 3000);
                                    });
                            };

                            const siteKey = typeof caseStudyAjax !== 'undefined' ? caseStudyAjax.recaptchaSiteKey : '';
                            if (typeof grecaptcha !== 'undefined' && siteKey) {
                                grecaptcha.ready(() => {
                                    grecaptcha.execute(siteKey, { action: 'submit_career_application' })
                                        .then(token => {
                                            submitForm(token);
                                        })
                                        .catch(err => {
                                            console.error('reCAPTCHA error:', err);
                                            submitForm();
                                        });
                                });
                            } else {
                                submitForm();
                            }
                        }
                    }
                };
                this.formEl.addEventListener('submit', this.handleSubmit);
            }
            destroy() {
                if (this.formEl) {
                    this.formEl.removeEventListener('submit', this.handleSubmit);
                    if (this.inputs) {
                        this.inputs.forEach(input => {
                            input.removeEventListener('input', this.handleInput);
                            input.removeEventListener('change', this.handleInput);
                        });
                    }
                }
            }
        },
        Popup: class {
            constructor() {
                this.overlay = null;
                this.closeBtn = null;
                this.openBtn = null;
                this.fileInput = null;
                this.formEl = null;
                this.handleOpen = this.handleOpen.bind(this);
                this.handleClose = this.handleClose.bind(this);
                this.handleOutsideClick = this.handleOutsideClick.bind(this);
                this.handleFileChange = this.handleFileChange.bind(this);
                this.handleSubmit = this.handleSubmit.bind(this);
            }
            trigger(data) {
                this.overlay = document.getElementById('careerPopupOverlay');
                this.closeBtn = document.getElementById('careerPopupClose');
                this.openBtn = document.querySelector('.about_cta_inner_content_title_button');
                this.fileInput = document.querySelector('input[name="upload_cv"]');
                this.formEl = document.querySelector('.career_popup_form');

                if (this.openBtn && this.overlay && this.closeBtn) {
                    this.openBtn.addEventListener('click', this.handleOpen);
                    this.closeBtn.addEventListener('click', this.handleClose);
                    this.overlay.addEventListener('click', this.handleOutsideClick);
                }

                if (this.fileInput) {
                    this.fileInput.addEventListener('change', this.handleFileChange);
                }

                if (this.formEl) {
                    this.formEl.addEventListener('submit', this.handleSubmit);
                }
            }
            handleOpen(e) {
                e.preventDefault();
                this.overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            handleClose(e) {
                e.preventDefault();
                this.overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            handleOutsideClick(e) {
                if (e.target === this.overlay) {
                    this.overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
            handleFileChange(e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    const uploadNote = document.querySelector('.career_popup_upload_note');
                    if (uploadNote) {
                        uploadNote.textContent = fileName;
                    }
                }
            }
            handleSubmit(e) {
                e.preventDefault();
                let isValid = true;
                const inputs = this.formEl.querySelectorAll('input[required], select[required]');
                inputs.forEach(input => {
                    if (input.type === 'file') {
                        if (input.files.length === 0) isValid = false;
                    } else if (!input.value.trim()) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc.');
                    return;
                }

                const submitBtnInit = this.formEl.querySelector('.career_popup_submit_btn .init');
                const submitBtnActive = this.formEl.querySelector('.career_popup_submit_btn .active');
                const originalText = submitBtnInit ? submitBtnInit.innerText : '';

                const updateBtnText = (text) => {
                    if (submitBtnInit) submitBtnInit.innerText = text;
                    if (submitBtnActive) submitBtnActive.innerText = text;
                };

                updateBtnText("SENDING...");

                const submitForm = (token = '') => {
                    const formData = new FormData(this.formEl);
                    formData.append('action', 'submit_career_popup_application');
                    if (token) {
                        formData.append('g-recaptcha-response', token);
                    }

                    const ajaxUrl = (typeof caseStudyAjax !== 'undefined' && caseStudyAjax.ajaxurl) ? caseStudyAjax.ajaxurl : '/wp-admin/admin-ajax.php';

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 1) {
                                updateBtnText("SENT SUCCESSFULLY");
                                this.formEl.style.display = 'none';
                                const successMsg = document.createElement('div');
                                successMsg.className = 'form_success_message';
                                successMsg.style.textAlign = 'center';
                                successMsg.innerHTML = '<h4 style="color:#F32B3B; margin-bottom:15px; font-size: 2rem;">Ứng tuyển thành công!</h4><p style="font-size: 1.6rem;">Cảm ơn bạn đã gửi hồ sơ. Chúng tôi đã nhận được thông tin và sẽ sớm liên hệ lại với bạn.</p>';
                                this.formEl.parentNode.insertBefore(successMsg, this.formEl);
                                this.formEl.reset();
                            } else {
                                alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                                updateBtnText("SEND FAILED");
                                setTimeout(() => { updateBtnText(originalText); }, 3000);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            updateBtnText("ERROR");
                            setTimeout(() => { updateBtnText(originalText); }, 3000);
                        });
                };

                const siteKey = typeof caseStudyAjax !== 'undefined' ? caseStudyAjax.recaptchaSiteKey : '';
                if (typeof grecaptcha !== 'undefined' && siteKey) {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(siteKey, { action: 'submit_career_popup_application' })
                            .then(token => {
                                submitForm(token);
                            })
                            .catch(err => {
                                console.error('reCAPTCHA error:', err);
                                submitForm();
                            });
                    });
                } else {
                    submitForm();
                }
            }
            destroy() {
                if (this.openBtn && this.overlay && this.closeBtn) {
                    this.openBtn.removeEventListener('click', this.handleOpen);
                    this.closeBtn.removeEventListener('click', this.handleClose);
                    this.overlay.removeEventListener('click', this.handleOutsideClick);
                }
                if (this.fileInput) {
                    this.fileInput.removeEventListener('change', this.handleFileChange);
                }
                if (this.formEl) {
                    this.formEl.removeEventListener('submit', this.handleSubmit);
                }
            }
        }
    };
    const CaseStudyPage = {
        Hero: class {
            constructor() {
                this.el = null;
                this.titleSplit = null;
                this.descFade = null;
                this.bgFade = null;
                this.master = null;
            }
            trigger() {
                this.el = document.querySelector('.casestudy_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const title = this.el.querySelector('.casestudy_hero_title');
                const desc = this.el.querySelector('.casestudy_hero_des');
                const bg = this.el.querySelector('.casestudy_hero_bg');

                if (title) this.titleSplit = new FadeSplitText({ el: title });
                if (desc) this.descFade = new FadeIn({ el: desc, type: 'bottom', isDisableRevert: true });
                if (bg) this.bgFade = new FadeIn({ el: bg, type: 'none', isDisableRevert: true });
            }
            animFade() {
                const tweenArr = [];
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.descFade) tweenArr.push(this.descFade);
                if (this.bgFade) tweenArr.push(this.bgFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({ triggerInit: this.el, tweenArr, stagger: 0.1 });
                }
            }
            destroy() {
                if (this.master) { this.master.destroy(); this.master = null; }
                [this.titleSplit, this.descFade, this.bgFade].forEach(a => a?.destroy?.());
            }
        },
        Items: class {
            constructor() {
                this.master = null;
                this.masterList = [];
            }
            trigger() {
                const isMobile = getScreenType().isMobile;
                if (isMobile) {
                    const items = document.querySelectorAll('.home_case_content_list .home_case_content_item');
                    if (!items || items.length === 0) return;

                    items.forEach(item => {
                        const tweenArr = [
                            new FadeIn({ el: item, type: 'bottom', isDisableRevert: true })
                        ];
                        this.masterList.push(new MasterTimeline({
                            triggerInit: item,
                            scrollTrigger: {
                                start: "top bottom-=10%",
                                once: true
                            },
                            tweenArr: tweenArr
                        }));
                    });
                } else {
                    const list = document.querySelector('.home_case_content_list');
                    if (!list) return;

                    const tweenArr = [
                        new FadeIn({ el: list, type: 'bottom', isDisableRevert: true, duration: 1.2 })
                    ];

                    this.master = new MasterTimeline({
                        triggerInit: list,
                        scrollTrigger: {
                            start: "top bottom-=10%",
                            once: true
                        },
                        tweenArr: tweenArr
                    });
                }
            }
            destroy() {
                if (this.master) {
                    this.master.destroy();
                    this.master = null;
                }
                this.masterList.forEach(m => m.destroy());
                this.masterList = [];
            }
        },
        Content: class {
            constructor() {
                this.el = null;
                this.btn = null;
                this.clickAjax = null;
            }
            trigger(data) {
                this.el = document.querySelector('.casestudy_content');
                if (!this.el) return;
                this.interact();

                // Fix initial scroll bug when images load asynchronously
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    if (typeof smoothScroll !== 'undefined' && smoothScroll.lenis) smoothScroll.lenis.resize();
                    if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
                }, 500);
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    if (typeof smoothScroll !== 'undefined' && smoothScroll.lenis) smoothScroll.lenis.resize();
                    if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
                }, 1500);
            }
            interact() {
                this.btn = this.el.querySelector('#load-more-case-study');
                if (!this.btn) return;

                this.clickAjax = (e) => {
                    e.preventDefault();

                    const button = jQuery(this.btn);
                    let page = parseInt(button.attr('data-page'));
                    let maxPages = parseInt(button.attr('data-max'));

                    if (page >= maxPages) return;

                    if (!button.attr('data-original-text')) {
                        button.attr('data-original-text', button.find('.init').text());
                    }
                    let originalText = button.attr('data-original-text');

                    button.find('.init').text('LOADING...');
                    button.find('.active').text('LOADING...');

                    jQuery.ajax({
                        url: caseStudyAjax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'load_more_case_studies',
                            paged: page + 1
                        },
                        success: (response) => {
                            if (response.success) {
                                jQuery('.home_case_content_list').append(response.data.html);

                                button.attr('data-page', page + 1);

                                let newPage = page + 1;
                                if (newPage >= maxPages) {
                                    button.hide();
                                } else {
                                    button.find('.init').text(originalText);
                                    button.find('.active').text(originalText);
                                }
                                setTimeout(() => {
                                    if (typeof smoothScroll !== 'undefined' && smoothScroll.lenis) smoothScroll.lenis.resize();
                                    if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
                                }, 200);
                            } else {
                                button.find('.init').text(originalText);
                                button.find('.active').text(originalText);
                            }
                        },
                        error: () => {
                            button.find('.init').text('Error! Try again');
                            button.find('.active').text('Error! Try again');
                        }
                    });
                };

                this.btn.addEventListener('click', this.clickAjax);
            }
            destroy() {
                if (this.btn && this.clickAjax) {
                    this.btn.removeEventListener('click', this.clickAjax);
                    this.clickAjax = null;
                    this.btn = null;
                }
                this.el = null;
            }
        }
    };

    const CaseStudyDetailPage = {};

    CaseStudyDetailPage.Animations = class {
        constructor() {
            this.heroMaster = null;
            this.infoMasterList = [];
            this.blogMasterList = [];
            this.socialMaster = null;
            this.relatedMasterList = [];
        }

        trigger() {
            const hero = document.querySelector('.casestudydetail_hero');
            if (hero) {
                const tweenArr = [];
                const heroImg = hero.querySelector('.casestudydetail_hero_content_img');
                if (heroImg) tweenArr.push(new FadeIn({ el: heroImg, type: 'bottom', isDisableRevert: true }));
                const heroTxt = hero.querySelector('.casestudydetail_hero_content_txt');
                if (heroTxt) tweenArr.push(new FadeSplitText({ el: heroTxt }));

                if (tweenArr.length > 0) {
                    this.heroMaster = new MasterTimeline({ triggerInit: hero, tweenArr });
                }
            }

            document.querySelectorAll('.casestudydetail_content_info_item').forEach((item) => {
                this.infoMasterList.push(new MasterTimeline({
                    triggerInit: item,
                    tweenArr: [new FadeIn({ el: item, type: 'bottom', isDisableRevert: true })]
                }));
            });

            document.querySelectorAll('.casestudydetail_content_blog_item').forEach((item) => {
                this.blogMasterList.push(new MasterTimeline({
                    triggerInit: item,
                    tweenArr: [new FadeIn({ el: item, type: 'bottom', isDisableRevert: true })]
                }));
            });

            const socialContainer = document.querySelector('.casestudydetail_content_social');
            if (socialContainer) {
                const links = Array.from(socialContainer.querySelectorAll('a'));
                if (links.length > 0) {
                    this.socialMaster = new MasterTimeline({
                        triggerInit: socialContainer,
                        tweenArr: links.map((a, i) => new FadeIn({ el: a, type: 'left', delay: i * 0.1, isDisableRevert: true }))
                    });
                }
            }

            document.querySelectorAll('.home_case_content_list .home_case_content_item').forEach((item) => {
                const title = item.querySelector('.home_case_content_item_txt_title');
                const des = item.querySelector('.home_case_content_item_des_txt');
                const tweenArr = [];
                if (title) tweenArr.push(new FadeSplitText({ el: title }));
                if (des) tweenArr.push(new FadeSplitText({ el: des }));

                if (tweenArr.length > 0) {
                    this.relatedMasterList.push(new MasterTimeline({
                        triggerInit: item,
                        tweenArr: tweenArr
                    }));
                }
            });
        }

        destroy() {
            this.heroMaster?.destroy();
            this.infoMasterList.forEach(m => m.destroy());
            this.blogMasterList.forEach(m => m.destroy());
            this.socialMaster?.destroy();
            this.relatedMasterList.forEach(m => m.destroy());
        }
    };

    CaseStudyDetailPage.SocialShare = class {
        constructor() {
            this.copyBtns = null;
            this.fbBtns = null;
            this.inBtns = null;

            this.handleCopy = this.handleCopy.bind(this);
            this.handleShareFB = this.handleShareFB.bind(this);
            this.handleShareIN = this.handleShareIN.bind(this);
        }

        trigger() {
            this.copyBtns = document.querySelectorAll('.btn-copy-link');
            this.fbBtns = document.querySelectorAll('.btn-share-fb');
            this.inBtns = document.querySelectorAll('.btn-share-in');

            if (this.copyBtns) this.copyBtns.forEach(btn => btn.addEventListener('click', this.handleCopy));
            if (this.fbBtns) this.fbBtns.forEach(btn => btn.addEventListener('click', this.handleShareFB));
            if (this.inBtns) this.inBtns.forEach(btn => btn.addEventListener('click', this.handleShareIN));
        }

        handleCopy(e) {
            e.preventDefault();
            const url = e.currentTarget.dataset.url;
            if (url) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Đã sao chép liên kết!');
                }).catch(err => {
                    console.error('Copy failed', err);
                });
            }
        }

        handleShareFB(e) {
            e.preventDefault();
            const url = e.currentTarget.dataset.url;
            if (url) window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        handleShareIN(e) {
            e.preventDefault();
            const url = e.currentTarget.dataset.url;
            const title = e.currentTarget.dataset.title;
            if (url) window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`, '_blank', 'width=600,height=400');
        }

        destroy() {
            if (this.copyBtns) this.copyBtns.forEach(btn => btn.removeEventListener('click', this.handleCopy));
            if (this.fbBtns) this.fbBtns.forEach(btn => btn.removeEventListener('click', this.handleShareFB));
            if (this.inBtns) this.inBtns.forEach(btn => btn.removeEventListener('click', this.handleShareIN));
            this.copyBtns = null;
            this.fbBtns = null;
            this.inBtns = null;
        }
    };

    const ContactPage = {
        ParallaxBg: class {
            constructor() {
                this.parallax = null;
                this.onScroll = this.onScroll.bind(this);
            }
            trigger() {
                this.parallax = document.getElementById("parallax");
                if (this.parallax) {
                    window.addEventListener("scroll", this.onScroll);
                }
            }
            onScroll() {
                let rect = this.parallax.getBoundingClientRect();
                if (rect.top <= 0) {
                    let offset = -rect.top;
                    this.parallax.style.backgroundPositionY = (offset * 0.5) + "px";
                } else {
                    this.parallax.style.backgroundPositionY = "0px";
                }
            }
            destroy() {
                if (this.parallax) {
                    window.removeEventListener("scroll", this.onScroll);
                    this.parallax = null;
                }
            }
        },
        Hero: class {
            constructor() {
                this.el = null;
                this.bgFade = null;
                this.vectoFade = null;
                this.titleSplit = null;
                this.subFade = null;
                this.infoFade = null;
                this.formFade = null;
                this.master = null;
            }
            trigger() {
                this.el = document.querySelector('.contact_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const bg = this.el.querySelector('.contact_hero_bg');
                const vecto = this.el.querySelector('.contact_hero_vecto');
                const title = this.el.querySelector('.contact_hero_content_title');
                const sub = this.el.querySelector('.contact_hero_content_subtitle');
                const info = this.el.querySelector('.contact_hero_content_info');
                const form = this.el.querySelector('.contact_hero_form');

                if (bg) this.bgFade = new FadeIn({ el: bg, type: 'none', isDisableRevert: true, duration: 1.5 });
                if (vecto) this.vectoFade = new FadeIn({ el: vecto, type: 'right', isDisableRevert: true, duration: 1.5 });
                if (title) this.titleSplit = new FadeSplitText({ el: title, splitType: 'words', isDisableRevert: true, duration: 1.2, stagger: 0.05 });
                if (sub) this.subFade = new FadeIn({ el: sub, type: 'bottom', isDisableRevert: true });
                if (info) this.infoFade = new FadeIn({ el: info, type: 'bottom', isDisableRevert: true });
                if (form) this.formFade = new FadeIn({ el: form, type: 'bottom', isDisableRevert: true });

                // Form Validation Logic
                this.formEl = this.el.querySelector('.contact_form');
                if (this.formEl) {
                    this.inputs = this.formEl.querySelectorAll('input[required]');

                    this.handleInput = (e) => {
                        const parent = e.target.closest('.contact_form_col') || e.target.closest('.contact_form_row');
                        if (parent) parent.classList.remove('has-error');
                    };

                    this.inputs.forEach(input => {
                        input.addEventListener('input', this.handleInput);
                    });

                    this.handleSubmit = (e) => {
                        e.preventDefault();
                        let isValid = true;

                        this.inputs.forEach(input => {
                            const parent = input.closest('.contact_form_col') || input.closest('.contact_form_row');
                            const type = input.getAttribute('type');
                            const value = input.value.trim();
                            let isInputValid = true;

                            if (value === '') {
                                isInputValid = false;
                            } else if (type === 'email') {
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(value)) isInputValid = false;
                            }

                            if (!isInputValid) {
                                parent.classList.add('has-error');
                                isValid = false;
                            } else {
                                parent.classList.remove('has-error');
                            }
                        });

                        if (isValid) {
                            const submitBtnInit = this.formEl.querySelector('.btn_submit .init');
                            let originalText = "SUBMIT";
                            if (submitBtnInit) {
                                originalText = submitBtnInit.innerText;
                                submitBtnInit.innerText = "SENDING...";
                            }

                            const submitForm = (token = '') => {
                                const formData = new FormData(this.formEl);
                                formData.append('action', 'submit_contact_form');
                                if (token) {
                                    formData.append('g-recaptcha-response', token);
                                }
                                const ajaxUrl = typeof ajax_obj !== 'undefined' ? ajax_obj.ajax_url : '/wp-admin/admin-ajax.php';

                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 1) {
                                            if (submitBtnInit) submitBtnInit.innerText = "SENT SUCCESSFULLY";

                                            this.formEl.style.display = 'none';
                                            const successMsg = document.createElement('div');
                                            successMsg.className = 'form_success_message';
                                            successMsg.innerHTML = '<h4 style="color:#F32B3B; margin-bottom:15px;">Gửi thông tin thành công!</h4><p>Cảm ơn bạn đã liên hệ. Chúng tôi đã gửi một email xác nhận đến địa chỉ hòm thư của bạn. Đội ngũ tư vấn sẽ sớm liên hệ lại với bạn.</p>';
                                            this.formEl.parentNode.insertBefore(successMsg, this.formEl);
                                            this.formEl.reset();
                                        } else {
                                            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại sau.');
                                            if (submitBtnInit) submitBtnInit.innerText = "SEND FAILED";
                                            setTimeout(() => { if (submitBtnInit) submitBtnInit.innerText = originalText; }, 3000);
                                        }
                                    })
                                    .catch(err => {
                                        if (submitBtnInit) submitBtnInit.innerText = "ERROR";
                                        setTimeout(() => { if (submitBtnInit) submitBtnInit.innerText = originalText; }, 3000);
                                    });
                            };

                            const siteKey = typeof caseStudyAjax !== 'undefined' ? caseStudyAjax.recaptchaSiteKey : '';
                            if (typeof grecaptcha !== 'undefined' && siteKey) {
                                grecaptcha.ready(() => {
                                    grecaptcha.execute(siteKey, { action: 'submit_contact_form' })
                                        .then(token => {
                                            submitForm(token);
                                        })
                                        .catch(err => {
                                            console.error('reCAPTCHA error:', err);
                                            submitForm();
                                        });
                                });
                            } else {
                                submitForm();
                            }
                        }
                    };
                    this.formEl.addEventListener('submit', this.handleSubmit);
                }
            }
            animFade() {
                const tweenArr = [];
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.infoFade) tweenArr.push(this.infoFade);
                if (this.formFade) tweenArr.push(this.formFade);

                const bgArr = [];
                if (this.bgFade) bgArr.push(this.bgFade);
                if (this.vectoFade) bgArr.push(this.vectoFade);

                if (tweenArr.length) {
                    const fadeTl = gsap.timeline();
                    this.master = new MasterTimeline({ timeline: fadeTl, triggerInit: this.el, tweenArr: [...bgArr, ...tweenArr], stagger: 0.1 });
                }
            }
            destroy() {
                if (this.master) { this.master.destroy(); this.master = null; }
                [this.titleSplit, this.subFade, this.infoFade, this.formFade, this.bgFade, this.vectoFade].forEach(a => a?.destroy?.());

                if (this.formEl) {
                    this.formEl.removeEventListener('submit', this.handleSubmit);
                    if (this.inputs) {
                        this.inputs.forEach(input => input.removeEventListener('input', this.handleInput));
                    }
                }
            }
        }
    };
    const ServicePage = {
        ParallaxBg: class {
            constructor() {
                this.parallax = null;
                this.onScroll = this.onScroll.bind(this);
            }
            trigger() {
                this.parallax = document.getElementById("parallax");
                if (this.parallax) {
                    window.addEventListener("scroll", this.onScroll);
                }
            }
            onScroll() {
                let rect = this.parallax.getBoundingClientRect();
                if (rect.top <= 0) {
                    let offset = -rect.top;
                    this.parallax.style.backgroundPositionY = (offset * 0.5) + "px";
                } else {
                    this.parallax.style.backgroundPositionY = "0px";
                }
            }
            destroy() {
                if (this.parallax) {
                    window.removeEventListener("scroll", this.onScroll);
                    this.parallax = null;
                }
            }
        },
        Hero: class {
            constructor() {
                this.el = null;
                this.titleSplit = null;
                this.descFade = null;
                this.btnFade = null;
                this.bgFade = null;
                this.master = null;
            }
            trigger() {
                this.el = document.querySelector('.service_hero');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const title = this.el.querySelector('.service_hero_left');
                const desc = this.el.querySelector('.service_hero_right_txt');
                const btn = this.el.querySelector('.service_hero_right_discover');
                const bg = this.el.querySelector('.service_hero_bg');

                if (title) this.titleSplit = new FadeSplitText({ el: title });
                if (desc) this.descFade = new FadeIn({ el: desc, type: 'bottom', isDisableRevert: true });
                if (btn) this.btnFade = new FadeIn({ el: btn, type: 'bottom', isDisableRevert: true });
                if (bg) this.bgFade = new FadeIn({ el: bg, type: 'none', isDisableRevert: true });
            }
            animFade() {
                const tweenArr = [];
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.descFade) tweenArr.push(this.descFade);
                if (this.btnFade) tweenArr.push(this.btnFade);
                if (this.bgFade) tweenArr.push(this.bgFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({ triggerInit: this.el, tweenArr, stagger: 0.1 });
                }
            }
            destroy() {
                if (this.master) { this.master.destroy(); this.master = null; }
                [this.titleSplit, this.descFade, this.btnFade, this.bgFade].forEach(a => a?.destroy?.());
            }
        },
        ServiceImg: class {
            constructor() {
                this.el = null;
                this.imgFade = null;
                this.master = null;
            }
            trigger() {
                this.el = document.querySelector('.service_img');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const img = this.el.querySelectorAll('.service_img_content');
                if (img.length > 0) {
                    this.imgFade = new FadeIn({ el: img, type: 'none', isDisableRevert: true, clearProps: 'opacity' });
                }
            }
            animFade() {
                if (this.imgFade) {
                    this.master = new MasterTimeline({ triggerInit: this.el, tweenArr: [this.imgFade] });
                }
            }
            destroy() {
                if (this.master) { this.master.destroy(); this.master = null; }
                if (this.imgFade) { this.imgFade.destroy(); this.imgFade = null; }
            }
        },
        ServicesTop: class {
            constructor() {
                this.el = null;
                this.subFade = null;
                this.titleSplit = null;
                this.desFade = null;
                this.bgLeftFade = null;
                this.bgRightFade = null;
                this.master = null;
            }
            trigger() {
                this.el = document.querySelector('.home_services_top');
                if (!this.el) return;
                this.setup();
                this.animFade();
            }
            setup() {
                const sub = this.el.querySelector('.home_services_sub');
                const title = this.el.querySelector('.home_services_title');
                const des = this.el.querySelector('.home_services_des');
                const bgLeft = this.el.querySelector('.home_services_top_bg.left');
                const bgRight = this.el.querySelector('.home_services_top_bg.right');

                if (sub) this.subFade = new FadeIn({ el: sub, type: 'bottom', isDisableRevert: true });
                if (title) this.titleSplit = new FadeSplitText({ el: title });
                if (des) this.desFade = new FadeIn({ el: des, type: 'bottom', isDisableRevert: true });
                if (bgLeft) this.bgLeftFade = new FadeIn({ el: bgLeft, type: 'left', isDisableRevert: true });
                if (bgRight) this.bgRightFade = new FadeIn({ el: bgRight, type: 'right', isDisableRevert: true });
            }
            animFade() {
                const tweenArr = [];
                if (this.subFade) tweenArr.push(this.subFade);
                if (this.titleSplit) tweenArr.push(this.titleSplit);
                if (this.desFade) tweenArr.push(this.desFade);

                // Backgrounds can fade in simultaneously with text
                const bgArr = [];
                if (this.bgLeftFade) bgArr.push(this.bgLeftFade);
                if (this.bgRightFade) bgArr.push(this.bgRightFade);

                if (tweenArr.length) {
                    this.master = new MasterTimeline({
                        triggerInit: this.el,
                        scrollTrigger: {
                            start: 'top top+=75%',
                            once: true
                        },
                        tweenArr: [...tweenArr, ...bgArr],
                        stagger: 0.1
                    });
                }
            }
            destroy() {
                if (this.master) { this.master.destroy(); this.master = null; }
                [this.subFade, this.titleSplit, this.desFade, this.bgLeftFade, this.bgRightFade].forEach(a => a?.destroy?.());
            }
        },
        ServicesList: class {
            constructor() {
                this.masterList = [];
            }
            trigger() {
                const items = document.querySelectorAll('.home_services_item');
                if (!items || items.length === 0) return;

                items.forEach(item => {
                    const title = item.querySelector('.home_services_content_title');
                    const sub = item.querySelector('.home_services_content_sub');
                    const bottomDes = item.querySelector('.home_services_content_bottom_des');
                    const listItems = item.querySelectorAll('.home_services_content_bottom_list_item');
                    const img = item.querySelector('.home_services_img');

                    const tweenArr = [];
                    if (title) tweenArr.push(new FadeSplitText({ el: title }));
                    if (sub) tweenArr.push(new FadeIn({ el: sub, type: 'bottom', isDisableRevert: true }));
                    if (bottomDes) tweenArr.push(new FadeIn({ el: bottomDes, type: 'bottom', isDisableRevert: true }));
                    if (listItems && listItems.length > 0) {
                        tweenArr.push(new FadeIn({ el: listItems, type: 'bottom', isDisableRevert: true, stagger: 0.1 }));
                    }
                    if (img) {
                        const isMobile = getScreenType().isMobile;
                        tweenArr.push(new FadeIn({ el: img, type: isMobile ? 'bottom' : 'none', isDisableRevert: true, delay: 1.2 }));
                    }

                    if (tweenArr.length > 0) {
                        this.masterList.push(new MasterTimeline({
                            triggerInit: item,
                            tweenArr: tweenArr,
                            stagger: 0.1
                        }));
                    }
                });
            }
            destroy() {
                this.masterList.forEach(m => m.destroy());
                this.masterList = [];
            }
        }

    };

    class PageManager {
        constructor(page) {
            this.sections = Object.values(page).map((section) => new section());

            this.boundSetupHandler = this.setupHandler.bind(this);
            this.boundOncePlayHandler = this.oncePlayHandler.bind(this);
            this.boundEnterPlayHandler = this.enterPlayHandler.bind(this);
        }

        initOnce(data) {
            const container = data.next.container;
            console.log("initOnce", container);
            container.addEventListener("onceSetup", (event) => {
                this.boundSetupHandler({ detail: event.detail, mode: "once" });
            });
            container.addEventListener("oncePlay", this.boundOncePlayHandler);
        }

        initEnter(data) {
            const container = data.next.container;
            console.log("initEnter", container);
            container.addEventListener("enterSetup", (event) => {
                this.boundSetupHandler({ detail: event.detail, mode: "enter" });
            });
            container.addEventListener("enterPlay", this.boundEnterPlayHandler);
        }

        oncePlayHandler(event) {
            this.sections.forEach((section) => {
                if (section.playOnce) {
                    section.playOnce(event.detail);
                }
            });
        }

        enterPlayHandler(event) {
            this.sections.forEach((section) => {
                if (section.playEnter) {
                    section.playEnter(event.detail);
                }
            });
        }

        setupHandler(event) {
            const data = event.detail;
            const mode = event.mode;
            setTimeout(() => {
                $('[data-init]').removeAttr('data-init');
            }, 100);

            this.sections.forEach((section) => {
                if (section.trigger) {
                    section.trigger(data);
                }
                if (typeof section.setup === "function" && section.setup.length > 0) {
                    section.setup(data, mode);
                }
            });
        }

        destroy(data) {
            const container = data.next.container;
            container.removeEventListener("onceSetup", this.boundSetupHandler);
            container.removeEventListener("oncePlay", this.boundOncePlayHandler);
            container.removeEventListener("enterSetup", this.boundSetupHandler);
            container.removeEventListener("enterPlay", this.boundEnterPlayHandler);

            this.sections.forEach((section) => {
                if (section.destroy) {
                    section.destroy();
                }
                if (section.cleanTrigger) {
                    section.cleanTrigger();
                }
            });
        }
    }

    class HomePageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class OurClientPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class AboutUsPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class CareerPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class CareerDetailPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class CaseStudyPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class CaseStudyDetailPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class ContactPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    class ServicePageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }
    const PolicyPage = {};
    class PolicyPageManager extends PageManager {
        constructor(page) {
            super(page);
        }
    }

    const PageManagerRegistry = {
        home: new HomePageManager(HomePage),
        ourClient: new OurClientPageManager(OurClientPage),
        aboutus: new AboutUsPageManager(AboutUsPage),
        career: new CareerPageManager(CareerPage),
        careerDetail: new CareerDetailPageManager(CareerDetailPage),
        caseStudy: new CaseStudyPageManager(CaseStudyPage),
        caseStudyDetail: new CaseStudyDetailPageManager(CaseStudyDetailPage),
        contact: new ContactPageManager(ContactPage),
        service: new ServicePageManager(ServicePage),
        policy: new PolicyPageManager(PolicyPage),
    };

    const getNamespace = () => {
        let ns = $(".main").attr("data-namespace");
        return ns || "home";
    };

    // Direct initialization on DOM Ready without Barba.js
    $(document).ready(() => {
        // 0. Initialize custom lazy loading for images
        const lazyImageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazyload');
                    }
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '300px 0px',
        });

        document.querySelectorAll('img.lazyload').forEach((img) => {
            // For service slider images (except the first one), let's NOT observe them here
            // because we want them to load specifically when their slide transition begins.
            const isSlideImage = img.closest('.home_services_item');
            if (isSlideImage) {
                const serviceItems = Array.from(document.querySelectorAll('.home_services_item'));
                const itemIndex = serviceItems.indexOf(isSlideImage);
                if (itemIndex > 0) {
                    return;
                }
            }
            lazyImageObserver.observe(img);
        });

        const namespace = getNamespace();
        const data = {
            next: {
                container: document.querySelector('.main-inner') || document.body,
                namespace: namespace
            }
        };

        // 1. Initialize global and page lifecycle utilities
        smoothScroll.init(data);
        globalChange.init(data);

        if (typeof documentHeightObserver === "function") {
            documentHeightObserver("init", data);
        }

        // 2. Initialize corresponding page manager
        const pageManager = PageManagerRegistry[namespace];
        if (pageManager) {
            pageManager.initOnce(data);
        }

        // 3. Initialize and play loader
        loader.init(data);
        loader.play(data);
        resetScroll(data);
        header.init(data);
        footer.init(data);
        cta.init(data);
        buttonTop.init();
    });
};
window.onload = mainScript;


