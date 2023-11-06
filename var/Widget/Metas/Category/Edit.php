<?php

namespace Widget\Metas\Category;

use Typecho\Common;
use Typecho\Db\Exception;
use Typecho\Validate;
use Typecho\Widget\Helper\Form;
use Widget\Base\Metas;
use Widget\ActionInterface;
use Widget\Notice;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑分类组件
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Edit extends Metas implements ActionInterface
{
    /**
     * 入口函数
     */
    public function execute()
    {
        /** 编辑以上权限 */
        $this->user->pass('editor');
    }

    /**
     * 判断分类是否存在
     *
     * @param integer $mid 分类主键
     * @return boolean
     * @throws Exception
     */
    public function categoryExists(int $mid): bool
    {
        $category = $this->db->fetchRow($this->db->select()
            ->from('table.metas')
            ->where('type = ?', 'category')
            ->where('mid = ?', $mid)->limit(1));

        return (bool)$category;
    }

    /**
     * 判断分类名称是否存在
     *
     * @param string $name 分类名称
     * @return boolean
     * @throws Exception
     */
    public function nameExists(string $name): bool
    {
        $select = $this->db->select()
            ->from('table.metas')
            ->where('type = ?', 'category')
            ->where('name = ?', $name)
            ->limit(1);

        if ($this->request->mid) {
            $select->where('mid <> ?', $this->request->mid);
        }

        $category = $this->db->fetchRow($select);
        return !$category;
    }

    /**
     * 判断分类名转换到缩略名后是否合法
     *
     * @param string $name 分类名
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
     * 判断分类缩略名是否存在
     *
     * @param string $slug 缩略名
     * @return boolean
     * @throws Exception
     */
    public function slugExists(string $slug): bool
    {
        $select = $this->db->select()
            ->from('table.metas')
            ->where('type = ?', 'category')
            ->where('slug = ?', Common::slugName($slug))
            ->limit(1);

        if ($this->request->mid) {
            $select->where('mid <> ?', $this->request->mid);
        }

        $category = $this->db->fetchRow($select);
        return !$category;
    }

    /**
     * 增加分类
     *
     * @throws Exception
     */
    public function insertCategory()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $category = $this->request->from('name', 'slug', 'description', 'parent');

        $category['slug'] = Common::slugName(empty($category['slug']) ? $category['name'] : $category['slug']);
        $category['type'] = 'category';
        $category['order'] = $this->getMaxOrder('category', $category['parent']) + 1;

        /** 插入数据 */
        $category['mid'] = $this->insert($category);
        $this->push($category);

        /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()->set(
            _t('Danh mục <a href="%s">%s</a> đã được thêm vào', $this->permalink, $this->name),
            'success'
        );

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-categories.php'
            . ($category['parent'] ? '?parent=' . $category['parent'] : ''), $this->options->adminUrl));
    }

    /**
     * 生成表单
     *
     * @param string|null $action 表单动作
     * @return Form
     * @throws Exception
     */
    public function form(?string $action = null): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/metas-category-edit'), Form::POST_METHOD);

        /** 分类名称 */
        $name = new Form\Element\Text('name', null, null, _t('Tên chuyên mục') . ' *');
        $form->addInput($name);

        /** 分类缩略名 */
        $slug = new Form\Element\Text(
            'slug',
            null,
            null,
            _t('Chuyên mục viết tắt'),
            _t('Viết tắt danh mục được sử dụng để tạo một dạng liên kết thân thiện, các chữ cái, số, dấu gạch dưới và dấu gạch ngang được khuyến nghị.')
        );
        $form->addInput($slug);

        /** 父级分类 */
        $options = [0 => _t('Không chọn')];
        $parents = Rows::allocWithAlias(
            'options',
            (isset($this->request->mid) ? 'ignore=' . $this->request->mid : '')
        );

        while ($parents->next()) {
            $options[$parents->mid] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $parents->levels) . $parents->name;
        }

        $parent = new Form\Element\Select(
            'parent',
            $options,
            $this->request->parent,
            _t('Chuyên mục mẹ'),
            _t('Thể loại này sẽ được nộp dưới thể loại cha mẹ của sự lựa chọn của bạn.')
        );
        $form->addInput($parent);

        /** 分类描述 */
        $description = new Form\Element\Textarea(
            'description',
            null,
            null,
            _t('Mô tả chuyên mục'),
            _t('Văn bản này được sử dụng để mô tả danh mục và nó sẽ được hiển thị trong một số chủ đề.')
        );
        $form->addInput($description);

        /** 分类动作 */
        $do = new Form\Element\Hidden('do');
        $form->addInput($do);

        /** 分类主键 */
        $mid = new Form\Element\Hidden('mid');
        $form->addInput($mid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if (isset($this->request->mid) && 'insert' != $action) {
            /** 更新模式 */
            $meta = $this->db->fetchRow($this->select()
                ->where('mid = ?', $this->request->mid)
                ->where('type = ?', 'category')->limit(1));

            if (!$meta) {
                $this->response->redirect(Common::url('manage-categories.php', $this->options->adminUrl));
            }

            $name->value($meta['name']);
            $slug->value($meta['slug']);
            $parent->value($meta['parent']);
            $description->value($meta['description']);
            $do->value('update');
            $mid->value($meta['mid']);
            $submit->value(_t('Chỉnh sửa danh mục'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('Thêm chuyên mục'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $name->addRule('required', _t('Tên chuyên mục là bắt buộc'));
            $name->addRule([$this, 'nameExists'], _t('Tên chuyên mục đã tồn tại'));
            $name->addRule([$this, 'nameToSlug'], _t('Không thể chuyển đổi tên chuyên mục thành tên viết tắt'));
            $name->addRule('xssCheck', _t('Vui lòng không sử dụng ký tự đặc biệt trong tên chuyên mục'));
            $slug->addRule([$this, 'slugExists'], _t('Viết tắt đã tồn tại'));
            $slug->addRule('xssCheck', _t('Vui lòng không sử dụng ký tự đặc biệt trong từ viết tắt'));
        }

        if ('update' == $action) {
            $mid->addRule('required', _t('Khóa chính của danh mục không tồn tại'));
            $mid->addRule([$this, 'categoryExists'], _t('Chuyên mục không tồn tại'));
        }

        return $form;
    }

    /**
     * 更新分类
     *
     * @throws Exception
     */
    public function updateCategory()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $category = $this->request->from('name', 'slug', 'description', 'parent');
        $category['mid'] = $this->request->mid;
        $category['slug'] = Common::slugName(empty($category['slug']) ? $category['name'] : $category['slug']);
        $category['type'] = 'category';
        $current = $this->db->fetchRow($this->select()->where('mid = ?', $category['mid']));

        if ($current['parent'] != $category['parent']) {
            $parent = $this->db->fetchRow($this->select()->where('mid = ?', $category['parent']));

            if ($parent['mid'] == $category['mid']) {
                $category['order'] = $parent['order'];
                $this->update([
                    'parent' => $current['parent'],
                    'order'  => $current['order']
                ], $this->db->sql()->where('mid = ?', $parent['mid']));
            } else {
                $category['order'] = $this->getMaxOrder('category', $category['parent']) + 1;
            }
        }

        /** 更新数据 */
        $this->update($category, $this->db->sql()->where('mid = ?', $this->request->filter('int')->mid));
        $this->push($category);

        /** 设置高亮 */
        Notice::alloc()->highlight($this->theId);

        /** 提示信息 */
        Notice::alloc()
            ->set(_t('Danh mục <a href="%s">%s</a> đã được cập nhật', $this->permalink, $this->name), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-categories.php'
            . ($category['parent'] ? '?parent=' . $category['parent'] : ''), $this->options->adminUrl));
    }

    /**
     * 删除分类
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public function deleteCategory()
    {
        $categories = $this->request->filter('int')->getArray('mid');
        $deleteCount = 0;

        foreach ($categories as $category) {
            $parent = $this->db->fetchObject($this->select()->where('mid = ?', $category))->parent;

            if ($this->delete($this->db->sql()->where('mid = ?', $category))) {
                $this->db->query($this->db->delete('table.relationships')->where('mid = ?', $category));
                $this->update(['parent' => $parent], $this->db->sql()->where('parent = ?', $category));
                $deleteCount++;
            }
        }

        /** 提示信息 */
        Notice::alloc()
            ->set($deleteCount > 0 ? _t('Chuyên mục đã bị xóa') : _t('Không có chuyên mục nào bị xóa'), $deleteCount > 0 ? 'success' : 'notice');

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 合并分类
     */
    public function mergeCategory()
    {
        /** 验证数据 */
        $validator = new Validate();
        $validator->addRule('merge', 'required', _t('Khóa chính của danh mục không tồn tại'));
        $validator->addRule('merge', [$this, 'categoryExists'], _t('Vui lòng chọn chuyên mục sẽ được hợp nhất'));

        if ($error = $validator->run($this->request->from('merge'))) {
            Notice::alloc()->set($error, 'error');
            $this->response->goBack();
        }

        $merge = $this->request->merge;
        $categories = $this->request->filter('int')->getArray('mid');

        if ($categories) {
            $this->merge($merge, 'category', $categories);

            /** 提示信息 */
            Notice::alloc()->set(_t('Chuyên mục đã được hợp nhất'), 'success');
        } else {
            Notice::alloc()->set(_t('Không có chuyên mục nào được chọn'), 'notice');
        }

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 分类排序
     */
    public function sortCategory()
    {
        $categories = $this->request->filter('int')->getArray('mid');
        if ($categories) {
            $this->sort($categories, 'category');
        }

        if (!$this->request->isAjax()) {
            /** 转向原页 */
            $this->response->redirect(Common::url('manage-categories.php', $this->options->adminUrl));
        } else {
            $this->response->throwJson(['success' => 1, 'message' => _t('Việc sắp xếp được thực hiện')]);
        }
    }

    /**
     * 刷新分类
     *
     * @throws Exception
     */
    public function refreshCategory()
    {
        $categories = $this->request->filter('int')->getArray('mid');
        if ($categories) {
            foreach ($categories as $category) {
                $this->refreshCountByTypeAndStatus($category, 'post', 'publish');
            }

            Notice::alloc()->set(_t('Làm mới chuyên mục đã hoàn thành'), 'success');
        } else {
            Notice::alloc()->set(_t('Không có chuyên mục nào được chọn'), 'notice');
        }

        /** 转向原页 */
        $this->response->goBack();
    }

    /**
     * 设置默认分类
     *
     * @throws Exception
     */
    public function defaultCategory()
    {
        /** 验证数据 */
        $validator = new Validate();
        $validator->addRule('mid', 'required', _t('Khóa chính của chuyên mục không tồn tại'));
        $validator->addRule('mid', [$this, 'categoryExists'], _t('Chuyên mục không tồn tại'));

        if ($error = $validator->run($this->request->from('mid'))) {
            Notice::alloc()->set($error, 'error');
        } else {
            $this->db->query($this->db->update('table.options')
                ->rows(['value' => $this->request->mid])
                ->where('name = ?', 'defaultCategory'));

            $this->db->fetchRow($this->select()->where('mid = ?', $this->request->mid)
                ->where('type = ?', 'category')->limit(1), [$this, 'push']);

            /** 设置高亮 */
            Notice::alloc()->highlight($this->theId);

            /** 提示信息 */
            Notice::alloc()->set(
                _t('<a href="%s">%s</a> đã được đặt làm danh mục mặc định', $this->permalink, $this->name),
                'success'
            );
        }

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-categories.php', $this->options->adminUrl));
    }

    /**
     * 获取菜单标题
     *
     * @return string|null
     * @throws \Typecho\Widget\Exception|Exception
     */
    public function getMenuTitle(): ?string
    {
        if (isset($this->request->mid)) {
            $category = $this->db->fetchRow($this->select()
                ->where('type = ? AND mid = ?', 'category', $this->request->mid));

            if (!empty($category)) {
                return _t('Chỉnh sửa danh mục %s', $category['name']);
            }

        }
        if (isset($this->request->parent)) {
            $category = $this->db->fetchRow($this->select()
                ->where('type = ? AND mid = ?', 'category', $this->request->parent));

            if (!empty($category)) {
                return _t('Thêm danh mục phụ của %s', $category['name']);
            }

        } else {
            return null;
        }

        throw new \Typecho\Widget\Exception(_t('Chuyên mục không tồn tại'), 404);
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
        $this->on($this->request->is('do=insert'))->insertCategory();
        $this->on($this->request->is('do=update'))->updateCategory();
        $this->on($this->request->is('do=delete'))->deleteCategory();
        $this->on($this->request->is('do=merge'))->mergeCategory();
        $this->on($this->request->is('do=sort'))->sortCategory();
        $this->on($this->request->is('do=refresh'))->refreshCategory();
        $this->on($this->request->is('do=default'))->defaultCategory();
        $this->response->redirect($this->options->adminUrl);
    }
}
