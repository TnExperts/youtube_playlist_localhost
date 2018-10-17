@echo off

REM watch for file Updates and print newly added Lines
REM sortof Tail in pure batch.
REM Marcedo@habMalNeFrage.de

SETLOCAL ENABLEEXTENSIONS
SETLOCAL ENABLEDELAYEDEXPANSION

set FileName="new.txt"
if not exist %FileName% exit

echo Watchin %FileName% for new lines

SET count=1 
SET trigger=0
FOR %%A IN (%FileName%) DO SET FileSize=%%~zA

::Check for File Updates
set oldFileSize=%FileSize%
:loop
FOR %%A IN (%FileName%) DO SET FileSize=%%~zA
if %oldFileSize% neq %FileSize% (
	::echo file updated
	SET count=0
	call :tail
	set oldFileSize=%FileSize%
	)
goto :loop

:: Print newly added Lines
:tail
for /f  "usebackq tokens=*" %%b in (`type %FileName%`) do (
    set line=%%b
		call :cnt
  )
exit /b 0
:done

:cnt
    set /a count+=1
		rem echo %count% %trigger%
		if %count% GTR %trigger% (
			echo %line%
			SET trigger=%count% 
			)
		exit /b 0
:done
