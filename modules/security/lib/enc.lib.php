<?php
/**
 * All encryption / decryption should get routed here.
 * 
 * @see htdocs/bw/lib/FunctionsCrypt.php
 */
class MOD_enc
{
    /**
     * encryption strategy
     */
    const MODE = MCRYPT_MODE_CFB;
    const CIPHER = MCRYPT_DES;
    
    // every encrypted value has a single referencing
	// value in one of the following tables
    const TABLE_NAME_REF_MEMBER = 1;    // table members
    const TABLE_NAME_REF_ADDR = 2;      // table addresses
    private static $_supportedTables =
        array(MOD_enc::TABLE_NAME_REF_MEMBER, MOD_enc::TABLE_NAME_REF_ADDR);

    /**
     * @see init method
     */
    private static $_cipherAndMode = null;
    private static $_ivSize = null;
    private static $_keySize = null;
    
    /**
     * key for encrypting by admin; this key is stored in
     * a separate file
     * 
     * @see initAdmin method
     */
    private static $_keyAdmin = null;
        
    /**
     * initialization vector
     * @see __construct
     */
    private $_ivAdmin = null;
    
    /**
     * individual key for encryption of member data;
     * this key is provided in every login as an offset 
     * of the password
     * 
     * @see __construct
     */
    private $_keyMember;
    
    /**
     * initializiation vector
     * @see initMember member
     */
    private $_ivMember;
    
    /**
     * tablename and rownumber are required to build the initialization
     * vector
     * 
     * @param int $tablename name of table expressed as an integer,
     * 				e.g. TABLE_NAME_REF_MEMBER
     * @param int $rownumber number of row in the table afore mentioned
     * @return void
     */
    public function __construct($tablename, $rownumber, $keyMember)
    {
        if (!in_array('mcrypt', get_loaded_extensions())) {
            throw new PException('Module "mcrypt" not found!');
        }
        
        // TODO: sort of a static constructor; any better strategies
        // for doing this in PHP?
        if (empty(MOD_enc::$_keyAdmin)) {
            $this->initAdmin();
        }
        
        if (!in_array($tablename, MOD_enc::$_supportedTables)) {
            throw new PException('Unsupported table!');
        }
        
        if (!is_int($rownumber) || $rownumber < 0) {
            throw new PException('Missing valid rownumber!');
        }
        
        // TODO: tablename and rownumber are used for initialization
        // vector; did I understand the idea of the iv wrong? are there
        // faster/safer alternatives?
        $iv = $tablename * $rownumber;
        $this->_ivAdmin = mcrypt_create_iv(MOD_enc::$_ivSize, $iv);
        
        $this->initMember($keyMember);
        $this->_ivMember = mcrypt_create_iv(MOD_enc::$_ivSize, $iv);
    }
    
    private function initMember($keyMember)
    {
        $this->_keyMember = substr($keyMember, 0, MOD_enc::$_keySize);        
    }
    
    private function initAdmin()
    {
        // file contains one line, e. g.
        // $key = 'xm4-s90.3kf1S9';
        require_once SCRIPT_BASE.'inc/enckey.inc.php';
        if (empty($key)) {
            throw new PException('Security key missing!');
        }
        
        MOD_enc::$_cipherAndMode =
            mcrypt_module_open(
                MOD_enc::CIPHER, '', MOD_enc::MODE, '');
        
        MOD_enc::$_ivSize = 
            mcrypt_enc_get_iv_size(MOD_enc::$_cipherAndMode);
        
        MOD_enc::$_keySize =
            mcrypt_enc_get_key_size(MOD_enc::$_cipherAndMode);
               
        MOD_enc::$_keyAdmin = substr($key, 0, MOD_enc::$_keySize);        
    }
    
    protected function adminEncrypt($plainString)
    {
        mcrypt_generic_init(MOD_enc::$_cipherAndMode, MOD_enc::$_keyAdmin, $this->_ivAdmin);
        $result = base64_encode(mcrypt_generic(MOD_enc::$_cipherAndMode, $plainString));
        mcrypt_generic_deinit(MOD_enc::$_cipherAndMode);
        return $result;
    }

    protected function adminDecrypt($encryptedString)
    {
        mcrypt_generic_init(MOD_enc::$_cipherAndMode, MOD_enc::$_keyAdmin, $this->_ivAdmin);
        $result = trim(
            mdecrypt_generic(MOD_enc::$_cipherAndMode, base64_decode($encryptedString)));
        mcrypt_generic_deinit(MOD_enc::$_cipherAndMode);
        return $result;
    }

    protected function memberEncrypt($plainString)
    {
        mcrypt_generic_init(MOD_enc::$_cipherAndMode, $this->_keyMember, $this->_ivMember);
        $result = base64_encode(mcrypt_generic(MOD_enc::$_cipherAndMode, $plainString));
        mcrypt_generic_deinit(MOD_enc::$_cipherAndMode);
        return $result;
    }

    protected function memberDecrypt($encryptedString)
    {
        mcrypt_generic_init(MOD_enc::$_cipherAndMode, $this->_keyMember, $this->_ivMember);
        $result = trim(
            mdecrypt_generic(MOD_enc::$_cipherAndMode, base64_decode($encryptedString)));
        mcrypt_generic_deinit(MOD_enc::$_cipherAndMode);
        return $result;
    }
    
    public function test()
    {
        $s = "New York";
        $enc = $this->adminEncrypt($s);
        $dec = $this->adminDecrypt($enc);
        assert(strcmp($s, $dec) === 0);

        $s = "Moldova";
        $enc = $this->memberEncrypt($s);
        $dec = $this->memberDecrypt($enc);
        assert(strcmp($s, $dec) === 0);

        echo "<h1>Finished test enc.lib.php successfully.</h1>";
        
    }
    
}
