@echo off
chcp 65001 1>NUL
..\php\php.exe  yt_get_playlist_apikey.php RDg3ml_WCpbsg %1
::..\php7\php.exe yt_get_playlist_apikey.php %1
if ["%ERRORLEVEL%"] GTR ["0"] (echo ...Error fetching playlist.. Exitcode: %ERRORLEVEL%)


pause
exit