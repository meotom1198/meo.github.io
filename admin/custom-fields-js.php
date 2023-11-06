<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script>
$(document).ready(function () {
    // 自定义字段
    $('#custom-field-expand').click(function() {
        var btn = $('i', this);
        if (btn.hasClass('i-caret-right')) {
            btn.removeClass('i-caret-right').addClass('i-caret-down');
        } else {
            btn.removeClass('i-caret-down').addClass('i-caret-right');
        }
        $(this).parent().toggleClass('fold');
        return false;
    });

    function attachDeleteEvent (el) {
        $('button.btn-xs', el).click(function () {
            if (confirm('<?php _e('Bạn có chắc chắn muốn xóa trường này không?'); ?>')) {
                $(this).parents('tr').fadeOut(function () {
                    $(this).remove();
                });

                $(this).parents('form').trigger('field');
            }
        });
    }

    $('#custom-field table tbody tr').each(function () {
        attachDeleteEvent(this);
    });

    $('#custom-field button.operate-add').click(function () {
        var html = '<tr><td><input type="text" name="fieldNames[]" placeholder="<?php _e('Tên trường'); ?>" class="text-s w-100"></td>'
                + '<td><select name="fieldTypes[]" id="">'
                + '<option value="str"><?php _e('Nhân vật'); ?></option>'
                + '<option value="int"><?php _e('Số nguyên'); ?></option>'
                + '<option value="float"><?php _e('Số thập phân'); ?></option>'
                + '</select></td>'
                + '<td><textarea name="fieldValues[]" placeholder="<?php _e('Giá trị trường'); ?>" class="text-s w-100" rows="2"></textarea></td>'
                + '<td><button type="button" class="btn btn-xs"><?php _e('Xóa bỏ'); ?></button></td></tr>',
            el = $(html).hide().appendTo('#custom-field table tbody').fadeIn();

            $(':input', el).bind('input change', function () {
                $(this).parents('form').trigger('field');
            });

        attachDeleteEvent(el);
    });
});
</script>
