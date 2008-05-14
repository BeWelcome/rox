<?php


function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    
    // echo 'buuug';
    // throw new DOMException($errstr);
    /*
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
    {
        throw new DOMException($errstr);
        echo 'exception ausgeworfen';
    }
    */
    return true;
    
}


class PageRenderer
{
    public function renderPage($page)
    {
        // set_error_handler('HandleXmlError');
        
        $starttime = microtime();
        
        $content = $this->renderPageAsString($page);
        
        /*
        $nowtime = microtime();
        echo '<p>time for render = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        $tidy = new tidy();
        $config = array(
            'new-blocklevel-tags' => 'jstext arg',
            'new-inline-tags' => 'ww attribute',
            'wrap-sections' => false,
            // 'markup' => false,
            // 'input-xml' => true,
            'output-xml' => true,
            'numeric-entities' => true,
            'drop-empty-paras' => false
        );
        $tidy->parseString($content, $config, 'utf8');
        $tidy->cleanRepair();
        
        // $deepnode = $this->recursiveTidyManipulation(array(), $tidy->root());
        // echo '<pre>';
        // print_r(get_class_methods($tidy->root()));
        // print_r($deepnode);
        // print_r($tidy->root());
        // echo '</pre>';
        
        $content = (string) $tidy;
        
        $nowtime = microtime();
        echo '<p>time for tidy = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        
        $doc = new DOMDocument('1.0', 'iso-8859-1');
        $doc->resolveExternals = TRUE;
        $doc->strictErrorChecking = FALSE;
        
        $nowtime = microtime();
        echo '<p>time for DOMDocument() = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        $doc->loadXML($content);
        
        $nowtime = microtime();
        echo '<p>time for DOMDocument::loadXML() = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        $this->insertTranslations($doc);
        
        $nowtime = microtime();
        echo '<p>time for translations = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        
        $this->insertAttributes($doc);
        
        $nowtime = microtime();
        echo '<p>time for attributes = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        $this->insertScriptTags($doc);
        
        $nowtime = microtime();
        echo '<p>time for script tags = ' . ($nowtime - $starttime) . '</p>';
        $starttime = $nowtime;
        
        echo $doc->saveXML();

        */
        
        echo $content;
        
    }
    
    
    protected function recursiveTidyManipulation($nodes_path, $end_node)
    {
        if (!$end_node->hasChildren()) {
            if (!$tagname = $end_node->name) {
                $deepnode = end($nodes_path);
                // a text node
                //echo '<br>'.end($nodes_path)->name.' - '.$end_node->value;
                // print_r($end_node);
                //echo '<br>';
            } else {
                $deepnode = $end_node;
                // this is a leave node
                //echo '<br>'.$end_node->name.'<br>';
                // echo '<pre>';
                // print_r($end_node);
                // echo '</pre>';
            }
        } else foreach ($end_node->child as $child) {
            $nodes_path[] = $end_node;
            $deepnode = $this->recursiveTidyManipulation($nodes_path, $child);
        }
        return $deepnode;
    }
    
    
    protected function recursiveTidyManipulation_attrib($nodes_path, $end_node)
    {
        $nodes_path[] = $end_node;
        foreach ($end_node->child as $child) {
            $this->recursiveTidyManipulation_attrib($nodes_path, $child);
        }
    }
    
    
    protected function renderPageAsString($page)
    {
        ob_start();
        $page->render();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    
    protected function insertTranslations($doc)
    {
        $words = new MOD_words();
        
        $lookup_list = array();
        
        // which words are needed?
        foreach ($doc->getElementsByTagName('ww') as $element) {
            $wordcode = $element->textContent;
            if (!isset($lookup_list[$wordcode])) {
                $lookup_list[$wordcode] = 1;
            } else {
                $lookup_list[$wordcode] ++;
            }  
        }
        
        /*
        // look them up all at once.
        $languages = array('de', 'en');
        $lookup_results = array();
        foreach ($languages as $lang) {
            $lookup_results[$lang] = $words->bulkLookup($lookup_list, $lang);
            foreach ($lookup_results[$lang] as $key => $value) {
                unset($lookup_list[$key]);
            }
        }
        
        foreach ($lookup_list as $key => $value) {
            $lookup_results = 
        }
        */
        
        // insert the looked-up words
        while ($element = $doc->getElementsByTagName('ww')->item(0)) {
            $parent = $element->parentNode;
            if ('attribute' == $parent->tagName) {
                $newtext = $words->getBuffered($element->textContent, 'x', 'y', 'z');
            } else {
                $newtext = $words->get($element->textContent, 'x', 'y', 'z');
            }
            $newfragment = $doc->createDocumentFragment();
            $tidy = new tidy();
            $tidy->parseString($newtext, array(
                'wrap-sections' => false,
                // 'markup' => false,
                // 'input-xml' => true,
                'output-xhtml' => true,
                'numeric-entities' => true,
                'drop-empty-paras' => false,
                'show-body-only' => true
            ), 'utf8');
            $tidy->cleanRepair();
            $newfragment->appendXML($tidy);
            $parent->replaceChild($newfragment, $element);
        }
    }
    
    
    protected function insertAttributes($doc)
    {
        while ($element = $doc->getElementsByTagName('attribute')->item(0)) {
            $parent = $element->parentNode;
            if (!$target_element = $element->previousSibling) {
                $target_element = $parent;
            } else while (!is_a($target_element, 'DOMElement')) {
                if (!$target_element = $target_element->previousSibling) {
                    $target_element = $parent;
                    break;
                }
            }
            if ($attribute_key = $element->getAttribute('key')) {
                $inner_html = '';
                foreach ($element->childNodes as $child_node) {
                    $inner_html .= $doc->saveXML($child_node);
                }
                $target_element->setAttribute($attribute_key, $inner_html);
            }
            $parent->removeChild($element);
        }
    }
    
    
    protected function insertScriptTags($doc)
    {
        while ($element = $doc->getElementsByTagName('jstext')->item(0)) {
            $outer_html = $doc->saveXML($element);
            echo "<br>
            outer_html = ($outer_html)
            <br>";
            // echo "<br>textContent = ($element->textContent)<br>";
            $parent = $element->parentNode;
            if ($function_name = $element->getAttribute('key')) {
                $arg_names = array();
                foreach ($element->getElementsByTagName('arg') as $arg_element) {
                    $arg_name = $arg_element->textContent;
                    $arg_names[$arg_name] = $arg_name;
                }
                $inner_html = '';
                
                $child_nodes = $element->childNodes; 
                foreach ($child_nodes as $child_node) {
                    $inner_html .= $doc->saveXML($child_node);
                }
                echo "<br>
                inner_html = ($inner_html)
                <br>";
                // echo "<br>function_name = ($function_name)<br>";
                // print_r($element);
                // echo '<br><br>';
                
                $text = str_replace(
                    array('"', "'", '/', "\n"),
                    array('\"', "\'", '\/', '\n'),
                    $inner_html
                );
                
                $script =
'
function '.$function_name.' (
    '.implode($arg_names, ',
').'
) {
    var text = "'.$text.'";'
                ;
                foreach ($arg_names as $arg_name) {
                    $script .=
'
    text = text.replace("<arg>'.$arg_name.'<\/arg>", '.$arg_name.');'
                    ;
                }
                $script .=
'
    return text;
}
'
                ;
                
                $scriptfragment = $doc->createDocumentFragment();
                try {
                    $scriptfragment->appendXML(
'
<script type="text/javascript">
//<![CDATA[
'.$script.'
//]]>
</script>
'
                    );
                } catch (DOMException $e) {
                    // do nothing, hehe
                }
                $parent->insertBefore($scriptfragment, $element);
                
            }
            $parent->removeChild($element);
        }
        
    }
    
    
}


?>