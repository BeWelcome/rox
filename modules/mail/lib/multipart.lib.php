<?php
/**
 * @author Philipp Hunstein & Seong-Min Kang <info@respice.de>
 * @version v2.0.0 Pre-Alpha
 */
class MOD_mail_Multipart {
    protected $parts = array();
    protected $textParts = array();
    protected $header;
    protected $message;
    protected $boundary;

    public function __construct() {
        $this->boundary = sha1(uniqid(time()));
        $this->header = array();
        $this->header['MIME-Version'] = '1.0';
        $this->header['Content-Type'] = 'multipart/related; boundary="'.$this->boundary.'"';
        $this->header['X-Mailer']     = 'respice platform PT';
    }
    
    public function __get($name) {
        if (!isset($this->$name))
            return false;
        return $this->$name;
    }

    public function addMessage($msg = '', $ctype = 'text/plain', $charset = 'utf-8', $part = false) {
        $pos = strlen($msg) - 3;
        $t = substr($msg, $pos, 1);
        if ($t != "\n") {
            $msg.="\n";
        }
        if ($part === false) {
            $part = count($this->textParts);
        }
        $this->textParts[$part] = "--".$this->boundary."_T\n".
                             "Content-Type: $ctype; charset=$charset\n" .
                             "Content-Transfer-Encoding: 7bit\n" .
                             "\n".$msg;
        return $part;
    }

    public function addAttachment($file, $ctype) {
        if (!file_exists($file))
            return false;
        $fname = basename($file, "/");
        $data = file_get_contents($file);
        $i = count($this->parts);
        $contentId = "part$i.".sprintf('%09d', crc32($fname));
        $this->parts[] = "Content-Type: $ctype; name=\"$fname\"\n" .
                         "Content-Transfer-Encoding: base64\n" .
                         "Content-Disposition: attachment;\n" .
                         " filename=\"$fname\"\n" .
                         "Content-ID: <$contentId>\n" .
                         "\n" .
                         chunk_split(base64_encode($data), 68, "\n");
        return $contentId;
    }
    
    public function addTextAttachment($str, $ctype) {
        $i = count($this->parts);
        $contentId = "part$i.".sprintf('%09d', crc32($str));
        $this->parts[] = "Content-Type: $ctype\n" .
                         "Content-Transfer-Encoding: 7bit\n" .
                         "Content-Disposition: attachment;\n" .
                         "Content-ID: <$contentId>\n" .
                         "\n" .
                         $str."\n";
        return $contentId;
    }

    public function buildMessage() {
        $this->message = "This is a multipart message in mime format.\n";
        $this->message.= "--".$this->boundary."\n".
                         "Content-type: multipart/alternative; boundary=\"".$this->boundary."_T\"\n";
        foreach ($this->textParts as $part) {
            $this->message .= $part;
        }
        $this->message.= "--".$this->boundary."_T--\n";
        foreach ($this->parts as $part) {
            $this->message .= "--".$this->boundary."\n".$part;
        }
        $this->message .= "--".$this->boundary."--\n";
    }
}
?>