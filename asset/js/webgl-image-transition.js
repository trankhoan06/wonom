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
            return fract(10000.0 * sin(17.0 * point.x + point.y * 0.1) * (0.1 + abs(sin(point.y * 13.0 + point.x))));
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
            float noise = smoothNoise(vUv * uResolution / 100.0);
            vec2 centerVector = vec2(0.5) - vUv;
            vec2 direction = vec2(0.0, centerVector.y / max(length(centerVector), 0.0001));
            float strength = 0.2 * (1.0 + noise * 0.5);
            vec4 currentColor = texture2D(uCurrentTexture, currentUv + direction * uProgress * strength);
            vec4 nextColor = texture2D(uNextTexture, nextUv - direction * (1.0 - uProgress) * strength);

            gl_FragColor = mix(currentColor, nextColor, uProgress);
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
            this.pixelRatio = options.pixelRatio || Math.min(window.devicePixelRatio || 1, 2);
            this.currentIndex = options.initialIndex || 0;
            this.pendingIndex = this.currentIndex;
            this.textures = [];
            this.isTransitioning = false;
            this.rafId = null;
            this.positionedContainer = false;

            if (!this.container || this.images.length < 1) return;

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

            this.gl = this.canvas.getContext('webgl', { alpha: false, antialias: true })
                || this.canvas.getContext('experimental-webgl', { alpha: false, antialias: true });
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
            gl.clearColor(0, 0, 0, 1);

            this.resize = this.resize.bind(this);
            this.resizeObserver = new ResizeObserver(this.resize);
            this.resizeObserver.observe(this.container);
            this.resize();
        }

        preloadTextures() {
            Promise.all(this.images.map((source) => this.loadTexture(source)))
                .then((textures) => {
                    this.textures = textures;
                    this.currentIndex = Math.min(this.pendingIndex, textures.length - 1);
                    this.container.classList.add('webgl-transition-ready');
                    this.render(0, this.currentIndex, this.currentIndex);
                })
                .catch(() => {
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
                    const gl = this.gl;
                    const texture = gl.createTexture();
                    gl.bindTexture(gl.TEXTURE_2D, texture);
                    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
                    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
                    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
                    resolve({ texture, aspect: image.naturalWidth / image.naturalHeight });
                };
                image.onerror = reject;
                image.src = source;
            });
        }

        resize() {
            if (!this.gl || !this.canvas) return;
            const width = Math.max(1, Math.round(this.container.clientWidth * this.pixelRatio));
            const height = Math.max(1, Math.round(this.container.clientHeight * this.pixelRatio));

            if (this.canvas.width !== width || this.canvas.height !== height) {
                this.canvas.width = width;
                this.canvas.height = height;
                this.gl.viewport(0, 0, width, height);
            }

            if (this.textures.length) this.render(0, this.currentIndex, this.currentIndex);
        }

        render(progress, currentIndex, nextIndex) {
            if (!this.gl || !this.textures.length) return;
            const gl = this.gl;
            const current = this.textures[currentIndex];
            const next = this.textures[nextIndex];

            gl.useProgram(this.program);
            gl.bindBuffer(gl.ARRAY_BUFFER, this.positionBuffer);
            gl.enableVertexAttribArray(this.locations.position);
            gl.vertexAttribPointer(this.locations.position, 2, gl.FLOAT, false, 0, 0);

            gl.activeTexture(gl.TEXTURE0);
            gl.bindTexture(gl.TEXTURE_2D, current.texture);
            gl.uniform1i(this.locations.currentTexture, 0);
            gl.activeTexture(gl.TEXTURE1);
            gl.bindTexture(gl.TEXTURE_2D, next.texture);
            gl.uniform1i(this.locations.nextTexture, 1);

            gl.uniform2f(this.locations.resolution, this.canvas.width, this.canvas.height);
            gl.uniform1f(this.locations.currentAspect, current.aspect);
            gl.uniform1f(this.locations.nextAspect, next.aspect);
            gl.uniform1f(this.locations.progress, progress);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        }

        goTo(index) {
            if (!this.images.length) return;
            this.pendingIndex = ((index % this.images.length) + this.images.length) % this.images.length;
            if (!this.textures.length || this.pendingIndex === this.currentIndex || this.isTransitioning) return;

            this.isTransitioning = true;
            const fromIndex = this.currentIndex;
            const toIndex = this.pendingIndex;
            const startTime = performance.now();
            const ease = (value) => value < 0.5
                ? 4 * value * value * value
                : 1 - Math.pow(-2 * value + 2, 3) / 2;

            const update = (time) => {
                const progress = Math.min((time - startTime) / this.duration, 1);
                this.render(ease(progress), fromIndex, toIndex);

                if (progress < 1) {
                    this.rafId = requestAnimationFrame(update);
                } else {
                    this.currentIndex = toIndex;
                    this.isTransitioning = false;
                    if (this.pendingIndex !== this.currentIndex) this.goTo(this.pendingIndex);
                }
            };

            this.rafId = requestAnimationFrame(update);
        }

        destroy() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
            if (this.resizeObserver) this.resizeObserver.disconnect();

            if (this.gl) {
                this.textures.forEach(({ texture }) => this.gl.deleteTexture(texture));
                if (this.positionBuffer) this.gl.deleteBuffer(this.positionBuffer);
                if (this.program) this.gl.deleteProgram(this.program);
            }

            if (this.canvas) this.canvas.remove();
            if (this.positionedContainer) this.container.style.position = '';
            this.textures = [];
            this.isTransitioning = false;
        }
    }

    window.WebGLImageTransition = WebGLImageTransition;
}(window));
