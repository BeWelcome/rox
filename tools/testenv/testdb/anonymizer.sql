-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 18. Mai 2014 um 09:55
-- Server Version: 5.6.16
-- PHP-Version: 5.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Structure for table `dbversion`
--

CREATE TABLE IF NOT EXISTS `dbversion` (
  `version` int(11) NOT NULL COMMENT 'Version info as integer (major * 256 + minor). Example 2.5 = 517.',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date the migration script ran.',
  `active` int(1) DEFAULT NULL COMMENT 'Indicates which is the active version.',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TRUNCATE `dbversion`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/* Current DB version is 2.5 */
REPLACE INTO `dbversion` SET version = 517, active = 1;

update activities set address = '3 Sample Road, Somewhere', description = 'Some funny activity';

update blog_comments set title = Substring('This is a blog comment title', 1, length(title)), `text` = Substring('This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. This is a blog comment. ', 1, length(`text`));

update blog_data set blog_title = substring('This is a rather stupid blog title', 1, length(blog_title)), blog_text = Substring('<p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Integer ac magna. Ut ultricies cursus magna. Aliquam metus ligula, aliquam sed, adipiscing ut, vulputate eu, dui. Nulla facilisi. Etiam id arcu. Quisque feugiat. Pellentesque ultrices lacus non erat. Suspendisse condimentum adipiscing augue. Suspendisse aliquet pulvinar dolor. Vivamus sit amet arcu eu urna egestas eleifend. Cras sit amet mauris. Praesent ligula mauris, convallis at, blandit id, aliquet sed, ipsum. Phasellus pellentesque, justo at sagittis vehicula, libero eros consequat lacus, id convallis dolor mi sit amet odio. Etiam velit sapien, varius sed, semper sed, pellentesque ut, ipsum. Proin gravida dui in diam. Sed imperdiet augue vitae neque. Donec ultricies rhoncus quam. </p> <hr/><p>Maecenas nisl quam, pellentesque ut, vestibulum tincidunt, varius sit amet, eros. Nullam sit amet felis. Nullam ligula. Suspendisse pede. Mauris et turpis ut quam adipiscing faucibus. Suspendisse eu massa et dolor vestibulum tincidunt. Donec augue. Nunc magna. Morbi eu sem. Aenean aliquam, justo vel accumsan egestas, felis lorem tincidunt sapien, quis venenatis ipsum eros id ante. </p> <p>Nulla rhoncus ullamcorper mi. Nam ut magna et ante egestas hendrerit. In in ante. Ut massa erat, laoreet vel, rutrum eget, congue vitae, lectus. Vivamus porttitor, nulla sit amet sollicitudin blandit, mi augue varius velit, in ullamcorper leo nunc non erat. Donec ac eros id arcu congue tempor. Quisque molestie tortor in arcu. Donec libero nisi, elementum in, semper feugiat, dignissim vel, ante. Vestibulum iaculis lorem in libero. Proin ipsum massa, porttitor eget, tincidunt sed, varius eget, tortor. Nam vel tellus. Integer velit orci, convallis id, pharetra sed, congue sit amet, ante. Maecenas malesuada. Maecenas faucibus aliquet augue. Integer a mauris. Aliquam fringilla feugiat est. Vivamus sed risus. Cras vestibulum, dolor ac vestibulum vulputate, nisi mi tincidunt lorem, vitae suscipit magna sapien ac nulla. </p><p>Vivamus eget mi ut leo hendrerit rutrum. Etiam cursus nulla vel est. Phasellus fringilla. Ut congue, metus convallis cursus convallis, tellus magna pretium tortor, ut cursus metus arcu quis velit. Nulla consectetuer, est eu tincidunt condimentum, elit lacus viverra neque, at vestibulum augue metus vel massa. In iaculis lacus vitae lectus. Vivamus turpis. Mauris nec felis eget pede lobortis tempor. Suspendisse eu pede. Fusce tempus nonummy risus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed volutpat fermentum ipsum. Nullam nisl ligula, accumsan congue, ullamcorper ut, venenatis sed, dolor. Maecenas id nunc. Etiam leo lorem, dapibus sit amet, tristique quis, dictum id, turpis. Duis nunc. Nunc egestas.  </p><p>Curabitur nec ipsum in odio varius cursus. Phasellus feugiat volutpat est. Curabitur semper. Duis tincidunt, enim eget tristique bibendum, dolor dolor viverra tortor, sed malesuada tellus neque eu neque. Donec lacus metus, mattis ut, fringilla in, sodales ac, velit. Maecenas est. Proin sagittis lacus sit amet turpis. Maecenas sit amet magna. Sed mattis tellus a orci. Donec at lacus id felis dignissim viverra. Nullam vehicula. In sed lectus ut velit varius lobortis. Quisque blandit metus a ligula condimentum venenatis. Fusce lorem elit, lobortis eu, feugiat ac, dignissim blandit, magna. Cras dapibus purus eget orci. Mauris commodo. Phasellus euismod mi eget sem. </p>', 1, length(blog_text));

update comments set textfree = substring('I\'m going to describe my experience here. It might be good or bad. I couldn\'t tell the difference. Now we need some more text. Fine we are done', 1, length(textfree)), textwhere = 'We met somewhere over the rainbow.';

update donations set email = 'donor@bewelcome.org', namegiven = '', referencepaypal = '', membercomment = '', systemcomment = '';

update flagsmembers set `comment`= 'Some comment';

update forums_posts set message = 'This is a forum post';

update forums_threads set title = 'This is a thread title';

update gallery set title = 'Gallery title', `text` = 'Gallery comment';

UPDATE gallery_comments set title = substring('This is a stupid comment title but who cares?', 1, length(title)), `text`= substring('Some lovely comment about the picture shown above. Or maybe not so lovely. Who knows?', 1, length(`text`));

update gallery_items set original = concat('this_is_picture_number', id, '.jpg'), title = concat('this is picture ', id), description = substring('This is the description of the pictures. I guess they are normally short. So I just truncated them...', 1, length(description));

update groups set `name`= concat('group name ', id);

update members, user set members.username = concat('member-', members.id), user.handle = concat('member-', members.id), user.email = concat('member-', members.id, '@test.bewelcome.org') where members.username = user.handle;

update members set username = concat('member-', id), WebSite = NULL, PhotoFilePath = '', password = ''  where username not like 'member-%';

update user set handle = 'unused', email = 'unused@test.bewelcome.org' where handle not like 'member-%';

update members set username = concat( 'member-', id);

/* Anonymize website on profile */
UPDATE members SET website = 'http://www.bewelcome.org' WHERE NOT website = '';

update membersphotos set FilePath = concat('/membersphotos/', IdMember, '.jpg');

update messages set message = concat('A message that had ', length(message), ' characters before it was anonymized'), CheckerComment = '';

update mycontacts set category = 'Category', `Comment` = substring('My note about this person', 1, length(`comment`));

update polls_contributions set `comment` = substring('Poll comment left by voter. Lorem ipsum dolores et cetera pp. No one really can follow this debate.', 1, length(`comment`));

update reports_to_moderators set PostComment = '';

update rightsvolunteers set `comment` = 'Granted by someone';

update shouts set title = substring('Trip comment title without meaning', 1, length(title)), `text` = substring('Some comment about a trip someone left here. Might have been a rather long text as well.', 1, length(`text`)) where `table` = 'trip';

update shouts set title = substring('Gallery comment title without meaning', 1, length(title)), `text` = substring('Some comment about a gallery someone left here. Might have been a rather long text as well.', 1, length(`text`)) where `table` = 'gallery';

update shouts set title = substring('Group comment title without meaning', 1, length(title)), `text` = substring('Some comment about a group someone left here. Might have been a rather long text as well.', 1, length(`text`)) where `table` = 'groups';

update shouts set title = substring('Picture comment title without meaning', 1, length(title)), `text` = substring('Some comment about a picture someone left here. Might have been a rather long text as well.', 1, length(`text`)) where `table` = 'gallery_items';

update trip_data set trip_name = substring('This is a rather stupid blog title', 1, length(trip_name)), trip_text = '', trip_descr = Substring('<p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Integer ac magna. Ut ultricies cursus magna. Aliquam metus ligula, aliquam sed, adipiscing ut, vulputate eu, dui. Nulla facilisi. Etiam id arcu. Quisque feugiat. Pellentesque ultrices lacus non erat. Suspendisse condimentum adipiscing augue. Suspendisse aliquet pulvinar dolor. Vivamus sit amet arcu eu urna egestas eleifend. Cras sit amet mauris. Praesent ligula mauris, convallis at, blandit id, aliquet sed, ipsum. Phasellus pellentesque, justo at sagittis vehicula, libero eros consequat lacus, id convallis dolor mi sit amet odio. Etiam velit sapien, varius sed, semper sed, pellentesque ut, ipsum. Proin gravida dui in diam. Sed imperdiet augue vitae neque. Donec ultricies rhoncus quam. </p> <hr/><p>Maecenas nisl quam, pellentesque ut, vestibulum tincidunt, varius sit amet, eros. Nullam sit amet felis. Nullam ligula. Suspendisse pede. Mauris et turpis ut quam adipiscing faucibus. Suspendisse eu massa et dolor vestibulum tincidunt. Donec augue. Nunc magna. Morbi eu sem. Aenean aliquam, justo vel accumsan egestas, felis lorem tincidunt sapien, quis venenatis ipsum eros id ante. </p> <p>Nulla rhoncus ullamcorper mi. Nam ut magna et ante egestas hendrerit. In in ante. Ut massa erat, laoreet vel, rutrum eget, congue vitae, lectus. Vivamus porttitor, nulla sit amet sollicitudin blandit, mi augue varius velit, in ullamcorper leo nunc non erat. Donec ac eros id arcu congue tempor. Quisque molestie tortor in arcu. Donec libero nisi, elementum in, semper feugiat, dignissim vel, ante. Vestibulum iaculis lorem in libero. Proin ipsum massa, porttitor eget, tincidunt sed, varius eget, tortor. Nam vel tellus. Integer velit orci, convallis id, pharetra sed, congue sit amet, ante. Maecenas malesuada. Maecenas faucibus aliquet augue. Integer a mauris. Aliquam fringilla feugiat est. Vivamus sed risus. Cras vestibulum, dolor ac vestibulum vulputate, nisi mi tincidunt lorem, vitae suscipit magna sapien ac nulla. </p><p>Vivamus eget mi ut leo hendrerit rutrum. Etiam cursus nulla vel est. Phasellus fringilla. Ut congue, metus convallis cursus convallis, tellus magna pretium tortor, ut cursus metus arcu quis velit. Nulla consectetuer, est eu tincidunt condimentum, elit lacus viverra neque, at vestibulum augue metus vel massa. In iaculis lacus vitae lectus. Vivamus turpis. Mauris nec felis eget pede lobortis tempor. Suspendisse eu pede. Fusce tempus nonummy risus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed volutpat fermentum ipsum. Nullam nisl ligula, accumsan congue, ullamcorper ut, venenatis sed, dolor. Maecenas id nunc. Etiam leo lorem, dapibus sit amet, tristique quis, dictum id, turpis. Duis nunc. Nunc egestas.  </p><p>Curabitur nec ipsum in odio varius cursus. Phasellus feugiat volutpat est. Curabitur semper. Duis tincidunt, enim eget tristique bibendum, dolor dolor viverra tortor, sed malesuada tellus neque eu neque. Donec lacus metus, mattis ut, fringilla in, sodales ac, velit. Maecenas est. Proin sagittis lacus sit amet turpis. Maecenas sit amet magna. Sed mattis tellus a orci. Donec at lacus id felis dignissim viverra. Nullam vehicula. In sed lectus ut velit varius lobortis. Quisque blandit metus a ligula condimentum venenatis. Fusce lorem elit, lobortis eu, feugiat ac, dignissim blandit, magna. Cras dapibus purus eget orci. Mauris commodo. Phasellus euismod mi eget sem. </p>', 1, length(trip_descr));

UPDATE `volunteer_boards` SET `TextContent`='';

update cryptedfields set AdminCryptedValue = '2', MemberCryptedValue = '2', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.HouseNumber' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'Sample street', MemberCryptedValue = 'Sample street', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.StreetName' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = '90762', MemberCryptedValue = '90762', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.zip' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'First', MemberCryptedValue = 'First', temporary_uncrypted_buffer = '' where tableColumn = 'members.FirstName' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'Second', MemberCryptedValue = 'Second', temporary_uncrypted_buffer = '' where tableColumn = 'members.SecondName' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'Last', MemberCryptedValue = 'Last', temporary_uncrypted_buffer = '' where tableColumn = 'members.LastName' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'someone@test.bewelcome.org', MemberCryptedValue = 'someone@test.bewelcome.org', temporary_uncrypted_buffer = '' where tableColumn = 'members.Email' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = '001-234-567890', MemberCryptedValue = '001-234-567890', temporary_uncrypted_buffer = '' where tableColumn = 'members.HomePhoneNumber' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = '001-234-567890', MemberCryptedValue = '001-234-567890', temporary_uncrypted_buffer = '' where tableColumn = 'members.CellPhoneNumber' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_SKYPE' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_MSN' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_ICQ' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_YAHOO' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_Others' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = '001-2345-678901', MemberCryptedValue = '001-2345-678901', temporary_uncrypted_buffer = '' where tableColumn = 'members.WorkPhoneNumber' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'NotSet' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_AOL' AND IsCrypted = 'not crypted';
update cryptedfields set AdminCryptedValue = 'no_one', MemberCryptedValue = 'no_one', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_GOOGLE' AND IsCrypted = 'not crypted';

update cryptedfields set AdminCryptedValue = '<admincrypted>2</admincrypted>', MemberCryptedValue = '<membercrypted>2</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.HouseNumber' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>Sample street</admincrypted>', MemberCryptedValue = '<membercrypted>Sample street</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.StreetName' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>90762</admincrypted>', MemberCryptedValue = '<membercrypted>90762</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'addresses.zip' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>First</admincrypted>', MemberCryptedValue = '<membercrypted>First</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.FirstName' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>Second</admincrypted>', MemberCryptedValue = '<membercrypted>Second</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.SecondName' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>Last</admincrypted>', MemberCryptedValue = '<membercrypted>Last</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.LastName' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>someone@test.bewelcome.org</admincrypted>', MemberCryptedValue = '<membercrypted>someone@test.bewelcome.org</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.Email' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>001-234-567890</admincrypted>', MemberCryptedValue = '<membercrypted>001-234-567890</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.HomePhoneNumber' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>001-234-567890</admincrypted>', MemberCryptedValue = '<membercrypted>001-234-567890</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.CellPhoneNumber' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_SKYPE' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_MSN' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_ICQ' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_YAHOO' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_Others' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>001-2345-678901</admincrypted>', MemberCryptedValue = '<membercrypted>001-2345-678901</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.WorkPhoneNumber' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'NotSet' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_AOL' AND IsCrypted = 'crypted';
update cryptedfields set AdminCryptedValue = '<admincrypted>no_one</admincrypted>', MemberCryptedValue = '<membercrypted>no_one</membercrypted>', temporary_uncrypted_buffer = '' where tableColumn = 'members.chat_GOOGLE' AND IsCrypted = 'crypted';

UPDATE cryptedfields
SET AdminCryptedValue = '<admincrypted>someone@test.bewelcome.org</admincrypted>',
  MemberCryptedValue = '<membercrypted>someone@test.bewelcome.org</membercrypted>', temporary_uncrypted_buffer = ''
WHERE tableColumn = 'members.Email' AND IsCrypted = 'always';

update memberstrads set Sentence = 'Occupation' where TableColumn = 'members.Occupation';
update memberstrads set Sentence = 'This is the profile summary (now about me)' where TableColumn = 'members.ProfileSummary';
update memberstrads set Sentence = 'A photo comment' where TableColumn = 'membersphotos.Comment';
update memberstrads set Sentence = 'Some additional info just in case' where TableColumn = 'members.AdditionalAccomodationInfo';
update memberstrads set Sentence = 'BeVolunteer' where TableColumn = 'members.Organizations';
update memberstrads set Sentence = 'a group members comment' where TableColumn = 'membersgroups.Comment';
update memberstrads set Sentence = 'Because I can' where TableColumn = 'members.MotivationForHospitality';
update memberstrads set Sentence = 'Someone special' where TableColumn = 'members.ILiveWith';
update memberstrads set Sentence = '3 days' where TableColumn = 'members.MaxLenghtOfStay';
update memberstrads set Sentence = 'Some more restrictions apply.' where TableColumn = 'members.OtherRestrictions';
update memberstrads set Sentence = 'I comment here' where TableColumn = 'specialrelations.Comment';
update memberstrads set Sentence = 'BeWelcome' where TableColumn = 'members.Hobbies';
update memberstrads set Sentence = 'Little Brother' where TableColumn = 'members.Books';
update memberstrads set Sentence = 'Tom Waits (for you?)' where TableColumn = 'members.Music';
update memberstrads set Sentence = 'Went to the grocery store' where TableColumn = 'members.PastTrips';
update memberstrads set Sentence = 'I stay at home.' where TableColumn = 'members.PlannedTrips';
update memberstrads set Sentence = 'a towel' where TableColumn = 'members.PleaseBring';
update memberstrads set Sentence = 'Good beer, sweet music and hilarious conversation' where TableColumn = 'members.OfferGuests';
update memberstrads set Sentence = 'I offer the world' where TableColumn = 'members.OfferHosts';
update memberstrads set Sentence = 'Some way to get to my place' where TableColumn = 'members.PublicTransport';
update memberstrads set Sentence = 'I don\'t like reggae, but movies' where TableColumn = 'members.Movies';
update memberstrads set Sentence = 'A group description' where TableColumn = 'groups.IdDescription';
update memberstrads set Sentence = '90762' where TableColumn = 'addresses.zip';
update memberstrads set Sentence = 'Sample street' where TableColumn = 'addresses.StreetName';
update memberstrads set Sentence = '2' where TableColumn = 'addresses.HouseNumber';
update memberstrads set Sentence = 'NotSet' where TableColumn = 'NotSet';
update memberstrads set Sentence = 'Special relation' where TableColumn = 'specialrelations.IdComment';

ALTER TABLE `translations` CHANGE COLUMN `updated` `updated` TIMESTAMP NULL DEFAULT NULL AFTER `IdTranslator`;
update translations set Sentence = concat('tag ', IdTrad) where TableColumn = 'forums_tags.IdName';
update translations set Sentence = Substring('<p> Lorem <b>ipsum dolor</b> sit amet, <u>consectetuer adipiscing elit</u>. <ul><li>Integer ac magna. Ut ultricies cursus magna.</li><li>Aliquam metus ligula, aliquam sed, adipiscing ut, vulputate eu, dui.</li></ul></p><p>Nulla facilisi. Etiam id arcu. Quisque feugiat. Pellentesque ultrices lacus non erat. Suspendisse condimentum adipiscing augue. Suspendisse aliquet pulvinar dolor. Vivamus sit amet arcu eu urna egestas eleifend. Cras sit amet mauris. Praesent ligula mauris, convallis at, blandit id, aliquet sed, ipsum. Phasellus pellentesque, justo at sagittis vehicula, libero eros consequat lacus, id convallis dolor mi sit amet odio. Etiam velit sapien, varius sed, semper sed, pellentesque ut, ipsum. Proin gravida dui in diam. Sed imperdiet augue vitae neque. Donec ultricies rhoncus quam. </p> <hr/><p>Maecenas nisl quam, pellentesque ut, vestibulum tincidunt, varius sit amet, eros. Nullam sit amet felis. Nullam ligula. Suspendisse pede. Mauris et turpis ut quam adipiscing faucibus. Suspendisse eu massa et dolor vestibulum tincidunt. Donec augue. Nunc magna. Morbi eu sem. Aenean aliquam, justo vel accumsan egestas, felis lorem tincidunt sapien, quis venenatis ipsum eros id ante. </p> <p>Nulla rhoncus ullamcorper mi. Nam ut magna et ante egestas hendrerit. In in ante. Ut massa erat, laoreet vel, rutrum eget, congue vitae, lectus. Vivamus porttitor, nulla sit amet sollicitudin blandit, mi augue varius velit, in ullamcorper leo nunc non erat. Donec ac eros id arcu congue tempor. Quisque molestie tortor in arcu. Donec libero nisi, elementum in, semper feugiat, dignissim vel, ante. Vestibulum iaculis lorem in libero. Proin ipsum massa, porttitor eget, tincidunt sed, varius eget, tortor. Nam vel tellus. Integer velit orci, convallis id, pharetra sed, congue sit amet, ante. Maecenas malesuada. Maecenas faucibus aliquet augue. Integer a mauris. Aliquam fringilla feugiat est. Vivamus sed risus. Cras vestibulum, dolor ac vestibulum vulputate, nisi mi tincidunt lorem, vitae suscipit magna sapien ac nulla. </p><p>Vivamus eget mi ut leo hendrerit rutrum. Etiam cursus nulla vel est. Phasellus fringilla. Ut congue, metus convallis cursus convallis, tellus magna pretium tortor, ut cursus metus arcu quis velit. Nulla consectetuer, est eu tincidunt condimentum, elit lacus viverra neque, at vestibulum augue metus vel massa. In iaculis lacus vitae lectus. Vivamus turpis. Mauris nec felis eget pede lobortis tempor. Suspendisse eu pede. Fusce tempus nonummy risus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed volutpat fermentum ipsum. Nullam nisl ligula, accumsan congue, ullamcorper ut, venenatis sed, dolor. Maecenas id nunc. Etiam leo lorem, dapibus sit amet, tristique quis, dictum id, turpis. Duis nunc. Nunc egestas.  </p><p>Curabitur nec ipsum in odio varius cursus. Phasellus feugiat volutpat est. Curabitur semper. Duis tincidunt, enim eget tristique bibendum, dolor dolor viverra tortor, sed malesuada tellus neque eu neque. Donec lacus metus, mattis ut, fringilla in, sodales ac, velit. Maecenas est. Proin sagittis lacus sit amet turpis. Maecenas sit amet magna. Sed mattis tellus a orci. Donec at lacus id felis dignissim viverra. Nullam vehicula. In sed lectus ut velit varius lobortis. Quisque blandit metus a ligula condimentum venenatis. Fusce lorem elit, lobortis eu, feugiat ac, dignissim blandit, magna. Cras dapibus purus eget orci. Mauris commodo. Phasellus euismod mi eget sem. </p>', 1, length(Sentence)) where TableColumn = 'forums_posts.IdContent';
update translations set Sentence = 'Tag description' where TableColumn = 'forums_tags.IdDescription';
update translations set Sentence = 'chat_rooms.RoomTitle' where TableColumn = 'chat_rooms.RoomTitle';
update translations set Sentence = 'chat_rooms.RoomDescription' where TableColumn = 'chat_rooms.RoomDescription';
update translations set Sentence = 'Title of this poll' where TableColumn = 'polls.Title';
update translations set Sentence = 'The desciption of the poll' where TableColumn = 'polls.Description';
update translations set Sentence = concat('Choice no ', IdTrad) where TableColumn = 'polls_choices.IdChoiceText';
update translations set Sentence = 'localvolmessages.IdTitleText' where TableColumn = 'localvolmessages.IdTitleText';
update translations set Sentence = 'localvolmessages.IdMessageText' where TableColumn = 'localvolmessages.IdMessageText';
update translations set Sentence = 'NotSet' where TableColumn = 'NotSet';
update translations set Sentence = Substring('A rather pointless forum thread title', 1, length(Sentence))  where TableColumn = 'forums_threads.title';


update translations 
left join forums_threads on translations.id = forums_threads.IdTitle
set
    Sentence = (Substring(CONCAT(CONCAT('(', forums_threads.id), ') A rather pointless forum thread title'), 1, length(Sentence))) where TableColumn = 'forums_threads.IdTitle';


ALTER TABLE `translations` CHANGE COLUMN `updated` `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE
CURRENT_TIMESTAMP COMMENT 'when the record was updated';

/* **************************
/* ***    geonames   ********
/* **************************


/* reducing geonames */

    DELETE FROM geonames
    WHERE NOT (fcode='adm1'
            OR fcode LIKE 'pcl%'
            OR population>100000
            OR geonameid IN (
                SELECT DISTINCT(idcity)
                FROM members
            )
    );
    
    DELETE FROM geonames
    WHERE fcode LIKE 'adm%'
      AND NOT fcode LIKE 'ADM1%'
      AND geonameid NOT IN (
        SELECT DISTINCT(idcity)
        FROM members
      );


/* reducing alternatenames */

    TRUNCATE TABLE geonames_alternate_names;

    INSERT INTO geonames_alternate_names (
        alternateNameId,
        geonameId,
        isoLanguage,
        alternateName,
        ispreferredName,
        isshortName
    )
    SELECT alternatenameId,
        geonameid,
        isolanguage,
        alternatename,
        ispreferred,
        isshort
    FROM geonamesalternatenames
    WHERE geonameid IN (SELECT geonameid FROM geonames);

    DELETE FROM geonamesalternatenames 
    WHERE geonameid NOT IN ( 
        SELECT geonameid FROM geonames);

/* reducing adminunits */
    DELETE FROM geonamesadminunits
    WHERE geonameid NOT IN (SELECT geonameid FROM geonames);


/* **************************
/* ***    privacy    ********
/* **************************

/* Shuffle birthday */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), orbday int(11), orbmonth int(11), orbyear int(11), orbdate date);
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids));
INSERT INTO shuffle1 (id, orbday,orbmonth,orbyear,orbdate) SELECT NULL, bday,bmonth,byear,birthdate FROM members ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM members;
UPDATE members SET bday = (SELECT shuffle1.orbday FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);
UPDATE members SET bmonth = (SELECT shuffle1.orbmonth FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);
UPDATE members SET byear = (SELECT shuffle1.orbyear FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);
UPDATE members SET birthdate = (SELECT shuffle1.orbdate FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);

/* Shuffle gender,hidegender */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), orgender varchar(255), orhidegender varchar(255));
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids));
INSERT INTO shuffle1 (id, orgender,orhidegender) SELECT NULL, gender,hidegender FROM members ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM members;
UPDATE members SET gender = (SELECT shuffle1.orgender FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);
UPDATE members SET hidegender = (SELECT shuffle1.orhidegender FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);
    
/* Shuffle sender of messages (todo: avoid messages being sent before senders membership)*/
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_values int(11), key original_values(original_values) );
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids) );
INSERT INTO shuffle1 (id, original_values) SELECT NULL, IdSender FROM messages ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM messages;
UPDATE messages SET IdSender = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE messages.id = shuffle2.original_ids);

/* Shuffle commentqualifications and securityactions */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), orquality varchar(99), oradminaction varchar(99), ordisplayinpublic tinyint(1), orallowedit tinyint(1));
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids));
INSERT INTO shuffle1 (id, orquality,oradminaction,ordisplayinpublic,orallowedit) SELECT NULL, quality,adminaction,displayinpublic,allowedit FROM comments ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM comments;
UPDATE comments SET quality = (SELECT shuffle1.orquality FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.id = shuffle2.original_ids);
UPDATE comments SET adminaction = (SELECT shuffle1.oradminaction FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.id = shuffle2.original_ids);
UPDATE comments SET displayinpublic = (SELECT shuffle1.ordisplayinpublic FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.id = shuffle2.original_ids);
UPDATE comments SET allowedit = (SELECT shuffle1.orallowedit FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.id = shuffle2.original_ids);

/* Shuffle sender/receiver of comments (tricky to maintain pairs!, todo: see messages) */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_values int(11), key original_values(original_values) );
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids) );
INSERT INTO shuffle1 (id, original_values) select null,idfrommember from comments union select null,idtomember from comments ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) select null,idfrommember from comments union select null,idtomember from comments;
UPDATE comments SET idfrommember = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.idfrommember = shuffle2.original_ids);
UPDATE comments SET idtomember = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE comments.idtomember = shuffle2.original_ids);

/* Shuffle specialrelations (tricky to maintain pairs!, todo: see messages) */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_values int(11), key original_values(original_values) );
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids) );
INSERT INTO shuffle1 (id, original_values) select null,idowner from specialrelations union select null,idrelation from specialrelations ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) select null,idowner from specialrelations union select null,idrelation from specialrelations;
ALTER TABLE `specialrelations` DROP INDEX `UniqueRelation`;
UPDATE specialrelations SET idowner = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE specialrelations.idowner = shuffle2.original_ids);
UPDATE specialrelations SET idrelation = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE specialrelations.idrelation = shuffle2.original_ids);
ALTER TABLE `specialrelations` ADD UNIQUE INDEX `UniqueRelation` (`IdOwner`, `IdRelation`);

/* Shuffle logcount */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_values int(11), key original_values(original_values) );
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids) );
INSERT INTO shuffle1 (id, original_values) SELECT NULL, logcount FROM members ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM members;
UPDATE members SET logcount = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids);

/* Shuffle suggestionvotings */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_values int(11), key original_values(original_values) );
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids) );
INSERT INTO shuffle1 (id, original_values) SELECT NULL, rank FROM suggestions_votes ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM suggestions_votes;
UPDATE suggestions_votes SET rank = (SELECT shuffle1.original_values FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE suggestions_votes.id = shuffle2.original_ids);

/* Randomise suggestion ranks */
UPDATE suggestions_option_ranks SET vote = IF(RAND()<0.4,-1,1);

/* Shuffle donations */
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), orid int(11));
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids));
INSERT INTO shuffle1 (id, orid) SELECT NULL, id FROM members ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id FROM members;
UPDATE donations SET idmember = (SELECT shuffle1.orid FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE donations.idmember = shuffle2.original_ids) WHERE donations.idmember>0;

/* Anonymize ewiki authors and remove ip addresses */
UPDATE ewiki SET author = concat('member-',mod(lastmodified,10000)+1);

/***********************
 ** Reset Timestamps  **
 ***********************/

/* Reset timestamps to midnight for all timestampcolumns */

DROP PROCEDURE IF EXISTS `striptime`; 

DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE striptime() DETERMINISTIC

BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE tab, col TEXT;
DECLARE collist CURSOR FOR select table_name,column_name
                        from information_schema.columns
                        where table_schema = DATABASE() and (data_type = 'datetime' or data_type = 'timestamp');
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
OPEN collist;
read_loop: LOOP
  FETCH collist INTO tab, col;
    IF done THEN
      LEAVE read_loop;
    END IF;

  SET @sqltxt = concat('UPDATE `',tab,'` SET `',col,'` = TIMESTAMP(DATE(`',col,'`))');
  prepare stmt from @sqltxt;
  execute stmt;
END LOOP read_loop;
CLOSE collist;
END ;;
DELIMITER ;

call striptime();
DROP PROCEDURE `striptime`;

/***********************
 ** Shuffle memberids **
 ***********************/
ALTER TABLE `members` ADD COLUMN `id2` INT(11) NOT NULL DEFAULT '0' AFTER `id`;
update members set id2 = id;
update members set Username = concat('temp-',id) WHERE id>1 AND id <> 62196;
DROP TEMPORARY TABLE IF EXISTS shuffle1;
DROP TEMPORARY TABLE IF EXISTS shuffle2;
CREATE TEMPORARY TABLE shuffle1 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), orid int(11), orcrea timestamp);
CREATE TEMPORARY TABLE shuffle2 (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), original_ids int(11), key original_ids(original_ids));
INSERT INTO shuffle1 (id, orid, orcrea) SELECT NULL, id2, created FROM members WHERE id2>1 ORDER BY rand();
INSERT INTO shuffle2 (id, original_ids) SELECT NULL, id2 FROM members WHERE id2>1;

/* these variables can be relatively easy shuffled */
/* take addresses and cryptedfields as well, to avoid dereferenced fields */
UPDATE members SET id2 = (SELECT shuffle1.orid FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids) WHERE members.id>1 AND id <> members.62196;
UPDATE members SET created = (SELECT shuffle1.orcrea FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE members.id = shuffle2.original_ids) WHERE members.id>1 AND id <> members.62196;
UPDATE addresses SET IdMember = (SELECT shuffle1.orid FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE addresses.IdMember = shuffle2.original_ids) WHERE addresses.idMember > 1 AND id <> addresses.62196;
UPDATE cryptedfields SET IdMember = (SELECT shuffle1.orid FROM shuffle1 JOIN shuffle2 ON shuffle2.id = shuffle1.id WHERE cryptedfields.IdMember = shuffle2.original_ids) WHERE cryptedfields.IdMember>1 AND id <> cryptedfields.62196;

/* before really resetting the members.id, drop all the foreign keys */
ALTER TABLE `polls` DROP FOREIGN KEY `polls_ibfk_1`;
ALTER TABLE `polls_contributions` DROP FOREIGN KEY `polls_contributions_ibfk_1`;
ALTER TABLE `polls_record_of_choices` DROP FOREIGN KEY `polls_record_of_choices_ibfk_1`;
ALTER TABLE `comments` DROP FOREIGN KEY `comments_ibfk_1`;
ALTER TABLE `comments` DROP FOREIGN KEY `comments_ibfk_2`;
ALTER TABLE `comments` DROP FOREIGN KEY `comments_ibfk_3`;

/* to avoid double ids during the process, set first all ids to values higher than max, then set it really */
SET @maxid = (SELECT MAX(id) FROM members) + 1;
UPDATE members SET id = id + @maxid WHERE id>1;
UPDATE members SET id = id2 WHERE id>1;

/* add all the foreign keys again */
ALTER TABLE `polls` ADD CONSTRAINT `polls_ibfk_1` FOREIGN KEY (`IdCreator`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
ALTER TABLE `polls_contributions` ADD CONSTRAINT `polls_contributions_ibfk_1` FOREIGN KEY (`IdMember`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
ALTER TABLE `polls_record_of_choices` ADD CONSTRAINT `polls_record_of_choices_ibfk_1` FOREIGN KEY (`IdMember`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
ALTER TABLE `comments` ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`IdFromMember`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
ALTER TABLE `comments` ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`IdFromMember`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
ALTER TABLE `comments` ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`IdToMember`) REFERENCES `members` (`id`) ON UPDATE RESTRICT;
/* restore the usernames */
UPDATE members SET Username = concat('member-',id) WHERE id>1 AND ID <> 62196;
/* cleanup */
ALTER TABLE `members` DROP COLUMN `id2`;

/******************************************
 *** Define standard user               ***
 ******************************************/
UPDATE members SET Username = 'admin' WHERE id = 1;
UPDATE user SET handle = 'admin' WHERE handle = 'member-1';
UPDATE members SET username = 'SuggestionsTeam' WHERE id = 62196;
UPDATE user SET handle = 'SuggestionsTeam' where handle = 'member-62196';

/******************************************
 *** Reset password                     ***
 ******************************************/
UPDATE members SET password = PASSWORD("password");
UPDATE user SET pw = '';

/* more cleanup */
/* more cleanup */
DROP TABLE `alternatenames`;
DROP TABLE `bm`;
ALTER TABLE `cal_event_to_tag` COMMENT 'OBSOLETE';
ALTER TABLE `cal_eventdata` COMMENT 'OBSOLETE';
ALTER TABLE `cal_events` COMMENT 'OBSOLETE';
ALTER TABLE `cal_tags` COMMENT 'OBSOLETE';
ALTER TABLE `chat_messages` COMMENT 'OBSOLETE';
ALTER TABLE `chat_room_moderators` COMMENT 'OBSOLETE';
ALTER TABLE `chat_rooms` COMMENT 'OBSOLETE';
ALTER TABLE `chat_rooms_members` COMMENT 'OBSOLETE';
ALTER TABLE `comments_ofthemomment_votes` COMMENT 'OBSOLETE';
DROP TABLE `copy_translation`;
ALTER TABLE `counters_cities_nbmembers` COMMENT 'OBSOLETE';
ALTER TABLE `counters_regions_nbcities` COMMENT 'OBSOLETE';
ALTER TABLE `counters_regions_nbmembers` COMMENT 'OBSOLETE';
ALTER TABLE `countries` COMMENT 'OBSOLETE';
ALTER TABLE `forums_posts_votes` COMMENT 'DEPRECATED';
ALTER TABLE `geo_hierarchy` COMMENT 'DEPRECATED';
ALTER TABLE `geo_location` COMMENT 'OBSOLETE';
ALTER TABLE `geo_type` COMMENT 'OBSOLETE';
ALTER TABLE `geo_usage` COMMENT 'OBSOLETE';
DROP TABLE `geo_usage_before_jyh_touch_it`;
ALTER TABLE `geonames_admincodes` COMMENT 'DEPRECATED';
ALTER TABLE `geonames_alternate_names` COMMENT 'DEPRECATED';
ALTER TABLE `geonames_cache` COMMENT 'DEPRECATED';
ALTER TABLE `geonames_cache_backup` COMMENT 'DEPRECATED';
ALTER TABLE `geonames_countries` COMMENT 'DEPRECATED';
ALTER TABLE `geonames_timezones` COMMENT 'DEPRECATED';
ALTER TABLE `groups_locations` COMMENT 'OBSOLETE';
ALTER TABLE `groupshierarchy` COMMENT 'OBSOLETE';
ALTER TABLE `guestsonline` COMMENT 'OBSOLETE';
ALTER TABLE `intermembertranslations` COMMENT 'DEPRECATED';
ALTER TABLE `linklist` COMMENT 'DEPRECATED (no longer updated)';
ALTER TABLE `localvolmessages` COMMENT 'OBSOLETE';
ALTER TABLE `localvolmessages_location` COMMENT 'OBSOLETE';
ALTER TABLE `members_updating_status` COMMENT 'OBSOLETE';
ALTER TABLE `memberscounters` COMMENT 'OBSOLETE';
DROP TABLE `message`;
DROP TABLE `messages_copy`;
ALTER TABLE `mod_user_apps` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_apps_seq` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_auth` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_auth_seq` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_authgroups` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_authrights` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_groupauth` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_grouprights` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_implications` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_rights` COMMENT 'DEPRECATED';
ALTER TABLE `mod_user_rights_seq` COMMENT 'DEPRECATED';
DROP TABLE `oldvisits`;
ALTER TABLE `online` COMMENT 'DEPRECATED';
ALTER TABLE `pendingmandatory` COMMENT 'OBSOLETE';
ALTER TABLE `recentvisits` COMMENT 'DEPRECATED';
ALTER TABLE `recorded_usernames_of_left_members` COMMENT 'DEPRECATED';
ALTER TABLE `regions_count` COMMENT 'OBSOLETE';
ALTER TABLE `sqlforgroupsmembers` COMMENT 'DEPRECATED';
DROP TABLE `t_countries`;
TRUNCATE `timezone`;
DROP TABLE `tmp_test`;
DROP TABLE `translations_copy7`;
ALTER TABLE `urlheader_languages` COMMENT 'DEPRECATED';
ALTER TABLE `user_friends` COMMENT 'DEPRECATED';
ALTER TABLE `user_inbox` COMMENT 'DEPRECATED';
ALTER TABLE `user_outbox` COMMENT 'DEPRECATED';
ALTER TABLE `user_settings` COMMENT 'DEPRECATED';
ALTER TABLE `verifiedmembers` COMMENT 'DEPRECATED';
ALTER TABLE `volunteer_boards` COMMENT 'OBSOLETE';
ALTER TABLE `volunteers_reports_schedule` COMMENT 'OBSOLETE';
DROP TABLE `words_copy`;
DROP TABLE `words_original`;

/* Get rid of long tables */
TRUNCATE `broadcastmessages`;
TRUNCATE `logs`;
TRUNCATE `posts_notificationqueue`;

/* Truncate obsolete tables */
TRUNCATE members_sessions;
TRUNCATE members_updating_status;
TRUNCATE `online`;
TRUNCATE pendingmandatory;
TRUNCATE tantable;
TRUNCATE verifiedmembers;

/* Truncate feedbacks (for privacy reasons) */
TRUNCATE feedbacks;
