<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/


    /**
     * @author Lemon-Head
     * @author Lupochen
     * @author Fake51
     * @author shevek
	 * @Fix jeanyves (2011-09-19)
     */

    /**
     * members app model
     *
     * @package Apps
     * @subpackage Members
     */
class MembersModel extends RoxModelBase
{

    private $profile_language = null;

    /**
     * Constructor
     *
     * @param void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap();
    }

    public function getStatuses() {
        return array('Active',
            'MailToConfirm',
            'PassedAway',
            'Pending',
            'DuplicateSigned',
            'NeedMore',
            'Banned',
            'ChoiceInactive',
            'OutOfRemind',
            'Rejected',
            'CompletedPending',
            'TakenOut',
            'Sleeper',
            'Renamed',
            'ActiveHidden',
            'SuspendedBeta',
            'AskToLeave',
            'StopBoringMe',
            'Buggy');
    }

    /**
     *
     * @param unknown $email
     * @return boolean|Ambigous <object, mixed>
     */
    public function getMemberFromEmail($email)
    {
        /**
         * this is so far the worst, most useless piece of hacky crap
         * I've had to code for BW so far. The cause is the 'encryption'
         * that means we cannot query for a member by email - even if
         * emails are stored unencrypted in the database
         *
         */
        global $_SYSHCVOL;
        $email = $this->dao->escape($email);
        $db_version = "<admincrypted>" . strtr($email, array('@' => '%40')) . "</admincrypted>";
        $result = $this->singleLookup(<<<SQL
SELECT
    IdMember
FROM
   {$_SYSHCVOL['Crypted']}cryptedfields
WHERE
    AdminCryptedValue = '{$db_version}'
    AND
    TableColumn = 'members.Email'
SQL
);
        if (!$result) {
            return false;
        }
        return $this->createEntity('Member', $result->IdMember);
    }

    public function getMemberWithUsername($username)
    {
        return $this->createEntity('Member')->findByUsername($username);
    }

    /**
     * @param $id
     * @return Member
     */
    public function getMemberWithId($id)
    {
        if (!($id = intval($id)))
        {
            return false;
        }

        return $this->createEntity('Member')->findById($id);
    }


    /**
     * retrieves a specific comment
     *
     * @param int $id
     *
     * @access public
     * @return Comment
     */
    public function getComment($id)
    {
        return $this->createEntity('Comment')->findById($id);
    }

    /**
     * marks a comment as problematic
     *
     * @param string $username   - username of member with comment
     * @param int    @comment_id - id of comment to mark
     *
     * @access public
     * @return bool
     */
    public function reportBadComment($username, $comment_id)
    {
        if (!($member = $this->getMemberWithUsername($username)) || !($comment = $this->getComment($comment_id)) || $member->id != $comment->IdToMember)
        {
            return false;
        }
        $comment->AdminAction = 'AdminCommentMustCheck';
        return $comment->update();
    }

      public function get_relation_between_members($IdMember_rel)
      {
          $myself = $this->getMemberWithId($_SESSION['IdMember']);
          $member = $this->getMemberWithId($IdMember_rel);
          $words = $this->getWords();
          $all_relations = $member->all_relations();
          $relation = array();
          $relation['member'] = array();
          if (count($all_relations) > 0) {
              foreach ($all_relations as $rel) {
                if ($rel->IdRelation == $myself->id)
                    $relation['member'] = $rel;
              }
          }
          $all_relations_myself = $myself->all_relations();
          $relation['myself'] = array();
          if (count($all_relations_myself) > 0) {
              foreach ($all_relations_myself as $rel) {
                if ($rel->IdRelation == $member->id)
                    $relation['myself'] = $rel;
              }
          }
          return $relation;
      }

    /**
     * set the location of a member
     */
    public function setLocation($vars)
    {
        // Address IdCity address must only consider Populated places (definition of cities), it also must consider the address checking process
        $rank = 0; // Rank=0 means the main address, todo when we will deal with several addresses we will need to consider the other rank Values ;
        $member = $this->getLoggedInMember();;

        $geonameid = 0;
        $latitude = null;
        $longitude = null;
        $errors = array();
        if (!isset($vars['location-geoname-id'])) {
            $errors['Geonameid'] = 'Geoname not set';
        } else {
            $geonameid = $vars['location-geoname-id'];
        }

        if (!isset($vars['location-latitude'])) {
            $errors['Latitude'] = 'Latitude not set';
        } else {
            $latitude = $vars['location-latitude'];
        }

        if (!isset($vars['location-longitude'])) {
            $errors['Longitude'] = 'Longitude not set';
        } else {
            $longitude = $vars['location-longitude'];
        }

        // Set members location even if the geoname id didn't change as exact location might have been updated
        $result = $this->dao->query("
            UPDATE  addresses
            SET     IdCity = {$geonameid}
            WHERE   IdMember = {$member->id} and Rank=" . $rank
        );

        if (!$result) {
            $errors['Geonameid'] = 'Geoname not set';
        }

        if (empty($errors))
        {
            $member->IdCity = $geonameid;
            $member->Latitude = $latitude;
            $member->Longitude = $longitude;
            $member->update();
            $this->logWrite(
                "The Member with the Id: ". $member->id
                . " changed his location to Geo-Id ". $geonameid
                . " and set exact position to (" . ($latitude <> null) ? $latitude : 'NULL'
                . ", " . ($longitude <> null) ? $longitude : 'NULL' . ")",
                "Members"
            );
        }

        return array(
            'errors' => $errors,
        );
    }


    /**
     * Not totally sure it belongs here - but better this
     * than member object? As it's more of a business of this
     * model to know about different states of the member
     * object to be displayed..
     *
     * JY: not sure neither, anyway, I change the $langcode parameter to be either a numeric (languages.id, or a not numeric languages.ShortCode)
     * Nota no need to test if the profile exist in the language, since this setting is used for the sub-headers of the page (profile content is something else than headers)
     */
    public function set_profile_language($langcode){
        $langcode = $this->dao->escape($langcode);
        if (is_numeric($langcode)) {
            $ss=  "
SELECT SQL_CACHE
    id,ShortCode, Name,WordCode
FROM
    languages
WHERE
    id = '$langcode'
            " ;
        }
        else {
            $ss=  "
SELECT SQL_CACHE
    id,ShortCode, Name,WordCode
FROM
    languages
WHERE
    shortcode = '$langcode'
" ;
        }
        if ($language = $this->singleLookup($ss)) {
            $this->profile_language = $language;
        } else {
            $l = new stdClass;
            $l->id = 0;
            $l->ShortCode = 'en';
            $l->WordCode = 'Lang_en';
            $l->Name = 'English';
            $this->profile_language = $l;
        }
    }

    public function get_profile_language()
    {
        if(isset($this->profile_language)) {
            return $this->profile_language;
        } else {
            // check if current session language is a profile language
            $found = false;
            if (isset($_SESSION['IdMember'])) {
                $memberId = intval($_SESSION['IdMember']);
                $member = $this->createEntity('Member', $memberId);
                $member->set_profile_languages();
                $langs = $member->profile_languages;
                foreach($langs as $lang) {
                    $found = ($lang->ShortCode == $_SESSION['lang']);
                    if ($found) break;
                }
            }
            if ($found) {
                $this->set_profile_language($_SESSION['lang']);
            } else {
                // if no language is set use English
                $this->set_profile_language("en");
            }
            return $this->profile_language;
        }
    }


    /**
     * Delete a profile translation for a member
     */
    public function delete_translation_multiple($trad_ids = array(),$IdOwner, $lang_id)
    {
        $words = new MOD_words();
        $count=0 ;
        foreach ($trad_ids as $trad_id){
            $words->deleteMTrad($trad_id, $IdOwner, $lang_id);
            $count++ ;
        }
        $this->logWrite("Deleting translation for language " .$lang_id." ".$count." translations deleted", "Update profile") ;
    }

    /**
     * Set the preferred language for a member
     *
     * @todo make sure that places that call this function uses the return code
     * @param int $IdMember
     * @param int $IdPreference
     * @param string $Value
     * @access public
     * @return bool
     */
    public function set_preference($IdMember,$IdPreference,$Value)
    {
        $IdMember = $this->dao->escape($IdMember);
        $IdPreference = $this->dao->escape($IdPreference);
        $Value = $this->dao->escape($Value);
        $rr = $this->singleLookup("select memberspreferences.id as id,Value from memberspreferences,preferences where IdMember='{$IdMember}' and IdPreference=preferences.id and preferences.id='{$IdPreference}'");
        $rPref = $this->singleLookup("select preferences.codeName from preferences where  id='{$IdPreference}'");
        if (isset ($rr->id))
        {
            $query = <<<SQL
UPDATE
    memberspreferences
SET
    Value = '{$Value}'
WHERE
    id = {$rr->id} and Value!='{$Value}'
SQL;

            if ($Value!=$rr->Value) {
                $this->logWrite("updating  preference " . $rPref->codeName . " (previous value=<b>".$rr->Value."</b>) To Value <b>" . $Value . "</b>", "Update Preference");
            }
        }
        else
        {
            $query = <<<SQL
INSERT INTO
    memberspreferences (IdMember, IdPreference, Value, created)
VALUES
    ('{$IdMember}', '{$IdPreference}', '{$Value}', NOW())
SQL;
            $this->logWrite("inserting one preference " . $rPref->codeName . " To Value <b>" . $Value . "</b>", "Update Preference");
        }
        return ((!$this->dao->query($query)) ? true : false);
    }

    /**
     * Set a member's profile public/private
     */
    public function set_public_profile ($IdMember,$Public = false)
    {
        $rr = $this->singleLookup(
            "
SELECT *
FROM memberspublicprofiles
WHERE IdMember = ".$IdMember
         );
        if (!$rr && $Public == true) {
        $s = $this->dao->query("
INSERT INTO
    memberspublicprofiles
    (
    IdMember,
    created,
    Type
    )
VALUES
    (
    '$IdMember',
    NOW(),
    'normal'
    )
        ");
            $this->logWrite("Set public profile", "Update Preference");
        } elseif ($rr && $Public == false) {
        $s = $this->dao->query("
DELETE FROM
    memberspublicprofiles
WHERE
    id = ". $rr->id
        );
            $this->logWrite("Remove public profile", "Update Preference");
        }
    }

    
    /**
     * Set preference if comment guidelines have been read.
     */
    public function setCommentGuidelinesRead() {
        $query = "
            SELECT
                id
            FROM
                preferences
            WHERE
                CodeName = 'ReadCommentGuidelines'
            LIMIT 1
            ";
        $row = $this->dao->query($query);
        $readCommentGuidlinesPref = $row->fetch(PDB::FETCH_OBJ);
        if ($readCommentGuidlinesPref === false) {
            return false;
        }

        $membersModel = new MembersModel();
        $membersModel->set_preference($this->getLoggedInMember()->id, $readCommentGuidlinesPref->id, 1);
    }
    
    public function getCommentGuidelinesRead() {
        $layoutbits = new MOD_layoutbits();
        return intval($layoutbits->getPreference("ReadCommentGuidelines"));
    }


    public function _checkSimilarity($comments, $weight = 95) {
        $similar = 0;
        $count = count($comments);
        for($i = 0;$i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                similar_text(
                    $comments[$i]->TextFree, $comments[$j]->TextFree, $percent
                );
                if ($percent > $weight) {
                    $similar++;
                }
            }
        }
        $result = ($similar == $count * ($count -1));
        return $result;
    }

    private function _checkCommentQuality($memberId, $duration, $count) {
        $result = false;
        // Check number of comments written in the last two minutes
        $query = "
            SELECT
                COUNT(*) as cnt
            FROM
                comments c
            WHERE
                c.IdFromMember = " . $memberId . "
                AND TIMEDIFF(NOW(), created) < '" . $duration . "'
            ";
        $s = $this->dao->query($query);
        $row = $s->fetch(PDB::FETCH_OBJ);
        $cnt = $row->cnt;
        if ($cnt >= $count) {
            // Okay limit was hit, check for comment quality
            // Get all comments written during the given duration
            $query = "
                SELECT
                    c.TextFree,
                    c.TextWhere
                FROM
                    comments c
                WHERE
                    c.IdFromMember = " . $memberId . "
                    AND TIMEDIFF(NOW(), created) < '" . $duration . "'
                ";
            $comments = $this->bulkLookup($query);

            $result = $this->_checkSimilarity($comments);
        }
        return $result;
    }

    // checkCommentForm
    public function checkCommentForm(&$vars, $random)
    {
        $errors = array();
        $member = $this->getLoggedInMember();
        $syshcvol = PVars::getObj('syshcvol');
        $max = count($syshcvol->LenghtComments);
        $tt = $syshcvol->LenghtComments;
        for ($ii = 0; $ii < $max; $ii++) {
            $chkName = "Comment_" . $tt[$ii];
            if (isset($vars[$chkName])) {
                $one_selected = true;
            }
        }
        if ($vars['Quality'] == "") {
            $errors[] = 'Comment_MustSelectQuality';
        }
        if (!isset($one_selected)) {
            $errors[] = 'Comment_NoCommentLengthSelected';
        }
        if ($vars['Quality'] == "Good" && isset ($vars["Comment_NeverMetInRealLife"])) {
            $errors[] = 'NoPositiveComment_if_NeverMetInRealLife';
        }
        if (!isset ($vars["CommentGuidelines"])) {
            $errors[] = 'CommentMustAcceptGuidelines';
        }

        $check1 = $this->_checkCommentQuality($member->id, '00:02:00', 1);
        $check2 = $this->_checkCommentQuality($member->id, '00:20:00', 5);
        $check3 = $this->_checkCommentQuality($member->id, '06:00:00', 25);

        if ($check1 || $check2 || $check3) {
            $errors[] = 'CommentSomethingWentWrong';
        }
        return $errors;
    }

    public function addComment($TCom,&$vars)
    {
        $return = true;
        $commentRecipient = $this->createEntity('Member', $vars['IdMember']);
        $commentSender = $this->createEntity('Member', $_SESSION['IdMember']);
        // Mark if an admin's check is needed for this comment (in case it is "bad")
        $AdminAction = "NothingNeeded";
        if ($vars['Quality'] == "Bad") {
            $AdminAction = "AdminCommentMustCheck";
            // notify OTRS
            //Load the files we'll need
            // require_once "bw/lib/swift/Swift.php";
            // require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            // require_once "bw/lib/swift/Swift/Message/Encoder.php";
            // $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
            // $subj = "Bad comment from  " .$mCommenter->Username.  " about " . fUsername($IdMember) ;
            // $text = "Please check the comments. A bad comment was posted by " . $mCommenter->Username.  " about " . fUsername($IdMember) . "\n";
            // $text .= $mCommenter->Username . "\n" . ww("CommentQuality_" . $Quality) . "\n" . GetStrParam("TextWhere") . "\n" . GetStrParam("Commenter");
            // bw_mail($_SYSHCVOL['CommentNotificationSenderMail'], $subj, $text, "", $_SYSHCVOL['CommentNotificationSenderMail'], $defLanguage, "no", "", "");
        }
        $syshcvol = PVars::getObj('syshcvol');
        $max = count($syshcvol->LenghtComments);
        $tt = $syshcvol->LenghtComments;
        $LenghtComments = "";
        for ($ii = 0; $ii < $max; $ii++) {
            $var = $tt[$ii];
            if (isset ($vars["Comment_" . $var])) {
                if ($LenghtComments != "")
                    $LenghtComments = $LenghtComments . ",";
                $LenghtComments = $LenghtComments . $var;
            }
        }
//        $mReceiver=$this->getMemberWithId($vars["IdMember"]) ;
        if (!isset ($TCom->id)) {
            $str = "
INSERT INTO
    comments (
        IdToMember,
        IdFromMember,
        Lenght,
        Quality,
        TextWhere,
        TextFree,
        AdminAction,
        created,
        updated
    )
    values (
        " . $vars['IdMember'] . ",
        " . $_SESSION['IdMember'] . ",
        '" . $LenghtComments . "','" . $vars['Quality'] . "',
        '" . $this->dao->escape($vars['TextWhere']) . "',
        '" . $this->dao->escape($vars['TextFree']) . "',
        '" . $AdminAction . "',
        now(),
        now()
    )"
    ;
            $qry = $this->dao->query($str);
            if(!$qry) {
                $return = false;
            } else {
                $commentId = $qry->insertId();
                $noteWordCode = 'Notify_profile_comment';
                $messageWordCode = 'CommentNotificationMessageNew';
                $messageSubjectWordCode = 'Message_profile_comment_subject';
            }
            $this->logWrite("Adding a comment quality <b>" . $vars['Quality'] . "</b> on " . $commentRecipient->Username, "Comment");
        } else {
            $str = "
UPDATE
    comments
SET
    AdminAction='" . $AdminAction . "',
    IdToMember=" . $vars['IdMember'] . ",
    IdFromMember=" . $_SESSION['IdMember'] . ",
    Lenght='" . $LenghtComments . "',
    Quality='" . $vars['Quality'] . "',
    TextWhere='" . $this->dao->escape($vars['TextWhere']) . "',
    TextFree='" . $this->dao->escape($vars['TextFree']) . "',
    updated = NOW()
WHERE
    id=" . $TCom->id;
            $qry = $this->dao->exec($str);
            if(!$qry) {
                $return = false;
            } else {
                $commentId = $TCom->id;
                $noteWordCode = 'Notify_profile_comment_update';
                $messageWordCode = 'CommentNotificationMessageUpdate';
                $messageSubjectWordCode = 'Message_profile_comment_update_subject';
            }
            $this->logWrite("Updating a comment quality <b>" . $vars['Quality'] . "</b> on " . $commentRecipient->Username, "Comment");
        }
        if ($return != false) {
            // Create a note (member-notification) for this action
            $c_add = ($vars['Quality'] == "Bad") ? '_bad' : '';
            $note = array(
                'IdMember' => $vars['IdMember'],
                'IdRelMember' => $_SESSION['IdMember'],
                'Type' => 'profile_comment' . $c_add,
                'Quality' => $vars['Quality'],
                'commentText' => $vars['TextFree'],
                'Link' => 'members/' . $commentRecipient->Username . '/comments',
                'replyLink'   => 'members/' . $commentSender->Username . '/comments/add',
                'reportLink'  => 'members/reportcomment/' . $commentRecipient->Username . '/' . $commentId,
                'WordCode' => $noteWordCode
            );
            
            if (!$TCom || $TCom->DisplayInPublic == 1) {
                $this->sendCommentNotification($note, $messageWordCode, $messageSubjectWordCode);
            }
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        $this->setCommentGuidelinesRead();
        return $return;

    }

    public function addRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $mReceiver=
        $TData= $this->singleLookup("select * from specialrelations where IdRelation=".$vars["IdRelation"]." and IdOwner=".$_SESSION["IdMember"]);
        $mReceiver=$this->getMemberWithId($vars["IdRelation"]) ;

        if (!isset ($TData->id) ) {
            $str = "
INSERT INTO
    specialrelations (
        IdOwner,
        IdRelation,
        Type,
        Comment,
        created
    )
    values (
        ".$_SESSION["IdMember"].",
        ".$vars['IdRelation'].",
        '".stripslashes($vars['stype'])."',
        ".$words->InsertInMTrad($this->dao->escape($vars['Comment']),"specialrelations.Comment",0).",
        now()
    )"
    ;
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
            $this->logWrite("Adding relation for ".$mReceiver->Username,"MyRelations");
        } else $return = false;
        if ($return != false) {
            // Create a note (member-notification) for this action
            $note = array('IdMember' => $vars['IdRelation'], 'IdRelMember' => $_SESSION['IdMember'], 'Type' => 'relation', 'Link' => 'members/'.$vars['IdOwner'].'/relations/add','WordCode' => 'Notify_relation_new');
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        return $return;

    }

    public function updateRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $TData= $this->singleLookup("select * from specialrelations where IdRelation=".$vars["IdRelation"]." and IdOwner=".$_SESSION["IdMember"]);
        $mReceiver=$this->getMemberWithId($vars["IdRelation"]) ;
        if (isset ($TData->id)) {
            $str = "
UPDATE
    specialrelations
SET
    Type = '".stripslashes($vars['stype'])."',
    Comment = ".$words->InsertInMTrad($this->dao->escape($vars['Comment']),"specialrelations.Comment",0)."
WHERE
    IdOwner = ".$_SESSION["IdMember"]." AND
    IdRelation = ".$vars['IdRelation']."
            ";
            $qry = $this->dao->query($str);
            if(!$qry) $return = false;
            $this->logWrite("Updating relation for ".$mReceiver->Username,"MyRelations");
        } else $return = false;
        if ($return != false) {
            // Create a note (member-notification) for this action
            $note = array('IdMember' => $vars['IdRelation'], 'IdRelMember' => $_SESSION['IdMember'], 'Type' => 'relation', 'Link' => 'members/'.$vars['IdOwner'].'/relations/add','WordCode' => 'Notify_relation_update');
            $noteEntity = $this->createEntity('Note');
            $noteEntity->createNote($note);
        }
        return $return;

    }

    public function confirmRelation(&$vars)
    {
        $return = true;
        $words = new MOD_words();
        $TData = array();
        $TData[1]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdOwner']." AND IdRelation=".$vars['IdRelation']);
        $TData[2]= $this->singleLookup("select * from specialrelations where IdOwner=".$vars['IdRelation']." AND IdRelation=".$vars['IdOwner']);
        if (isset($TData) && count($TData[1]) > 0 && count($TData[2]) > 0 && isset($vars['confirm'])) {
            foreach ($TData as $rel) {
                $IdOwner = $rel->IdOwner;
                $IdRelation = $rel->IdRelation;
                $str = "
UPDATE
    specialrelations
SET
    Confirmed = '".$vars['confirm']."'
WHERE
    IdOwner = ".$IdOwner." AND
    IdRelation = ".$IdRelation."
                ";
                $qry = $this->dao->query($str);
                if(!$qry) $return = false;
                if ($return != false) {
                    // Create a note (member-notification) for this action
                    $note = array('IdMember' => $IdRelation, 'IdRelMember' => $IdOwner, 'Type' => 'relation', 'Link' => 'members/'.$IdOwner.'/relations/add','WordCode' => 'Notify_relation_confirm_'.$vars['confirm']);
                    $noteEntity = $this->createEntity('Note');
                    $noteEntity->createNote($note);
                }
            }
        } else $return = false;
        return $return;
    }

    /**
     * Delete member special relation
     *
     * @param int $id ID of relation
     * @return bool Deletion result, true for success, false on error
     */
    public function deleteRelation($id)
    {
        // Wash ID
        $id = intval($id);

        // Fetch relation from database
        $relation = $this->singleLookup("
            SELECT
                *
            FROM
                specialrelations
            WHERE
                id = '$id'
        ");

        // Return unsuccessfully if no relation was found
        if ($relation === false) {
            return false;
        }

        // Delete relation
        $deleteResult = $this->dao->query("
            DELETE FROM
                specialrelations
            WHERE
                id = '$id'
        ");

        // Return unsuccessfully if deletion failed
        if ($deleteResult === false) {
            return false;
        }

        // Fetch partner relation from database
        $idOwner = $relation->IdRelation;
        $idRelation = $relation->IdOwner;
        $partnerRelation = $this->singleLookup("
            SELECT
                *
            FROM
                specialrelations
            WHERE
                IdRelation = '$idRelation'
                AND
                IdOwner = '$idOwner'
        ");

        // Update partner relation if it exists
        if ($partnerRelation) {
            $relationId = $partnerRelation->id;
            $updateResult = $this->dao->query("
                UPDATE
                    specialrelations
                SET
                    Confirmed = 'No'
                WHERE
                    id = '$relationId'
            ");
            // Test if update was successful
            if ($updateResult != NULL) {
                // Create a note on partner's start page
                $member = $this->getMemberWithId(
                    $relation->IdOwner
                );
                $note = array(
                    'IdMember' => $partnerRelation->IdOwner,
                    'IdRelMember' => $relation->IdOwner,
                    'Type' => 'relation',
                    'Link' => 'members/' . $member->Username
                        . '/relations/',
                    'WordCode' => 'Notify_relation_delete'
                );
                $noteEntity = $this->createEntity('Note');
                $noteEntity->createNote($note);
            }
        }
        return true;
    }

    /**
     * Check form values of MyPreferences form,
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function checkMyPreferences(&$vars)
    {
        $errors = array();
        $member = $this->createEntity('Member', $_SESSION['IdMember']);

        // Password Check
        if (isset($vars['passwordnew']) && $vars['passwordnew'] != '') {
        $query = "select id from members where id=" . $_SESSION['IdMember'] . " and PassWord=PASSWORD('" . $member->preparePassword($vars['passwordold']) . "')";            $qry = $this->dao->query($query);
            $rr = $qry->fetch(PDB::FETCH_OBJ);
            if (!$rr || !array_key_exists('id', $rr))
                $errors[] = 'ChangePasswordInvalidPasswordError';
            if( isset($vars['passwordnew']) && strlen($vars['passwordnew']) > 0) {
                if( strlen($vars['passwordnew']) < 6) {
                    $errors[] = 'ChangePasswordPasswordLengthError';
                }
                if(isset($vars['passwordconfirm'])) {
                    if(strlen(trim($vars['passwordconfirm'])) == 0) {
                        $errors[] = 'ChangePasswordConfirmPasswordError';
                    } elseif(trim($vars['passwordnew']) != trim($vars['passwordconfirm'])) {
                        $errors[] = 'ChangePasswordMatchError';
                    }
                }
            }
        }

        // Languages Check
        if (isset($vars['PreferenceLanguage'])) {
            $squery = "
SELECT
    id,
    Name,
	WordCode
FROM
    languages
ORDER BY
    Name" ;
            $qry = $this->dao->query($squery);
            $langok = false;
            while ($rp = $qry->fetch(PDB::FETCH_OBJ)) {
              $rp->id;
              if ($vars['PreferenceLanguage'] == $rp->id)
                  $langok = true;
            }
            if ($langok == false) {
                $errors[] = 'PreferenceLanguageError';
            }
        }

        // email (e-mail duplicates in BW database allowed)
        // if (!isset($vars['Email']) || !PFunctions::isEmailAddress($vars['Email'])) {
            // $errors[] = 'SignupErrorInvalidEmail';
            // $this->logWrite("Editmyprofile: Invalid Email update with value " .$vars['Email'], "Email Update");
        // }

        return $errors;
    }

    /**
     * Edit a members preferences, one at a time
     *
     */
    public function editPreferences(&$vars)
    {
        // set other preferences
        $query = "select * from preferences";
        $rr = $this->bulkLookup($query);
        foreach ($rr as $rWhile) { // browse all preference
            if (isset($vars[$rWhile->codeName]) && $vars[$rWhile->codeName] != '')
                $result = $this->set_preference($vars['memberid'], $rWhile->id, $vars[$rWhile->codeName]);
        }
    }

    /**
     * sets the language of the current session
     *
     * @param int $language_id
     * @access public
     * @return bool
     */
    public function setSessionLanguage($language_id)
    {
        if (($lang = $this->createEntity('Language', $language_id)) && $lang->isLoaded())
        {
            return $lang->setLanguage();
        }
        return false;
    }

    /**
     * Check form values of Mandatory form,
     * should always be analog to /build/signup/signup.model.php !!
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function checkProfileForm(&$vars)
    {
        $errors = array();

        if ($vars['BirthYear'] == 0 || $vars['BirthMonth'] == 0 || $vars['BirthDay'] == 0) {
            $errors[] = 'SignupErrorInvalidBirthDate';
        }
        else
        {
            $res=$this->validateBirthdate($vars['BirthYear'] . '-' . $vars['BirthMonth'] . '-' . $vars['BirthDay']);
            if ($res === self::DATE_INVALID) {
                $errors[] = 'SignupErrorInvalidBirthDate';
            }
            if ($res === self::TOO_YOUNG) {
                $errors[] = 'MembersErrorTooYoung';
            }
        }
        if (empty($vars['gender']) || !in_array($vars['gender'], array('male','female','other'))) {
            $errors[] = 'SignupErrorInvalidGender';
        }

        if (empty($vars['FirstName'])) {
            $errors[] = 'SignupErrorInvalidFirstName';
        }

        if (empty($vars['LastName'])) {
            $errors[] = 'SignupErrorInvalidLastName';
        }

        if ((empty($vars['Email']) || !PFunctions::isEmailAddress($vars['Email'])) && ($vars['Email']!='cryptedhidden')) {
            $errors[] = 'SignupErrorInvalidEmail';
        }

        if (!empty($_FILES['profile_picture']['name']) && ($_FILES['profile_picture']['error'] != UPLOAD_ERR_OK)) {
        	switch ($_FILES['profile_picture']['error']) {
        		case UPLOAD_ERR_INI_SIZE:
        		case UPLOAD_ERR_FORM_SIZE:
        			$errors[] = 'UploadedProfileImageTooBig';
        			break;
        		default:
        			$errors[] = 'ProfileImageUploadFailed';
        			break;
        	}
        }
        return $errors;
    }

    /**
     * validates a date and outputs valid date or false
     * checks if the age of the person is 17 > x > 100
     *
     * @param string $birthdate
     * @access public
     * @return string|bool
     */

       	public function ageValue($dd)
	{
		$iDate = strtotime($dd);
		$age = (time() - $iDate) / (365 * 24 * 60 * 60);
		return ($age);
	}

    const TOO_YOUNG = -1;
    const DATE_INVALID = -2;

    public function validateBirthdate($birthdate)
    {
        $birthdate = str_replace(array('/','.'),'-',$birthdate);
        if (preg_match('/^([1-2]\d\d\d)-([0-1]?[0-9])-([0-3]?[0-9])$/', $birthdate, $matches) || preg_match('/^([0-3]?[0-9])-([0-1]?[0-9])-([1-2]\d\d\d)$/', $birthdate, $matches))
        {
            if (strlen($matches[1]) == 4)
            {
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
            }
            else
            {
                $year = $matches[3];
                $month = $matches[2];
                $day = $matches[1];
            }
            // fair chance date is american, so switch day and month
            if ($month > 12 && $day < 12)
            {
                $temp = $day;
                $day = $month;
                $month = $temp;
            }

            if (!checkdate($month, $day, $year)) {
                return self::DATE_INVALID;
            }

            $iso_date =  $year . "-" . $month . "-" . $day;
            if (($this->ageValue($iso_date) < SignupModel::YOUNGEST_MEMBER))
                {
                return self::TOO_YOUNG;
                }
                else
                {
                return $iso_date;
            }
        }
        else
        {
            return self::DATE_INVALID;
        }
    }

    /**
     * Update Member's Profile
     *
     * @param unknown_type $vars
     * @return unknown
     */
    public function updateProfile(&$vars)     {
        $IdMember = (int)$vars['memberid'];
        $words = new MOD_words();
        $rights = new MOD_right();
        $m = $vars['member'];

        // fantastic ... love the implementation. Fake
        $CanTranslate = false;
        // $CanTranslate = CanTranslate($vars["memberid"], $_SESSION['IdMember']);
        $ReadCrypted = "MemberReadCrypted"; // This might be changed in the future
        if ($rights->hasRight('Admin') || $rights->hasRight('SafetyTeam') /* or $CanTranslate */) { // admin or CanTranslate can alter other profiles
            $ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
        }
        $m->removeLanguages();;
        foreach ($vars['languages_selected'] as $lang)
        {
            if ($language = $this->createEntity('Language')->findById($lang->IdLanguage))
            {
                $ml = $this->createEntity('MemberLanguage');
                $ml->setSpokenLanguage($m, $language, $lang->Level);
            }
        }

        // Set the language that ReplaceinMTrad uses for writing
        $words->setlangWrite($vars['profile_language']);

        // refactoring to use member entity
//        $m->LastLogin = '0000-00-00' ? 'Never' : $layoutbits->ago(strtotime($TM->LastLogin)); // Members lastlogin is no to be updated here

        if (isset($vars['Status']) && (!empty($vars['Status']))) {
            // this can only happen when an admin or the safety team edits a profile
            $m->Status = $vars['Status'];
        }
        $m->Gender = $vars['gender'];
        $m->HideGender = $vars['HideGender'];
        $m->BirthDate = $vars['BirthYear'] . '-' . $vars['BirthMonth'] . '-' . $vars['BirthDay'];
        $m->bday = $vars['BirthDay'];
        $m->bmonth = $vars['BirthMonth'];
        $m->byear = $vars['BirthYear'];
        $m->HideBirthDate = $vars['HideBirthDate'];
        $m->HideGender = $vars['HideGender'];
        $m->ProfileSummary = $words->ReplaceInMTrad($vars['ProfileSummary'],"members.ProfileSummary", $IdMember, $m->ProfileSummary, $IdMember);
        $m->WebSite = strip_tags($vars['WebSite']);
        $m->Accomodation = $vars['Accomodation'];
        $m->Organizations = $words->ReplaceInMTrad($vars['Organizations'],"members.Organizations", $IdMember, $m->Organizations, $IdMember);
        $m->Occupation = $words->ReplaceInMTrad(strip_tags($vars['Occupation']),"members.Occupation", $IdMember, $m->Occupation, $IdMember);
        $m->ILiveWith = $words->ReplaceInMTrad($vars['ILiveWith'],"members.ILiveWith", $IdMember, $m->ILiveWith, $IdMember);
        $m->MaxGuest = strip_tags($vars['MaxGuest']);
        $m->MaxLenghtOfStay = $words->ReplaceInMTrad($vars['MaxLenghtOfStay'],"members.MaxLenghtOfStay", $IdMember, $m->MaxLenghtOfStay, $IdMember);
        $m->AdditionalAccomodationInfo = $words->ReplaceInMTrad($vars['AdditionalAccomodationInfo'],"members.AdditionalAccomodationInfo", $IdMember, $m->AdditionalAccomodationInfo, $IdMember);
        $m->TypicOffer = strip_tags($vars['TypicOffer']);
        $m->Restrictions = strip_tags($vars['Restrictions']);
        $m->OtherRestrictions = $words->ReplaceInMTrad($vars['OtherRestrictions'],"members.OtherRestrictions", $IdMember, $m->OtherRestrictions, $IdMember);
        $m->Hobbies = $words->ReplaceInMTrad($vars['Hobbies'],"members.Hobbies", $IdMember, $m->Hobbies, $IdMember);
        $m->Books = $words->ReplaceInMTrad($vars['Books'],"members.Books", $IdMember, $m->Books, $IdMember);
        $m->Music = $words->ReplaceInMTrad($vars['Music'],"members.Music", $IdMember, $m->Music, $IdMember);
        $m->Movies = $words->ReplaceInMTrad($vars['Movies'],"members.Movies", $IdMember, $m->Movies, $IdMember);
        $m->PastTrips = $words->ReplaceInMTrad($vars['PastTrips'],"members.PastTrips", $IdMember, $m->PastTrips, $IdMember);
        $m->PlannedTrips = $words->ReplaceInMTrad($vars['PlannedTrips'],"members.PlannedTrips", $IdMember, $m->PlannedTrips, $IdMember);
        $m->PleaseBring = $words->ReplaceInMTrad($vars['PleaseBring'],"members.PleaseBring", $IdMember, $m->PleaseBring, $IdMember);
        $m->OfferGuests = $words->ReplaceInMTrad($vars['OfferGuests'],"members.OfferGuests", $IdMember, $m->OfferGuests, $IdMember);
        $m->OfferHosts = $words->ReplaceInMTrad($vars['OfferHosts'],"members.OfferHosts", $IdMember, $m->OfferHosts, $IdMember);
        $m->PublicTransport = $words->ReplaceInMTrad($vars['PublicTransport'],"members.PublicTransport", $IdMember, $m->PublicTransport, $IdMember);

        // as $CanTranslate is set explicitly above, this is disabled
        // if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data

        if ($vars["HouseNumber"] != $m->get_housenumber()) {
            $this->logWrite("Housenumber updated", "Address Update");
        }
        if ($vars["Street"] != $m->get_street()) {
            $this->logWrite("Street updated", "Address Update");
        }
        if ($vars["Zip"] != $m->get_zip()) {
            $this->logWrite("Zip updated", "Address Update");
        }

		if ($vars["Email"]=="cryptedhidden") {
			$this->logWrite("members.model updateprofile email keeps previous value (cryptedhidden detected)", "Debug");
		}
        else {
			if ($vars["Email"] != $m->email) {
				$this->logWrite("Email updated (previous was " . $m->email . ")", "Email Update"); // Sticking to old BW, the previous email is stored in logs,
                                                                                               // this might be discussed, but if the member fills a bad email,
                                                                                               // there is no more way to retrieve him
                                                                                               // Todo : get rid with this, but implement a confimmation mail
				$m->Email = MOD_crypt::NewReplaceInCrypted(strip_tags($vars['Email']),"members.Email",$IdMember, $m->Email, $IdMember, $this->ShallICrypt($vars,"Email"));
			}
		}
		if ($vars["HomePhoneNumber"]!="cryptedhidden") {
			$m->HomePhoneNumber = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['HomePhoneNumber'])),"members.HomePhoneNumber",$IdMember, $m->HomePhoneNumber, $IdMember, $this->ShallICrypt($vars,"HomePhoneNumber"));
		}
		if ($vars["CellPhoneNumber"]!="cryptedhidden") {
			$m->CellPhoneNumber = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['CellPhoneNumber'])),"members.CellPhoneNumber",$IdMember, $m->CellPhoneNumber, $IdMember, $this->ShallICrypt($vars,"CellPhoneNumber"));
		}
		if ($vars["WorkPhoneNumber"]!="cryptedhidden") {
			$m->WorkPhoneNumber = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['WorkPhoneNumber'])),"members.WorkPhoneNumber",$IdMember, $m->WorkPhoneNumber, $IdMember, $this->ShallICrypt($vars,"WorkPhoneNumber"));
		}
		if ($vars["chat_SKYPE"]!="cryptedhidden") {
			$m->chat_SKYPE = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_SKYPE'])),"members.chat_SKYPE",$IdMember, $m->chat_SKYPE, $IdMember, $this->ShallICrypt($vars,"chat_SKYPE"));
		}
		if ($vars["chat_AOL"]!="cryptedhidden") {
			$m->chat_AOL = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_AOL'])),"members.chat_AOL",$IdMember, $m->chat_AOL, $IdMember, $this->ShallICrypt($vars,"chat_AOL"));
		}
		if ($vars["chat_YAHOO"]!="cryptedhidden") {
			$m->chat_YAHOO = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_YAHOO'])),"members.chat_YAHOO",$IdMember, $m->chat_YAHOO, $IdMember, $this->ShallICrypt($vars,"chat_YAHOO"));
		}
		if ($vars["chat_ICQ"]!="cryptedhidden") {
			$m->chat_ICQ = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_ICQ'])),"members.chat_ICQ",$IdMember, $m->chat_ICQ, $IdMember, $this->ShallICrypt($vars,"chat_ICQ"));
		}
		if ($vars["chat_Others"]!="cryptedhidden") {
			$m->chat_Others = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_Others'])),"members.chat_Others",$IdMember, $m->chat_Others, $IdMember, $this->ShallICrypt($vars,"chat_Others"));
		}
		if ($vars["chat_GOOGLE"]!="cryptedhidden") {
			$m->chat_GOOGLE = MOD_crypt::NewReplaceInCrypted(addslashes(strip_tags($vars['chat_GOOGLE'])),"members.chat_GOOGLE",$IdMember,$m->chat_GOOGLE, $IdMember, $this->ShallICrypt($vars,"chat_GOOGLE"));
		}

        $firstname = MOD_crypt::AdminReadCrypted($m->FirstName);
        $secondname = MOD_crypt::AdminReadCrypted($m->SecondName);
        $lastname = MOD_crypt::AdminReadCrypted($m->LastName);
        if ($firstname != strip_tags($vars['FirstName']) || $secondname != strip_tags($vars['SecondName']) || $lastname != strip_tags($vars['LastName']))
        {
            $this->logWrite("{$m->Username} changed name. Firstname: {$firstname} -> " . strip_tags($vars['FirstName']) . ", second name: {$secondname} -> " . strip_tags($vars['SecondName']) . ", second name: {$lastname} -> " . strip_tags($vars['LastName']), 'Profile update');
        }

		if ($vars["FirstName"]!="cryptedhidden") {
			MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['FirstName'])),"members.FirstName",$IdMember, $m->FirstName, $IdMember, $this->ShallICrypt($vars, "FirstName"));
		}
        if ($vars["SecondName"] != "cryptedhidden") {
            $cryptId = MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['SecondName'])),"members.SecondName",$IdMember, $m->SecondName, $IdMember, $this->ShallICrypt($vars, "SecondName"));

            // Update member if a new crypted SecondName value was added
            if ($cryptId != $m->SecondName) {
                $m->SecondName = $cryptId;
            }
        }
		if ($vars["LastName"]!="cryptedhidden") {
			MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['LastName'])),"members.LastName",$IdMember, $m->LastName, $IdMember, $this->ShallICrypt($vars, "LastName"));
		}
        if ($vars["Zip"] != "cryptedhidden") {
            $this->logWrite("in members.model updateprofile() Before Zip update addresss.Zip=" . $m->address->Zip, "Debug");
            $cryptId = MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['Zip'])), "addresses.Zip", $m->IdAddress, $m->address->Zip, $IdMember, $this->ShallICrypt($vars, "Zip"));

            // Update addresses table if a new crypted zip value was added
            if ($cryptId != $m->address->Zip) {
                $m->setCryptedZip($cryptId);
            }

            $this->logWrite("in members.model updateprofile() After Zip update addresss.Zip=". $m->address->Zip . " \$cryptId=" . $cryptId, "Debug");
        }
        if ($vars["HouseNumber"] != "cryptedhidden") {
            $cryptId = MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['HouseNumber'])), "addresses.HouseNumber", $m->IdAddress, $m->address->HouseNumber, $IdMember, $this->ShallICrypt($vars, "Address"));

            // Update addresses table if a new crypted HouseNumber value was added
            if ($cryptId != $m->address->HouseNumber) {
                $m->setCryptedHouseNumber($cryptId);
            }
        }
		if ($vars["Street"]!="cryptedhidden") {
			$cryptId = MOD_crypt::NewReplaceInCrypted($this->dao->escape(strip_tags($vars['Street'])),"addresses.StreetName",$m->IdAddress,$m->address->StreetName,$IdMember,$this->ShallICrypt($vars, "Address"));
            // Update addresses table if a new crypted StreetName value was added
            if ($cryptId != $m->address->StreetName) {
                $m->setCryptedStreetName($cryptId);
            }
        }

        // Check relations, and update them if they have changed
        $Relations=$m->get_all_relations() ;
        foreach($Relations as $Relation) {
            if (($words->mInTrad($Relation->Comment,$vars['profile_language'])!=$vars["RelationComment_".$Relation->id])
                and (!empty($vars["RelationComment_".$Relation->id])))  {
//              echo "Relation #".$Relation->id,"<br />", $words->mInTrad($Relation->Comment,$vars['profile_language']),"<br />",$vars['RelationComment_'.$Relation->id],"<br />" ;
                $IdTrad = $words->ReplaceInMTrad(strip_tags($vars["RelationComment_".$Relation->id]),"specialrelations.Comment", $Relation->id, $Relation->Comment, $IdMember);
                // Empty comments have trad id 0. Causing ReplaceInMTrad to create
                // a new trad id and returning the new number.
                if ($IdTrad != $Relation->id) {
                    $m->update_relation($Relation->id, $IdTrad);
                }
                $this->logWrite("updating relation #".$Relation->id." Relation Confirmed=".$Relation->Confirmed, "Profile update");
            }
        }

        // Check groups membership description, and update them if they have changed
        // Tod od with Peter: check if there is other feature to update a group membership (a groupmembership model for example, or entity)
        /* group membership should not be present here, disabled for now
        $Groups=$m->getGroups() ;
        for ($i = 0; $i < count($Groups) ; $i++) {
            $group=$Groups[$i] ;
            $group_id = $group->getPKValue() ;
            $group_name_translated = $words->get("Group_".$group->Name);
            $group_comment_translated = htmlspecialchars($words->mInTrad($m->getGroupMembership($group)->Comment,$vars['profile_language']), ENT_QUOTES);
            $IdMemberShip=$m->getGroupMembership($group)->id ;
            if (($words->mInTrad($m->getGroupMembership($group)->Comment,$vars['profile_language'])!=$vars["GroupMembership_".$IdMemberShip])
                and (!empty($vars["GroupMembership_".$IdMemberShip])))  {
                echo "Group #".$group_id,"<br />",$words->mInTrad($m->getGroupMembership($group)->Comment,$vars['profile_language']),"<br />",$vars["GroupMembership_".$IdMemberShip],"<br />" ;
                $words->ReplaceInMTrad(strip_tags($vars["GroupMembership_".$IdMemberShip]),"membersgroups.Comment", $IdMemberShip, $m->getGroupMembership($group)->Comment, $IdMember);
                $this->logWrite("updating membership description in group #".$group_id." Group name=".$group->name, "Profil update");
            }
        }
        */

        // if a member with status NeedMore updates her/his profile, moving them back to pending
        if ($m->Status == 'NeedMore') $m->Status = 'Pending';

        $status = $m->update();

        if (!empty($_FILES['profile_picture']) && !empty($_FILES['profile_picture']['tmp_name']))
        {
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0)
                $this->avatarMake($vars['memberid'],$_FILES['profile_picture']['tmp_name']);
        }

        if ($IdMember == $_SESSION['IdMember'])
        {
            $this->logWrite("Profile update by member himself [Status={$m->Status}]", "Profile update");
        }
        else {
            $this->logWrite("update of another profile <b>".$m->Username."</b>", "Profile update"); // It can be an admin update or a delegated translation update
        }

        return $status;
    }

    /**
     * prettify values from post request
     *
     * @param array $vars
     * @access public
     * @return array
     */
    public function polishProfileFormValues($vars)
    {
        $m = $vars['member'];

        // Prepare $vars
        // JY fix, the escaping will be done from ReplaceInMTrad so I remove it
//        $vars['ProfileSummary'] = $this->dao->escape($vars['ProfileSummary']);
        $birthDate = $vars['BirthYear'] . '-' . $vars['BirthMonth'] . '-' . $vars['BirthDay'];
        $vars['BirthDate'] = (($date = $this->validateBirthdate($birthDate)) ? $date : $birthDate);
        if (!isset($vars['HideBirthDate'])) $vars['HideBirthDate'] = 'No';
        // $vars['Occupation'] = ($member->Occupation > 0) ? $member->get_trad('ProfileOccupation', $profile_language) : '';

        // update $vars for $languages
        if(!isset($vars['languages_selected'])) {
            $vars['languages_selected'] = array();
        }
        $ii = 0;
        $ii2 = 0;
        $lang_used = array();
        foreach($vars['memberslanguages'] as $lang) {
            if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                $vars['languages_selected'][$ii] = new StdClass;
                $vars['languages_selected'][$ii]->IdLanguage = $lang;
                $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                $ii++;
            }
            $ii2++;
        }

        if (!isset($vars['IsHidden_FirstName'])) $vars['IsHidden_FirstName'] = 'No';
        if (!isset($vars['IsHidden_SecondName'])) $vars['IsHidden_SecondName'] = 'No';
        if (!isset($vars['IsHidden_LastName'])) $vars['IsHidden_LastName'] = 'No';
        if (!isset($vars['IsHidden_Address'])) $vars['IsHidden_Address'] = 'No';
        if (!isset($vars['IsHidden_Zip'])) $vars['IsHidden_Zip'] = 'No';
        if (!isset($vars['HideGender'])) $vars['HideGender'] = 'No';
        if (!isset($vars['IsHidden_HomePhoneNumber'])) $vars['IsHidden_HomePhoneNumber'] = 'No';
        if (!isset($vars['IsHidden_CellPhoneNumber'])) $vars['IsHidden_CellPhoneNumber']  = 'No';
        if (!isset($vars['IsHidden_WorkPhoneNumber'])) $vars['IsHidden_WorkPhoneNumber'] = 'No';

//        $vars['Accomodation'] = $this->dao->escape($vars['Accomodation']);
//        $vars['MaxLenghtOfStay'] = $this->dao->escape($vars['MaxLenghtOfStay']);
//        $vars['ILiveWith'] = $this->dao->escape($vars['ILiveWith']);
//        $vars['OfferGuests'] = $this->dao->escape($vars['OfferGuests']);
//        $vars['OfferHosts'] = $this->dao->escape($vars['OfferHosts']);

        // Analyse TypicOffer list
        $TypicOffer = $m->TabTypicOffer;
        $max = count($TypicOffer);
        $vars['TypicOffer'] = "";
        for ($ii = 0; $ii < $max; $ii++) {
            if (isset($vars["check_" . $TypicOffer[$ii]]) && $vars["check_" . $TypicOffer[$ii]] == "on") {
                if ($vars['TypicOffer'] != "")
                    $vars['TypicOffer'] .= ",";
                $vars['TypicOffer'] .= $TypicOffer[$ii];
            }
        } // end of for $ii

        // Analyse Restrictions list
        $TabRestrictions = $m->TabRestrictions;
        $max = count($TabRestrictions);
        $vars['Restrictions'] = "";
        for ($ii = 0; $ii < $max; $ii++) {
            if (isset($vars["check_" . $TabRestrictions[$ii]]) && $vars["check_" . $TabRestrictions[$ii]] == "on") {
                if ($vars['Restrictions'] != "")
                    $vars['Restrictions'] .= ",";
                $vars['Restrictions'] .= $TabRestrictions[$ii];
            }
        } // end of for $ii

//        $vars['PublicTransport'] = $this->dao->escape($vars['PublicTransport']);
//        $vars['Restrictions'] = $this->dao->escape($vars['Restrictions']);
//        $vars['OtherRestrictions'] = $this->dao->escape($vars['OtherRestrictions']);
//        $vars['AdditionalAccomodationInfo'] = $this->dao->escape($vars['AdditionalAccomodationInfo']);
//        $vars['OfferHosts'] = $this->dao->escape($vars['OfferHosts']);
//        $vars['OfferGuests'] = $this->dao->escape($vars['OfferGuests']);
//        $vars['Hobbies'] = $this->dao->escape($vars['Hobbies']);
//        $vars['Books'] = $this->dao->escape($vars['Books']);
//        $vars['Music'] = $this->dao->escape($vars['Music']);
//        $vars['Movies'] = $this->dao->escape($vars['Movies']);
//        $vars['Organizations'] = $this->dao->escape($vars['Organizations']);
//        $vars['PastTrips'] = $this->dao->escape($vars['PastTrips']);
//        $vars['PlannedTrips'] = $this->dao->escape($vars['PlannedTrips']);

        return $vars;
    }

    /**
     * Sends an email to inform the member of a new comment.
     *
     * @param object $note Notification configuration.
     * @param string $messageWordCode i18n code for email content.
     * @param string $subjectWordCode i18n code for email subject.
     */
    public function sendCommentNotification($note, $messageWordCode, $subjectWordCode) {
        $fromMember = $this->createEntity('Member', $note['IdRelMember']);
        $toMember = $this->createEntity('Member', $note['IdMember']);

        if ($fromMember && $toMember) {
            $words = new MOD_words();
            $commentsUrl = PVars::getObj('env')->baseuri . $note['Link'];
            $replyUrl = PVars::getObj('env')->baseuri . $note['replyLink'];
            $reportUrl = PVars::getObj('env')->baseuri . $note['reportLink'];
            $languageCode = $toMember->getLanguagePreference();

            // Prepare email content
            $subject = $words->getRaw($subjectWordCode, array(), $languageCode);
            $body = $words->getRaw($messageWordCode,
                                   array($toMember->Username,
                                         $fromMember->Username,
                                         $words->get('CommentQuality'.$note['Quality'].'InSentence'),
                                         $note['commentText'],
                                         $commentsUrl,
                                         $replyUrl,
                                         $reportUrl),
                                   $languageCode);
            // TODO: Error handling
            $toMember->sendMail($subject, $body);
        }
    }

    // Return the crypting criteria according of IsHidden_* field of a checkbox
    protected function ShallICrypt($vars, $ss) {
        if (isset($vars["IsHidden_" . $ss]) and $vars["IsHidden_" . $ss] == "Yes")
            return ("crypted");
        else
            return ("not crypted");
    } // end of ShallICrypt

    /**
     * Shows a members picture in different sizes
     *
     */
    public function showAvatar($memberId = false, $suffix = null)
    {
        $file = (int)$memberId;
        if ($suffix == null) {
            if (isset($_GET)) {
                if (isset($_GET['xs']) or isset($_GET['50_50']))
                    $suffix = '_xs';
                elseif (isset($_GET['30_30']))
                    $suffix = '_30_30';
                elseif (isset($_GET['75_75']))
                    $suffix = '_75_75';
                elseif (isset($_GET['150']))
                    $suffix = '_150';
                elseif (isset($_GET['200']))
                    $suffix = '_200';
                elseif (isset($_GET['500']))
                    $suffix = '_500';
            }
            $file .= $suffix;
        } else {
            $file = $file . $suffix;
        }

        $member = $this->createEntity('Member', $memberId);
        $browseable = $member->isBrowsable();
        if ((!$browseable) || !$this->hasAvatar($memberId, $suffix) || (!$member->publicProfile && !$this->getLoggedInMember())) {
            header('Content-type: image/png');
            header ("cache-control: must-revalidate");
            $offset = 48 * 60 * 60;
            $expire = "Expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
            header ($expire);
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($suffix) ? $suffix : '').'.png', 'php://output');
            PPHP::PExit();
        }
        $img = new MOD_images_Image($this->avatarDir->dirName().'/'.$file);
        if (!$img->isImage()) {
            header('Content-type: image/png');
            $expires = new DateTime("now + 11 months");
            header("Expires:" . $expires->format(DateTime::RFC1123));
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($suffix) ? $suffix : '').'.png', 'php://output');
            PPHP::PExit();
        }
        header('Content-type: '.$img->getMimetype());
        $this->avatarDir->readFile($file);
        PPHP::PExit();
    }

    public function hasAvatar($memberid, $suffix = '')
    {
        if ($this->avatarDir->fileExists((int)$memberid . $suffix))
            return true;
        elseif ($this->avatarDir->fileExists((int)$memberid . '_original'))
            return $this->avatarMake($memberid, $this->avatarDir->dirName() . '/' . (int)$memberid . '_original', true);
        else {
            $img_path = $this->getOldPicture($memberid);
            return $this->avatarMake($memberid,$img_path);
        }
    }


    public function getOldPicture($memberid) {
        $s = $this->dao->query('
SELECT
    `membersphotos`.`FilePath` as FilePath
FROM
    `members`
LEFT JOIN
    `membersphotos` on `membersphotos`.`IdMember`=`members`.`id`
WHERE
    `members`.`id`=\'' . $memberid . '\' AND
    `members`.`Status`=\'Active\'
ORDER BY membersphotos.SortOrder
');
        // look if any of the pics exists
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $path = str_replace("/bw", "", $row->FilePath);
            $full_path = getcwd().'/bw'.$path;
            if (PPHP::os() == 'WIN') {
                $full_path = str_replace("/", "\\", $full_path);
            }
            if(is_file($full_path)) {
                return $full_path;
            }
        }
        return false;
    }

    public function avatarMake($memberid, $img_file, $using_original=false)
    {
        $img = new MOD_images_Image($img_file);
        if( !$img->isImage())
            return false;
        $size = $img->getImageSize();
        $type = $size[2];
        // maybe this should be changed by configuration
        if( $type != IMAGETYPE_GIF && $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG)
            return false;
        $max_x = $size[0];
        $max_y = $size[1];
        if( $max_x > 150)
            $max_x = 150;

        if (!$using_original) {
        	$original_x = min($size[0],PVars::getObj('images')->max_width);
        	$original_y = min($size[1],PVars::getObj('images')->max_height);
            $this->writeMemberphoto($memberid);
            $img->createThumb($this->avatarDir->dirName(), $memberid.'_original', $original_x, $original_y, true, 'ratio');
        }
        $img->createThumb($this->avatarDir->dirName(), $memberid, $max_x, $max_y, true, '');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_200',200, 266, true, 'ratio');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_xs', 50, 50, true, 'square');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_150', 150, 150, true, 'square');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_30_30', 30, 30, true, 'square');
        $img->createThumb($this->avatarDir->dirName(), $memberid.'_500', 500, 500, true, 'ratio');
        return true;
    }

    public function writeMemberphoto($memberid)
    {
        $s = $this->dao->exec("
INSERT INTO
    `membersphotos`
    (
        FilePath,
        IdMember,
        created,
        SortOrder,
        Comment
    )
VALUES
    (
        '" . $this->avatarDir->dirName() ."/". $memberid . "',
        " . $memberid . ",
        now(),
        -1,
        ''
    )
");
        return $s;
    }

    public function bootstrap()
    {
        $this->avatarDir = new PDataDir('user/avatars');
    }


    public function sendRetiringFeedback($feedback = '')
    {
        if (!empty($feedback))
        {
            $feedback_model = new FeedbackModel;
            $feedback_model->sendFeedback(array(
                "IdCategory"       => FeedbackModel::DELETE_PROFILE,
                "FeedbackQuestion" => $feedback,
            ));
        }
    }

    /**
     * Creates or updates a note for a member
     *
     * @param string username Username of the member for which the note is written
     * @param string category Category under which the note is filed
     * @param string comment Comment text. May be empty
     */
    public function writeNoteForMember($username, $category, $comment) {
        $loggedInMember = $this->getLoggedInMember();
        $member =$this->getMemberWithUsername($username);
        // Check if it is a new note
        $sql = "SELECT * FROM mycontacts WHERE IdMember = ". $loggedInMember->id . " AND IdContact = "
                . $member->id;
        $res = $this->dao->query($sql);
        if (!$res) {
            return false;
        }
        if ($res->numRows()) {
            $sql = "UPDATE mycontacts SET Updated = NOW(), Category = '" . $this->dao->escape($category) . "'"
                 . ", Comment = '" . $this->dao->escape($comment)
                 . "' WHERE IdMember = " . $loggedInMember->id . " AND IdContact = " . $member->id;
                    } else {
            $sql = "INSERT INTO mycontacts SET IdMember = " . $loggedInMember->id . ", IdContact = "
                 . $member->id . ", Created = NOW(), Category = '" . $this->dao->escape($category) . "'"
                 . ", Comment = '" . $this->dao->escape($comment) . "'";
        }
        $this->dao->query($sql);
    }

    /**
     * Deletes the note for a member
     *
     * @param string memberId Id of the member for which the note was written
     */
    public function deleteNoteForMember($memberId) {
        $loggedInMember = $this->getLoggedInMember();

        $sql = "DELETE FROM mycontacts WHERE IdMember = ". $loggedInMember->id . " AND IdContact = ". $memberId;
        $res = $this->dao->query($sql);
    }

    /**
     * Deletes the note for a member
     *
     * @param string memberId Id of the member for which the note was written
     */
    public function setStatus($memberId, $newStatus)
    {
        $member = $this->createEntity('Member', $memberId);
        if ($member) {
            $member->Status = $newStatus;
            $member->update();
            return true;
        }
        return false;
    }

    /**
     * Helper function for removeMembers. Removes private information.
     *
     * Links to private information are stored in addresses and members.
     * The info itself is stored in syshcvol|Crypted.cryptedfields.
     *
     * @param Member $member
     */
    private function _removeCryptedInfo(Member $member, $cryptedTable) {
        // First get information from addresses
        $rows = $this->pdoBulkLookup("
            SELECT
                HouseNumber,
                StreetName,
                Zip,
                Explanation,
                IdGettingThere
            FROM
                addresses
            WHERE
                IdMember = :memberId
            ", array(':memberId' => $member->id)
        );
        if (count($rows) != 0) {
            $cryptedFields = array();
            foreach ($rows as $row) {
                $cryptedFields[] = $row->HouseNumber;
                $cryptedFields[] = $row->StreetName;
                $cryptedFields[] = $row->Zip;
                $cryptedFields[] = $row->Explanation;
                $cryptedFields[] = $row->IdGettingThere;
            }
            $query = $this->get_pdo()->prepare("
            DELETE FROM " .
                $cryptedTable . "
            WHERE
                id IN ( '" . implode("', '", $cryptedFields) . "')
                ");
            $query->execute();
            $query = $this->get_pdo()->prepare("
            DELETE FROM
                addresses
            WHERE
                IdMember = :memberId
            ");
            $query->execute(array('memberId' => $member->id));
        }

        // Now remove link between members table and addresses
        $member->IdCity = 0;
        $member->Id4City = 0;

        // Second remove links to crypted fields stored in members table
        $cryptedFields = $member->get_crypted_fields();
        $rows = $this->pdoBulkLookup("
            SELECT "
            . implode(', ', $cryptedFields) .
            " FROM
                members
            WHERE
                id = :memberId
            ", array('memberId' => $member->id)
        );
        if (count($rows) != 0) {
            $cryptedIds = array();
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    // Collect original value
                    $cryptedIds[$key] = $value;
                    // Remove link
                    $member->$key = 0;
                }
            }
            $query = $this->get_pdo()->prepare("
            DELETE FROM " .
                $cryptedTable . "
            WHERE
                id IN ( '" . implode("', '", $cryptedIds) . "' )
                ");
            $query->execute();
        }
        return $member;
    }

    /**
     * Helper function for removeMembers. Removes profile information.
     *
     * Links to private information are stored in members.
     * The info itself is stored in memberstrads.
     *
     * @param Member $member
     */
    private function _removeProfileInfo(Member $member, $tradIdFields) {
        // First get information from members for profile info
        $rows = $this->pdoBulkLookup("
            SELECT "
                . implode(', ', $tradIdFields) .
            " FROM
                members
            WHERE
                id = :memberId
            ", array(
                'memberId' => $member->id
            )
        );
        $tradIds = array();
        foreach($rows as $row) {
            foreach($row as $key => $value) {
                // Collect original value
                $tradIds[$key] = $value;
                // Remove link
                $member->$key = 0;
            }
        }
        $query = $this->get_pdo()->prepare("
            DELETE FROM
                memberstrads
            WHERE
                IdOwner = :memberId
                AND IdTrad IN ( '" . implode("', '", $tradIds) . "' )
                ");
        $query->execute(
            array(
                ':memberId' => $member->id
            )
        );
        return $member;
    }

    /**
     * Helper function for removeMembers. Cleans the members table.
     *
     * @param Member $member
     * @param array remainingColumns All columns that are neither crypted nor translations
     * @param array tableDescription Detailed information about the different columns
     */
    private function _cleanupMembersTable(Member $member, $remainingColumns, $tableDescription) {
        foreach($remainingColumns as $column) {
            switch($tableDescription[$column]['type']) {
                case 'set':
                case 'int':
                    $member->$column = 0;
                    break;
                case 'date':
                case 'datetime':
                    $member->$column = '1970-01-01';
                    break;
                case 'timestamp':
                    break;
                case "enum":
                    if ($tableDescription[$column]['allow_null']) {
                        $member->$column = 0;
                    } else {
                        $member->$column = $tableDescription[$column]['values'][0];
                    }
                    break;
                case "tinytext":
                    $member->$column = '';
                    break;
                default:
                    break;
            }
        }
        $member->Accomodation = 'NeverAsk';
        $member->updated = date('Y-m-d H:i:s');
        $member->setPassword('password', false);
        return $member;
    }

    /**
     * Helper function for removeMembers. Cleans the memberslanguageslevel table.
     *
     * @param Member $member
     */
    private function _cleanupMemberLanguages(Member $member) {
        $query = $this->get_pdo()->prepare("
            DELETE FROM
                memberslanguageslevel
            WHERE
                IdMember = :memberId
        ");
        $query->execute(
            array(
                ':memberId' => $member->id
            )
        );
        return $member;
    }

    /**
     * Helper function for removeMembers
     *
     * Sets the user handle to retired_xyz, removes the password and email address
     * from the user table
     */
    private function _updateUserTable(Member $member, $newUsername)
    {
        $query = $this->get_pdo()->prepare("
            UPDATE
                user
            SET
                handle = :newHandle,
                pw = PASSWORD(:password),
                email = :email
            WHERE
                handle = :handle
            ");
        $query->bindValue(':handle', $member->Username);
        $query->bindValue(':newHandle', $newUsername);
        $query->bindValue(':password', 'password');
        $query->bindValue(':email', 'noemail@example.com');
        $query->execute();
        $member->Username = $newUsername;
        return $member;
    }

    /**
     * Helper function for removeMembers
     *
     * Deletes the profile picture files
     */
    private function _removeProfilePictures(Member $member)
    {
        $memberPath = $this->avatarDir->dirName() . '/' . $member->id;
        $suffixes = array("_xs", "_30_30", "_150", "_200", "_500", "_original", "");
        foreach($suffixes as $suffix) {
            $filename =  $memberPath . $suffix;
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * This functions is called daily by a cron job to ensure that data of members that asked to leave a year ago
     * are removed from the database.
     *
     * The following is done for each member:
     * - Collect all member trad IDs and delete the rows from the member trads table
     * - set all ids to 0
     * - delete row for this member from address
     * - delete row for this member from user
     * - delete all personal information
     * - Set username to retired_xyz
     */
    public function removeMembers()
    {
        $cryptedTable = PVars::getObj('syshcvol')->Crypted . "cryptedfields";
        $entity = new Member();
        $tableDescription = $entity->getTableDescription();
        $columns = $entity->getColumns();
        $tradIdFields = $entity->get_trads_fields();
        $cryptedFields = $entity->get_crypted_fields();

        $remainingColumns = array_diff($columns, $tradIdFields, $cryptedFields,
            array(
                'id',
                'Status',
                'Username',
                'password',
                'Accomodation'
            )
        );

        $rawMembers = $this->pdoBulkLookup("
            SELECT
                id
            FROM
                members
            WHERE
                status = 'AskToLeave'
                AND Username NOT LIKE 'retired\_%'
                AND LastLogin < CURDATE() - INTERVAL 1 YEAR
             ");
        if (count($rawMembers) != 0) {
            MOD_log::get()->write("Removing private data for " . count($rawMembers) . " members.", "Data Retention");
            foreach ($rawMembers as $rawMember) {
                $member = new Member($rawMember->id);
                $username = $member->Username;
                $newUsername = 'retired_' . $member->id;
                $member = $this->_removeCryptedInfo($member, $cryptedTable);
                $member = $this->_removeProfileInfo($member, $tradIdFields);
                $member = $this->_cleanupMembersTable($member, $remainingColumns, $tableDescription);
                $member = $this->_cleanupMemberLanguages($member);
                $member = $this->_updateUserTable($member, $newUsername);
                $this->_removeProfilePictures($member);
                $member->update();
                MOD_log::get()->write("Removed private data for " . $username, "Data Retention");
            }
            MOD_log::get()->write("Removed private data for " . count($rawMembers) . " members.", "Data Retention");
        }
        return count($rawMembers);
    }
}
