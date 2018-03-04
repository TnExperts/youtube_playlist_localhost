@Echo off
REM Streams a YouTube Link, an ID, or even a Playlist to a VLC Instance.
REM Make it available on http://localhost:8080/gogo.ts
REM removed dependency to "youtube-dl" and "ffmpeg"
REM Mar 2018, Marcedo@habMalNeFrage.de

setlocal enabledelayedexpansion
SET /P Link=YouTube Link , Playlist or ID ? (empty to use last generated list) : 
TASKKILL /IM VLC.exe 1>NUL 2>NUL

IF ["%Link%"] neq [""] ECHO ...Fetching Playlist Urls to yt-playlist.lst... 
IF ["%Link%"] neq [""] php\php yt_get_playlist.php "%Link%" > yt-playlist.lst

ECHO  ..Init VLC-OBS Bridge on http://localhost:8080/gogo.ts
echo @echo off >vlc_start.bat
echo  :wait if not EXIST stream.ts  ( goto :wait ) >> vlc_start.bat
echo start /MIN /ABOVENORMAL vlc.exe --no-interact  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{dst=:8080/gogo.ts} >>vlc_start.bat
echo REM # Using FlashVideo can be seen as "more compatible" but also does requires more processing time.  >>vlc_start.bat
echo ::start /MIN /ABOVENORMAL vlc.exe --no-interact  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{mux=ffmpeg{mux=flv},dst=:8080/bla}  >>vlc_start.bat
echo exit >>vlc_start.bat 
start  /MIN vlc_start.bat

for /F %%e IN (yt-playlist.lst) do ( :: Iterate through downbloaded playlist ::
	call set youtube_id=%%e
	call :sub_stream_file
)

:sub_stream_file
REM # https://stackoverflow.com/questions/2323292/assign-output-of-a-program-to-a-variable
REM # yt_get_prot.php ::> https://gist.github.com/arjunae/6737ecf40956efa3fe4c4d3b45d99f2d
REM # Quality : mp4-640x360 (Format No 18). Change the php script if you need the Data in another Format.
for /f %%i in ('php\php yt_get_protID.php %youtube_id%') do set Link=%%i
ECHO  ..Stream https://www.youtube.com/watch?v=%youtube_id%
start /WAIT /MIN /ABOVENORMAL vlc.exe --vout=dummy --volume 0 --playlist-enqueue "%Link%"  --sout-keep --sout=#duplicate{dst=std{access=file,mux=ts,dst=stream.ts},dst=display}"
exit /b 0 
:end_sub