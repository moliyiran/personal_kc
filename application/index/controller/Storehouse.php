<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db; 
class Storehouse extends \app\index\controller\Common
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
        $goodsObj=Db::name('store');
        $goods_result=$goodsObj->where('status=0')->limit(($now_page-1)*$page_per,$page_per)->order('id DESC')->select();
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
			Db::name('store')
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
            $data['address']=$request_data['address'] ? addslashes($request_data['address']) : '';
            $data['tel']=$request_data['tel'];
            $data['create_time']=time();
            $exe_result=Db::name('store')->insert($data);
            $last_Id =Db::name('store')->getLastInsID();
            if($exe_result){
                $z_data=array();
                if(!empty($_POST['zone'])){
                    for($i=0;$i<count($_POST['zone']);$i++){
                        if(!empty($_POST['zone'][$i])){
                            $z_data[]=array('name'=>$_POST['zone'][$i],'store_id'=>$last_Id);
                        }
                    }
                    if(!empty($z_data)){
                        Db::name('store_zone')->insertAll($z_data);
                    }
                }                
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
            $data['address']=$request_data['address'] ? addslashes($request_data['address']) : '';
            $data['tel']=$request_data['tel'];
            $sellerObj=Db::name('store');
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
	        $goodsObj=Db::name('store');
	        $goods_result=$goodsObj->where('id='.$id.' and status=0')->find(); 
            $zoneObj=Db::name('store_zone');
            $zone_result=$zoneObj->where('store_id='.$id.' and status=0')->select();            
	        $this->assign('seller',$goods_result);
            $this->assign('zone',$zone_result);        	
        	return $this->fetch();  
        }
    }





}    