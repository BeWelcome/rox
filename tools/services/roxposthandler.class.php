<?php


/**
 * The PPostHandler sucks, so we make a new one.
 * Unlike the singleton PPostHandler, this one will be nicest dynamic OOP.
 * 
 * The handler will be called from RoxLauncher.
 */
class RoxPostHandler extends ObjectWithInjection
{
    private $_registered_callbacks = array();
    
    private $classes;

    public function __sleep()
    {
        return array('_registered_callbacks');
    }
    
    public function setClasses(array $classes)
    {
        $this->classes = $classes;
    }

    public function getCallbackAction(array $post_args)
    {
        if (isset($post_args['formkit_redirect_req'])) {
            $redirect_req = $post_args['formkit_redirect_req'];
        } else {
            $redirect_req = false;
        }
        
        if (isset($post_args['formkit_memory_recovery'])) {
            $mem_from_recovery = unserialize(stripslashes(htmlspecialchars_decode($post_args['formkit_memory_recovery'])));
        } else {
            $mem_from_recovery = false;
        }
        
        if (!is_array($post_args) || !isset($post_args['posthandler_callback_id'])) {
            // the form did not contain any posthandler_callback_id
            return false;
        } else {
            $key_on_page = $post_args['posthandler_callback_id'];
            $key_in_table = PFunctions::hex2base64(sha1($key_on_page));
            
            if (isset($this->_registered_callbacks[$key_in_table])) {
                
                // stored callback action
                $action_attributes = $this->_registered_callbacks[$key_in_table];
                
                // increment counter
                $this->_registered_callbacks[$key_in_table]['count']++;
                
            } else {
                // session has expired.
                // we can't determine the callback.
                $action_attributes = $this->getExpiredCallbackActionAttributes($post_args);
            }
            
            if (!$action_attributes) {
                return false;
            } else {
                $action_attributes['redirect_req'] = $redirect_req;
                $action_attributes['mem_from_recovery'] = $mem_from_recovery;
                
                $action = new ReadOnlyObject($action_attributes);
                return $action;
            }
        }
    }
    
    
    
    protected function getExpiredCallbackActionAttributes($post_args)
    {
        if (!isset($post_args['posthandler_callback_classname'])) {
            return false;
        } else if (!isset($post_args['posthandler_callback_methodname'])) {
            return false;
        } else {
            $classname_crypt = $post_args['posthandler_callback_classname'];
            $methodname_crypt = $post_args['posthandler_callback_methodname'];
            $classname = false;
            $methodname = false;
            $secret_word = $this->getSecretWord();
            
            if (!is_array($this->classes)) {
                
                // the classes have to be set from outside
                echo __METHOD__ .' - please set $this->classes';
                
            } else foreach (@$this->classes as $classname_i) {
                
                $classname_i_crypt = PFunctions::hex2base64(sha1($classname_i . $secret_word));
                 
                if ($classname_i_crypt == $classname_crypt) {
                    // found the class!!
                    $classname = $classname_i;
                    break;
                }
            }
            
            if (!$classname) {
                // no such class
                return false;
            } else foreach (get_class_methods($classname) as $methodname_i) {
                
                $methodname_i_crypt = PFunctions::hex2base64(sha1($methodname_i . $secret_word));
                 
                if ($methodname_i_crypt == $methodname_crypt) {
                    // found the method!!
                    $methodname = $methodname_i;
                    break;
                }
            }
            
            if (!$methodname) {
                // no such method in the class
                return false;
            } else {
                // another callback action
                return array(
                    'classname' => $classname,
                    'methodname' => $methodname,
                    'count' => -1,  // indicates that session has expired
                );
            }
        }
    }
    
    
    
    public function registerCallbackMethod($classname, $methodname, $mem_resend = [])
    {
        do {
            $random_string = PFunctions::randomString(42); 
            $key_on_page = PFunctions::hex2base64(sha1($classname.$random_string.$methodname));
            $key_in_table = PFunctions::hex2base64(sha1($key_on_page));
        } while (
            // try to avoid duplicates
            isset($this->_registered_callbacks[$key_in_table])
        );
        
        $this->_registered_callbacks[$key_in_table] = array(
            'key_on_page' => $key_on_page,
            'classname' => $classname,
            'methodname' => $methodname,
            'count' => 0,
            'mem_resend' => new ReadWriteObject($mem_resend)
        );
        
        $secret_word = $this->getSecretWord();
        
        $classname_crypt = PFunctions::hex2base64(sha1($classname . $secret_word));
        $methodname_crypt = PFunctions::hex2base64(sha1($methodname . $secret_word));
        
        return '
        <input type="hidden" name="posthandler_callback_id" value="'.$key_on_page.'"/>
        <input type="hidden" name="posthandler_callback_classname" value="'.$classname_crypt.'"/>
        <input type="hidden" name="posthandler_callback_methodname" value="'.$methodname_crypt.'"/>';
    }
    
    protected function getSecretWord() {
        if (!$secret_word = $this->secret_word) {
            $secret_word = 'xyz';
        }
        return $secret_word;
    }
}


?>
