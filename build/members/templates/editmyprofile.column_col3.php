<form method="post" action="<?= $page_url ?>" name="signup" id="profile-edit-form" class="fieldset-menu-form row"
      enctype="multipart/form-data">
    <input type="hidden" name="memberid" value="<?= $member->id ?>"/>
    <input type="hidden" name="profile_language" value="<?= $profile_language ?>"/>
    <?php

    echo $callback_tag;

    require_once 'editprofile_form.php';

    ?>
</form>

<script type="text/javascript">
    var iterator = 1;

    $(document).ready(function () {
        $('input[type="range"]').rangeslider({
            polyfill: false,
            onInit: function () {
                updateValueOutput(this.value);
            },
            onSlide: function (pos, value) {
                updateValueOutput(value);
            }
        });

        window.addEventListener('hashchange', openHash);

        // Trigger the event (useful on page load).
        openHash();
    });

    $("input:radio[name='Accomodation']").change(function () {
        let value = document.forms.signup.Accomodation.value;
        switch (value) {
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

    function initLanguageSelect2s() {
        $(".mll,.lang_selector").each(function (i, obj) {
            if (!$(obj).hasClass("select2-hidden-accessible")) {
                $(obj).select2({
                    theme: 'bootstrap4',
                    width: 'auto'
                });
            }
        });
    }

    function destroyLanguageSelect2s() {
        $(".mll,.lang_selector").each(function (i, obj) {
            if ($(obj).hasClass("select2-hidden-accessible")) {
                $(obj).select2('destroy');
                $(obj).removeAttr('data-select2-id');
            }
        });
    }

    function insertNewTemplate(e) {
        e.preventDefault();
        destroyLanguageSelect2s();
        $("div.langsel:first").clone(true, true).insertAfter("div.langsel:last");
        $("div.langsel:last").removeClass('d-none');
        initLanguageSelect2s();
    }

    function removeLang(e) {
        e.preventDefault();
        let id = e.target.id;
        let languageId = $("#" + id + "_id").val();
        let languageName = $("#" + id + "_name").val();
        $('.lang_selector').append($('<option>', {value: languageId, text: languageName}));
        let row = "#" + id + "_row";
        $(row).remove();
    }

    $('#langbutton').click(insertNewTemplate);
    $('button.remove_lang').click(removeLang);
    destroyLanguageSelect2s();
    initLanguageSelect2s();

    $(function () {
        let maxDate = moment().subtract(18, "years");
        $("#birth-date").datetimepicker({
            format: 'YYYY-MM-DD',
            maxDate: maxDate,
            viewMode: 'years',
            keepInvalid: true
        });
    });

    function openHash()
    {
        // Alerts every time the hash changes!
        let hash = location.hash;
        $('[id^=collapse-]').removeClass('show').addClass('collapse');
        $(hash).addClass('show');
        $(document).scrollTop($(hash).offset().top);
    }
</script>
