<?php
/**
 * Info Plugin: Displays information about various DokuWiki internals
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_info extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 155;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~INFO:\w+~~',$mode,'plugin_info');
    }

    /**
     * Handle the match
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match,7,-2); //strip ~~INFO: from start and ~~ from end
        return array(strtolower($match));
    }

    /**
     * Create output
     *
     * @param string $format   string     output format being rendered
     * @param Doku_Renderer    $renderer  the current renderer object
     * @param array            $data      data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($format, Doku_Renderer $renderer, $data) {
        if($format == 'xhtml'){
            /** @var Doku_Renderer_xhtml $renderer */
            //handle various info stuff
            switch ($data[0]){
                case 'syntaxmodes':
                    $renderer->doc .= $this->_syntaxmodes_xhtml();
                    break;
                case 'syntaxtypes':
                    $renderer->doc .= $this->_syntaxtypes_xhtml();
                    break;
                case 'syntaxplugins':
                    $this->_plugins_xhtml('syntax', $renderer);
                    break;
                case 'adminplugins':
                    $this->_plugins_xhtml('admin', $renderer);
                    break;
                case 'actionplugins':
                    $this->_plugins_xhtml('action', $renderer);
                    break;
                case 'rendererplugins':
                    $this->_plugins_xhtml('renderer', $renderer);
                    break;
                case 'helperplugins':
                    $this->_plugins_xhtml('helper', $renderer);
                    break;
                case 'authplugins':
                    $this->_plugins_xhtml('auth', $renderer);
                    break;
                case 'remoteplugins':
                    $this->_plugins_xhtml('remote', $renderer);
                    break;
                case 'helpermethods':
                    $this->_helpermethods_xhtml($renderer);
                    break;
                default:
                    $renderer->doc .= "no info about ".htmlspecialchars($data[0]);
            }
            return true;
        }
        return false;
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     *
     * @param string $type
     * @param Doku_Renderer_xhtml $renderer
     */
    function _plugins_xhtml($type, Doku_Renderer_xhtml $renderer){
        global $lang;
        $renderer->doc .= '<ul>';

        $plugins = plugin_list($type);
        $plginfo = array();

        // remove subparts
        foreach($plugins as $p){
            if (!$po = plugin_load($type,$p)) continue;
            list($name,/* $part */) = explode('_',$p,2);
            $plginfo[$name] = $po->getInfo();
        }

        // list them
        foreach($plginfo as $info){
            $renderer->doc .= '<li><div class="li">';
            $renderer->externallink($info['url'],$info['name']);
            $renderer->doc .= ' ';
            $renderer->doc .= '<em>'.$info['date'].'</em>';
            $renderer->doc .= ' ';
            $renderer->doc .= $lang['by'];
            $renderer->doc .= ' ';
            $renderer->emaillink($info['email'],$info['author']);
            $renderer->doc .= '<br />';
            $renderer->doc .= strtr(hsc($info['desc']),array("\n"=>"<br />"));
            $renderer->doc .= '</div></li>';
            unset($po);
        }

        $renderer->doc .= '</ul>';
    }

    /**
     * list all installed plugins
     *
     * uses some of the original renderer methods
     *
     * @param Doku_Renderer_xhtml $renderer
     */
    function _helpermethods_xhtml(Doku_Renderer_xhtml $renderer){
        $plugins = plugin_list('helper');
        foreach($plugins as $p){
            if (!$po = plugin_load('helper',$p)) continue;

            if (!method_exists($po, 'getMethods')) continue;
            $methods = $po->getMethods();
            $info = $po->getInfo();

            $hid = $this->_addToTOC($info['name'], 2, $renderer);
            $doc = '<h2><a name="'.$hid.'" id="'.$hid.'">'.hsc($info['name']).'</a></h2>';
            $doc .= '<div class="level2">';
            $doc .= '<p>'.strtr(hsc($info['desc']), array("\n"=>"<br />")).'</p>';
            $doc .= '<pre class="code">$'.$p." = plugin_load('helper', '".$p."');</pre>";
            $doc .= '</div>';
            foreach ($methods as $method){
                $title = '$'.$p.'->'.$method['name'].'()';
                $hid = $this->_addToTOC($title, 3, $renderer);
                $doc .= '<h3><a name="'.$hid.'" id="'.$hid.'">'.hsc($title).'</a></h3>';
                $doc .= '<div class="level3">';
                $doc .= '<div class="table"><table class="inline"><tbody>';
                $doc .= '<tr><th>Description</th><td colspan="2">'.$method['desc'].
                    '</td></tr>';
                if ($method['params']){
                    $c = count($method['params']);
                    $doc .= '<tr><th rowspan="'.$c.'">Parameters</th><td>';
                    $params = array();
                    foreach ($method['params'] as $desc => $type){
                        $params[] = hsc($desc).'</td><td>'.hsc($type);
                    }
                    $doc .= join($params, '</td></tr><tr><td>').'</td></tr>';
                }
                if ($method['return']){
                    $doc .= '<tr><th>Return value</th><td>'.hsc(key($method['return'])).
                        '</td><td>'.hsc(current($method['return'])).'</td></tr>';
                }
                $doc .= '</tbody></table></div>';
                $doc .= '</div>';
            }
            unset($po);

            $renderer->doc .= $doc;
        }
    }

    /**
     * lists all known syntax types and their registered modes
     *
     * @return string
     */
    function _syntaxtypes_xhtml(){
        global $PARSER_MODES;
        $doc  = '';

        $doc .= '<div class="table"><table class="inline"><tbody>';
        foreach($PARSER_MODES as $mode => $modes){
            $doc .= '<tr>';
            $doc .= '<td class="leftalign">';
            $doc .= $mode;
            $doc .= '</td>';
            $doc .= '<td class="leftalign">';
            $doc .= join(', ',$modes);
            $doc .= '</td>';
            $doc .= '</tr>';
        }
        $doc .= '</tbody></table></div>';
        return $doc;
    }

    /**
     * lists all known syntax modes and their sorting value
     *
     * @return string
     */
    function _syntaxmodes_xhtml(){
        $modes = p_get_parsermodes();

        $compactmodes = array();
        foreach($modes as $mode){
            $compactmodes[$mode['sort']][] = $mode['mode'];
        }
        $doc  = '';
        $doc .= '<div class="table"><table class="inline"><tbody>';

        foreach($compactmodes as $sort => $modes){
            $rowspan = '';
            if(count($modes) > 1) {
                $rowspan = ' rowspan="'.count($modes).'"';
            }

            foreach($modes as $index => $mode) {
                $doc .= '<tr>';
                $doc .= '<td class="leftalign">';
                $doc .= $mode;
                $doc .= '</td>';

                if($index === 0) {
                    $doc .= '<td class="rightalign" '.$rowspan.'>';
                    $doc .= $sort;
                    $doc .= '</td>';
                }
                $doc .= '</tr>';
            }
        }

        $doc .= '</tbody></table></div>';
        return $doc;
    }

    /**
     * Adds a TOC item
     *
     * @param string $text
     * @param int $level
     * @param Doku_Renderer_xhtml $renderer
     * @return string
     */
    protected function _addToTOC($text, $level, Doku_Renderer_xhtml $renderer){
        global $conf;

        $hid = '';
        if (($level >= $conf['toptoclevel']) && ($level <= $conf['maxtoclevel'])){
            $hid  = $renderer->_headerToLink($text, true);
            $renderer->toc[] = array(
                'hid'   => $hid,
                'title' => $text,
                'type'  => 'ul',
                'level' => $level - $conf['toptoclevel'] + 1
            );
        }
        return $hid;
    }
}

//Setup VIM: ex: et ts=4 :
