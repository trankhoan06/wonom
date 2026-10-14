(function ($) {
    'use strict';

    if (typeof $.Redactor === 'undefined' || typeof window.TypeRocket === 'undefined') {
        return;
    }

    function escapeAttribute(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    $.Redactor.prototype.wonommedia = function () {
        return {
            init: function () {
                var button = this.button.add('wonommedia', 'Thêm hình ảnh');
                this.button.addCallback(button, this.wonommedia.open);
                this.wonommedia.frame = null;
                this.wonommedia.hasSavedSelection = false;
            },

            open: function () {
                var redactor = this;

                if (typeof wp === 'undefined' || !wp.media) {
                    return;
                }

                this.selection.save();
                this.wonommedia.hasSavedSelection = true;

                if (!this.wonommedia.frame) {
                    this.wonommedia.frame = wp.media({
                        title: 'Chọn hình ảnh',
                        button: { text: 'Chèn vào nội dung' },
                        library: { type: 'image' },
                        multiple: true
                    });

                    this.wonommedia.frame.on('select', function () {
                        var html = '';

                        redactor.wonommedia.frame.state().get('selection').each(function (model) {
                            var image = model.toJSON();
                            var source = image.sizes && image.sizes.large
                                ? image.sizes.large.url
                                : image.url;
                            var alt = image.alt || image.title || '';

                            if (source) {
                                html += '<p><img src="' + escapeAttribute(source) + '" alt="' + escapeAttribute(alt) + '"></p>';
                            }
                        });

                        redactor.selection.restore();
                        redactor.wonommedia.hasSavedSelection = false;

                        if (html) {
                            redactor.insert.html(html);
                            redactor.code.sync();
                        }
                    });

                    this.wonommedia.frame.on('close', function () {
                        if (redactor.wonommedia.hasSavedSelection) {
                            redactor.selection.restore();
                            redactor.wonommedia.hasSavedSelection = false;
                        }
                    });
                }

                this.wonommedia.frame.state().get('selection').reset();
                this.wonommedia.frame.open();
            }
        };
    };

    window.TypeRocket.redactorSettings = {
        formatting: ['p', 'h2', 'h3', 'h4', 'h5', 'blockquote'],
        buttons: [
            'formatting',
            'bold',
            'italic',
            'underline',
            'deleted',
            'unorderedlist',
            'orderedlist',
            'outdent',
            'indent',
            'link',
            'alignment',
            'horizontalrule',
            'html'
        ],
        plugins: ['wonommedia', 'fullscreen'],
        minHeight: 220
    };
}(jQuery));
