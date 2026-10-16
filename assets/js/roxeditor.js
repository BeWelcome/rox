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

/* \todo Add all UI languages */
import English from 'ckeditor5/translations/en.js';
import Arabic from 'ckeditor5/translations/ar.js';
import German from 'ckeditor5/translations/de.js';
import Greek from 'ckeditor5/translations/el.js';
import French from 'ckeditor5/translations/fr.js';
import Spanish from 'ckeditor5/translations/es.js';

import 'ckeditor5/ckeditor5.css';

const translations = [
    English, Arabic, German, Greek, French, Spanish,
]

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
    ClassicEditor.create(allEditors[i], {
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
        translations: translations,
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
    } )
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

