@echo off
for /d %%d in (*) do (
 if exist %%d\bin\mysqld.exe set database_dir=%%d&& set database_exe=mysqld.exe
)
%CD%\%database_dir%\bin\%database_exe% --skip-grant-tables >nul 2>nul
exit