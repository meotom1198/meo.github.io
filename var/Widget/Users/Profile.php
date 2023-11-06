<?php

namespace Widget\Users;

use Typecho\Common;
use Typecho\Db\Exception;
use Typecho\Plugin;
use Typecho\Widget\Helper\Form;
use Utils\PasswordHash;
use Widget\ActionInterface;
use Widget\Base\Options;
use Widget\Notice;
use Widget\Plugins\Rows;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑用户组件
 *
 * @link typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Profile extends Edit implements ActionInterface
{
    /**
     * 执行函数
     */
    public function execute()
    {
        /** 注册用户以上权限 */
        $this->user->pass('subscriber');
        $this->request->setParam('uid', $this->user->uid);
    }

    /**
     * 输出表单结构
     *
     * @access public
     * @return Form
     */
    public function optionsForm(): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-profile'), Form::POST_METHOD);

        /** 撰写设置 */
        $markdown = new Form\Element\Radio(
            'markdown',
            ['0' => _t('Đóng'), '1' => _t('Mở')],
            $this->options->markdown,
            _t('Chỉnh sửa và phân tích nội dung bằng cú pháp Markdown'),
            _t('Sử dụng cú pháp <a href="http://daringfireball.net/projects/markdown/">Markdown</a> để giúp quá trình viết của bạn dễ dàng và trực quan hơn.')
            . '<br />' . _t('Bật tính năng này sẽ không ảnh hưởng đến nội dung chưa được chỉnh sửa trước đó bằng cú pháp Markdown.')
        );
        $form->addInput($markdown);

        $xmlrpcMarkdown = new Form\Element\Radio(
            'xmlrpcMarkdown',
            ['0' => _t('Đóng'), '1' => _t('Mở')],
            $this->options->xmlrpcMarkdown,
            _t('Sử dụng cú pháp Markdown trong giao diện XMLRPC'),
            _t('Đối với các trình chỉnh sửa ngoại tuyến hỗ trợ đầy đủ cách viết cú pháp <a href="http://daringfireball.net/projects/markdown/">Markdown</a>, việc bật tùy chọn này sẽ ngăn chuyển đổi nội dung thành HTML.')
        );
        $form->addInput($xmlrpcMarkdown);

        /** 自动保存 */
        $autoSave = new Form\Element\Radio(
            'autoSave',
            ['0' => _t('Đóng'), '1' => _t('Mở')],
            $this->options->autoSave,
            _t('Tự động lưu'),
            _t('Chức năng lưu tự động có thể bảo vệ bài viết của bạn khỏi bị mất tốt hơn.')
        );
        $form->addInput($autoSave);

        /** 默认允许 */
        $allow = [];
        if ($this->options->defaultAllowComment) {
            $allow[] = 'comment';
        }

        if ($this->options->defaultAllowPing) {
            $allow[] = 'ping';
        }

        if ($this->options->defaultAllowFeed) {
            $allow[] = 'feed';
        }

        $defaultAllow = new Form\Element\Checkbox(
            'defaultAllow',
            ['comment' => _t('Có thể được bình luận'), 'ping' => _t('Có thể được trích dẫn'), 'feed' => _t('Xuất hiện trong tập hợp')],
            $allow,
            _t('Được phép theo mặc định'),
            _t('Đặt quyền mặc định mà bạn sử dụng thường xuyên')
        );
        $form->addInput($defaultAllow);

        /** 用户动作 */
        $do = new Form\Element\Hidden('do', null, 'options');
        $form->addInput($do);

        /** 提交按钮 */
        $submit = new Form\Element\Submit('submit', null, _t('Lưu các thiết lập'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        return $form;
    }

    /**
     * 自定义设置列表
     *
     * @throws Plugin\Exception
     */
    public function personalFormList()
    {
        $plugins = Rows::alloc('activated=1');

        while ($plugins->next()) {
            if ($plugins->personalConfig) {
                [$pluginFileName, $className] = Plugin::portal($plugins->name, $this->options->pluginDir);

                $form = $this->personalForm($plugins->name, $className, $pluginFileName, $group);
                if ($this->user->pass($group, true)) {
                    echo '<br><section id="personal-' . $plugins->name . '">';
                    echo '<h3>' . $plugins->title . '</h3>';

                    $form->render();

                    echo '</section>';
                }
            }
        }
    }

    /**
     * 输出自定义设置选项
     *
     * @access public
     * @param string $pluginName 插件名称
     * @param string $className 类名称
     * @param string $pluginFileName 插件文件名
     * @param string|null $group 用户组
     * @throws Plugin\Exception
     */
    public function personalForm(string $pluginName, string $className, string $pluginFileName, ?string &$group)
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-profile'), Form::POST_METHOD);
        $form->setAttribute('name', $pluginName);
        $form->setAttribute('id', $pluginName);

        require_once $pluginFileName;
        $group = call_user_func([$className, 'personalConfig'], $form);
        $group = $group ?: 'subscriber';

        $options = $this->options->personalPlugin($pluginName);

        if (!empty($options)) {
            foreach ($options as $key => $val) {
                $form->getInput($key)->value($val);
            }
        }

        $form->addItem(new Form\Element\Hidden('do', null, 'personal'));
        $form->addItem(new Form\Element\Hidden('plugin', null, $pluginName));
        $submit = new Form\Element\Submit('submit', null, _t('Lưu các thiết lập'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        return $form;
    }

    /**
     * 更新用户
     *
     * @throws Exception
     */
    public function updateProfile()
    {
        if ($this->profileForm()->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $user = $this->request->from('mail', 'screenName', 'url');
        $user['screenName'] = empty($user['screenName']) ? $user['name'] : $user['screenName'];

        /** 更新数据 */
        $this->update($user, $this->db->sql()->where('uid = ?', $this->user->uid));

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $this->user->uid);

        /** 提示信息 */
        Notice::alloc()->set(_t('Hồ sơ của bạn đã được cập nhật'), 'success');

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 生成表单
     *
     * @return Form
     */
    public function profileForm()
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-profile'), Form::POST_METHOD);

        /** 用户昵称 */
        $screenName = new Form\Element\Text('screenName', null, null, _t('Tên nick'), _t('Biệt hiệu người dùng có thể khác với tên người dùng, được sử dụng cho màn hình nền trước.')
            . '<br />' . _t('Nếu bạn để trống phần này, tên người dùng sẽ được sử dụng theo mặc định.'));
        $form->addInput($screenName);

        /** 个人主页地址 */
        $url = new Form\Element\Text('url', null, null, _t('Địa chỉ trang cá nhân'), _t('Địa chỉ trang chủ cá nhân của người dùng này, vui lòng bắt đầu bằng <code>http://</code>.'));
        $form->addInput($url);

        /** 电子邮箱地址 */
        $mail = new Form\Element\Text('mail', null, null, _t('Địa chỉ email') . ' *', _t('Địa chỉ email sẽ được sử dụng làm phương thức liên hệ chính cho người dùng này.')
            . '<br />' . _t('Vui lòng không trùng lặp một địa chỉ email hiện có trong hệ thống.'));
        $form->addInput($mail);

        /** 用户动作 */
        $do = new Form\Element\Hidden('do', null, 'profile');
        $form->addInput($do);

        /** 提交按钮 */
        $submit = new Form\Element\Submit('submit', null, _t('Cập nhật hồ sơ của tôi'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        $screenName->value($this->user->screenName);
        $url->value($this->user->url);
        $mail->value($this->user->mail);

        /** 给表单增加规则 */
        $screenName->addRule([$this, 'screenNameExists'], _t('Biệt danh đã tồn tại'));
        $screenName->addRule('xssCheck', _t('Vui lòng không sử dụng các ký tự đặc biệt trong biệt danh của bạn'));
        $url->addRule('url', _t('Lỗi định dạng địa chỉ trang chủ cá nhân'));
        $mail->addRule('required', _t('Email thì cần thiết'));
        $mail->addRule([$this, 'mailExists'], _t('Địa chỉ email đã tồn tại'));
        $mail->addRule('email', _t('Lỗi định dạng email'));

        return $form;
    }

    /**
     * 执行更新动作
     *
     * @throws Exception
     */
    public function updateOptions()
    {
        $settings['autoSave'] = $this->request->autoSave ? 1 : 0;
        $settings['markdown'] = $this->request->markdown ? 1 : 0;
        $settings['xmlrpcMarkdown'] = $this->request->xmlrpcMarkdown ? 1 : 0;
        $defaultAllow = $this->request->getArray('defaultAllow');

        $settings['defaultAllowComment'] = in_array('comment', $defaultAllow) ? 1 : 0;
        $settings['defaultAllowPing'] = in_array('ping', $defaultAllow) ? 1 : 0;
        $settings['defaultAllowFeed'] = in_array('feed', $defaultAllow) ? 1 : 0;

        foreach ($settings as $name => $value) {
            if (
                $this->db->fetchObject($this->db->select(['COUNT(*)' => 'num'])
                    ->from('table.options')->where('name = ? AND user = ?', $name, $this->user->uid))->num > 0
            ) {
                Options::alloc()
                    ->update(
                        ['value' => $value],
                        $this->db->sql()->where('name = ? AND user = ?', $name, $this->user->uid)
                    );
            } else {
                Options::alloc()->insert([
                    'name'  => $name,
                    'value' => $value,
                    'user'  => $this->user->uid
                ]);
            }
        }

        Notice::alloc()->set(_t("Đã lưu cài đặt"), 'success');
        $this->response->goBack();
    }

    /**
     * 更新密码
     *
     * @throws Exception
     */
    public function updatePassword()
    {
        /** 验证格式 */
        if ($this->passwordForm()->validate()) {
            $this->response->goBack();
        }

        $hasher = new PasswordHash(8, true);
        $password = $hasher->hashPassword($this->request->password);

        /** 更新数据 */
        $this->update(
            ['password' => $password],
            $this->db->sql()->where('uid = ?', $this->user->uid)
        );

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $this->user->uid);

        /** 提示信息 */
        Notice::alloc()->set(_t('Mật khẩu đã được thay đổi thành công'), 'success');

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 生成表单
     *
     * @return Form
     */
    public function passwordForm(): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-profile'), Form::POST_METHOD);

        /** 用户密码 */
        $password = new Form\Element\Password('password', null, null, _t('Mật khẩu'), _t('Gán mật khẩu cho người dùng này.')
            . '<br />' . _t('Nên sử dụng kiểu hỗn hợp các ký tự đặc biệt, chữ cái và số để tăng tính bảo mật cho hệ thống.'));
        $password->input->setAttribute('class', 'w-60');
        $form->addInput($password);

        /** 用户密码确认 */
        $confirm = new Form\Element\Password('confirm', null, null, _t('Xác nhận mật khẩu người dùng'), _t('Vui lòng xác nhận mật khẩu của bạn, phù hợp với mật khẩu đã nhập ở trên.'));
        $confirm->input->setAttribute('class', 'w-60');
        $form->addInput($confirm);

        /** 用户动作 */
        $do = new Form\Element\Hidden('do', null, 'password');
        $form->addInput($do);

        /** 提交按钮 */
        $submit = new Form\Element\Submit('submit', null, _t('Cập nhật mật khẩu'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        $password->addRule('required', _t('Mật khẩu là bắt buộc'));
        $password->addRule('minLength', _t('Để đảm bảo an toàn cho tài khoản, vui lòng nhập mật khẩu ít nhất sáu ký tự'), 6);
        $confirm->addRule('confirm', _t('Hai mật khẩu đã nhập không khớp'), 'password');

        return $form;
    }

    /**
     * 更新个人设置
     *
     * @throws \Typecho\Widget\Exception
     */
    public function updatePersonal()
    {
        /** 获取插件名称 */
        $pluginName = $this->request->plugin;

        /** 获取已启用插件 */
        $plugins = Plugin::export();
        $activatedPlugins = $plugins['activated'];

        /** 获取插件入口 */
        [$pluginFileName, $className] = Plugin::portal(
            $this->request->plugin,
            __TYPECHO_ROOT_DIR__ . '/' . __TYPECHO_PLUGIN_DIR__
        );
        $info = Plugin::parseInfo($pluginFileName);

        if (!$info['personalConfig'] || !isset($activatedPlugins[$pluginName])) {
            throw new \Typecho\Widget\Exception(_t('Không thể định cấu hình plugin'), 500);
        }

        $form = $this->personalForm($pluginName, $className, $pluginFileName, $group);
        $this->user->pass($group);

        /** 验证表单 */
        if ($form->validate()) {
            $this->response->goBack();
        }

        $settings = $form->getAllRequest();
        unset($settings['do'], $settings['plugin']);
        $name = '_plugin:' . $pluginName;

        if (!$this->personalConfigHandle($className, $settings)) {
            if (
                $this->db->fetchObject($this->db->select(['COUNT(*)' => 'num'])
                    ->from('table.options')->where('name = ? AND user = ?', $name, $this->user->uid))->num > 0
            ) {
                Options::alloc()
                    ->update(
                        ['value' => serialize($settings)],
                        $this->db->sql()->where('name = ? AND user = ?', $name, $this->user->uid)
                    );
            } else {
                Options::alloc()->insert([
                    'name'  => $name,
                    'value' => serialize($settings),
                    'user'  => $this->user->uid
                ]);
            }
        }

        /** 提示信息 */
        Notice::alloc()->set(_t("Đã lưu cài đặt %s", $info['title']), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('profile.php', $this->options->adminUrl));
    }

    /**
     * 用自有函数处理自定义配置信息
     *
     * @access public
     * @param string $className 类名
     * @param array $settings 配置值
     * @return boolean
     */
    public function personalConfigHandle(string $className, array $settings): bool
    {
        if (method_exists($className, 'personalConfigHandle')) {
            call_user_func([$className, 'personalConfigHandle'], $settings, false);
            return true;
        }

        return false;
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->security->protect();
        $this->on($this->request->is('do=profile'))->updateProfile();
        $this->on($this->request->is('do=options'))->updateOptions();
        $this->on($this->request->is('do=password'))->updatePassword();
        $this->on($this->request->is('do=personal&plugin'))->updatePersonal();
        $this->response->redirect($this->options->siteUrl);
    }
}
