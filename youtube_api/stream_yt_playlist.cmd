@Echo off
REM Streams a YouTube Link, an ID, or even a Playlist to http://localhost:8080/gogo.ts 
REM removed dependency to "youtube-dl" and "ffmpeg"
REM Oct 2018, Marcedo@habMalNeFrage.de
REM .Engeneered to survive BlackHoles :)

chcp 65001 1>NUL
setlocal enabledelayedexpansion
set PATH=%PATH%;C:\Program Files\VideoLAN\VLC;C:\Program Files (x86)\VideoLAN\VLC
set php_bin=..\php\php.exe 
set link=%1
set windowTitle=_Playlist_Streamer_
TITLE %windowTitle%

where /Q vlc.exe
IF [%ERRORLEVEL%] gtr [0] (
echo Please install VLC first. -> http://www.videolan.org/
pause
exit
)

echo        --== Route a youtube htttp stream via localhost ==--
IF ["%link%"] equ [""] SET /P Link=YouTube Link , Playlist or ID ? (empty to use last generated list) : 
TASKKILL /F /IM  VLC.exe 1>NUL 2>NUL
TASKKILL /F /IM  php.exe 1>NUL 2>NUL

IF ["%Link%"] neq [""]  ECHO ...Fetching Playlist Urls to yt_playlist.lst... 
IF ["%Link%"] neq [""]  %php_bin% yt_get_playlist_apikey.php "%Link%" > yt_playlist.txt
IF ["%Link%"] neq [""]  if ["%ERRORLEVEL%"] GTR ["0"] (goto err_api)

if NOT EXIST yt_playlist.txt (goto err_playlist)
for /f "tokens=2 delims=:" %%i in ('find /V /C "" yt_playlist.txt') do echo  ..Found%%i Items.

:: WorkAround  for VLC Version 3.01 - Need to route the stream via localhost
::  OBS-Playlist Bug. Should be fixed in 3.03
:: todo -> Watchdog cleaning up when execution of the steering batchfile ends. 
ECHO  ..Init VLC-OBS Bridge via http://localhost:8080/gogo.ts 
start /MIN cmd.exe /c wd.cmd

::ECHO  ..Streaming YouNow Comments to yn-comments.txt
::start  younow\yn_comments.cmd

for /F "delims=; eol=# tokens=1,2*" %%e IN (yt_playlist.txt) do ( :: Iterate through downloaded playlist ::
	call set youtube_id=%%e
	ECHO  ..Stream https://www.youtube.com/watch?v=%%e %%f
	ECHO  ..Pumuckl: %%f > yt_title.txt
	call :sub_stream_file
)

del /F vlc_start.bat 1>NUL 2>NUL
del /F stream.ts 1>NUL 2>NUL
goto :ende

:sub_stream_file
REM # https://stackoverflow.com/questions/2323292/assign-output-of-a-program-to-a-variable
REM # yt_get_prot.php ::> https://gist.github.com/arjunae/6737ecf40956efa3fe4c4d3b45d99f2d
REM # Quality : mp4-640x360 (Format No 18). Change the php script if you need the Data in another Format.
for /f %%i in ('%php_bin% yt_get_protID.php %youtube_id%') do set Link=%%i
REM echo "%Link% : %youtube_id%"
start /WAIT /MIN /ABOVENORMAL vlc.exe "%Link%" --sout=#duplicate{dst=std{access=file,mux=ts,dst=stream.ts},dst=display} vlc://quit
exit /b 0

:err_api
echo ...Error connecting to youtube API. Exitcode: %ERRORLEVEL% 
pause
EXIT

:err_playlist
echo ...Error creating playlist Exitcode: %ERRORLEVEL% 
pause
exit

:ende
