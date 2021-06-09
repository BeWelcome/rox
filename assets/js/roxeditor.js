import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic/src/index';
import { Essentials } from '@ckeditor/ckeditor5-essentials/src/index';
import { UploadAdapter } from '@ckeditor/ckeditor5-adapter-ckfinder/src/index';
import { Bold, Underline, Italic } from '@ckeditor/ckeditor5-basic-styles/src/index';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote/src/index';
import { EasyImage } from '@ckeditor/ckeditor5-easy-image/src/index';
import { Image, ImageCaption, ImageStyle, ImageToolbar, ImageUpload }  from '@ckeditor/ckeditor5-image/src/index';
import { Link } from '@ckeditor/ckeditor5-link/src/index';
import { List } from '@ckeditor/ckeditor5-list/src/index';
import { Mention } from '@ckeditor/ckeditor5-mention/src/index';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph/src/index';
import { SpecialCharacters, SpecialCharactersEssentials }  from '@ckeditor/ckeditor5-special-characters/src/index';
import { CloudServices } from '@ckeditor/ckeditor5-cloud-services/src/index';

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
            Essentials,
            Bold,
            Underline,
            Italic,
            BlockQuote,
            Image,
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
            'imageUpload',
            'blockQuote',
            '|',
            'undo',
            'redo'
        ],
        image: {
            toolbar: [
                'imageTextAlternative'
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
        }
    } )
        .then( editor => {
        } )
        .catch( error => {
            console.error( error );
        } );
}
