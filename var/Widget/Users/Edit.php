<?php

namespace Widget\Users;

use Typecho\Common;
use Typecho\Widget\Exception;
use Typecho\Widget\Helper\Form;
use Utils\PasswordHash;
use Widget\ActionInterface;
use Widget\Base\Users;
use Widget\Notice;

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
class Edit extends Users implements ActionInterface
{
    /**
     * 执行函数
     *
     * @return void
     * @throws Exception|\Typecho\Db\Exception
     */
    public function execute()
    {
        /** 管理员以上权限 */
        $this->user->pass('administrator');

        /** 更新模式 */
        if (($this->request->uid && 'delete' != $this->request->do) || 'update' == $this->request->do) {
            $this->db->fetchRow($this->select()
                ->where('uid = ?', $this->request->uid)->limit(1), [$this, 'push']);

            if (!$this->have()) {
                throw new Exception(_t('Người dùng không tồn tại'), 404);
            }
        }
    }

    /**
     * 获取菜单标题
     *
     * @return string
     */
    public function getMenuTitle(): string
    {
        return _t('Chỉnh sửa người dùng %s', $this->name);
    }

    /**
     * 判断用户是否存在
     *
     * @param integer $uid 用户主键
     * @return boolean
     * @throws \Typecho\Db\Exception
     */
    public function userExists(int $uid): bool
    {
        $user = $this->db->fetchRow($this->db->select()
            ->from('table.users')
            ->where('uid = ?', $uid)->limit(1));

        return !empty($user);
    }

    /**
     * 增加用户
     *
     * @throws \Typecho\Db\Exception
     */
    public function insertUser()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        }

        $hasher = new PasswordHash(8, true);

        /** 取出数据 */
        $user = $this->request->from('name', 'mail', 'screenName', 'password', 'url', 'group');
        $user['screenName'] = empty($user['screenName']) ? $user['name'] : $user['screenName'];
        $user['password'] = $hasher->hashPassword($user['password']);
        $user['created'] = $this->options->time;

        /** 插入数据 */
        $user['uid'] = $this->insert($user);

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $user['uid']);

        /** 提示信息 */
        Notice::alloc()->set(_t('Người dùng %s đã được thêm vào', $user['screenName']), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php', $this->options->adminUrl));
    }

    /**
     * 生成表单
     *
     * @access public
     * @param string|null $action 表单动作
     * @return Form
     */
    public function form(?string $action = null): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-edit'), Form::POST_METHOD);

        /** 用户名称 */
        $name = new Form\Element\Text('name', null, null, _t('Tên tài khoản') . ' *', _t('Tên người dùng này sẽ được sử dụng làm tên mà người dùng đăng nhập.')
            . '<br />' . _t('Vui lòng không trùng tên đăng nhập hiện có trong hệ thống.'));
        $form->addInput($name);

        /** 电子邮箱地址 */
        $mail = new Form\Element\Text('mail', null, null, _t('Địa chỉ email') . ' *', _t('Địa chỉ email sẽ được sử dụng làm phương thức liên hệ chính cho người dùng này.')
            . '<br />' . _t('Vui lòng không trùng lặp một địa chỉ email hiện có trong hệ thống.'));
        $form->addInput($mail);

        /** 用户昵称 */
        $screenName = new Form\Element\Text('screenName', null, null, _t('Biệt hiệu của người dùng'), _t('Biệt hiệu người dùng có thể khác với tên người dùng, được sử dụng cho màn hình nền trước.')
            . '<br />' . _t('Nếu bạn để trống phần này, tên người dùng sẽ được sử dụng theo mặc định.'));
        $form->addInput($screenName);

        /** 用户密码 */
        $password = new Form\Element\Password('password', null, null, _t('Mật khẩu'), _t('Gán mật khẩu cho người dùng này.')
            . '<br />' . _t('Nên sử dụng kiểu hỗn hợp các ký tự đặc biệt, chữ cái và số để tăng tính bảo mật cho hệ thống.'));
        $password->input->setAttribute('class', 'w-60');
        $form->addInput($password);

        /** 用户密码确认 */
        $confirm = new Form\Element\Password('confirm', null, null, _t('Xác nhận mật khẩu'), _t('Vui lòng xác nhận mật khẩu của bạn, phù hợp với mật khẩu đã nhập ở trên.'));
        $confirm->input->setAttribute('class', 'w-60');
        $form->addInput($confirm);

        /** 个人主页地址 */
        $url = new Form\Element\Text('url', null, null, _t('Địa chỉ trang cá nhân'), _t('Địa chỉ trang chủ cá nhân của người dùng này, vui lòng bắt đầu bằng <code>https://facebook.com/....</code>.'));
        $form->addInput($url);

        /** 用户组 */
        $group = new Form\Element\Select(
            'group',
            [
                'subscriber'  => _t('Member'),
                'contributor' => _t('Moder'), 'editor' => _t('Admin'), 'administrator' => _t('Người sáng lập')
            ],
            null,
            _t('Nhóm người dùng'),
            _t('Các nhóm người dùng khác nhau có các quyền khác nhau.') . '<br />' . _t('Vui lòng tham khảo <a href="http://docs.typecho.org/develop/acl">tại đây</a> để biết bảng phân bổ quyền hạn cụ thể.')
        );
        $form->addInput($group);

        /** 用户动作 */
        $do = new Form\Element\Hidden('do');
        $form->addInput($do);

        /** 用户主键 */
        $uid = new Form\Element\Hidden('uid');
        $form->addInput($uid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (null != $this->request->uid) {
            $submit->value(_t('Người dùng biên tập'));
            $name->value($this->name);
            $screenName->value($this->screenName);
            $url->value($this->url);
            $mail->value($this->mail);
            $group->value($this->group);
            $do->value('update');
            $uid->value($this->uid);
            $_action = 'update';
        } else {
            $submit->value(_t('Thêm người dùng'));
            $do->value('insert');
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $screenName->addRule([$this, 'screenNameExists'], _t('Biệt danh đã tồn tại'));
            $screenName->addRule('xssCheck', _t('Vui lòng không sử dụng các ký tự đặc biệt trong biệt danh của bạn'));
            $url->addRule('url', _t('Lỗi định dạng địa chỉ trang chủ cá nhân'));
            $mail->addRule('required', _t('Email thì cần thiết'));
            $mail->addRule([$this, 'mailExists'], _t('Địa chỉ email đã tồn tại'));
            $mail->addRule('email', _t('Lỗi định dạng email'));
            $password->addRule('minLength', _t('Để đảm bảo an toàn cho tài khoản, vui lòng nhập mật khẩu ít nhất sáu ký tự'), 6);
            $confirm->addRule('confirm', _t('Hai mật khẩu đã nhập không khớp'), 'password');
        }

        if ('insert' == $action) {
            $name->addRule('required', _t('Tên người dùng là bắt buộc'));
            $name->addRule('xssCheck', _t('Vui lòng không sử dụng các ký tự đặc biệt trong tên người dùng'));
            $name->addRule([$this, 'nameExists'], _t('Tên tài khoản đã tồn tại'));
            $password->label(_t('Mật khẩu người dùng') . ' *');
            $confirm->label(_t('Xác nhận mật khẩu người dùng') . ' *');
            $password->addRule('required', _t('Mật khẩu là bắt buộc'));
        }

        if ('update' == $action) {
            $name->input->setAttribute('disabled', 'disabled');
            $uid->addRule('required', _t('Khóa chính của người dùng không tồn tại'));
            $uid->addRule([$this, 'userExists'], _t('Người dùng không tồn tại'));
        }

        return $form;
    }

    /**
     * 更新用户
     *
     * @throws \Typecho\Db\Exception
     */
    public function updateUser()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $user = $this->request->from('mail', 'screenName', 'password', 'url', 'group');
        $user['screenName'] = empty($user['screenName']) ? $user['name'] : $user['screenName'];
        if (empty($user['password'])) {
            unset($user['password']);
        } else {
            $hasher = new PasswordHash(8, true);
            $user['password'] = $hasher->hashPassword($user['password']);
        }

        /** 更新数据 */
        $this->update($user, $this->db->sql()->where('uid = ?', $this->request->uid));

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $this->request->uid);

        /** 提示信息 */
        Notice::alloc()->set(_t('Người dùng %s đã được cập nhật', $user['screenName']), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php?' .
            $this->getPageOffsetQuery($this->request->uid), $this->options->adminUrl));
    }

    /**
     * 获取页面偏移的URL Query
     *
     * @param integer $uid 用户id
     * @return string
     * @throws \Typecho\Db\Exception
     */
    protected function getPageOffsetQuery(int $uid): string
    {
        return 'page=' . $this->getPageOffset('uid', $uid);
    }

    /**
     * 删除用户
     *
     * @throws \Typecho\Db\Exception
     */
    public function deleteUser()
    {
        $users = $this->request->filter('int')->getArray('uid');
        $masterUserId = $this->db->fetchObject($this->db->select(['MIN(uid)' => 'num'])->from('table.users'))->num;
        $deleteCount = 0;

        foreach ($users as $user) {
            if ($masterUserId == $user || $user == $this->user->uid) {
                continue;
            }

            if ($this->delete($this->db->sql()->where('uid = ?', $user))) {
                $deleteCount++;
            }
        }

        /** 提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('Người dùng đã bị xóa') : _t('Không có người dùng nào bị xóa'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php', $this->options->adminUrl));
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->user->pass('administrator');
        $this->security->protect();
        $this->on($this->request->is('do=insert'))->insertUser();
        $this->on($this->request->is('do=update'))->updateUser();
        $this->on($this->request->is('do=delete'))->deleteUser();
        $this->response->redirect($this->options->adminUrl);
    }
}
