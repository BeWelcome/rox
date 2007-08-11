ALTER TABLE `geonames_admincodes` DROP FOREIGN KEY `geonames_admincodes_ibfk_1`;
ALTER TABLE `geonames_cache` DROP FOREIGN KEY `geonames_cache_ibfk_1`;
ALTER TABLE `geonames_cache` DROP FOREIGN KEY `geonames_cache_ibfk_2`;
ALTER TABLE `forums_threads` DROP FOREIGN KEY `forums_threads_ibfk_11`;
ALTER TABLE `forums_threads` DROP FOREIGN KEY `forums_threads_ibfk_12`;
DROP TABLE IF EXISTS 
`blog_comments` ,
`blog_data` ,
`blog_seq` ,
`blog_tags_seq` ,
`blog_to_category` ,
`blog_to_tag` ,
`cal_eventdata` ,
`cal_event_to_tag` ,
`cal_tags` ,
`gallery_items_seq` ,
`gallery_items_to_gallery` ,
`mod_user_apps_seq` ,
`mod_user_authrights` ,
`mod_user_auth_seq` ,
`mod_user_groupauth` ,
`mod_user_grouprights` ,
`mod_user_implications` ,
`mod_user_rights` ,
`mod_user_rights_seq` ,
`trip_data` ,
`trip_to_gallery` ,
`user_friends` ,
`user_seq` ,
`user_settings`,
`forums_posts`,
`ewiki` ;
DROP TABLE IF EXISTS
`blog` ,
`blog_categories` ,
`blog_tags` ,
`cal_events` ,
`gallery` ,
`gallery_items` ,
`trip`,
`user_inbox`,
`user_outbox`,
`message`,
`forums_threads`;
DROP TABLE IF EXISTS
`user`,
`mod_user_authgroups`,
`mod_user_apps` ,
`mod_user_auth` ,
`geonames_cache`,
`forums_tags`;
DROP TABLE IF EXISTS
`geonames_admincodes`;
DROP TABLE IF EXISTS
`geonames_countries`;