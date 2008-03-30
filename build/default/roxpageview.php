<?php



class RoxTemplate
{
    private $_rel_path;
    private $_args;
    
    public static function echo_me() {echo 'roxtemplate';}
    
    public function __construct($rel_path, $args)
    {
        $this->_rel_path = $rel_path;
        $this->_args = $args;
    }
    
    public function render()
    {
        if (!file_exists($this->filepath())) {
            $this->templateNotFound();
        } else {
            $this->showTemplate();
        }
    }
    
    protected function templateNotFound()
    {
        echo '<br>did not find '.$this->filepath().'<br>';
    }
    
    protected function showTemplate()
    {
        if (!is_array($this->_args)) {
            // no parameters given
        } else foreach ($this->_args as $key => $value) {
            $$key = $value;
        }
        require $this->filepath();
    }
    
    protected function filepath()
    {
        return TEMPLATE_DIR.$this->_rel_path;
    }
}


abstract class RoxWidget
{
    /**
     * please implement!
     * render() method does the output.
     */
    abstract public function render();
    
    public function getStylesheets() {
        return array();
    }
    
    public function getScriptfiles() {
        return array();
    }
    
}




abstract class PageWithAJAX extends AbstractBasePage
{
}

abstract class PageWithJavascript extends AbstractBasePage
{
}

/**
 * render() will copy a file in filesystem to output stream
 *
 */
class PageWithFileOutput extends AbstractBasePage
{
    public function render()
    {
        // copy file to output stream
        $filepath = $this->get('filepath');
        
        if (isset($_SESSION['lastRequest'])) {
            PRequest::ignoreCurrentRequest();
        }
        
        if (!file_exists($filepath)) {
            PPHP::PExit();
        }
        $headers = apache_request_headers();
        // Checking if the client is validating his cache and if it is current.
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($filepath))) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filepath)).' GMT', true, 304);
        } else {
            // File not cached or cache outdated, we respond '200 OK' and output the image.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filepath)).' GMT', true, 200);
            header('Content-Length: '.filesize($filepath));
        }
        header('Content-type: '.$this->get('content_type'));
        @copy($filepath, 'php://output');
        PPHP::PExit();
    }
}


/**
 * alias for the PageWithRoxLayout
 *
 */
class RoxPageView extends PageWithRoxLayout
{}





?>