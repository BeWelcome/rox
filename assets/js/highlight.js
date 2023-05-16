import Mark from 'mark.js';

const keyword = document.getElementById("keyword").value;
const context = document.querySelector(".js-highlight");

const  instance = new Mark(".js-highlight");
instance.mark(keyword, {
    "diacritics": false
});
