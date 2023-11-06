<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();
$posts = \Widget\Contents\Post\Admin::alloc();
$isAllPosts = ('on' == $request->get('__typecho_all_posts') || 'on' == \Typecho\Cookie::get('__typecho_all_posts'));
?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12 typecho-list">
                <div class="clearfix">
                    <ul class="typecho-option-tabs right">
                        <?php if ($user->pass('editor', true) && !isset($request->uid)): ?>
                            <li class="<?php if ($isAllPosts): ?> current<?php endif; ?>"><a
                                    href="<?php echo $request->makeUriByRequest('__typecho_all_posts=on&page=1'); ?>"><?php _e('Tất cả'); ?></a>
                            </li>
                            <li class="<?php if (!$isAllPosts): ?> current<?php endif; ?>"><a
                                    href="<?php echo $request->makeUriByRequest('__typecho_all_posts=off&page=1'); ?>"><?php _e('Của tôi'); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="typecho-option-tabs">
                        <li<?php if (!isset($request->status) || 'all' == $request->get('status')): ?> class="current"<?php endif; ?>>
                            <a href="<?php $options->adminUrl('manage-posts.php'
                                . (isset($request->uid) ? '?uid=' . $request->uid : '')); ?>"><?php _e('Có sẵn'); ?></a>
                        </li>
                        <li<?php if ('waiting' == $request->get('status')): ?> class="current"<?php endif; ?>><a
                                href="<?php $options->adminUrl('manage-posts.php?status=waiting'
                                    . (isset($request->uid) ? '&uid=' . $request->uid : '')); ?>"><?php _e('Chờ duyệt'); ?>
                                <?php if (!$isAllPosts && $stat->myWaitingPostsNum > 0 && !isset($request->uid)): ?>
                                    <span class="balloon"><?php $stat->myWaitingPostsNum(); ?></span>
                                <?php elseif ($isAllPosts && $stat->waitingPostsNum > 0 && !isset($request->uid)): ?>
                                    <span class="balloon"><?php $stat->waitingPostsNum(); ?></span>
                                <?php elseif (isset($request->uid) && $stat->currentWaitingPostsNum > 0): ?>
                                    <span class="balloon"><?php $stat->currentWaitingPostsNum(); ?></span>
                                <?php endif; ?>
                            </a></li>
                        <li<?php if ('draft' == $request->get('status')): ?> class="current"<?php endif; ?>><a
                                href="<?php $options->adminUrl('manage-posts.php?status=draft'
                                    . (isset($request->uid) ? '&uid=' . $request->uid : '')); ?>"><?php _e('Bản nháp'); ?>
                                <?php if (!$isAllPosts && $stat->myDraftPostsNum > 0 && !isset($request->uid)): ?>
                                    <span class="balloon"><?php $stat->myDraftPostsNum(); ?></span>
                                <?php elseif ($isAllPosts && $stat->draftPostsNum > 0 && !isset($request->uid)): ?>
                                    <span class="balloon"><?php $stat->draftPostsNum(); ?></span>
                                <?php elseif (isset($request->uid) && $stat->currentDraftPostsNum > 0): ?>
                                    <span class="balloon"><?php $stat->currentDraftPostsNum(); ?></span>
                                <?php endif; ?>
                            </a></li>
                    </ul>
                </div>

                <div class="typecho-list-operate clearfix">
                    <form method="get">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('Chọn tất cả'); ?></i><input type="checkbox"
                                                                                   class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                        class="sr-only"><?php _e('Hoạt động'); ?></i><?php _e('Chọn mục'); ?> <i
                                        class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa những bài viết này?'); ?>"
                                           href="<?php $security->index('/action/contents-post-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                    <?php if ($user->pass('editor', true)): ?>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=publish'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Công khai')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=waiting'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Chờ duyệt')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=hidden'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Ẩn')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=private'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Riêng tư')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="search" role="search">
                            <?php if ('' != $request->keywords || '' != $request->category): ?>
                                <a href="<?php $options->adminUrl('manage-posts.php'
                                    . (isset($request->status) || isset($request->uid) ? '?' .
                                        (isset($request->status) ? 'status=' . htmlspecialchars($request->get('status')) : '') .
                                        (isset($request->uid) ? '?uid=' . htmlspecialchars($request->get('uid')) : '') : '')); ?>"><?php _e('&laquo; Hủy bộ lọc'); ?></a>
                            <?php endif; ?>
                            <input type="text" class="text-s" placeholder="<?php _e('Vui lòng nhập từ khóa'); ?>"
                                   value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>" name="keywords"/>
                            <select name="category">
                                <option value=""><?php _e('Tất cả chuyên mục'); ?></option>
                                <?php \Widget\Metas\Category\Rows::alloc()->to($category); ?>
                                <?php while ($category->next()): ?>
                                    <option
                                        value="<?php $category->mid(); ?>"<?php if ($request->get('category') == $category->mid): ?> selected="true"<?php endif; ?>><?php $category->name(); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-s"><?php _e('Lọc'); ?></button>
                            <?php if (isset($request->uid)): ?>
                                <input type="hidden" value="<?php echo htmlspecialchars($request->get('uid')); ?>"
                                       name="uid"/>
                            <?php endif; ?>
                            <?php if (isset($request->status)): ?>
                                <input type="hidden" value="<?php echo htmlspecialchars($request->get('status')); ?>"
                                       name="status"/>
                            <?php endif; ?>
                        </div>
                    </form>
                </div><!-- end .typecho-list-operate -->

                <form method="post" name="manage_posts" class="operate-form">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20" class="kit-hidden-mb"/>
                                <col width="6%" class="kit-hidden-mb"/>
                                <col width="45%"/>
                                <col width="" class="kit-hidden-mb"/>
                                <col width="18%" class="kit-hidden-mb"/>
                                <col width="16%"/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th class="kit-hidden-mb"></th>
                                <th class="kit-hidden-mb"></th>
                                <th><?php _e('Tiêu đề'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('Tác giả'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('Chuyên mục'); ?></th>
                                <th><?php _e('Ngày'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($posts->have()): ?>
                                <?php while ($posts->next()): ?>
                                    <tr id="<?php $posts->theId(); ?>">
                                        <td class="kit-hidden-mb"><input type="checkbox" value="<?php $posts->cid(); ?>"
                                                                         name="cid[]"/></td>
                                        <td class="kit-hidden-mb"><a
                                                href="<?php $options->adminUrl('manage-comments.php?cid=' . ($posts->parentId ? $posts->parentId : $posts->cid)); ?>"
                                                class="balloon-button size-<?php echo \Typecho\Common::splitByCount($posts->commentsNum, 1, 10, 20, 50, 100); ?>"
                                                title="<?php $posts->commentsNum(); ?> <?php _e('Bình luận'); ?>"><?php $posts->commentsNum(); ?></a>
                                        </td>
                                        <td>
                                            <a href="<?php $options->adminUrl('write-post.php?cid=' . $posts->cid); ?>"><?php $posts->title(); ?></a>
                                            <?php
                                            if ($posts->hasSaved || 'post_draft' == $posts->type) {
                                                echo '<em class="status">' . _t('Bản nhap') . '</em>';
                                            }

                                            if ('hidden' == $posts->status) {
                                                echo '<em class="status">' . _t('Ẩn') . '</em>';
                                            } elseif ('waiting' == $posts->status) {
                                                echo '<em class="status">' . _t('Chờ duyệt') . '</em>';
                                            } elseif ('private' == $posts->status) {
                                                echo '<em class="status">' . _t('Riêng tư') . '</em>';
                                            } elseif ($posts->password) {
                                                echo '<em class="status">' . _t('Mật khẩu bảo vệ') . '</em>';
                                            }
                                            ?>
                                            <a href="<?php $options->adminUrl('write-post.php?cid=' . $posts->cid); ?>"
                                               title="<?php _e('Chỉnh sửa %s', htmlspecialchars($posts->title)); ?>"><i
                                                    class="i-edit"></i></a>
                                            <?php if ('post_draft' != $posts->type): ?>
                                                <a href="<?php $posts->permalink(); ?>"
                                                   title="<?php _e('Duyệt qua %s', htmlspecialchars($posts->title)); ?>"><i
                                                        class="i-exlink"></i></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="kit-hidden-mb"><a
                                                href="<?php $options->adminUrl('manage-posts.php?uid=' . $posts->author->uid); ?>"><?php $posts->author(); ?></a>
                                        </td>
                                        <td class="kit-hidden-mb"><?php $categories = $posts->categories;
                                            $length = count($categories); ?>
                                            <?php foreach ($categories as $key => $val): ?>
                                                <?php echo '<a href="';
                                                $options->adminUrl('manage-posts.php?category=' . $val['mid']
                                                    . (isset($request->uid) ? '&uid=' . $request->uid : '')
                                                    . (isset($request->status) ? '&status=' . $request->status : ''));
                                                echo '">' . $val['name'] . '</a>' . ($key < $length - 1 ? ', ' : ''); ?>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <?php if ($posts->hasSaved): ?>
                                                <span class="description">
                                <?php $modifyDate = new \Typecho\Date($posts->modified); ?>
                                <?php _e('Được lưu trong %s', $modifyDate->word()); ?>
                                </span>
                                            <?php else: ?>
                                                <?php $posts->dateWord(); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('Chưa có bài viết nào'); ?></h6>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form><!-- end .operate-form -->

                <div class="typecho-list-operate clearfix">
                    <form method="get">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('Chọn tất cả'); ?></i><input type="checkbox"
                                                                                   class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                        class="sr-only"><?php _e('Hoạt động'); ?></i><?php _e('Chọn mục'); ?> <i
                                        class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa những bài viết này?'); ?>"
                                           href="<?php $security->index('/action/contents-post-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                    <?php if ($user->pass('editor', true)): ?>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=publish'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Công khai')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=waiting'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Chờ duyệt')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=hidden'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Ẩn')); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php $security->index('/action/contents-post-edit?do=mark&status=private'); ?>"><?php _e('Đánh dấu là <strong>%s</strong>', _t('Riêng tư')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if ($posts->have()): ?>
                            <ul class="typecho-pager">
                                <?php $posts->pageNav(); ?>
                            </ul>
                        <?php endif; ?>
                    </form>
                </div><!-- end .typecho-list-operate -->
            </div><!-- end .typecho-list -->
        </div><!-- end .typecho-page-main -->
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>
