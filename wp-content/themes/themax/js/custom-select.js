(function () {
    'use strict';

    var selectCounter = 0;

    function closeSelect(wrapper, restoreFocus) {
        if (!wrapper || !wrapper.classList.contains('is-open')) return;
        wrapper.classList.remove('is-open', 'is-up');
        var parent = wrapper.parentElement;
        if (parent) parent.classList.remove('wonom-select-parent-open');
        var trigger = wrapper.querySelector('.wonom-select__trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) trigger.focus();
        }
    }

    function closeAll(except) {
        document.querySelectorAll('.wonom-select.is-open').forEach(function (wrapper) {
            if (wrapper !== except) closeSelect(wrapper, false);
        });
    }

    function focusOption(wrapper, direction) {
        var options = Array.from(wrapper.querySelectorAll('.wonom-select__option:not([disabled])'));
        if (!options.length) return;
        var activeIndex = options.indexOf(document.activeElement);
        var selectedIndex = options.findIndex(function (option) {
            return option.classList.contains('is-selected');
        });
        var nextIndex;

        if ('first' === direction) nextIndex = selectedIndex >= 0 ? selectedIndex : 0;
        else if ('last' === direction) nextIndex = options.length - 1;
        else if ('next' === direction) nextIndex = activeIndex < 0 ? 0 : (activeIndex + 1) % options.length;
        else nextIndex = activeIndex < 0 ? options.length - 1 : (activeIndex - 1 + options.length) % options.length;

        options[nextIndex].focus();
    }

    function openSelect(wrapper) {
        if (!wrapper || wrapper.classList.contains('is-disabled')) return;
        closeAll(wrapper);
        wrapper.classList.add('is-open');
        if (wrapper.parentElement) wrapper.parentElement.classList.add('wonom-select-parent-open');
        wrapper.querySelector('.wonom-select__trigger').setAttribute('aria-expanded', 'true');
        wrapper.classList.remove('is-up');

        window.requestAnimationFrame(function () {
            var menu = wrapper.querySelector('.wonom-select__menu');
            var rect = wrapper.getBoundingClientRect();
            var menuHeight = Math.min(menu.scrollHeight, window.innerHeight * .42);
            var roomBelow = window.innerHeight - rect.bottom;
            var roomAbove = rect.top;
            wrapper.classList.toggle('is-up', roomBelow < menuHeight + 16 && roomAbove > roomBelow);
            focusOption(wrapper, 'first');
        });
    }

    function syncSelect(select, wrapper) {
        var selected = select.options[select.selectedIndex];
        var value = wrapper.querySelector('.wonom-select__value');
        var trigger = wrapper.querySelector('.wonom-select__trigger');
        var label = selected ? selected.textContent.trim() : '';
        value.textContent = label;
        trigger.classList.toggle('is-placeholder', !selected || '' === selected.value);
        trigger.disabled = select.disabled;
        wrapper.classList.toggle('is-disabled', select.disabled);
        wrapper.classList.remove('has-error');
        wrapper.querySelectorAll('.wonom-select__option').forEach(function (option) {
            var isSelected = Number(option.dataset.optionIndex) === select.selectedIndex;
            option.classList.toggle('is-selected', isSelected);
            option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });
    }

    function buildOptions(select, wrapper) {
        var menu = wrapper.querySelector('.wonom-select__menu');
        menu.replaceChildren();

        Array.from(select.children).forEach(function (child) {
            if ('OPTGROUP' === child.tagName) {
                var group = document.createElement('li');
                group.className = 'wonom-select__group';
                group.textContent = child.label;
                menu.appendChild(group);
                Array.from(child.children).forEach(function (option) {
                    appendOption(option);
                });
                return;
            }
            if ('OPTION' === child.tagName) appendOption(child);
        });

        function appendOption(option) {
            if (option.hidden || (option.disabled && '' === option.value)) return;
            var item = document.createElement('li');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'wonom-select__option';
            button.setAttribute('role', 'option');
            button.dataset.optionIndex = String(option.index);
            button.textContent = option.textContent.trim();
            button.disabled = option.disabled;
            item.appendChild(button);
            menu.appendChild(item);
        }
        syncSelect(select, wrapper);
    }

    function enhanceSelect(select) {
        if (!select || select.dataset.wonomSelectReady || select.multiple || select.size > 1) return;
        var originalStyle = window.getComputedStyle(select);
        select.dataset.wonomSelectReady = 'true';
        selectCounter += 1;

        var wrapper = document.createElement('div');
        var trigger = document.createElement('button');
        var value = document.createElement('span');
        var arrow = document.createElement('span');
        var menu = document.createElement('ul');
        var menuId = 'wonom-select-menu-' + selectCounter;

        wrapper.className = 'wonom-select';
        trigger.type = 'button';
        trigger.className = 'wonom-select__trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', menuId);
        value.className = 'wonom-select__value';
        arrow.className = 'wonom-select__arrow';
        arrow.setAttribute('aria-hidden', 'true');
        menu.className = 'wonom-select__menu';
        menu.id = menuId;
        menu.setAttribute('role', 'listbox');

        [
            'box-sizing', 'height', 'min-height',
            'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'border-top', 'border-right', 'border-bottom', 'border-left', 'border-radius',
            'background-color', 'background-image', 'box-shadow', 'color',
            'font-family', 'font-size', 'font-style', 'font-weight', 'line-height',
            'letter-spacing', 'text-transform'
        ].forEach(function (property) {
            trigger.style.setProperty(property, originalStyle.getPropertyValue(property));
        });

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        trigger.appendChild(value);
        trigger.appendChild(arrow);
        select.classList.add('wonom-select__native');

        Array.from(wrapper.parentNode.children).forEach(function (sibling) {
            if (sibling !== wrapper && (sibling.classList.contains('popup_form_input_icon') || sibling.classList.contains('select_icon'))) {
                sibling.classList.add('wonom-select-icon-hidden');
            }
        });

        buildOptions(select, wrapper);

        trigger.addEventListener('click', function () {
            if (wrapper.classList.contains('is-open')) closeSelect(wrapper, false);
            else openSelect(wrapper);
        });
        trigger.addEventListener('keydown', function (event) {
            if ('ArrowDown' === event.key || 'ArrowUp' === event.key) {
                event.preventDefault();
                openSelect(wrapper);
                window.requestAnimationFrame(function () {
                    focusOption(wrapper, 'ArrowDown' === event.key ? 'first' : 'last');
                });
            }
        });
        menu.addEventListener('click', function (event) {
            var option = event.target.closest('.wonom-select__option');
            if (!option || option.disabled) return;
            select.selectedIndex = Number(option.dataset.optionIndex);
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncSelect(select, wrapper);
            closeSelect(wrapper, true);
        });
        menu.addEventListener('keydown', function (event) {
            if ('ArrowDown' === event.key || 'ArrowUp' === event.key) {
                event.preventDefault();
                focusOption(wrapper, 'ArrowDown' === event.key ? 'next' : 'previous');
            } else if ('Home' === event.key || 'End' === event.key) {
                event.preventDefault();
                focusOption(wrapper, 'Home' === event.key ? 'first' : 'last');
            } else if ('Escape' === event.key) {
                event.preventDefault();
                closeSelect(wrapper, true);
            } else if ('Tab' === event.key) {
                closeSelect(wrapper, false);
            }
        });
        select.addEventListener('change', function () {
            syncSelect(select, wrapper);
        });
        select.addEventListener('invalid', function () {
            wrapper.classList.add('has-error');
            trigger.focus();
        });
        if (select.form) {
            select.form.addEventListener('reset', function () {
                window.setTimeout(function () { syncSelect(select, wrapper); }, 0);
            });
        }

        new MutationObserver(function () {
            buildOptions(select, wrapper);
        }).observe(select, { childList: true, subtree: true });
    }

    function init(root) {
        if (root.matches && root.matches('select')) enhanceSelect(root);
        if (root.querySelectorAll) root.querySelectorAll('select').forEach(enhanceSelect);
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.wonom-select')) closeAll();
    });
    document.addEventListener('keydown', function (event) {
        if ('Escape' === event.key) closeAll();
    });

    function boot() {
        init(document);
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (1 === node.nodeType) init(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if ('loading' === document.readyState) document.addEventListener('DOMContentLoaded', boot);
    else boot();
}());
