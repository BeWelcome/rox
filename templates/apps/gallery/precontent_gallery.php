<?php
/* 
//  This is the output above a user's gallery, the title so to say
// @author: lupochen
*/
$User = APP_User::login();
$words = new MOD_words();
?>

<h2 id="g-title"><?=$g->title ?></h2>
<?php if ($User && $User->getId() == $g->user_id_foreign) {
?>
<script type="text/javascript">
new Ajax.InPlaceEditor('g-title', 'gallery/ajax/set/', {
        callback: function(form, value) {
            return '?item=<?=$g->id?>&title=' + escape(value)
        },
        ajaxOptions: {method: 'get'}
    })
</script>
<?php } ?>