ClassicEditor
    .create( document.querySelector( '.editor' ) , {
        language: '{{ locale }}'
    } )
    .catch( error => {
        console.error( error );
    } );
