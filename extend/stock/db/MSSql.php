<?php
namespace stock\db;

class MSSql{
  private $Host;    //连接数据库服务器
      private $Database;//连接数据库主机名
      private $UID;     //连接数据库用户名
      private $PWD;     //连接数据库密码
      public $conn;
      
      /**
       * @author XL
       *析构函数，完成数据成员的初始化
       */
      
      function __construct($Host='',$Database='',$UID='',$PWD=''){
        $this->conn = mssql_connect($Host,$UID,$PWD) or die ("can not connect");
        mssql_select_db($Database,$this->conn); 
      }
      function query($sql,$type=''){
        $AdminResult=mssql_query($sql);
        $num = mssql_num_rows($AdminResult);
        $data=array();
        if($num){
            //循环出每一条记录
            for($i=0;$i<$num;$i++) {
               //这里是一条记录的信息，你也可以循环显示出来，这里只输出第一第二条
                $Row=mssql_fetch_object($AdminResult);
                $data[]=(array)$Row;
            } 
        }
        if($type){
            $new_data['data']=$data;
            unset($data);
            $new_data['count']=$num;
        }else{
            $new_data=$data;
            unset($data);           
        }
        return $new_data;        
      }
      function procedure($pr_name){
        return $pre_hander=mssql_init($pr_name,$this->conn) or die("initialize stored procedure failure");
      }

      function sw($sql_array=array()){
          if(empty($sql_array))return;
        
          mssql_query("BEGIN TRANSACTION DEPS02_DEL"); //开始事务

          for ($i=0; $i < count($sql_array); $i++) { 
            
            $sql_array[$i]=iconv("GBK", "UTF-8", $sql_array[$i]); 
            //echo $sql_array[$i].'<br>======';
            mssql_query($sql_array[$i]); //操作数据库
          }//exit;
          
          if(true){    //判断是否回滚提交
            return $result=mssql_query("COMMIT TRANSACTION DEPS02_DEL"); //提交事务
          
          }else{
            mssql_query("ROLLBACK TRANSACTION DEPS02_DEL"); //回滚事务
            return false;
          }      
      }
      function __destruct(){
        mssql_close($this->conn);
      }
}