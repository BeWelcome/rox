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
WHERE IdLanguage=0 AND donottranslate!='yes'";
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
            $strLang = '';
        }

        $sql = "
SELECT languages.EnglishName englishName,
       languages.shortcode shortCode,
       SUM(LENGTH(w2.sentence)) translated
FROM words w1
	JOIN words w2 ON w2.code = w1.code AND w2.IdLanguage=0
	JOIN languages ON w1.idlanguage=languages.id
WHERE w2.donottranslate!='yes' AND w2.updated<=w1.updated $strLang
GROUP BY w1.idlanguage
ORDER BY SUM(LENGTH(w2.sentence)) DESC";
        return $this->BulkLookup($sql);
    }
    
    /*
     * Collect the data for in the translation list
     *
     * @param string $type Type of list: all, missing, update. '-x' for time-unlimited lists
     * @param int idLanguage Language of the translations
     * @return object Queryresult
     */    
    public function getTrListData($type,$idLanguage){
        switch ($type) {
        case 'all'     :
        case 'missing' :
        case 'update'  :
            $dateSelect1 = ' AND datediff(now(),w1.created) > 6 AND datediff(now(),w1.updated) < 183';
            $dateSelect2 = ' AND datediff(now(),w3.created) > 6 AND datediff(now(),w3.updated) < 183';            
            break;
        default        :
            $dateSelect1 = '';
            $dateSelect2 = '';
            break;
        }

        $sql = '
(SELECT
    w1.code EngCode,
    w1.description EngDesc,
    w1.IdMember EngMember,
    w1.donottranslate EngDnt,
    w1.updated EngUpdated,
    w1.sentence EngSent,
    w2.id as TrId,
    w2.updated TrUpdated,
    w2.Sentence TrSent,
    w2.IdMember TrMember
FROM words w1
    JOIN words w2 USING(code)
WHERE w1.idlanguage=0 AND w2.idlanguage='.$idLanguage.$dateSelect1.')

UNION

(SELECT
    w3.code EngCode,
    w3.description EngDesc,
    w3.IdMember EngMember,
    w3.donottranslate EngDnt,
    w3.updated EngUpdated,
    w3.sentence EngSent,
    null as TrId,
    null as TrUpdated,
    null as TrSent,
    null as TrMember
FROM words w3
WHERE w3.idlanguage=0 '.$dateSelect2.'
    AND w3.code NOT IN (SELECT code
                        FROM words w2
                        WHERE idlanguage='.$idLanguage.')
)        
ORDER BY EngUpdated DESC
            ';
        $listing = $this->BulkLookup($sql);
        $data = array();
    
        foreach ($listing as $key => $item){
            $item->missing = ($item->TrId?false:true);
            $item->update  = ($item->TrId && $item->EngUpdated > $item->TrUpdated?true:false);
            if (($type == 'missing' || $type == 'missingx') && !$item->missing) {continue;}
            if (($type == 'update' || $type == 'updatex') && !$item->update) {continue;}
            $data[$key] = $item;
        }

    return $data;
    }
}