/*
This script is run each 30 minutes by a cron and results in closing edit possibility for the post which have been already repiled to
*/


update  `forums_posts` , forums_threads
set OwnerCanStillEdit='No'
WHERE forums_threads.id = forums_posts.id
AND last_postid != forums_posts.id
AND `OwnerCanStillEdit` = 'Yes' and create_time<date_sub(now(),interval 30 minute) and  create_time>date_sub(now(),interval 90 minute)
  
/* proceed only post in the last hour */
;

insert into `BW_ARCH`.`logs`(`IdMember`,`Str`,`Type`,`created`,`IpAddress`) values(1,'Disabling Edit for forums post older than 30 minutes which have been replied','cron_task',now(),2130706433) ;