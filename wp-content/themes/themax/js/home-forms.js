(function () {
    'use strict';

    var config = window.wonomHome || {};
    var i18n = config.i18n || {};
    var submitting = new WeakSet();
    var pageStartedAt = Date.now();

    function t(key, fallback) {
        return i18n[key] || fallback;
    }

    function normalizePhone(value) {
        return String(value || '').replace(/[^0-9+]/g, '');
    }

    function isValidName(value) {
        value = String(value || '').trim();
        return value.length >= 2 && value.length <= 80 && !/[0-9]/.test(value);
    }

    function isValidPhone(value) {
        return /^(?:\+?84|0)(?:3|5|7|8|9)[0-9]{8}$/.test(normalizePhone(value));
    }

    function isValidGuests(value) {
        value = String(value || '').trim();
        return /^[1-9][0-9]{0,2}$/.test(value) && Number(value) <= 999;
    }

    function isValidDate(value) {
        var input = String(value || '').trim();
        var match = input.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        var day;
        var month;
        var year;
        if (match) {
            day = Number(match[1]);
            month = Number(match[2]);
            year = Number(match[3]);
        } else {
            match = input.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!match) return false;
            year = Number(match[1]);
            month = Number(match[2]);
            day = Number(match[3]);
        }
        var date = new Date(year, month - 1, day);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day && date >= today;
    }

    function isValidTime(value) {
        return /^(?:[01]\d|2[0-3]):[0-5]\d(?:\s*-\s*(?:[01]\d|2[0-3]):[0-5]\d)?$/.test(String(value || '').trim());
    }

    function fieldContainer(field) {
        return field ? field.closest('.popup_form_group, .popup_member_checkbox_wrap') : null;
    }

    function clearErrors(scope) {
        scope.querySelectorAll('.wonom-field-error').forEach(function (error) { error.remove(); });
        scope.querySelectorAll('.is-invalid').forEach(function (field) {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        });
        scope.querySelectorAll('.wonom-select.has-error').forEach(function (select) {
            select.classList.remove('has-error');
        });
    }

    function resetValidationState(scope) {
        if (!scope) return;
        clearErrors(scope);
        scope.querySelectorAll('.wonom-form-status').forEach(function (status) {
            status.className = 'wonom-form-status';
            status.textContent = '';
        });
    }

    function resetValidationWhenPopupOpens() {
        document.querySelectorAll('.popup_form, .popup_member, .popup_tour.tour').forEach(function (popup) {
            var wasOpen = popup.classList.contains('active');
            var observer = new MutationObserver(function () {
                var isOpen = popup.classList.contains('active');
                if (isOpen && !wasOpen) resetValidationState(popup);
                wasOpen = isOpen;
            });
            observer.observe(popup, { attributes: true, attributeFilter: ['class'] });
        });
    }

    function showFieldError(field, message) {
        if (!field) return;
        var container = fieldContainer(field) || field.parentElement;
        var customSelect = field.closest('.wonom-select') || (container && container.querySelector('.wonom-select'));
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        if (customSelect) customSelect.classList.add('has-error');
        if (!container.querySelector('.wonom-field-error')) {
            var error = document.createElement('div');
            error.className = 'wonom-field-error';
            error.setAttribute('role', 'alert');
            error.textContent = message;
            container.appendChild(error);
        }
    }

    function focusFirstError(scope) {
        var field = scope.querySelector('.is-invalid');
        if (!field) return;
        var customTrigger = field.closest('.wonom-select') && field.closest('.wonom-select').querySelector('.wonom-select__trigger');
        (customTrigger || field).focus();
        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function ensureStatus(scope, button) {
        var status = scope.querySelector('.wonom-form-status');
        if (!status) {
            status = document.createElement('div');
            status.className = 'wonom-form-status';
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
            button.insertAdjacentElement('afterend', status);
        }
        return status;
    }

    function setStatus(scope, button, type, message) {
        var status = ensureStatus(scope, button);
        status.className = 'wonom-form-status' + (type ? ' is-' + type : '');
        status.textContent = message || '';
    }

    function validateBooking(form) {
        clearErrors(form);
        var errors = 0;
        var name = form.querySelector('[name="name"]');
        var phone = form.querySelector('[name="phone"]');
        var guests = form.querySelector('[name="guests"]');
        var date = form.querySelector('[name="date"]');
        var time = form.querySelector('[name="time"]');
        var bookingType = form.querySelector('[name="booking_type"]');
        var productId = form.querySelector('[name="product_id"]');
        var productName = form.querySelector('[name="product_name"]');
        var floor = form.querySelector('[data-booking-floor-select]');

        if (!isValidName(name.value)) { showFieldError(name, t('invalid_name', 'Vui lòng nhập họ tên hợp lệ.')); errors++; }
        if (!isValidPhone(phone.value)) { showFieldError(phone, t('invalid_phone', 'Vui lòng nhập đúng số điện thoại Việt Nam.')); errors++; }
        if (!isValidGuests(guests.value)) { showFieldError(guests, t('required_guests', 'Vui lòng chọn số người.')); errors++; }
        if (!isValidDate(date.value)) { showFieldError(date, t('invalid_date', 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.')); errors++; }
        if (!isValidTime(time.value)) { showFieldError(time, t('invalid_time_short', 'Nhập thời gian theo định dạng HH:mm.')); errors++; }
        if (bookingType && 'general' === bookingType.value && floor && !floor.value) {
            showFieldError(floor, t('required_floor', 'Vui lòng chọn tầng bạn muốn đến.')); errors++;
        }
        if (productId && productId.value && productName && !productName.value.trim()) {
            showFieldError(productName, 'Không xác định được tên sản phẩm workshop. Vui lòng chọn lại sản phẩm.'); errors++;
        }
        if (errors) focusFirstError(form);
        return 0 === errors;
    }

    function validateTour(form) {
        clearErrors(form);
        var errors = 0;
        var name = form.querySelector('[name="name"]');
        var phone = form.querySelector('[name="phone"]');
        var guests = form.querySelector('[name="guests"]');
        var date = form.querySelector('[name="date"]');
        var time = form.querySelector('[name="time"]');
        if (!isValidName(name.value)) { showFieldError(name, t('invalid_name', 'Vui lòng nhập họ tên hợp lệ.')); errors++; }
        if (!isValidPhone(phone.value)) { showFieldError(phone, t('invalid_phone', 'Vui lòng nhập đúng số điện thoại Việt Nam.')); errors++; }
        if (!isValidGuests(guests.value)) { showFieldError(guests, t('required_guests', 'Vui lòng chọn số người.')); errors++; }
        if (!isValidDate(date.value)) { showFieldError(date, t('invalid_date', 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.')); errors++; }
        if (!isValidTime(time.value)) { showFieldError(time, t('invalid_time_short', 'Nhập thời gian theo định dạng HH:mm.')); errors++; }
        if (errors) focusFirstError(form);
        return 0 === errors;
    }

    function submitData(scope, button, data, onSuccess) {
        if (submitting.has(scope)) return;
        submitting.add(scope);
        var originalText = button.textContent;
        button.disabled = true;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');
        button.textContent = t('submitting', 'ĐANG GỬI...');
        setStatus(scope, button, '', '');
        data.append('action', 'wonom_submit_form');
        data.append('nonce', config.submitNonce || '');
        data.append('lang', config.language || 'vi');
        var honeypot = scope.querySelector('[name="website"]');
        data.set('website', honeypot ? honeypot.value : '');
        data.set('form_elapsed_ms', String(Math.max(0, Date.now() - pageStartedAt)));

        fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json().then(function (body) {
                return { ok: response.ok, body: body };
            });
        }).then(function (result) {
            if (!result.ok || !result.body.success) {
                var payload = result.body && result.body.data ? result.body.data : {};
                if (payload.fields) {
                    Object.keys(payload.fields).forEach(function (name) {
                        var field = scope.querySelector('[name="' + name + '"]');
                        if ('floor' === name) field = scope.querySelector('[data-booking-floor-select]');
                        if ('consent' === name) field = scope.querySelector('.popup_member_checkbox');
                        showFieldError(field, payload.fields[name]);
                    });
                    focusFirstError(scope);
                }
                throw new Error(payload.message || t('cannot_send', 'Không thể gửi yêu cầu. Vui lòng thử lại.'));
            }
            setStatus(scope, button, 'success', result.body.data.message);
            if (onSuccess) onSuccess();
        }).catch(function (error) {
            setStatus(scope, button, 'error', error.message || t('cannot_connect', 'Không thể kết nối máy chủ. Vui lòng thử lại.'));
        }).finally(function () {
            submitting.delete(scope);
            button.disabled = false;
            button.classList.remove('is-loading');
            button.removeAttribute('aria-busy');
            button.textContent = originalText;
        });
    }

    function bindForm(form, type, validator) {
        if (!form) return;
        var button = form.querySelector('[type="submit"]');
        form.setAttribute('novalidate', 'novalidate');
        form.addEventListener('input', function (event) {
            var container = fieldContainer(event.target);
            event.target.classList.remove('is-invalid');
            event.target.removeAttribute('aria-invalid');
            if (container) {
                var error = container.querySelector('.wonom-field-error');
                if (error) error.remove();
                var customSelect = container.querySelector('.wonom-select');
                if (customSelect) customSelect.classList.remove('has-error');
            }
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!validator(form)) {
                setStatus(form, button, 'error', t('check_fields', 'Vui lòng kiểm tra lại các thông tin đã nhập.'));
                return;
            }
            var data = new FormData(form);
            data.append('form_type', type);
            submitData(form, button, data, function () {
                form.reset();
                clearErrors(form);
            });
        });
    }

    function bindMembership() {
        var popup = document.querySelector('.popup_member');
        if (!popup) return;

        var title = popup.querySelector('[data-member-title]');
        var registerForm = popup.querySelector('.popup_member_register');
        var lookupForm = popup.querySelector('.popup_member_lookup');
        var verifyForm = popup.querySelector('.popup_member_verify');
        var otpInputs = Array.prototype.slice.call(popup.querySelectorAll('[data-member-otp] input'));
        var confirmButton = popup.querySelector('[data-member-confirm]');
        var resendButton = popup.querySelector('[data-member-resend]');
        var countdown = popup.querySelector('[data-member-countdown]');
        var maskedEmail = popup.querySelector('[data-member-masked-email]');
        var toast = popup.querySelector('.popup_member_toast');
        var logoutButton = popup.querySelector('[data-member-logout]');
        var cardSessionKey = 'wonomMembershipCardSession';
        var cardSessionDuration = 30 * 60 * 1000;
        var session = { token: '', mode: 'register', email: '', timer: null, card: null, expiresAt: 0, logoutTimer: null };
        var titles = {
            register: 'ĐĂNG KÝ THÀNH VIÊN',
            verify: 'XÁC MINH THÀNH VIÊN',
            lookup: 'XEM THẺ THÀNH VIÊN',
            card: 'NHẬN THẺ THÀNH VIÊN'
        };

        function setMemberState(state) {
            popup.setAttribute('data-member-state', state);
            popup.querySelectorAll('[data-member-panel]').forEach(function (panel) {
                panel.classList.toggle('is-active', panel.getAttribute('data-member-panel') === state);
            });
            title.textContent = titles[state] || titles.register;
            logoutButton.hidden = 'card' !== state;
            resetValidationState(popup);
            if ('verify' !== state && session.timer) {
                window.clearInterval(session.timer);
                session.timer = null;
            }
        }

        function renderMemberCard(card) {
            if (!card) return;
            popup.querySelector('[data-member-name]').textContent = String(card.name || '').toLocaleUpperCase('vi-VN');
            popup.querySelector('[data-member-number]').textContent = card.number || '';
        }

        function clearCardSession(showRegister) {
            session.card = null;
            session.expiresAt = 0;
            if (session.logoutTimer) window.clearTimeout(session.logoutTimer);
            session.logoutTimer = null;
            try { window.sessionStorage.removeItem(cardSessionKey); } catch (error) { /* Storage may be disabled. */ }
            if (showRegister) {
                registerForm.reset();
                lookupForm.reset();
                session.email = '';
                setMemberState('register');
            }
        }

        function scheduleCardLogout() {
            if (session.logoutTimer) window.clearTimeout(session.logoutTimer);
            var delay = session.expiresAt - Date.now();
            if (delay <= 0) return clearCardSession(true);
            session.logoutTimer = window.setTimeout(function () {
                clearCardSession(true);
            }, delay);
        }

        function saveCardSession(card) {
            session.card = card;
            session.expiresAt = Date.now() + cardSessionDuration;
            try {
                window.sessionStorage.setItem(cardSessionKey, JSON.stringify({
                    card: card,
                    expiresAt: session.expiresAt
                }));
            } catch (error) { /* Keep the in-memory session when storage is unavailable. */ }
            scheduleCardLogout();
        }

        function restoreCardSession() {
            try {
                var saved = JSON.parse(window.sessionStorage.getItem(cardSessionKey) || 'null');
                if (!saved || !saved.card || Number(saved.expiresAt) <= Date.now()) {
                    clearCardSession(false);
                    return false;
                }
                session.card = saved.card;
                session.expiresAt = Number(saved.expiresAt);
                renderMemberCard(session.card);
                scheduleCardLogout();
                return true;
            } catch (error) {
                clearCardSession(false);
                return false;
            }
        }

        function setButtonLoading(button, loading, text) {
            if (!button) return;
            if (loading) {
                button.dataset.originalText = button.textContent;
                button.textContent = text || 'ĐANG GỬI...';
                button.disabled = true;
                button.classList.add('is-loading');
            } else {
                button.textContent = button.dataset.originalText || button.textContent;
                button.classList.remove('is-loading');
                button.disabled = false;
            }
        }

        function membershipRequest(data) {
            data.append('action', 'wonom_membership');
            data.append('nonce', config.submitNonce || '');
            data.append('lang', config.language || 'vi');
            return fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST', credentials: 'same-origin', body: data
            }).then(function (response) {
                return response.json().then(function (body) {
                    if (!response.ok || !body.success) {
                        var payload = body && body.data ? body.data : {};
                        var error = new Error(payload.message || 'Không thể xử lý yêu cầu. Vui lòng thử lại.');
                        error.fields = payload.fields || {};
                        throw error;
                    }
                    return body.data;
                });
            });
        }

        function showRequestError(form, button, error) {
            Object.keys(error.fields || {}).forEach(function (name) {
                var field = form.querySelector('[name="' + name + '"]');
                showFieldError(field, error.fields[name]);
            });
            setStatus(form, button, 'error', error.message);
            focusFirstError(form);
        }

        function startCountdown(seconds) {
            if (session.timer) window.clearInterval(session.timer);
            var remaining = Number(seconds) || 60;
            resendButton.disabled = true;
            resendButton.innerHTML = 'Gửi lại sau <span data-member-countdown>' + remaining + '</span> giây';
            countdown = resendButton.querySelector('[data-member-countdown]');
            session.timer = window.setInterval(function () {
                remaining -= 1;
                if (countdown) countdown.textContent = String(Math.max(0, remaining));
                if (remaining <= 0) {
                    window.clearInterval(session.timer);
                    session.timer = null;
                    resendButton.disabled = false;
                    resendButton.textContent = 'Gửi mã';
                }
            }, 1000);
        }

        function openVerification(payload) {
            session.token = payload.token;
            session.mode = payload.mode;
            titles.verify = session.mode === 'lookup' ? 'XÁC NHẬN EMAIL' : 'XÁC MINH THÀNH VIÊN';
            maskedEmail.textContent = payload.masked_email;
            otpInputs.forEach(function (input) { input.value = ''; });
            confirmButton.disabled = true;
            confirmButton.textContent = session.mode === 'lookup' ? 'XÁC NHẬN & XEM THẺ' : 'XÁC NHẬN & HOÀN TẤT';
            setMemberState('verify');
            startCountdown(payload.resend_after);
            otpInputs[0].focus();
        }

        function validateEmail(field) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(field.value || '').trim());
        }

        registerForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearErrors(registerForm);
            var name = registerForm.elements.name;
            var phone = registerForm.elements.phone;
            var email = registerForm.elements.email;
            var consent = registerForm.elements.consent;
            var button = registerForm.querySelector('[type="submit"]');
            var errors = 0;
            if (!isValidName(name.value)) { showFieldError(name, t('invalid_name', 'Vui lòng nhập họ tên hợp lệ.')); errors++; }
            if (!isValidPhone(phone.value)) { showFieldError(phone, t('invalid_phone', 'Vui lòng nhập đúng số điện thoại Việt Nam.')); errors++; }
            if (!validateEmail(email)) { showFieldError(email, 'Vui lòng nhập địa chỉ email hợp lệ.'); errors++; }
            if (!consent.checked) { showFieldError(consent, t('required_consent', 'Bạn cần đồng ý điều khoản để đăng ký.')); errors++; }
            if (errors) return focusFirstError(registerForm);

            var data = new FormData(registerForm);
            data.append('operation', 'begin');
            data.append('mode', 'register');
            session.email = email.value.trim();
            setButtonLoading(button, true, 'ĐANG GỬI MÃ...');
            membershipRequest(data).then(openVerification).catch(function (error) {
                showRequestError(registerForm, button, error);
            }).finally(function () { setButtonLoading(button, false); });
        });

        lookupForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearErrors(lookupForm);
            var email = lookupForm.elements.email;
            var button = lookupForm.querySelector('[type="submit"]');
            if (!validateEmail(email)) {
                showFieldError(email, 'Vui lòng nhập địa chỉ email hợp lệ.');
                return focusFirstError(lookupForm);
            }
            var data = new FormData(lookupForm);
            data.append('operation', 'begin');
            data.append('mode', 'lookup');
            session.email = email.value.trim();
            setButtonLoading(button, true, 'ĐANG GỬI MÃ...');
            membershipRequest(data).then(openVerification).catch(function (error) {
                showRequestError(lookupForm, button, error);
            }).finally(function () { setButtonLoading(button, false); });
        });

        function updateOtpButton() {
            confirmButton.disabled = otpInputs.some(function (input) { return !/^\d$/.test(input.value); });
        }

        otpInputs.forEach(function (input, index) {
            input.addEventListener('input', function () {
                input.value = input.value.replace(/\D/g, '').slice(-1);
                if (input.value && otpInputs[index + 1]) otpInputs[index + 1].focus();
                updateOtpButton();
            });
            input.addEventListener('keydown', function (event) {
                if ('Backspace' === event.key && !input.value && otpInputs[index - 1]) otpInputs[index - 1].focus();
            });
            input.addEventListener('paste', function (event) {
                var digits = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                if (!digits) return;
                event.preventDefault();
                digits.split('').forEach(function (digit, digitIndex) {
                    if (otpInputs[digitIndex]) otpInputs[digitIndex].value = digit;
                });
                otpInputs[Math.min(digits.length, 6) - 1].focus();
                updateOtpButton();
            });
        });

        verifyForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var code = otpInputs.map(function (input) { return input.value; }).join('');
            if (!/^\d{6}$/.test(code)) return;
            var data = new FormData();
            data.append('operation', 'verify');
            data.append('token', session.token);
            data.append('code', code);
            setButtonLoading(confirmButton, true, 'ĐANG XÁC NHẬN...');
            membershipRequest(data).then(function (payload) {
                saveCardSession(payload.card);
                titles.card = payload.mode === 'lookup' ? 'THẺ THÀNH VIÊN' : 'NHẬN THẺ THÀNH VIÊN';
                renderMemberCard(payload.card);
                setMemberState('card');
                if ('register' === payload.mode) {
                    toast.classList.add('is-visible');
                    window.setTimeout(function () { toast.classList.remove('is-visible'); }, 5000);
                }
            }).catch(function (error) {
                setStatus(verifyForm, confirmButton, 'error', error.message);
                otpInputs.forEach(function (input) { input.value = ''; });
                updateOtpButton();
                otpInputs[0].focus();
            }).finally(function () { setButtonLoading(confirmButton, false); updateOtpButton(); });
        });

        resendButton.addEventListener('click', function () {
            var data = new FormData();
            data.append('operation', 'resend');
            data.append('token', session.token);
            resendButton.disabled = true;
            membershipRequest(data).then(function (payload) {
                setStatus(verifyForm, confirmButton, 'success', payload.message);
                startCountdown(payload.resend_after);
            }).catch(function (error) {
                setStatus(verifyForm, confirmButton, 'error', error.message);
                resendButton.disabled = false;
            });
        });

        popup.querySelectorAll('[data-member-go]').forEach(function (button) {
            button.addEventListener('click', function () {
                var next = button.getAttribute('data-member-go');
                if ('register' === next && session.email) registerForm.elements.email.value = session.email;
                setMemberState(next);
            });
        });
        document.querySelectorAll('.header_button_member').forEach(function (button) {
            button.addEventListener('click', function () {
                if (session.card && session.expiresAt > Date.now()) {
                    renderMemberCard(session.card);
                    setMemberState('card');
                    return;
                }
                clearCardSession(true);
            });
        });
        logoutButton.addEventListener('click', function () {
            clearCardSession(true);
        });
        toast.querySelector('button').addEventListener('click', function () { toast.classList.remove('is-visible'); });

        restoreCardSession();

        popup.querySelector('[data-member-download]').addEventListener('click', function () {
            if (!session.card) return;
            var button = this;
            var cardImage = popup.querySelector('[data-member-card-image]');
            var cardPanel = popup.querySelector('.popup_member_card_panel');
            var safeName = String(session.card.name || '').trim().toLocaleUpperCase('vi-VN');
            var safeNumber = String(session.card.number || '').trim().toUpperCase();
            var imageSource = cardImage.currentSrc || cardImage.getAttribute('src') || cardImage.getAttribute('data-wonom-src');

            function fitCanvasText(context, textValue, maxWidth, startSize, minSize, weight) {
                var size = startSize;
                do {
                    context.font = weight + ' ' + size + 'px "Mona Sans", Arial, sans-serif';
                    if (context.measureText(textValue).width <= maxWidth) break;
                    size -= 1;
                } while (size > minSize);
            }

            function createCardDownload(image) {
                var width = image.naturalWidth || 766;
                var height = image.naturalHeight || 440;
                var scale = width / 766;
                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');
                canvas.width = width;
                canvas.height = height;
                context.drawImage(image, 0, 0, width, height);

                context.textBaseline = 'alphabetic';
                context.fillStyle = '#460700';
                fitCanvasText(context, safeName, 628 * scale, 34 * scale, 22 * scale, '800');
                context.fillText(safeName, 70 * scale, 316 * scale);

                context.fillStyle = '#ed1c24';
                context.shadowColor = '#72140d';
                context.shadowBlur = 0;
                context.shadowOffsetX = 5 * scale;
                context.shadowOffsetY = 6 * scale;
                fitCanvasText(context, safeNumber, 628 * scale, 66 * scale, 42 * scale, '900');
                context.strokeStyle = '#FFF6DB';
                context.lineWidth = 4 * scale;
                context.lineJoin = 'round';
                context.strokeText(safeNumber, 70 * scale, 405 * scale);
                context.shadowColor = 'transparent';
                context.fillText(safeNumber, 70 * scale, 405 * scale);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        setStatus(cardPanel, button, 'error', 'Không thể tạo ảnh thẻ. Vui lòng thử lại.');
                        setButtonLoading(button, false);
                        return;
                    }
                    var url = URL.createObjectURL(blob);
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'the-thanh-vien-' + safeNumber.replace(/[^A-Z0-9-]/g, '') + '.png';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
                    setButtonLoading(button, false);
                }, 'image/png', 1);
            }

            if (!imageSource) {
                setStatus(cardPanel, button, 'error', 'Không tìm thấy ảnh mẫu thẻ thành viên.');
                return;
            }

            setButtonLoading(button, true, 'ĐANG TẠO ẢNH THẺ...');
            var exportImage = new Image();
            exportImage.onload = function () {
                var fontsReady = document.fonts && document.fonts.load
                    ? Promise.all([
                        document.fonts.load('800 34px "Mona Sans"'),
                        document.fonts.load('900 66px "Mona Sans"')
                    ])
                    : Promise.resolve();
                fontsReady
                    .then(function () { createCardDownload(exportImage); })
                    .catch(function () { createCardDownload(exportImage); });
            };
            exportImage.onerror = function () {
                setStatus(cardPanel, button, 'error', 'Không tải được ảnh mẫu thẻ thành viên.');
                setButtonLoading(button, false);
            };
            exportImage.src = imageSource;
        });

        setMemberState('register');
    }

    function formatDateForDisplay(value) {
        var match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
        return match ? [match[3], match[2], match[1]].join('/') : '';
    }

    function enhanceMobileDateInputs() {
        document.querySelectorAll('.popup_form_input[type="date"]').forEach(function (input) {
            var wrap = input.closest('.popup_form_input_wrap');
            if (!wrap || wrap.classList.contains('has-mobile-date-display')) return;

            var display = document.createElement('span');
            display.className = 'popup_form_date_display is-placeholder';
            display.setAttribute('aria-hidden', 'true');
            input.insertAdjacentElement('afterend', display);
            wrap.classList.add('has-mobile-date-display');

            var updateDisplay = function () {
                var formattedDate = formatDateForDisplay(input.value);
                display.textContent = formattedDate || 'dd/mm/yyyy';
                display.classList.toggle('is-placeholder', !formattedDate);
            };

            input.addEventListener('input', updateDisplay);
            input.addEventListener('change', updateDisplay);
            if (input.form) {
                input.form.addEventListener('reset', function () {
                    window.setTimeout(updateDisplay, 0);
                });
            }
            updateDisplay();
        });
    }

    function enhanceMobileTimeInputs() {
        document.querySelectorAll('.popup_form_input[type="time"]').forEach(function (input) {
            var wrap = input.closest('.popup_form_input_wrap');
            if (!wrap || wrap.classList.contains('has-mobile-time-display')) return;

            var display = document.createElement('span');
            display.className = 'popup_form_time_display is-placeholder';
            display.setAttribute('aria-hidden', 'true');
            input.insertAdjacentElement('afterend', display);
            wrap.classList.add('has-mobile-time-display');

            var updateDisplay = function () {
                display.textContent = input.value || '--:--';
                display.classList.toggle('is-placeholder', !input.value);
            };

            input.addEventListener('input', updateDisplay);
            input.addEventListener('change', updateDisplay);
            if (input.form) {
                input.form.addEventListener('reset', function () {
                    window.setTimeout(updateDisplay, 0);
                });
            }
            updateDisplay();
        });
    }

    function boot() {
        var today = new Date();
        var minimumDate = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0')
        ].join('-');
        document.querySelectorAll('.popup_form_input[type="date"]').forEach(function (input) {
            input.min = minimumDate;
        });
        enhanceMobileDateInputs();
        enhanceMobileTimeInputs();
        resetValidationWhenPopupOpens();
        bindForm(document.querySelector('.popup_form_booking_form'), 'booking', validateBooking);
        bindForm(document.querySelector('.tour_detail_form'), 'tour', validateTour);
        bindMembership();
    }

    if ('loading' === document.readyState) document.addEventListener('DOMContentLoaded', boot);
    else boot();
}());
