import {
    ClassicEditor,
    InlineEditor,
    Essentials,
    Bold,
    Underline,
    Italic,
    BlockQuote,
    EasyImage,
    Image,
    ImageBlock,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    CKFinderUploadAdapter,
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

const plugins = [
    Autosave,
    PendingActions,
    Essentials,
    Bold,
    Underline,
    Italic,
    HorizontalLine,
    BlockQuote,
    Link,
    List,
    Mention,
    Paragraph,
    CKFinderUploadAdapter,
    SpecialCharacters,
    SpecialCharactersEssentials,
    Image,
    EasyImage,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageBlock,
    LinkImage,
    ImageUpload,
    CloudServices
];

const config = {
    licenseKey: 'GPL',
    plugins: plugins,
    // So is the rest of the default configuration.
    toolbar: {
        items: [
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
        shouldNotGroupWhenFull: false
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
            return saveData( editor );
        }
    },
    ckfinder: {
        uploadUrl: uploadUrl
    },
    image: {
        toolbar: [
            'imageTextAlternative',
            '|',
            'toggleImageCaption',
            'linkImage'
        ]
    }
}


for (let i = 0; i < mentions.length; i++) {
    feed.push('@' + mentions.item(i).value);
}


// add editors based on editor type
let editors = new Map();

const sourceElements = document.querySelectorAll('[data-editor-type]');
sourceElements.forEach( (element) => {
    const allowImageUpload = element.dataset.imageUpload === 'yes';

    let editor = null;
    switch (element.dataset.editorType) {
        case 'textarea':
            editor = ClassicEditor.create(element, config);
            break;
        case 'inline':
            editor = InlineEditor.create(element, config);
            break;
        case 'decoupled':
            throw 'Decoupled editor not implemented yet';
        default:
            throw 'Unknown editor type';
    }

    editor
        .then( editor => {
            editors.set(element.id, editor)

            const form = editor.sourceElement.closest('form')

            registerSubmitHandler(form);

            const storedData = JSON.parse(window.localStorage.getItem(editor.sourceElement.id));
            if (storedData !== null) {
                const diff = new Date() - new Date(storedData.lastChange);

                // Data needs to be younger than 24h)
                if (diff < 1000 * 60 * 60 * 24) {
                    editor.setData(storedData.editorData);
                } else {
                    window.localStorage.removeItem(editor.sourceElement.id);
                }
            }
            if (!allowImageUpload) {
                editor.ui.view.toolbar.items.get(11).isEnabled = false
            }

        } )
        .catch( error => {
            console.error( error )
        } );
})


function registerSubmitHandler( form ) {
    form.addEventListener('submit', function( form ) {
        // get all editors and set data on hidden input for inline and decoupled editor
        // then remove data from localeStorage
        for (let [id, editor] of editors.entries()) {
            const element = editor.sourceElement

            if (element.dataset.editorType === 'inline') {
                const input = document.getElementById(element.dataset.input)
                input.value = editor.getData()
            }

            window.localStorage.removeItem(element.id);
        }
    })
}

function saveData( editor ) {
    const lastChange = new Date();

    window.localStorage.setItem(editor.sourceElement.id, JSON.stringify({
        lastChange: lastChange,
        editorData: editor.getData()
    }));
}

