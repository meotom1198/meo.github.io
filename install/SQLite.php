<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $defaultDir = __TYPECHO_ROOT_DIR__ . '/usr/' . uniqid() . '.db'; ?>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbFile"><?php _e('Đường dẫn tệp cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbFile" id="dbFile" value="<?php echo $defaultDir; ?>"/>
        <p class="description"><?php _e('"%s" là địa chỉ chúng tôi tự động tạo cho bạn', $defaultDir); ?></p>
    </li>
</ul>
