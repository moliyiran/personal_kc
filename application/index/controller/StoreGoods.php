<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db;
use think\Session; 
class StoreGoods extends \app\index\controller\Common
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
    public function index(){
        $storeObj=Db::name('store');
        $store_result=$storeObj->where('status=0')->order('id DESC')->select();
        $str='';
        foreach ($store_result as $key => $value) {
            $str .='<option value="'.$value['id'].'">'.$value['name'].'</option>';
        }
        $this->assign('store_result',$str);        
        return $this->fetch();
    }
    public function order(){
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
        $request = request()->param();
        $data=array();
        if(!empty($request['goods_id'])){
            for($i=0;$i<count($request['goods_id']);$i++){
                if($request['goods_id'][$i]){
                    $goods_data=array();
                    $goods_data['name']=$request['name'][$i];
                    $goods_data['name_en']=$request['name_en'][$i];
                    $goods_data['num']=(int)$request['num'][$i];
                    $goods_data['spec']=$request['switch_num'][$i];
                    $goods_data['store_id']=$request['store'][$i];
                    $goods_data['zone_id']=$request['store_zone'][$i];
                    $goods_data['seller_id']=$request['sid'][$i]; 
                    $goods_data['orig_price']=$request['price'][$i]; 
                    $goods_data['sell_price']=$request['price1'][$i];
                    $goods_data['vip_price']=$request['price2'][$i];
                    $goods_data['good_id']=$request['goods_id'][$i];
                    $goods_data['order_id']=$request['order_id'][$i];
                }
                if(!empty($goods_data)){
                    $data[]=$goods_data;
                }
            }
            if(!empty($data)){
                Db::name('store_goods')->insertAll($data);
                suc();
            }
        }
        err();
    }
    public function goods(){
        $now_page=$_POST['pageNum'] ? (int)$_POST['pageNum'] : 1;
        $page_per=PAGEPERNUM;
        $goodsObj=Db::table('rep_store_goods');
        $where='';
        $goods_val=$_POST['goods_name'] ? addslashes(trim($_POST['goods_name'])) : '';
        if($goods_val&&$this->inject_check($goods_val)){
            exit;
        }
        $seller_val=(int)$_POST['seller'];
        $store_val=(int)$_POST['store'];
        if($goods_val){
            $where .=" and (a.name like '%".$goods_val."%' or a.name like '%".$goods_val."%' )";
            $this->assign('goods_name',$_POST['goods_name']);
        }
        if($seller_val){
            $where .=" and a.seller_id=".$seller_val;
            $this->assign('seller_id',$seller_val);
        }
        if($store_val){
            $where .=" and a.store_id=".$store_val;
            $this->assign('store_id',$store_val);
        }        
        //$goodsObj=Db::table('rep_goods');
        $join = [
            ['rep_goods g','a.good_id = g.id','LEFT'],
            ['rep_seller b','a.seller_id = b.id','LEFT'],
            ['rep_store u','a.store_id = u.id','LEFT'],
            ['rep_store_zone z','a.zone_id = z.id','LEFT'],
        ];     
        $goods_result=$goodsObj->alias('a')->where('a.status=0'.$where)->join($join)->field('a.*,b.name bname,b.type btype,u.name uname,z.name zname,g.barcode,g.goods_No')->limit(($now_page-1)*$page_per,$page_per)->order('id desc')->select();
        $totalcount=Db::name('store_goods as a')->where('a.status=0'.$where)->count();
        $this->assign('nowpage',$now_page); 
        $this->assign('total',$totalcount);  
        $this->assign('result',$goods_result);        
        $sellerObj=Db::name('seller');
        $seller_result=$sellerObj->where('status=0')->select();
        $storeObj=Db::name('store');
        $store_result=$storeObj->where('status=0')->select();        
        $this->assign('seller',$seller_result); 
        $this->assign('store',$store_result);   
        return $this->fetch();     
    }
    public function store_zone(){
        $request = request()->param();
        $id=(int)$request['id'];        
        if($id){
            $goodsObj=Db::name('store_zone');
    
            $goods_result=$goodsObj->where('store_id='.$id)->select();
            if($goods_result){
                echo json_encode(array('status'=>1,'data'=>$goods_result));
            }           
        }         
    }
    public function order_detail(){
        $request = request()->param();
        $id=(int)$request['id'];        
        if($id){
            $goodsObj=Db::table('rep_purchase_order_goods');
            $join = [
            ['rep_goods b','a.goods_id = b.id','LEFT'],
            ['rep_seller s','a.seller_id = s.id','LEFT']
            ];     
            $goods_result=$goodsObj->alias('a')->where('a.order_id='.$id)->join($join)->field('a.*,b.name bname,b.goods_No,b.unit_tiny,b.unit_big,s.name sname')->select();
            if($goods_result){
                $unitObj=Db::name('unit');
                                
                foreach ($goods_result as $key => $value) {
                    $unit_result=$unitObj->where('id='.$value['unit_tiny'])->find();
                    $unit_result1=$unitObj->where('id='.$value['unit_big'])->find();
                    $goods_result[$key]['tiny']=$unit_result['name'];
                    $goods_result[$key]['big']=$unit_result['name'];
                }
            }
            if($goods_result){
                echo json_encode(array('status'=>1,'data'=>$goods_result));
            }           
        }       
    }

}    