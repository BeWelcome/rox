@ECHO OFF
GOTO _dos
echo This program requires DOS.
exit

:_dos
set PHP=PHP.EXE
IF EXIST C:\PHP\PHP-CGI.EXE  set PHP=C:\PHP\PHP-CGI.EXE
If EXIST C:\PHP\PHP.EXE      set PHP=C:\PHP\PHP.EXE
IF EXIST C:\PHP\PHP-CLI.EXE  set PHP=C:\PHP\PHP-CLI.EXE

%PHP%  ewikictl  %1 %2 %3 %4 %5 %6 %7 %8 %9

