<?php 

/*
    This plugin appends a "Delete Page" button to the edit form/page.
*/

$ewiki_plugins["edit_form_append"][] = "ewiki_edit_form_append_delete";

/**
 * append a delete page button to the edit form.
 *
 * @param mixed id
 * @param mixed data
 * @param string action
 * @return string html output for delete button
 */
function ewiki_edit_form_append_delete($id, $data, $action) 
{     
    return <<<END
<script type="text/javascript" language="JavaScript"><!--
document.write('<br /><input type="submit" name="save" id="save" value="Delete Page" onClick="document.ewiki.content.value=&quot;DeleteMe&quot;;" />');
--></script>
END;
}

?>