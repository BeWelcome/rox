$(function () {
    $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(
        {
            semanticallyStrict: true
        }
    );
});
