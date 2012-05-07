<?php
$words = new MOD_words();
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
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
    require 'relation_item.php';
}
?>
</ul>
