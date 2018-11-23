$(function () {
    $(".img-check").click(function(){
        $(this).toggleClass("checked").toggleClass("not_checked");
    });
    $(".advanced").click(function(){
        $(this).toggleClass("btn-primary").toggleClass("btn-outline-primary");
    });
});
