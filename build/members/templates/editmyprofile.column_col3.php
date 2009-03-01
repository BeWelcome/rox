
<form method="post" action="<?=$page_url?>" name="signup" id="profile" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

$this->editMyProfileFormContent($vars);

?>
</form>
<script type="text/javascript">//<!--
    function linkDropDown(event){
        var element = Event.element(event);
        var index = element.selectedIndex;
        var lang = element.options[index].value;
        window.location.href = http_baseuri + 'editmyprofile/' + lang;
    }

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
      new FieldsetMenu('profile-edit-form', {active: "profilesummary"});
      $('langbutton').observe('click',insertNewTemplate);
      $('add_language').observe('change',linkDropDown);
    });
//-->
</script>
            
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "ProfileSummary",
    plugins : "advimage",
    theme: "advanced",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,link,bullist,separator,justifyleft,justifycenter,justifyfull,bullist,numlist,forecolor,backcolor,image, charmap",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true

});
//-->
</script>
