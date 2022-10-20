import Mark from 'mark.js';

const keyword = document.getElementById("keyword").value;
const context = document.querySelector(".js-highlight");
console.log(context);
const  instance = new Mark(".js-highlight");
instance.mark(keyword, {
    "diacritics": false
});
