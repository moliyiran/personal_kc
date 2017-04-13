<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db;
use think\Session; 
class Outorder extends \app\index\controller\Common
{
	public $dbinfo='';
    //public $request=Request::instance();
    protected $beforeActionList = [
	    //'lay_do',
        'per_index',
	];
    public function per_index(){
        if(empty(Session::get('admin_id'))||empty(Session::get('admin_name'))){
            Session::get('admin_id','123');
            Session::set('admin_name','admin');
        }
        $this->view->engine->layout(false);
    }
    public function purchase(){
        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $orderObj=Db::name('out_order');
        $order_result=$orderObj->where('id>0')->limit(($now_page-1)*$page_per,$page_per)->order('id desc')->select();
        $totalcount=$orderObj->where('id>0')->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$order_result);             
        return $this->fetch();
    }
    public function add(){
        if(!empty($_POST)){
            $request = request()->param();//print_r($request);exit;
            $customer=(int)$request['customer'];
            if(!empty($request)&&$customer){
                $order_data=array();
                $goods_data=array();
                $seller_data=array();
                $time=time();
                $order_php_amount=0;
                $goods_data=array();//echo json_encode($request);exit;
                $num_max=array();

                foreach ($request['goods_id'] as $key => $value) {
                    $goods_id=(int)$value;
                    $goods_num=(int)$request['num1'][$key];
                    $per_price=(float)$request['sell_price'][$key];
                    $store_goods_id=(int)$request['store_goods_id'][$key];//大单位的单价
                    $store_id=(int)$request['store_id'][$key];
                    $zone_id=(int)$request['zone_id'][$key];
                    $_key=$goods_id.'-'.$store_id.'-'.$zone_id;
                    if(!$num_max[$_key]){
                        $num_max[$_key]=$goods_num;
                    }else{
                        $num_max[$_key] +=$goods_num;
                    }
                    
                    if($num_max[$_key]>(int)$request['before_num'][$key]){
                        err('out of the store number');
                    } 
                    if($goods_id&&$goods_num){
                        $order_php_amount +=$goods_num*$per_price;
                        $goods_data[]=array(
                            'goods_id'=>$goods_id,
                            'store_goods_id'=>$store_goods_id,
                            'num'=>$goods_num,
                            'fee'=>$per_price,
                            'store_id'=>$store_id,
                            'zone_id'=>$zone_id,                            
                        ); 
                        $goods_dec_data[]=array(
                            'store_goods_id'=>$store_goods_id,
                            'num'=>$goods_num                          
                        );                                               
                    }
                }
                if(!empty($goods_data)){
                    //a.订单数据
                    $order_info=array();
                    $order_info['order_No']=date('YmdHis').rand(100000,999999);
                    $order_info['php_amount']=$order_php_amount;
                    $order_info['admin_id']=12;//Session::get('admin_id');
                    $order_info['admin_name']='admin';//Session::get('admin_name');
                    $order_info['create_time']=$time;
                    $order_info['customer_id']=$customer;
                    Db::startTrans();
                    try{
                        $orderObj=Db::name('out_order');
                        $order_id=$orderObj->insert($order_info);
                        $last_Id = $orderObj->getLastInsID();
                        //b.订单商品
                        foreach ($goods_data as $key => $value) {
                            if($value){
                                $goods_data[$key]['order_id']=$last_Id;
                            }
                        }
                        Db::name('out_order_goods')->insertAll($goods_data);
                        $storeOBJ= Db::name('store_goods');
                        foreach ($goods_dec_data as $key => $value) {
                            if($value){
                                $storeOBJ->where('id',$value['store_goods_id'])->setDec('num', $value['num']);                                                                   
                            }
                        }                        
                        // 提交事务
                        Db::commit();    
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                    }                    
                   
                }
            }

            if($order_id){
                suc();
            }else{
                err();
            }
        }else{
            /*
            $seller_result=Db::name('seller')->where('status=0')->order('id desc')->select();
             $cate_result=Db::name('category')->where('del=0')->select();
            if($cate_result){
                $cate_result=sortdata($cate_result);
                $this->assign('cate_result',$cate_result);  
            }
            $unit_result=Db::name('unit')->where('del=0')->select();

            $this->assign('seller_result',$seller_result);  

            $this->assign('unit_result',$unit_result);
            */ 
            $cust_result=Db::name('customer')->where('status=0')->order('id desc')->select(); 
            $this->assign('cust_result',$cust_result);          
            return $this->fetch();           
        }

    }
    public function show(){
        $request = request()->param();
        $id=(int)$request['id'];
        if($id){
            $orderObj=Db::name('out_order');
            
            $join = [
                ['rep_goods b','a.goods_id = b.id','LEFT'],
                ['rep_store s','a.store_id = s.id','LEFT'],
                ['rep_store_zone z','a.zone_id = z.id','LEFT'],
            ];
            
            $order_result=$orderObj->where('id='.$id)->find();
            if($order_result){
                $goods_result=Db::table('rep_out_order_goods')->alias('a')->join($join)->field('a.*,b.goods_No,b.name,s.name sname,z.name zname')->where('a.order_id='.$id)->select();
                $this->assign('order',$order_result);
                $this->assign('order_goods',$goods_result);
                return $this->fetch(); 
            }
             
        }
    }
    public function purchaseGoodsAdd(){

        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $goodsObj=Db::table('rep_store_goods');
        $where='';
        $goods_val=$_POST['goods_name'] ? addslashes(trim($_POST['goods_name'])) : '';
        if($goods_val&&$this->inject_check($goods_val)){
            exit;
        }
        $store_val=(int)$_POST['store'];
        if($goods_val){
            $where .=" and (a.name like '%".$goods_val."%' or a.name like '%".$goods_val."%' )";
            $this->assign('goods_name',$_POST['goods_name']);
        }
        if($store_val){
            $where .=" and a.store_id=".$store_id_val;
            $this->assign('store_id_id',$store_id_val);
        }

        $join = [
            ['rep_goods b','a.good_id = b.id','LEFT'],
            ['rep_store s','a.store_id = s.id','LEFT'],
            ['rep_store_zone z','a.zone_id = z.id','LEFT'],
        ];     
        $goods_result=$goodsObj->alias('a')->where('a.status=0 and a.num>0'.$where)->join($join)->field('a.*,b.name bname,b.barcode,b.goods_No,s.name sname,z.name zname')->limit(($now_page-1)*$page_per,$page_per)->order('id desc')->select();
        $totalcount=Db::name('store_goods as a')->where('a.status=0'.$where)->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$goods_result);        
        $sellerObj=Db::name('seller');
        $seller_result=$sellerObj->where('status=0')->select();
        $this->assign('seller',$seller_result);    
        return $this->fetch();

    }
}
