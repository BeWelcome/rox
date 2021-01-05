<form method="post" action="<?=$page_url?>" name="signup" id="profile-edit-form" class="fieldset-menu-form row" enctype="multipart/form-data">
<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
<?php

echo $callback_tag;

require_once 'editprofile_form.php';

?>
</form>

<script type="text/javascript">
    var iterator = 1;

    $(document).ready(function() {
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
