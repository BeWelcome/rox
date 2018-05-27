require('popper.js/dist/umd/popper');

$(function () {
    setInterval(checkServer(), 1000);

    function checkServer() {
        var instance = new Tooltip(document.getElementById("#requestCount"), {
            title: "Hey there",
            trigger: "click",
        });
        instance.show();
        instance.show();
    }
});
