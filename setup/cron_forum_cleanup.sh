#!/bin/bash
cat <<EOF | mysql BW_MAIN 
/*
This script is run each 30 minutes by a cron and results in closing edit possibility for the post which have been already repiled to
*/


update  forums_posts , forums_threads
set OwnerCanStillEdit='No'
WHERE forums_threads.id = forums_posts.threadid
AND last_postid != forums_posts.id
AND OwnerCanStillEdit = 'Yes' and forums_posts.create_time<date_sub(now(),interval 30 minute);

set @result=concat('Updated rows=',row_count()) ;
insert into BW_ARCH.logs(IdMember,Str,Type,created,IpAddress) values(1,concat('Disabling Edit for forums post older than 30 minutes which have been replied, ',@result),'cron_task',now(),2130706433) ;
EOF
