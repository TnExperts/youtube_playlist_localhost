@echo off
::REM https://www.codeproject.com/Tips/123810/Get-user-input-from-DOS-prompt
::REM https://github.com/streamlink/streamlink
:: Replicate a (YouNow, Twitch etc) Live Stream on a localhost Port (Link will be displayed) 
::https://www.youtube.com/watch?v=qjqJk6jPCaU
SET /P Link=Stream Quelle ? : 
IF ["%Link%"] neq [""] streamlink --stream-url %Link% best
::IF ["%Link%"] neq [""] streamlink --player-external-http --player-external-http-port 8080 %Link% best
IF ["%Link%"] neq [""] streamlink --player-external-http %Link% best
pause
