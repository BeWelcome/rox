<?php

/**
 * defines an exception class for entities
 * so, any EntityExceptions caught anywhere will
 * come from one place only - guess where
 */
class EntityException extends PException {}

/**
 * Unlike an application model,
 * an "entity" does actually represent a realworld (or imaginary) object!
 *
 * This could be a group, a member, whatever.
 *
 * The entity has its own database connection
 * and can answer questions about its associations in the database.
 *
 * @property PDB dao
 */
class RoxEntityBase extends RoxModelBase
{

    public $sql_order = '';

    /**
     * This is the table that the entity belongs to
     * Entity implementations should obviously override this to be meaningful
     *
     * @var string
     */
    protected $_table_name;

    /**
     * cache for list of columns
     *
     * @var array
     */
    protected $_columns_cache = array();

    /**
     * The primary key of the table, most likely id
     * Again, needs an override in the implementations
     *
     * @var string
     */
    protected $_primary_key;

    /**
     * Determines whether a given entity has been loaded with data
     * Does not show whether or not the entity has manage to init correctly!
     *
     * @var bool
     */
    protected $_has_loaded = false;

    /**
     * contains validation errors after validation
     * wiped on every validation
     *
     * @var array
     */
    protected $_validations = array();

    /**
     * contains validation errors after validation
     * wiped on every validation
     *
     * @var array
     */
    protected $_validation_errors = array();

    private $_method_cache = array();

    /**
     * @var PDB
     */
    protected $_dao = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * init function, replaces part of the __construct function
     *
     * @param array $parameters - array of parameters to store in the entity
     * @param object $dao - data access object
     * @access public
     */
    public function init($store, $dao)
    {
        $this->_parameters = $store;
        $this->_dao = $dao;
    }

    /**
     * returns an array of column names for the entity
     *
     * @access public
     * @return array
     */
    public function getColumns()
    {
        if (empty($this->_columns_cache))
        {
            $this->_columns_cache = array_keys($this->getTableDescription());
        }
        return $this->_columns_cache;
    }

    /**
     * returns the table name for the entity
     *
     * @access public
     * @return string
     */
    public function getTableName()
    {
        return $this->_table_name;
    }

    /**
     * returns an array describing the table of the entity
     *
     * @access public
     * @return array
     * @throws EntityException
     */
    public function getTableDescription()
    {
        if (!($cached_version = $this->_entity_factory->getEntityTableDescription($this)))
        {
            $query = "DESCRIBE `{$this->getTableName()}`";
            try
            {
                if (!($result = $this->dao->query($query)))
                {
                    throw new Exception("");
                }
            }
            catch (Exception $e)
            {
                throw new EntityException("Could not load information for {$this->getTableName()}");
            }
            $info = array();
            while ($data = $result->fetch(PDB::FETCH_ASSOC))
            {
                $info[$data['Field']] = $this->parseDescribeResult($data);
            }
            if (empty($info))
            {
                throw new EntityException("Could not load information for {$this->getTableName()}");
            }
            $this->_entity_factory->storeTableDescription($info, $this);
            $cached_version = $info;
        }
        return $cached_version;
    }

    /**
     * parses an associative array result from a DESCRIBE TABLE call
     * returns an array with more usable info
     *
     * @param array $data
     * @access private
     * @return array
     */
    private function parseDescribeResult($data)
    {
        $return = array();
        $return['allow_null'] = ((strtolower($data['Null']) == 'no') ? false : true);
        $return['default'] = ((!empty($data['Default']) && strtolower($data['Default']) != 'null') ? $data['Default'] : null);
        $return['auto_increment'] = ((!empty($data['Extra']) && stristr($data['Extra'], 'auto_increment') !== false) ? true : false);
        list($return['type'], $return['values'], $return['min'], $return['max'], $return['unsigned']) = $this->getDataType($data['Type']);
        $return['keytype'] = '';
        if (!empty($data['Key']))
        {
            switch(strtolower($data['Key']))
            {
                case "pri":
                    $return['keytype'] = "primary";
                    break;

            }
        }
        return $return;
    }

    /**
     * returns the type of data and possible values if any
     * values are only returned for enum and set
     *
     * @param string $type
     * @access private
     * @return array
     */
    private function getDataType($type)
    {
        if (false === ($pos = strpos($type, '(')))
        {
            $datatype = $type;
            $values = $min = $max = null;
        }
        else
        {
            $datatype = substr($type, 0, $pos);
            $value_string = substr($type, ($pos+1), -1);
            if (in_array(strtolower($datatype), array('enum', 'set')))
            {
                $values = explode(',', str_replace("'",'',$value_string));
                $min = $max = null;
            }
            else
            {
                $values = null;
                $min = 0;
                $max = $value_string;
            }
        }
        $unsigned = ((stristr($type, 'unsigned')) ? true : false);
        return array($datatype, $values, $min, $max, $unsigned);
    }

    /**
     * returns the name of the primary key for the entity
     * return val is string if primary key is single field
     * array if primary key spans fields, and null
     * for no primary key
     *
     * @access public
     * @return string|array|null
     */
    public function getPrimaryKey()
    {
        if (empty($this->_primary_key))
        {
            $info = $this->getTableDescription();
            $return = array();
            foreach ($info as $key => $array)
            {
                if ($array['keytype'] == 'primary')
                {
                    $return[] = $key;
                }
            }
            $this->_primary_key = ((count($return)) ? ((count($return) == 1) ? $return[0] : $return) : null);
        }
        return $this->_primary_key;
    }

    /**
     * returns the value of the entity's primary key
     *
     * @access public
     * @return string
     */
    public function getPKValue()
    {
        $array = array();
        if (is_array($this->_primary_key))
        {
            foreach ($this->_primary_key as $key)
            {
                $array[$key] = $this->$key;
            }
            return $array;
        }
        else
        {
            $pk = $this->_primary_key;
            return $this->$pk;
        }
    }


    function __call($key, $args)
    {
        if (empty($args)) {
            return parent::__call($key, $args);
        } else if (!method_exists($this, $methodname = 'get_'.$key)) {
            return false;
        } else {
            $key = $key.':'.serialize($args);
            if (isset($this->_method_cache[$key])) {
                return $this->_method_cache[$key];
            } else if (empty($args)) {
                return $this->_method_cache[$key] = $this->$methodname();
            } else {
                return $this->_method_cache[$key] = call_user_func_array(array($this, 'get_'.$methodname), $args);
            }
        }
    }

    /**
     * Lets the world know if it's been loaded with data properly or not
     *
     * @access public
     * @return bool
     */
    public function isLoaded()
    {
        return $this->_has_loaded;
    }


/****************** SQL SELECT functions ******************/

    /**
     * load an object by it's id
     *
     * @param int $id - id of the group to load
     * @access public
     * @return mixed false or an object of class $this
     */
    public function findById($id)
    {
        if (!$this->getTableName() || !$this->getPrimaryKey())
        {
            return false;
        }
        $id = $this->dao->escape($id);

        if (is_array($this->getPrimaryKey()))
        {
            // function only works for single_column pk's atm
            return false;
        }

        $query = <<<SQL
SELECT *
FROM `{$this->getTableName()}`
WHERE `{$this->getPrimaryKey()}` = '{$id}'
SQL;
        return $this->queryForEntity($query);
    }

    /**
     * load an object by a specified sql WHERE clause
     *
     * @param string $where - sql where clause to use
     * @access public
     * @return mixed false or $this
     */
    public function findByWhere($where)
    {
        if (empty($where) || !$this->getTableName())
        {
            return false;
        }
        $query = <<<SQL
SELECT *
FROM `{$this->getTableName()}`
WHERE {$where}
SQL;
        if (!empty($this->sql_order))
        {
            $query .= "\nORDER BY " . $this->sql_order;
        }

        return $this->queryForEntity($query);

    }


    /**
     * runs an SQL query to find an entity, loads the current
     * entity with data if it finds it
     *
     * @param string $query
     * @access protected
     * @return object|bool
     * @throws EntityException
     */
    protected function queryForEntity($query)
    {
        try
        {
            if (!($result = $this->dao->query($query)) || !($data = $result->fetch(PDB::FETCH_ASSOC)))
            {
                $this->_has_loaded = false;
                return false;
            }
            else
            {
                $this->loadEntity($data);
                return $this;
            }
        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to load entity with sql: {$query}");
            }
            return false;
        }
    }

    /**
     * runs an SQL query to find several entities
     *
     * @param string $query
     * @access protected
     * @return array
     * @throws EntityException
     */
    protected function queryForEntities($query)
    {
        try
        {
            if (!($result = $this->dao->query($query)))
            {
                return array();
            }
            else
            {
                return $this->loadEntities($result);
            }
        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to load entity with sql: {$query}");
            }
            return array();
        }
    }

    /**
     * load an object by a specified sql WHERE clause
     *
     * @param string $where - sql where clause to use
     * @param integer $offset - offset to start from. Defaults to 0
     * @param integer $limit - how many results to limit the query to. Defaults to 0 (no limit)
     * @access public
     * @return mixed false or array or entities
     */
    public function findByWhereMany($where, $offset = 0, $limit = 0)
    {
        if (empty($where) || !$this->getTableName())
        {
            return false;
        }
        $query = <<<SQL
SELECT *
FROM `{$this->getTableName()}`
WHERE {$where}
SQL;
        if (!empty($this->sql_order))
        {
            $query .= "\nORDER BY " . $this->sql_order;
        }

        if (intval($limit) && intval($offset))
        {
            $query .= "\nLIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }
        elseif (intval($limit))
        {
            $query .= "\nLIMIT " . intval($limit);
        }

        return $this->queryForEntities($query);
    }

    /**
     * lets you load a single entity using an sql query
     * should only be used in cases where the other find methods won't work
     *
     * @param string $sql
     * @access public
     * @return object
     */
    public function findBySQL($sql)
    {
        if (empty($sql))
        {
            return false;
        }
        return $this->queryForEntity($sql);
    }

    /**
     * lets you load an array of entities using an sql query
     * should only be used in cases where the other find methods won't work
     *
     * @param string $sql
     * @access public
     * @return array
     */
    public function findBySQLMany($sql)
    {
        if (empty($sql))
        {
            return array();
        }
        return $this->queryForEntities($sql);
    }

    /**
     * loads all rows from a table and returns them as an array of class $this
     *
     * @param integer $offset - offset to start from. Defaults to 0
     * @param integer $limit - how many results to limit the query to. Defaults to 0 (no limit)
     * @access public
     * @return mixed false or an array of objects of class $this
     */
    public function findAll($offset = 0, $limit = 0)
    {
        if (!$this->getTableName())
        {
            return false;
        }
        $query = <<<SQL
SELECT *
FROM `{$this->getTableName()}`
SQL;
        if (!empty($this->sql_order))
        {
            $query .= "\nORDER BY " . $this->sql_order;
        }

        if (intval($limit) && intval($offset))
        {
            $query .= "\nLIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }
        elseif (intval($limit))
        {
            $query .= "\nLIMIT " . intval($limit);
        }

        return $this->queryForEntities($query);
    }

/****************** SQL COUNT() functions ******************/

    /**
     * returns a count of all the rows in the entity's table
     *
     * @access public
     * @return int
     */
    public function countAll()
    {
        if (!$this->getTableName())
        {
            return 0; // if there is no table there are no members. And zero doubles as false = fail
        }

        $field = ((is_array($this->getPrimaryKey())) ? '*' : $field = "`{$this->getPrimaryKey()}`");

        $query = <<<SQL
SELECT COUNT({$field}) AS count
FROM `{$this->getTableName()}`
SQL;
        return $this->sqlCount($query);
    }

    /**
     * returns a count of all the rows in the entity's table
     *
     * @access public
     * @return int
     */
    public function countWhere($where)
    {
        if (!($table = $this->getTableName()))
        {
            return 0; // if there is no table or primary key, there are no members. And zero doubles as false = fail
        }

        $field = ((is_array($this->getPrimaryKey())) ? '*' : $field = "`{$this->getPrimaryKey()}`");

        $query = <<<SQL
SELECT COUNT({$field}) AS count
FROM `{$this->getTableName()}`
WHERE {$where}
SQL;
        return $this->sqlCount($query);
    }

    /**
     * executes sql to count rows in a table
     *
     * @param string $query
     * @access protected
     * @return int
     * @throws EntityException
     */
    protected function sqlCount($query)
    {
        try
        {
            if (!($result = $this->dao->query($query)))
            {
                return 0;
            }
            else
            {
                $data = $result->fetch(PDB::FETCH_BOTH);
                $return = isset($data['count']) ? $data['count'] : null;
                $return = is_null($return) && isset($data[0]) ? $data[0] : $return;
                $return = is_null($return) ? 0 : $return;
                return intval($return);
            }
        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to count rows with sql: {$query}");
            }
            return 0;
        }

    }

/****************** Object load functions ******************/

    /**
     * Creates entities from a result set, using findById
     *
     * @param resource $result - the DB resource result from the lookup
     * @access protected
     * @return array
     */
    protected function loadEntities($result)
    {
        $entities = array();
        $entity_class = get_class($this);
        while ($data = $result->fetch(PDB::FETCH_ASSOC))
        {
            $entity = $this->createEntity($entity_class);
            if ($entity->loadFromArray($data))
            {
                $entities[] = $entity;
            }
        }
        return $entities;
    }


    /**
     * Creates entities from a result set, using findById
     *
     * @param array $data - an array of data based on a DB lookup
     * @access protected
     * @return object - passes itself back
     */
    public function loadFromArray($data)
    {
        if ($this->loadEntity($data))
        {
            return $this;
        }
        else
        {
            return false;
        }
    }

    /**
     * Fills up an object with data
     *
     * @param array $data - associative array used to fill object with data
     * @access protected
     * @return bool
     */
    protected function loadEntity($data)
    {
        if (!is_array($data))
        {
            $this->_has_loaded = false;
            return false;
        }
        foreach ($this->getColumns() as $field)
        {
            if (isset($data[$field]))
            {
                $this->$field = $data[$field];
            }
        }
        $this->_has_loaded = (($this->isPKSet()) ? true : false);
        if (!$this->_has_loaded)
        {
            $this->wipeEntity();
        }
        return $this->_has_loaded;
    }

    /**
     * wipes an entity clean for data and resets the has_loaded flag
     *
     * @access public
     */
    public function wipeEntity()
    {
        foreach ($this->getColumns() as $field)
        {
            $this->$field = null;
        }
        $this->_has_loaded = false;
    }

/****************** INSERT, UPDATE, DELETE SQL functions ******************/

    /**
     * Attempts to insert a row into the database, in the relevant table
     * will build a SQL query based on the set variables in the object
     *
     * @return bool
     * @access public
     * @throws EntityException
     */
    public function insert()
    {
        if (!empty($this->_has_loaded) || !$this->getPrimaryKey() || !$this->getColumns())
        {
            return false;
        }

        $fields = $values = array();
        $description = $this->getTableDescription();
        foreach ($this->getTableDescription() as $field => $info)
        {
            if (($info['keytype'] == 'primary' && $info['auto_increment'] == true && !$this->$field) || $this->$field === false)
            {
                continue;
            }
            if (!$this->validateField($field, $description))
            {
                return false;
            }
            $fields[] = "`{$field}`";
            $values[] = "'" . $this->dao->escape($this->$field). "'";
        }

        $query = "INSERT INTO {$this->getTableName()} (" . implode(',',$fields) . ") VALUES (" . implode(',',$values) . ")";
        try
        {
            if (!($result = $this->dao->query($query)))
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to insert row into {$this->getTableName()} with sql: {$query}");
            }
            return false;
        }

        if (!is_array($this->getPrimaryKey()))
        {
            $pk = $this->getPrimaryKey();
            $this->$pk = $result->insertId();
        }

        // seeing as this entity now matches a row in the db, it's loaded
        $this->_has_loaded = true;
        return true;
    }

    /**
     * Attempts to update a row into the database, in the relevant table
     * will build a SQL query based on the set variables in the object
     *
     * @return bool
     * @access public
     * @throws EntityException
     */
    public function update()
    {
        if (empty($this->_has_loaded) || !$this->getPrimaryKey() || !$this->getColumns())
        {
            return false;
        }

        // if primary key is not loaded with data, don't try to update anything
        if (!$this->isPKSet())
        {
            return false;
        }

        if (!$this->validateSelf())
        {
            return false;
        }

        if (!($where = $this->preparePKWhereString()))
        {
            return false;
        }

        $set_string = "";
        $tableDescription = $this->getTableDescription();
        foreach ($this->getColumns() as $key)
        {
            if (is_array($this->getPrimaryKey()))
            {
                if (in_array($key, $this->getPrimaryKey()))
                {
                    continue;
                }
            }
            else
            {
                if ($key == $this->getPrimaryKey())
                {
                    continue;
                }
            }
            $set_string .= (($set_string != '') ? ', ' : '');
            if ($this->$key) {
                $set_string .= "{$key} = '{$this->dao->escape($this->$key)}'";
            } else {
                if ($tableDescription[$key]['allow_null']) {
                    $set_string .= "{$key} = NULL";
                } else {
                    $set_string .= "{$key} = 0";
                }
            }
        }

        $query = "UPDATE `{$this->getTableName()}` SET {$set_string} WHERE {$where}";

        try
        {
            $this->dao->exec($query);
            return true;

        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to update row in {$this->getTableName()} with sql: {$query}");
            }
            return false;
        }
    }


    /**
     * Delete a row from a table
     *
     * @access public
     * @return bool
     * @throws EntityException
     */
    public function delete()
    {
        if (!$this->_has_loaded || !$this->isPKSet())
        {
            return false;
        }

        if (!($where = $this->preparePKWhereString()))
        {
            return false;
        }

        $query = <<<SQL
DELETE FROM
    `{$this->getTableName()}`
WHERE
    {$where}
SQL;

        try
        {
            $this->dao->exec($query);
        }
        catch (Exception $e)
        {
            if (PVars::get()->debug)
            {
                throw new EntityException("Failed to delete row from {$this->getTableName()} with sql: {$query}");
            }
            return false;
        }

        // make sure entity can't be used after this
        $this->wipeEntity();

        // TODO: check result before returning it
        return true;
    }


/****************** Entity validation functions ******************/

    /**
     * Checks if the vars set in an entity are valid
     * compares with the entities $_fields_array to make sure that values
     * needed are present and the right type
     *
     * @param bool $strict - whether to do a strict check or a relaxed check
     * @return bool
     * @access public
     */
    public function validateSelf()
    {
        $this->_validation_errors = array();
        $info = $this->getTableDescription();
        foreach ($this->getColumns() as $field)
        {
            if (!$this->validateField($field, $info))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks a supplied key and value against the $_fields_array to check for validity
     *
     * @param string $key - the field to check
     * @param array $info - table description
     * @access protected
     * @return bool
     */
    protected function validateField($key, $info)
    {
        if (!strlen($key) || !in_array($key, array_keys($info)))
        {
            $this->_validation_errors[] = "Field name ({$key}) to check is not a column in entity table";
            return false;
        }
        $datatype = $info[$key]['type'];
        $value = $this->$key;
        if (in_array($key, $this->_validations))
        {
            // perform strict validation on field
            if (($value == null && !$info[$key]['allow_null']) || !$this->checkDataType($value, $datatype))
            {
                $type = gettype($value);
                $this->_validation_errors[] = "Failed check for {$key} (type: {$datatype}) - value: {$value} / type: {$type}";
                return false;
            }
            if (($value == null && $info[$key]['allow_null']) || $this->performStrictValueCheck($value, $info[$key]))
            {
                return true;
            }
            else
            {
                $type = gettype($value);
                $this->_validation_errors[] = "Failed check for {$key} (type: {$datatype}) - value: {$value} / type: {$type}";
                return false;
            }
        }
        else
        {
            // perform relaxed validation on field
            if ($value == null || $this->checkDataType($value, $datatype))
            {
                return true;
            }
            else
            {
                $type = gettype($value);
                $this->_validation_errors[] = "Failed check for {$key} (type: {$datatype}) - value: {$value} / type: {$type}";
                return false;
            }
        }
    }

    private function performStrictValueCheck($value, $field_info)
    {
        switch (strtolower($field_info['type']))
        {
            case "tinyint":
            case "smallint":
            case "mediumint":
            case "int":
            case "bigint":
                return (((!is_int($value) && intval($value) != $value) || ($field_info['unsigned'] && $value < 0) || (strlen($value) > 0 && intval($value) === 0)) ? false : true);
                break;
            case "float":
            case "double":
            case "decimal":
                return (((!is_float($value) && floatval($value) != $value) || ($field_info['unsigned'] && $value < 0) || (strlen($value) > 0 && intval($value) === 0)) ? false : true);
                break;
            case "char":
            case "varchar":
            case "binary":
            case "varbinary":
                if (empty($field_info['min']) || empty($field_info['max']))
                {
                    return true;
                }
                elseif (strlen($value) >= $field_info['min'] && strlen($value) <= $field_info['max'])
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case "tinyblob":
            case "tinytext":
                return ((strlen($value) <= 255) ? true : false);
                break;
            case "longblob":
            case "longtext":
                return ((strlen($value) <= 4294967295) ? true : false);
                break;
            case "mediumtext":
            case "mediumblob":
                return ((strlen($value) <= 16777215) ? true : false);
                break;
            case "text":
            case "blob":
                return ((strlen($value) <= 65535) ? true : false);
                break;
            case "enum":
            case "set":
                return ((in_array($value, $field_info['values'])) ? true : false);
                break;
            case "date":
            case "datetime":
            case "time":
            case "timestamp":
            case "year":
                if (preg_match('/(now\(\)|interval|day|month|year)/i', $value) || strtotime($value) > 0)
                {
                    return true;
                }
                break;
            default:
                return false;
        }
    }

    private function checkDataType($value, $datatype)
    {
        switch (strtolower($datatype))
        {
            case "tinyint":
            case "smallint":
            case "mediumint":
            case "int":
            case "bigint":
                return ((is_int($value) || intval($value) == $value) ? true : false);
                break;
            case "float":
            case "double":
            case "decimal":
                return ((is_float($value) || floatval($value) == $value) ? true : false);
                break;
            case "char":
            case "varchar":
            case "text":
            case "tinytext":
            case "mediumtext":
            case "longtext":
            case "binary":
            case "varbinary":
            case "blob":
            case "tinyblob":
            case "mediumblob":
            case "longblob":
            case "enum":
            case "set":
            case "date":
            case "datetime":
            case "time":
            case "timestamp":
            case "year":
                return ((is_string($value) || (string)$value == $value) ? true : false);
                break;
            default:
                return false;
        }
    }

    public function getValidationErrors()
    {
        return $this->_validation_errors;
    }

/**************** other stuff ***********************/

    /**
     * returns a string to serve as a where clause, uniquely specifying a row
     *
     * @return string|false
     * @access protected
     */
    protected function preparePKWhereString()
    {
        if (!$this->getPrimaryKey())
        {
            return false;
        }
        if (is_string($this->getPrimaryKey()))
        {
            $pk = $this->getPrimaryKey();
            return "`{$pk}` = '{$this->dao->escape($this->$pk)}'";
        }
        elseif (is_array($this->getPrimaryKey()))
        {
            $string = '';
            foreach ($this->getPrimaryKey() as $key)
            {
                $string .= (($string) ? ' AND ' : '');
                $string .= "`{$key}` = '{$this->dao->escape($this->$key)}'";
            }
            return $string;
        }
        else
        {
            return false;
        }
    }

    /**
     * checks whether all the primary key fields of an entity are set
     *
     * @return bool
     * @access public
     */
    public function isPKSet()
    {
        $pk = $this->getPrimaryKey();
        if (is_array($pk))
        {
            foreach ($pk as $field)
            {
                if (!($val = $this->$field))
                {
                    return false;
                }
            }
        }
        elseif (is_string($pk))
        {
            if (!($val = $this->$pk) && $val !== 0 && $val !== '0')
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        return true;
    }

}

?>
