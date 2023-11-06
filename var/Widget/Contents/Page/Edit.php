<?php

namespace Widget\Contents\Page;

use Typecho\Common;
use Typecho\Date;
use Typecho\Widget\Exception;
use Widget\Contents\Post\Edit as PostEdit;
use Widget\ActionInterface;
use Widget\Notice;
use Widget\Service;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑页面组件
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
     * 自定义字段的hook名称
     *
     * @var string
     * @access protected
     */
    protected $themeCustomFieldsHook = 'themePageFields';

    /**
     * 执行函数
     *
     * @access public
     * @return void
     * @throws Exception
     * @throws \Typecho\Db\Exception
     */
    public function execute()
    {
        /** 必须为编辑以上权限 */
        $this->user->pass('editor');

        /** 获取文章内容 */
        if (!empty($this->request->cid)) {
            $this->db->fetchRow($this->select()
                ->where('table.contents.type = ? OR table.contents.type = ?', 'page', 'page_draft')
                ->where('table.contents.cid = ?', $this->request->filter('int')->cid)
                ->limit(1), [$this, 'push']);

            if ('page_draft' == $this->status && $this->parent) {
                $this->response->redirect(Common::url('write-page.php?cid=' . $this->parent, $this->options->adminUrl));
            }

            if (!$this->have()) {
                throw new Exception(_t('Trang không tồn tại'), 404);
            } elseif (!$this->allow('edit')) {
                throw new Exception(_t('Không có quyền chỉnh sửa'), 403);
            }
        }
    }

    /**
     * 发布文章
     */
    public function writePage()
    {
        $contents = $this->request->from(
            'text',
            'template',
            'allowComment',
            'allowPing',
            'allowFeed',
            'slug',
            'order',
            'visibility'
        );

        $contents['title'] = $this->request->get('title', _t('Trang không có tiêu đề'));
        $contents['created'] = $this->getCreated();
        $contents['visibility'] = ('hidden' == $contents['visibility'] ? 'hidden' : 'publish');

        if ($this->request->markdown && $this->options->markdown) {
            $contents['text'] = '<!--markdown-->' . $contents['text'];
        }

        $contents = self::pluginHandle()->write($contents, $this);

        if ($this->request->is('do=publish')) {
            /** 重新发布已经存在的文章 */
            $contents['type'] = 'page';
            $this->publish($contents);

            // 完成发布插件接口
            self::pluginHandle()->finishPublish($contents, $this);

            /** 发送ping */
            Service::alloc()->sendPing($this);

            /** 设置提示信息 */
            Notice::alloc()->set(
                _t('Trang "<a href="%s">%s</a>" đã được xuất bản', $this->permalink, $this->title),
                'success'
            );

            /** 设置高亮 */
            Notice::alloc()->highlight($this->theId);

            /** 页面跳转 */
            $this->response->redirect(Common::url('manage-pages.php?', $this->options->adminUrl));
        } else {
            /** 保存文章 */
            $contents['type'] = 'page_draft';
            $this->save($contents);

            // 完成发布插件接口
            self::pluginHandle()->finishSave($contents, $this);

            /** 设置高亮 */
            Notice::alloc()->highlight($this->cid);

            if ($this->request->isAjax()) {
                $created = new Date($this->options->time);
                $this->response->throwJson([
                    'success' => 1,
                    'time'    => $created->format('H:i:s A'),
                    'cid'     => $this->cid,
                    'draftId' => $this->draft['cid']
                ]);
            } else {
                /** 设置提示信息 */
                Notice::alloc()->set(_t('Bản nháp "%s" đã được lưu', $this->title), 'success');

                /** 返回原页面 */
                $this->response->redirect(Common::url('write-page.php?cid=' . $this->cid, $this->options->adminUrl));
            }
        }
    }

    /**
     * 标记页面
     *
     * @throws \Typecho\Db\Exception
     */
    public function markPage()
    {
        $status = $this->request->get('status');
        $statusList = [
            'publish' => _t('Công khai'),
            'hidden'  => _t('Ẩn')
        ];

        if (!isset($statusList[$status])) {
            $this->response->goBack();
        }

        $pages = $this->request->filter('int')->getArray('cid');
        $markCount = 0;

        foreach ($pages as $page) {
            // 标记插件接口
            self::pluginHandle()->mark($status, $page, $this);
            $condition = $this->db->sql()->where('cid = ?', $page);

            if ($this->db->query($condition->update('table.contents')->rows(['status' => $status]))) {
                // 处理草稿
                $draft = $this->db->fetchRow($this->db->select('cid')
                    ->from('table.contents')
                    ->where('table.contents.parent = ? AND table.contents.type = ?', $page, 'page_draft')
                    ->limit(1));

                if (!empty($draft)) {
                    $this->db->query($this->db->update('table.contents')->rows(['status' => $status])
                        ->where('cid = ?', $draft['cid']));
                }

                // 完成标记插件接口
                self::pluginHandle()->finishMark($status, $page, $this);

                $markCount++;
            }

            unset($condition);
        }

        /** 设置提示信息 */
        Notice::alloc()
            ->set(
                $markCount > 0 ? _t('Trang đã được đánh dấu là <strong>%s</strong>', $statusList[$status]) : _t('Không có trang nào được gắn thẻ'),
                $markCount > 0 ? 'success' : 'notice'
            );

        /** 返回原网页 */
        $this->response->goBack();
    }

    /**
     * 删除页面
     *
     * @throws \Typecho\Db\Exception
     */
    public function deletePage()
    {
        $pages = $this->request->filter('int')->getArray('cid');
        $deleteCount = 0;

        foreach ($pages as $page) {
            // 删除插件接口
            self::pluginHandle()->delete($page, $this);

            if ($this->delete($this->db->sql()->where('cid = ?', $page))) {
                /** 删除评论 */
                $this->db->query($this->db->delete('table.comments')
                    ->where('cid = ?', $page));

                /** 解除附件关联 */
                $this->unAttach($page);

                /** 解除首页关联 */
                if ($this->options->frontPage == 'page:' . $page) {
                    $this->db->query($this->db->update('table.options')
                        ->rows(['value' => 'recent'])
                        ->where('name = ?', 'frontPage'));
                }

                /** 删除草稿 */
                $draft = $this->db->fetchRow($this->db->select('cid')
                    ->from('table.contents')
                    ->where('table.contents.parent = ? AND table.contents.type = ?', $page, 'page_draft')
                    ->limit(1));

                /** 删除自定义字段 */
                $this->deleteFields($page);

                if ($draft) {
                    $this->deleteDraft($draft['cid']);
                    $this->deleteFields($draft['cid']);
                }

                // 完成删除插件接口
                self::pluginHandle()->finishDelete($page, $this);

                $deleteCount++;
            }
        }

        /** 设置提示信息 */
        Notice::alloc()
            ->set(
                $deleteCount > 0 ? _t('Trang đã bị xóa') : _t('Thông có trang nào bị xóa'),
                $deleteCount > 0 ? 'success' : 'notice'
            );

        /** 返回原网页 */
        $this->response->goBack();
    }

    /**
     * 删除页面所属草稿
     *
     * @throws \Typecho\Db\Exception
     */
    public function deletePageDraft()
    {
        $pages = $this->request->filter('int')->getArray('cid');
        $deleteCount = 0;

        foreach ($pages as $page) {
            /** 删除草稿 */
            $draft = $this->db->fetchRow($this->db->select('cid')
                ->from('table.contents')
                ->where('table.contents.parent = ? AND table.contents.type = ?', $page, 'page_draft')
                ->limit(1));

            if ($draft) {
                $this->deleteDraft($draft['cid']);
                $this->deleteFields($draft['cid']);
                $deleteCount++;
            }
        }

        /** 设置提示信息 */
        Notice::alloc()
            ->set(
                $deleteCount > 0 ? _t('Bản nháp đã bị xóa') : _t('Không có bản nháp nào bị xóa'),
                $deleteCount > 0 ? 'success' : 'notice'
            );

        /** 返回原网页 */
        $this->response->goBack();
    }

    /**
     * 页面排序
     *
     * @throws \Typecho\Db\Exception
     */
    public function sortPage()
    {
        $pages = $this->request->filter('int')->getArray('cid');

        if ($pages) {
            foreach ($pages as $sort => $cid) {
                $this->db->query($this->db->update('table.contents')->rows(['order' => $sort + 1])
                    ->where('cid = ?', $cid));
            }
        }

        if (!$this->request->isAjax()) {
            /** 转向原页 */
            $this->response->goBack();
        } else {
            $this->response->throwJson(['success' => 1, 'message' => _t('Sắp xếp trang hoàn tất')]);
        }
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
        $this->on($this->request->is('do=publish') || $this->request->is('do=save'))->writePage();
        $this->on($this->request->is('do=delete'))->deletePage();
        $this->on($this->request->is('do=mark'))->markPage();
        $this->on($this->request->is('do=deleteDraft'))->deletePageDraft();
        $this->on($this->request->is('do=sort'))->sortPage();
        $this->response->redirect($this->options->adminUrl);
    }
}
