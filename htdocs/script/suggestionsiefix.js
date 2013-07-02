jQuery('.no-checkedselector').on('change', 'input[type="radio"].vote', function () {
    if (this.checked) {
        jQuery('input[name="' + this.name + '"].checked').removeClass('checked');
        jQuery(this).addClass('checked');
        jQuery('#suggestion-vote-form').addClass('force-update').removeClass('force-update');
    }
});
jQuery('.no-checkedselector input[type="radio"].toggle:checked').addClass('checked');