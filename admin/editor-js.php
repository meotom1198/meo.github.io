<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php $content = !empty($post) ? $post : $page; if ($options->markdown): ?>
<script src="<?php $options->adminStaticUrl('js', 'hyperdown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'pagedown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'paste.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
<script>
$(document).ready(function () {
    var textarea = $('#text'),
        isFullScreen = false,
        toolbar = $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent()),
        preview = $('<div id="wmd-preview" class="wmd-hidetab" />').insertAfter('.editor');

    var options = {}, isMarkdown = <?php echo intval($content->isMarkdown || !$content->have()); ?>;

    options.strings = {
        bold: '<?php _e('In đậm'); ?> <strong> Ctrl+B',
        boldexample: '<?php _e('Chữ in đậm'); ?>',
            
        italic: '<?php _e('In nghiêng'); ?> <em> Ctrl+I',
        italicexample: '<?php _e('Chữ in nghiêng'); ?>',

        link: '<?php _e('Liên kết'); ?> <a> Ctrl+L',
        linkdescription: '<?php _e('Vui lòng nhập mô tả liên kết'); ?>',

        quote:  '<?php _e('Trích dẫn'); ?> <blockquote> Ctrl+Q',
        quoteexample: '<?php _e('Văn bản được trích dẫn'); ?>',

        code: '<?php _e('Mã'); ?> <pre><code> Ctrl+K',
        codeexample: '<?php _e('Vui lòng nhập mã'); ?>',

        image: '<?php _e('Hình ảnh'); ?> <img> Ctrl+G',
        imagedescription: '<?php _e('Vui lòng nhập mô tả hình ảnh'); ?>',

        olist: '<?php _e('Danh sách các số'); ?> <ol> Ctrl+O',
        ulist: '<?php _e('Danh sách bình thường'); ?> <ul> Ctrl+U',
        litem: '<?php _e('Mục danh sách'); ?>',

        heading: '<?php _e('Thẻ H'); ?> <h1>/<h2> Ctrl+H',
        headingexample: '<?php _e('Văn bản thẻ H'); ?>',

        hr: '<?php _e('Vạch chia'); ?> <hr> Ctrl+R',
        more: '<?php _e('Tóm tắt đường phân chia'); ?> <!--more--> Ctrl+M',

        undo: '<?php _e('Làm lại'); ?> - Ctrl+Z',
        redo: '<?php _e('Làm lại'); ?> - Ctrl+Y',
        redomac: '<?php _e('Làm lại'); ?> - Ctrl+Shift+Z',

        fullscreen: '<?php _e('Toàn màn hình'); ?> - Ctrl+J',
        exitFullscreen: '<?php _e('Thoát toàn màn hình'); ?> - Ctrl+E',
        fullscreenUnsupport: '<?php _e('Trình duyệt này không hỗ trợ thao tác toàn màn hình'); ?>',

        imagedialog: '<p><b><?php _e('Chèn ảnh'); ?></b></p><p><?php _e('Vui lòng nhập URL của hình ảnh từ xa sẽ được chèn vào hộp nhập bên dưới'); ?></p><p><?php _e('Bạn cũng có thể sử dụng tính năng đính kèm để chèn hình ảnh cục bộ đã tải lên'); ?></p>',
        linkdialog: '<p><b><?php _e('Chèn liên kết'); ?></b></p><p><?php _e('Vui lòng nhập địa chỉ liên kết cần chèn vào ô nhập bên dưới'); ?></p>',

        ok: '<?php _e('Ok'); ?>',
        cancel: '<?php _e('Hủy bỏ'); ?>',

        help: '<?php _e('Trợ giúp cú pháp Markdown'); ?>'
    };

    var converter = new HyperDown(),
        editor = new Markdown.Editor(converter, '', options);

    // 自动跟随
    converter.enableHtml(true);
    converter.enableLine(true);
    reloadScroll = scrollableEditor(textarea, preview);

    // 修正白名单
    converter.hook('makeHtml', function (html) {
        html = html.replace('<p><!--more--></p>', '<!--more-->');
        
        if (html.indexOf('<!--more-->') > 0) {
            var parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                summary = parts.shift(),
                details = parts.join('');

            html = '<div class="summary">' + summary + '</div>'
                + '<div class="details">' + details + '</div>';
        }

        // 替换block
        html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
            if (src[src.length - 1] == '/') {
                src = src.substring(0, src.length - 1);
            }

            return '<div class="embed"><strong>'
                + tag + '</strong> : ' + $.trim(src) + '</div>';
        });

        return DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
    });

    editor.hooks.chain('onPreviewRefresh', function () {
        var images = $('img', preview), count = images.length;

        if (count == 0) {
            reloadScroll(true);
        } else {
            images.bind('load error', function () {
                count --;

                if (count == 0) {
                    reloadScroll(true);
                }
            });
        }
    });

    <?php \Typecho\Plugin::factory('admin/editor-js.php')->markdownEditor($content); ?>

    var th = textarea.height(), ph = preview.height(),
        uploadBtn = $('<button type="button" id="btn-fullscreen-upload" class="btn btn-link">'
            + '<i class="i-upload"><?php _e('Phụ lục'); ?></i></button>')
            .prependTo('.submit .right')
            .click(function() {
                $('a', $('.typecho-option-tabs li').not('.active')).trigger('click');
                return false;
            });

    $('.typecho-option-tabs li').click(function () {
        uploadBtn.find('i').toggleClass('i-upload-active',
            $('#tab-files-btn', this).length > 0);
    });

    editor.hooks.chain('enterFakeFullScreen', function () {
        th = textarea.height();
        ph = preview.height();
        $(document.body).addClass('fullscreen');
        var h = $(window).height() - toolbar.outerHeight();
        
        textarea.css('height', h);
        preview.css('height', h);
        isFullScreen = true;
    });

    editor.hooks.chain('enterFullScreen', function () {
        $(document.body).addClass('fullscreen');
        
        var h = window.screen.height - toolbar.outerHeight();
        textarea.css('height', h);
        preview.css('height', h);
        isFullScreen = true;
    });

    editor.hooks.chain('exitFullScreen', function () {
        $(document.body).removeClass('fullscreen');
        textarea.height(th);
        preview.height(ph);
        isFullScreen = false;
    });

    editor.hooks.chain('commandExecuted', function () {
        textarea.trigger('input');
    });

    function initMarkdown() {
        editor.run();

        var imageButton = $('#wmd-image-button'),
            linkButton = $('#wmd-link-button');

        Typecho.insertFileToEditor = function (file, url, isImage) {
            var button = isImage ? imageButton : linkButton;

            options.strings[isImage ? 'imagename' : 'linkname'] = file;
            button.trigger('click');

            var checkDialog = setInterval(function () {
                if ($('.wmd-prompt-dialog').length > 0) {
                    $('.wmd-prompt-dialog input').val(url).select();
                    clearInterval(checkDialog);
                    checkDialog = null;
                }
            }, 10);
        };

        Typecho.uploadComplete = function (file) {
            Typecho.insertFileToEditor(file.title, file.url, file.isImage);
        };

        // 编辑预览切换
        var edittab = $('.editor').prepend('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('Đăng bài'); ?></a><a href="#wmd-preview"><?php _e('Xem trước'); ?></a></div>'),
            editarea = $(textarea.parent()).attr("id", "wmd-editarea");

        $(".wmd-edittab a").click(function() {
            $(".wmd-edittab a").removeClass('active');
            $(this).addClass("active");
            $("#wmd-editarea, #wmd-preview").addClass("wmd-hidetab");
        
            var selected_tab = $(this).attr("href"),
                selected_el = $(selected_tab).removeClass("wmd-hidetab");

            // 预览时隐藏编辑器按钮
            if (selected_tab == "#wmd-preview") {
                $("#wmd-button-row").addClass("wmd-visualhide");
            } else {
                $("#wmd-button-row").removeClass("wmd-visualhide");
            }

            // 预览和编辑窗口高度一致
            $("#wmd-preview").outerHeight($("#wmd-editarea").innerHeight());

            return false;
        });

        // 剪贴板复制图片
        textarea.pastableTextarea().on('pasteImage', function (e, data) {
            var name = data.name ? data.name.replace(/[\(\)\[\]\*#!]/g, '') : (new Date()).toISOString().replace(/\..+$/, '');
            if (!name.match(/\.[a-z0-9]{2,}$/i)) {
                var ext = data.blob.type.split('/').pop();
                name += '.' + ext;
            }

            Typecho.uploadFile(new File([data.blob], name), name);
        });
    }

    if (isMarkdown) {
        initMarkdown();
    } else {
        var notice = $('<div class="message notice"><?php _e('Bài đăng này không được tạo bằng cú pháp Markdown, tiếp tục chỉnh sửa nó bằng Markdown?'); ?> '
            + '<button class="btn btn-xs primary yes"><?php _e('Có'); ?></button> ' 
            + '<button class="btn btn-xs no"><?php _e('Không'); ?></button></div>')
            .hide().insertBefore(textarea).slideDown();

        $('.yes', notice).click(function () {
            notice.remove();
            $('<input type="hidden" name="markdown" value="1" />').appendTo('.submit');
            initMarkdown();
        });

        $('.no', notice).click(function () {
            notice.remove();
        });
    }
});
</script>
<?php endif; ?>

