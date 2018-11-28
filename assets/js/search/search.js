$(function () {
    $(".img-check").click(function(){
        $(this).toggleClass("checked").toggleClass("not_checked");
    });
    $(".advanced").click(function(){
        $(this).toggleClass("btn-primary").toggleClass("btn-outline-primary");
    });
    $(".showMap").click(function(){
        if ($(this).is(":checked")) {
            $(".map-box").addClass("d-block").removeClass("d-none");
        } else {
            $(".map-box").addClass("d-none").removeClass("d-block");
        }
    });
});
