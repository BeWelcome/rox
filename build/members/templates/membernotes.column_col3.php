<div id="profile">
    <div id="profile_notes" class="floatbox box">
    <?php // display my notes, if there are any
    echo "<h3>" . $words->get('ProfileMyNotes') . "</h3>";
    if (!empty($mynotes)) {
        $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
        echo $this->pager->render(); 
        echo '<ul class="floatbox">';
        $left = "";
        $right = "";
        $ii = 0;
        foreach ($this->pager->getActiveSubset($mynotes) as $note) {
            $m = $this->model->getMemberWithId($note->IdContact);
            echo '<li class="notepicbox float_left">';
            echo '<div style="float: left;  padding-right: 0.5em; text-align: center">';
            echo '<div style="clear:both">' . $layoutbits->PIC_50_50($m->Username, 'class="framed"') . '</div>';
            echo '<div><a href="members/' . $m->Username . '" target="_blank">'.$m->Username.'</a></div>';
            echo '</div>';
            echo '<p style="text-align: right;">' . $note->Category . '</p>';
            echo '<hr>';
            echo '<p>' . $purifier->purify($note->Comment) . '</p>';
            echo '<hr>';
            echo '<p style="text-align: right;">' .$layoutbits->ago($note->updated). '</p>';
            echo '</li>';
/*
 
                    <table border="1" rules="rows" cellspacing="4" style="width:95%;">
            <tr><td style="text-align: left;"><a href="members/' . $m->Username . '">' . $m->Username . '</a></td><td style="text-align: right;">' . $note->Category . '</td></tr>
            <tr><td colspan="2">' . $purifier->purify($note->Comment) . '</td></tr>
            <tr><td colspan="2" style="text-align: right"><a href="members/' . $m->Username . '/note/update">' . $words->get('NotesUpdate') . '</a></td></tr>
            </table></div>';
*/          
        } 
        echo "</ul>";
        $this->pager->render(); 
    } else {
        echo $words->get("MyNotesNoNotes");
    }  ?>
    </div> <!-- profile_groups -->
</div> <!-- profile -->
