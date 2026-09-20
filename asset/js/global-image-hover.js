/*
 * One shared WebGL renderer for every `.img-hover` card.
 * Add the class to a card containing an <img>, or directly to an <img>.
 * The global instance is available at window.imageHoverShader.
 */
(function (window) {
    'use strict';

    const VERTEX_SHADER = `
        attribute vec2 aPosition;
        varying vec2 vUv;

        void main() {
            vUv = aPosition * 0.5 + 0.5;
            gl_Position = vec4(aPosition, 0.0, 1.0);
        }
    `;

    const FRAGMENT_SHADER = `
        precision highp float;

        varying vec2 vUv;
        uniform sampler2D uTexture;
        uniform vec2 uResolution;
        uniform float uImageAspect;
        uniform vec2 uMouse;
        uniform float uProgress;
        uniform float uHover;
        uniform float uDirection;
        uniform float uTime;

        const float PI = 3.14159265359;

        vec2 coverUv(vec2 uv, float imageAspect) {
            float canvasAspect = uResolution.x / uResolution.y;
            vec2 scale = canvasAspect > imageAspect
                ? vec2(1.0, imageAspect / canvasAspect)
                : vec2(canvasAspect / imageAspect, 1.0);
            return (uv - 0.5) * scale + 0.5;
        }

        float maxCornerDistance(vec2 point) {
            float bottomLeft = distance(point, vec2(0.0, 0.0));
            float bottomRight = distance(point, vec2(1.0, 0.0));
            float topLeft = distance(point, vec2(0.0, 1.0));
            float topRight = distance(point, vec2(1.0, 1.0));
            return max(max(bottomLeft, bottomRight), max(topLeft, topRight));
        }

        void main() {
            vec2 fromMouse = vUv - uMouse;
            float distanceFromMouse = length(fromMouse);
            float normalizedDistance = distanceFromMouse / max(maxCornerDistance(uMouse), 0.0001);
            float envelope = sin(uProgress * PI);
            float transitionWave = sin(-PI * 6.0 * distanceFromMouse + uTime) * 0.012 * envelope * uDirection;
            float cursorMask = 1.0 - smoothstep(0.0, 0.34, distanceFromMouse);
            float cursorLens = sin(cursorMask * PI) * 0.008 * uHover;
            float wave = transitionWave + cursorLens;
            vec2 direction = fromMouse / max(distanceFromMouse, 0.0001);
            vec2 uv = coverUv(vUv + direction * wave, uImageAspect);
            vec4 image = texture2D(uTexture, uv);

            float ring = 1.0 - smoothstep(0.0, 0.075, abs(normalizedDistance - uProgress));
            float gray = dot(image.rgb, vec3(0.299, 0.587, 0.114));
            vec3 color = mix(image.rgb, vec3(gray), ring * envelope * 0.22);
            float exitMix = step(uDirection, 0.0);
            color += ring * envelope * mix(0.04, 0.055, exitMix);

            gl_FragColor = vec4(color, image.a);
        }
    `;

    const createShader = (gl, type, source) => {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            const message = gl.getShaderInfoLog(shader);
            gl.deleteShader(shader);
            throw new Error(message);
        }

        return shader;
    };

    const createProgram = (gl) => {
        const program = gl.createProgram();
        gl.attachShader(program, createShader(gl, gl.VERTEX_SHADER, VERTEX_SHADER));
        gl.attachShader(program, createShader(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER));
        gl.linkProgram(program);

        if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
            const message = gl.getProgramInfoLog(program);
            gl.deleteProgram(program);
            throw new Error(message);
        }

        return program;
    };

    class ImageHoverShader {
        constructor(options = {}) {
            this.selector = options.selector || '.img-hover';
            this.duration = options.duration || 1.2;
            this.maxTextures = options.maxTextures || 8;
            this.pixelRatio = Math.min(options.pixelRatio || window.devicePixelRatio || 1, 1.5);
            this.textureCache = new Map();
            this.activeCard = null;
            this.activeImage = null;
            this.activeTexture = null;
            this.tween = null;
            this.outgoingLayers = new Set();
            this.state = { progress: 0, hover: 0, direction: 1, time: 0 };
            this.renderFrame = null;
            this.enabled = Boolean(
                window.gsap
                && window.matchMedia('(hover: hover) and (pointer: fine)').matches
                && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
            );

            if (!this.enabled) return;

            try {
                this.setupRenderer();
                this.bindEvents();
            } catch (error) {
                this.enabled = false;
                this.destroyRenderer();
                console.warn('[ImageHoverShader] WebGL initialization failed.', error);
            }
        }

        setupRenderer() {
            this.canvas = document.createElement('canvas');
            this.canvas.className = 'global-img-hover-canvas';
            this.canvas.setAttribute('aria-hidden', 'true');

            this.gl = this.canvas.getContext('webgl', {
                alpha: true,
                antialias: false,
                depth: false,
                powerPreference: 'high-performance',
                premultipliedAlpha: false,
            });
            if (!this.gl) throw new Error('WebGL is not supported');

            const gl = this.gl;
            this.program = createProgram(gl);
            this.locations = {
                position: gl.getAttribLocation(this.program, 'aPosition'),
                texture: gl.getUniformLocation(this.program, 'uTexture'),
                resolution: gl.getUniformLocation(this.program, 'uResolution'),
                imageAspect: gl.getUniformLocation(this.program, 'uImageAspect'),
                mouse: gl.getUniformLocation(this.program, 'uMouse'),
                progress: gl.getUniformLocation(this.program, 'uProgress'),
                hover: gl.getUniformLocation(this.program, 'uHover'),
                direction: gl.getUniformLocation(this.program, 'uDirection'),
                time: gl.getUniformLocation(this.program, 'uTime'),
            };

            this.buffer = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, this.buffer);
            gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
                -1, -1,
                1, -1,
                -1, 1,
                1, 1,
            ]), gl.STATIC_DRAW);

            this.mouse = { x: 0.5, y: 0.5 };
            this.mouseXTo = gsap.quickTo(this.mouse, 'x', {
                duration: 0.28,
                ease: 'power2.out',
                onUpdate: () => this.requestRender(),
            });
            this.mouseYTo = gsap.quickTo(this.mouse, 'y', {
                duration: 0.28,
                ease: 'power2.out',
                onUpdate: () => this.requestRender(),
            });
            this.resizeObserver = new ResizeObserver(() => this.resizeCanvas());
        }

        bindEvents() {
            this.onPointerOver = this.onPointerOver.bind(this);
            this.onPointerOut = this.onPointerOut.bind(this);
            this.onPointerMove = this.onPointerMove.bind(this);
            document.addEventListener('pointerover', this.onPointerOver, { passive: true });
            document.addEventListener('pointerout', this.onPointerOut, { passive: true });
            document.addEventListener('pointermove', this.onPointerMove, { passive: true });
        }

        findCard(target) {
            return target instanceof Element ? target.closest(this.selector) : null;
        }

        findImage(card) {
            if (card instanceof HTMLImageElement) return card;
            return Array.from(card.querySelectorAll('img')).find((image) => {
                const rect = image.getBoundingClientRect();
                return rect.width > 0 && rect.height > 0;
            }) || null;
        }

        onPointerOver(event) {
            const card = this.findCard(event.target);
            if (!card || card.contains(event.relatedTarget)) return;
            this.activate(card, event);
        }

        onPointerOut(event) {
            const card = this.findCard(event.target);
            if (!card || card !== this.activeCard || card.contains(event.relatedTarget)) return;
            this.deactivate();
        }

        onPointerMove(event) {
            if (!this.activeImage) return;
            const rect = this.activeImage.getBoundingClientRect();
            const x = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
            const y = Math.min(1, Math.max(0, 1 - ((event.clientY - rect.top) / rect.height)));
            this.mouseXTo(x);
            this.mouseYTo(y);
        }

        async activate(card, event) {
            const image = this.findImage(card);
            if (!image) return;

            if (image === this.activeImage) {
                this.onPointerMove(event);
                this.animateIn();
                return;
            }

            if (!image.complete || !image.naturalWidth) {
                try {
                    await image.decode();
                } catch (_) {
                    return;
                }
            }

            if (!card.matches(':hover')) return;
            if (this.activeImage) {
                this.createOutgoingLayer();
                this.restoreActiveImage();
            }

            const texture = this.getTexture(image);
            if (!texture) return;

            this.activeCard = card;
            this.activeImage = image;
            this.activeTexture = texture;
            this.mountCanvas(image);
            this.onPointerMove(event);
            this.state.progress = 0;
            this.state.hover = 0;
            this.state.direction = 1;
            this.state.time = 0;
            this.render();
            image.dataset.webglHoverVisibility = image.style.visibility;
            image.style.visibility = 'hidden';

            this.animateIn();
        }

        animateIn() {
            if (!this.activeImage) return;
            if (this.tween) this.tween.kill();
            this.state.direction = 1;
            this.tween = gsap.to(this.state, {
                progress: 1,
                hover: 1,
                duration: this.duration,
                ease: 'power3.out',
                onUpdate: () => {
                    this.state.time += 0.12;
                    this.requestRender();
                },
            });
        }

        deactivate() {
            if (!this.activeImage) return;
            if (this.tween) this.tween.kill();

            // Restart the ripple from the pointer, but invert its displacement
            // so leaving has its own clear response instead of only fading out.
            this.state.progress = 0;
            this.state.direction = -1;

            this.tween = gsap.to(this.state, {
                progress: 1,
                hover: 0,
                duration: Math.min(this.duration * 0.78, 0.9),
                ease: 'power2.inOut',
                onUpdate: () => {
                    this.state.time += 0.12;
                    this.requestRender();
                },
                onComplete: () => this.restoreActiveImage(),
            });
        }

        createOutgoingLayer() {
            if (!this.activeImage || !this.canvas || !this.canvas.isConnected) return;

            const image = this.activeImage;
            const parent = image.parentElement;
            const layer = document.createElement('canvas');
            const context = layer.getContext('2d');
            if (!context) return;

            layer.width = this.canvas.width;
            layer.height = this.canvas.height;
            layer.className = 'global-img-hover-canvas global-img-hover-outgoing';
            layer.setAttribute('aria-hidden', 'true');
            layer.style.left = this.canvas.style.left;
            layer.style.top = this.canvas.style.top;
            layer.style.width = this.canvas.style.width;
            layer.style.height = this.canvas.style.height;
            layer.style.borderRadius = this.canvas.style.borderRadius;
            layer.style.transformOrigin = `${this.mouse.x * 100}% ${(1 - this.mouse.y) * 100}%`;
            context.drawImage(this.canvas, 0, 0);
            image.insertAdjacentElement('afterend', layer);
            this.outgoingLayers.add(layer);

            if (this.outgoingLayers.size > 2) {
                const oldestLayer = this.outgoingLayers.values().next().value;
                this.removeOutgoingLayer(oldestLayer);
            }

            const origin = `${this.mouse.x * 100}% ${(1 - this.mouse.y) * 100}%`;
            gsap.fromTo(layer, {
                opacity: 1,
                scale: 1,
                filter: 'blur(0px)',
                clipPath: `circle(80% at ${origin})`,
            }, {
                opacity: 0,
                scale: 1.025,
                filter: 'blur(2px)',
                clipPath: `circle(0% at ${origin})`,
                duration: 0.72,
                ease: 'power2.out',
                overwrite: true,
                onComplete: () => this.removeOutgoingLayer(layer, parent),
            });
        }

        removeOutgoingLayer(layer, parent = layer?.parentElement) {
            if (!layer) return;
            gsap.killTweensOf(layer);
            this.outgoingLayers.delete(layer);
            layer.remove();
            this.releaseParentPosition(parent);
        }

        mountCanvas(image) {
            const parent = image.parentElement;
            if (window.getComputedStyle(parent).position === 'static') {
                parent.dataset.webglHoverPositioned = 'true';
                parent.style.position = 'relative';
            }

            image.insertAdjacentElement('afterend', this.canvas);
            this.canvas.style.left = `${image.offsetLeft}px`;
            this.canvas.style.top = `${image.offsetTop}px`;
            this.canvas.style.borderRadius = window.getComputedStyle(image).borderRadius;
            this.resizeObserver.disconnect();
            this.resizeObserver.observe(image);
            this.resizeCanvas();
        }

        resizeCanvas() {
            if (!this.activeImage || !this.canvas || !this.gl) return;
            const width = Math.max(1, this.activeImage.offsetWidth);
            const height = Math.max(1, this.activeImage.offsetHeight);
            this.canvas.style.width = `${width}px`;
            this.canvas.style.height = `${height}px`;
            this.canvas.width = Math.round(width * this.pixelRatio);
            this.canvas.height = Math.round(height * this.pixelRatio);
            this.gl.viewport(0, 0, this.canvas.width, this.canvas.height);
            this.requestRender();
        }

        getTexture(image) {
            if (this.textureCache.has(image)) {
                const cached = this.textureCache.get(image);
                cached.lastUsed = performance.now();
                return cached;
            }

            try {
                const gl = this.gl;
                const texture = gl.createTexture();
                gl.bindTexture(gl.TEXTURE_2D, texture);
                gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
                gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);

                const entry = {
                    texture,
                    aspect: image.naturalWidth / image.naturalHeight,
                    lastUsed: performance.now(),
                };
                this.textureCache.set(image, entry);
                this.trimTextureCache(image);
                return entry;
            } catch (error) {
                console.warn('[ImageHoverShader] Image texture could not be created.', error);
                return null;
            }
        }

        trimTextureCache(activeImage) {
            if (this.textureCache.size <= this.maxTextures) return;
            const oldest = Array.from(this.textureCache.entries())
                .filter(([image]) => image !== activeImage)
                .sort((a, b) => a[1].lastUsed - b[1].lastUsed)[0];

            if (!oldest) return;
            this.gl.deleteTexture(oldest[1].texture);
            this.textureCache.delete(oldest[0]);
        }

        requestRender() {
            if (this.renderFrame) return;
            this.renderFrame = requestAnimationFrame(() => {
                this.renderFrame = null;
                this.render();
            });
        }

        render() {
            if (!this.activeImage || !this.activeTexture || !this.gl) return;
            const gl = this.gl;
            gl.useProgram(this.program);
            gl.bindBuffer(gl.ARRAY_BUFFER, this.buffer);
            gl.enableVertexAttribArray(this.locations.position);
            gl.vertexAttribPointer(this.locations.position, 2, gl.FLOAT, false, 0, 0);
            gl.activeTexture(gl.TEXTURE0);
            gl.bindTexture(gl.TEXTURE_2D, this.activeTexture.texture);
            gl.uniform1i(this.locations.texture, 0);
            gl.uniform2f(this.locations.resolution, this.canvas.width, this.canvas.height);
            gl.uniform1f(this.locations.imageAspect, this.activeTexture.aspect);
            gl.uniform2f(this.locations.mouse, this.mouse.x, this.mouse.y);
            gl.uniform1f(this.locations.progress, this.state.progress);
            gl.uniform1f(this.locations.hover, this.state.hover);
            gl.uniform1f(this.locations.direction, this.state.direction);
            gl.uniform1f(this.locations.time, this.state.time);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
        }

        restoreActiveImage() {
            if (!this.activeImage) return;
            const image = this.activeImage;
            const parent = image.parentElement;
            const activeTween = this.tween;
            this.tween = null;
            if (activeTween) activeTween.kill();
            image.style.visibility = image.dataset.webglHoverVisibility || '';
            delete image.dataset.webglHoverVisibility;
            this.resizeObserver.disconnect();
            this.canvas.remove();

            this.releaseParentPosition(parent);

            this.activeCard = null;
            this.activeImage = null;
            this.activeTexture = null;
            this.mouse.x = 0.5;
            this.mouse.y = 0.5;
        }

        releaseParentPosition(parent) {
            if (!parent || parent.dataset.webglHoverPositioned !== 'true') return;
            if (parent.querySelector('.global-img-hover-canvas')) return;
            parent.style.position = '';
            delete parent.dataset.webglHoverPositioned;
        }

        destroyRenderer() {
            if (this.tween) this.tween.kill();
            if (this.renderFrame) cancelAnimationFrame(this.renderFrame);
            if (this.resizeObserver) this.resizeObserver.disconnect();
            if (this.canvas) this.canvas.remove();
            this.outgoingLayers.forEach((layer) => this.removeOutgoingLayer(layer));

            if (this.gl) {
                this.textureCache.forEach(({ texture }) => this.gl.deleteTexture(texture));
                if (this.buffer) this.gl.deleteBuffer(this.buffer);
                if (this.program) this.gl.deleteProgram(this.program);
            }

            this.textureCache.clear();
        }

        destroy() {
            if (!this.enabled) return;
            document.removeEventListener('pointerover', this.onPointerOver);
            document.removeEventListener('pointerout', this.onPointerOut);
            document.removeEventListener('pointermove', this.onPointerMove);
            this.restoreActiveImage();
            this.destroyRenderer();
            this.enabled = false;
        }
    }

    window.ImageHoverShader = ImageHoverShader;
    window.imageHoverShader = new ImageHoverShader();
}(window));
