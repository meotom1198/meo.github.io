<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbHost"><?php _e('Địa chỉ cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbHost" id="dbHost" value="localhost"/>
        <p class="description"><?php _e('Bạn có thể sử dụng "%s"', 'localhost'); ?></p>
    </li>
</ul>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbPort"><?php _e('Cổng cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbPort" id="dbPort" value="5432"/>
        <p class="description"><?php _e('Nếu bạn không biết ý nghĩa của tùy chọn này, hãy để mặc định'); ?></p>
    </li>
</ul>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbUser"><?php _e('Tên người dùng cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="postgres" />
        <p class="description"><?php _e('Bạn có thể sử dụng "%s"', 'postgres'); ?></p>
    </li>
</ul>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbPassword"><?php _e('Mật khẩu cơ sở dữ liệu'); ?></label>
        <input type="password" class="text" name="dbPassword" id="dbPassword" value="" />
    </li
</ul>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbDatabase"><?php _e('1tên cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbDatabase" id="dbDatabase" value="" />
        <p class="description"><?php _e('Vui lòng chỉ định tên cơ sở dữ liệu'); ?></p>
    </li
</ul>

<input type="hidden" name="dbCharset" value="utf8" />
