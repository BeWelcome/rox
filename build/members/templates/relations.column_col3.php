<?php
$words = new MOD_words();
$purifier = (new MOD_htmlpure())->getBasicHtmlPurifier();
$member = $this->member;
$relations = $member->relations;
$username = $member->Username;
$myself = $this->myself;
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
?>
<h3><?php echo $words->get('MyRelations'); ?></h3>

<ul class="linklist">
<?php
foreach ($relations as $rel) {
    $comment = $words->mInTrad($rel->IdTradComment, $profile_language, true);

    // Hack to filter out accidental '0' or '123456' comments that were saved
    // by users while relation comment update form was buggy (see #1580)
    if (is_numeric($comment)) {
        $comment = '';
    }

    $rel->Comment = $purifier->purify($comment);
    require 'relation_item.php';
}
?>
</ul>
