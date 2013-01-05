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
        'Address'           => 'build/members/address.entity',
        'BlogEntity'        => 'build/blog/blog.entity',
        'Comment'           => 'build/members/comment.entity',
        'Donation'          => 'build/donate/donation.entity',
        'Feedback'          => 'build/about/feedback.entity',
        'Gallery'           => 'build/gallery/gallery.entity',
        'GalleryComment'    => 'build/gallery/gallerycomment.entity',
        'GalleryItem'       => 'build/gallery/galleryitem.entity',
        'Geo'               => 'build/geo/geo.entity',
        'GeoHierarchy'      => 'build/geo/geohierarchy.entity',
        'GeoAlternateName'  => 'build/geo/geoaltname.entity',
        'GeoUse'            => 'build/geo/geouse.entity',
        'GeoType'           => 'build/geo/geotype.entity',
        'Group'             => 'build/groups/group.entity',
        'GroupMembership'   => 'build/groups/groupmembership.entity',
        'Subgroup'          => 'build/subgroups/subgroup.entity',
        'Language'          => 'build/rox/language.entity',
        'Member'            => 'build/members/member.entity',
        'MemberLanguage'    => 'build/members/memberlanguage.entity',
        'MemberRole'        => 'build/rights/memberrole.entity',
        'Message'           => 'build/messages/message.entity',
        'ProfileVisit'      => 'build/members/profilevisit.entity',
        'Note'              => 'build/notify/note.entity',
        'Post'              => 'build/forums/post.entity',
        'PostVote'          => 'build/forums/postvote.entity',
        'Privilege'         => 'build/rights/privilege.entity',
        'PrivilegeScope'    => 'build/rights/privilegescope.entity',
        'Role'              => 'build/rights/role.entity',
        'RolePrivilege'     => 'build/rights/roleprivilege.entity',
        'Thread'            => 'build/forums/thread.entity',
        'ThreadVote'        => 'build/forums/threadvote.entity',
        'VolunteerBoard'    => 'build/admin/volunteerboard.entity',
        );

    /**
     * This static array stores entity DB definitions - that is, the
     * parsed DESCRIBE queries done
     *
     * @var array
     */
    private static $_table_descriptions = array();

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

        if (empty(self::$_entity_classes[$entity_name]))
        {
            require_once(SCRIPT_BASE . $this->_entities[$entity_name] . '.php');

            // all entity classes are created using reflection classes and stored for reuse
            self::$_entity_classes[$entity_name] = new ReflectionClass($entity_name);
        }

        return self::$_entity_classes[$entity_name]->newInstanceArgs($arguments);
    }


    /**
     * stores the table description for a given entity
     *
     * @param array $info - array of table info
     * @param object $entity - an entity object
     * @access public
     */
    public function storeTableDescription($info, RoxEntityBase $entity)
    {
        if (!is_array($info) || !empty(self::$_table_descriptions[get_class($entity)]))
        {
            return;
        }
        self::$_table_descriptions[get_class($entity)] = $info;
        return;
    }

    /**
     * fetches the table description for a given entity
     *
     * @param object $entity - entity object to get info for
     * @access public
     * @return array|false
     */
    public function getEntityTableDescription(RoxEntityBase $entity)
    {
        $class = get_class($entity);
        return ((!empty(self::$_table_descriptions[$class])) ? self::$_table_descriptions[$class] : false);
    }

}
