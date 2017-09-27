rem -- http://www.upupw.net
rem -- webmaster@upupw.net
set nginx_vc=UPUPW_Nginx
set cgi_vc=UPUPW_PHPFPM
set database_vc=UPUPW_Database_N
set updaemon_vc=UPUPW_updaemon_N
set mem_vc=UPUPW_MemCached_N
set fzftp_vc=FileZilla Server
set nginx_port=80
set ftp_port=21
set database_port=3306
set phpfpm_port=9054
set mem_ip=127.0.0.1
set mem_port=11211
set mem_memory=256
set mem_connect=1920
for /d %%d in (*) do (
 if exist %%d\upcore.exe set upcore_dir=%%d
 if exist %%d\nginx.exe set nginx_dir=%%d&& set nginx_exe=nginx.exe
 if exist %%d\php.exe set php_dir=%%d
 if exist %%d\bin\mysqld.exe set database_dir=%%d&& set database_exe=mysqld.exe
 if exist %%d\memcached.exe set mem_dir=%%d
 if exist %%d\FileZilla_server.exe set fz_dir=%%d&& set fz_exe=FileZilla_server.exe
)
if "%upcore_dir%"=="" echo   # upcore Not Found. & pause & exit /b
if "%nginx_dir%"=="" echo   # Nginx Not Found. & pause & exit /b
if "%php_dir%"=="" echo   # PHP Not Found. & pause & exit /b
if "%database_dir%"=="" echo   # Database Not Found. & pause & exit /b
if "%mem_dir%"=="" echo   # memcached Not Found. & pause & exit /b
set php=%upcore_dir%\upcore.exe -d extension_dir=%upcore_dir% -d date.timezone=UTC -n %upcore_dir%\up.dll
set pause=%php% echo `- 请按任意键继续...`; ^&^& pause^>nul 2>nul
set phpfpm=%php_dir%\phpfpm
set vhosts_conf=%nginx_dir%\conf\vhosts.conf
set upcore=%upcore_dir%
set upd_config=%upcore_dir%\upd_config.cmd
set cfg_bak_zip=Backup\cfg_bak.zip
set cfg_sckf_zip=Backup\cfg_sckf.zip
set cfg_xnsp_zip=Backup\cfg_xnsp.zip
set Sys32=%SystemRoot%\system32
set Path=%Sys32%;%Sys32%\wbem;%SystemRoot%
set net=%Sys32%\net.exe
set taskkill=%Sys32%\taskkill.exe
set tasklist=%Sys32%\tasklist.exe
set findstr=%Sys32%\findstr.exe
if not exist %taskkill% echo   # 缺少 %taskkill%, 无法进行. & %pause%
if not exist %tasklist% echo   # 缺少 %tasklist%, 无法进行. & %pause%
if not exist %findstr% echo   # 缺少 %findstr%, 无法进行. & %pause%
if not exist %net% set net=%Sys32%\net1.exe
if not exist %net% echo   # 缺少 %Sys32%\net.exe, 不可继续. &%pause%&set php=&exit /b
%php% "chk_path(getcwd());" || %pause% && set php=
