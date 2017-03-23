<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db; 
class Goods extends \app\index\controller\Common
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
    public function index()
    {
        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $goodsObj=Db::name('goods');
        $goods_result=$goodsObj->where('status=0')->limit(($now_page-1)*$page_per,$page_per)->select();
        $totalcount=$goodsObj->where('status=0')->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$goods_result);     	
		return $this->fetch();

    }
    public function add(){
        if(!empty($_POST)){
            if(empty($_POST['name'])||empty($_POST['name_en'])||empty($_POST['barcode'])||empty($_POST['goods_No'])||empty($_POST['spec'])||empty($_POST['volume'])||empty($_POST['cate'])||empty($_POST['unit_big'])||empty($_POST['unit_tiny'])){
                err();
            }
            $request_data=reload_request(2);
            if(!is_numeric($request_data['volume'])||!is_numeric($request_data['spec'])||(int)$request_data['cate']<=0|(int)$request_data['unit_big']<=0|(int)$request_data['unit_tiny']<=0){
                err();
            }
            $data['name']=$request_data['name'];
            $data['name_en']=$request_data['name_en'];
            $data['unit_tiny']=$request_data['unit_tiny'];
            $data['unit_big']=$request_data['unit_big'];
            $data['spec']=$request_data['spec'];
            $data['volume']=$request_data['volume'];
            $data['barcode']=$request_data['barcode'];
            $data['goods_No']=$request_data['goods_No'];
            $data['cate']=$request_data['cate'];
            $data['seller']=(int)$request_data['seller'];
            $exe_result=$cate_result=Db::name('goods')->insert($data);
            if($exe_result){
                suc();
            }else{
                err();
            }
        }else{
            $seller_result=Db::name('seller')->where('status=0')->order('id desc')->select();
             $cate_result=Db::name('category')->where('del=0')->select();
            if($cate_result){
                $cate_result=sortdata($cate_result);
                $this->assign('cate_result',$cate_result);  
            }
            $unit_result=Db::name('unit')->where('del=0')->select();

            $this->assign('seller_result',$seller_result);  

            $this->assign('unit_result',$unit_result);  
                       
            return $this->fetch();           
        }

    }
    public function goods_unit(){
        $result=Db::name('unit')->where('del=0')->select();

        $this->assign('result',$result);  

        return $this->fetch();
    }
    public function unit_add(){
        if($_POST){
       
            if(!empty($_POST['name'])){
                $data['name']=addslashes($_POST['name']);
                Db::name('unit')->insert($data);
                suc();
            }
        }else{
            $result=Db::name('unit')->where('del=0')->select();
            if($result){
                $this->assign('result',$result);  
            }               
            return $this->fetch();
        }

    }    
    public function goods_category(){
        $result=Db::name('category')->where('del=0')->select();
        if($result){
            $result=sortdata($result);
            $this->assign('result',$result);  
        }   
        return $this->fetch();
    }
    public function cate_add(){
        if($_POST){
       
            if(!empty($_POST['name'])){
                $data['name']=addslashes($_POST['name']);
                $data['pid']=(int)$_POST['pid'];
                Db::name('category')->insert($data);
                suc();
            }
        }else{
            $result=Db::name('category')->where('del=0')->select();
            if($result){
                $result=sortdata($result);
                $this->assign('result',$result);  
            }               
            return $this->fetch();
        }

    }
  
}
