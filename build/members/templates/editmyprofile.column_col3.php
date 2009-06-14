
<form method="post" action="<?=$page_url?>" name="signup" id="profile" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

require_once 'editprofile_form.php';

?>
</form>
<script type="text/javascript">//<!--
    var iterator = 1;
    function insertNewTemplate(event){
        var element = Event.element(event);
        if (iterator == 7) {
            Event.stopObserving(element, 'click', insertNewTemplate);
            element.disable;
        }
        var node1 = $('lang'+iterator);
        var node2 = node1.cloneNode(true);
        iterator++;
        node2.setAttribute('id', 'lang'+iterator);
        node1.parentNode.appendChild(node2);
    }

    document.observe("dom:loaded", function() {
      //new FieldsetMenu('profile-edit-form', {active: "profilesummary"});
      $('langbutton').observe('click',insertNewTemplate);
    });
//-->
</script>
            
<script type="text/javascript">//<!--
/*bkLib.onDomLoaded(function() {
	new nicEditor({iconsPath: 'script/nicEditorIcons.gif', buttonList: ['bold','italic','underline','left','center','right','ol','ul','strikethrough','removeformat','hr','forecolor','link','fontFamily','fontFormat','xhtml']}).panelInstance('ProfileSummary');
});	*/
//-->
</script>
</div>
