<?php        
        $member = $this->member;
        $words = $this->getWords();
        $thumbnail_url = 'members/avatar/'.$member->Username.'?150';
        $picture_url = 'members/avatar/'.$member->Username.'?500';
        $username = $this->member->Username;
        $messagewordsname = $words->get('ContactMember') ;
        $messagelinkname = $words->get('ContactMember') ;
        $relationswordsname = $words->get('addRelation') ;
        $relationslinkname = "members/$username/relations/add" ;

        // notes button link and translation name
        if (isset($note)) {
            $mynotewordsname=$words->get('NoteEditMyNotesOfMember') ;
            $mynotelinkname= "members/$username/note/edit" ;
        }
        else {
            $mynotewordsname=$words->get('NoteAddToMyNotes') ;
            $mynotelinkname= "members/$username/note/add" ;
        }
        // comments button link and translation name
        if (isset($TCom[0])) {
            $commentswordsname=$words->get('EditComments') ;
            $commentslinkname= "members/$username/comments/edit" ;
        }
        else {
            $commentswordsname=$words->get('AddComments') ;
            $commentslinkname= "members/$username/comments/add" ;
        }
$member = $this->member;

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;

$words = $this->getWords();
$ww = $this->ww;
$wwsilent = $this->wwsilent;
$comments_count = $member->count_comments(); 

$layoutbits = new MOD_layoutbits;
$right = new MOD_right();

$verification_status = $member->verification_status;
if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);

$agestr = "";
if ($member->age == "hidden") {
    $agestr .= $ww->AgeHidden;
} else {
    if ($this->passedAway) {
        $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate, substr($member->updated, 0, 10)));
    } else {
        $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate));
    }
}

$occupation = $member->get_trad("Occupation", $profile_language,true);

if ($this->passedAway == 'PassedAway') {
$teaserusername = $words->get('ProfileInMemoriam', $member->Username);
} else {
$teaserusername = $member->Username;
}
        ?>
