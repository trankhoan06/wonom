/*
 * Reusable WebGL image transition.
 * Usage:
 * const transition = new WebGLImageTransition({
 *   container: document.querySelector('.my-section'),
 *   images: ['/image-1.jpg', '/image-2.jpg'],
 *   duration: 900
 * });
 * transition.goTo(1);
 */
(function (window) {
    'use strict';

    const vertexShader = `
        attribute vec2 aPosition;
        varying vec2 vUv;
        void main() {
            vUv = aPosition * 0.5 + 0.5;
            gl_Position = vec4(aPosition, 0.0, 1.0);
        }
    `;

    const fragmentShader = `
        precision highp float;

        varying vec2 vUv;
        uniform sampler2D uCurrentTexture;
        uniform sampler2D uNextTexture;
        uniform vec2 uResolution;
        uniform float uCurrentAspect;
        uniform float uNextAspect;
        uniform float uProgress;

        vec2 coverUv(vec2 uv, float imageAspect) {
            float canvasAspect = uResolution.x / uResolution.y;
            vec2 scale = canvasAspect > imageAspect
                ? vec2(1.0, imageAspect / canvasAspect)
                : vec2(canvasAspect / imageAspect, 1.0);
            return (uv - 0.5) * scale + 0.5;
        }

        float hash(vec2 point) {
            return fract(52.9829189 * fract(dot(point, vec2(0.06711056, 0.00583715))));
        }

        float smoothNoise(vec2 point) {
            vec2 cell = floor(point);
            vec2 local = fract(point);
            float a = hash(cell);
            float b = hash(cell + vec2(1.0, 0.0));
            float c = hash(cell + vec2(0.0, 1.0));
            float d = hash(cell + vec2(1.0, 1.0));
            vec2 blend = local * local * (3.0 - 2.0 * local);
            return mix(a, b, blend.x) + (c - a) * blend.y * (1.0 - blend.x) + (d - b) * blend.x * blend.y;
        }

        void main() {
            vec2 currentUv = coverUv(vUv, uCurrentAspect);
            vec2 nextUv = coverUv(vUv, uNextAspect);
            float canvasAspect = uResolution.x / uResolution.y;
            vec2 aspectScale = canvasAspect > 1.0
                ? vec2(canvasAspect, 1.0)
                : vec2(1.0, 1.0 / canvasAspect);
            float distanceFromCenter = length((vUv - 0.5) * aspectScale);
            float maxRadius = length(vec2(0.5) * aspectScale);
            float edgeWidth = maxRadius * 0.36;
            float noise = smoothNoise(vUv * 9.0 + vec2(uProgress * 0.7, -uProgress * 0.45));
            float radius = mix(-edgeWidth, maxRadius + edgeWidth, uProgress);
            float noisePulse = 4.0 * uProgress * (1.0 - uProgress);
            float rippleWave = sin(distanceFromCenter * 42.0 - uProgress * 9.0);
            float ripple = rippleWave * edgeWidth * 0.06 * noisePulse;
            float noisyDistance = distanceFromCenter
                + (noise - 0.5) * edgeWidth * 0.16 * noisePulse
                + ripple;
            float reveal = 1.0 - smoothstep(radius - edgeWidth, radius, noisyDistance);

            float currentScale = 1.0 - reveal * 0.025;
            float nextScale = 1.025 - reveal * 0.025;
            vec2 radialDirection = normalize((vUv - 0.5) + vec2(0.0001));
            vec2 uvRipple = radialDirection * rippleWave * 0.012 * noisePulse;
            vec2 currentZoomUv = (currentUv - 0.5) * currentScale + 0.5 + uvRipple * (1.0 - reveal);
            vec2 nextZoomUv = (nextUv - 0.5) * nextScale + 0.5 - uvRipple * reveal;
            vec4 currentColor = texture2D(uCurrentTexture, currentZoomUv);
            vec4 nextColor = texture2D(uNextTexture, nextZoomUv);

            gl_FragColor = mix(currentColor, nextColor, reveal);
        }
    `;

    const createShader = (gl, type, source) => {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            const error = gl.getShaderInfoLog(shader);
            gl.deleteShader(shader);
            throw new Error(`WebGL shader error: ${error}`);
        }

        return shader;
    };

    const createProgram = (gl) => {
        const program = gl.createProgram();
        gl.attachShader(program, createShader(gl, gl.VERTEX_SHADER, vertexShader));
        gl.attachShader(program, createShader(gl, gl.FRAGMENT_SHADER, fragmentShader));
        gl.linkProgram(program);

        if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
            const error = gl.getProgramInfoLog(program);
            gl.deleteProgram(program);
            throw new Error(`WebGL program error: ${error}`);
        }

        return program;
    };

    class WebGLImageTransition {
        constructor(options) {
            this.container = options.container;
            this.images = options.images || [];
            this.duration = options.duration || 900;
            const isSmallScreen = window.matchMedia('(max-width: 767px)').matches;
            const pixelRatioCap = options.maxPixelRatio || (isSmallScreen ? 1 : 1.35);
            this.pixelRatio = options.pixelRatio || Math.min(window.devicePixelRatio || 1, pixelRatioCap);
            this.maxCanvasPixels = options.maxCanvasPixels || (isSmallScreen ? 1200000 : 2500000);
            this.maxTextureDimension = options.maxTextureDimension || (isSmallScreen ? 1280 : 2048);
            this.maxFps = options.maxFps || (isSmallScreen ? 45 : 60);
            this.lazy = Boolean(options.lazy);
            this.currentIndex = options.initialIndex || 0;
            this.pendingIndex = this.currentIndex;
            this.textures = [];
            this.isTransitioning = false;
            this.rafId = null;
            this.resizeRafId = null;
            this.lastFrameTime = 0;
            this.positionedContainer = false;
            this.isDestroyed = false;
            this.isInitialized = false;
            this.isVisible = true;

            if (!this.container || this.images.length < 1) return;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
            document.addEventListener('visibilitychange', this.handleVisibilityChange);

            const canObserveVisibility = 'IntersectionObserver' in window;
            if (canObserveVisibility) {
                this.visibilityObserver = new IntersectionObserver((entries) => {
                    const entry = entries[0];
                    this.isVisible = Boolean(entry && entry.isIntersecting);

                    if (this.isVisible && !this.isInitialized) this.initialize();
                    if (this.isVisible && this.isInitialized) this.scheduleResize();
                    if (!this.isVisible && this.isTransitioning) this.finishTransitionImmediately();
                }, { rootMargin: '200px 0px' });
                this.visibilityObserver.observe(this.container);
            }

            if (!this.lazy || !canObserveVisibility) this.initialize();
        }

        initialize() {
            if (this.isInitialized || this.isDestroyed) return;
            this.isInitialized = true;
            try {
                if (window.getComputedStyle(this.container).position === 'static') {
                    this.container.style.position = 'relative';
                    this.positionedContainer = true;
                }
                this.setup();
                this.preloadTextures();
            } catch (error) {
                console.warn('[WebGLImageTransition] Canvas could not be initialized.', error);
                this.destroy();
                this.container.classList.remove('webgl-transition-ready');
            }
        }

        setup() {
            this.canvas = document.createElement('canvas');
            this.canvas.className = 'webgl-image-transition-canvas';
            this.canvas.setAttribute('aria-hidden', 'true');
            this.container.prepend(this.canvas);

            const contextOptions = {
                alpha: false,
                antialias: false,
                depth: false,
                stencil: false,
                preserveDrawingBuffer: false,
                powerPreference: 'high-performance',
                desynchronized: true,
            };
            this.gl = this.canvas.getContext('webgl', contextOptions)
                || this.canvas.getContext('experimental-webgl', contextOptions);
            if (!this.gl) throw new Error('WebGL is not supported');

            const gl = this.gl;
            this.program = createProgram(gl);
            this.locations = {
                position: gl.getAttribLocation(this.program, 'aPosition'),
                currentTexture: gl.getUniformLocation(this.program, 'uCurrentTexture'),
                nextTexture: gl.getUniformLocation(this.program, 'uNextTexture'),
                resolution: gl.getUniformLocation(this.program, 'uResolution'),
                currentAspect: gl.getUniformLocation(this.program, 'uCurrentAspect'),
                nextAspect: gl.getUniformLocation(this.program, 'uNextAspect'),
                progress: gl.getUniformLocation(this.program, 'uProgress'),
            };

            const vertices = new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]);
            this.positionBuffer = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, this.positionBuffer);
            gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);
            gl.useProgram(this.program);
            gl.enableVertexAttribArray(this.locations.position);
            gl.vertexAttribPointer(this.locations.position, 2, gl.FLOAT, false, 0, 0);
            gl.uniform1i(this.locations.currentTexture, 0);
            gl.uniform1i(this.locations.nextTexture, 1);
            gl.clearColor(0, 0, 0, 1);

            this.scheduleResize = this.scheduleResize.bind(this);
            this.resizeObserver = new ResizeObserver(this.scheduleResize);
            this.resizeObserver.observe(this.container);
            this.resize();
        }

        preloadTextures() {
            const texturePromises = new Map();
            const requests = this.images.map((source) => {
                if (!texturePromises.has(source)) {
                    texturePromises.set(source, this.loadTexture(source));
                }
                return texturePromises.get(source);
            });

            Promise.all(requests)
                .then((textures) => {
                    if (this.isDestroyed) {
                        textures.forEach(({ texture }) => this.gl && this.gl.deleteTexture(texture));
                        return;
                    }

                    this.textures = textures;
                    this.currentIndex = Math.min(this.pendingIndex, textures.length - 1);
                    this.container.classList.add('webgl-transition-ready');
                    this.render(0, this.currentIndex, this.currentIndex);
                })
                .catch(() => {
                    if (this.isDestroyed) return;
                    console.warn('[WebGLImageTransition] Image preload failed; using the HTML fallback.');
                    this.destroy();
                    this.container.classList.remove('webgl-transition-ready');
                });
        }

        loadTexture(source) {
            return new Promise((resolve, reject) => {
                const image = new Image();
                image.decoding = 'async';
                image.onload = () => {
                    if (this.isDestroyed || !this.gl) {
                        reject(new Error('WebGL transition was destroyed'));
                        return;
                    }

                    const gl = this.gl;
                    const texture = gl.createTexture();
                    let uploadSource = image;
                    let resizeCanvas = null;
                    const largestDimension = Math.max(image.naturalWidth, image.naturalHeight);

                    if (largestDimension > this.maxTextureDimension) {
                        const scale = this.maxTextureDimension / largestDimension;
                        resizeCanvas = document.createElement('canvas');
                        resizeCanvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                        resizeCanvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
                        const context = resizeCanvas.getContext('2d', { alpha: false });
                        context.imageSmoothingEnabled = true;
                        context.imageSmoothingQuality = 'high';
                        context.drawImage(image, 0, 0, resizeCanvas.width, resizeCanvas.height);
                        uploadSource = resizeCanvas;
                    }

                    gl.bindTexture(gl.TEXTURE_2D, texture);
                    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
                    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, uploadSource);
                    if (resizeCanvas) {
                        resizeCanvas.width = 1;
                        resizeCanvas.height = 1;
                    }
                    resolve({ texture, aspect: image.naturalWidth / image.naturalHeight });
                };
                image.onerror = reject;
                image.src = source;
            });
        }

        resize() {
            if (!this.gl || !this.canvas) return;
            let width = Math.max(1, Math.round(this.container.clientWidth * this.pixelRatio));
            let height = Math.max(1, Math.round(this.container.clientHeight * this.pixelRatio));
            const pixelCount = width * height;

            if (pixelCount > this.maxCanvasPixels) {
                const scale = Math.sqrt(this.maxCanvasPixels / pixelCount);
                width = Math.max(1, Math.round(width * scale));
                height = Math.max(1, Math.round(height * scale));
            }

            if (this.canvas.width !== width || this.canvas.height !== height) {
                this.canvas.width = width;
                this.canvas.height = height;
                this.gl.viewport(0, 0, width, height);
            }

            if (this.textures.length) this.render(0, this.currentIndex, this.currentIndex);
        }

        scheduleResize() {
            if (this.resizeRafId || this.isDestroyed) return;
            this.resizeRafId = requestAnimationFrame(() => {
                this.resizeRafId = null;
                this.resize();
            });
        }

        render(progress, currentIndex, nextIndex) {
            if (!this.gl || !this.textures.length || !this.isVisible || document.hidden) return;
            const gl = this.gl;
            const current = this.textures[currentIndex];
            const next = this.textures[nextIndex];

            gl.activeTexture(gl.TEXTURE0);
            gl.bindTexture(gl.TEXTURE_2D, current.texture);
            gl.activeTexture(gl.TEXTURE1);
            gl.bindTexture(gl.TEXTURE_2D, next.texture);

            gl.uniform2f(this.locations.resolution, this.canvas.width, this.canvas.height);
            gl.uniform1f(this.locations.currentAspect, current.aspect);
            gl.uniform1f(this.locations.nextAspect, next.aspect);
            gl.uniform1f(this.locations.progress, progress);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        }

        goTo(index) {
            if (!this.images.length) return;
            this.pendingIndex = ((index % this.images.length) + this.images.length) % this.images.length;
            if (!this.isInitialized || !this.isVisible || document.hidden) {
                this.currentIndex = this.pendingIndex;
                return;
            }
            if (!this.textures.length || this.pendingIndex === this.currentIndex || this.isTransitioning) return;

            this.isTransitioning = true;
            const fromIndex = this.currentIndex;
            const toIndex = this.pendingIndex;
            const startTime = performance.now();
            const frameInterval = 1000 / this.maxFps;
            const ease = (value) => -(Math.cos(Math.PI * value) - 1) / 2;

            const update = (time) => {
                if (this.isDestroyed || !this.isVisible || document.hidden) {
                    this.finishTransitionImmediately();
                    return;
                }

                const progress = Math.min((time - startTime) / this.duration, 1);
                if (!this.lastFrameTime || time - this.lastFrameTime >= frameInterval || progress === 1) {
                    this.lastFrameTime = time;
                    this.render(ease(progress), fromIndex, toIndex);
                }

                if (progress < 1) {
                    this.rafId = requestAnimationFrame(update);
                } else {
                    this.currentIndex = toIndex;
                    this.isTransitioning = false;
                    this.rafId = null;
                    this.lastFrameTime = 0;
                    this.render(0, this.currentIndex, this.currentIndex);
                    if (this.pendingIndex !== this.currentIndex) this.goTo(this.pendingIndex);
                }
            };

            this.rafId = requestAnimationFrame(update);
        }

        finishTransitionImmediately() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
            this.rafId = null;
            this.lastFrameTime = 0;
            this.currentIndex = this.pendingIndex;
            this.isTransitioning = false;
            if (this.isVisible && !document.hidden && this.textures.length) {
                this.render(0, this.currentIndex, this.currentIndex);
            }
        }

        handleVisibilityChange() {
            if (document.hidden) {
                this.finishTransitionImmediately();
            } else if (this.isVisible) {
                this.scheduleResize();
            }
        }

        destroy() {
            this.isDestroyed = true;
            if (this.rafId) cancelAnimationFrame(this.rafId);
            if (this.resizeRafId) cancelAnimationFrame(this.resizeRafId);
            if (this.resizeObserver) this.resizeObserver.disconnect();
            if (this.visibilityObserver) this.visibilityObserver.disconnect();
            if (this.handleVisibilityChange) {
                document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            }

            if (this.gl) {
                const uniqueTextures = new Set(this.textures.map(({ texture }) => texture));
                uniqueTextures.forEach((texture) => this.gl.deleteTexture(texture));
                if (this.positionBuffer) this.gl.deleteBuffer(this.positionBuffer);
                if (this.program) this.gl.deleteProgram(this.program);
                const loseContext = this.gl.getExtension('WEBGL_lose_context');
                if (loseContext) loseContext.loseContext();
            }

            if (this.canvas) this.canvas.remove();
            if (this.container) this.container.classList.remove('webgl-transition-ready');
            if (this.positionedContainer) this.container.style.position = '';
            this.textures = [];
            this.isTransitioning = false;
        }
    }

    window.WebGLImageTransition = WebGLImageTransition;
}(window));
