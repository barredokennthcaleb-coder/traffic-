' Launches the Traffic System database watchdog with no visible window.
Set fso = CreateObject("Scripting.FileSystemObject")
here = fso.GetParentFolderName(WScript.ScriptFullName)
CreateObject("WScript.Shell").Run """" & here & "\watchdog-database.bat""", 0, False
