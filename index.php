<?php
define('SYS_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('FRAMEWORK_ROOT', SYS_ROOT . 'Framework' . DIRECTORY_SEPARATOR);
define('SYS_LOG', 'logs' . DIRECTORY_SEPARATOR);
define('MONITOR_LOG', SYS_LOG . 'monitor' . DIRECTORY_SEPARATOR);
require_once FRAMEWORK_ROOT . 'Lib/Autoloader.php';
Core\Lib\Autoloader::loadAll();
require_once(FRAMEWORK_ROOT . 'Main.php');
\Core\Main::instance()->run();
