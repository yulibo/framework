<?php
/**
 * Description of Smarty
 *
 * @author ylb
 */

namespace Core\Config;

class Smarty extends ConfigBase
{

    public $caching = false;
    public $compile_dir = 'Temp/Compile/';
    public $cache_dir = 'Temp/Cache/'; 
    public $template_dir = array(
        'Mall' => '/Mall/Template/',
        'Admin' => '/Admin/Template/',
        'Store' => '/Store/Template/',
        'Mobile' => '/Mobile/Template/',
	'Stream' => '/Stream/Template/'
    );
}
