(function () {
    'use strict';

    var config = window.wonomHome || {};
    var submitting = new WeakSet();

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

    function isValidDate(value) {
        var match = String(value || '').match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!match) return false;
        var day = Number(match[1]);
        var month = Number(match[2]);
        var year = Number(match[3]);
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
        var floor = form.querySelector('[data-booking-floor-select]');

        if (!isValidName(name.value)) { showFieldError(name, 'Vui lòng nhập họ tên hợp lệ.'); errors++; }
        if (!isValidPhone(phone.value)) { showFieldError(phone, 'Vui lòng nhập đúng số điện thoại Việt Nam.'); errors++; }
        if (!guests.value) { showFieldError(guests, 'Vui lòng chọn số người.'); errors++; }
        if (!isValidDate(date.value)) { showFieldError(date, 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.'); errors++; }
        if (!isValidTime(time.value)) { showFieldError(time, 'Nhập thời gian theo định dạng HH:mm.'); errors++; }
        if (bookingType && 'general' === bookingType.value && floor && !floor.value) {
            showFieldError(floor, 'Vui lòng chọn tầng bạn muốn đến.'); errors++;
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
        if (!isValidName(name.value)) { showFieldError(name, 'Vui lòng nhập họ tên hợp lệ.'); errors++; }
        if (!isValidPhone(phone.value)) { showFieldError(phone, 'Vui lòng nhập đúng số điện thoại Việt Nam.'); errors++; }
        if (!guests.value) { showFieldError(guests, 'Vui lòng chọn số người.'); errors++; }
        if (!isValidDate(date.value)) { showFieldError(date, 'Vui lòng chọn ngày hợp lệ, không sớm hơn hôm nay.'); errors++; }
        if (!isValidTime(time.value)) { showFieldError(time, 'Nhập thời gian theo định dạng HH:mm.'); errors++; }
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
        button.textContent = 'ĐANG GỬI...';
        setStatus(scope, button, '', '');
        data.append('action', 'wonom_submit_form');
        data.append('nonce', config.submitNonce || '');
        data.append('website', '');

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
                throw new Error(payload.message || 'Không thể gửi yêu cầu. Vui lòng thử lại.');
            }
            setStatus(scope, button, 'success', result.body.data.message);
            if (onSuccess) onSuccess();
        }).catch(function (error) {
            setStatus(scope, button, 'error', error.message || 'Không thể kết nối máy chủ. Vui lòng thử lại.');
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
                setStatus(form, button, 'error', 'Vui lòng kiểm tra lại các thông tin đã nhập.');
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
        var scope = document.querySelector('.popup_member_form');
        if (!scope) return;
        var inputs = scope.querySelectorAll('.popup_form_input');
        var name = inputs[0];
        var phone = inputs[1];
        var consent = scope.querySelector('.popup_member_checkbox');
        var button = scope.querySelector('.popup_member_btn');
        if (!name || !phone || !consent || !button) return;
        name.name = 'name';
        name.autocomplete = 'name';
        phone.name = 'phone';
        phone.type = 'tel';
        phone.autocomplete = 'tel';

        button.addEventListener('click', function (event) {
            event.preventDefault();
            clearErrors(scope);
            var errors = 0;
            if (!isValidName(name.value)) { showFieldError(name, 'Vui lòng nhập họ tên hợp lệ.'); errors++; }
            if (!isValidPhone(phone.value)) { showFieldError(phone, 'Vui lòng nhập đúng số điện thoại Việt Nam.'); errors++; }
            if (!consent.checked) { showFieldError(consent, 'Bạn cần đồng ý điều khoản để đăng ký.'); errors++; }
            if (errors) {
                setStatus(scope, button, 'error', 'Vui lòng kiểm tra lại các thông tin đã nhập.');
                focusFirstError(scope);
                return;
            }
            var data = new FormData();
            data.append('form_type', 'membership');
            data.append('name', name.value.trim());
            data.append('phone', phone.value.trim());
            data.append('consent', '1');
            submitData(scope, button, data, function () {
                name.value = '';
                phone.value = '';
                consent.checked = false;
                clearErrors(scope);
            });
        });
        scope.addEventListener('keydown', function (event) {
            if ('Enter' === event.key && 'BUTTON' !== event.target.tagName) {
                event.preventDefault();
                button.click();
            }
        });
    }

    function boot() {
        bindForm(document.querySelector('.popup_form_booking_form'), 'booking', validateBooking);
        bindForm(document.querySelector('.tour_detail_form'), 'tour', validateTour);
        bindMembership();
    }

    if ('loading' === document.readyState) document.addEventListener('DOMContentLoaded', boot);
    else boot();
}());
