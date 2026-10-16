import ShowMore from 'show-more-read/dist/js/showMore.esm.js';

// Translated button labels from the page's hidden inputs (as on prod); pages without them keep the library's text.
const label = (id) => document.getElementById(id)?.value;
const labels = () => Object.fromEntries(
    [['more', label('read.more')], ['less', label('show.less')]].filter(([, text]) => undefined !== text)
);

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelectorAll('.js-read-more').length !== 0) {
        new ShowMore('.js-read-more', {
            config: {
                type: "text",
                btnClass: "o-show-more-btn",
                limit: 300,
                ...labels(),
            }
        });
    }
    new ShowMore('.js-read-more-received', {
        config: {
            type: "text",
            limit: 240,
            after: 60,
            btnClass: "o-show-more-btn",
            ...labels(),
        }
    });
    new ShowMore('.js-read-more-written', {
        config: {
            type: "text",
            limit: 120,
            after: 30,
            btnClass: "o-show-more-btn",
            ...labels(),
        }
    });
    if (document.querySelectorAll('.js-read-more-comment').length !== 0) {
        new ShowMore(".js-read-more-comment", {
            config: {
                type: "text",
                btnClass: "o-show-more-btn",
                ...labels(),
            }
        });
    }
});
