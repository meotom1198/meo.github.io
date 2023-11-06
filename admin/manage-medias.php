<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = \Widget\Stat::alloc();
$attachments = \Widget\Contents\Attachment\Admin::alloc();
?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">

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
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa các tệp này không?'); ?>"
                                           href="<?php $security->index('/action/contents-attachment-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                </ul>
                                <button class="btn btn-s btn-warn btn-operate"
                                        href="<?php $security->index('/action/contents-attachment-edit?do=clear'); ?>"
                                        lang="<?php _e('Bạn có chắc chắn muốn dọn dẹp các tệp không được lưu trữ không?'); ?>"><?php _e('Dọn dẹp ngay'); ?></button>
                            </div>
                        </div>
                        <div class="search" role="search">
                            <?php if ('' != $request->keywords): ?>
                                <a href="<?php $options->adminUrl('manage-medias.php'); ?>"><?php _e('&laquo; hủy bộ lọc'); ?></a>
                            <?php endif; ?>
                            <input type="text" class="text-s" placeholder="<?php _e('Vui lòng nhập từ khóa'); ?>"
                                   value="<?php echo htmlspecialchars($request->keywords ?? ''); ?>"<?php if ('' == $request->keywords): ?> onclick="value='';name='keywords';" <?php else: ?> name="keywords"<?php endif; ?>/>
                            <button type="submit" class="btn btn-s"><?php _e('Lọc'); ?></button>
                        </div>
                    </form>
                </div><!-- end .typecho-list-operate -->

                <form method="post" name="manage_medias" class="operate-form">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table draggable">
                            <colgroup>
                                <col width="20" class="kit-hidden-mb"/>
                                <col width="6%" class="kit-hidden-mb"/>
                                <col width="30%"/>
                                <col width="" class="kit-hidden-mb"/>
                                <col width="30%" class="kit-hidden-mb"/>
                                <col width="16%"/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th class="kit-hidden-mb"></th>
                                <th class="kit-hidden-mb"></th>
                                <th><?php _e('Tên tập tin'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('Upload bởi'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('Bài viết mà nó thuộc về '); ?></th>
                                <th><?php _e('Ngày upload'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($attachments->have()): ?>
                                <?php while ($attachments->next()): ?>
                                    <?php $mime = \Typecho\Common::mimeIconType($attachments->attachment->mime); ?>
                                    <tr id="<?php $attachments->theId(); ?>">
                                        <td class="kit-hidden-mb"><input type="checkbox"
                                                                         value="<?php $attachments->cid(); ?>"
                                                                         name="cid[]"/></td>
                                        <td class="kit-hidden-mb"><a
                                                href="<?php $options->adminUrl('manage-comments.php?cid=' . $attachments->cid); ?>"
                                                class="balloon-button size-<?php echo \Typecho\Common::splitByCount($attachments->commentsNum, 1, 10, 20, 50, 100); ?>"><?php $attachments->commentsNum(); ?></a>
                                        </td>
                                        <td>
                                            <i class="mime-<?php echo $mime; ?>"></i>
                                            <a href="<?php $options->adminUrl('media.php?cid=' . $attachments->cid); ?>"><?php $attachments->title(); ?></a>
                                            <a href="<?php $attachments->permalink(); ?>"
                                               title="<?php _e('Duyệt qua %s', $attachments->title); ?>"><i
                                                    class="i-exlink"></i></a>
                                        </td>
                                        <td class="kit-hidden-mb"><?php $attachments->author(); ?></td>
                                        <td class="kit-hidden-mb">
                                            <?php if ($attachments->parentPost->cid): ?>
                                                <a href="<?php $options->adminUrl('write-' . (0 === strpos($attachments->parentPost->type, 'post') ? 'post' : 'page') . '.php?cid=' . $attachments->parentPost->cid); ?>"><?php $attachments->parentPost->title(); ?></a>
                                            <?php else: ?>
                                                <span class="description"><?php _e('Không lưu trữ'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php $attachments->dateWord(); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('Chưa có tập tin nào'); ?></h6>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table><!-- end .typecho-list-table -->
                    </div><!-- end .typecho-table-wrap -->
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
                                    <li><a lang="<?php _e('Bạn có chắc chắn muốn xóa các tệp này không?'); ?>"
                                           href="<?php $security->index('/action/contents-attachment-edit?do=delete'); ?>"><?php _e('Xóa bỏ'); ?></a>
                                    </li>
                                </ul>
                            </div>
                            <button class="btn btn-s btn-warn btn-operate"
                                    href="<?php $security->index('/action/contents-attachment-edit?do=clear'); ?>"
                                    lang="<?php _e('Bạn có chắc chắn muốn dọn dẹp các tệp không được lưu trữ không?'); ?>"><?php _e('Dọn dẹp ngay'); ?></button>
                        </div>
                        <?php if ($attachments->have()): ?>
                            <ul class="typecho-pager">
                                <?php $attachments->pageNav(); ?>
                            </ul>
                        <?php endif; ?>
                    </form>
                </div><!-- end .typecho-list-operate -->

            </div>
        </div><!-- end .typecho-page-main -->
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>
