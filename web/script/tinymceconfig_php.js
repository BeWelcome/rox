<script src="/script/ckeditor.js"></script>
<script>
var allEditors = document.querySelectorAll('.editor');
for (var i = 0; i < allEditors.length; ++i) {
    ClassicEditor.create(allEditors[i], {
        removePlugins: [ 'Heading' ],
        toolbar: [ 'bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList', '|', 'blockQuote' ]
    } )
        .then( editor => {
            window.editor = editor;
        } )
        .catch( err => {
            console.error( err.stack );
        } );
}
</script>
<style>
.ck-editor__editable {
    min-height: 10em;
}
</style>
