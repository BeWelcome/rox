<form method="post" action="<?=$page_url?>" name="signup" id="profile-edit-form" class="fieldset-menu-form w-100" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

require_once 'editprofile_form.php';

?>
</form>

<script type="text/javascript"><!--
    var iterator = 1;
    $(document).ready(function() {
        $('#langbutton').click( insertNewTemplate );
        $('a.remove_lang').click( removeLang );
        initLanguageSelect2s();
    });

    function initLanguageSelect2s()
    {
        $(".lang_selector").select2({
            theme: 'bootstrap',
            containerCssClass: 'form-control'
        });
        $(".mll").select2({
            theme: 'bootstrap',
            containerCssClass: 'form-control',
            minimumResultsForSearch: Infinity
        });
    }

    function destroyLanguageSelect2s()
    {
        let langSelector = $(".lang_selector");
        langSelector.select2('destroy');
        langSelector.removeAttr('data-select2-id');
        let mll = $(".mll");
        mll.select2('destroy');
        mll.removeAttr('data-select2-id');
    }

    function insertNewTemplate(e){
        e.preventDefault();
        destroyLanguageSelect2s();
        $("div.langsel:first").clone(true, true).insertAfter("div.langsel:last");
        $("div.langsel:last").removeClass('d-none');
        initLanguageSelect2s();
    }

    function removeLang(e)
    {
        e.preventDefault();
        alert('removeLang');
        /*
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
        */
    }
    //-->
</script>
