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

window.addEventListener('hashchange', openHash);

function openHash()
{
    // Alerts every time the hash changes!
    let hash = location.hash;
    $(hash).click();
    $(document).scrollTop($(hash).offset().top);
}

$(function () {
    // Trigger the event (useful on page load).
    openHash();
});
