<form method="post" action="<?=$page_url?>" name="signup" id="profile-edit-form" class="fieldset-menu-form w-100" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

$this->addLateLoadScriptFile('build/tempusdominus.js');

require_once 'editprofile_form.php';

?>
</form>

<script type="text/javascript"><!--
    var iterator = 1;

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
        let id = e.target.id;
        let languageId = $("#" + id + "_id").val();
        let languageName = $("#" + id + "_name").val();
        $('.lang_selector').append($('<option>', {value: languageId, text: languageName}));
        let row = "#" + id + "_row";
        $(row).remove();
    }

    $(document).ready(function() {
        $('#langbutton').click( insertNewTemplate );
        $('button.remove_lang').click( removeLang );
        initLanguageSelect2s();

        let minDate = moment();
        let maxDate = moment().add(30, 'days');
        $("#hes-duration-div").datetimepicker({
            format: 'YYYY-MM-DD',
            minDate: minDate,
            maxDate: maxDate,
            keepInvalid: true
        });
    });

</script>
