import ShowMore from 'show-more-read/dist/js/showMore.esm.js';
import Mark from 'mark.js';

const keyword = document.getElementById("keyword").value;
let markInstance = null;

document.addEventListener('DOMContentLoaded', function () {
    new ShowMore('.js-read-more', {
        onMoreLess: (type, object) => {
            markInstance.mark(keyword, {
                "diacritics": false
            });
        },
        regex: {
            image: {
                match: /<img([\w\W]+?)[/]?>/g,
                replace: ''
            },
            blockquote: {
                match: /<blockquote>.*?<\/blockquote>/gi,
                replace: ""
            }

        },
        config: {
            type: "text",
            btnClass: "o-show-more-btn",
            limit: 300,
            more: document.getElementById('read.more').value,
            less: document.getElementById('show.less').value,
        }
    });

    markInstance = new Mark(".js-highlight");
    markInstance.mark(keyword, {
        "diacritics": false
    });
});
