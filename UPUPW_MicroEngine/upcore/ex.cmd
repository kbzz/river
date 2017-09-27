@echo off
:ex_menu
cls
echo   此处为UPUPW定制服务扩展窗口CMD文件位于upcore/ex.cmd
echo.
echo   [ 1 ] 打开远程登录窗口           [ 2 ] 打开程序管理器窗口
echo.
echo   [ 3 ] 打开本地网络连接           [ 4 ] 打开计算机管理窗口
echo.
echo   [ 5 ] 打开本地防火墙             [ 6 ] 打开任务管理器窗口
echo.
echo   [ 7 ] 打开本地服务窗口           [ 8 ] 打开性能监测器窗口
echo.
set input=
set /p input=-^> 请选择: 
cls
echo.
if /i "%input%"=="1" goto ex_1
if /i "%input%"=="2" goto ex_2
if /i "%input%"=="3" goto ex_3
if /i "%input%"=="4" goto ex_4
if /i "%input%"=="5" goto ex_5
if /i "%input%"=="6" goto ex_6
if /i "%input%"=="7" goto ex_7
if /i "%input%"=="8" goto ex_8
if /i "%input%"=="" goto ex_menu
if /i "%input%"=="q" goto ex_main
echo.
echo    未选择操作或输入有误 请返回！
echo.
%pause%
goto ex_menu

:ex_1
start mstsc
goto ex_menu

:ex_2
start appwiz.cpl
goto ex_menu

:ex_3
start ncpa.cpl
goto ex_menu

:ex_4
start compmgmt.msc
goto ex_menu

:ex_5
start firewall.cpl
goto ex_menu

:ex_6
start taskmgr
goto ex_menu

:ex_7
start services.msc
goto ex_menu

:ex_8
start perfmon.msc
goto ex_menu

:ex_main
prompt
popd
cls
