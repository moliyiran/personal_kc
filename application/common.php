<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// 应用公共文件
	function gtest(){
		exit('aa');
	}
	function sortdata($catArray, $id = 0 , $prefix = ''){
		static $formatCat = array();
		static $floor     = 0;

		foreach($catArray as $key => $val)
		{
			if($val['pid'] == $id)
			{
				$str         = $floor>0 ? nstr('--',$floor) : '';
				$val['name'] = $str.$val['name'];

				$val['floor'] = $floor;
				$formatCat[]  = $val;

				unset($catArray[$key]);

				$floor++;
				sortdata($catArray, $val['id'] ,$prefix);
				$floor--;
			}
		}
		return $formatCat;
	}
	//处理商品列表显示缩进
	function nstr($str='-',$num=0)
	{
		$return = '';
		for($i=0;$i<$num;$i++)
		{
			$return .= $str;
		}
		return $return;
	}
	function suc($message='成功'){
		$array=array(
			'statusCode'=>'200',
			'message'=>$message

		);
		/*	
		"statusCode":"200",
		"message":"\u64cd\u4f5c\u6210\u529f",
		"navTabId":"",
		"rel":"",
		"callbackType":"",
		"forwardUrl":"",
		"confirmMsg":""
		*/
		exit(json_encode($array));		
	}
	function err($message='failed'){
		$array=array(
			'statusCode'=>'301',
			'message'=>$message
		);
		exit(json_encode($array));			
	}
	function reload_request($type=0){
		$result=array();
		$result['get']=$_GET;
		$result['post']=$_POST;
		if('1'==$type){
			return $result['get'];
		}else if('2'==$type){
			return $result['post'];
		}else{
			return $result;
		}
	}	