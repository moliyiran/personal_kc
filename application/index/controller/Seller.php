<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db; 
class Seller extends \app\index\controller\Common
{
	public $dbinfo='';
    //public $request=Request::instance();
    protected $beforeActionList = [
	    //'lay_do',
        'per_index',
	];
    public function per_index(){
        $this->view->engine->layout(false);
    }
    public function index(){
        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $goodsObj=Db::name('seller');
        $goods_result=$goodsObj->where('status=0')->limit(($now_page-1)*$page_per,$page_per)->select();
        $totalcount=$goodsObj->where('status=0')->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$goods_result);      	
    	return $this->fetch();
    }
    public function del(){
    	$request_data=reload_request(1);
    	$id=(int)$request_data['uid'];
    	if(!empty($id)){
			Db::name('seller')
		    ->where('id', $id)
		    ->update(['status' => 1]);
		    suc();
    	}
    	err();
    }
    public function add(){
        if(!empty($_POST)){
            if(empty($_POST['name'])){
                err();
            }
            $request_data=reload_request(2);

            $data['name']=$request_data['name'];
            $data['name_en']=$request_data['name_en'];
            $data['address']=$request_data['address'] ? addslashes($request_data['address']) : '';
            $data['tel']=$request_data['tel'];
            $data['email']=$request_data['email'];
            $data['type']=(int)$request_data['type'];
            $exe_result=$cate_result=Db::name('seller')->insert($data);
            if($exe_result){
                suc();
            }else{
                err();
            }
        }else{
        	return $this->fetch();  
        }
    }
    public function edit(){
        if(!empty($_POST)){
        	$request_data=reload_request(2);
        	$id=(int)$request_data['id'];
            if(empty($request_data['name'])||empty($id)){
                err();
            }

            $data['name']=trim($request_data['name']);
            $data['name_en']=$request_data['name_en'];
            $data['address']=$request_data['address'] ? addslashes($request_data['address']) : '';
            $data['tel']=$request_data['tel'];
            $data['email']=$request_data['email'];
            $data['type']=(int)$request_data['type'];
            $sellerObj=Db::name('seller');
            if($find_result=$sellerObj->where('name="'.$data['name'].'" and id !='.$id)->find()){
            	err();
            }
            $exe_result=$sellerObj->where('id', $id)
		    ->update($data);
            if($exe_result){
                suc();
            }else{
                err();
            }
        }else{
        	$request_data=reload_request(1);
        	$id=$request_data['uid'];
            if(empty($id)){
                err();
            }        	
	        $goodsObj=Db::name('seller');
	        $goods_result=$goodsObj->where('id='.$id.' and status=0')->find(); 
	        $this->assign('seller',$goods_result);       	
        	return $this->fetch();  
        }
    }





}    