<?php


class FlaglistModel extends PAppModel
{
    public function getLanguages()
    {
        
        $db_vars = PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
        
        $dbresult = $dao->query('
SELECT DISTINCT languages.*
FROM languages, words
WHERE languages.id = words.IdLanguage
AND words.code = \'WelcomeToSignup\'
ORDER BY FlagSortCriteria
        ');
        
        $langs = array();
        while ($row = $dbresult->fetch(PDB::FETCH_OBJ)) {
            $langs[] = $row;
        }
        return $langs;
        
    }
}


?>