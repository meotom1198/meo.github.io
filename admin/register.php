<?php
include 'common.php';

if ($user->hasLogin() || !$options->allowRegister) {
    $response->redirect($options->siteUrl);
}
$rememberName = htmlspecialchars(\Typecho\Cookie::get('__typecho_remember_name'));
$rememberMail = htmlspecialchars(\Typecho\Cookie::get('__typecho_remember_mail'));
\Typecho\Cookie::delete('__typecho_remember_name');
\Typecho\Cookie::delete('__typecho_remember_mail');

$bodyClass = 'body-100';

include 'header.php';
?>
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1><a href="http://typecho.org" class="i-logo">Typecho Việt Hóa</a></h1>
        <form action="<?php $options->registerAction(); ?>" method="post" name="register" role="form">
            <p>
                <label for="name" class="sr-only"><?php _e('Tên tài khoản'); ?></label>
                <input type="text" id="name" name="name" placeholder="<?php _e('Tên tài khoản'); ?>" value="<?php echo $rememberName; ?>" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="mail" class="sr-only"><?php _e('Email'); ?></label>
                <input type="email" id="mail" name="mail" placeholder="<?php _e('Email'); ?>" value="<?php echo $rememberMail; ?>" class="text-l w-100" />
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary"><?php _e('Đăng ký'); ?></button>
            </p>
        </form>
        
        <p class="more-link">
            <a href="<?php $options->siteUrl(); ?>"><?php _e('Về trang chủ'); ?></a>
            &bull;
            <a href="<?php $options->adminUrl('login.php'); ?>"><?php _e('Đăng nhập'); ?></a>
        </p>
    </div>
</div>
<?php 
include 'common-js.php';
?>
<script>
$(document).ready(function () {
    $('#name').focus();
});
</script>
<?php
include 'footer.php';
?>
