@echo off
chcp 65001 1>NUL
..\php\php.exe  yt_get_playlist_apikey.php %1
::..\php7\php.exe yt_get_playlist_apikey.php %1
pause
exit