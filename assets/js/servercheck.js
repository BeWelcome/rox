require('popper.js/dist/umd/popper');

document.addEventListener('DOMContentLoaded', function () {
    setInterval(checkServer, 1000);

    function checkServer() {
        var el = document.getElementById("requestCount");
        if (el) {
            var instance = new Tooltip(el, {
                title: "Hey there",
                trigger: "click",
            });
            instance.show();
        }
    }
});
