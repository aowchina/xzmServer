<?php
/**
 * 公共文件
 * 公共类
 * author moyu
 */
include_once("functions_mut.php");
include_once("functions_mdb.php");
include_once("functions_mcheck.php");

class Common{
	private $client_id;
	private $client_secret;
	private $org_name;
	private $app_name;
	private $hx=array();
	public function __construct($option){
		$this->client_id = 'YXA67gOvwDoWEeevFDleJH6VnQ';
		$this->client_secret = 'YXA6sXRRweQi_NT9hVUzs9G8xEdXCJA';
		$this->org_name = 'minfo';
		$this->app_name = 'carautorepair';

		$this->hx['client_id'] = $this->client_id;
		$this->hx['client_secret'] = $this->client_secret;
		$this->hx['org_name'] = $this->org_name;
		$this->hx['app_name'] = $this->app_name;
	}
	/**
	 * 验证参数个数
	 * $n : 除8段以外的参数个数
	 * $type : 总个数是否加8段(0代表加，1代表不加)
	 */
	public function com($n=0,$type=0)
	{
		// var_dump($_POST);exit;

		$paramNum = $type == 0 ? $n+8 : $n;
		if(!(count($reqlist) == $paramNum)){
		    forExit($lock_array);
		    toExit(9, $return_list);
		}

	}	
	/**
	 * 验证参数id
	 * $paremId : 参数Id
	 */
	public function checkParemId($paremId="")
	{

		$id = intval(trim($reqlist[8]));
		if(!($id >= 1)){
		    forExit($lock_array);
		    toExit(10, $return_list);
		}

	}
	/**
	 * 获取数据表中的数据
	 * $table ~ 表名
	 * $where ~ 条件
	 * 
	 * return $data ~ 返回数据（array）
	 */
	public function getData($table="",$where="")
	{
		$sql = "select * from ".$table." where ".$where;
		$list = dbLoad(dbQuery($sql, $con));

		return $list;
	}

	/**
	 *
	 * 环信相关参数
	 * auther : mo_yu
	 * time : 2017 6 22
	 * return array
	 */
	public function hx(){
		return $this->hx;
	}
}
?>