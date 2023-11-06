<?php

namespace Widget\Contents\Attachment;

use Typecho\Common;
use Typecho\Widget\Exception;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Layout;
use Widget\ActionInterface;
use Widget\Contents\Post\Edit as PostEdit;
use Widget\Notice;
use Widget\Upload;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑文章组件
 *
 * @author qining
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Edit extends PostEdit implements ActionInterface
{
    /**
     * 执行函数
     *
     * @throws Exception|\Typecho\Db\Exception
     */
    public function execute()
    {
        /** 必须为贡献者以上权限 */
        $this->user->pass('contributor');

        /** 获取文章内容 */
        if (!empty($this->request->cid)) {
            $this->db->fetchRow($this->select()
                ->where('table.contents.type = ?', 'attachment')
                ->where('table.contents.cid = ?', $this->request->filter('int')->cid)
                ->limit(1), [$this, 'push']);

            if (!$this->have()) {
                throw new Exception(_t('Tập tin không tồn tại'), 404);
            } elseif (!$this->allow('edit')) {
                throw new Exception(_t('Không có quyền chỉnh sửa'), 403);
            }
        }
    }

    /**
     * 判断文件名转换到缩略名后是否合法
     *
     * @param string $name 文件名
     * @return boolean
     */
    public function nameToSlug(string $name): bool
    {
        if (empty($this->request->slug)) {
            $slug = Common::slugName($name);
            if (empty($slug) || !$this->slugExists($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判断文件缩略名是否存在
     *
     * @param string $slug 缩略名
     * @return boolean
     * @throws \Typecho\Db\Exception
     */
    public function slugExists(string $slug): bool
    {
        $select = $this->db->select()
            ->from('table.contents')
            ->where('type = ?', 'attachment')
            ->where('slug = ?', Common::slugName($slug))
            ->limit(1);

        if ($this->request->cid) {
            $select->where('cid <> ?', $this->request->cid);
        }

        $attachment = $this->db->fetchRow($select);
        return !$attachment;
    }

    /**
     * 更新文件
     *
     * @throws \Typecho\Db\Exception
     * @throws Exception
     */
    public function updateAttachment()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $input = $this->request->from('name', 'slug', 'description');
        $input['slug'] = Common::slugName(empty($input['slug']) ? $input['name'] : $input['slug']);

        $attachment['title'] = $input['name'];
        $attachment['slug'] = $input['slug'];

        $content = $this->attachment->toArray();
        $content['description'] = $input['description'];

        $attachment['text'] = serialize($content);
        $cid = $this->request->filter('int')->cid;

        /** 更新数据 */
        $updateRows = $this->update($attachment, $this->db->sql()->where('cid = ?', $cid));

        if ($updateRows > 0) {
            $this->db->fetchRow($this->select()
                ->where('table.contents.type = ?', 'attachment')
                ->where('table.contents.cid = ?', $cid)
                ->limit(1), [$this, 'push']);

            /** 设置高亮 */
            Notice::alloc()->highlight($this->theId);

            /** 提示信息 */
            Notice::alloc()->set('publish' == $this->status ?
                _t('Tệp <a href="%s">%s</a> đã được cập nhật', $this->permalink, $this->title) :
                _t('Tệp không được lưu trữ %s đã được cập nhật ', $this->title), 'success');
        }

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-medias.php?' .
            $this->getPageOffsetQuery($cid, $this->status), $this->options->adminUrl));
    }

    /**
     * 生成表单
     *
     * @return Form
     */
    public function form(): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/contents-attachment-edit'), Form::POST_METHOD);

        /** 文件名称 */
        $name = new Form\Element\Text('name', null, $this->title, _t('Tiêu đề') . ' *');
        $form->addInput($name);

        /** 文件缩略名 */
        $slug = new Form\Element\Text(
            'slug',
            null,
            $this->slug,
            _t('Viết tắt'),
            _t('Các chữ viết tắt của tệp được sử dụng để tạo các liên kết thân thiện, các chữ cái, số, dấu gạch dưới và dấu gạch ngang được khuyến nghị.')
        );
        $form->addInput($slug);

        /** 文件描述 */
        $description = new Form\Element\Textarea(
            'description',
            null,
            $this->attachment->description,
            _t('Mô tả'),
            _t('Văn bản này được sử dụng để mô tả tệp và nó sẽ được hiển thị trong một số chủ đề.')
        );
        $form->addInput($description);

        /** 分类动作 */
        $do = new Form\Element\Hidden('do', null, 'update');
        $form->addInput($do);

        /** 分类主键 */
        $cid = new Form\Element\Hidden('cid', null, $this->cid);
        $form->addInput($cid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit(null, null, _t('Gửi thay đổi'));
        $submit->input->setAttribute('class', 'btn primary');
        $delete = new Layout('a', [
            'href'  => $this->security->getIndex('/action/contents-attachment-edit?do=delete&cid=' . $this->cid),
            'class' => 'operate-delete',
            'lang'  => _t('Bạn có chắc chắn muốn xóa tệp %s không?', $this->attachment->name)
        ]);
        $submit->container($delete->html(_t('Xóa các tập tin')));
        $form->addItem($submit);

        $name->addRule('required', _t('Tiêu đề tệp là bắt buộc'));
        $name->addRule([$this, 'nameToSlug'], _t('Không thể chuyển đổi tiêu đề tệp thành tên viết tắt'));
        $slug->addRule([$this, 'slugExists'], _t('Viết tắt đã tồn tại'));

        return $form;
    }

    /**
     * 获取页面偏移的URL Query
     *
     * @param integer $cid 文件id
     * @param string|null $status 状态
     * @return string
     * @throws \Typecho\Db\Exception|Exception
     */
    protected function getPageOffsetQuery(int $cid, string $status = null): string
    {
        return 'page=' . $this->getPageOffset(
            'cid',
            $cid,
            'attachment',
            $status,
            $this->user->pass('editor', true) ? 0 : $this->user->uid
        );
    }

    /**
     * 删除文章
     *
     * @throws \Typecho\Db\Exception
     */
    public function deleteAttachment()
    {
        $posts = $this->request->filter('int')->getArray('cid');
        $deleteCount = 0;

        foreach ($posts as $post) {
            // 删除插件接口
            self::pluginHandle()->delete($post, $this);

            $condition = $this->db->sql()->where('cid = ?', $post);
            $row = $this->db->fetchRow($this->select()
                ->where('table.contents.type = ?', 'attachment')
                ->where('table.contents.cid = ?', $post)
                ->limit(1), [$this, 'push']);

            if ($this->isWriteable(clone $condition) && $this->delete($condition)) {
                /** 删除文件 */
                Upload::deleteHandle($row);

                /** 删除评论 */
                $this->db->query($this->db->delete('table.comments')
                    ->where('cid = ?', $post));

                // 完成删除插件接口
                self::pluginHandle()->finishDelete($post, $this);

                $deleteCount++;
            }

            unset($condition);
        }

        if ($this->request->isAjax()) {
            $this->response->throwJson($deleteCount > 0 ? ['code' => 200, 'message' => _t('Tập tin đã bị xóa')]
                : ['code' => 500, 'message' => _t('Không có tệp nào bị xóa')]);
        } else {
            /** 设置提示信息 */
            Notice::alloc()
                ->set(
                    $deleteCount > 0 ? _t('Tập tin đã bị xóa') : _t('Không có tệp nào bị xóa'),
                    $deleteCount > 0 ? 'success' : 'notice'
                );

            /** 返回原网页 */
            $this->response->redirect(Common::url('manage-medias.php', $this->options->adminUrl));
        }
    }

    /**
     * clearAttachment
     *
     * @access public
     * @return void
     * @throws \Typecho\Db\Exception
     */
    public function clearAttachment()
    {
        $page = 1;
        $deleteCount = 0;

        do {
            $posts = array_column($this->db->fetchAll($this->select('cid')
                ->from('table.contents')
                ->where('type = ? AND parent = ?', 'attachment', 0)
                ->page($page, 100)), 'cid');
            $page++;

            foreach ($posts as $post) {
                // 删除插件接口
                self::pluginHandle()->delete($post, $this);

                $condition = $this->db->sql()->where('cid = ?', $post);
                $row = $this->db->fetchRow($this->select()
                    ->where('table.contents.type = ?', 'attachment')
                    ->where('table.contents.cid = ?', $post)
                    ->limit(1), [$this, 'push']);

                if ($this->isWriteable(clone $condition) && $this->delete($condition)) {
                    /** 删除文件 */
                    Upload::deleteHandle($row);

                    /** 删除评论 */
                    $this->db->query($this->db->delete('table.comments')
                        ->where('cid = ?', $post));

                    $status = $this->status;

                    // 完成删除插件接口
                    self::pluginHandle()->finishDelete($post, $this);

                    $deleteCount++;
                }

                unset($condition);
            }
        } while (count($posts) == 100);

        /** 设置提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('Các tệp không được lưu trữ đã được làm sạch') : _t('Không có tệp chưa lưu trữ nào được làm sạch'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 返回原网页 */
        $this->response->redirect(Common::url('manage-medias.php', $this->options->adminUrl));
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->security->protect();
        $this->on($this->request->is('do=delete'))->deleteAttachment();
        $this->on($this->have() && $this->request->is('do=update'))->updateAttachment();
        $this->on($this->request->is('do=clear'))->clearAttachment();
        $this->response->redirect($this->options->adminUrl);
    }
}
