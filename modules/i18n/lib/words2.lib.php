<?php
/*

Copyright (c) 2007 BeVolunteer

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

/*
 * @author: Andreas (lemon-head)
 */





class TranslationWrap
{
    protected $translation_module;
    protected $context;
    
    function __construct($translation_module, $context) {
        $this->translation_module = $translation_module;
    }
    
    function __call($code, $args) {
        return $this->translation_module->translate($code, $this->context, $args);
    }
    
    function __get($code) {
        return $this->translation_module->translate($code, $this->context, array());
    }
}

class OldWordsAdapter
{
    // TODO: Make this thing behave like the old MOD_words
}



class TranslationModule
{
    protected $spoken_languages;
    protected $print_strategy_map;
    protected $words_gateway;
    
    function __construct(array $spoken_languages, array $print_strategy_map, $words_gateway) {
        $this->spoken_languages = $spoken_languages;
        $this->print_strategy_map = $print_strategy_map;
        $this->words_gateway = $words_gateway;
    }
    
    function translate($code, $context, $args)
    {
        $first_language = false;
        foreach ($this->spoken_languages as $lang) {
            $translation = $this->words_gateway->getTranslation($code, $lang->id);
            if (is_object($translation)) {
                $text = sprintf($translation->Sentence, $args);
                if (!$first_language) {
                    // TODO: check for obsolete translations!
                    if (false) {
                        return $this->print_strategy_map['obsolete']->$context($text, $code, $lang, 'obsolete');
                    }
                    return $this->print_strategy_map['successful']->$context($text, $code, $lang, 'successful');
                }
                // translation found, but not in original language...
                return $this->print_strategy_map['missing_translation']->$context($text, $code, $first_language, 'missing_translation');
            }
            $first_language = $lang;
        }
        // no translation found..
        // we assume that the last in the array of $spoken_languages is always the reference language.. 
        return $this->print_strategy_map['missing_word']->$context($code, $code, $lang, 'missing_word');
    }
}





class WordPrintStrategy_notranslate
{
    function ww($text) {
        return $text;
    }
    function wwsilent($text) {
        return $text;  // TODO: escape!
    }
    function wwscript($text) {
        return $text;  // TODO: escape!
    }
    function wwattribute($text) {
        return $text;  // TODO: escape!
    }
}








class WordPrintStrategy_translate extends WordPrintStrategy_notranslate
{
    protected $tr_link_buffer = array();
    
    function ww($text, $code, $missing_language, $tr_quality) {
        $func_args = func_get_args();
        return call_user_func_array(array($this, 'clickableText'), $func_args);
    }
    
    /**
     * possible methodnames are wwsilent, wwscript, wwattribute
     */
    function __call($methodname, $args) {
        $this->tr_link_buffer[$code] = func_get_args();
        // let other class do the escaping
        return parent::$methodname($args[0]);
    }
    
    function showBufferedTranslationLinks($glue = '', $format = false) {
        $collect = array();
        foreach ($this->tr_link_buffer as $func_args) {
            $trlink = call_user_func_array(array($this, 'standaloneTrLink'), $func_args);
            $collect[] = $format ? sprintf($format, $trlink) : $trlink;
        }
        return implode($glue, $collect);
    }
    
    protected function standaloneTrLink($text, $code, $missing_language, $tr_quality)
    {
        $href = "/admin/translations/" . $missing_language->id . "/" . $code . "/edit";
        $class = 'standalone tr_link '.$this->css_class_strings[$tr_quality];
        $title = $this->title_action_strings[$tr_quality].' '.$code.' in '.$missing_language->ShortCode;
        return '
            <span class="tr_span">
            <a class="'.$class.'" title="'.$title.'" target="new" href="'.$href.'">
            '.$missing_language->ShortCode.'
            </a>
            </span>'
        ;
    }
    
    protected function clickableText($text, $code, $missing_language, $tr_quality) {
        $func_args = func_get_args();
        return $text.call_user_func_array(array($this, 'standaloneTrLink'), $func_args);
    }
    
    protected $css_class_strings = array(
        'missing_word'        => 'missing_word',
        'missing_translation' => 'missing_translation',
        'obsolete'            => 'obsolete',
        'successful'          => 'successful_translation'
    );
    
    protected $title_action_strings = array(
        'missing_word'        => 'define',
        'missing_translation' => 'translate',
        'obsolete'            => 'update',
        'successful'          => 'edit'
    );
}

class WordPrintStrategy_translateClickFullText extends WordPrintStrategy_translate
{
    protected function clickableText($text, $code, $missing_language, $tr_quality)
    {
        $href = PVars::getObj('env')->baseuri."bw/admin/adminwords_edit.php?lang=$missing_language->id&code=$code";
        $class = 'standalone tr_link '.$this->css_class_strings[$tr_quality];
        $title = $this->title_action_strings[$tr_quality].' '.$code.' in '.$missing_language->ShortCode;
        return '
            <span class="tr_span">
            <a class="'.$class.'" title="'.$title.'" target="new" href="'.$href.'">
            '.$text.'
            </a>
            </span>'
        ;
    }
}





class WordsGateway
{
    protected $dao;  // database access object
    
    
    function __construct($dao)
    {
        $this->dao = $dao;
    }
    
    function getTranslation($code, $language_id)
    {
        echo "<p>get $code in $language_id</p>";
        
        $code = $this->dao->escape($code);
        $language_id = (int)$language_id;
        
        return $this->dao->query("
SELECT SQL_CACHE
    Sentence,
    donottranslate,
    updated
FROM
    words
WHERE
    code       = '$code' AND
    IdLanguage = $language_id
        ")->fetch(PDB::FETCH_OBJ);
    }
}
