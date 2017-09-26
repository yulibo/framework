<?php
namespace Core\Config;

class MNLogger extends ConfigBase
{

    public $exception = array(
        'on' => true,
        'app' => 'admin',
        'logdir' => MONITOR_LOG,
    );
    
    public $trace = array(
        'on' => true,
        'app' => 'Mall',
        'logdir' => MONITOR_LOG,
    );

    public $users = array(
        'on' => true,
        'app' => 'Store',
        'logdir' => SYS_LOG,
    );
}

