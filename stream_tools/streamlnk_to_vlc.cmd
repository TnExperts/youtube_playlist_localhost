@echo off
::REM https://www.codeproject.com/Tips/123810/Get-user-input-from-DOS-prompt
::REM https://github.com/streamlink/streamlink
:: Open Live Stream with VLC (YouNow, Twitch etc)
SET /P Link=Stream Quelle ? :  
IF ["%Link%"] neq [""] streamlink --stream-url %Link% worst
IF ["%Link%"] neq [""] streamlink %Link% worst
pause
