<?php

namespace Widget;

use Typecho\Common;
use Typecho\Cookie;
use Typecho\Db\Exception;
use Typecho\Validate;
use Utils\PasswordHash;
use Widget\Base\Users;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 注册组件
 *
 * @author qining
 * @category typecho
 * @package Widget
 */
class Register extends Users implements ActionInterface
{
    /**
     * 初始化函数
     *
     * @throws Exception
     */
    public function action()
    {
        // protect
        $this->security->protect();

        /** 如果已经登录 */
        if ($this->user->hasLogin() || !$this->options->allowRegister) {
            /** 直接返回 */
            $this->response->redirect($this->options->index);
        }

        /** 初始化验证类 */
        $validator = new Validate();
        $validator->addRule('name', 'required', _t('Tên tài khoản là bắt buộc'));
        $validator->addRule('name', 'minLength', _t('Tên người dùng phải chứa ít nhất 2 ký tự'), 2);
        $validator->addRule('name', 'maxLength', _t('Tên người dùng có thể chứa tối đa 32 ký tự'), 32);
        $validator->addRule('name', 'xssCheck', _t('Vui lòng không sử dụng các ký tự đặc biệt trong tên người dùng'));
        $validator->addRule('name', [$this, 'nameExists'], _t('Tên tài khoản đã được sử dụng'));
        $validator->addRule('mail', 'required', _t('Email thì cần thiết'));
        $validator->addRule('mail', [$this, 'mailExists'], _t('Đia chỉ email đã tồn tại'));
        $validator->addRule('mail', 'email', _t('Lỗi định dạng email'));
        $validator->addRule('mail', 'maxLength', _t('Email có thể chứa tối đa 64 ký tự'), 64);

        /** 如果请求中有password */
        if (array_key_exists('password', $_REQUEST)) {
            $validator->addRule('password', 'required', _t('Mật khẩu là bắt buộc'));
            $validator->addRule('password', 'minLength', _t('Để đảm bảo an toàn cho tài khoản, vui lòng nhập mật khẩu ít nhất 6 ký tự'), 6);
            $validator->addRule('password', 'maxLength', _t('Để dễ nhớ, vui lòng không vượt quá 18 ký tự về độ dài mật khẩu'), 18);
            $validator->addRule('confirm', 'confirm', _t('Hai mật khẩu đã nhập không khớp'), 'password');
        }

        /** 截获验证异常 */
        if ($error = $validator->run($this->request->from('name', 'password', 'mail', 'confirm'))) {
            Cookie::set('__typecho_remember_name', $this->request->name);
            Cookie::set('__typecho_remember_mail', $this->request->mail);

            /** 设置提示信息 */
            Notice::alloc()->set($error);
            $this->response->goBack();
        }

        $hasher = new PasswordHash(8, true);
        $generatedPassword = Common::randString(7);

        $dataStruct = [
            'name' => $this->request->name,
            'mail' => $this->request->mail,
            'screenName' => $this->request->name,
            'password' => $hasher->hashPassword($generatedPassword),
            'created' => $this->options->time,
            'group' => 'subscriber'
        ];

        $dataStruct = self::pluginHandle()->register($dataStruct);

        $insertId = $this->insert($dataStruct);
        $this->db->fetchRow($this->select()->where('uid = ?', $insertId)
            ->limit(1), [$this, 'push']);

        self::pluginHandle()->finishRegister($this);

        $this->user->login($this->request->name, $generatedPassword);

        Cookie::delete('__typecho_first_run');
        Cookie::delete('__typecho_remember_name');
        Cookie::delete('__typecho_remember_mail');

        Notice::alloc()->set(
            _t(
                'Đăng ký tài khoản <strong>%s</strong> thành công, mật khẩu là <strong>%s</strong>!',
                $this->screenName,
                $generatedPassword
            ),
            'success'
        );
        $this->response->redirect($this->options->adminUrl);
    }
}
