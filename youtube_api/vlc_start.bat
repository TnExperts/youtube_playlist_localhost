@echo off 
:wait  
if not EXIST stream.ts goto :wait 
REM simple trick to just send a file as a ts stream - without further processing. 
start /MIN vlc.exe -I dummy  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{dst=:8080/gogo.ts} 
REM # Using FlashVideo can be seen as "more compatible" but also does requires more processing time.  
REM # start /MIN /ABOVENORMAL vlc.exe -I dummy --no-interact  --one-instance --playlist-enqueue "stream.ts" --loop --sout-keep --sout=#gather:http{mux=ffmpeg{mux=flv},dst=:8080/bla}  
exit 
