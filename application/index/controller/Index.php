<?php
namespace app\index\controller;
use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
class Index extends \app\index\controller\Common
{
	public $dbinfo='';
    protected $beforeActionList = [
	    //'lay_do',
	];
    public function index()
    {
    	//$this->view->engine->layout(false);
		return $this->fetch();

    }
    public function hh(){
        phpinfo();
    }
    public function lay_do(){
        if(empty($_SESSION['admin_id'])){
            $this->redirect('/Index/Check/login');exit;
        }        
        //210.14.16.162/122.53.158.2
        $db=config('dbinfo');
        $this->dbinfo=new MSSql($db['host'],$db['db'],$db['user'],$db['pwd']);
    	$this->view->engine->layout('admin');
    }
    public function productBySid(){
        $request = Request::instance();

        $sid=$request->only(['stock_id']);

        if($sid){
            $sql="select CommID from MICommStockInv where StockId='".$sid['stock_id']."'";

            $result=$this->dbinfo->query($sql);
            if($result){
                $str='';
                foreach ($result as $key => $value) {
                    $str .='<option value="'.$value['CommID'].'">'.$value['CommID'].'</option>';
                }
                exit($str);
            }

        }        
    }
    public function stock(){
        //SELECT TOP 30 * FROM ARTICLE WHERE ID NOT IN(SELECT TOP 45000 ID FROM ARTICLE ORDER BY YEAR DESC, ID DESC) ORDER BY YEAR DESC,ID DESC 

        //$now_page=$_GET['page'] ? (int)trim($_GET['page']) : 1;

        header("content-type:text/html;charset=utf-8");

        //$_SESSION['admin_id']='110244'; 

        $request = Request::instance();

        $page=$request->only(['page']);
        if($page)
        $now_page=$page['page'] ? (int)$page['page'] : 1;
        else
         $now_page=1;
        $this->assign('page',$now_page);
        $page_size=30;
        //$page_size=6;
        $new_begin=($now_page-1)*$page_size;

       $stock=$request->only(['stock']);

       $pro=$request->only(['pro']);

       $str='';

       if(!empty($stock))

            $stock=$stock['stock'];

        else

            $stock='';

       if(!empty($pro))

            $pro=$pro['pro'];

        else
            $pro='';

       $search='';
       $param='';

       if($stock&&!$this->inject_check($stock)){
            //$search.=' and kc.StockName like "%'.trim($stock).'%"';

            //$search.=" and kc.StockID ='".trim($stock)."'";

            $search.="SupId='".$_SESSION['admin_id']."' and StockId='".$stock."'";

            
            //$search.=" and kc.StockID ='".trim($stock)."'";



            $param.='/stock/'.$stock;
            
       }
       if($pro&&!$this->inject_check($pro)){
            //$search.=' and sp2.CommName like "%'.trim($pro).'%"';
            $search.=" and CommID='".$pro."'";
            $param.='/pro/'.$pro;

       }
        $stock_sql="select * from MIStock";
        $stock_info=$this->dbinfo->query($stock_sql); 
        $this->assign('stock_list',$stock_info);      
       if(!$stock){
            $stock='';
            $pro='';
            $list='';

            $this->assign('page_str','');
            $this->assign('stock','');
            
            $this->assign('str',$str); 
            $this->assign('pro',$pro);
      
            $this->assign('list',$list);
            // 把分页数据赋值给模板变量list

            return $this->fetch();

            exit;
       }
        //$id_sql="select CommID from MICommStockInv where StockId='".$sid['stock_id']."'";


       //$page_size=5; 

        //$top_size=6;

        $count_sql="select count(CommID) as counter from MICommStockInv where (".$search.")";

        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter'];
        $page=new PageSql('/Index/index/stock'.$param,$count,$now_page,$page_size);
        $page_num=$page->get_page_num();
        if($now_page>=$page_num){
            $now_page=$page_num;
            $top_size=$count-($page_num-1)*30;
        }else{
            $top_size=$page_size;
        }
        $this->assign('page_str',$page->page_str());          

        

        $sql="select top ".($page_size*$now_page)." CommID,QPurInv,QFine from MICommStockInv where ".$search." order by CommID desc";


         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by CommID desc";            
 
        if($now_page==1){
            $sql="select top ".($page_size*2)." CommID,QPurInv,QFine from MICommStockInv where ".$search."  order by CommID desc";


             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID desc) e order by CommID desc";   
        }                              
       /*
        $count_sql="select count(sp1.CommID) as counter from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.")"; 
        
        //$count_sql="select top 1 * from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.")"; 



        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter'];
        $page=new PageSql('/Index/index/stock'.$param,$count,$now_page); 

        $page_num=$page->get_page_num();
        if($now_page>=$page_num){
            $now_page=$page_num;
            $top_size=$count-($page_num-1)*30;
        }else{
            $top_size=$page_size;
        }
        */
        //$top_size=$page_size*$now_page;
        //echo $top_size.'=='.($page_size*$now_page).'=='.$page_num;

        //$this->assign('page_str',$page->page_str());          
        //$sql="select top ".($page_size*$now_page)." CommID,QPurInv,QFine from MICommStockInv where (StockId=".$stock.") order by CommID desc";
        /*
        $sql="select top ".($page_size*$now_page)." CommID,QPurInv,QFine from MICommStockInv where (".$search.") order by CommID desc";

         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by e.CommID desc";
         //echo $sql.'<br>';
         //echo $sql_1;exit;
         
        if($now_page==1){

            $sql="select top ".($page_size*2)." CommID,QPurInv,QFine from MICommStockInv where (".$search.") order by CommID desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID  desc) e order by e.CommID desc";   
        }
           
        /*

        $sql="select top ".($page_size*$now_page)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
        //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc



         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by e.CommID desc,e.StockId desc";            
  
  
        //$sql_1="select top ".$page_size." * from(".$sql.") s order by s.CommID asc,s.StockId asc";     

        //echo $sql_1;   

        if($now_page==1){

            $sql="select top ".($page_size*2)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc



             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID desc) e order by e.CommID desc";   
        }
        */
        
        $list=$this->dbinfo->query($sql_1);

        $str='';

        if($list){
            
            foreach ($list as $key => $data) {
                if($data['CommID']){
                    $a_sql="select top 1 CommName,InvUnit from MICommMain where CommID=".$data['CommID'];
                    $info=$this->dbinfo->query($a_sql);
                    if($info){
                        $name=$info[0]['CommName'];
                        $dw=$info[0]['InvUnit'];
                    }else{
                        $name='';
                        $dw='';
                    }
                    $str.='<tr>'.
                    '<td>'.$data['CommID'].'</td>'
                    .'<td>'.$name.'</td>'
                    .'<td>'.$data['QPurInv'].'</td>'
                    .'<td>'.$data['QFine'].'</td>'
                    .'<td>'.$dw.'</td>'
                    .'<td>---</td>'
                    .'</tr>';                   
                }

            }
                     
        }
        $this->assign('str',$str); 
        
        $this->assign('stock',$stock);

        $this->assign('pro',$pro);
  
        $this->assign('list',$list);
        // 把分页数据赋值给模板变量list

        return $this->fetch();
        

    }          
    public function stock2(){
        //SELECT TOP 30 * FROM ARTICLE WHERE ID NOT IN(SELECT TOP 45000 ID FROM ARTICLE ORDER BY YEAR DESC, ID DESC) ORDER BY YEAR DESC,ID DESC 

        //$now_page=$_GET['page'] ? (int)trim($_GET['page']) : 1;

        header("content-type:text/html;charset=utf-8");

        //$_SESSION['admin_id']='110244'; 

        $request = Request::instance();

        $page=$request->only(['page']);
        if($page)
        $now_page=$page['page'] ? (int)$page['page'] : 1;
        else
         $now_page=1;
        $this->assign('page',$now_page);
        $page_size=30;
        $page_size=6;
        $new_begin=($now_page-1)*$page_size;

       $stock=$request->only(['stock']);

       $pro=$request->only(['pro']);

       $str='';

       if(!empty($stock))

            $stock=$stock['stock'];

        else

            $stock='';

       if(!empty($pro))

            $pro=$pro['pro'];

        else
            $pro='';

       $search='';
       $param='';

       if($stock&&!$this->inject_check($stock)){
            //$search.=' and kc.StockName like "%'.trim($stock).'%"';

            //$search.=" and kc.StockID ='".trim($stock)."'";

            $search.="SupId='".$_SESSION['admin_id']."' and StockId='".$stock."'";

            
            //$search.=" and kc.StockID ='".trim($stock)."'";



            $param.='/stock/'.serialize($stock);
            
       }
       if($pro&&!$this->inject_check($pro)){
            //$search.=' and sp2.CommName like "%'.trim($pro).'%"';
            $search.=" and CommID='".$pro."'";
            $param.='/stock/'.serialize($pro);

       }
        $stock_sql="select * from MIStock";
        $stock_info=$this->dbinfo->query($stock_sql); 
        $this->assign('stock_list',$stock_info);      
       if(!$stock){
            $stock='';
            $pro='';
            $list='';

            $this->assign('page_str','');
            $this->assign('stock','');
            
            $this->assign('str',$str); 
            $this->assign('pro',$pro);
      
            $this->assign('list',$list);
            // 把分页数据赋值给模板变量list

            return $this->fetch();

            exit;
       }
        //$id_sql="select CommID from MICommStockInv where StockId='".$sid['stock_id']."'";


       $page_size=5; 

       $top_size=5;

        $count_sql="select count(CommID) as counter from MICommStockInv where (".$search.")";

        echo $count_sql; 

        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter']; echo $count;exit;              
       /*
        $count_sql="select count(sp1.CommID) as counter from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.")"; 
        
        //$count_sql="select top 1 * from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.")"; 



        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter'];
        $page=new PageSql('/Index/index/stock'.$param,$count,$now_page); 

        $page_num=$page->get_page_num();
        if($now_page>=$page_num){
            $now_page=$page_num;
            $top_size=$count-($page_num-1)*30;
        }else{
            $top_size=$page_size;
        }
        */
        //$top_size=$page_size*$now_page;
        //echo $top_size.'=='.($page_size*$now_page).'=='.$page_num;

        //$this->assign('page_str',$page->page_str());          
        //$sql="select top ".($page_size*$now_page)." CommID,QPurInv,QFine from MICommStockInv where (StockId=".$stock.") order by CommID desc";
        $sql="select top ".($page_size*$now_page)." CommID,QPurInv,QFine from MICommStockInv where (".$search.") order by CommID desc";

         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by e.CommID desc";
         echo $sql.'<br>';
         echo $sql_1;exit;
         /*
        if($now_page==1){

            $sql="select top ".($page_size*2)." CommID,QPurInv,QFine from MICommStockInv where (".$search.") order by CommID desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID  desc) e order by e.CommID desc";   
        }
           
        /*

        $sql="select top ".($page_size*$now_page)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
        //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc



         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by e.CommID desc,e.StockId desc";            
  
  
        //$sql_1="select top ".$page_size." * from(".$sql.") s order by s.CommID asc,s.StockId asc";     

        //echo $sql_1;   

        if($now_page==1){

            $sql="select top ".($page_size*2)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc



             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID desc) e order by e.CommID desc";   
        }
        */
        /*
        $list=$this->dbinfo->query($sql_1);

        $str='';

        if($list){
            
            foreach ($list as $key => $data) {
                if($data['CommID']){
                    $a_sql="select top 1 CommName,InvUnit from MICommMain where CommID=".$data['CommID'];
                    $info=$this->dbinfo->query($a_sql);
                    if($info){
                        $name=$info[0]['CommName'];
                        $dw=$info[0]['InvUnit'];
                    }else{
                        $name='';
                        $dw='';
                    }
                    $str.='<tr>'.
                    '<td>'.$data['CommID'].'</td>'
                    .'<td>'.$name.'</td>'
                    .'<td>'.$data['QPurInv'].'</td>'
                    .'<td>'.$data['QFine'].'</td>'
                    .'<td>'.$dw.'</td>'
                    .'<td>---</td>'
                    .'</tr>';                   
                }

            }
                     
        }
        $this->assign('str',$str); 
        
        $this->assign('stock',$stock);

        $this->assign('pro',$pro);
  
        $this->assign('list',$list);
        // 把分页数据赋值给模板变量list

        return $this->fetch();
        */

    }    
    public function stock1(){
        //SELECT TOP 30 * FROM ARTICLE WHERE ID NOT IN(SELECT TOP 45000 ID FROM ARTICLE ORDER BY YEAR DESC, ID DESC) ORDER BY YEAR DESC,ID DESC 
        //$now_page=$_GET['page'] ? (int)trim($_GET['page']) : 1;
        header("content-type:text/html;charset=utf-8");
        //$_SESSION['admin_id']='110244'; 
        $request = Request::instance();
        $page=$request->only(['page']);
        if($page)
        $now_page=$page['page'] ? (int)$page['page'] : 1;
        else
         $now_page=1;
        $page_size=30;
        $new_begin=($now_page-1)*$page_size;

       $stock=$request->only(['stock']);

       $pro=$request->only(['pro']);



       if(!empty($stock))

            $stock=$stock['stock'];

        else

            $stock='';

       if(!empty($pro))

            $pro=$pro['pro'];

        else
            $pro='';

       $search='';
       $param='';
       if($stock&&!$this->inject_check($stock)){
            $search.=' and kc.StockName like "%'.trim($stock).'%"';
            $param.='/stock/'.serialize($stock);
            
       }
       if($pro&&!$this->inject_check($pro)){
            $search.=' and sp2.CommName like "%'.trim($pro).'%"';
            $param.='/stock/'.serialize($pro);

       }
        $count_sql="select top 1 count(sp1.CommID) as counter from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.")"; 
        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter'];
        $page=new PageSql('/Index/index/stock'.$param,$count,$now_page); 
        $page_num=$page->get_page_num();
        if($now_page>=$page_num){
            $now_page=$page_num;
            $top_size=$count-($page_num-1)*30;
        }else{
            $top_size=$page_size;
        }
        //echo $top_size.'=='.($page_size*$now_page).'=='.$page_num;
        $this->assign('page_str',$page->page_str());          

        

        $sql="select top ".($page_size*$now_page)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
        //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID asc) e order by e.CommID desc,e.StockId desc";            
  
  
        //$sql_1="select top ".$page_size." * from(".$sql.") s order by s.CommID asc,s.StockId asc";     
        //echo $sql_1;   
        if($now_page==1){
            $sql="select top ".($page_size*2)." sp1.QPurInv,sp1.QFine,sp1.CommID,sp2.CommName,sp2.SupID,kc.StockName,kc.StockId from MICommStockInv sp1,MICommMain sp2,MIStock kc where (sp1.CommID=sp2.CommID and sp1.StockId=kc.StockId and sp1.SupID='".$_SESSION['admin_id']."' and sp1.isStockUnit=1".$search.") order by sp1.CommID desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.CommID desc) e order by e.CommID desc";   
        }
        $list=$this->dbinfo->query($sql_1);
        
        $this->assign('stock',$stock);

        $this->assign('pro',$pro);
  
        $this->assign('list',$list);
        // 把分页数据赋值给模板变量list
        return $this->fetch();
    }
    public function add_order(){

        $stock_sql='select stockid,StockName from MIStock';

        $stock_list=$this->dbinfo->query($stock_sql);

        $this->assign('stock_list',$stock_list);       

        return $this->fetch();
    }
    public function pro_list(){
        $this->view->engine->layout(false);
        $request = Request::instance();
        $stockid=$request->only(['stock_id']);     
        $stockid=$stockid['stock_id'];
        $sp_data=array();
        $ex_arr=array();
        $sp_data=$request->only(['data']);
        if($sp_data){
            $ex_data=$sp_data['data'];
            
            if($ex_data){
                $ex_arr=explode(',', trim($ex_data));
            }           
        }

        if($stockid&&!$this->inject_check($stockid)){
            $sql="select a.CommID,a.CommName,a.InvUnit,b.PTRefPur from MICommMain a,MICommStockInv b where a.CommID=b.CommID and a.SupID='".$_SESSION['admin_id']."' and b.StockId='".$stockid."' and b.isstockunit=1";
            $str='';
            $list=$this->dbinfo->query($sql);
            /*
            if($list){
                
                foreach ($list as $key => $value) {
                    $value['CommName']=str_replace('"', '', $value['CommName']);
                    if(in_array($value['CommID'], $ex_arr)){
                        $str.='<li><label><input type="checkbox" name="prod" value="'.$value['CommName'].'_'.$value['CommID'].'" checked>'.str_replace('"', '', $value['CommName']).'</label></li>';
                    }else{
                        $str.='<li><label><input type="checkbox" name="prod" value="'.$value['CommName'].'_'.$value['CommID'].'">'.str_replace('"', '', $value['CommName']).'</label></li>';                       
                    }

                }                             
            }
            */
          
            $this->assign('ex_arr',$ex_arr);
            $this->assign('n_list',$list);
            //$this->assign('str',$str);
            return $this->fetch();
          
        }
    }


    public function deal_order(){
        $request = Request::instance();
        $data=$request->post();
        if(empty($data['stock'])||empty($data['sum_total'])||!is_numeric($data['sum_total'])||empty($data['pro'])){
            $this->error('请正确选择订单产品');
        }
        $billNo=$data['stock'].'-'.time();

        $supInfo=$_SESSION['supInfo'];

        //$supInfo=array("AllName"=>'test',"ShortName"=>'test');
       
        
        $stock_id=$data['stock'];
        $order_main=array();
        $order_main['SupID']=$_SESSION['admin_id'];
        $order_main['StockId']=$data['stock'];
        $order_main['StockName']=str_replace("'",'"',$data['stock_name']);
        $order_main['AllName']=str_replace("'",'"',$supInfo['AllName']);
        $order_main['ShortName']=str_replace("'",'"',$supInfo['ShortName']);
        $order_main['LinkMan']=$order_main['AllName'];
        $order_main['CTel1']=0;
        $order_main['CFax']=0;
        $order_main['CEMail']=0;
        $order_main['Tel']=0;
        $order_main['Fax']=0;
        //$order_main['CFax']=0;
        $order_main['BillNo']=$billNo;
        $order_main['BillNoID']=date('YmdHis').$_SESSION['admin_id'].$data['stock'].time();
        $order_main['RecordCnt']=count($data['pro']);
        //$order_main['DCreate']=(string)date("M").' '.(string)date("d").' '.(string)date('Y').' '.(string)date('H:i:s:msA');
        //$t_time="to_date('".date('Y-m-d H:i:s')."','yyyy-mm-dd')";
        $t_time=date('Y-m-d H:i:s');//Oct  1 2044 12:00:00:000AM
        $order_main['DCreate']=$t_time;
        $order_main['DBill']=$t_time;//(string)$t_time;
        $order_main["MainAmtPur"]=$data['sum_total'];
        $order_main["MainAmtPurTax"]=0;
        $order_main["MainAmtTPur"]=$data['sum_total'];  
        $order_main["Status"]= 0;
        $order_main['BillType'] =iconv("utf-8","GB2312//IGNORE",'供应商');//iconv('utf-8', 'GBK//IGNORE', '供应商');;
        $order_main['EmployeeID'] =0;
        $order_main['EmployeeName'] =0;
        $order_main['CloseType'] =0;
        $order_main['LockStatus'] =0;
        $order_main['MCreate'] =$t_time;
        $order_main['MModify'] =$t_time;
        $order_main['DCreate']=$t_time;
        if(!empty($data['beizu']))
        $order_main['MainNotes']=iconv('utf-8', 'GB2312//IGNORE', $data['beizu']);
        //$order_main['LockID'] =0;
        /*            
        $order_main_sql="insert into mipomain(SupID,StockId,StockName,AllName,ShortName,BillNo,BillNoID,RecordCnt,MainAmtPur,MainAmtPurTax,MainAmtTPur,Status,DCreate,DBill,BillType)values('".$order_main['SupID']."','".$order_main['StockId']."','".$order_main['StockName']."','".$order_main['AllName']."','".$order_main['ShortName']."','".$order_main['BillNo']."','".$order_main['BillNoID']."','".$order_main['RecordCnt']."','".$order_main["MainAmtPur"]."','".$order_main["MainAmtPurTax"]."','".$order_main["MainAmtTPur"]."',".$order_main["Status"].",'".$order_main['DCreate']."','".$order_main['DBill']."','".."')";ff
        */
        $key_str=array();
        $va_str=array();
        foreach ($order_main as $key => $value) {
            $key_str[]=$key;
            /*
            if(in_array($key,array('MCreate','MModify','DCreate'))){
                $va_str[]="'Jul 15 2014 11:47:58:080AM'";
            }else{
                $va_str[]="'".$value."'";
            }
            */
           
            if(in_array($key,array('BillType','MainNotes'))){
                //$va_str[]="N'".$value."'";
                $va_str[]="N'".$value."'";
            }else{
                $va_str[]="'".$value."'";
            }
           
            //$va_str[]="'".$value."'";
        }
        $order_main_sql='insert into mipomain('.implode(',', $key_str).')values('.implode(',', $va_str).')';
        
        //$order_main_sql=iconv('utf-8', 'GBK//IGNORE', $order_main_sql);//
        
        $all_pro_info=array();
        $sort_num=1;
        foreach ($data['pro'] as $key => $value) {
            $unit_str='';
            if(empty($value)){
                continue;
            }
            $order_goods=array();
            $insert_info=array();
            $goods_sql="select top 1 a.* from MICommMain a where a.CommID='".$value."' and a.SupID='".$_SESSION['admin_id']."'";
            $order_goods=$this->dbinfo->query($goods_sql);

            if(empty($order_goods)){
                continue;
            }
           $order_goods=$order_goods[0];
            $insert_info["BillItemNo"]="'".$order_main['BillNoID'].$sort_num."'";
            $insert_info["ItemNo"]="'".$sort_num."'";
            $insert_info['BillNoID']="'".$order_main['BillNoID']."'";
            $insert_info['CommID']="'".$value."'";
            $insert_info['CommName']="'". str_replace("'",'"',$order_goods['CommName'])."'";
            $insert_info['ShortName']="'".str_replace("'",'"',$order_goods['ShortName'])."'";
            $insert_info['CommDesc']="'".str_replace("'",'"',$order_goods['CommDesc'])."'";
            $insert_info['LabelName']="'".str_replace("'",'"',$order_goods['LabelName'])."'";

            $insert_info["SourceDesc"]="'".str_replace("'",'"',$order_goods['SourceDesc'])."'";
            $insert_info['CommGrade']="'".$order_goods['CommGrade']."'";
            $insert_info['BCode']=0;

            $insert_info['CommGrade']="'".$order_goods['CommGrade']."'";
            $insert_info["CommTypeId"]="'".$order_goods['CommTypeID']."'";
            $insert_info["InvUnit"]=0;
            $insert_info["PRefPur"]="'".$data['unit'][$value]."'";
            $insert_info["PTRefPur"]="'".$data['unit'][$value]."'";
            $insert_info["ItemNotes"]=0;
            $insert_info["SaleMode"]=0;
            $insert_info['FlgPoSchedule']=0;
            $insert_info['DPRec']=0;

            $insert_info['Qty']="'".$data['num'][$value]."'";
            $insert_info['invqty']=$insert_info['Qty'];
            $insert_info['itemAmtTPur']="'".($data['num'][$value]*$data['unit'][$value])."'";
            $insert_info['itemAmtPur']=$insert_info['itemAmtTPur'];
            $insert_info['ConvRate']=0;
            $insert_info['PurTaxRate']=0;
            $insert_info['itemAmtPurTax']=0;


            $sort_num++;
            $unit_str='('.implode(',',$insert_info).')';
            $all_pro_info[]=$unit_str;
        }
        if(empty($all_pro_info)){
            $this->error('请确认订单内容正确');
        }
        $insert_key=array();
        foreach ($insert_info as $key => $value) {
            $insert_key[]=$key;      
        }       
        $order_goods_sql="insert into mipoitem(".implode(',', $insert_key).")values".implode(',',$all_pro_info);
        if($this->dbinfo->sw(array($order_main_sql,$order_goods_sql))){
            $this->success('订单提交成功','/Index/index/order_list');
        }else{
            $this->error('订单提交失败');
        }
        /*
        $pro_hander=$this->dbinfo->procedure("P__Test_GetData");
        $userId = 1; // 定义输入参数   
        $userName; // 定义返回值  
        //$stmt = mssql_init("P__Test_GetData", $conn) or die("initialize stored procedure failure");  
        mssql_bind($pro_hander, "@userid", $userId, SQLINT4);  
        mssql_bind($pro_hander, "@username", $userName, SQLVARCHAR, true);  
        $rs = mssql_execute($pro_hander, false);
        mssql_free_statement($pro_hander);  
        echo "This user name is: ".$userName; 
        */ 
    }
    public function order_list(){
        $request = Request::instance();
        $page=$request->only(['page']);
        if($page)
        $now_page=$page['page'] ? (int)$page['page'] : 1;
        else
         $now_page=1;
        $page_size=30;
        $new_begin=($now_page-1)*$page_size;        
        $count_sql="select top 1 count(BillNo) as counter from mipomain where SupID='".$_SESSION['admin_id']."' and Status=1"; 
        $counter=$this->dbinfo->query($count_sql);
        $count=$counter[0]['counter'];
        $page=new PageSql('/Index/index/order_list',$count,$now_page); 
        $page_num=$page->get_page_num();
        if($now_page>=$page_num){
            $now_page=$page_num;
            $top_size=$count-($page_num-1)*30;
        }else{
            $top_size=$page_size;
        }
        //echo $top_size.'=='.($page_size*$now_page).'=='.$page_num;
        $this->assign('page_str',$page->page_str());          

        

        $sql="select top ".($page_size*$now_page)." a.* from  mipomain a where a.SupID='".$_SESSION['admin_id']."' and  a.Status=1 order by a.DCreate desc";
        //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

         $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.DCreate asc) e order by e.DCreate desc";            
  
  
        //$sql_1="select top ".$page_size." * from(".$sql.") s order by s.CommID asc,s.StockId asc";     
        //echo $sql_1;   
        if($now_page==1){
            $sql="select top ".($page_size*2)." a.* from  mipomain a where a.SupID='".$_SESSION['admin_id']."' and  a.Status=1  order by a.DCreate desc";
            //exit($sql); and sp1.CommID not in(select top ".$new_begin." CommID from MICommMain order by CommID desc)),sp1.StockId desc

             $sql_1="select * from  (select top ".$top_size." * from(".$sql.") s order by s.DCreate desc) e order by e.DCreate desc";   
        }
        $list=$this->dbinfo->query($sql_1);
        
  
        $this->assign('list',$list);
        // 把分页数据赋值给模板变量list
        return $this->fetch();
    }
    function order_info(){
        $request = Request::instance();
        $oid=$request->only(['oid']);
        $oid=$oid['oid'];
        if(empty($oid)||$this->inject_check($oid)){
            return;
        }
        $count_sql="select top 1  * from mipomain where SupID='".$_SESSION['admin_id']."' and BillNoID='".$oid."'";
        $info=$this->dbinfo->query($count_sql);
         $count_sql="select * from mipoitem where BillNoID='".$oid."'"; 
        $list=$this->dbinfo->query($count_sql);
        $str='';

        if($list){
            
            foreach ($list as $key => $data) {
                if($data['CommID']){
                    $a_sql="select top 1 InvUnit from MICommMain where CommID=".$data['CommID'];
                    $infog=$this->dbinfo->query($a_sql);
                    if($info){
                        //$name=$info[0]['CommName'];
                        $dw=$infog[0]['InvUnit'];
                    }else{
                        //$name='';
                        $dw='';
                    }
                    $str.='<tr>'.
                    '<td>'.$data['CommID'].'</td>'
                    .'<td>'.$data['CommName'].'</td>'
                    .'<td>'.$data['Qty'].'</td>'
                    .'<td>'.$data['PTRefPur'].'</td>'
                    .'<td>'.$dw.'</td>'
                    .'</tr>';                   
                }

            }
                  
        }
        $this->assign('str',$str);         
        $this->assign('info',$info[0]);
        //$this->assign('list',$list);
        return $this->fetch();               
    }


    function order_print(){
        $request = Request::instance();
        $oid=$request->only(['oid']);
        $oid=$oid['oid'];
        if(empty($oid)||$this->inject_check($oid)){
            return;
        }
        $count_sql="select top 1  * from mipomain where SupID='".$_SESSION['admin_id']."' and BillNoID='".$oid."'";
        $info=$this->dbinfo->query($count_sql);//var_dump($info[0]);
       
         $count_sql="select * from mipoitem where BillNoID='".$oid."'"; 
        $list=$this->dbinfo->query($count_sql);
        $str='';

        if($list){
            $num_show=1;
            foreach ($list as $key => $data) {
                if($data['CommID']){
                    $a_sql="select top 1 InvUnit from MICommMain where CommID=".$data['CommID'];
                    $infog=$this->dbinfo->query($a_sql);
                    if($info){
                        //$name=$info[0]['CommName'];
                        $dw=$infog[0]['InvUnit'];
                    }else{
                        //$name='';
                        $dw='';
                    }
                    $str.='<tr>'.
                    '<td align="center">'.$num_show.'</td>'
                    .'<td align="center">'.$data['CommID'].'</td>'
                    .'<td align="center">'.$data['CommName'].'</td>'
                    .'<td align="center">'.$data['CommNotes'].'</td>'
                    .'<td align="center">'.$data['Qty'].'</td>'
                    .'<td align="center">'.$dw.'</td>'
                    .'<td align="center">'.$data['PTRefPur'].'</td>'
                    .'<td align="center">'.($data['PTRefPur']*$data['Qty']).'</td>'
                    .'</tr>';                    
                    $num_show++;                   
                }

            }
            $str.='<tr>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">&nbsp;</td>'
            .'<td align="center">TOTAL:'.$info[0]['MainAmtTPur'].'</td>'
            .'</tr>';             
                  
        }
        $this->assign('str',$str);         
        $this->assign('info',$info[0]);
        //$this->assign('list',$list);
        return $this->fetch(); 
                    
    }    
}
