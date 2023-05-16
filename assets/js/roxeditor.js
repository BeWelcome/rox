import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic/src/index';
import { Essentials } from '@ckeditor/ckeditor5-essentials/src/index';
import { UploadAdapter } from '@ckeditor/ckeditor5-adapter-ckfinder/src/index';
import { Bold, Underline, Italic } from '@ckeditor/ckeditor5-basic-styles/src/index';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote/src/index';
import { EasyImage } from '@ckeditor/ckeditor5-easy-image/src/index';
import { Image, ImageCaption, ImageStyle, ImageToolbar, ImageUpload } from '@ckeditor/ckeditor5-image/src/index';
import { Link, LinkImage } from '@ckeditor/ckeditor5-link/src/index';
import { List } from '@ckeditor/ckeditor5-list/src/index';
import { Mention } from '@ckeditor/ckeditor5-mention/src/index';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph/src/index';
import { SpecialCharacters, SpecialCharactersEssentials }  from '@ckeditor/ckeditor5-special-characters/src/index';
import { CloudServices } from '@ckeditor/ckeditor5-cloud-services/src/index';
import { Autosave } from "@ckeditor/ckeditor5-autosave/src/index";
import HorizontalLine from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline';
import PendingActions from "@ckeditor/ckeditor5-core/src/pendingactions";

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
        // The plugins are now passed directly to .create().
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
            UploadAdapter,
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

