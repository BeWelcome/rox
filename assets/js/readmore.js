import ShowMore from 'show-more-read/dist/js/showMore.esm.min.js';

import 'show-more-read/dist/css/show-more.min.css';

document.addEventListener('DOMContentLoaded', function () {
    new ShowMore('.js-read-more', {
        config: {
            type: "text",
            limit: 250,
            more: "&#8594; comment.read.more",
            less: "&#8592; comment.show.less"
        }
    });
});
