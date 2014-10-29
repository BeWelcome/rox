<?php

use Phinx\Migration\AbstractMigration;

/************************************
 * Class AllCollationsToUnicode
 *
 * Sets all table and column allocations to utf8_unicode_ci
 *
 * See ticket: #2218
 *
 */
class AllCollationsToUnicode extends AbstractMigration
{
    // All tables that contain columns that need to be converted
    private $convertArr = array(
        'latin1_swedish_ci' => array(
            'activities','activitiesattendees',     
            'notes',        
            'suggestions','suggestions_options','suggestions_option_ranks','suggestions_votes',
            'volunteer_boards'),
        'utf8_general_ci' => array(
            'blog_categories','blog_comments','blog_data','blog_tags',
            'countries',
            'cryptedfields',
            'ewiki',
            'forums_posts','forums_posts_votes','forums_tags','forums_threads',
            'gallery','gallery_comments','gallery_items',
            'geonames','geonames_admincodes','geonames_cache','geonames_cache_backup',
            'geonames_countries','geonamesadminunits','geonamesalternatenames','geonamescountries',
            'groups','groups_locations',
            'guestsonline',
            'languages',
            'members','members_groups_subscribed','members_sessions','members_tags_subscribed',
            'mod_user_apps','mod_user_auth','mod_user_authgroups','mod_user_rights',
            'online',
            'polls','polls_contributions','polls_record_of_choices',
            'privileges','privilegescopes',
            'recorded_usernames_of_left_members',
            'reports_to_moderators',
            'roles',
            'shouts',
            'tantable',
            'timezone',
            'trip_data',
            'urlheader_languages',
            'user','user_settings',
            'verifiedmembers',
            'volunteers_reports_schedule',
            'words','words_use')
        );

    // All tables that only need another default
    private $setDefaultArr = array(
        'latin1_swedish_ci' => array(
            'blog_categories_seq','blog_comments_seq','blog_tags_seq',           
            'gallery_items_seq','gallery_seq',             
            'groups_related',          
            'mod_user_apps_seq','mod_user_auth_seq','mod_user_rights_seq',     
            'sqlforgroupsmembers',     
            'trip_seq',
            'user_seq'),
        'utf8_general_ci' => array(
            'blog','blog_seq','blog_to_category','blog_to_tag',
            'gallery_items_to_gallery',
            'geo_usage',
            'intermembertranslations',
            'linklist',
            'members_roles',
            'mod_user_authrights','mod_user_groupauth','mod_user_grouprights','mod_user_implications',
            'phinxlog',
            'polls_choices','polls_choices_hierachy','polls_list_allowed_countries',
            'polls_list_allowed_groups','polls_list_allowed_locations',
            'recentvisits',
            'roles_privileges',
            'shouts_seq',
            'trip','trip_to_gallery',
            'user_friends','user_inbox','user_outbox')
        );

    /**
     * Migrate Up.
     */
    public function up()
    {
        foreach ($this->convertArr as $columns){
            foreach ($columns as $col){
                $this->execute("
ALTER TABLE $col
CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
                                ");

            }
        }

        foreach ($this->setDefaultArr as $columns){
            foreach ($columns as $col){
                $this->execute("
ALTER TABLE $col
DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
                                ");
            }
        }


    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        foreach ($this->convertArr as $origCollation => $columns){
            $charset = strstr($origCollation,'_',true);
            foreach ($columns as $col){
                $this->execute("
ALTER TABLE $col
CONVERT TO CHARACTER SET $charset COLLATE $origCollation;
                                ");
            }
        }

        foreach ($this->setDefaultArr as $origCollation => $columns){
            $charset = strstr($origCollation,'_',true);
            foreach ($columns as $col){
                $this->execute("
ALTER TABLE $col
DEFAULT CHARACTER SET $charset COLLATE $origCollation;
                                ");
            }
        }
    }
}
