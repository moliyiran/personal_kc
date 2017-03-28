<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db;
use think\Session; 
class Order extends \app\index\controller\Common
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
        $orderObj=Db::name('purchase_order');
        $order_result=$orderObj->where('id>0')->limit(($now_page-1)*$page_per,$page_per)->order('id desc')->select();
        $totalcount=$orderObj->where('id>0')->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$order_result);             
        return $this->fetch();
    }
    public function add(){
        if(!empty($_POST)){
            $request = request()->param();
            if(!empty($request)){
                $order_data=array();
                $goods_data=array();
                $seller_data=array();
                $time=time();
                $order_php_amount=0;
                $order_rmb_amount=0;
                $order_php_amount_seller=array();
                $order_rmb_amount_seller=array();
                $goods_data=array();//echo json_encode($request);exit;
                foreach ($request['goods_id'] as $key => $value) {
                    $goods_id=(int)$value;
                    $goods_num=(int)$request['num'][$key];
                    $per_price=(float)$request['price'][$key];
                    $goods_unit=$per_price*(int)$request['switch_num'][$key];//大单位的单价
                    $fee_type=(int)$request['type'][$key];
                    $goods_amount=0;
                    $seller_id=(int)$request['sid'][$key];
                    if($goods_id&&$seller_id&&$goods_num){
                        if(!$request['buy_type'][$key]){//赠品不计算在内
                             if(!$order_php_amount_seller[$seller_id]){
                                $order_php_amount_seller[$seller_id]=0;
                            }
                            if(!$order_rmb_amount_seller[$seller_id]){
                                $order_rmb_amount_seller[$seller_id]=0;
                            }
                            $per_goods_amount=$goods_unit*$goods_num;
                            if($request['type'][$key]){//0中国1本地
                                $order_php_amount_seller[$seller_id] +=$per_goods_amount;
                                $order_php_amount +=$per_goods_amount;
                            }else{
                                $order_rmb_amount_seller[$seller_id] +=$per_goods_amount;
                                $order_rmb_amount +=$per_goods_amount;
                            }                             
                        }
                      
                        $goods_data[]=array(
                            'goods_id'=>$goods_id,
                            'num'=>$goods_num,
                            'currency_type'=>(int)$request['type'][$key],
                            'per_fee'=>$per_price,
                            'seller_id'=>$seller_id,
                            'buy_type'=>(int)$request['buy_type'][$key],
                            'spec'=>(int)$request['switch_num'][$key]
                        );                      
                    }
                }
                if(!empty($goods_data)){
                    //a.订单数据
                    $order_info=array();
                    $order_info['order_No']=date('YmdHis').rand(100000,999999);
                    $order_info['php_amount']=$order_php_amount;
                    $order_info['rmb_amount']=$order_rmb_amount;
                    $order_info['admin_id']=12;//Session::get('admin_id');
                    $order_info['admin_name']='admin';//Session::get('admin_name');
                    $order_info['create_time']=$time;
                    Db::startTrans();
                    try{
                        $order_id=Db::name('purchase_order')->insert($order_info);
                        //b.订单商品
                        foreach ($goods_data as $key => $value) {
                            if($value){
                                $goods_data[$key]['order_id']=$order_id;
                            }
                        }
                        Db::name('purchase_order_goods')->insertAll($goods_data);
                        //c. 供应商帐单
                        $seller_info=array();
                        foreach ($order_php_amount_seller as $key => $value) {
                            $seller_info[]=array(
                                'order_id'=>$order_id,
                                'seller_id'=>$key,
                                'php_order_amount'=>$value,
                                'rmb_order_amount'=>$order_rmb_amount_seller[$key]
                            );
                        }
                        Db::name('purchase_seller_fee')->insertAll($seller_info);   
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
                       
            return $this->fetch();           
        }

    }
    public function show(){
        $request = request()->param();
        $id=(int)$request['id'];
        if($id){
            $orderObj=Db::name('purchase_order');
            /*
            $join = [
                ['rep_purchase_order_goods b','a.id = b.order_id','LEFT'],
                ['rep_purchase_seller_fee c','a.id = c.order_id','LEFT'],
            ];
            */
            $order_result=$orderObj->where('id='.$id)->find();
            if($order_result){
                $seller_result=Db::table('rep_purchase_seller_fee')->alias('a')->join('rep_seller s','a.seller_id = s.id','LEFT')->where('a.order_id='.$id)->field('a.*,s.name sname')->select();
                $goods_result=Db::table('rep_purchase_order_goods')->alias('a')->join('rep_goods g','a.goods_id = g.id','LEFT')->field('a.*,g.name gname,g.name_en gname_en,g.goods_No')->where('a.order_id='.$id)->select();
                $this->assign('order',$order_result);
                $this->assign('order_goods',$goods_result);
                $this->assign('order_seller',$seller_result);
                return $this->fetch(); 
            }
             
        }
    }
    public function purchaseGoodsAdd(){

        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $goodsObj=Db::table('rep_goods');
        $where='';
        $goods_val=$_POST['goods_name'] ? addslashes(trim($_POST['goods_name'])) : '';
        if($goods_val&&$this->inject_check($goods_val)){
            exit;
        }
        $seller_val=(int)$_POST['seller'];
        if($goods_val){
            $where .=" and (a.name like '%".$goods_val."%' or a.name like '%".$goods_val."%' )";
            $this->assign('goods_name',$_POST['goods_name']);
        }
        if($seller_val){
            $where .=" and a.seller=".$seller_val;
            $this->assign('seller_id',$seller_val);
        }
        $goodsObj=Db::table('rep_goods');
        $join = [
            ['rep_seller b','a.seller = b.id','LEFT'],
            ['rep_unit u','a.unit_tiny = u.id','LEFT'],
            ['rep_unit u2','a.unit_big = u2.id','LEFT'],
        ];     
        $goods_result=$goodsObj->alias('a')->where('a.status=0'.$where)->join($join)->field('a.*,b.name bname,b.type btype,u.name uname,u2.name u2name')->limit(($now_page-1)*$page_per,$page_per)->order('id desc')->select();
        $totalcount=Db::name('goods as a')->where('a.status=0'.$where)->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$goods_result);        
        $sellerObj=Db::name('seller');
        $seller_result=$sellerObj->where('status=0')->select();
        $this->assign('seller',$seller_result);    
        return $this->fetch();

    }
}
