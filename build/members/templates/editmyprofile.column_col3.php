<div class="row">
<form method="post" action="<?=$page_url?>" name="signup" id="profile-edit-form" class="fieldset-menu-form" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

require_once 'editprofile_form.php';

?>
</form>
</div>

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
        var sel1 = node1.cells[0].children[1];
        if (sel1.selectedIndex > 0)
        {
            var langval = sel1.options[sel1.selectedIndex].value;
            
            var removelink = document.createElement('a');
            removelink.appendChild(document.createTextNode('<?= $words->getSilent('RemoveLanguage')?>'));
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

            // Cleanup select2 states
            var cell2 = node1.cells[1];
            var mll = cell2.children[1];
            jQuery(sel1).select2("destroy");
            jQuery(mll).select2("destroy");

            var node2 = node1.cloneNode(true);
            node1.cells[0].removeChild(node1.cells[0].children[0]);
            node1.cells[0].appendChild(langid);
            node1.cells[0].appendChild(langname);
            node1.cells[2].appendChild(removelink);
            node1.cells[0].setStyle("vertical-align: middle;");
            node1.cells[2].setStyle("vertical-align: middle;");
            jQuery(mll).select2({dropdownAutoWidth: true, width: 'element'});

            iterator++;
            node2.setAttribute('id', 'lang'+iterator);
            node1.parentNode.appendChild(node2);
            var sel2 = node2.cells[0].children[0];
            for (var i = 0; sel2.options.length; i++)
            {
                if (sel2.options[i].value == langval)
                {
                    sel2.options[i].remove();
                    break;
                }
            }
            jQuery(sel2).select2({dropdownAutoWidth: true, width: 'element'});
            jQuery(node2.cells[1].children[0]).select2({dropdownAutoWidth: true, width: 'element'});
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
      var activeFieldset = '<?php if (!empty($vars['activeFieldset'])) { echo $vars['activeFieldset']; } ?>'; // Value inserted by PHP.
      if (activeFieldset == '') {
        var defaultFieldset = 'profilesummary';
        // Trim leading hashbang
        var hashValue = document.location.hash.replace('#!', '');
        if (hashValue == '') {
          activeFieldset = defaultFieldset;
        } else {
          /* This allows URLs like "/editmyprofile#!profileaccommodation",
           * which opens the "Accommodation" form tab after loading the page.
           * The hashbang value needs to match the ID of the fieldset that
           * is to be opened.
           */
          var tab = document.getElementById(hashValue);
          if (tab != null && tab.tagName.toLowerCase() == 'fieldset') {
            activeFieldset = hashValue;
          } else {
            activeFieldset = defaultFieldset;
          }
        }
      }
      new FieldsetMenu('profile-edit-form', {active: activeFieldset});
      $('langbutton').observe('click',insertNewTemplate);
      $$('a.remove_lang').each(function(a){
        a.observe('click', removeLang);
      });
    });

//-->
</script>
