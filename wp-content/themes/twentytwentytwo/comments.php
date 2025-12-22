<?php
/**
 * 评论模板
 *
 * @package Enterprise Theme
 */

// 如果当前文章需要密码保护，直接返回
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php
    // 如果有评论
    if (have_comments()) :
    ?>
        <h2 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            if ('1' === $comments_number) {
                printf(_x('1 条评论', 'comments title', 'enterprise-theme'));
            } else {
                printf(
                    _nx(
                        '%1$s 条评论',
                        '%1$s 条评论',
                        $comments_number,
                        'comments title',
                        'enterprise-theme'
                    ),
                    number_format_i18n($comments_number)
                );
            }
            ?>
        </h2>

        <?php
        // 评论导航（上一页/下一页）
        the_comments_navigation();
        ?>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
                'avatar_size' => 50,
                'callback' => 'enterprise_theme_comment_callback',
            ));
            ?>
        </ol>

        <?php
        // 评论导航（上一页/下一页）
        the_comments_navigation();

        // 如果评论已关闭
        if (!comments_open()) :
        ?>
            <p class="no-comments"><?php _e('评论已关闭。', 'enterprise-theme'); ?></p>
        <?php
        endif;

    endif; // 结束 have_comments() 检查

    // 显示评论表单
    comment_form(array(
        'title_reply' => __('发表评论', 'enterprise-theme'),
        'title_reply_to' => __('回复给 %s', 'enterprise-theme'),
        'cancel_reply_link' => __('取消回复', 'enterprise-theme'),
        'label_submit' => __('提交评论', 'enterprise-theme'),
        'comment_notes_before' => '<p class="comment-notes">' . __('您的电子邮件地址不会被公开。必填项已用 * 标注', 'enterprise-theme') . '</p>',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x('评论内容', 'noun') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required"></textarea></p>',
    ));
    ?>

</div><!-- #comments -->

<?php
/**
 * 自定义评论回调函数
 */
function enterprise_theme_comment_callback($comment, $args, $depth) {
    $tag = ('div' === $args['style']) ? 'div' : 'li';
?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class($comment->has_children ? 'parent' : '', $comment); ?>>
        <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
            <div class="comment-author vcard">
                <?php
                if (0 != $args['avatar_size']) {
                    echo get_avatar($comment, $args['avatar_size'], '', '', array('class' => 'comment-avatar'));
                }
                ?>
                <div class="comment-author-info">
                    <b class="fn"><?php comment_author_link($comment); ?></b>
                    <?php if ('0' == $comment->comment_approved) : ?>
                        <span class="comment-awaiting-moderation"><?php _e('您的评论正在等待审核。', 'enterprise-theme'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="comment-meta">
                <a href="<?php echo esc_url(get_comment_link($comment, $args)); ?>">
                    <time datetime="<?php comment_time('c'); ?>">
                        <?php
                        printf(
                            __('%1$s at %2$s', 'enterprise-theme'),
                            get_comment_date('', $comment),
                            get_comment_time()
                        );
                        ?>
                    </time>
                </a>
                <?php
                edit_comment_link(__('编辑', 'enterprise-theme'), '<span class="edit-link">', '</span>');
                ?>
            </div>

            <div class="comment-content">
                <?php comment_text(); ?>
            </div>

            <div class="comment-reply">
                <?php
                comment_reply_link(array_merge($args, array(
                    'add_below' => 'div-comment',
                    'depth' => $depth,
                    'max_depth' => $args['max_depth'],
                    'before' => '<span class="reply-link">',
                    'after' => '</span>',
                )));
                ?>
            </div>
        </article>
<?php
}
