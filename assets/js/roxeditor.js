import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import UploadAdapterPlugin from '@ckeditor/ckeditor5-adapter-ckfinder/src/uploadadapter';
import AutoformatPlugin from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import EasyImagePlugin from '@ckeditor/ckeditor5-easy-image/src/easyimage';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';

var allEditors = document.querySelectorAll('.editor');
for (var i = 0; i < allEditors.length; ++i) {
    ClassicEditor.create(allEditors[i], {
        // The plugins are now passed directly to .create().
        plugins: [
            EssentialsPlugin,
            AutoformatPlugin,
            BoldPlugin,
            UnderlinePlugin,
            ItalicPlugin,
            BlockQuotePlugin,
            ImagePlugin,
            ImageCaptionPlugin,
            ImageStylePlugin,
            ImageToolbarPlugin,
            EasyImagePlugin,
            ImageUploadPlugin,
            LinkPlugin,
            ListPlugin,
            ParagraphPlugin,
            UploadAdapterPlugin
        ],
        ckfinder: {
            uploadUrl:"/gallery/upload/image"
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
            '|',
            'imageUpload',
            'blockQuote',
            '|',
            'undo',
            'redo'
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
