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
     * @author Tsjoek
     */

    /**
     * adminwords model
     *
     * @package Apps
     * @subpackage Admin
     */
class AdminWordModel extends RoxModelBase
{
    
    /*
     * Calculate the summed length of all translatable English wordcodes
     *
     * This is used as the divisor for the stats calculation
     *
     * @return object Queryresult
     */
    public function getEnglishTotalLength(){
        $sql = "
SELECT SUM(LENGTH(sentence)) AS cnt
FROM words
WHERE IdLanguage=0 AND (NOT donottranslate='yes')
    AND (isarchived=0 OR isarchived is NULL)";
        $query = $this->dao->query($sql);
        return $query->fetch(PDB::FETCH_OBJ);
    }
    
    /*
     * Calculate the summed length of all translated English wordcodes
     *
     * For each language the total length of the English texts of the items that
     * are translated in the language.
     *
     * @param int idLanguage Languageid to be used when only 1 language is selected
     * @return object Modified queryresult
     */    
    public function getTranslationLength($idLanguage=false){

        // create filter for single-language request
        if ($idLanguage) {
            $strLang = " AND w1.idLanguage = " . (int)$idLanguage;
        } else {
            $strLang = 'AND w1.idLanguage in (SELECT idlanguage FROM words where code = "WelcomeToSignup")';
        }

        $sql = '
SELECT languages.EnglishName englishName,
       languages.shortcode shortCode,
       SUM(LENGTH(w2.sentence)) translated
FROM words w1
	JOIN words w2 ON w2.code = w1.code AND w2.IdLanguage=0
	JOIN languages ON w1.idlanguage=languages.id
WHERE (NOT w2.donottranslate="yes")
    AND (w2.isarchived = 0 OR w2.isarchived is NULL)
    AND w2.updated<=w1.updated
    '.$strLang.'
GROUP BY w1.idlanguage
ORDER BY SUM(LENGTH(w2.sentence)) DESC';
        return $this->BulkLookup($sql);
    }
    
    public function findTranslation($params){
        $codeSelect = '';    
        $descSelect = '';    
        $sentSelect = '';
        $langSelect = '';
        if (isset($params['EngCode'])){
            $codeSelect = ' AND w1.code LIKE "%'.$this->dao->escape($params['EngCode']).'%"';
        }
        if (isset($params['EngDesc'])){
            $descSelect = ' AND w1.description LIKE "%'.$this->dao->escape($params['EngDesc']).'%"';
        }
        if (isset($params['TrSent'])){
            $sentSelect = ' AND w2.sentence LIKE "%'.$this->dao->escape($params['TrSent']).'%"';
        }
        if (isset($params['lang'])){
            $langSelect = ' AND w2.shortcode = "'.$this->dao->escape($params['lang']).'"';
        }
    
        $sql = '
SELECT
    w1.code EngCode,
    w1.description EngDesc,
    w2.Sentence TrSent,
    w2.shortcode TrShortcode
FROM words w1
    JOIN words w2 USING(code)
WHERE w1.idlanguage=0 AND (w1.isarchived=0 or w1.isarchived is null)'.$codeSelect.$descSelect.$sentSelect.$langSelect.'
ORDER BY w1.code,w2.shortcode';
        return $this->BulkLookup($sql);
    }

    /*
     * Collect the data for in the translation list
     *
     * @param string $type Type of list: all, missing, update. '-x' for time-unlimited lists
     * @param int $idLanguage Language of the translations
     * @param string $wordcode Wordcode to select on
     * @return object Queryresult
     */    
    public function getTranslationData($type,$shortcode,$wordcode = false){
        switch ($type) {
        case 'all'     :
        case 'missing' :
        case 'update'  :
            $dateSelect1 = ' AND datediff(now(),w1.created) > 6 AND datediff(now(),w1.updated) < 183';
            $dateSelect2 = ' AND datediff(now(),w3.created) > 6 AND datediff(now(),w3.updated) < 183';            
            break;
        case 'edit'    :
        default        :
            $dateSelect1 = '';
            $dateSelect2 = '';
            break;
        }
        if ($wordcode){
            // select by wordcode if wordcode is given
            $singleSelect1 = ' AND w1.code = "'.$this->dao->escape($wordcode).'"';
            $singleSelect2 = ' AND w3.code = "'.$this->dao->escape($wordcode).'"';            
        } elseif ($shortcode == 'en') {
            // show also the DNT items in English list
            $singleSelect1 = ' AND (w1.isarchived = 0 OR w1.isarchived is null)';
            $singleSelect2 = ' AND (w3.isarchived = 0 OR w3.isarchived is null)';
        } else {
            // only translatable items in other translationlists
            $singleSelect1 = ' AND w1.donottranslate = "no" AND (w1.isarchived = 0 OR w1.isarchived is null)';
            $singleSelect2 = ' AND w3.donottranslate = "no" AND (w3.isarchived = 0 OR w3.isarchived is null)';                        
        }
        $sql = '
(SELECT
    w1.code EngCode,
    w1.description EngDesc,
    (SELECT Username from members where id = w1.IdMember) EngMember,
    w1.donottranslate EngDnt,
    w1.updated EngUpdated,
    w1.sentence EngSent,
    w1.TranslationPriority EngPrio,
    w2.id as TrId,
    w2.updated TrUpdated,
    w2.Sentence TrSent,
    (SELECT Username from members where id = w2.IdMember) TrMember
FROM words w1
    JOIN words w2 USING(code)
WHERE w1.idlanguage=0  
    AND w2.shortcode="'.$this->dao->escape($shortcode).'"'.
    $dateSelect1.
    $singleSelect1.')

UNION

(SELECT
    w3.code EngCode,
    w3.description EngDesc,
    (SELECT Username from members where id = w3.IdMember) EngMember,
    w3.donottranslate EngDnt,
    w3.updated EngUpdated,
    w3.sentence EngSent,
    w3.TranslationPriority EngPrio,
    null as TrId,
    null as TrUpdated,
    null as TrSent,
    null as TrMember
FROM words w3
WHERE w3.idlanguage=0'. 
    $dateSelect2.
    $singleSelect2.'
    AND w3.code NOT IN (SELECT code
                        FROM words w4
                        WHERE w4.shortcode="'.$this->dao->escape($shortcode).'")
)        
ORDER BY EngUpdated DESC
            ';
        $listing = $this->BulkLookup($sql);
        $data = array();
    
        // some postprocessing
        if ($type == 'edit'){
            if (isset($listing[0])){
                return $listing[0];
            } else {
                if ($shortcode=='en' && $wordcode){
                    $dummy->EngCode = $wordcode;
                    return $dummy;
                } else {
                    return false;
                }
            }
        } else {
            foreach ($listing as $key => $item){
                $item->missing = ($item->TrId?false:true);
                $item->update  = ($item->TrId && $item->EngUpdated > $item->TrUpdated?true:false);
                if (($type == 'missing' || $type == 'missingx') && !$item->missing) {continue;}
                if (($type == 'update' || $type == 'updatex') && !$item->update) {continue;}
                $data[$key] = $item;
        }   }

    return $data;
    }
    
    public function getWordcodeById($id){
        $sql = 'SELECT code FROM words WHERE id=' . (int)$id;
        $query = $this->dao->query($sql);
        return $query->fetch(PDB::FETCH_OBJ);
    }
    
    public function wordcodeExist($code,$shortcode){
        $sql = '
SELECT count(*) cnt
FROM words
WHERE code="' . $this->dao->escape($code) . '"
    AND shortcode="' . $this->dao->escape($shortcode) .'"';
        $query = $this->dao->query($sql);
        return $query->fetch(PDB::FETCH_OBJ);
    }
    
    public function getLangarr($scope){
        $sql = "SELECT * FROM languages WHERE ";
        if (strpos($scope, "All") === false) {
            $langall = false;
            $scope = str_replace('"', '', $scope);
            $scope = str_replace(';', ',', $scope);
            $langs = array_map('mysql_real_escape_string',explode(",", $scope));
            $langs = array_map('trim',$langs);
            $sql .= "ShortCode IN ('" . implode("','", $langs) . "')";
        } else {
            $langall = true;
            $sql .= "1 = 1";
        }
        $sql .= " AND IsWrittenLanguage = 1 ORDER BY EnglishName";

        $res = $this->BulkLookup($sql);
        $langarr = array();
        foreach ($res as $rec) {
            $langarr[] = $rec;
        }
        return $langarr;
    }
    
    public function updateNoChanges($id){
        $sql = 'UPDATE words SET updated = NOW() WHERE id = '.(int)$id;
        $this->dao->query($sql);
    }
    
    public function updateSingleTranslation($form){
        
        $eng_ins = '';
        $eng_upd = '';
        $desc = '';
        $changeInAll = '';        
            
        if ($form['lang']=='en'){
            $eng_ins = 'majorupdate = now(),';
            if (isset($form['changetype'])){
                $eng_upd = ($form['changetype']=='major' && $form["lang"]=="en"?'majorupdate = now(),':'');
            } else {
                $eng_upd = '';
            }
            if (isset($form['EngDesc'])){
                $desc = 'description = "'.$this->dao->escape($form['EngDesc']).'", ';
            }
            if (isset($form["EngDnt"])){
                $changeInAll.= 'donottranslate = "'.$this->dao->escape($form["EngDnt"]).'", ';
            }
            if (isset($form["isarchived"])){
                $changeInAll.= 'isarchived = '.(int)$form["isarchived"].', ';
            }
            if (isset($form["EngPrio"])){
                $changeInAll.= 'TranslationPriority = '.(int)$form["EngPrio"].', ';
            }
        }
        
        $sql = '
INSERT INTO words SET
    code = "'.$this->dao->escape($form["EngCode"]).'",
    ShortCode = "'.$this->dao->escape($form["lang"]).'",
    IdLanguage = (SELECT id FROM languages WHERE shortcode="'.$this->dao->escape($form["lang"]).'"),
    Sentence = "'.$this->dao->escape($form["TrSent"]).'",
    updated = now(),
    '.$eng_ins.$desc.'
    IdMember = '.(int)$_SESSION["IdMember"].',
    created = now()
ON DUPLICATE KEY UPDATE
    Sentence = "'.$this->dao->escape($form["TrSent"]).'",
    updated = now(),
    '.$eng_upd.$desc.'
    IdMember = '.(int)$_SESSION["IdMember"];
        $this->dao->query($sql);
        $returnval = array(mysql_insert_id(),mysql_affected_rows());
        
// update dnt,isarchived and TP for all translations,
// but do not change the update moment for the other languages
        if (count($changeInAll)>0){
            $sql = '
UPDATE words
SET ' . $changeInAll. 'updated = updated
WHERE code = "'.$form["EngCode"].'"
                ';
            $this->dao->query($sql);
        }    
        return $returnval;
    }

    public function editFormCheck($form){
        $errors = array();
        switch($form['DOACTION']){
        case 'Submit':
            if (empty($form['EngCode'])){
                $errors[] = 'AdminWordErrorCodeEmpty';
            } else {
                $sql = '
SELECT count(id) AS cnt
FROM words
WHERE code = "'.$this->dao->escape($form['EngCode']).'" AND idLanguage=0
                    ';
                $query = $this->dao->query($sql);
                $res = $query->fetch(PDB::FETCH_OBJ);
                if (!$res->cnt > 0) {
                    if ($form['lang'] == 'en'){
                        if (!preg_match('#^[a-z][-a-z0-9_]+[a-z0-9]$#i',$form['EngCode'])){
                            $errors[] = 'AdminWordErrorBadCodeFormat';
                        }
                    } else {
                        $errors[] = 'AdminWordErrorCodeNotExist';
                    }
                }
            }
    
            if (empty($form['TrSent'])){$errors[] = 'AdminWordErrorSentenceEmpty';}
            if (empty($form['lang'])){$errors[] = 'AdminWordErrorLangEmpty';}
            break;
        case 'Find':
            if (empty($form['EngCode']) && empty($form['TrSent'])){
                $errors[] = 'AdminWordErrorNeedOneSearchTerm';
            }

            break;
        }
        return $errors;
    }

    public function editEngFormCheck($form){
        $errors = array();
        $rights = MOD_right::get();
        $wordLevel = $rights->hasRight('Words');
 
        switch($form['DOACTION']){
        case 'Submit':
            if ($wordLevel >= 10) {
                if ($form['EngDesc'] == $form['EngCode'] || $form['EngDesc'] == $form['EngSent']) {
                    $errors[] = 'AdminWordErrorDescIsCodeSent';
                }
                if (empty($form['EngDesc'])){
                    $errors[] = 'AdminWordErrorDescriptionEmpty';
                } elseif (strlen($form['EngDesc'])<15){
                    $errors[] = 'AdminWordErrorDescriptionTooShort';
                }
                if (empty($form['EngPrio'])){
                    $errors[] = 'AdminWordErrorPriorityEmpty';
                } elseif ($form['EngPrio']<1 || $form['EngPrio']>10) {
                    $errors[] = 'AdminWordErrorPriorityWrong';
                }
            }
            if (empty($form['changetype'])){$errors[] = 'AdminWordErrorChangeTypeEmpty';}
            break;
        case 'Back':
            break;  
        }
        return $errors;        
    }
    
}