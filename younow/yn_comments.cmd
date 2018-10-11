@echo off
chcp 65001 1>NUL
del /f yn_comments.txt
..\php\php.exe yn_comments.php
exit