<?php


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


?>