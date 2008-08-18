<?php


/**
 * Hello universe view, a first simple version.
 * We redefine the methods of RoxPageView to configure this page.
 * We don't need to redefine all the methods, we already get something for an empty subclass of RoxPageView.
 * For the start, we only redefine the content of the main column.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseSimplePage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        echo '
        <h3>The hello universe middle column</h3>
        using the class HellouniverseSimplePage.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!<br><br>';
        
        require_once SCRIPT_BASE.'modules/i18n/lib/words2.lib.php';
        
        
        $db_vars = PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
        
        $spoken_languages = array(
            (object)array(
                'id' => 6,
                'ShortCode' => 'de'
            ),
            (object)array(
                'id' => 0,
                'ShortCode' => 'en',
            ),
        );
        
        $print_strategy_map = array();
        $print_strategy_map['successful']          = new WordPrintStrategy_translateClickFullText();
        $print_strategy_map['obsolete']            = new WordPrintStrategy_translate();
        $print_strategy_map['missing_word']        = new WordPrintStrategy_translate();
        $print_strategy_map['missing_translation'] = new WordPrintStrategy_translateClickFullText();
        
        $words_gateway = new WordsGateway($dao);
        
        $translation_module = new TranslationModule($spoken_languages, $print_strategy_map, $words_gateway);
        
        echo $translation_module->translate('FindMembers', 'ww', array());
    }
}




?>