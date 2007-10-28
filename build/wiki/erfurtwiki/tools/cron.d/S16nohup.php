<?php
/*
   Ensure that execution can't be stopped from this point on, so there is
   no security risk leaving the whole cron.d/ script collection open to
   be activated by ANYONE.
*/

ignore_user_abort(true);
set_time_limit(+500);   // 6 minutes, should be injected into run-parts loop?!

?>