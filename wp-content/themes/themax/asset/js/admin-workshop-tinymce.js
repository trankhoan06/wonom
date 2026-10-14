(function () {
    'use strict';

    if (typeof tinymce === 'undefined') {
        return;
    }

    function escapeAttribute(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    tinymce.PluginManager.add('wonom_media', function (editor) {
        var mediaFrame;
        var selectionBookmark;

        editor.addButton('wonom_media', {
            title: 'Thêm hình ảnh',
            icon: 'image',
            onclick: function () {
                if (typeof wp === 'undefined' || !wp.media) {
                    return;
                }

                selectionBookmark = editor.selection.getBookmark(2, true);

                if (!mediaFrame) {
                    mediaFrame = wp.media({
                        title: 'Chọn hình ảnh',
                        button: { text: 'Chèn vào nội dung' },
                        library: { type: 'image' },
                        multiple: true
                    });

                    mediaFrame.on('select', function () {
                        var html = '';

                        mediaFrame.state().get('selection').each(function (model) {
                            var image = model.toJSON();
                            var source = image.sizes && image.sizes.large
                                ? image.sizes.large.url
                                : image.url;
                            var alt = image.alt || image.title || '';

                            if (source) {
                                html += '<p><img src="' + escapeAttribute(source) + '" alt="' + escapeAttribute(alt) + '"></p>';
                            }
                        });

                        if (html) {
                            editor.focus();
                            editor.selection.moveToBookmark(selectionBookmark);
                            editor.insertContent(html);
                        }
                    });
                }

                mediaFrame.state().get('selection').reset();
                mediaFrame.open();
            }
        });
    });
}());
