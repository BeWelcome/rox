<?php

use Phinx\Migration\AbstractMigration;

/************************************
 * Class DropObsoleteDbItems1
 *
 * Remove database items that don't show in respective parts of the code anymore
 *
 * See ticket: #2208
 *
 */
class DropObsoleteDbItems1 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {

        // list of involved database items
        $tables = array(
            'cal_event_to_tag','cal_eventdata','cal_events','cal_tags',
            'chat_messages','chat_room_moderators','chat_rooms','chat_rooms_members',
            'comments_ofthemomment_votes',
            'counters_cities_nbmembers','counters_regions_nbmembers',
            'geo_location','geo_usage_before_jyh_touch_it',
            'groups_locations','groupshierarchy',
            'localvolmessages','localvolmessages_location',
            'members_updating_status','memberscounters',
            'regions_count',
            'volunteers_reports_schedule'
        );
        $views = array(
            'nbmembersbycities','v_activemembers','v_countries',
            'v_flags','v_members_roles','v_memberspublic','v_words_english'
        );
        $functions = array(
            'CommentNbVotes','fLanguageName','MemberNbComments','minutes_between','NbSharedGroup'
        );

        // drop all of them
        $this->execute("
DROP TABLE " . implode(',',$tables) . ";"
                        );
        $this->execute("
DROP VIEW " . implode(',',$views) . ";"
                        );
        foreach ($functions as $func){
            $this->execute("
DROP FUNCTION $func;"
                            );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
CREATE TABLE `cal_event_to_tag` (
  `tag_id` int(10) unsigned DEFAULT NULL,
  `event_id` int(10) unsigned DEFAULT NULL,
  KEY `tag_id` (`tag_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `cal_eventdata` (
  `event_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `link` text,
  `description` text,
  KEY `event_id` (`event_id`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `cal_events` (
  `event_id` int(10) unsigned NOT NULL,
  `user_id_foreign` int(10) unsigned DEFAULT NULL COMMENT 'Owner of event',
  `from` datetime NOT NULL,
  `to` datetime DEFAULT NULL,
  `type` int(2) unsigned NOT NULL DEFAULT '0' COMMENT 'visibility or other options',
  `created` datetime NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `member_id` (`user_id_foreign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `cal_tags` (
  `id` int(10) unsigned NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the chat message',
  `IdAuthor` int(11) NOT NULL COMMENT 'who wrote the message',
  `IdRoom` int(11) NOT NULL DEFAULT '1' COMMENT 'chatroom of the message. For now we begin with one chatroom only.',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when the message was last modified',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the message was sent (added to the DB)',
  `text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'text content of the message - for now it is raw html',
  PRIMARY KEY (`id`),
  KEY `chatroom_id` (`IdRoom`)
) ENGINE=InnoDB AUTO_INCREMENT=10693 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `chat_room_moderators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when the record was updated',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `IdRoom` int(11) NOT NULL COMMENT 'The room',
  `IdMember` int(11) NOT NULL COMMENT 'The member',
  `MemberCan` set('InviteAndKick','CleanRoom') NOT NULL COMMENT 'The thing the member is allowed to do in the room',
  PRIMARY KEY (`id`),
  KEY `Id_RoomMember` (`IdRoom`,`IdMember`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'if of the room',
  `RoomTitle` int(11) NOT NULL COMMENT 'This is a forum trad (this title will be used in the header or the room web page)',
  `RoomDescription` int(11) NOT NULL COMMENT 'This is a forum_trad, will be used to describe the purpose of the room',
  `IdRoomOwner` int(11) NOT NULL COMMENT 'This is the member owning the room',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when the record was updated',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `RoomStatus` enum('Open','Close') NOT NULL DEFAULT 'Open' COMMENT 'Wether the room is open or Closed',
  `RoomType` enum('Public','GroupDedicated','Private') NOT NULL DEFAULT 'Private' COMMENT 'Type of the room',
  `IdGroupOwner` int(11) NOT NULL DEFAULT '0' COMMENT 'Optional group Id for room with Type GroupDedicated',
  `RefreshIntervall` int(11) NOT NULL DEFAULT '4500' COMMENT 'This is the refresh intervall for the room in second',
  PRIMARY KEY (`id`),
  KEY `IdRoomOwner` (`IdRoomOwner`,`IdGroupOwner`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `chat_rooms_members` (
  `IdRoom` int(11) NOT NULL COMMENT 'The room where the member is',
  `IdMember` int(11) NOT NULL COMMENT 'The Id of the member',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the members came in',
  `StatusInRoom` enum('Invited','Banned') NOT NULL DEFAULT 'Invited' COMMENT 'This is the status of the member in the room, it can be used to ban a member from a room',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the members refresh the room',
  `LastWrite` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when teh user in th room did his last write',
  `LastRefresh` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the member has refreshed his room window for the last time',
  `CountActivity` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of loop (ie with room window open) for this member',
  PRIMARY KEY (`IdRoom`,`IdMember`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `comments_ofthemomment_votes` (
  `IdMember` int(11) NOT NULL COMMENT 'id of the member',
  `IdComment` int(11) NOT NULL COMMENT 'Id of the comment',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when teh record was updated',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the record was created',
  UNIQUE KEY `Id_Member_Comment` (`IdMember`,`IdComment`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `counters_cities_nbmembers` (
  `IdCity` int(11) NOT NULL DEFAULT '0' COMMENT 'This is the id of the corresponding city in the cities view table',
  `NbMembers` int(11) NOT NULL DEFAULT '0' COMMENT 'Current number of members in this city (redudancy, it is computed)',
  PRIMARY KEY (`IdCity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `counters_regions_nbmembers` (
  `IdRegion` int(11) NOT NULL DEFAULT '0' COMMENT 'This is the id of the corresponding region in the regions view table',
  `NbMembers` int(11) NOT NULL DEFAULT '0' COMMENT 'Current number of members in this region (redudancy, it is computed)',
  PRIMARY KEY (`IdRegion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `geo_location` (
  `locationId` int(11) NOT NULL AUTO_INCREMENT,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `locationId` (`locationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `groups_locations` (
  `id` int(11) NOT NULL COMMENT 'unique Id of the record',
  `IdGroupMembership` int(11) NOT NULL COMMENT 'Id of the groupmebership we are refering too',
  `IdLocation` int(11) NOT NULL COMMENT 'for now it is an IdCity or IdCOuntry, but in future it will be any geonameid',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when it was updated',
  `AdminComment` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Dont yet know what it will be used for',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when it was created',
  `MemberComment` int(11) NOT NULL COMMENT 'an (future) additiobal comment of the member (?)',
  PRIMARY KEY (`IdGroupMembership`,`IdLocation`),
  KEY `IdLocation` (`IdLocation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `groupshierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `IdGroupParent` int(11) NOT NULL COMMENT 'Parent Group',
  `IdGroupChild` int(11) NOT NULL COMMENT 'Child Group',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `IdGroupParent` (`IdGroupParent`,`IdGroupChild`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `localvolmessages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Status` enum('ToApprove','ToSend','Sent') NOT NULL DEFAULT 'ToApprove' COMMENT 'Status of the message (if it is to be approved, to send by mailbot or Sent)',
  `IdSender` int(11) NOT NULL COMMENT 'Id of the sender of the message',
  `Type` enum('Meeting','HelpRequest','Info') NOT NULL DEFAULT 'Info' COMMENT 'type of the message',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the message was updated',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the message was created',
  `PurposeDescription` text NOT NULL COMMENT 'Purpose of the message, either the creator or the local vol coordinator can add details here, these details are only for them',
  `IdTitleText` int(11) NOT NULL COMMENT 'a forum trads for teh title of the message',
  `IdMessageText` int(11) NOT NULL COMMENT 'a forum trads for the text of the message',
  PRIMARY KEY (`id`),
  KEY `IdSender` (`IdSender`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `localvolmessages_location` (
  `IdLocation` int(11) NOT NULL COMMENT 'Location where the members are supposed to recieve the message',
  `IdLocalVolMessage` int(11) NOT NULL COMMENT 'Id of the message',
  UNIQUE KEY `IdLocation` (`IdLocation`,`IdLocalVolMessage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `members_updating_status` (
  `IdMember` int(11) NOT NULL COMMENT 'id of the members',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when the record was updated',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'when the record was created',
  `OldStatus` tinytext NOT NULL COMMENT 'The value of the status before',
  `NewStatus` tinytext NOT NULL COMMENT 'the new value of the status',
  KEY `IdMember` (`IdMember`,`updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `memberscounters` (
  `IdMember` int(11) NOT NULL COMMENT 'Id of the member',
  `NbGoodComment` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of good comment the member has',
  `NbComment` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of comments the member has',
  `NbSentMessages` int(11) NOT NULL DEFAULT '0' COMMENT 'Nb of messages the member has sent',
  `NbSendMessageRead` int(11) NOT NULL DEFAULT '0' COMMENT 'Nb of messages the member has ent and the receiver has read',
  `NbSentSpam` int(11) NOT NULL DEFAULT '0' COMMENT 'Nb of messages the member has sent and that have been marked as spam',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the record was updated',
  `NbLogged` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of time the member has logged',
  PRIMARY KEY (`IdMember`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `regions_count` (
  `id` int(11) NOT NULL COMMENT 'geoid',
  `count` int(11) NOT NULL COMMENT 'count of members in this region'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `volunteers_reports_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `IdVolunteer` int(11) NOT NULL COMMENT 'Id of the accepter',
  `TimeToDeliver` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'datetime for the next delivery of teh report',
  `DelayInHourForNextOne` int(11) NOT NULL DEFAULT '24' COMMENT 'delay to schedule the next report',
  `Type` enum('Accepter','Group','Abuse') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of report',
  PRIMARY KEY (`id`),
  KEY `IdAccepter` (`IdVolunteer`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='OBSOLETE'
                        ");
        $this->execute("
CREATE TABLE `geo_usage_before_jyh_touch_it` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `geoId` int(11) NOT NULL COMMENT 'geonameId or locationId',
  `typeId` int(11) NOT NULL COMMENT 'id specifying the usage type, eg member, blog or gallery, its a foreign key to geo_types',
  `count` int(11) NOT NULL COMMENT 'counts the number of references of type typeId to ths geoId',
  PRIMARY KEY (`geoId`,`typeId`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COMMENT='table to keep track how often a geoId is used by a certain t'
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `nbmembersbycities` AS select `members`.`IdCity` AS `IdCity`,count(0) AS `NbMembers` from `members` where ((`members`.`Status` = _utf8'Active') or (`members`.`Status` = _utf8'ChoiceActive')) group by `members`.`IdCity`
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_activemembers` AS select `members`.`id` AS `id`,`members`.`Username` AS `Username`,`members`.`Status` AS `Status`,`members`.`ChangedId` AS `ChangedId`,`members`.`Email` AS `Email`,`members`.`HomePhoneNumber` AS `HomePhoneNumber`,`members`.`CellPhoneNumber` AS `CellPhoneNumber`,`members`.`WorkPhoneNumber` AS `WorkPhoneNumber`,`members`.`SecEmail` AS `SecEmail`,`members`.`FirstName` AS `FirstName`,`members`.`SecondName` AS `SecondName`,`members`.`LastName` AS `LastName`,`members`.`Accomodation` AS `Accomodation`,`members`.`AdditionalAccomodationInfo` AS `AdditionalAccomodationInfo`,`members`.`ILiveWith` AS `ILiveWith`,`members`.`IdentityCheckLevel` AS `IdentityCheckLevel`,`members`.`InformationToGuest` AS `InformationToGuest`,`members`.`TypicOffer` AS `TypicOffer`,`members`.`Offer` AS `Offer`,`members`.`MaxGuest` AS `MaxGuest`,`members`.`MaxLenghtOfStay` AS `MaxLenghtOfStay`,`members`.`Organizations` AS `Organizations`,`members`.`Restrictions` AS `Restrictions`,`members`.`OtherRestrictions` AS `OtherRestrictions`,`members`.`bday` AS `bday`,`members`.`bmonth` AS `bmonth`,`members`.`byear` AS `byear`,`members`.`updated` AS `updated`,`members`.`created` AS `created`,`members`.`LastLogin` AS `LastLogin`,`members`.`SecurityFlag` AS `SecurityFlag`,`members`.`Quality` AS `Quality`,`members`.`ProfileSummary` AS `ProfileSummary`,`members`.`Occupation` AS `Occupation`,`members`.`CounterGuests` AS `CounterGuests`,`members`.`CounterHosts` AS `CounterHosts`,`members`.`CounterTrusts` AS `CounterTrusts`,`members`.`PassWord` AS `PassWord`,`members`.`Gender` AS `Gender`,`members`.`HideGender` AS `HideGender`,`members`.`GenderOfGuest` AS `GenderOfGuest`,`members`.`MotivationForHospitality` AS `MotivationForHospitality`,`members`.`HideBirthDate` AS `HideBirthDate`,`members`.`BirthDate` AS `BirthDate`,`members`.`AdressHidden` AS `AdressHidden`,`members`.`WebSite` AS `WebSite`,`members`.`chat_SKYPE` AS `chat_SKYPE`,`members`.`chat_ICQ` AS `chat_ICQ`,`members`.`chat_AOL` AS `chat_AOL`,`members`.`chat_MSN` AS `chat_MSN`,`members`.`chat_YAHOO` AS `chat_YAHOO`,`members`.`chat_Others` AS `chat_Others`,`members`.`IdCity` AS `IdCity`,`members`.`FutureTrips` AS `FutureTrips`,`members`.`OldTrips` AS `OldTrips`,`members`.`LogCount` AS `LogCount`,`members`.`Hobbies` AS `Hobbies`,`members`.`Books` AS `Books`,`members`.`Music` AS `Music`,`members`.`PastTrips` AS `PastTrips`,`members`.`PlannedTrips` AS `PlannedTrips`,`members`.`PleaseBring` AS `PleaseBring`,`members`.`OfferGuests` AS `OfferGuests`,`members`.`OfferHosts` AS `OfferHosts`,`members`.`PublicTransport` AS `PublicTransport`,`members`.`Movies` AS `Movies`,`members`.`chat_GOOGLE` AS `chat_GOOGLE` from `members` where (`members`.`Status` in (_utf8'Active',_utf8'ChoiceInactive'))
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_countries` AS select `countries`.`id` AS `id`,`countries`.`Name` AS `Name`,`countries`.`isoalpha2` AS `isoalpha2`,`countries`.`isoalpha3` AS `isoalpha3`,`countries`.`isonumeric` AS `isonumeric`,`countries`.`fipscode` AS `fipscode`,`countries`.`capital` AS `capital`,`countries`.`areaInSqKm` AS `areaInSqKm`,`countries`.`population` AS `population`,`countries`.`continent` AS `continent`,`countries`.`languages` AS `languages`,`countries`.`regionopen` AS `regionopen`,`countries`.`countadmin1` AS `countadmin1`,`countries`.`NbMembers` AS `NbMembers` from `countries`
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_flags` AS select `flagsmembers`.`id` AS `id`,`flagsmembers`.`IdMember` AS `IdMember`,`flagsmembers`.`IdFlag` AS `IdFlag`,`flagsmembers`.`Level` AS `Level`,`flagsmembers`.`Scope` AS `Scope`,`flagsmembers`.`Comment` AS `Comment`,`flagsmembers`.`updated` AS `updated`,`flagsmembers`.`created` AS `created`,`members`.`Username` AS `Username`,`flags`.`Name` AS `Name` from ((`flagsmembers` join `members`) join `flags`) where ((`flagsmembers`.`IdMember` = `members`.`id`) and (`flags`.`id` = `flagsmembers`.`IdFlag`))
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_members_roles` AS select `m`.`Username` AS `Username`,`mr`.`IdMember` AS `IdMember`,`mr`.`IdRole` AS `IdRole`,`mr`.`updated` AS `updated`,`r`.`name` AS `RoleName`,`r`.`description` AS `Description`,`ps`.`IdType` AS `IdType` from (((((`members` `m` join `members_roles` `mr`) join `roles` `r`) join `roles_privileges` `rp`) join `privileges` `p`) join `privilegescopes` `ps`) where ((`m`.`id` = `mr`.`IdMember`) and (`r`.`id` = `mr`.`IdRole`) and (`rp`.`IdRole` = `r`.`id`) and (`rp`.`IdPrivilege` = `p`.`id`) and (`ps`.`IdMember` = `m`.`id`) and (`p`.`id` = `ps`.`IdPrivilege`) and (`ps`.`IdRole` = `r`.`id`))
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_memberspublic` AS select `members`.`id` AS `id`,`members`.`Username` AS `Username`,`members`.`Status` AS `Status`,`members`.`ChangedId` AS `ChangedId`,`members`.`Email` AS `Email`,`members`.`HomePhoneNumber` AS `HomePhoneNumber`,`members`.`CellPhoneNumber` AS `CellPhoneNumber`,`members`.`WorkPhoneNumber` AS `WorkPhoneNumber`,`members`.`SecEmail` AS `SecEmail`,`members`.`FirstName` AS `FirstName`,`members`.`SecondName` AS `SecondName`,`members`.`LastName` AS `LastName`,`members`.`Accomodation` AS `Accomodation`,`members`.`AdditionalAccomodationInfo` AS `AdditionalAccomodationInfo`,`members`.`ILiveWith` AS `ILiveWith`,`members`.`IdentityCheckLevel` AS `IdentityCheckLevel`,`members`.`InformationToGuest` AS `InformationToGuest`,`members`.`TypicOffer` AS `TypicOffer`,`members`.`Offer` AS `Offer`,`members`.`MaxGuest` AS `MaxGuest`,`members`.`MaxLenghtOfStay` AS `MaxLenghtOfStay`,`members`.`Organizations` AS `Organizations`,`members`.`Restrictions` AS `Restrictions`,`members`.`OtherRestrictions` AS `OtherRestrictions`,`members`.`bday` AS `bday`,`members`.`bmonth` AS `bmonth`,`members`.`byear` AS `byear`,`members`.`updated` AS `updated`,`members`.`created` AS `created`,`members`.`LastLogin` AS `LastLogin`,`members`.`SecurityFlag` AS `SecurityFlag`,`members`.`Quality` AS `Quality`,`members`.`ProfileSummary` AS `ProfileSummary`,`members`.`Occupation` AS `Occupation`,`members`.`CounterGuests` AS `CounterGuests`,`members`.`CounterHosts` AS `CounterHosts`,`members`.`CounterTrusts` AS `CounterTrusts`,`members`.`PassWord` AS `PassWord`,`members`.`Gender` AS `Gender`,`members`.`HideGender` AS `HideGender`,`members`.`GenderOfGuest` AS `GenderOfGuest`,`members`.`MotivationForHospitality` AS `MotivationForHospitality`,`members`.`HideBirthDate` AS `HideBirthDate`,`members`.`BirthDate` AS `BirthDate`,`members`.`AdressHidden` AS `AdressHidden`,`members`.`WebSite` AS `WebSite`,`members`.`chat_SKYPE` AS `chat_SKYPE`,`members`.`chat_ICQ` AS `chat_ICQ`,`members`.`chat_AOL` AS `chat_AOL`,`members`.`chat_MSN` AS `chat_MSN`,`members`.`chat_YAHOO` AS `chat_YAHOO`,`members`.`chat_Others` AS `chat_Others`,`members`.`IdCity` AS `IdCity`,`members`.`FutureTrips` AS `FutureTrips`,`members`.`OldTrips` AS `OldTrips`,`members`.`LogCount` AS `LogCount`,`members`.`Hobbies` AS `Hobbies`,`members`.`Books` AS `Books`,`members`.`Music` AS `Music`,`members`.`PastTrips` AS `PastTrips`,`members`.`PlannedTrips` AS `PlannedTrips`,`members`.`PleaseBring` AS `PleaseBring`,`members`.`OfferGuests` AS `OfferGuests`,`members`.`OfferHosts` AS `OfferHosts`,`members`.`PublicTransport` AS `PublicTransport`,`members`.`Movies` AS `Movies`,`members`.`chat_GOOGLE` AS `chat_GOOGLE` from (`members` join `memberspublicprofiles`) where ((`members`.`id` = `memberspublicprofiles`.`IdMember`) and (`members`.`Status` in (_utf8'Active',_utf8'ChoiceInactive')))
                        ");
        $this->execute("
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_words_english` AS select `words`.`id` AS `id`,`words`.`code` AS `code`,`words`.`ShortCode` AS `ShortCode`,`words`.`Sentence` AS `Sentence`,`words`.`updated` AS `updated`,`words`.`donottranslate` AS `donottranslate`,`words`.`IdLanguage` AS `IdLanguage`,`words`.`Description` AS `Description`,`words`.`IdMember` AS `IdMember`,`words`.`created` AS `created`,`words`.`TranslationPriority` AS `TranslationPriority` from `words` where (`words`.`IdLanguage` = 0)
                        ");
        $this->execute("
CREATE DEFINER=`root`@`localhost` FUNCTION `CommentNbVotes`(v_IdComment INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
  DECLARE iRes INT ;
      select count(*) into iRes from comments_ofthemomment_votes where IdComment=v_IdComment ;
 RETURN iRes ;
    END
                        ");
        $this->execute("
CREATE DEFINER=`root`@`localhost` FUNCTION `fLanguageName`(s VARCHAR(32)) RETURNS varchar(200) CHARSET utf8
    DETERMINISTIC
begin
declare sResult VARCHAR(32) ;
if ( s REGEXP ('[0-9]')) then
	select EnglishName from languages where id=s into sResult ;
else
	select EnglishName from languages where ShortCode=s into sResult ;
end if ;
return sResult ;
end
                        ");
        $this->execute("
CREATE DEFINER=`root`@`localhost` FUNCTION `MemberNbComments`(v_IdMember INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
  DECLARE iRes INT ;
      select count(*) into iRes from comments where IdToMember=v_IdMember ;
 RETURN iRes ;
    END
                        ");
        $this->execute("
CREATE DEFINER=`root`@`localhost` FUNCTION `minutes_between`(A TIMESTAMP, B TIMESTAMP) RETURNS int(11)
    READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE RETURN_VALUE INT;
    SET RETURN_VALUE = DATEDIFF(A, B) * 24 * 60 * 60 + (TIME_TO_SEC(A) - TIME_TO_SEC(B));
    RETURN (IF(RETURN_VALUE > 0, RETURN_VALUE, RETURN_VALUE * -1)) DIV 60;
    END
                        ");
        $this->execute("
CREATE DEFINER=`root`@`localhost` FUNCTION `NbSharedGroup`(IdMember1 INT,IdMember2 int) RETURNS int(11)
    DETERMINISTIC
begin
declare iResult int ;
SELECT count(*) into iResult
FROM membersgroups AS g1, membersgroups AS g2
WHERE g1.IdGroup = g2.IdGroup
AND g1.Status = 'In'
AND g2.Status = 'In'
AND g1.IdMember =IdMember1
AND g2.IdMember =IdMember2 ;
return (iResult);
end
                        ");
    }
}
