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
        waitingTime: 2000,
        save( editor ) {
            return saveData( editor, false );
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
            editor.ui.focusTracker.on( 'change:isFocused', ( evt, data, isFocused ) => {
                console.log(editor.sourceElement.id + " - " + isFocused)

                const host = editor.sourceElement;
                const editorType = host.dataset.editorType;

                if (editorType === 'inline') {
                    const progress = document.getElementById(host.dataset.progress)

                    if (isFocused) {
                        progress.classList.remove('u:hidden')
                        progress.classList.add('u:bg-bewelcome')
                    } else {
                        saveData(editor, true)
                    }
                }
            } );

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
        // Remove data from localeStorage.
        for (let [editor] of editors.entries()) {
            const element = editor.sourceElement

            window.localStorage.removeItem(element.id);
        }
    })
}

async function saveData( editor, lostFocus ) {
    const element = document.getElementById(editor.sourceElement.id)

    const lastChange = new Date();
    const language = element.dataset.language;
    const storageKey = editor.sourceElement.id + "-" + (language ? language : '');

    window.localStorage.setItem(storageKey, JSON.stringify({
        lastChange: lastChange,
        editorData: editor.getData()
    }));

    if (lostFocus) {
        // Only triggered for inline editor: messages, forum posts, trips description stay keep the data till form submit
        const progress = document.getElementById(element.dataset.progress);

        if (progress) {
            progress.classList.add('u:bg-bewelcome', 'u:animate-pulse')
        }

        // Post data to the server
        const form = new FormData();
        form.append('field', element.dataset.field);
        form.append('language', element.dataset.language);
        form.append('username', element.dataset.username);
        form.append('content', editor.getData());

        await fetch("/members/update/field", { method: 'POST', body: form })
            .then(() => {
                if (progress) {
                    progress.classList.remove('u:animate-pulse', 'u:bg-bewelcome')
                    progress.classList.add('u:hidden')
                }
                window.localStorage.removeItem(storageKey);
            })
    }
}
