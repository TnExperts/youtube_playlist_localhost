@echo off
del /f yn_comments.txt
start /min /LOW ..\php\php yn_comments.php
exit