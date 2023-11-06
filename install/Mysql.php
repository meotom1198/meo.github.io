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
        <label class="typecho-label" for="dbUser"><?php _e('Tên người dùng cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="" />
        <p class="description"><?php _e('Bạn có thể sử dụng "%s"', 'root'); ?></p>
    </li>
</ul>

<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbPassword"><?php _e('Mật khẩu cơ sở dữ liệu'); ?></label>
        <input type="password" class="text" name="dbPassword" id="dbPassword" value="" />
    </li>
</ul>
<ul class="typecho-option">
    <li>
        <label class="typecho-label" for="dbDatabase"><?php _e('Tên cơ sở dữ liệu'); ?></label>
        <input type="text" class="text" name="dbDatabase" id="dbDatabase" value="" />
        <p class="description"><?php _e('Vui lòng chỉ định tên cơ sở dữ liệu'); ?></p>
    </li>

</ul>

<details>
    <summary>
        <strong><?php _e('Tùy chọn nâng cao'); ?></strong>
    </summary>
    <ul class="typecho-option">
        <li>
            <label class="typecho-label" for="dbPort"><?php _e('Cổng cơ sở dữ liệu'); ?></label>
            <input type="text" class="text" name="dbPort" id="dbPort" value="3306"/>
            <p class="description"><?php _e('Nếu bạn không biết ý nghĩa của tùy chọn này, hãy để mặc định'); ?></p>
        </li>
    </ul>

    <ul class="typecho-option">
        <li>
            <label class="typecho-label" for="dbCharset"><?php _e('Mã cơ sở dữ liệu'); ?></label>
            <select name="dbCharset" id="dbCharset">
                <option value="utf8mb4">utf8mb4</option>
                <option value="utf8">utf8</option>
            </select>
            <p class="description"><?php _e('Chọn mã hóa utf8mb4 yêu cầu ít nhất phiên bản MySQL 5.5.3'); ?></p>
        </li>
    </ul>

    <ul class="typecho-option">
        <li>
            <label class="typecho-label" for="dbEngine"><?php _e('Cơ sở dữ liệu'); ?></label>
            <select name="dbEngine" id="dbEngine">
                <option value="InnoDB">InnoDB</option>
                <option value="MyISAM">MyISAM</option>
            </select>
        </li>
    </ul>
</details>