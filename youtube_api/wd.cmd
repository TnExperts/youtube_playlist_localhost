@echo off
REM = A funny BatchBased Process Watchdog.
REM = Ends %DEPEND% when %WATCH% ends.
REM = requires winVersion >= Vista 
REM = LIC BSD3CLause

set watch="_Playlist_Streamer_"
set depend="vlc_start.bat"

REM = Create the depend BatchJob
echo @echo off >vlc_start.bat
echo :wait  >> vlc_start.bat
echo if not EXIST stream.ts goto :wait >> vlc_start.bat
echo REM simple trick to just send a file as a ts stream - without further processing. >>vlc_start.bat
echo start /MIN vlc.exe -I dummy  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{dst=:8080/gogo.ts} >>vlc_start.bat
echo REM # Using FlashVideo can be seen as "more compatible" but also does requires more processing time.  >>vlc_start.bat
echo REM # start /MIN /ABOVENORMAL vlc.exe -I dummy --no-interact  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{mux=ffmpeg{mux=flv},dst=:8080/bla}  >>vlc_start.bat
echo exit >>vlc_start.bat

start /b /min vlc_start.bat
for /F "tokens=1,2* delims=, " %%a in ('tasklist /v /FO:CSV ^| find %DEPEND%') do ( set PIDD=%%b )
echo Watchdog %watch%--%depend%

REM Wait for the Watched Batch Job to finish.
:loop
TIMEOUT /T 1 /NOBREAK 1>NUL
set PIDW=0
for /F "tokens=1,2* delims=, " %%a in ('tasklist /v /FO:CSV ^| find %WATCH%') do (
set PIDW=%%b
)
if [%PIDW%] NEQ [0] goto :loop

REM Now end the depend Job.
echo %WATCH% has ended , stopping %PIDD%
TASKKILL /F /IM  VLC.exe
del /F vlc_start.bat 1>NUL 2>NUL
del /F stream.ts 1>NUL 2>NUL
del /F vlc.log