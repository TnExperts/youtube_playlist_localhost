@echo off
SET /P Link=HTTP Source: 
curl "%Link%" --output - | vlc.exe "-" --one-instance  --sout #gather:transcode{vcodec=h264}:std{access=http,dst=:8090/go.ts} vlc://quit
pause