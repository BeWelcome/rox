import Mark from 'mark.js';

// Marks the searched words (#keyword) in the results (.js-highlight); pages without a search have nothing to mark
const keyword = document.getElementById('keyword');
if (keyword && keyword.value) {
    new Mark('.js-highlight').mark(keyword.value, {
        'diacritics': false
    });
}
