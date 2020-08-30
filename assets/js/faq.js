import '../scss/faq.scss';

var faqs = jQuery('#faqs');
faqs.find("dd").hide();
faqs.find("dt").click(function (e) {
    e.preventDefault();
    jQuery(this).next("#faqs dd").slideToggle(500);

});

document.addEventListener('DOMContentLoaded', function () {
    jQuery('dt').on('click', function () {
        jQuery(this)
        .find('[data-fa-i2svg]')
        .toggleClass('fa-plus-circle')
        .toggleClass('fa-minus-circle');
    });
  });