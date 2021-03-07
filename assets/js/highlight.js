import Mark from 'mark.js';

const context = document.getElementById("content");
const keyword = document.getElementById("keyword").value;
const instance = new Mark(context);
instance.mark(keyword);

