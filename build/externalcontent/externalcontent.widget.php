<?php


class ExternalContentWidget extends RoxWidget
{
    private $_document = false;
    
    function render($nodepath = false)
    {
        if (!$this->_document) {
            $content = file_get_contents($this->inclusion_url);
            // $tidy = new tidy();
            // $tidy->parseString($content);
            // $content = $tidy->html();
            $newdoc = new DOMDocument();
            @$newdoc->loadHTML($content);
            
            if ($this->link_replace_callback) {
                foreach ($newdoc->getElementsByTagName('a') as $a_node) {
                    $href = $a_node->getAttribute('href');
                    $href = call_user_func($this->link_replace_callback, $href);
                    $a_node->setAttribute('href', $href);
                }
            }
            $this->_document = $newdoc;
        }
        $doc = $this->_document;
        $bodynodes = $doc->getElementsByTagName('body');
        $node = $bodynodes->item(0);
        
        foreach (explode(' ', $nodepath) as $step) {
            if (empty($step) || !is_string($step)) {
                continue;
            } else switch ($step{0}) {
                case '#':
                    $tagid = substr($step, 1);
                    if (empty($tagid)) {
                        continue;
                    } else if ($newnode = $doc->getElementById($tagid)) {
                        $node = $newnode;
                    } else {
                        print_r($newnode);
                    }
                    break;
                default:
                    // TODO: allow to really define a path to pick a specific node,
                    // not just by id
                    continue;
            }
        }
        
        echo '
        
        
        <div id="external_content">
        
        
        
        '.$doc->saveXML($node).'
        
        
        
        </div>
        
        
        ';
    }
}


?>