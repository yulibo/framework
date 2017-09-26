<?php  
  
namespace Core\Lib\Tools;

class ExtendsModel extends \Core\Lib\ModelBase{  


	/**
	 * magic __get method. You can access the instance of the default DbConnection and field, if filled, values directly.
	 *
	 * @param string $name
	 * @return \Core\Lib\DbConnection|multitype:
	 */
	public function __get($name) {
		switch ($name) {
			case 'exDb';
				return $this->getExtendsDb();
				continue;
			default:
				return parent::__get($name);
				break;
		}
	}
	
	//获取扩展db
	public function getExtendsDb(){
		return \Module\WobeiDataMigrationDb::getIns();
	}


}
    