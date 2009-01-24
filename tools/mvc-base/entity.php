<?php


/**
 * Unlike an application model,
 * an "entity" does actually represent a realworld (or imaginary) object!
 * 
 * This could be a group, a member, whatever.
 * 
 * The entity has its own database connection
 * and can answer questions about its associations in the database.
 *  
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
     * The primary key of the table, most likely id
     * Again, needs an override in the implementations
     *
     * @var string
     */
    protected $_primary_key;

    /**
     * whether the table is using an auto incrementing primary key
     *
     * @var bool
     */
    protected $_auto_incrementing;


    /**
     * An array that details the database layout for the table
     *
     * @var array
     */
    protected $_fields_array;


    /**
     * validateSelf() stores the resulting valid keys => values here
     * used by insert() and update() to construct a SQL query
     *
     * @var array
     */
    protected $_valid_fields;

    /**
     * Determines whether a given entity has been loaded with data
     * Does not show whether or not the entity has manage to init correctly!
     *
     * @var bool
     */
    protected $_has_loaded = false;

    protected $_validation_error = "";

    private $_method_cache = array();
    
    public function __construct($ini_data)
    {
        $this->_parse_ini_data($ini_data);
        parent::__construct();
    }

    protected function _parse_ini_data($ini_data)
    {
        $this->_table_name = $ini_data['table_name'];
        $this->_primary_key = $ini_data['primary_key'];
        $this->_auto_incrementing = $ini_data['auto_incrementing'];
        $this->_fields_array = $ini_data['fields_array'];
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
     * returns the name of the primary key for the entity
     *
     * @access public
     * @return string
     */
    public function getPrimaryKey()
    {
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
        if (!($id = intval($id)) || empty($this->_table_name) || empty($this->_primary_key))
        {
            return false;
        }

        if (is_array($this->_primary_key))
        {
            // function only works for single_column pk's atm
            return false;
        }

        $query = <<<SQL
SELECT *
FROM {$this->_table_name}
WHERE {$this->_primary_key} = '{$id}'
SQL;
        if (!($result = $this->dao->query($query)) || !($data = $result->fetch(PDB::FETCH_ASSOC)))
        {
            $this->_has_loaded = false;
            return false;
        }
        else
        {
            $this->loadData($data);
            return $this;
        }
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
        if (empty($where) || empty($this->_table_name))
        {
            return false;
        }
        $query = <<<SQL
SELECT *
FROM {$this->_table_name}
WHERE {$where}
SQL;
        if (!empty($this->sql_order))
        {
            $query .= "\nORDER BY " . $this->sql_order;
        }
        if (!($result = $this->dao->query($query)) || !($data = $result->fetch(PDB::FETCH_ASSOC)))
        {
            $this->_has_loaded = false;
            return false;
        }
        else
        {
            $this->loadData($data);
            return $this;
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
        if (empty($where) || empty($this->_table_name))
        {
            return false;
        }
        $query = <<<SQL
SELECT *FROM {$this->_table_name}WHERE {$where}SQL;
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

        if (!($result = $this->dao->query($query)))
        {
            return false;
        }
        else
        {
            return $this->loadObjects($result);
        }
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
        if (empty($this->_table_name))
        {
            return false;
        }
        $query = <<<SQL
SELECT *FROM {$this->_table_name}SQL;
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

        if (!($result = $this->dao->query($query)))
        {
            return false;
        }
        else
        {
            return $this->loadObjects($result);
        }
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
        if (!isset($this->_table_name))
        {
            return 0; // if there is no table there are no members. And zero doubles as false = fail
        }

        $field = ((is_array($this->_primary_key)) ? '*' : $field = $this->_primary_key);

        $query = <<<SQL
SELECT COUNT({$field}) AS count
FROM {$this->_table_name}
SQL;
        if (!($result = $this->dao->query($query)))
        {
            return 0;
        }
        else
        {
            $data = $result->fetch(PDB::FETCH_ASSOC);
            return $data['count'];
        }
    }
     
    /**
     * returns a count of all the rows in the entity's table
     *
     * @access public
     * @return int
     */
    public function countWhere($where)
    {
        if (!($table = $this->_table_name))
        {
            return 0; // if there is no table or primary key, there are no members. And zero doubles as false = fail
        }

        $field = ((is_array($this->_primary_key)) ? '*' : $field = $this->_primary_key);

        $query = <<<SQL
SELECT COUNT({$field}) AS count
FROM {$this->_table_name}
WHERE {$where}
SQL;
        if (!($result = $this->dao->query($query)))
        {
            return 0;
        }
        else
        {
            $data = $result->fetch(PDB::FETCH_ASSOC);
            return $data['count'];
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
    protected function loadObjects($result)
    {
        $entities = array();
        $entity_class = get_class($this);
        while ($data = $result->fetch(PDB::FETCH_ASSOC))
        {
            $entity = $this->_entity_factory->create($entity_class);
            $entities[] = $entity->loadFromArray($data);
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
        // TODO: validate array first
        if ($this->loadData($data))
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
     * @param array $array - associative array used to fill object with data
     * @access protected
     */
    protected function loadData($array)
    {
        if (is_array($array) && $this->validateArray($array))
        {
            foreach ($array as $key => $value)
            {
                $this->$key = $value;
            }
            $this->_has_loaded = true;
            return true;
        }
        else
        {
            $this->_has_loaded = false;
            return false;
        }
    }


/****************** INSERT, UPDATE, DELETE SQL functions ******************/

    /**
     * Attempts to insert a row into the database, in the relevant table
     * will build a SQL query based on the set variables in the object
     *
     * @return bool
     * @access protected
     */
    protected function insert()
    {
        if (!empty($this->_has_loaded) || empty($this->_primary_key) || empty($this->_fields_array))
        {
            return false;
        }
        
        // if auto increment is set, the primary key should be empty - if not, something's fishy
        if (!empty($this->_auto_incrementing) && $this->isPKSet())
        {
            return false;
        }
        // conversely, if auto increment is not set, the primary key had better be set
        elseif(empty($this->_auto_incrementing) && !$this->isPKSet())
        {
            return false;
        }

        if (!$this->validateSelf() || !is_array($this->_valid_fields) || count($this->_valid_fields) == 0)
        {
            return false;
        }
        $column_string = implode(',', array_keys($this->_valid_fields));
        $value_string = "'" . implode("','", array_values($this->_valid_fields)) . "'";

        $query = "INSERT INTO {$this->_table_name} ({$column_string}) VALUES ({$value_string})";
        $result = $this->dao->query($query);
        if (!$result)
        {
            return false;
        }

        if (!empty($this->_auto_incrementing) && !is_array($this->_primary_key))
        {
            $pk = $this->_primary_key;
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
     * @access protected
     */
    protected function update()
    {
        if (empty($this->_has_loaded) || empty($this->_primary_key) || empty($this->_fields_array))
        {
            return false;
        }
        
        // if primary key is not loaded with data, don't try to update anything
        if (!$this->isPKSet())
        {
            return false;
        }
        
        if (!$this->validateSelf() || !is_array($this->_valid_fields) || count($this->_valid_fields) == 0)
        {
            return false;
        }

        if (!($where = $this->preparePKWhereString()))
        {
            return false;
        }

        $set_string = "";
        foreach ($this->_valid_fields as $key => $value)
        {
            if (is_array($this->_primary_key))
            {
                if (in_array($key, $this->_primary_key))
                {
                    continue;
                }
            }
            else
            {
                if ($key == $this->_primary_key)
                {
                    continue;
                }
            }
            $value = $this->dao->escape($value);
            $set_string .= (($set_string != '') ? ', ' : '');
            $set_string .= "{$key} = '{$value}'";
        }

        $query = "UPDATE {$this->_table_name} SET {$set_string} WHERE {$where}";
        return $this->dao->exec($query);

    }


    /**
     * Delete a row from a table
     *
     * @access protected
     * @return bool
     */
    protected function delete()
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
    {$this->_table_name}
WHERE
    {$where}
SQL;

        $result = $this->dao->exec($query);

        // make sure entity can't be used after this
        foreach ($this->_fields_array as $field => $val)
        {
            $this->$field = null;
        }

        // TODO: check result before returning it
        return (bool) $result;
    }


/****************** Entity validation functions ******************/

    /**
     * Checks if an array is useable for loading an entity from
     * basically compares it with the entities $_fields_array
     *
     * @param array $array - the array to check. Only associative (or both) arrays work
     * @return bool
     * @access public
     */
    public function validateArray($array)
    {
        // if $_fields_array isn't valid, it's an auto-fail
        if (!isset($this->_fields_array) || !is_array($this->_fields_array))
        {
            return false;
        }

        $valid_array = array();
        foreach ($array as $key => $value)
        {
            if (isset($this->_fields_array[$key]) && $this->validateField($key, $value))
            {
                $valid_array[$key] = $value;
            }

        }
        if (count($valid_array) != count($this->_fields_array))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Checks if the vars set in an entity are valid
     * compares with the entities $_fields_array to make sure that values
     * needed are present and the right type
     *
     * @param bool $strict - whether to do a strict check or a relaxed check
     * @return bool
     * @access public
     */
    public function validateSelf($strict = false)
    {
        // if $_fields_array isn't valid, it's an auto-fail
        if (!isset($this->_fields_array) || !is_array($this->_fields_array))
        {
            return false;
        }
        
        $this->_valid_fields = array();
        foreach ($this->_fields_array as $key => $value)
        {
            if (false !== ($val = $this->$key) && $this->validateField($key, $this->$key))
            {
                $this->_valid_fields[$key] = $this->$key;
            }
        }
        if (($strict && count($this->_valid_fields) != count($this->_fields_array)) || count($this->_valid_fields) == 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Checks a supplied key and value against the $_fields_array to check for validity
     *
     * @param string $key - the field to check
     * @param mixed $value - the value to check
     * @access protected
     * @return bool
     */
    protected function validateField($key, $value)
    {
        if (!strlen($key) || !isset($this->_fields_array[$key]))
        {
            $this->_validation_error = "Field name to check not set in entity";
            return false;
        }

        $lookupkey = $this->_fields_array[$key];

        // if no type is set, accept any type
        if (empty($lookupkey['type']))
        {
            return true;
        }

        if (isset($lookupkey['allow_null']) && $lookupkey['allow_null'] == true && (is_null($value) || $value == ''))
        {
            return true;
        }

        // TODO: implement checks for enums and dates
        switch ($lookupkey['type'])
        {
            case "string":
                if (!is_string($value) ||
                   (isset($lookupkey['min']) && strlen($value) < $lookupkey['min']) ||
                   (isset($lookupkey['max']) && strlen($value) > $lookupkey['max']))
                {
                    $this->_validation_error = "Failed string value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "number":
                if (!is_numeric($value) ||
                   (isset($lookupkey['min']) && $value < $lookupkey['min']) ||
                   (isset($lookupkey['max']) && $value > $lookupkey['max']))
                {
                    $this->_validation_error = "Failed number value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "bool":
                if (!is_bool($value))
                {
                    $this->_validation_error = "Failed bool value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "double":
                if (!is_double($value) ||
                   (isset($lookupkey['min']) && $value < $lookupkey['min']) ||
                   (isset($lookupkey['max']) && $value > $lookupkey['max']))
                {
                    $this->_validation_error = "Failed double value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "float":
                if (!is_float($value) ||
                   (isset($lookupkey['min']) && $value < $lookupkey['min']) ||
                   (isset($lookupkey['max']) && $value > $lookupkey['max']))
                {
                    $this->_validation_error = "Failed float value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "int":
                if (!(intval($value) == $value || is_int($value)) ||
                   (isset($lookupkey['min']) && $value < $lookupkey['min']) ||
                   (isset($lookupkey['max']) && $value > $lookupkey['max']))
                {
                    $this->_validation_error = "Failed int value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "date":
                // a hack, but might be needed - support setting time inside mysql
                if ('now()' == strtolower($value))
                {
                    return true;
                }
                // TODO: consider checking the time part too
                if (preg_match('/[0-9]{4}[-.\/][0-9]{2}[-.\/][0-9]{2}/', $value, $matches))
                {
                    $year = substr($matches[0], 0, 4);
                    $month = substr($matches[0], 5, 2);
                    $day = substr($matches[0], 8, 2);
                }
                elseif (preg_match('/[0-9]{2}[-.\/][0-9]{2}[-.\/][0-9]{4}/', $value, $matches))
                {
                    $day = substr($matches[0], 0, 2);
                    $month = substr($matches[0], 3, 2);
                    $year = substr($matches[0], 6, 4);
                }
                else
                {
                    $this->_validation_error = "Failed date value check for {$key}, value = {$value}, wrong date format";
                    return false;
                }

                // another hack: allow for 'blank' values
                if ($year == '0000' && $month == '00' && $day == '00')
                {
                    return true;
                }


                if (!checkdate($month, $day, $year))
                {
                    $this->_validation_error = "Failed date value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "enum":
                if (!isset($lookupkey['values']) || !is_array($lookupkey['values']) || !in_array($value, $lookupkey['values']))
                {
                    $this->_validation_error = "Failed enum value check for {$key}, value = {$value}";
                    return false;
                }
                break;
            case "set":
                if (!isset($lookupkey['values']) || !is_array($lookupkey['values']))
                {
                    $this->_validation_error = "Failed set value check for {$key}, value = {$value}";
                    return false;
                }
                if ($value == "")
                {
                    return true;
                }
                $vals = explode(',', $value);
                foreach ($vals as $val)
                {
                    if (!in_array($val, $lookupkey['values']))
                    {
                        $this->_validation_error = "Failed set value check for {$key}, value = {$value}";
                        return false;
                    }
                }
                break;
            default:
                $this->_validation_error = "Type not defined for {$key}, value = {$value}";
                return false;
        }
        return true;
    }

    /**
     * returns a string to serve as a where clause, uniquely specifying a row
     *
     * @return mixed string or false on fail
     * @access protected
     */
    protected function preparePKWhereString()
    {
        if (empty($this->_primary_key))
        {
            return false;
        }
        if (is_string($this->_primary_key))
        {
            $pk = $this->_primary_key;
            return "{$this->_primary_key} = {$this->$pk}";
        }
        elseif (is_array($this->_primary_key))
        {
            $string = '';
            foreach ($this->_primary_key as $key)
            {
                $string .= (($string) ? ' AND ' : '');
                $string .= "{$key} = {$this->$key}";
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
        $pk = $this->_primary_key;
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
            if (!($val = $this->$pk))
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
