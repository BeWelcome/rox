<script type="text/javascript">//<!--
tinymce.srcMode = '';
tinymce.baseURL = http_baseuri+'script/tinymce-4.0.28';
tinymce.init({
    menubar : false,
    mode: "exact",
    elements: "<?php echo $textarea; ?>",
    // plugins : "advimage,preview,fullscreen,autolink",
    plugins: "image, charmap, link, autolink, paste, preview",
    theme: "modern",
    content_css : http_baseuri + "styles/css/minimal/screen/tinymce.css",
    relative_urls:false,
    convert_urls:false,
    toolbar: "undo, redo,|,bold,italic,underline,strikethrough,|,bullist,numlist,|,forecolor,backcolor,|,charmap,link,image,|,blockquote,|,preview",
    theme_modern_toolbar_location: 'top',
    theme_modern_statusbar_location: 'bottom',
    theme_modern_resizing: true,
    theme_modern_resize_horizontal : false,
    gecko_spellcheck : true,
    plugin_preview_width : "600",
    plugin_preview_height : "500",
    body_class : "forumsmessage",
});
//-->
</script>