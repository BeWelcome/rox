<?php
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('GroupsController', 'newPostCallback');
$vars = $this->getRedirectedMem('vars');
if (empty($vars))
{
    $vars['topic_text'] = '';
    $vars['topic_title'] = '';
    $vars['NotifyMe'] = false;
    $error['text'] = false;
    $error['title'] = false;
}
elseif (isset($vars['errors'])) {
    if (!isset($vars['NotifyMe'])) {
        $vars['NotifyMe'] = false;
    }
    $error['text'] = in_array('text', $vars['errors']);
    $error['title'] = in_array('title', $vars['errors']);
}
$notifyChecked = ($vars['NotifyMe']) ? 'checked="checked"' : '';
?>
<div class="row">
    <div class="col-12">
        <h3><?php echo $words->getFormatted("forum_new_topic"); ?></h3>
    </div>
    <div class="col-12">
        <form method="post" action="/group/<?= $this->group->id; ?>/new" name="editform" id="forumsform">
            <div class="row no-gutters">
                <?= $callback_tag ?>
                <input type="hidden" name="IdLanguage" id="IdLanguage" value="0">
                <div class="col-12">
                    <div class="form-group mb-2">
                        <label class="m-0"
                               for="topic_title"><?php echo $words->getFormatted("forum_label_topicTitle"); ?></label>
                            <input type="text" class="form-control <?= ($error['title']) ? 'is-invalid': ''; ?>" name="topic_title" maxlength="200" id="topic_title"
                                   value="<?= $vars['topic_title']; ?>" aria-describedby="forumaddtitle">
                        <div class="invalid-feedback">
                            <span class="form-error-icon badge badge-danger text-uppercase"><?= $words->get('Error'); ?></span>
                            <span class="form-error-message"><?= $words->getFormatted("forum_error_title"); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-2">
                    <div class="form-group">
                        <label for="topic_text"><?php echo $words->getFormatted("forum_label_text"); ?></label>

                        <textarea name="topic_text" id="topic_text" class="form-control editor <?= ($error['text']) ? 'is-invalid': ''; ?>" rows="10" style="min-height: 10em;" placeholder="<?= $words->get('forum.post.placeholder'); ?>" >
                            <?= $vars['topic_text']; ?>
                        </textarea>

                        <?php
                            echo '<input type="hidden" name="IdGroup" value="' . $this->group->id . '">';
                         ?>
                        <div class="invalid-feedback">
                            <span class="form-error-icon badge badge-danger text-uppercase"><?= $words->get('Error'); ?></span>
                            <span class="form-error-message"><?= $words->getFormatted("forum_error_post"); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 order-1 order-md-2 mb-1 px-1">
                    <div class="form-check">
                        <input type="checkbox" name="NotifyMe" id="NotifyMe" class="form-check-input" <?= $notifyChecked ?>>
                        <label for="NotifyMe" class="form-check-label"><?php echo $words->getFormatted("forum_NotifyMeForThisThread") ?></label>
                    </div>
                </div>

                <div class="col-12 col-md-4 order-2 order-md-3 mb-1 px-1">
                    <legend class="sr-only"><?= $words->getFormatted("forum_label_visibility") ?></legend>
                    <?php
                        echo $this->visibilityCheckbox;
                    ?>
                </div>

                <div class="col-12 col-md-4 order-3 order-md-1 mb-2">
                    <input type="submit" class="btn btn-primary" value="<?php echo $words->getFormatted("forum_label_create_topic"); ?>">
                </div>
            </div>
        </form>
    </div>
</div>