REM where to find the php phar file
SET phpunitdir=D:\EasyPHP\phpunit
REM where to find the version of php you want to use
SET phpdir=C:\Program Files (x86)\EasyPHP-Devserver-17\eds-binaries\php\php749vc15x86x200830155446
"%phpdir%\php.exe" "%phpunitdir%\phpunit-9.5.1.phar" --colors --bootstrap autoloader.php --verbose --debug unittests
