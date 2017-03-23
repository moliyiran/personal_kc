<?php
namespace app\index\controller;
use stock\db\MSSql;
class Common extends \think\Controller
{

	public $dbinfo='';

	protected $beforeActionList = [
	    //'check_login',
	];
	
	public function check_login(){
		/*
		if(empty($this->dbinfo)){
			$this->dbinfo=new MSSql();
		}
		*/
		if(empty($_SESSION['admin_id'])){
			$this->redirect('Check/login');exit;
		}
	}
	public function objectToArray($obj){
	    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
	    if(is_array($arr)){
	        return array_map(__FUNCTION__, $arr);
	    }else{
	        return $arr;
	    }
	}
	public function inject_check($Sql_Str) {//自动过滤Sql的注入语句。
		return $check=preg_match('/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i',$Sql_Str);

	}
}
