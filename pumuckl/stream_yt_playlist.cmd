@Echo off
REM Streams a YouTube Link, an ID, or even a Playlist to http://localhost:8080/gogo.ts 
REM removed dependency to "youtube-dl" and "ffmpeg"
REM Oct 2018, Marcedo@habMalNeFrage.de
REM .Engeneered to survive BlackHoles :)

REM Choose to use user defined Terminal colours
:: call :ColouredTerminal

chcp 65001 1>NUL
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
TASKKILL  /IM  VLC.exe 1>NUL 2>NUL

IF ["%Link%"] neq [""]  ECHO ...Fetching Playlist Urls to yt_playlist.lst... 
IF ["%Link%"] neq [""]  %php_bin% yt_get_playlist_apikey.php "%Link%" > yt_playlist.txt
IF ["%Link%"] neq [""]  if ["%ERRORLEVEL%"] EQU ["101"] (goto err_api_key)
IF ["%Link%"] neq [""]  if ["%ERRORLEVEL%"] GTR ["101"] (goto err_api)

if NOT EXIST yt_playlist.txt (goto err_playlist)
for /f "tokens=2 delims=:" %%i in ('find /V /C "" yt_playlist.txt') do echo  ..Found%%i Items.

:: OBS-Playlist Bug. Should be fixed in libvlc3.03 
:: WorkAround - route the stream via localhost  
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
REM # Quality : 18/mp4/360 (standard Formats No 17/18/36). Extend yt_get_protID.php if you need the Data in another Format.
for /f %%i in ('%php_bin% yt_get_protID.php %youtube_id%') do set Link=%%i
	REM  echo "%Link% : %youtube_id%"
	set skipper=0
	if ["ERR:102"] equ ["%Link%"]  (echo ..API Error - encryption failed - Skipping %youtube_id%  && set skipper=1)
	if ["ERR:150"] equ ["%Link%"]  (echo ..Skipping GeoBlocked %youtube_id% && set skipper=1)
	if ["ERR:400"] equ ["%Link%"]  (echo ..API Error - bad request - Skipping %youtube_id%  && set skipper=1)
	if ["ERR:401"] equ ["%Link%"]  (echo ..API Error - unauthorized - Skipping %youtube_id%  && set skipper=1)
	if ["ERR:403"] equ ["%Link%"]  (echo ..API Error - forbidden - Skipping %youtube_id%  && set skipper=1)
	if ["ERR:404"] equ ["%Link%"]  (echo ..API Error - not found - Skipping %youtube_id%  && set skipper=1)
	if [0] equ [%skipper%] (start /B /WAIT /MIN /ABOVENORMAL vlc.exe "%Link%" --verbose=1 --file-logging --logfile=vlc.log --sout=#duplicate{dst=std{access=file,mux=ts,dst=stream.ts},dst=display} vlc://quit)
	exit /b 0
:err_api_key
echo ... yt_get_playlist_apikey.php:
echo ... Please get a free Youtube apikey first.
echo ... see https://developers.google.com/youtube/v3/getting-started
echo ... stop ...
pause
exit

:err_api
echo ...Error connecting to youtubes playlist API. Exitcode: %ERRORLEVEL% 
pause
exit

:err_playlist
echo ...Error creating playlist Exitcode: %ERRORLEVEL% 
pause
exit

:ColouredTerminal
REM Change current Batchfiles Visuals
REM echo "Switching to coloured Terminal"
reg query HKCU\Console\TinyTonCMD /v runs 1>NUL 2>NUL
if %ERRORLEVEL% EQU 1  (
	echo Windows Registry Editor Version 5.00 >>term.reg
	echo [-HKEY_CURRENT_USER\Console\TinyTonCMD] >>term.reg
	echo [HKEY_CURRENT_USER\Console\TinyTonCMD] >>term.reg
	echo "runs"=dword:00000000 >>term.reg
	echo "FontSize"=dword:000d0000 >>term.reg
	echo "ScreenColors"=dword:0000000a >>term.reg
	echo "WindowAlpha"=dword:000000e7 >>term.reg
	echo "ScreenBufferSize"=dword:07d00080 >>term.reg
	reg import term.reg 1>NUL
	del /f term.reg 1>NUL
	REM reg ADD HKCU\Console\TinyTonCMD /v runs /t REG_DWORD /d 1 1>NUL
	start "TinyTonCMD" %~nx0
EXIT
) else (
	reg delete HKCU\Console\TinyTonCMD\ /v runs /f 1>NUL
)

:ende
