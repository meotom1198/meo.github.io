<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\Widget\Metas\Tag\Admin::alloc()->to($tags);
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">

            <div class="col-mb-12 col-tb-8" role="main">

                <form method="post" name="manage_tags" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('Chọn tất cả'); ?></i><input type="checkbox"
                                                                                   class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                        class="sr-only"><?php _e('Hoạt động'); ?></i><?php _e('Chọn mục'); ?> <i
                                        class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa các thẻ này không?'); ?>"
                                           href="<?php $security->index('/action/metas-tag-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                    <li><a lang="<?php _e('Việc làm mới các tab có thể mất nhiều thời gian, bạn có chắc chắn muốn làm mới các tab này không?'); ?>"
                                           href="<?php $security->index('/action/metas-tag-edit?do=refresh'); ?>"><?php _e('Làm mới'); ?></a>
                                    </li>
                                    <li class="multiline">
                                        <button type="button" class="btn btn-s merge"
                                                rel="<?php $security->index('/action/metas-tag-edit?do=merge'); ?>"><?php _e('Hợp nhất để'); ?></button>
                                        <input type="text" name="merge" class="text-s"/>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <ul class="typecho-list-notable tag-list clearfix">
                        <?php if ($tags->have()): ?>
                            <?php while ($tags->next()): ?>
                                <li class="size-<?php $tags->split(5, 10, 20, 30); ?>" id="<?php $tags->theId(); ?>">
                                    <input type="checkbox" value="<?php $tags->mid(); ?>" name="mid[]"/>
                                    <span
                                        rel="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>"><?php $tags->name(); ?></span>
                                    <a class="tag-edit-link"
                                       href="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>"><i
                                            class="i-edit"></i></a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <h6 class="typecho-list-table-title"><?php _e('Chưa có thẻ nào'); ?></h6>
                        <?php endif; ?>
                    </ul>
                    <input type="hidden" name="do" value="delete"/>
                </form>

            </div>
            <div class="col-mb-12 col-tb-4" role="form">
                <?php \Widget\Metas\Tag\Edit::alloc()->form()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
    (function () {
        $(document).ready(function () {

            $('.typecho-list-notable').tableSelectable({
                checkEl: 'input[type=checkbox]',
                rowEl: 'li',
                selectAllEl: '.typecho-table-select-all',
                actionEl: '.dropdown-menu a'
            });

            $('.btn-drop').dropdownMenu({
                btnEl: '.dropdown-toggle',
                menuEl: '.dropdown-menu'
            });

            $('.dropdown-menu button.merge').click(function () {
                var btn = $(this);
                btn.parents('form').attr('action', btn.attr('rel')).submit();
            });

            <?php if (isset($request->mid)): ?>
            $('.typecho-mini-panel').effect('highlight', '#AACB36');
            <?php endif; ?>
        });
    })();
</script>
<?php include 'footer.php'; ?>

