<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();
$pages = \Widget\Contents\Page\Admin::alloc();
?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12 typecho-list">
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
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa các trang này không?'); ?>"
                                           href="<?php $security->index('/action/contents-page-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php $security->index('/action/contents-page-edit?do=mark&status=publish'); ?>"><?php _e('Được đánh dấu là <strong>%s</strong>', _t('Công khai')); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php $security->index('/action/contents-page-edit?do=mark&status=hidden'); ?>"><?php _e('Được đánh dấu là <strong>%s</strong>', _t('Ẩn')); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="search" role="search">
                            <?php if ('' != $request->keywords): ?>
                                <a href="<?php $options->adminUrl('manage-pages.php'); ?>"><?php _e('&laquo; Hủy bộ lọc'); ?></a>
                            <?php endif; ?>
                            <input type="text" class="text-s" placeholder="<?php _e('Vui lòng nhập từ khóa'); ?>"
                                   value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>" name="keywords"/>
                            <button type="submit" class="btn btn-s"><?php _e('Lọc'); ?></button>
                        </div>
                    </form>
                </div><!-- end .typecho-list-operate -->

                <form method="post" name="manage_pages" class="operate-form">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20" class="kit-hidden-mb"/>
                                <col width="6%" class="kit-hidden-mb"/>
                                <col width="30%"/>
                                <col width="30%"/>
                                <col width="" class="kit-hidden-mb"/>
                                <col width="16%"/>
                            </colgroup>
                            <thead>
                            <tr class="nodrag">
                                <th class="kit-hidden-mb"></th>
                                <th class="kit-hidden-mb"></th>
                                <th><?php _e('Tiêu đề'); ?></th>
                                <th><?php _e('Viết tắt'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('Tác giả'); ?></th>
                                <th><?php _e('Ngày'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($pages->have()): ?>
                                <?php while ($pages->next()): ?>
                                    <tr id="<?php $pages->theId(); ?>">
                                        <td class="kit-hidden-mb"><input type="checkbox" value="<?php $pages->cid(); ?>"
                                                                         name="cid[]"/></td>
                                        <td class="kit-hidden-mb"><a
                                                href="<?php $options->adminUrl('manage-comments.php?cid=' . $pages->cid); ?>"
                                                class="balloon-button size-<?php echo \Typecho\Common::splitByCount($pages->commentsNum, 1, 10, 20, 50, 100); ?>"
                                                title="<?php $pages->commentsNum(); ?> <?php _e('Bình luận'); ?>"><?php $pages->commentsNum(); ?></a>
                                        </td>
                                        <td>
                                            <a href="<?php $options->adminUrl('write-page.php?cid=' . $pages->cid); ?>"><?php $pages->title(); ?></a>
                                            <?php
                                            if ($pages->hasSaved || 'page_draft' == $pages->type) {
                                                echo '<em class="status">' . _t('Bản nháp') . '</em>';
                                            }

                                            if ('hidden' == $pages->status) {
                                                echo '<em class="status">' . _t('Ẩn') . '</em>';
                                            }
                                            ?>
                                            <a href="<?php $options->adminUrl('write-page.php?cid=' . $pages->cid); ?>"
                                               title="<?php _e('Chỉnh sửa %s', htmlspecialchars($pages->title)); ?>"><i
                                                    class="i-edit"></i></a>
                                            <?php if ('page_draft' != $pages->type): ?>
                                                <a href="<?php $pages->permalink(); ?>"
                                                   title="<?php _e('Duyệt qua %s', htmlspecialchars($pages->title)); ?>"><i
                                                        class="i-exlink"></i></a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php $pages->slug(); ?></td>
                                        <td class="kit-hidden-mb"><?php $pages->author(); ?></td>
                                        <td>
                                            <?php if ($pages->hasSaved): ?>
                                                <span class="description">
                                <?php $modifyDate = new \Typecho\Date($pages->modified); ?>
                                <?php _e('Được lưu trong %s', $modifyDate->word()); ?>
                                </span>
                                            <?php else: ?>
                                                <?php $pages->dateWord(); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('Chưa có trang nào'); ?></h6>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div><!-- end .typecho-table-wrap -->
                </form><!-- end .operate-form -->
            </div><!-- end .typecho-list -->
        </div><!-- end .typecho-page-main -->
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>

<?php if (!isset($request->status) || 'publish' == $request->get('status')): ?>
    <script type="text/javascript">
        (function () {
            $(document).ready(function () {
                var table = $('.typecho-list-table').tableDnD({
                    onDrop: function () {
                        var ids = [];

                        $('input[type=checkbox]', table).each(function () {
                            ids.push($(this).val());
                        });

                        $.post('<?php $security->index('/action/contents-page-edit?do=sort'); ?>',
                            $.param({cid: ids}));
                    }
                });
            });
        })();
    </script>
<?php endif; ?>

<?php include 'footer.php'; ?>
