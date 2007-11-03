<?php
/*
   This provides a [x]-minor-edit checkbox, as seen in many other WikiWare,
   which allows authors to prevent the current edit to show up on
   RecentChanges. Such a "minor edit" (denoted by the _DB_F_MINOR flag)
   will occoupy a full version entry in the database nevertheless (fully
   revertable in all cases). The flag currently only affects UpdatedPages
   and RecentChanges style plugins.
*/

$ewiki_plugins["edit_form_append"][] = "ewiki_edit_minor";
$ewiki_plugins["edit_save"][] = "ewiki_edit_minor_save";

$ewiki_t["de"]["minor edit"] = "unbedeutende Änderung";

function ewiki_edit_minor_save(&$save, &$old) {
   $save["flags"] = $save["flags"] & (0xFFFF ^ EWIKI_DB_F_MINOR)
                  | ($_REQUEST["page_minor_edit"] ? EWIKI_DB_F_MINOR : 0);
}

function ewiki_edit_minor($id, &$data, $action) {
   return '<input type="checkbox" name="page_minor_edit" value="1" '
      . ' id="page_minor_edit"><label for="page_minor_edit"> '
      . ewiki_t('minor edit') . '</label><br />' . "\n";
}

?>