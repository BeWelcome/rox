import ShowMore from 'show-more-read/dist/js/showMore.esm.js';

document.addEventListener('DOMContentLoaded', function () {
    new ShowMore('.js-read-more', {
        config: {
            type: "text",
            btnClass: "o-show-more-btn",
            limit: 160,
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value
        }
    });
    new ShowMore('.js-read-more-received', {
        config: {
            type: "text",
            limit: 240,
            after: 160,
            btnClass: "o-show-more-btn",
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value
        }
    });
    new ShowMore('.js-read-more-written', {
        config: {
            type: "text",
            limit: 160,
            after: 80,
            btnClass: "o-show-more-btn",
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value
        }
    });
});
