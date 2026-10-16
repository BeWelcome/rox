import {
    ClassicEditor,
    Essentials,
    CKFinderUploadAdapter,
    Bold,
    Underline,
    Italic,
    BlockQuote,
    EasyImage,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Link,
    LinkImage,
    List,
    Mention,
    Paragraph,
    SpecialCharacters,
    SpecialCharactersEssentials,
    CloudServices,
    Autosave,
    HorizontalLine,
    PendingActions } from "ckeditor5";


import 'ckeditor5/ckeditor5.css';

// The toolbar in the interface language, as on prod (which built every language): one lazily loaded file per
// language, so a page only downloads its own. English is built in; rm, su and sw have no CKEditor translation.
const translationLoaders = {
    'ar': () => import('ckeditor5/translations/ar.js'),
    'bg': () => import('ckeditor5/translations/bg.js'),
    'ca': () => import('ckeditor5/translations/ca.js'),
    'cs': () => import('ckeditor5/translations/cs.js'),
    'da': () => import('ckeditor5/translations/da.js'),
    'de': () => import('ckeditor5/translations/de.js'),
    'el': () => import('ckeditor5/translations/el.js'),
    'eo': () => import('ckeditor5/translations/eo.js'),
    'es': () => import('ckeditor5/translations/es.js'),
    'eu': () => import('ckeditor5/translations/eu.js'),
    'fa': () => import('ckeditor5/translations/fa.js'),
    'fi': () => import('ckeditor5/translations/fi.js'),
    'fr': () => import('ckeditor5/translations/fr.js'),
    'gl': () => import('ckeditor5/translations/gl.js'),
    'hi': () => import('ckeditor5/translations/hi.js'),
    'hr': () => import('ckeditor5/translations/hr.js'),
    'hu': () => import('ckeditor5/translations/hu.js'),
    'hy': () => import('ckeditor5/translations/hy.js'),
    'id': () => import('ckeditor5/translations/id.js'),
    'it': () => import('ckeditor5/translations/it.js'),
    'ja': () => import('ckeditor5/translations/ja.js'),
    'lt': () => import('ckeditor5/translations/lt.js'),
    'lv': () => import('ckeditor5/translations/lv.js'),
    'nb': () => import('ckeditor5/translations/nb.js'),
    'nl': () => import('ckeditor5/translations/nl.js'),
    'no': () => import('ckeditor5/translations/no.js'),
    'pl': () => import('ckeditor5/translations/pl.js'),
    'pt': () => import('ckeditor5/translations/pt.js'),
    'pt-br': () => import('ckeditor5/translations/pt-br.js'),
    'ro': () => import('ckeditor5/translations/ro.js'),
    'ru': () => import('ckeditor5/translations/ru.js'),
    'sk': () => import('ckeditor5/translations/sk.js'),
    'sl': () => import('ckeditor5/translations/sl.js'),
    'sr': () => import('ckeditor5/translations/sr.js'),
    'tr': () => import('ckeditor5/translations/tr.js'),
    'uk': () => import('ckeditor5/translations/uk.js'),
    'zh-hans': () => import('ckeditor5/translations/zh-cn.js'),
    'zh-hant': () => import('ckeditor5/translations/zh.js'),
};

function loadTranslations(language) {
    const loader = translationLoaders[language];
    if (undefined === loader) {
        return Promise.resolve(null);
    }
    return loader().then((module) => module.default).catch(() => null);
}

const translationsReady = loadTranslations(document.documentElement.lang);

function SpecialCharactersTextExtended( editor ) {
    editor.plugins.get( 'SpecialCharacters' ).addItems( 'Text', [
        { title: 'non breaking space', character: '\u00A0' }
    ] );
}

const uploadPath = document.getElementById('upload_path');
let uploadUrl = "/gallery/upload/image";
if (null !== uploadPath) {
    uploadUrl = uploadPath.value;
}

const mentions = document.getElementsByClassName('js-mention');
let feed = [];

for (let i = 0; i < mentions.length; i++) {
    feed.push('@' + mentions.item(i).value);
}

let allEditors = document.querySelectorAll('.editor');
for (let i = 0; i < allEditors.length; ++i) {
    translationsReady.then((translations) => ClassicEditor.create(allEditors[i], {
        licenseKey: 'GPL',
        plugins: [
            Autosave,
            PendingActions,
            Essentials,
            Bold,
            Underline,
            Italic,
            HorizontalLine,
            BlockQuote,
            Image,
            LinkImage,
            ImageCaption,
            ImageStyle,
            ImageToolbar,
            EasyImage,
            Link,
            List,
            Mention,
            Paragraph,
            CKFinderUploadAdapter,
            SpecialCharacters,
            SpecialCharactersEssentials,
            SpecialCharactersTextExtended,
            ImageUpload,
            CloudServices
        ],
        ckfinder: {
            uploadUrl: uploadUrl
        },
        // So is the rest of the default configuration.
        toolbar: [
            'bold',
            'underline',
            'italic',
            '|',
            'link',
            'bulletedList',
            'numberedList',
            'specialCharacters',
            '|',
            'horizontalLine',
            '|',
            'imageUpload',
            'blockQuote',
            '|',
            'undo',
            'redo'
        ],
        image: {
            toolbar: [
                'imageTextAlternative',
                '|',
                'toggleImageCaption',
                'linkImage'
            ]
        },
        language: document.documentElement.lang,
        // only when there is one: CKEditor fails on an empty translations array ("Reduce of empty array")
        ...(translations ? { translations: [translations] } : {}),
        mention: {
            feeds: [
                {
                    marker: '@',
                    feed: feed
                }
            ]
        },
        autosave: {
            save( editor ) {
                return saveData( editor.getData() );
            }
        }
    } ))
        .then( editor => {
            const form = editor.sourceElement.form;
            registerSubmitHandler(form);

            const storedData = JSON.parse(window.localStorage.getItem(window.location.href));
            if (storedData !== null) {
                const diff = new Date() - new Date(storedData.lastChange);

                if (diff < 1000 * 60 * 60 * 24) {
                    editor.setData(storedData.editorData);
                } else {
                    window.localStorage.removeItem(window.location.href);
                }
            }
        } )
        .catch( error => {
            console.error( error );
        } );
}

function registerSubmitHandler( form ) {
    form.addEventListener('submit', function() {
        window.localStorage.removeItem(window.location.href);
    });
}

function saveData( data ) {
    const lastChange = new Date();
    window.localStorage.setItem(window.location.href, JSON.stringify({
        lastChange: lastChange,
        editorData: data
    }));
}

