import InlineEditor from '@ckeditor/ckeditor5-editor-inline/src/inlineeditor';
import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import AutoformatPlugin from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';

var allEditors = document.querySelectorAll('.editor-inline');
for (var i = 0; i < allEditors.length; ++i) {
    InlineEditor.create(allEditors[i], {
        // The plugins are now passed directly to .create().
        plugins: [
            EssentialsPlugin,
            AutoformatPlugin,
            BoldPlugin,
            UnderlinePlugin,
            ItalicPlugin,
            LinkPlugin,
            ListPlugin,
            ParagraphPlugin
        ],
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
            'undo',
            'redo'
        ],
        language: document.documentElement.lang
    } )
        .then( editor => {
        } )
        .catch( error => {
            console.error( error );
        } );
}
