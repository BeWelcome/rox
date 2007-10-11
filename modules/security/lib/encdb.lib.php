<?php
/**
 * All r/w operations on potentially encrypted fields in database
 * should get initiated here.
 * 

TODO: would this enum be a good example for other tables?
TODO: do we use InnoDB?
TODO: do I use the correct character sets?
TODO: should we use binary fields and don't use base64?
TODO: what mysql datatype is appropriate for base64?

CREATE TABLE `safe` (
`id` int(11) NOT NULL auto_increment,
`val_admin` text collate utf8_unicode_ci NOT NULL,
`val_member` text collate utf8_unicode_ci NOT NULL,
`id_member` int(11) NOT NULL,
`encryption` enum('Y', 'N') collate utf8_unicode_ci NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='sensitive data' AUTO_INCREMENT=0;

 * 
 * 
 * @see htdocs/bw/lib/FunctionsCrypt.php
 */

class MOD_encdb extends MOD_enc
{
    const TABLE_NAME = 'safe';
    
    private $_idMember = null;
    
    /**
     * @param int $tablename name of table expressed as an integer,
     * 				e.g. MOD_enc::TABLE_NAME_REF_MEMBER
     * @param int $rownumber number of row in the table afore mentioned
     * @return void
     */
    public function __construct($tablename, $rownumber, $idMember, $keyMember)
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
        parent::__construct($tablename, $rownumber, $keyMember);
        $this->_idMember = $idMember;
    }
    
    /**
     * @param string 	$plainString to be added after encrypting in new row
     * @return int		rownumber of new row
     */
    public function writeEncrypted($plainString)
    {
        $aString = $this->adminEncrypt($plainString);
        $mString = $this->memberEncrypt($plainString);
        
        $query = '
INSERT INTO `' . MOD_encdb::TABLE_NAME . '`(
	`val_admin`,
	`val_member`,
	`id_member`,
	`encryption`
)
VALUES(
	"' . $aString . '",
	"' . $mString . '",
	' . $this->_idMember . ',
	\'Y\'
)';
        $result = $this->dao->query($query);
        $id = $result->insertId(); 
        return $id;
    }
    
    /**
     * @param int		id of recordset
     * @return string	decrypted plain string, gained by member key
     */
    public function readEncryptedMemberValue($id)
    {
        $query = '
SELECT SQL_CACHE `val_member`
FROM `' . MOD_encdb::TABLE_NAME . '`
WHERE `id`=' . $id;
        $result = $this->dao->query($query);
        $row = $result->fetch(PDB::FETCH_OBJ);
        if (!$row) {
            return '[Error]';    // TODO
        }
        $s = $row->val_member;
        return $this->memberDecrypt($s);   
    }

    /**
     * @param int		id of recordset
     * @return string	decrypted plain string, gained by admin key
     */
    public function readEncryptedAdminValue($id)
    {
        $query = '
SELECT SQL_CACHE `val_admin`
FROM `' . MOD_encdb::TABLE_NAME . '`
WHERE `id`=' . $id;
        $result = $this->dao->query($query);
        $row = $result->fetch(PDB::FETCH_OBJ);
        if (!$row) {
            return '[Error]';    // TODO
        }
        $s = $row->val_admin;
        return $this->adminDecrypt($s);   
    }
    
    public function test()
    {
        $s = "Chisinau";
        $id = $this->writeEncrypted($s);
        $val_m = $this->readEncryptedMemberValue($id);
        $val_a = $this->readEncryptedAdminValue($id);
        assert(strcmp($val_m, $val_a) == 0);
        
        echo "<h1>Finished test encdb.lib.php successfully.</h1>";
    }
}
