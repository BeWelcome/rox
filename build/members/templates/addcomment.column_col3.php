<?php
$words = $this->words;
$ww = $this->ww;
$layoutbits = new MOD_layoutbits();

$commenter = $this->loggedInMember->Username;
$member = $this->member;
$Username = $member->Username;

$syshcvol = PVars::getObj('syshcvol');
$ttc = $syshcvol->CommentRelations;
$max = count($ttc);
$comments = $member->get_comments_commenter($this->loggedInMember->id);
$TCom = (isset($comments[0])) ? $comments[0] : false;
$edit_mode = $TCom;

// values from previous form submit
if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
    // this is a fresh form
    $ttRelation = ($TCom) ? explode(',',$TCom->relations) : array();
    if ($this->commentGuidelinesRead) {
        $vars["CommentGuidelines"] = 'checked';
    }
} else {
    // last time something went wrong.
    // recover old form input.
    $vars = $mem_redirect->post;
    $TCom = new stdClass();
    $ttRelation = array();
    for ($ii = 0; $ii < $max; $ii++) {
        $chkName = "Comment_" . $ttc[$ii];
        if (isset($vars[$chkName])) {
            $ttRelation[] = $ttc[$ii];
        }
    }
    if (isset($vars['Quality'])) {
        $TCom->comQuality = $vars['Quality'];
    }
    if (isset($vars['TextWhere'])) {
        $TCom->TextWhere = $vars['TextWhere'];
    }
    if (isset($vars['TextFree'])) {
        $TCom->TextFree = $vars['TextFree'];
    }
    if (isset($vars['AllowEdit'])) {
        $TCom->AllowEdit = $vars['AllowEdit'];
    }
}

// Remove injected tags from old comments, so users don't have to edit HTML
// that they didn't write
$replacePatterns = array(
    '/<hr>/',
    '/<hr \/>/',
    '/<br>/',
    '/<br \/>/',
    '/<font color=gray><font size=1>(comment date .*)<\/font><\/font>/'
);
$replacements = array(
    "\n\n",
    "\n\n",
    "\n",
    "\n",
    '$1'
);
if (isset($TCom->TextFree)) {
    $textFreeWashed = preg_replace($replacePatterns, $replacements,
        $TCom->TextFree);
} else {
    $textFreeWashed = "";
}
if (isset($TCom->TextWhere)) {
    $textWhereWashed = preg_replace($replacePatterns, $replacements,
        $TCom->TextWhere);
} else {
    $textWhereWashed = "";
}

$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();

$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

$formkit = $this->layoutkit->formkit;
$random =  rand(1, 3);
$callbackFunction = "commentCallback";
$callbackFunction .= $random;
$callback_tag = $formkit->setPostCallback('MembersController', $callbackFunction);

?>


<?php
// Display errors from last submit
if (isset($vars['errors']) && !empty($vars['errors']))
{
    foreach ($vars['errors'] as $error)
    {
        echo '<div class="alert alert-danger w-100">'.$words->get($error).'</div>';
    }
}

// Display the form to propose to add a comment
?>
<?php
if (isset($TCom->comQuality) && $TCom->comQuality == "Bad" && $TCom->AllowEdit != 1) {
    echo '<h3 class="mt-3">' . $words->get("CantChangeNegative") . '</h3>' . $words->get("CantChangeNegative_Explanation");
} else {
?>
<?=$words->flushBuffer();?>


<form method="post" name="addcomment">
<?=$callback_tag ?>
    <?php if ($random == 2) { ?>
    <input type="text" id="sweet" name="sweet" value="" title="Leave free of content" hidden>
    <?php } ?>

    <?php /* <h1><?=(!$edit_mode) ? $words->get("AddComments") : $words->get("EditComments")?></h1> */ ?>

<input name="IdMember" value="<?=$member->id?>" type="hidden" />

    <div class="row mt-3">
        <div class="col-12">
            <h2><?=$words->get("CommentHeading" , $Username)?></h2>
            <div class="alert alert-info"><?=$words->get("FollowCommentGuidelines")?></div>
        </div>

        <div class="col-12 col-lg-6">
            <label class="m-0" for="Quality">
                <h5><?=$words->get("CommentQuality" , $Username)?></h5>
            </label>

            <a tabindex="0" class="btn btn-outline-primary btn-sm ml-1 py-0" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-trigger="focus" data-content="<?=$words->get("CommentQualityDescription", $Username, $Username, $Username)?>">
                <i class="fa fa-question"></i>
            </a>

            <select class="o-input select2 mb-2 mr-sm-2 mb-sm-0" name="Quality" id="Quality">
                <option value=""><?=$words->getSilent("CommentQuality_SelectOne")?></option>
                <option value="Good"
                    <?=(isset($TCom->comQuality) && $TCom->comQuality == "Good") ? " selected " : ""?>
                >
                    <?=$words->getSilent("CommentQuality_Good")?></option>
                <option value="Neutral"
                    <?=(isset($TCom->comQuality) && $TCom->comQuality == "Neutral") ? " selected " : ""?>
                >
                    <?=$words->getSilent("CommentQuality_Neutral")?></option>
                <option value="Bad"
                    <?=(isset($TCom->comQuality) && $TCom->comQuality == "Bad") ? " selected " : ""?>
                >
                    <?=$words->getSilent("CommentQuality_Bad")?></option>
            </select>

            <label class="m-0 mt-3" for="CommentLength">
                <h5><?=$words->get("CommentLength", $Username)?></h5>
            </label>
            <a tabindex="0" class="btn btn-outline-primary btn-sm ml-1 py-0" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-trigger="focus" data-content="<?php echo $words->get("CommentLengthDescription", $Username, $Username, $Username) ?>">
                <i class="fa fa-question"></i>
            </a>
            <?php
            for ($ii = 0; $ii < $max; $ii++) {
                $chkName = "Comment_" . $ttc[$ii];
                ?>
                <div class="o-checkbox my-2">
                    <label class="o-checkbox__label">
                        <input class="o-checkbox__input" type="checkbox" id="<?= $chkName; ?>" name="<?= $chkName; ?>"<?php if (in_array($ttc[$ii], $ttRelation)) echo ' checked'; ?>>
                        <?= $words->get($chkName); ?>
                    </label>
                </div>
            <?php } ?>

        </div>

        <div class="col-12 col-lg-6">
            <label for="Commenter" class="mb-1">
                <h5><?php echo $words->get("CommentsCommenter") ?></h5>
            </label>

            <a tabindex="0" class="btn btn-outline-primary btn-sm ml-1 py-0" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-trigger="focus" data-content="<?php echo $words->get("CommentsCommenterDescription", $Username) ?>">
                <i class="fa fa-question"></i>
            </a>

            <textarea name="TextFree" id="TextFree" class="w-100 o-input" rows="10"><?php echo $textFreeWashed; ?></textarea>
        </div>

        <div class="col-12">
            <div class="o-checkbox alertwarning">
                <label class="o-checkbox__label mb-2">
                    <input type="checkbox" name="CommentGuidelines" class="o-checkbox__input"
                        <?php
                        if (isset ($vars["CommentGuidelines"]))
                            echo ' checked="checked"';
                        echo '/>';
                        ?>
                    <?php echo $words->get('ConfirmationCommentGuidelines'); ?>
                </label>
            </div>

            <input type="hidden" value="<?php echo $member->id?>" name="cid">
            <input type="hidden" name="action" value="add">
            <input type="submit" class="btn btn-primary" id="submit" name="valide" value="<?php echo $words->getSilent('SubmitComment'); ?>"><?=$words->flushBuffer();?>
        </div>

    </div>

</form>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="popover"]').popover()
    })
    $('.popover-dismiss').popover({
        trigger: 'focus'
    })
</script>
<?php
}
$words->flushBuffer();


