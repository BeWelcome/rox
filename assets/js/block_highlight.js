import Mark from 'mark.js';

const context = document.querySelector(".js-highlight");

const  instance = new Mark(".js-highlight");
instance.mark(["@", ".at.", "-at-", "(at)", "verif", ".shop", "verif", "support"], {
    "diacritics": false
});
