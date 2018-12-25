import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import AutoformatPlugin from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
/*
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
*/
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';

let allEditors = document.querySelectorAll('.editor');
for (let i = 0; i < allEditors.length; ++i) {
ClassicEditor
    .create( allEditors[i], {
        // The plugins are now passed directly to .create().
        plugins: [
            EssentialsPlugin,
            AutoformatPlugin,
            BoldPlugin,
            ItalicPlugin,
            UnderlinePlugin,
            BlockQuotePlugin,
/*            ImagePlugin,
            ImageCaptionPlugin,
            ImageStylePlugin,
            ImageToolbarPlugin,
*/            LinkPlugin,
            ListPlugin
        ],

        // So is the rest of the default configuration.
        toolbar: [
            'undo',
            'redo',
            'bold',
            'italic',
            'underline',
            'link',
            'bulletedList',
            'numberedList',
            'imageUpload',
            'blockQuote',
        ],
        image: {
            toolbar: [
                'imageStyle:full',
                'imageStyle:side',
                '|',
                'imageTextAlternative'
            ]
        }
    } )
    .then( editor => {
        console.log( editor );
    } )
    .catch( error => {
        console.error( error );
    } );
}

