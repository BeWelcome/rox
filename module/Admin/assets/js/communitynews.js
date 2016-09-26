$(document).ready(function(){
    tinymce.init({
        selector: '#community_news_text',
        height: 500,
        theme: 'modern',
        plugins: [
            'autolink lists link image charmap hr',
            'searchreplace wordcount visualblocks visualchars',
            'media nonbreaking table contextmenu directionality',
            'emoticons template paste textpattern imagetools'
        ],
        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        toolbar2: 'media | emoticons',
        image_advtab: true,
        templates: [
            { title: 'Test template 1', content: 'Test 1' },
            { title: 'Test template 2', content: 'Test 2' }
        ],
        content_css: [
            '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            '//www.tinymce.com/css/codepen.min.css'
        ]
    });
}); // close out script

