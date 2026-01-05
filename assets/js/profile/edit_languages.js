import {initializeTomSelects, destroyTomSelects} from "../tom-select";

document
    .querySelectorAll('.js-add-language')
    .forEach(btn => {
        btn.addEventListener("click", addFormToCollection)
    });

addDeleteLanguageEventListener()

function addFormToCollection(e) {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    const html = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    collectionHolder.insertAdjacentHTML('beforeend', html)
    collectionHolder.insertAdjacentHTML('beforeend', '<hr class="u:bg-bewelcome" style="margin-top:0">')
    collectionHolder.dataset.index++

    addDeleteLanguageEventListener()
    initializeTomSelects()
}

function deleteFormFromCollection(e) {
    const current  = document.getElementById(e.currentTarget.dataset.related);
    console.log(current)
    current.remove()

    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
    collectionHolder.dataset.index--

    initializeTomSelects()
}

function addDeleteLanguageEventListener() {
    document
        .querySelectorAll('.js-delete-language')
        .forEach(btn => {
            btn.addEventListener("click", deleteFormFromCollection)
        });
}
