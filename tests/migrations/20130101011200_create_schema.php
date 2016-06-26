<?php

use Phinx\Migration\AbstractMigration;

/************************************
 * Class DropDbVersion
 *
 * Removes the spurious table dbversion which became obsolete thanks to phinx
 *
 * See ticket: #2219
 *
 */
class CreateSchema extends AbstractMigration
{
    public function change()
    {
        $this->table('activities', array())
            ->addColumn('creator', 'biginteger', array('length'=>11))
            ->addColumn('dateTimeStart', 'datetime')
            ->addColumn('dateTimeEnd', 'datetime', array('null'=>true))
            ->addColumn('locationId', 'biginteger', array('length'=>11))
            ->addColumn('address', 'string', array('length'=>320,'null'=>true))
            ->addColumn('title', 'string', array('length'=>80))
            ->addColumn('description', 'text', array('length'=>16777215,'null'=>true))
            ->addColumn('status', 'integer', array('length'=>65535))
            ->addColumn('public', 'integer', array('length'=>65535,'null'=>true))
            ->create();

        $this->table('activitiesattendees', array())
            ->addColumn('activityId', 'biginteger', array('length'=>11))
            ->addColumn('attendeeId', 'biginteger', array('length'=>11))
            ->addColumn('organizer', 'integer', array('length'=>65535))
            ->addColumn('status', 'integer', array('length'=>65535))
            ->addColumn('comment', 'string', array('length'=>80))
            ->create();

        $this->table('addresses', array())
            ->addColumn('IdMember', 'integer')
            ->addColumn('HouseNumber', 'integer')
            ->addColumn('StreetName', 'integer')
            ->addColumn('Zip', 'integer')
            ->addColumn('IdCity', 'integer')
            ->addColumn('Explanation', 'integer')
            ->addColumn('Rank', 'integer', array('length'=>255,'default'=>0))
            ->addColumn('updated', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('IdGettingThere', 'integer', array('default'=>0))
            ->create();

        $this->table('blog', array('id'=>false, 'primary_key'=>array('blog_id')))
            ->addColumn('blog_id', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('flags', 'binary')
            ->addColumn('blog_created', 'datetime')
            ->addColumn('country_id_foreign', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('trip_id_foreign', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('IdMember', 'integer', array('null'=>true))
            ->create();

        $this->table('blog_categories', array('id'=>false, 'primary_key'=>array('blog_category_id')))
            ->addColumn('blog_category_id', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('name', 'string', array('default'=>''))
            ->addColumn('IdMember', 'integer', array('null'=>true))
            ->create();

        $this->table('blog_categories_seq', array())
            ->create();

        $this->table('blog_comments', array())
            ->addColumn('blog_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('created', 'datetime')
            ->addColumn('title', 'string', array('length'=>75,'default'=>''))
            ->addColumn('text', 'text', array('length'=>16777215))
            ->addColumn('IdMember', 'integer', array('null'=>true))
            ->create();

        $this->table('blog_comments_seq', array())
            ->create();

        $this->table('blog_data', array('id'=>false, 'primary_key'=>array('blog_id')))
            ->addColumn('blog_id', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('edited', 'datetime', array('null'=>true))
            ->addColumn('blog_title', 'string', array('default'=>''))
            ->addColumn('blog_text', 'text', array('length'=>4294967295))
            ->addColumn('blog_start', 'datetime', array('null'=>true))
            ->addColumn('blog_end', 'datetime', array('null'=>true))
            ->addColumn('blog_latitude', 'float', array('default'=>0))
            ->addColumn('blog_longitude', 'float', array('default'=>0))
            ->addColumn('blog_geonameid', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('blog_display_order', 'integer', array('length'=>10,'default'=>0))
            ->create();

        $this->table('blog_seq', array())
            ->create();

        $this->table('blog_tags', array('id'=>false, 'primary_key'=>array('blog_tag_id')))
            ->addColumn('blog_tag_id', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('name', 'string', array('default'=>''))
            ->create();

        $this->table('blog_tags_seq', array())
            ->create();

        $this->table('blog_to_category', array())
            ->addColumn('created', 'datetime')
            ->addColumn('blog_category_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('blog_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->create();

        $this->table('blog_to_tag', array())
            ->addColumn('blog_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('blog_tag_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->create();

        $this->table('broadcast', array())
            ->addColumn('IdCreator', 'integer')
            ->addColumn('Name', 'text')
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('Status', 'enum', array('default'=>'Created','values'=>array (  0 => 'Created',  1 => 'Triggered',)))
            ->addColumn('Type', 'enum', array('null'=>true,'values'=>array (  0 => 'Normal',  1 => 'RemindToLog',  2 => 'Specific',  3 => 'SuggestionReminder',  4 => 'TermsOfUse',  5 => 'MailToConfirmReminder',)))
            ->addColumn('EmailFrom', 'text', array('null'=>true))
            ->create();

        $this->table('broadcastmessages', array('id'=>false, 'primary_key'=>array('IdBroadcast', 'IdReceiver')))
            ->addColumn('IdBroadcast', 'integer')
            ->addColumn('IdReceiver', 'integer')
            ->addColumn('IdEnqueuer', 'integer')
            ->addColumn('Status', 'enum', array('default'=>'ToApprove','values'=>array (  0 => 'ToApprove',  1 => 'ToSend',  2 => 'Sent',  3 => 'Failed',  4 => 'ToWait',  5 => 'ToResend',)))
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->create();

        $this->table('cities', array())
            ->addColumn('NbMembers', 'integer')
            ->addColumn('Name', 'string', array('length'=>200))
            ->addColumn('ansiname', 'string', array('length'=>200))
            ->addColumn('OtherNames', 'string', array('length'=>200))
            ->addColumn('latitude', 'decimal', array('length'=>10))
            ->addColumn('longitude', 'decimal', array('length'=>10))
            ->addColumn('feature_class', 'string', array('length'=>1,'null'=>true))
            ->addColumn('feature_code', 'string', array('length'=>10,'null'=>true))
            ->addColumn('country_code', 'char', array('length'=>2))
            ->addColumn('population', 'integer', array('length'=>10))
            ->addColumn('IdRegion', 'integer')
            ->addColumn('ActiveCity', 'string', array('length'=>4,'default'=>''))
            ->addColumn('IdCountry', 'integer')
            ->create();

        $this->table('comments', array())
            ->addColumn('IdFromMember', 'integer')
            ->addColumn('IdToMember', 'integer')
            ->addColumn('Lenght', 'set', array('values' => ['hewasmyguest','hehostedme','OnlyOnce','HeIsMyFamily','HeHisMyOldCloseFriend','NeverMetInRealLife','TravelledTogether','WeAreFriends']))
            ->addColumn('Quality', 'enum', array('default'=>'Neutral','values'=>array (  0 => 'Good',  1 => 'Neutral',  2 => 'Bad',)))
            ->addColumn('TextFree', 'text')
            ->addColumn('TextWhere', 'text')
            ->addColumn('updated', 'timestamp', array('null'=>true))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('AdminAction', 'enum', array('default'=>'NothingNeeded','values'=>array (  0 => 'NothingNeeded',  1 => 'AdminCommentMustCheck',  2 => 'AdminAbuserMustCheck',  3 => 'Checked',)))
            ->addColumn('DisplayableInCommentOfTheMonth', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
            ->addColumn('DisplayInPublic', 'integer', array('length'=>255,'default'=>1))
            ->addColumn('AllowEdit', 'integer', array('length'=>255,'default'=>0))
            ->create();

        $this->table('counters_regions_nbcities', array('id'=>false, 'primary_key'=>array('IdRegion')))
            ->addColumn('IdRegion', 'integer', array('default'=>0))
            ->addColumn('NbCities', 'integer', array('default'=>0))
            ->create();

        $this->table('countries', array())
            ->addColumn('Name', 'string', array('length'=>50))
            ->addColumn('isoalpha2', 'string', array('length'=>4))
            ->addColumn('isoalpha3', 'string', array('length'=>4))
            ->addColumn('isonumeric', 'integer', array('length'=>4))
            ->addColumn('fipscode', 'string', array('length'=>2))
            ->addColumn('capital', 'string', array('length'=>50))
            ->addColumn('areaInSqKm', 'integer')
            ->addColumn('population', 'integer')
            ->addColumn('continent', 'string', array('length'=>2))
            ->addColumn('languages', 'string', array('length'=>100))
            ->addColumn('regionopen', 'integer', array('length'=>255,'default'=>0))
            ->addColumn('countadmin1', 'integer', array('default'=>0))
            ->addColumn('NbMembers', 'integer', array('default'=>0))
            ->addColumn('FirstAdminLevel', 'string', array('length'=>10,'default'=>'ADM1'))
            ->addColumn('SecondAdminLevel', 'string', array('length'=>4,'default'=>'ADM2'))
            ->create();

        $this->table('cryptedfields', array())
            ->addColumn('AdminCryptedValue', 'text')
            ->addColumn('MemberCryptedValue', 'text')
            ->addColumn('IsCrypted', 'enum', array('default'=>'crypted','values'=>array (  0 => 'not crypted',  1 => 'crypted',  2 => 'always',)))
            ->addColumn('IdMember', 'integer')
            ->addColumn('ToDo', 'enum', array('default'=>'nothing','values'=>array (  0 => 'nothing',  1 => 'memberaskforcrypt',  2 => 'memberaskfordecrypt',)))
            ->addColumn('temporary_uncrypted_buffer', 'text', array('null'=>true))
            ->addColumn('IdRecord', 'integer', array('default'=>0))
            ->addColumn('TableColumn', 'string', array('length'=>200,'default'=>'NotSet'))
            ->create();

        $this->table('dbversion', array('id'=>false, 'primary_key'=>array('version')))
            ->addColumn('version', 'integer')
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('active', 'integer', array('length'=>1,'null'=>true))
            ->create();

        $this->table('donations', array())
            ->addColumn('IdMember', 'integer', array('default'=>0))
            ->addColumn('Email', 'text', array('length'=>255))
            ->addColumn('StatusPrivate', 'enum', array('default'=>'showamountonly','values'=>array (  0 => 'private',  1 => 'shownameonly',  2 => 'showamountonly',  3 => 'shownameandamount',)))
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('Amount', 'decimal', array('length'=>10))
            ->addColumn('Money', 'string', array('length'=>10))
            ->addColumn('IdCountry', 'integer')
            ->addColumn('namegiven', 'text')
            ->addColumn('referencepaypal', 'text')
            ->addColumn('membercomment', 'text')
            ->addColumn('SystemComment', 'text')
            ->create();

        $this->table('ewiki', array('id'=>false, 'primary_key'=>array('pagename', 'version')))
            ->addColumn('pagename', 'string', array('length'=>160))
            ->addColumn('version', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('flags', 'integer', array('length'=>10,'default'=>0,'null'=>true))
            ->addColumn('content', 'text', array('length'=>16777215,'null'=>true))
            ->addColumn('author', 'string', array('length'=>100,'default'=>'ewiki','null'=>true))
            ->addColumn('created', 'integer', array('length'=>10,'default'=>1168175948,'null'=>true))
            ->addColumn('lastmodified', 'integer', array('length'=>10,'default'=>0,'null'=>true))
            ->addColumn('refs', 'text', array('length'=>16777215,'null'=>true))
            ->addColumn('meta', 'text', array('length'=>16777215,'null'=>true))
            ->addColumn('hits', 'integer', array('length'=>10,'default'=>0,'null'=>true))
            ->create();

        $this->table('faq', array())
            ->addColumn('QandA', 'string', array('length'=>40))
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('Active', 'enum', array('default'=>'Active','values'=>array (  0 => 'Active',  1 => 'Not Active',)))
            ->addColumn('SortOrder', 'integer', array('default'=>0))
            ->addColumn('IdCategory', 'integer', array('default'=>0))
            ->addColumn('PageTitle', 'string', array('length'=>100))
            ->create();

        $this->table('faqcategories', array())
            ->addColumn('Description', 'string', array('length'=>40))
            ->addColumn('SortOrder', 'integer', array('default'=>0))
            ->addColumn('Type', 'enum', array('default'=>'ForAll','values'=>array (  0 => 'ForLogged',  1 => 'ForNotLogged',  2 => 'ForAll',)))
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->create();

        $this->table('feedbackcategories', array())
            ->addColumn('Name', 'text', array('length'=>255))
            ->addColumn('CategoryDescription', 'text', array('length'=>255))
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('EmailToNotify', 'text')
            ->addColumn('IdVolunteer', 'integer', array('default'=>0))
            ->addColumn('sortOrder', 'integer', array('length'=>1,'default'=>0))
            ->addColumn('visible', 'integer', array('length'=>1,'default'=>0))
            ->create();

        $this->table('feedbacks', array())
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('IdMember', 'integer')
            ->addColumn('Discussion', 'text')
            ->addColumn('IdFeedbackCategory', 'integer', array('default'=>1))
            ->addColumn('IdVolunteer', 'integer', array('default'=>0))
            ->addColumn('Status', 'enum', array('default'=>'open','values'=>array (  0 => 'open',  1 => 'answered',  2 => 'closed by member',  3 => ' member need more',  4 => 'close by volunteer',)))
            ->addColumn('IdLanguage', 'integer', array('default'=>0))
            ->create();

        $this->table('flags', array())
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('Name', 'text', array('length'=>255))
            ->addColumn('Description', 'text')
            ->create();

        $this->table('flagsmembers', array())
            ->addColumn('IdMember', 'integer')
            ->addColumn('IdFlag', 'integer')
            ->addColumn('Level', 'integer', array('default'=>0))
            ->addColumn('Scope', 'text', array('length'=>255))
            ->addColumn('Comment', 'text')
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->create();

        $this->table('forum_trads', array())
            ->addColumn('IdLanguage', 'integer')
            ->addColumn('IdOwner', 'integer')
            ->addColumn('IdTrad', 'integer')
            ->addColumn('IdTranslator', 'integer')
            ->addColumn('updated', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('Type', 'enum', array('values'=>array (  0 => 'member',  1 => 'translator',  2 => 'admin',)))
            ->addColumn('Sentence', 'text')
            ->addColumn('IdRecord', 'integer')
            ->addColumn('TableColumn', 'string', array('length'=>200,'default'=>'NotSet'))
            ->create();

        $this->table('forums_posts', array('id'=>'postid'))
            ->addColumn('threadid', 'integer', array('length'=>16777215,'null'=>true))
            ->addColumn('PostVisibility', 'enum', array('default'=>'NoRestriction','values'=>array (  0 => 'NoRestriction',  1 => 'MembersOnly',  2 => 'GroupOnly',  3 => 'ModeratorOnly',)))
            ->addColumn('authorid', 'integer', array('length'=>10))
            ->addColumn('IdWriter', 'integer', array('default'=>0))
            ->addColumn('create_time', 'datetime')
            ->addColumn('message', 'text')
            ->addColumn('IdContent', 'integer', array('default'=>0))
            ->addColumn('OwnerCanStillEdit', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
            ->addColumn('last_edittime', 'datetime', array('null'=>true))
            ->addColumn('last_editorid', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('edit_count', 'integer', array('length'=>255,'default'=>0))
            ->addColumn('IdFirstLanguageUsed', 'integer', array('default'=>0))
            ->addColumn('HasVotes', 'enum', array('default'=>'No','values'=>array (  0 => 'No',  1 => 'Yes',)))
            ->addColumn('IdLocalVolMessage', 'integer', array('default'=>0))
            ->addColumn('IdLocalEvent', 'integer', array('default'=>0))
            ->addColumn('IdPoll', 'integer', array('default'=>0))
            ->addColumn('PostDeleted', 'enum', array('default'=>'NotDeleted','values'=>array (  0 => 'NotDeleted',  1 => 'Deleted',)))
            ->create();

        $this->table('forums_posts_votes', array('id'=>false, 'primary_key'=>array('IdPost', 'IdContributor')))
            ->addColumn('IdPost', 'integer')
            ->addColumn('IdContributor', 'integer')
            ->addColumn('Choice', 'enum', array('null'=>true,'values'=>array (  0 => 'Yes',  1 => 'DontKnow',  2 => 'DontCare',  3 => 'No',)))
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('NbUpdates', 'integer', array('default'=>0))
            ->create();

        $this->table('forums_tags', array('id'=>'tagid'))
            ->addColumn('tag', 'string', array('length'=>64))
            ->addColumn('tag_description', 'string', array('null'=>true))
            ->addColumn('tag_position', 'integer', array('length'=>65536,'default'=>250))
            ->addColumn('counter', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('IdName', 'integer', array('default'=>0))
            ->addColumn('IdDescription', 'integer', array('default'=>0))
            ->addColumn('Type', 'enum', array('default'=>'Member','values'=>array (  0 => 'Category',  1 => 'Member',)))
            ->create();

        $this->table('forums_threads', array('id'=>'threadid'))
            ->addColumn('expiredate', 'timestamp', array('default'=>'0000-00-00 00:00:00','null'=>true))
            ->addColumn('IdTitle', 'integer', array('default'=>0))
            ->addColumn('title', 'string')
            ->addColumn('first_postid', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('last_postid', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('replies', 'integer', array('length'=>65535,'default'=>0))
            ->addColumn('views', 'integer', array('length'=>16777215,'default'=>0))
            ->addColumn('geonameid', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('admincode', 'char', array('length'=>2,'null'=>true))
            ->addColumn('countrycode', 'char', array('length'=>2,'null'=>true))
            ->addColumn('continent', 'enum', array('null'=>true,'values'=>array (  0 => 'AF',  1 => 'AN',  2 => 'AS',  3 => 'EU',  4 => 'NA',  5 => 'OC',  6 => 'SA',)))
            ->addColumn('tag1', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('tag2', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('tag3', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('tag4', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('tag5', 'integer', array('length'=>10,'null'=>true))
            ->addColumn('stickyvalue', 'integer', array('default'=>0))
            ->addColumn('IdFirstLanguageUsed', 'integer', array('default'=>0))
            ->addColumn('IdGroup', 'integer', array('default'=>0))
            ->addColumn('ThreadVisibility', 'enum', array('default'=>'NoRestriction','values'=>array (  0 => 'NoRestriction',  1 => 'MembersOnly',  2 => 'GroupOnly',  3 => 'ModeratorOnly',)))
            ->addColumn('WhoCanReply', 'enum', array('default'=>'MembersOnly','values'=>array (  0 => 'MembersOnly',  1 => 'GroupMembersOnly',  2 => 'ModeratorsOnly',)))
            ->addColumn('ThreadDeleted', 'enum', array('default'=>'NotDeleted','values'=>array (  0 => 'NotDeleted',  1 => 'Deleted',)))
            ->create();

        $this->table('gallery', array())
            ->addColumn('user_id_foreign', 'integer', array('length'=>10))
            ->addColumn('flags', 'binary')
            ->addColumn('title', 'string')
            ->addColumn('text', 'text', array('length'=>16777215))
            ->create();

        $this->table('gallery_comments', array())
            ->addColumn('gallery_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('gallery_items_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('user_id_foreign', 'integer', array('length'=>10,'default'=>0))
            ->addColumn('created', 'datetime')
            ->addColumn('title', 'string', array('length'=>75,'default'=>''))
            ->addColumn('text', 'text', array('length'=>16777215))
            ->create();

        $this->table('gallery_comments_seq', array())
            ->create();

        $this->table('gallery_items', array())
            ->addColumn('user_id_foreign', 'integer', array('length'=>10))
            ->addColumn('file', 'string', array('length'=>40))
            ->addColumn('original', 'string')
            ->addColumn('flags', 'binary')
            ->addColumn('mimetype', 'string', array('length'=>75))
            ->addColumn('width', 'integer', array('length'=>5))
            ->addColumn('height', 'integer', array('length'=>5))
            ->addColumn('title', 'string')
            ->addColumn('created', 'datetime')
            ->addColumn('description', 'text')
            ->create();

        $this->table('gallery_items_seq', array())
            ->create();

        $this->table('gallery_items_to_gallery', array())
            ->addColumn('item_id_foreign', 'integer', array('length'=>10))
            ->addColumn('gallery_id_foreign', 'integer', array('length'=>10))
            ->create();

        $this->table('gallery_seq', array())
            ->create();

        $this->table('geo_hierarchy', array())
            ->addColumn('geoId', 'integer')
            ->addColumn('parentId', 'integer')
            ->addColumn('comment', 'string', array('null'=>true))
            ->create();

        $this->table('geo_type', array())
            ->addColumn('name', 'string', array('length'=>20))
            ->addColumn('description', 'string', array('null'=>true))
            ->create();

        $this->table('geo_usage', array('id'=>false, 'primary_key'=>array('geoId', 'typeId')))
            ->addColumn('geoId', 'integer')
            ->addColumn('typeId', 'integer')
            ->addColumn('count', 'integer')
            ->create();

        $this->table('geonames', array('id'=>false, 'primary_key'=>array('geonameid')))
            ->addColumn('geonameid', 'integer')
            ->addColumn('name', 'string', array('length'=>200,'null'=>true))
            ->addColumn('latitude', 'decimal', array('length'=>10,'null'=>true))
            ->addColumn('longitude', 'decimal', array('length'=>10,'null'=>true))
            ->addColumn('fclass', 'char', array('length'=>1,'null'=>true))
            ->addColumn('fcode', 'string', array('length'=>10,'null'=>true))
            ->addColumn('country', 'string', array('length'=>2,'null'=>true))
            ->addColumn('admin1', 'string', array('length'=>20,'null'=>true))
            ->addColumn('population', 'integer', array('null'=>true))
            ->addColumn('moddate', 'date', array('null'=>true))
            ->create();

        $this->table('geonames_admincodes', array('id'=>false, 'primary_key'=>array('code')))
            ->addColumn('code', 'char', array('length'=>5))
            ->addColumn('country_code', 'char', array('length'=>2))
            ->addColumn('admin_code', 'char', array('length'=>2))
            ->addColumn('name', 'string', array('length'=>64))
            ->create();

        $this->table('geonames_cache', array('id'=>false, 'primary_key'=>array('geonameid')))
            ->addColumn('geonameid', 'integer')
            ->addColumn('latitude', 'decimal', array('length'=>10))
            ->addColumn('longitude', 'decimal', array('length'=>10))
            ->addColumn('name', 'string', array('length'=>200))
            ->addColumn('population', 'integer', array('length'=>10))
            ->addColumn('fk_countrycode', 'char', array('length'=>2))
            ->addColumn('fk_admincode', 'char', array('length'=>2,'null'=>true))
            ->addColumn('fclass', 'string', array('length'=>1,'null'=>true))
            ->addColumn('fcode', 'string', array('length'=>10,'null'=>true))
            ->addColumn('timezone', 'integer', array('null'=>true))
            ->addColumn('parentAdm1Id', 'integer')
            ->addColumn('parentCountryId', 'integer')
            ->create();

        $this->table('geonames_cache_backup', array())
            ->addColumn('geonameid', 'integer', array('length'=>10))
            ->addColumn('latitude', 'decimal', array('length'=>10))
            ->addColumn('longitude', 'decimal', array('length'=>10))
            ->addColumn('name', 'string', array('length'=>200))
            ->addColumn('population', 'integer', array('length'=>10))
            ->addColumn('fclass', 'string', array('length'=>1,'null'=>true))
            ->addColumn('fcode', 'string', array('length'=>10,'null'=>true))
            ->addColumn('fk_countrycode', 'char', array('length'=>2))
            ->addColumn('fk_admincode', 'char', array('length'=>2,'null'=>true))
            ->addColumn('timezone', 'integer', array('null'=>true))
            ->addColumn('date_updated', 'date')
            ->addColumn('parentid', 'integer', array('null'=>true))
            ->addColumn('parentAdm1Id', 'integer')
            ->addColumn('parentCountryId', 'integer')
            ->create();

        $this->table('geonames_countries', array('id'=>false, 'primary_key'=>array('iso_alpha2')))
            ->addColumn('iso_alpha2', 'string', array('length'=>2))
            ->addColumn('name', 'string', array('length'=>64))
            ->addColumn('continent', 'enum', array('values'=>array (  0 => 'AF',  1 => 'AN',  2 => 'AS',  3 => 'EU',  4 => 'NA',  5 => 'OC',  6 => 'SA',)))
            ->addColumn('languages', 'string', array('length'=>128))
            ->create();

        $this->table('geonames_timezones', array('id'=>false, 'primary_key'=>array('TimeZoneId')))
            ->addColumn('TimeZoneId', 'integer')
            ->addColumn('OffsetJanuary', 'decimal', array('length'=>10))
            ->addColumn('OffsetJuly', 'decimal', array('length'=>10))
            ->create();

        $this->table('geonamesadminunits', array('id'=>false, 'primary_key'=>array('geonameid')))
            ->addColumn('geonameid', 'integer')
            ->addColumn('name', 'string', array('length'=>200,'null'=>true))
            ->addColumn('fclass', 'char', array('length'=>1,'null'=>true))
            ->addColumn('fcode', 'string', array('length'=>10,'null'=>true))
            ->addColumn('country', 'string', array('length'=>2,'null'=>true))
            ->addColumn('admin1', 'string', array('length'=>20,'null'=>true))
            ->addColumn('moddate', 'date', array('null'=>true))
            ->create();

        $this->table('geonamesalternatenames', array('id'=>false, 'primary_key'=>array('alternatenameId')))
            ->addColumn('alternatenameId', 'integer')
            ->addColumn('geonameid', 'integer')
            ->addColumn('isolanguage', 'string', array('length'=>7,'null'=>true))
            ->addColumn('alternatename', 'string', array('length'=>200,'null'=>true))
            ->addColumn('ispreferred', 'boolean', array('null'=>true))
            ->addColumn('isshort', 'boolean', array('null'=>true))
            ->addColumn('iscolloquial', 'boolean', array('null'=>true))
            ->addColumn('ishistoric', 'boolean', array('null'=>true))
            ->create();

        $this->table('geonamescountries', array('id'=>false, 'primary_key'=>array('country')))
            ->addColumn('geonameId', 'integer', array('null'=>true))
            ->addColumn('country', 'char', array('length'=>2,'default'=>''))
            ->addColumn('name', 'string', array('length'=>200,'null'=>true))
            ->addColumn('continent', 'char', array('length'=>2,'null'=>true))
            ->create();

        $this->table('groups', array())
            ->addColumn('HasMembers', 'enum', array('default'=>'HasMember','values'=>array (  0 => 'HasMember',  1 => 'HasNotMember',)))
            ->addColumn('Name', 'string', array('length'=>40))
            ->addColumn('Type', 'enum', array('default'=>'Public','values'=>array (  0 => 'Public',  1 => 'NeedAcceptance',  2 => 'NeedInvitation',)))
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('NbChilds', 'integer', array('default'=>0))
            ->addColumn('Picture', 'text')
            ->addColumn('MoreInfo', 'text')
            ->addColumn('DisplayedOnProfile', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
            ->addColumn('IdDescription', 'integer', array('null'=>true))
            ->addColumn('VisiblePosts', 'enum', array('default'=>'yes','values'=>array (  0 => 'no',  1 => 'yes',)))
            ->addColumn('VisibleComments', 'enum', array('default'=>'no','values'=>array (  0 => 'no',  1 => 'yes',)))
            ->create();

        $this->table('groups_related', array())
            ->addColumn('group_id', 'integer', array('null'=>true))
            ->addColumn('related_id', 'integer', array('null'=>true))
            ->addColumn('addedby', 'integer', array('null'=>true))
            ->addColumn('deletedby', 'integer', array('null'=>true))
            ->addColumn('ts', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->create();

        $this->table('groupsmessages', array())
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('Title', 'text')
            ->addColumn('Message', 'text')
            ->addColumn('IdSender', 'integer')
            ->addColumn('IdGroup', 'integer')
            ->create();

        $this->table('guestsonline', array('id'=>false, 'primary_key'=>array('IpGuest')))
            ->addColumn('IpGuest', 'integer')
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('appearance', 'string', array('length'=>32))
            ->addColumn('lastactivity', 'string')
            ->addColumn('Status', 'string', array('length'=>32,'default'=>'Active'))
            ->create();

        $this->table('hcvol_config', array('id'=>false, 'primary_key'=>array('key')))
            ->addColumn('key', 'string', array('length'=>200))
            ->addColumn('value', 'text')
            ->addColumn('comment', 'text')
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->create();

        $this->table('intermembertranslations', array())
            ->addColumn('IdTranslator', 'integer')
            ->addColumn('IdMember', 'integer')
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('IdLanguage', 'integer')
            ->create();

        $this->table('languages', array())
            ->addColumn('EnglishName', 'text', array('length'=>255))
            ->addColumn('Name', 'text', array('length'=>255))
            ->addColumn('ShortCode', 'char', array('length'=>16))
            ->addColumn('WordCode', 'text', array('length'=>255))
            ->addColumn('FlagSortCriteria', 'integer', array('default'=>0))
            ->addColumn('IsWrittenLanguage', 'integer', array('length'=>255,'default'=>0))
            ->addColumn('IsSpokenLanguage', 'integer', array('length'=>255,'default'=>0))
            ->addColumn('IsSignLanguage', 'integer', array('length'=>255,'default'=>0))
            ->create();

        $this->table('linklist', array())
            ->addColumn('fromID', 'integer')
            ->addColumn('toID', 'integer')
            ->addColumn('degree', 'integer', array('length'=>255))
            ->addColumn('rank', 'integer', array('length'=>255))
            ->addColumn('path', 'string', array('length'=>10000))
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->create();

        $this->table('logs', array())
            ->addColumn('IdMember', 'integer')
            ->addColumn('Str', 'text')
            ->addColumn('Type', 'text', array('length'=>255))
            ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('IpAddress', 'integer')
            ->addColumn('DebugTracking', 'enum', array('values'=>array (  0 => 'Normal',  1 => 'Debug',)))
            ->create();

        $this->table('members', array())
            ->addColumn('ex_user_id', 'integer')
            ->addColumn('Username', 'string', array('length'=>32))
            ->addColumn('Status', 'enum', array('default'=>'MailToConfirm','values'=>array (  0 => 'MailToConfirm',  1 => 'Pending',  2 => 'DuplicateSigned',  3 => 'NeedMore',  4 => 'Rejected',  5 => 'CompletedPending',  6 => 'Active',  7 => 'TakenOut',  8 => 'Banned',  9 => 'Sleeper',  10 => 'ChoiceInactive',  11 => 'OutOfRemind',  12 => 'Renamed',  13 => 'ActiveHidden',  14 => 'SuspendedBeta',  15 => 'AskToLeave',  16 => 'StopBoringMe',  17 => 'PassedAway',  18 => 'Buggy',)))
            ->addColumn('ChangedId', 'integer', array('default'=>0))
            ->addColumn('Email', 'integer')
            ->addColumn('IdCity', 'integer')
            ->addColumn('NbRemindWithoutLogingIn', 'integer')
            ->addColumn('HomePhoneNumber', 'integer')
            ->addColumn('CellPhoneNumber', 'integer')
            ->addColumn('WorkPhoneNumber', 'integer')
            ->addColumn('SecEmail', 'integer')
            ->addColumn('FirstName', 'integer', array('default'=>0))
            ->addColumn('SecondName', 'integer', array('default'=>0))
            ->addColumn('LastName', 'integer', array('default'=>0))
            ->addColumn('Accomodation', 'enum', array('default'=>'dependonrequest','values'=>array (  0 => 'anytime',  1 => 'dependonrequest',  2 => 'neverask',)))
            ->addColumn('AdditionalAccomodationInfo', 'integer')
            ->addColumn('ILiveWith', 'integer')
            ->addColumn('IdentityCheckLevel', 'integer', array('length'=>255,'default'=>000))
            ->addColumn('InformationToGuest', 'integer')
            ->addColumn('TypicOffer', 'set', array( 'values' => ['guidedtour','dinner','CanHostWeelChair']))
            ->addColumn('Offer', 'integer')
            ->addColumn('MaxGuest', 'integer', array('default'=>0))
            ->addColumn('MaxLenghtOfStay', 'integer', array('default'=>0))
            ->addColumn('Organizations', 'integer')
            ->addColumn('Restrictions', 'set', array( 'values' => ['NoSmoker','NoAlchool','NoDrugs','SeeOtherRestrictions']))
            ->addColumn('OtherRestrictions', 'integer')
            ->addColumn('bday', 'integer')
            ->addColumn('bmonth', 'integer')
            ->addColumn('byear', 'integer')
            ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
            ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('LastLogin', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
            ->addColumn('SecurityFlag', 'integer', array('default'=>0))
            ->addColumn('Quality', 'set', array( 'values' => ['NeverLog','LogOnce','UpdateIsProfile','HasSentMessage','HasAnswerMessage','HasBeenGuest','HasBeenHost'], 'default'=>'NeverLog'))
            ->addColumn('ProfileSummary', 'integer')
            ->addColumn('Occupation', 'integer')
            ->addColumn('CounterGuests', 'integer', array('default'=>0))
            ->addColumn('CounterHosts', 'integer', array('default'=>0))
            ->addColumn('CounterTrusts', 'integer', array('default'=>0))
            ->addColumn('PassWord', 'string', array('length'=>100,'default'=>'','null'=>true))
                                                ->addColumn('Gender', 'enum', array('default'=>'IDontTell','values'=>array (  0 => 'IDontTell',  1 => 'male',  2 => 'female',  3 => 'other',)))
        ->addColumn('HideGender', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
        ->addColumn('GenderOfGuest', 'enum', array('default'=>'any','values'=>array (  0 => 'any',  1 => 'male',  2 => 'female',)))
        ->addColumn('MotivationForHospitality', 'integer', array('null'=>true))
        ->addColumn('HideBirthDate', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
        ->addColumn('BirthDate', 'date', array('null'=>true))
        ->addColumn('AdressHidden', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
        ->addColumn('WebSite', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_SKYPE', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_ICQ', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_AOL', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_MSN', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_YAHOO', 'text', array('length'=>255,'null'=>true))
        ->addColumn('chat_Others', 'text', array('length'=>255,'null'=>true))
        ->addColumn('Id4City', 'integer')
        ->addColumn('FutureTrips', 'integer', array('default'=>0))
        ->addColumn('OldTrips', 'integer', array('default'=>0))
        ->addColumn('LogCount', 'integer', array('default'=>0))
        ->addColumn('Hobbies', 'integer')
        ->addColumn('Books', 'integer')
        ->addColumn('Music', 'integer')
        ->addColumn('PastTrips', 'integer')
        ->addColumn('PlannedTrips', 'integer')
        ->addColumn('PleaseBring', 'integer')
        ->addColumn('OfferGuests', 'integer')
        ->addColumn('OfferHosts', 'integer')
        ->addColumn('PublicTransport', 'integer')
        ->addColumn('Movies', 'integer')
        ->addColumn('chat_GOOGLE', 'integer')
        ->addColumn('LastSwitchToActive', 'datetime', array('null'=>true))
        ->create();

            $this->table('members_groups_subscribed', array())
                ->addColumn('IdSubscriber', 'integer')
                ->addColumn('IdGroup', 'integer')
                ->addColumn('ActionToWatch', 'enum', array('default'=>'replies','values'=>array (  0 => 'replies',  1 => 'updates',)))
                ->addColumn('UnSubscribeKey', 'string', array('length'=>20))
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('members_roles', array('id'=>false, 'primary_key'=>array('IdMember', 'IdRole')))
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdRole', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('members_sessions', array())
                ->addColumn('IdMember', 'integer', array('null'=>true))
                ->addColumn('SeriesToken', 'char', array('length'=>32,'null'=>true))
                ->addColumn('AuthToken', 'char', array('length'=>32,'null'=>true))
                ->addColumn('modified', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('members_tags_subscribed', array())
                ->addColumn('IdSubscriber', 'integer')
                ->addColumn('IdTag', 'integer')
                ->addColumn('ActionToWatch', 'set', array('default'=>'replies', 'values'=>array (  0 => 'replies',  1 => 'updates',)))
                ->addColumn('UnSubscribeKey', 'string', array('length'=>20))
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('members_threads_subscribed', array())
                ->addColumn('IdSubscriber', 'integer')
                ->addColumn('IdThread', 'integer')
                ->addColumn('ActionToWatch', 'set', array('default'=>'replies', 'values'=>array (  0 => 'replies',  1 => 'updates',)))
                ->addColumn('UnSubscribeKey', 'string', array('length'=>20))
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('membersgroups', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Comment', 'integer')
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdGroup', 'integer')
                ->addColumn('Status', 'enum', array('default'=>'WantToBeIn','values'=>array (  0 => 'In',  1 => 'WantToBeIn',  2 => 'Kicked',  3 => 'Invited',)))
                ->addColumn('IacceptMassMailFromThisGroup', 'enum', array('default'=>'no','values'=>array (  0 => 'yes',  1 => 'no',)))
                ->addColumn('CanSendGroupMessage', 'enum', array('default'=>'yes','values'=>array (  0 => 'yes',  1 => 'no',)))
                ->create();

            $this->table('memberslanguageslevel', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdLanguage', 'integer')
                ->addColumn('Level', 'enum', array('default'=>'Beginner','values'=>array (  0 => 'MotherLanguage',  1 => 'Expert',  2 => 'Fluent',  3 => 'Intermediate',  4 => 'Beginner',  5 => 'HelloOnly',)))
                ->create();

            $this->table('membersphotos', array())
                ->addColumn('FilePath', 'text', array('length'=>255))
                ->addColumn('IdMember', 'integer')
                ->addColumn('SortOrder', 'integer', array('length'=>255,'default'=>0))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Comment', 'integer')
                ->create();

            $this->table('memberspreferences', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdPreference', 'integer')
                ->addColumn('Value', 'text')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('memberspublicprofiles', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('type', 'enum', array('default'=>'normal','values'=>array (  0 => 'GoodSample',  1 => 'normal',  2 => 'BadSample',)))
                ->create();

            $this->table('memberstrads', array())
                ->addColumn('IdLanguage', 'integer', array('default'=>0))
                ->addColumn('IdOwner', 'integer')
                ->addColumn('IdTrad', 'integer')
                ->addColumn('IdTranslator', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Type', 'enum', array('default'=>'member','values'=>array (  0 => 'member',  1 => 'translator',  2 => 'admin',)))
                ->addColumn('Sentence', 'text')
                ->addColumn('IdRecord', 'integer', array('default'=>-1))
                ->addColumn('TableColumn', 'string', array('length'=>200,'default'=>'NotSet'))
                ->create();

            $this->table('messages', array())
                ->addColumn('MessageType', 'enum', array('default'=>'MemberToMember','values'=>array (  0 => 'MemberToMember',  1 => 'LocalVolToMember',)))
                ->addColumn('IdMessageFromLocalVol', 'integer', array('default'=>0))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('DateSent', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('DeleteRequest', 'set', [ 'values' => ['senderdeleted','receiverdeleted']])
                ->addColumn('IdParent', 'integer', array('default'=>0))
                ->addColumn('IdReceiver', 'integer')
                ->addColumn('IdSender', 'integer')
                ->addColumn('IdentityInformation', 'text')
                ->addColumn('SendConfirmation', 'enum', array('values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('SpamInfo', 'set', array('default'=>'NotSpam', 'values' => ['NotSpam','SpamBlkWord','SpamSayChecker','SpamSayMember','ProcessedBySpamManager']))
                ->addColumn('Status', 'enum', array('default'=>'ToCheck','values'=>array (  0 => 'Draft',  1 => 'ToCheck',  2 => 'ToSend',  3 => 'Sent',  4 => 'Freeze',)))
                ->addColumn('Message', 'text')
                ->addColumn('InFolder', 'enum', array('default'=>'Normal','values'=>array (  0 => 'Normal',  1 => 'junk',  2 => 'Spam',  3 => 'Draft',)))
                ->addColumn('WhenFirstRead', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdChecker', 'integer', array('default'=>0))
                ->addColumn('IdTriggerer', 'integer', array('default'=>0))
                ->addColumn('JoinMemberPict', 'enum', array('default'=>'no','values'=>array (  0 => 'yes',  1 => 'no',)))
                ->addColumn('CheckerComment', 'text')
                ->create();

            $this->table('mod_user_apps', array())
                ->addColumn('name', 'string', array('length'=>75,'default'=>''))
                ->create();

            $this->table('mod_user_apps_seq', array())
                ->create();

            $this->table('mod_user_auth', array())
                ->addColumn('name', 'string', array('length'=>75,'default'=>''))
                ->create();

            $this->table('mod_user_auth_seq', array())
                ->create();

            $this->table('mod_user_authgroups', array())
                ->addColumn('name', 'string', array('length'=>75,'default'=>''))
                ->create();

            $this->table('mod_user_authrights', array())
                ->addColumn('auth_id', 'integer', array('length'=>10,'null'=>true))
                ->addColumn('right_id', 'integer', array('length'=>10,'null'=>true))
                ->create();

            $this->table('mod_user_groupauth', array())
                ->addColumn('auth_id', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('group_id', 'integer', array('length'=>10,'default'=>0))
                ->create();

            $this->table('mod_user_grouprights', array())
                ->addColumn('group_id', 'integer', array('length'=>10,'null'=>true))
                ->addColumn('right_id', 'integer', array('length'=>10,'null'=>true))
                ->create();

            $this->table('mod_user_implications', array())
                ->addColumn('right_id', 'integer', array('length'=>10,'null'=>true))
                ->addColumn('implies_id', 'integer', array('length'=>10,'null'=>true))
                ->create();

            $this->table('mod_user_rights', array())
                ->addColumn('app_id', 'integer', array('length'=>10,'null'=>true))
                ->addColumn('name', 'string', array('length'=>75))
                ->addColumn('has_implied', 'integer', array('length'=>1,'default'=>0))
                ->addColumn('level', 'integer', array('length'=>1,'default'=>0))
                ->create();

            $this->table('mod_user_rights_seq', array())
                ->create();

            $this->table('mycontacts', array())
                ->addColumn('Idmember', 'integer')
                ->addColumn('IdContact', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('Category', 'text', array('length'=>255))
                ->addColumn('Comment', 'text')
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('notes', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdRelMember', 'integer')
                ->addColumn('Type', 'enum', array('values'=>array (  0 => 'message',  1 => 'profile_comment',  2 => 'profile_comment_negative',  3 => 'gallery_comment',  4 => 'picture_comment',  5 => 'blog_comment',  6 => 'chat_invitation',)))
                ->addColumn('Link', 'string', array('length'=>300))
                ->addColumn('WordCode', 'string', array('length'=>300))
                ->addColumn('Checked', 'boolean', array('default'=>0))
                ->addColumn('SendMail', 'boolean', array('default'=>0))
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('TranslationParams', 'text', array('null'=>true))
                ->create();

            $this->table('online', array('id'=>false, 'primary_key'=>array('IdMember')))
                ->addColumn('IdMember', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('appearance', 'string', array('length'=>256))
                ->addColumn('lastactivity', 'string', array('length'=>256))
                ->addColumn('Status', 'string', array('length'=>32,'default'=>'Active'))
                ->create();

            $this->table('params', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('IsRealProductionDatabase', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('recordonline', 'integer', array('default'=>0))
                ->addColumn('ToggleDonateBar', 'integer', array('default'=>0))
                ->addColumn('neededperyear', 'integer', array('default'=>1260))
                ->addColumn('campaignstartdate', 'date', array('default'=>'2012-10-11'))
                ->addColumn('MailToNotifyWhenNewMemberSignup', 'text')
                ->addColumn('FeatureForumClosed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('FeatureAjaxChatClosed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('FeatureSignupClose', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('FeatureSearchPageIsClosed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('FeatureQuickSearchIsClosed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('RssFeedIsClosed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('AjaxChatSpecialAllowedList', 'text')
                ->addColumn('AjaxChatDebuLevel', 'integer', array('default'=>0))
                ->addColumn('ReloadRightsAndFlags', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('logs_id_midnight', 'integer')
                ->addColumn('previous_logs_id_midnight', 'integer')
                ->addColumn('memcache', 'enum', array('default'=>'False','values'=>array (  0 => 'False',  1 => 'True',)))
                ->addColumn('DayLightOffset', 'integer', array('default'=>0))
                ->addColumn('NbCommentsInLastComments', 'integer', array('default'=>20))
                ->addColumn('IdCommentOfTheMoment', 'integer', array('default'=>0))
                ->addColumn('MailBotMode', 'enum', array('default'=>'Manual','values'=>array (  0 => 'Auto',  1 => 'Manual',  2 => 'Stop',)))
                ->addColumn('ToggleStatsForWordsUsage', 'enum', array('default'=>'No','values'=>array (  0 => 'No',  1 => 'Yes',)))
                ->create();

            $this->table('pendingmandatory', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('FirstName', 'text')
                ->addColumn('SecondName', 'text')
                ->addColumn('LastName', 'text')
                ->addColumn('HouseNumber', 'text')
                ->addColumn('StreetName', 'text')
                ->addColumn('Zip', 'text')
                ->addColumn('IdCity', 'integer')
                ->addColumn('Comment', 'text')
                ->addColumn('Status', 'enum', array('default'=>'Pending','values'=>array (  0 => 'Pending',  1 => 'Processed',  2 => 'Rejected',)))
                ->addColumn('IdAddress', 'integer')
                ->create();

            $this->table('polls', array())
                ->addColumn('IdCreator', 'integer', array('default'=>0))
                ->addColumn('IdGroupCreator', 'integer', array('default'=>0))
                ->addColumn('Status', 'enum', array('default'=>'Project','values'=>array (  0 => 'Project',  1 => 'Open',  2 => 'Close',)))
                ->addColumn('ResultsVisibility', 'enum', array('default'=>'VisibleAfterVisit','values'=>array (  0 => 'Not Visible',  1 => 'Visible',  2 => 'VisibleAfterVisit',)))
                ->addColumn('Type', 'enum', array('default'=>'MemberPoll','values'=>array (  0 => 'MemberPoll',  1 => 'OfficialPoll',  2 => 'OfficialVote',)))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('Started', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Ended', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Title', 'integer')
                ->addColumn('ForMembersOnly', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('IdLocationsList', 'integer', array('default'=>0))
                ->addColumn('IdGroupsList', 'integer', array('default'=>0))
                ->addColumn('IdCountriesList', 'integer', array('default'=>0))
                ->addColumn('OpenToSubGroups', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('TypeOfChoice', 'enum', array('values'=>array (  0 => 'Exclusive',  1 => 'Inclusive',  2 => 'Ordered',)))
                ->addColumn('CanChangeVote', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('AllowComment', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('Description', 'integer')
                ->addColumn('WhereToRestrictMember', 'text')
                ->addColumn('Anonym', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->create();

            $this->table('polls_choices', array())
                ->addColumn('IdPoll', 'integer')
                ->addColumn('IdChoiceText', 'integer')
                ->addColumn('Counter', 'integer', array('default'=>0))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('polls_choices_hierachy', array('id'=>false, 'primary_key'=>array('IdPollChoice', 'HierarchyValue')))
                ->addColumn('IdPollChoice', 'integer')
                ->addColumn('HierarchyValue', 'integer', array('default'=>0))
                ->addColumn('Counter', 'integer', array('default'=>0))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('polls_contributions', array())
                ->addColumn('IdMember', 'integer', array('default'=>0))
                ->addColumn('Email', 'text', array('length'=>255))
                ->addColumn('EmailIsConfirmed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('comment', 'text')
                ->addColumn('IdPoll', 'integer')
                ->create();

            $this->table('polls_list_allowed_countries', array())
                ->addColumn('IdPoll', 'integer')
                ->addColumn('IdCountry', 'integer')
                ->create();

            $this->table('polls_list_allowed_groups', array())
                ->addColumn('IdPoll', 'integer')
                ->addColumn('IdGroup', 'integer')
                ->create();

            $this->table('polls_list_allowed_locations', array())
                ->addColumn('IdPoll', 'integer')
                ->addColumn('IdLocation', 'integer')
                ->create();

            $this->table('polls_record_of_choices', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdPoll', 'integer', array('default'=>0))
                ->addColumn('IdPollChoice', 'integer')
                ->addColumn('HierarchyValue', 'integer')
                ->addColumn('IdMember', 'integer')
                ->addColumn('Email', 'text', array('length'=>255))
                ->create();

            $this->table('posts_notificationqueue', array())
                ->addColumn('Status', 'enum', array('default'=>'ToSend','values'=>array (  0 => 'ToSend',  1 => 'Sent',  2 => 'Failed',)))
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdPost', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Type', 'enum', array('default'=>'buggy','values'=>array (  0 => 'newthread',  1 => 'reply',  2 => 'moderatoraction',  3 => 'deletepost',  4 => 'deletethread',  5 => 'useredit',  6 => 'translation',  7 => 'buggy',)))
                ->addColumn('IdSubscription', 'integer', array('default'=>0))
                ->addColumn('TableSubscription', 'string', array('length'=>64,'default'=>'NotSet'))
                ->create();

            $this->table('preferences', array())
                ->addColumn('position', 'integer')
                ->addColumn('codeName', 'string', array('length'=>30))
                ->addColumn('codeDescription', 'string', array('length'=>30))
                ->addColumn('Description', 'text')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('DefaultValue', 'text', array('length'=>255))
                ->addColumn('PossibleValues', 'text', array('length'=>255))
                ->addColumn('EvalString', 'text')
                ->addColumn('Status', 'enum', array('default'=>'Inactive','values'=>array (  0 => 'Normal',  1 => 'Inactive',  2 => 'Advanced',  3 => 'Beta',)))
                ->create();

            $this->table('previousversion', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('TableName', 'text', array('length'=>255))
                ->addColumn('IdInTable', 'integer')
                ->addColumn('Type', 'enum', array('default'=>'DoneByMember','values'=>array (  0 => 'DoneByMember',  1 => 'DoneByOtherMember',  2 => 'DoneByVolunteer',  3 => 'DoneByAdmin',)))
                ->addColumn('XmlOldVersion', 'text')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('privileges', array())
                ->addColumn('controller', 'string', array('length'=>64))
                ->addColumn('method', 'string', array('length'=>64))
                ->addColumn('type', 'string', array('length'=>64,'default'=>''))
                ->create();

            $this->table('privilegescopes', array('id'=>false, 'primary_key'=>array('IdMember', 'IdRole', 'IdPrivilege', 'IdType')))
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdRole', 'integer')
                ->addColumn('IdPrivilege', 'integer')
                ->addColumn('IdType', 'string', array('length'=>32))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('profilesvisits', array('id'=>false, 'primary_key'=>array('IdMember', 'IdVisitor')))
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdVisitor', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('updated', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('recentvisits', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdVisitor', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('recorded_usernames_of_left_members', array('id'=>false, 'primary_key'=>array('UsernameNotToUse')))
                ->addColumn('UsernameNotToUse', 'string', array('length'=>32))
                ->create();

            $this->table('regions', array())
                ->addColumn('Name', 'string', array('length'=>200))
                ->addColumn('ansiname', 'string', array('length'=>200))
                ->addColumn('OtherNames', 'string', array('length'=>200))
                ->addColumn('latitude', 'decimal', array('length'=>10))
                ->addColumn('longitude', 'decimal', array('length'=>10))
                ->addColumn('feature_class', 'string', array('length'=>1,'null'=>true))
                ->addColumn('feature_code', 'string', array('length'=>10,'null'=>true))
                ->addColumn('country_code', 'char', array('length'=>2))
                ->addColumn('population', 'integer', array('length'=>10))
                ->addColumn('citiesopen', 'string', array('length'=>1,'default'=>''))
                ->addColumn('IdCountry', 'integer')
                ->addColumn('NbCities', 'integer', array('default'=>0))
                ->addColumn('NbMembers', 'integer')
                ->create();

            $this->table('reports_to_moderators', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('PostComment', 'text')
                ->addColumn('IdReporter', 'integer')
                ->addColumn('ModeratorComment', 'text')
                ->addColumn('IdModerator', 'integer')
                ->addColumn('Status', 'enum', array('default'=>'Open','values'=>array (  0 => 'Open',  1 => 'OnDiscussion',  2 => 'Closed',)))
                ->addColumn('IdPost', 'integer')
                ->addColumn('IdThread', 'integer')
                ->addColumn('Type', 'enum', array('values'=>array (  0 => 'SeeText',  1 => 'AllowMeToEdit',  2 => 'Insults',  3 => 'RemoveMyPost',)))
                ->addColumn('LastWhoSpoke', 'enum', array('default'=>'Member','values'=>array (  0 => 'Member',  1 => 'Moderator',)))
                ->create();

            $this->table('rights', array())
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('Name', 'text', array('length'=>255))
                ->addColumn('Description', 'text')
                ->create();

            $this->table('rightsvolunteers', array())
                ->addColumn('IdMember', 'integer')
                ->addColumn('IdRight', 'integer')
                ->addColumn('Level', 'integer', array('default'=>0))
                ->addColumn('Scope', 'text', array('length'=>255))
                ->addColumn('Comment', 'text')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('roles', array())
                ->addColumn('name', 'string', array('length'=>128))
                ->addColumn('description', 'string', array('length'=>256))
                ->create();

            $this->table('roles_privileges', array('id'=>false, 'primary_key'=>array('IdRole', 'IdPrivilege')))
                ->addColumn('IdRole', 'integer')
                ->addColumn('IdPrivilege', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('shouts', array())
                ->addColumn('member_id_foreign', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('table', 'string', array('length'=>75))
                ->addColumn('table_id', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('created', 'datetime')
                ->addColumn('title', 'string', array('length'=>75))
                ->addColumn('text', 'text', array('length'=>16777215))
                ->create();

            $this->table('shouts_seq', array())
                ->create();

            $this->table('specialrelations', array())
                ->addColumn('updated', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Type', 'set', ['values' => ['CloseFriend','Family','SameRoof','Partner','Others']])
                ->addColumn('Comment', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdOwner', 'integer')
                ->addColumn('IdRelation', 'integer')
                ->addColumn('Confirmed', 'enum', array('default'=>'No','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->create();

            $this->table('sqlforgroupsmembers', array())
                ->addColumn('IdGroup', 'integer')
                ->addColumn('IdQuery', 'integer')
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('sqlforvolunteers', array())
                ->addColumn('Name', 'text')
                ->addColumn('Query', 'text')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('param1', 'text', array('null'=>true))
                ->addColumn('param2', 'text', array('null'=>true))
                ->addColumn('LogMe', 'enum', array('default'=>'False','values'=>array (  0 => 'False',  1 => 'True',)))
                ->addColumn('DefValueParam1', 'text')
                ->addColumn('DefValueParam2', 'text')
                ->addColumn('Param1Type', 'enum', array('default'=>'inputtext','values'=>array (  0 => 'inputtext',  1 => 'textarea',  2 => 'ListOfChoices',)))
                ->addColumn('Param2Type', 'enum', array('default'=>'inputtext','values'=>array (  0 => 'inputtext',  1 => 'textarea',  2 => 'ListOfChoices',)))
                ->addColumn('param3', 'text')
                ->addColumn('DefValueParam3', 'text')
                ->addColumn('Param3Type', 'enum', array('default'=>'inputtext','values'=>array (  0 => 'inputtext',  1 => 'textarea',  2 => 'ListOfChoices',)))
                ->addColumn('param4', 'text')
                ->addColumn('DefValueParam4', 'text')
                ->addColumn('Param4Type', 'enum', array('default'=>'inputtext','values'=>array (  0 => 'inputtext',  1 => 'textarea',  2 => 'ListOfChoices',)))
                ->addColumn('param5', 'text')
                ->addColumn('DefValueParam5', 'text')
                ->addColumn('Param5Type', 'enum', array('default'=>'inputtext','values'=>array (  0 => 'inputtext',  1 => 'textarea',  2 => 'ListOfChoices',)))
                ->create();

            $this->table('stats', array())
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('NbActiveMembers', 'integer')
                ->addColumn('NbMessageSent', 'integer')
                ->addColumn('NbMessageRead', 'integer')
                ->addColumn('NbMemberWithOneTrust', 'integer')
                ->addColumn('NbMemberWhoLoggedToday', 'integer')
                ->create();

            $this->table('suggestions', array())
                ->addColumn('summary', 'string', array('length'=>80))
                ->addColumn('description', 'text', array('length'=>16777215))
                ->addColumn('salt', 'char', array('length'=>64))
                ->addColumn('state', 'integer', array('length'=>65535))
                ->addColumn('flags', 'integer', array('default'=>0,'null'=>true))
                ->addColumn('threadId', 'integer', array('null'=>true))
                ->addColumn('created', 'date')
                ->addColumn('createdby', 'integer')
                ->addColumn('modified', 'date', array('null'=>true))
                ->addColumn('modifiedby', 'integer', array('null'=>true))
                ->addColumn('laststatechanged', 'date', array('null'=>true))
                ->addColumn('votingend', 'date', array('null'=>true))
                ->create();

            $this->table('suggestions_option_ranks', array('id'=>false, 'primary_key'=>array('optionid', 'memberhash')))
                ->addColumn('optionid', 'integer')
                ->addColumn('memberhash', 'char', array('length'=>64))
                ->addColumn('vote', 'integer', array('length'=>1))
                ->create();

            $this->table('suggestions_options', array())
                ->addColumn('suggestionId', 'integer')
                ->addColumn('state', 'integer', array('default'=>0))
                ->addColumn('summary', 'string', array('length'=>160))
                ->addColumn('description', 'text', array('length'=>16777215))
                ->addColumn('created', 'date')
                ->addColumn('createdBy', 'integer')
                ->addColumn('modified', 'date', array('null'=>true))
                ->addColumn('modifiedBy', 'integer', array('null'=>true))
                ->addColumn('deleted', 'date', array('null'=>true))
                ->addColumn('deletedBy', 'integer', array('null'=>true))
                ->addColumn('mutuallyExclusiveWith', 'text', array('length'=>16777215,'null'=>true))
                ->addColumn('rank', 'integer', array('length'=>255,'null'=>true))
                ->addColumn('orderHint', 'integer', array('null'=>true))
                ->create();

            $this->table('suggestions_votes', array())
                ->addColumn('suggestionId', 'integer')
                ->addColumn('optionId', 'integer')
                ->addColumn('rank', 'integer', array('length'=>1))
                ->addColumn('memberHash', 'string', array('length'=>64))
                ->create();

            $this->table('tags', array())
                ->addColumn('Name', 'integer')
                ->addColumn('Description', 'integer')
                ->addColumn('Type', 'enum', array('default'=>'UserTag','values'=>array (  0 => 'Category',  1 => 'UserTag',)))
                ->addColumn('Position', 'integer', array('default'=>200))
                ->addColumn('created', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->create();

            $this->table('tags_threads', array('id'=>false, 'primary_key'=>array('IdTag', 'IdThread')))
                ->addColumn('IdTag', 'integer')
                ->addColumn('IdThread', 'integer')
                ->create();

            $this->table('tantable', array('id'=>false, 'primary_key'=>array('Username')))
                ->addColumn('Username', 'string', array('length'=>100,'default'=>'nobody'))
                ->addColumn('OnePad', 'biginteger', array('default'=>0))
                ->create();

            $this->table('timezone', array())
                ->addColumn('tzname', 'text')
                ->addColumn('Offset', 'integer', array('default'=>0))
                ->addColumn('DayLightSaving', 'enum', array('default'=>'Yes','values'=>array (  0 => 'Yes',  1 => 'No',)))
                ->create();

            $this->table('translations', array())
                ->addColumn('IdLanguage', 'integer')
                ->addColumn('IdOwner', 'integer')
                ->addColumn('IdTrad', 'integer')
                ->addColumn('IdTranslator', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('Type', 'enum', array('values'=>array (  0 => 'member',  1 => 'translator',  2 => 'admin',)))
                ->addColumn('Sentence', 'text')
                ->addColumn('IdRecord', 'integer')
                ->addColumn('TableColumn', 'string', array('length'=>200,'default'=>'NotSet'))
                ->create();

            $this->table('trip', array('id'=>false, 'primary_key'=>array('trip_id')))
                ->addColumn('trip_id', 'integer', array('length'=>10))
                ->addColumn('trip_options', 'binary')
                ->addColumn('trip_touched', 'datetime', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdMember', 'integer', array('null'=>true))
                ->create();

            $this->table('trip_data', array('id'=>false, 'primary_key'=>array('trip_id')))
                ->addColumn('trip_id', 'integer', array('length'=>10))
                ->addColumn('edited', 'datetime', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('trip_name', 'string')
                ->addColumn('trip_text', 'text', array('length'=>16777215))
                ->addColumn('trip_descr', 'text', array('length'=>4294967295))
                ->create();

            $this->table('trip_seq', array())
                ->create();

            $this->table('trip_to_gallery', array())
                ->addColumn('trip_id_foreign', 'integer', array('length'=>10))
                ->addColumn('gallery_id_foreign', 'integer', array('length'=>10))
                ->create();

            $this->table('urlheader_languages', array('id'=>false, 'primary_key'=>array('urlheader')))
                ->addColumn('urlheader', 'string', array('length'=>10))
                ->addColumn('IdLanguage', 'integer')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('user', array())
                ->addColumn('auth_id', 'integer', array('length'=>10,'null'=>true))
                ->addColumn('handle', 'string', array('default'=>''))
                ->addColumn('email', 'string', array('length'=>75))
                ->addColumn('pw', 'text')
                ->addColumn('active', 'integer', array('length'=>1,'default'=>0))
                ->addColumn('lastlogin', 'datetime', array('null'=>true))
                ->addColumn('location', 'integer', array('length'=>10,'null'=>true))
                ->create();

            $this->table('user_friends', array())
                ->addColumn('user_id_foreign', 'integer', array('length'=>10))
                ->addColumn('user_id_foreign_friend', 'integer', array('length'=>10))
                ->create();

            $this->table('user_inbox', array())
                ->addColumn('user_id_foreign', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('message_id_foreign', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('seen', 'integer', array('length'=>255,'default'=>0))
                ->addColumn('replied', 'integer', array('length'=>255,'default'=>0))
                ->create();

            $this->table('user_outbox', array())
                ->addColumn('user_id_foreign', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('message_id_foreign', 'integer', array('length'=>10,'default'=>0))
                ->create();

            $this->table('user_seq', array())
                ->create();

            $this->table('user_settings', array())
                ->addColumn('user_id', 'integer', array('length'=>10,'default'=>0))
                ->addColumn('setting', 'string', array('length'=>25,'default'=>''))
                ->addColumn('value', 'text', array('null'=>true))
                ->addColumn('valueint', 'integer', array('null'=>true))
                ->addColumn('valuedate', 'datetime', array('null'=>true))
                ->create();

            $this->table('verifiedmembers', array())
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('IdVerifier', 'integer')
                ->addColumn('IdVerified', 'integer')
                ->addColumn('AddressVerified', 'enum', array('default'=>'False','values'=>array (  0 => 'False',  1 => 'True',)))
                ->addColumn('NameVerified', 'enum', array('default'=>'False','values'=>array (  0 => 'False',  1 => 'True',)))
                ->addColumn('Comment', 'text')
                ->addColumn('Type', 'enum', array('default'=>'VerifiedByNormal','values'=>array (  0 => 'Buggy',  1 => 'VerifiedByNormal',  2 => 'VerifiedByVerified',  3 => 'VerifiedByApproved',)))
                ->create();

            $this->table('volunteer_boards', array())
                ->addColumn('Name', 'string', array('length'=>64))
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('PurposeComment', 'text', array('length'=>16777215))
                ->addColumn('TextContent', 'text', array('length'=>16777215))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('words', array())
                ->addColumn('code', 'string', array('length'=>256))
                ->addColumn('ShortCode', 'char', array('length'=>16,'default'=>'en'))
                ->addColumn('Sentence', 'text')
                ->addColumn('updated', 'timestamp', array('default'=>'CURRENT_TIMESTAMP'))
                ->addColumn('donottranslate', 'enum', array('default'=>'no','values'=>array (  0 => 'no',  1 => 'yes',)))
                ->addColumn('IdLanguage', 'integer', array('default'=>0))
                ->addColumn('Description', 'text')
                ->addColumn('IdMember', 'integer', array('default'=>0))
                ->addColumn('created', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->addColumn('TranslationPriority', 'integer', array('default'=>5))
                ->addColumn('isarchived', 'integer', array('length'=>255,'null'=>true))
                ->addColumn('majorupdate', 'timestamp', array('default'=>'0000-00-00 00:00:00'))
                ->create();

            $this->table('words_use', array('id'=>false, 'primary_key'=>array('code')))
                ->addColumn('code', 'string', array('length'=>100))
                ->addColumn('NbUse', 'integer', array('default'=>0))
                ->create();
    }
}