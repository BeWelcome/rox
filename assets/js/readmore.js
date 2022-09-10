import ShowMore from 'show-more-read/dist/js/showMore.esm.min.js';

import 'show-more-read/dist/css/show-more.min.css';

document.addEventListener('DOMContentLoaded', function () {
    new ShowMore('.js-read-more-received', {
        config: {
            type: "text",
            limit: 240,
            after: 160,
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value
        }
    });
    new ShowMore('.js-read-more-written', {
        config: {
            type: "text",
            limit: 160,
            after: 80,
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value
        }
    });
});
