* Replace controllers checking for IdMember session with more appropriate auth system flow.
* Add Eloquent castings for all models, in case servers do not use mysqlnd
* The language setting has not been ported from RoxFrontRouter::initUser() to RestoreRememberListener - it should
probably have its own listener.
* Add php debugbar with PDO query log
