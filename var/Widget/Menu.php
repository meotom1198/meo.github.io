<?php

namespace Widget;

use Typecho\Common;
use Widget\Plugins\Config;
use Widget\Themes\Files;
use Widget\Users\Edit as UsersEdit;
use Widget\Contents\Attachment\Edit as AttachmentEdit;
use Widget\Contents\Post\Edit as PostEdit;
use Widget\Contents\Page\Edit as PageEdit;
use Widget\Contents\Post\Admin as PostAdmin;
use Widget\Comments\Admin as CommentsAdmin;
use Widget\Metas\Category\Admin as CategoryAdmin;
use Widget\Metas\Category\Edit as CategoryEdit;
use Widget\Metas\Tag\Admin as TagAdmin;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 后台菜单显示
 *
 * @package Widget
 */
class Menu extends Base
{
    /**
     * 当前菜单标题
     * @var string
     */
    public $title;

    /**
     * 当前增加项目链接
     * @var string
     */
    public $addLink;

    /**
     * 父菜单列表
     *
     * @var array
     */
    private $menu = [];

    /**
     * 当前父菜单
     *
     * @var integer
     */
    private $currentParent = 1;

    /**
     * 当前子菜单
     *
     * @var integer
     */
    private $currentChild = 0;

    /**
     * 当前页面
     *
     * @var string
     */
    private $currentUrl;

    /**
     * 执行函数,初始化菜单
     */
    public function execute()
    {
        $parentNodes = [null, _t('Bảng điều khiển'), _t('Đăng bài viết'), _t('Quản lý'), _t('Cài đặt')];

        $childNodes = [
            [
                [_t('Đăng nhập'), _t('Đăng nhập vào %s', $this->options->title), 'login.php', 'visitor'],
                [_t('Đăng ký'), _t('Đăng ký %s', $this->options->title), 'register.php', 'visitor']
            ],
            [
                [_t('Tổng quan'), _t('Tổng quan về trang web'), 'index.php', 'subscriber'],
                [_t('Thiết lập cá nhân'), _t('Thiết lập cá nhân'), 'profile.php', 'subscriber'],
                [_t('Plugins'), _t('Quản lý plugin'), 'plugins.php', 'administrator'],
                [[Config::class, 'getMenuTitle'], [Config::class, 'getMenuTitle'], 'options-plugin.php?config=', 'administrator', true],
                [_t('Giao diện'), _t('Giao diện trang web'), 'themes.php', 'administrator'],
                [[Files::class, 'getMenuTitle'], [Files::class, 'getMenuTitle'], 'theme-editor.php', 'administrator', true],
                [_t('Thiết lập giao diện'), _t('Thiết lập giao diện'), 'options-theme.php', 'administrator', true],
                [_t('Sao lưu'), _t('Sao lưu'), 'backup.php', 'administrator'],
                [_t('Nâng cấp'), _t('Chương trình nâng cấp'), 'upgrade.php', 'administrator', true],
                [_t('Chào mừng'), _t('Chào mừng'), 'welcome.php', 'subscriber', true]
            ],
            [
                [_t('Đăng bài viết'), _t('Đăng bài viết'), 'write-post.php', 'contributor'],
                [[PostEdit::class, 'getMenuTitle'], [PostEdit::class, 'getMenuTitle'], 'write-post.php?cid=', 'contributor', true],
                [_t('Tạo trang'), _t('Tạo trang mới'), 'write-page.php', 'editor'],
                [[PageEdit::class, 'getMenuTitle'], [PageEdit::class, 'getMenuTitle'], 'write-page.php?cid=', 'editor', true],
            ],
            [
                [_t('Bài viết'), _t('Quản lý bài viết'), 'manage-posts.php', 'contributor', false, 'write-post.php'],
                [[PostAdmin::class, 'getMenuTitle'], [PostAdmin::class, 'getMenuTitle'], 'manage-posts.php?uid=', 'contributor', true],
                [_t('Trang độc lập'), _t('Quản lý các trang độc lập'), 'manage-pages.php', 'editor', false, 'write-page.php'],
                [_t('Bình luận'), _t('Quản lý bình luận'), 'manage-comments.php', 'contributor'],
                [[CommentsAdmin::class, 'getMenuTitle'], [CommentsAdmin::class, 'getMenuTitle'], 'manage-comments.php?cid=', 'contributor', true],
                [_t('Chuyên mục'), _t('Quản lý chuyên mục'), 'manage-categories.php', 'editor', false, 'category.php'],
                [_t('Thêm chuyên mục'), _t('Thêm chuyên mục'), 'category.php', 'editor', true],
                [[CategoryAdmin::class, 'getMenuTitle'], [CategoryAdmin::class, 'getMenuTitle'], 'manage-categories.php?parent=', 'editor', true, [CategoryAdmin::class, 'getAddLink']],
                [[CategoryEdit::class, 'getMenuTitle'], [CategoryEdit::class, 'getMenuTitle'], 'category.php?mid=', 'editor', true],
                [[CategoryEdit::class, 'getMenuTitle'], [CategoryEdit::class, 'getMenuTitle'], 'category.php?parent=', 'editor', true],
                [_t('Thẻ'), _t('Quản lý thẻ'), 'manage-tags.php', 'editor'],
                [[TagAdmin::class, 'getMenuTitle'], [TagAdmin::class, 'getMenuTitle'], 'manage-tags.php?mid=', 'editor', true],
                [_t('Tập tin'), _t('Quản lý tập tin'), 'manage-medias.php', 'editor'],
                [[AttachmentEdit::class, 'getMenuTitle'], [AttachmentEdit::class, 'getMenuTitle'], 'media.php?cid=', 'contributor', true],
                [_t('Thành viên'), _t('Quản lý thành viên'), 'manage-users.php', 'administrator', false, 'user.php'],
                [_t('Tạo tài khoản'), _t('Tạo tài khoản'), 'user.php', 'administrator', true],
                [[UsersEdit::class, 'getMenuTitle'], [UsersEdit::class, 'getMenuTitle'], 'user.php?uid=', 'administrator', true],
            ],
            [
                [_t('Cơ bản'), _t('Cài đặt cơ bản'), 'options-general.php', 'administrator'],
                [_t('Bình luận'), _t('Cài đặt bình luận'), 'options-discussion.php', 'administrator'],
                [_t('Đọc'), _t('Cài đặt đọc'), 'options-reading.php', 'administrator'],
                [_t('Liên kết'), _t('Cài đặt liên kết'), 'options-permalink.php', 'administrator'],
            ]
        ];

        /** 获取扩展菜单 */
        $panelTable = unserialize($this->options->panelTable);
        $extendingParentMenu = empty($panelTable['parent']) ? [] : $panelTable['parent'];
        $extendingChildMenu = empty($panelTable['child']) ? [] : $panelTable['child'];
        $currentUrl = $this->request->makeUriByRequest();
        $adminUrl = $this->options->adminUrl;
        $menu = [];
        $defaultChildeNode = [null, null, null, 'administrator', false, null];

        $currentUrlParts = parse_url($currentUrl);
        $currentUrlParams = [];
        if (!empty($currentUrlParts['query'])) {
            parse_str($currentUrlParts['query'], $currentUrlParams);
        }

        if ('/' == $currentUrlParts['path'][strlen($currentUrlParts['path']) - 1]) {
            $currentUrlParts['path'] .= 'index.php';
        }

        foreach ($extendingParentMenu as $key => $val) {
            $parentNodes[10 + $key] = $val;
        }

        foreach ($extendingChildMenu as $key => $val) {
            $childNodes[$key] = array_merge($childNodes[$key] ?? [], $val);
        }

        foreach ($parentNodes as $key => $parentNode) {
            // this is a simple struct than before
            $children = [];
            $showedChildrenCount = 0;
            $firstUrl = null;

            foreach ($childNodes[$key] as $inKey => $childNode) {
                // magic merge
                $childNode += $defaultChildeNode;
                [$name, $title, $url, $access] = $childNode;

                $hidden = $childNode[4] ?? false;
                $addLink = $childNode[5] ?? null;

                // 保存最原始的hidden信息
                $orgHidden = $hidden;

                // parse url
                $url = Common::url($url, $adminUrl);

                // compare url
                $urlParts = parse_url($url);
                $urlParams = [];
                if (!empty($urlParts['query'])) {
                    parse_str($urlParts['query'], $urlParams);
                }

                $validate = true;
                if ($urlParts['path'] != $currentUrlParts['path']) {
                    $validate = false;
                } else {
                    foreach ($urlParams as $paramName => $paramValue) {
                        if (!isset($currentUrlParams[$paramName])) {
                            $validate = false;
                            break;
                        }
                    }
                }

                if (
                    $validate
                    && basename($urlParts['path']) == 'extending.php'
                    && !empty($currentUrlParams['panel']) && !empty($urlParams['panel'])
                    && $urlParams['panel'] != $currentUrlParams['panel']
                ) {
                    $validate = false;
                }

                if ($hidden && $validate) {
                    $hidden = false;
                }

                if (!$hidden && !$this->user->pass($access, true)) {
                    $hidden = true;
                }

                if (!$hidden) {
                    $showedChildrenCount++;

                    if (empty($firstUrl)) {
                        $firstUrl = $url;
                    }

                    if (is_array($name)) {
                        [$widget, $method] = $name;
                        $name = self::widget($widget)->$method();
                    }

                    if (is_array($title)) {
                        [$widget, $method] = $title;
                        $title = self::widget($widget)->$method();
                    }

                    if (is_array($addLink)) {
                        [$widget, $method] = $addLink;
                        $addLink = self::widget($widget)->$method();
                    }
                }

                if ($validate) {
                    if ('visitor' != $access) {
                        $this->user->pass($access);
                    }

                    $this->currentParent = $key;
                    $this->currentChild = $inKey;
                    $this->title = $title;
                    $this->addLink = $addLink ? Common::url($addLink, $adminUrl) : null;
                }

                $children[$inKey] = [
                    $name,
                    $title,
                    $url,
                    $access,
                    $hidden,
                    $addLink,
                    $orgHidden
                ];
            }

            $menu[$key] = [$parentNode, $showedChildrenCount > 0, $firstUrl, $children];
        }

        $this->menu = $menu;
        $this->currentUrl = $currentUrl;
    }

    /**
     * 获取当前菜单
     *
     * @return array
     */
    public function getCurrentMenu(): ?array
    {
        return $this->currentParent > 0 ? $this->menu[$this->currentParent][3][$this->currentChild] : null;
    }

    /**
     * 输出父级菜单
     */
    public function output($class = 'focus', $childClass = 'focus')
    {
        foreach ($this->menu as $key => $node) {
            if (!$node[1] || !$key) {
                continue;
            }

            echo "<ul class=\"root" . ($key == $this->currentParent ? ' ' . $class : null)
                . "\"><li class=\"parent\"><a href=\"{$node[2]}\">{$node[0]}</a>"
                . "</li><ul class=\"child\">";

            $last = 0;
            foreach ($node[3] as $inKey => $inNode) {
                if (!$inNode[4]) {
                    $last = $inKey;
                }
            }

            foreach ($node[3] as $inKey => $inNode) {
                if ($inNode[4]) {
                    continue;
                }

                $classes = [];
                if ($key == $this->currentParent && $inKey == $this->currentChild) {
                    $classes[] = $childClass;
                } elseif ($inNode[6]) {
                    continue;
                }

                if ($inKey == $last) {
                    $classes[] = 'last';
                }

                echo "<li" . (!empty($classes) ? ' class="' . implode(' ', $classes) . '"' : null) . "><a href=\""
                    . ($key == $this->currentParent && $inKey == $this->currentChild ? $this->currentUrl : $inNode[2])
                    . "\">{$inNode[0]}</a></li>";
            }

            echo "</ul></ul>";
        }
    }
}
