<?php

    /**
     * This factory is used to create entity objects like Group or Member
     * All MVC-model derived classes are born with an instance of it
     *
     * @author Fake51
     */
     
class RoxEntityFactory
{

    /**
     * here's where you store the name of the file of your entity class
     * omit the .php bit - because you have to store a .ini file too
     * and it has to have the same name as the class file, except for ending
     *
     * @var array
     */
    private $_entities = array(
        'Group'             => 'build/groups/group.entity',
        'Member'            => 'build/members/member.entity',
        'GroupMembership'   => 'build/groups/groupmembership.entity',
        'Role'              => 'build/rights/role.entity',
        'Privilege'         => 'build/rights/privilege.entity',
        'RolePrivilege'     => 'build/rights/roleprivilege.entity',
        'MemberRole'        => 'build/rights/memberrole.entity',
        'PrivilegeScope'    => 'build/rights/privilegescope.entity'
        );

    /**
     * This static array stores entity DB definitions - that is, the
     * parsed ini files for entities. Done in order to avoid reloading
     * them on every instantiation
     *
     * @var array
     */
    private static $_entity_defs = array();

    /**
     * This static array stores entity reflection instances - that is, the
     * reflection objects used to instantiate entities. Done in order to avoid
     * instantiating them  over and over
     *
     * @var array
     */
    private static $_entity_classes = array();

    /**
     * Creates an entity object based on input args and returns it
     * Passes any arguments to the entity when creating it
     * NOTE: to pass arguments to this function, pass them as normal!
     * I.E.: $this->_entity_factory('Group', $other_parameter, $other_parameter, etc);
     *
     * @param string Name of entity to instantiate
     * @return mixed An entity based on RoxEntityBase or false on fail
     * @access public
     */
    public function create(/* args */)
    {
        $arguments = func_get_args();
        if (empty($arguments))
        {
            return false;
        }

        $entity_name = array_shift($arguments);
        if (!isset($this->_entities[$entity_name]))
        {
            return false;
        }

        if (!class_exists($entity_name, false))
        {
            require_once(SCRIPT_BASE . $this->_entities[$entity_name] . '.php');
            self::$_entity_defs[$entity_name] = $this->_parse_info(parse_ini_file(SCRIPT_BASE . $this->_entities[$entity_name] . '.ini', true));
            if (!isset(self::$_entity_defs[$entity_name]) || !self::$_entity_defs[$entity_name])
            {
                throw new PException("Could not load {$this->_entities[$entity_name]}.ini to initialize {$entity_name} entity with.");
            }

            // all entity classes are created using reflection classes and stored for reuse
            self::$_entity_classes[$entity_name] = new ReflectionClass($entity_name);
        }

        array_unshift($arguments, self::$_entity_defs[$entity_name]);

        return self::$_entity_classes[$entity_name]->newInstanceArgs($arguments);
    }

    private function _parse_info($array)
    {
        if (!is_array($array) || !isset($array['fields_array']) || !is_array($array['fields_array']) || !isset($array['meta']) || !is_array($array['meta']) || !isset($array['meta']['table_name']) || !isset($array['meta']['primary_key']))
        {
            return false;
        }

        $def = array();

        $def['table_name'] = $array['meta']['table_name'];
        //TODO: change to handle multicolumn primary keys
        if (strstr($array['meta']['primary_key'], ','))
        {
            $pieces = explode(',',$array['meta']['primary_key']);
            foreach ($pieces as &$piece)
            {
                $piece = trim($piece);
            }
            $def['primary_key'] = $pieces;
        }
        else
        {
            $def['primary_key'] = $array['meta']['primary_key'];
        }

        if (isset($array['meta']['auto_incrementing']))
        {
            $def['auto_incrementing'] = (bool) $array['meta']['auto_incrementing'];
        }

        $fields = array();
        foreach ($array['fields_array'] as $field => $line)
        {
            $this_field = array();
            foreach (explode(' ', $line) as $line_part)
            {
                if (!strstr($line_part, ":"))
                {
                    continue;
                }
                list($key, $value) = explode(':', $line_part);

                switch($key)
                {
                    case "type":
                        $this_field['type'] = $value;
                        break;
                    case "allow_null":
                        $this_field['allow_null'] = ((strtolower($value) == 'true') ? true : false);
                        break;
                    case "min":
                        $this_field['min'] = ((is_numeric($value)) ? (int) $value : $value);
                        break;
                    case "max":
                        $this_field['max'] = ((is_numeric($value)) ? (int) $value : $value);
                        break;
                    case "values":
                        $this_field['values'] = explode(',', $value);
                        break;
                    default:
                        return false;
                }
            }

            $fields[$field] = $this_field;
        }

        $def['fields_array'] = $fields;
        
        return $def;
    }

}
