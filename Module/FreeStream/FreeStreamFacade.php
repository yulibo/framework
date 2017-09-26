<?php
namespace Module\FreeStream;

use \Exception as Exception;
use \Module\FreeStream\Extension\FreeFourG as FreeFourG;
use \Module\FreeStream\Extension\LoginFourG as LoginFourG;
use \Module\FreeStream\Extension\WeChat as WeChat;
use \Module\FreeStream\Extension\WishfulCard as WishfulCard;
use \Module\FreeStream\Extension\WoBuy as WoBuy;

class FreeStreamFacade
{
    
    // id 对应对象
    private $objName = array(
        1 => 'FreeFourG',
        2 => 'LoginFourG',
        3 => 'WoBuy',
        4 => 'WeChat',
        5 => 'WishfulCard'
    );

    private static $currentObj; // 当前对象

    /**
     * 组合数据
     * 
     * @var array
     */
    public $streamData;

    /**
     * 存储客户对象
     * 
     * @var array
     */
    private $obj = array();

    /**
     * 向对象结构中添加对象元素
     * 
     * @param $ele Customer            
     */
    private function addElement($ele)
    {
        $gameAda = $this->switchModel($ele);
        array_push($this->obj, $gameAda);
    }
    
    // 选择模型
    private function switchModel($ele)
    {
        switch ($ele) {
            case 'FreeFourG':
                $gameAda = new FreeFourG();
                break;
            case 'LoginFourG':
                $gameAda = new LoginFourG();
                break;
            case 'WeChat':
                $gameAda = new WeChat();
                break;
            case 'WishfulCard':
                $gameAda = new WishfulCard();
                break;
            case 'WoBuy':
                $gameAda = new WoBuy();
                break;
            default:
                trigger_error('try get undefined property: ' . $level . ' of class ' . __CLASS__, E_USER_NOTICE);
                break;
        }
        return $gameAda;
    }

    /**
     * 处理请求
     * 
     * @param $visitor Visitor            
     */
    private function handleRequest()
    {
        // 遍历对象结构中的元素，接受访问
        foreach ($this->obj as $ele) {
            $className = $this->getClassName($ele);
            $bid = $this->getBidByClass($className);
            if ($data = $ele->getStreamList()) {
                $this->streamData[$className]['streamList'] = $data;
            }
            if ($data = $ele->getStreamBoard()) {
                $this->streamData[$className]['streamBoard'] = $data;
            }
        }
    }
    
    // 根据name获取bid 板块ID
    private function getBidByClass($class)
    {
        return array_search($class, $this->objName);
    }
    
    // 获取类名
    private function getClassName($obj)
    {
        $list = explode('\\', get_class($obj));
        return array_pop($list);
    }
    
    // 组合数据
    public function facade()
    {
        foreach ($this->objName as $val) {
            $this->addElement($val);
        }
        $this->handleRequest(); // 批量请求处理
    }
    
    // 获取模型
    private function getModel($bId)
    {
        if (! isset($this->objName[$bId])) {
            throw new Exception('操作错误');
        }
        if (self::$currentObj) {
            return self::$currentObj;
        }
        $ele = $this->objName[$bId];
        return self::$currentObj = $this->switchModel($ele);
    }
    
    // 订购流量
    public function orderStream(array $data)
    {
        return $this->getModel($data['bId'])->orderStream($data);
    }
    
    // 订购流量
    public function getStreamList(array $data)
    {
        return $this->getModel($data['bId'])->getStreamList($data['page']);
    }
    
    // 获取错误
    public function getError()
    {
        return self::$currentObj->getError();
    }
}
?>