REM where to find the php phar file
SET phpunitdir=D:\wamp64\www\phpunit
REM where to find the version of php you want to use
SET phpdir=D:\wamp64\bin\php\php8.2.4
"%phpdir%\php.exe" "%phpunitdir%\phpunit-10.1.3.phar" --colors --bootstrap autoloader.php unittests
