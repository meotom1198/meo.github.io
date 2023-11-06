<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>

<?php
if (isset($post) || isset($page)) {
    $cid = isset($post) ? $post->cid : $page->cid;

    if ($cid) {
        \Widget\Contents\Attachment\Related::alloc(['parentId' => $cid])->to($attachment);
    } else {
        \Widget\Contents\Attachment\Unattached::alloc()->to($attachment);
    }
}
?>

<div id="upload-panel" class="p">
    <div class="upload-area" draggable="true"><?php _e('Kéo và thả tệp vào đây<br>hoặc %s chọn tệp tải lên%s', '<a href="###" class="upload-file">', '</a>'); ?></div>
    <ul id="file-list">
    <?php while ($attachment->next()): ?>
        <li data-cid="<?php $attachment->cid(); ?>" data-url="<?php echo $attachment->attachment->url; ?>" data-image="<?php echo $attachment->attachment->isImage ? 1 : 0; ?>"><input type="hidden" name="attachment[]" value="<?php $attachment->cid(); ?>" />
            <a class="insert" title="<?php _e('Nhấp để chèn tệp'); ?>" href="###"><?php $attachment->title(); ?></a>
            <div class="info">
                <?php echo number_format(ceil($attachment->attachment->size / 1024)); ?> Kb
                <a class="file" target="_blank" href="<?php $options->adminUrl('media.php?cid=' . $attachment->cid); ?>" title="<?php _e('Chỉnh sửa'); ?>"><i class="i-edit"></i></a>
                <a href="###" class="delete" title="<?php _e('Xóa bỏ'); ?>"><i class="i-delete"></i></a>
            </div>
        </li>
    <?php endwhile; ?>
    </ul>
</div>

