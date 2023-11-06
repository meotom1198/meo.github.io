<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li><a href="<?php $options->adminUrl('themes.php'); ?>"><?php _e('Giao diện có thể sử dụng'); ?></a></li>
                    <?php if (!defined('__TYPECHO_THEME_WRITEABLE__') || __TYPECHO_THEME_WRITEABLE__): ?>
                        <li><a href="<?php $options->adminUrl('theme-editor.php'); ?>"><?php _e('Chỉnh sửa giao diện'); ?></a></li>
                    <?php endif; ?>
                    <li class="current"><a
                            href="<?php $options->adminUrl('options-theme.php'); ?>"><?php _e('Cài đặt giao diện'); ?></a></li>
                </ul>
            </div>
            <div class="col-mb-12 col-tb-8 col-tb-offset-2" role="form">
                <?php \Widget\Themes\Config::alloc()->config()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
include 'footer.php';
?>
