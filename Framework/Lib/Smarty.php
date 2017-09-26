<?php
/**
 * Description of Smarty
 *
 * @author WangChengjin
 */

namespace Core\Lib;

class Smarty
{
    
    protected static $smartyConfig;
    protected static $instance;
    
    /**
     *
     * @return self
     */
    public static function instance()
    {
        if(!static::$instance)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @param string $namespaceDir 模板所在命名空间的目录
     * @return void
     */
	
    public function fetch($templateFile, $var, $namespaceDir = '')
    {
        require_once(FRAMEWORK_ROOT . 'Lib/Smarty/Smarty.class.php');
        $tpl = new \Smarty();
        self::$smartyConfig = Sys::getCfg('Smarty');
        $tpl->caching = self::$smartyConfig->caching;
        
        //按照模块自动判读模块模板
        if(isset(self::$smartyConfig->template_dir[MOUDLE_NAME])){
            $tpl->template_dir = SYS_ROOT.self::$smartyConfig->template_dir[MOUDLE_NAME];
        }else{
            $tpl->template_dir = SYS_ROOT.self::$smartyConfig->template_dir;
        }        

        // $tpl->template_dir = self::$smartyConfig->template_dir;
        $tpl->left_delimiter = "<%";
        $tpl->right_delimiter = "%>";
        if(self::$smartyConfig->token){
            $tpl->token = self::$smartyConfig->getToken();
        }
        $tpl->compile_dir = SYS_ROOT . self::$smartyConfig->compile_dir;
        $tpl->cache_dir = SYS_ROOT . self::$smartyConfig->cache_dir;
        $tpl->assign($var);
        $tpl->display($_SERVER['DOCUMENT_ROOT'].self::$smartyConfig->template_dir[$namespaceDir] . $templateFile);
    }
 /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @param string $namespaceDir 模板所在命名空间的目录
     * @return void
     */
	
    public function fetchout($templateFile, $var, $namespaceDir = '')
    {
        require_once(FRAMEWORK_ROOT . 'Lib/Smarty/Smarty.class.php');
        $tpl = new \Smarty();
        self::$smartyConfig = Sys::getCfg('Smarty');
        $tpl->caching = self::$smartyConfig->caching;
        
        //按照模块自动判读模块模板
        if(isset(self::$smartyConfig->template_dir[MOUDLE_NAME])){
            $tpl->template_dir = SYS_ROOT.self::$smartyConfig->template_dir[MOUDLE_NAME];
        }else{
            $tpl->template_dir = SYS_ROOT.self::$smartyConfig->template_dir;
        }        

        // $tpl->template_dir = self::$smartyConfig->template_dir;
        $tpl->left_delimiter = "<%";
        $tpl->right_delimiter = "%>";
        $tpl->compile_dir = SYS_ROOT . self::$smartyConfig->compile_dir;
        $tpl->cache_dir = SYS_ROOT . self::$smartyConfig->cache_dir;
        $tpl->assign($var);
        return $tpl->fetch($_SERVER['DOCUMENT_ROOT'].self::$smartyConfig->template_dir[$namespaceDir] . $templateFile);
    }
    

}
