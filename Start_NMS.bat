@echo off
TITLE NMS Startup Script

:: Check for Administrator privileges (required to edit the hosts file)
>nul 2>&1 "%SYSTEMROOT%\system32\cacls.exe" "%SYSTEMROOT%\system32\config\system"
if '%errorlevel%' NEQ '0' (
    echo Requesting Administrator privileges to run the NMS Client Agent...
    goto UACPrompt
) else ( goto gotAdmin )

:UACPrompt
    :: Use PowerShell to request elevation (bypasses Windows Script Host restrictions)
    powershell -Command "Start-Process '%~s0' -Verb RunAs"
    exit /B

:gotAdmin
    :: Change directory to where this batch file is located
    cd /d "%~dp0"
    
    echo Starting NMS Client Agent in the background...
    :: Start python agent in a minimized window so it doesn't clutter the screen
    start /min cmd /c "python client_agent.py"
    
    echo Opening NMS Dashboard in your web browser...
    :: Open the default browser to the localhost dashboard
    start http://localhost:8000
    
    exit
