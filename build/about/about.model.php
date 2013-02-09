<?php


/**
 * Aboutus Model
 *
 * @package about
 * @author Andreas (lemon-head), based on work by Michael Dettbarn (bw: lupochen)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutModel extends RoxModelBase
{
    //---------------------------------
    // needed for affiliations page
    //---------------------------------
    
    /**
     * checks if the given member is part of the bevolunteer group
     *
     * @param object $member
     * @access public
     * @return bool
     */
    public function isVolunteer($member)
    {
        if (!$member->isLoaded())
        {
            return false;
        }
        $group = $this->createEntity('Group', 17);
        return (bool)$member->getGroupMembership($group);
    }

    public function getFaqsCategorized()
    {
        /*
        $language_ids = array(4,3,2);
        $faqs = $this->bulkLookup(
            "
SELECT
    faq.*,
    words_q_0.Sentence  AS q_0,
    words_q_1.Sentence  AS q_1,
    words_q_2.Sentence  AS q_2,
    words_q_en.Sentence AS q_en,
    words_a_0.Sentence  AS a_0,
    words_a_1.Sentence  AS a_1,
    words_a_2.Sentence  AS a_2,
    words_a_en.Sentence AS a_en
FROM
    faq
    LEFT JOIN words  AS  words_q_0   ON  (STRCMP(CONCAT(' ', words_q_0.code)  , CONCAT(' FaqQ_', faq.QandA)) = 0  AND  words_q_0.IdLanguage  = $language_ids[0])
    LEFT JOIN words  AS  words_q_1   ON  (STRCMP(CONCAT(' ', words_q_1.code)  , CONCAT(' FaqQ_', faq.QandA)) = 0  AND  words_q_1.IdLanguage  = $language_ids[1])
    LEFT JOIN words  AS  words_q_2   ON  (STRCMP(CONCAT(' ', words_q_2.code)  , CONCAT(' FaqQ_', faq.QandA)) = 0  AND  words_q_2.IdLanguage  = $language_ids[2])
    LEFT JOIN words  AS  words_q_en  ON  (STRCMP(CONCAT(' ', words_q_en.code) , CONCAT(' FaqQ_', faq.QandA)) = 0  AND  words_q_en.IdLanguage = 1)
    LEFT JOIN words  AS  words_a_0   ON  (STRCMP(CONCAT(' ', words_a_0.code)  , CONCAT(' FaqA_', faq.QandA)) = 0  AND  words_a_0.IdLanguage  = $language_ids[0])
    LEFT JOIN words  AS  words_a_1   ON  (STRCMP(CONCAT(' ', words_a_1.code)  , CONCAT(' FaqA_', faq.QandA)) = 0  AND  words_a_1.IdLanguage  = $language_ids[1])
    LEFT JOIN words  AS  words_a_2   ON  (STRCMP(CONCAT(' ', words_a_2.code)  , CONCAT(' FaqA_', faq.QandA)) = 0  AND  words_a_2.IdLanguage  = $language_ids[2])
    LEFT JOIN words  AS  words_a_en  ON  (STRCMP(CONCAT(' ', words_a_en.code) , CONCAT(' FaqA_', faq.QandA)) = 0  AND  words_a_en.IdLanguage = 1)
            ",
            array('IdCategory', false)
        );
        */

        $faqs = $this->bulkLookup(
            "
SELECT *
FROM faq where faq.Active='Active' 
            ",
            array('IdCategory', false)
        );
        
        $faq_wordcodes_Q = array();
        foreach ($faqs as $fcat) foreach ($fcat as $faq) {
            $faq_wordcodes_Q[] = "'FaqQ_$faq->QandA'";
        }
        
        $faq_wordcodes_A = array();
        foreach ($faqs as $fcat) foreach ($fcat as $faq) {
            $faq_wordcodes_A[] = "'FaqA_$faq->QandA'";
        }
        
        $faq_words_Q = $this->bulkLookup(
            "
SELECT *
FROM words
WHERE code IN (".implode(", ", $faq_wordcodes_Q).")
            ",
            array('code', 'IdLanguage')
        );
        
        $faq_words_A = $this->bulkLookup(
            "
SELECT *
FROM words
WHERE code IN (".implode(", ", $faq_wordcodes_A).")
            ",
            array('code', 'IdLanguage')
        );
        
        $categories = $this->bulkLookup(
            "
SELECT *
FROM faqcategories
            "
        );
        
        foreach ($faqs as &$fcat) foreach ($fcat as $faq) {
            if (isset($faq_words_Q['FaqQ_'.$faq->QandA])) {
                $faq->words_Q = $faq_words_Q['FaqQ_'.$faq->QandA];
            }
            if (isset($faq_words_A['FaqA_'.$faq->QandA])) {
                $faq->words_A = $faq_words_A['FaqA_'.$faq->QandA];
            }
        }
        
        foreach ($categories as &$category) {
            if (isset($faqs[$category->id])) {
                $category->faqs = $faqs[$category->id];
            } else {
                $category->faqs = array();
            }
        }
        
        return $categories;
    }
    
    
    function getFaqSection($key) {
        $cats = $this->getFaqsCategorized();
        if (isset($cats[$key])) {
            return $cats[$key];
        } else {
            echo 'no';
            return false;
        }
    }
}

