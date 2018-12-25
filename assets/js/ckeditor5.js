import ClassicEditorBase from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import UploadAdapterPlugin from '@ckeditor/ckeditor5-adapter-ckfinder/src/uploadadapter';
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
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
 */
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';

export default class RoxEditor extends ClassicEditorBase {}

RoxEditor.builtinPlugins = [
    EssentialsPlugin,
    UploadAdapterPlugin,
    AutoformatPlugin,
    BoldPlugin,
    ItalicPlugin,
    UnderlinePlugin,
    BlockQuotePlugin,
/*    ImagePlugin,
    ImageCaptionPlugin,
    ImageStylePlugin,
    ImageToolbarPlugin,
    ImageUploadPlugin,
*/    LinkPlugin,
    ListPlugin
];

RoxEditor.defaultConfig = {
    toolbar: {
        items: [
            'undo',
            'redo'
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'link',
            '|',
            'blockQuote',
        ]
    },
/*    image: {
        toolbar: [
            'imageStyle:full',
            'imageStyle:side',
            '|',
            'imageTextAlternative'
        ]
    },
*/    language: 'en'
};