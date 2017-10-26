<?php
ini_set('display_errors', 1);
ini_set('session.cookie_path', '/');
ini_set('session.cookie_lifetime', '1800');
error_reporting(E_ALL);
define('SYS_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('FRAMEWORK_ROOT', SYS_ROOT . 'Framework' . DIRECTORY_SEPARATOR);
define('SYS_LOG', 'logs' . DIRECTORY_SEPARATOR);
define('MONITOR_LOG', SYS_LOG . 'monitor' . DIRECTORY_SEPARATOR);
define('WS_ADMIN', SYS_ROOT . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR);
define("TOKEN_ON", TRUE);

require_once FRAMEWORK_ROOT . 'Lib/Autoloader.php';
Core\Lib\Autoloader::loadAll();


require_once(FRAMEWORK_ROOT . 'Main.php');
\Core\Main::instance()->run();
