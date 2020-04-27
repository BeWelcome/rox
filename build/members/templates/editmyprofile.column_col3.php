<form method="post" action="<?=$page_url?>" name="signup" id="profile-edit-form" class="fieldset-menu-form row" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

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

        $('input[type="range"]').rangeslider({
            polyfill: false,
            onInit: function() {
                updateValueOutput(this.value);
            },
            onSlide: function(pos, value) {
                updateValueOutput(value);
            }
        });
    });

    $( "input:radio[name='Accomodation']" ).change(function() {
        let value = document.forms.signup.Accomodation.value;
        switch(value) {
            case 'neverask':
                $('#hi_block').addClass('d-none');
                break;
            case 'anytime':
                $('#hi_block').removeClass('d-none');
                break;
        }
    });

    let markers = [
        "<?= $words->get('Please set your hosting interest') ?>",
        "<?= $words->get('Very low') ?>",
        "<?= $words->get('low') ?>",
        "<?= $words->get('lower') ?>",
        "<?= $words->get('low to medium') ?>",
        "<?= $words->get('medium') ?>",
        "<?= $words->get('medium to high') ?>",
        "<?= $words->get('high') ?>",
        "<?= $words->get('higher') ?>",
        "<?= $words->get('very high') ?>",
        "<?= $words->get('can\'t wait') ?>"
    ];

    function updateValueOutput(value) {
        let $valueOutput = $('.rangeslider__value-output');
        if ($valueOutput.length) {
            $valueOutput[0].innerHTML = markers[value];
        }
    }

</script>
