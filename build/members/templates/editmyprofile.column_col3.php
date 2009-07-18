
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
/* no reason for an arbitrary limit
        var element = Event.element(event);
        if (iterator == 7) {
            Event.stopObserving(element, 'click', insertNewTemplate);
            element.disable;
        } */
        var node1 = $('lang'+iterator);
        var sel1 = node1.cells[0].firstChild;
        if (sel1.selectedIndex > 0)
        {
            var langval = sel1.options[sel1.selectedIndex].value;
            
            var removelink = document.createElement('a');
            removelink.appendChild(document.createTextNode('Remove'));
            removelink.setAttribute('href','#');
            $(removelink).observe('click', removeLang);
            var langid = document.createElement('input');
            langid.name = 'memberslanguages[]';
            langid.value = langval;
            langid.type = 'hidden';
            var langname = document.createElement('input');
            langname.value = sel1.options[sel1.selectedIndex].text;
            langname.type = 'text';
            langname.setAttribute('disabled', 'disabled');
            var node2 = node1.cloneNode(true);
            node1.cells[0].removeChild(sel1);
            node1.cells[0].appendChild(document.createTextNode(' '));
            node1.cells[0].appendChild(langid);
            node1.cells[0].appendChild(langname);
            node1.cells[2].appendChild(removelink);
            iterator++;
            node2.setAttribute('id', 'lang'+iterator);
            node1.parentNode.appendChild(node2);
            var sel2 = node2.cells[0].firstChild;
            for (var i = 0; sel2.options.length; i++)
            {
                if (sel2.options[i].value == langval)
                {
                    sel2.removeChild(sel2.options[i]);
                    break;
                }
            }
        }
    }

    function removeLang(e)
    {
        Event.stop(e);
        var eve = e || window.event;
        var elem = eve.target || eve.srcElement;
        var tr = elem.parentNode.parentNode;
        var lang = document.createElement('option');
        lang.value = tr.cells[0].getElementsByTagName('input')[0].value;
        lang.text = tr.cells[0].getElementsByTagName('input')[1].value;
        var sel = $$('select.lang_selector');
        sel[0].appendChild(lang); 
        tr.parentNode.removeChild(tr);
    }

    document.observe("dom:loaded", function() {
      //new FieldsetMenu('profile-edit-form', {active: "profilesummary"});
      $('langbutton').observe('click',insertNewTemplate);
      $$('a.remove_lang').each(function(a){
        a.observe('click', removeLang);
      });
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
