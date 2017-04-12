<?php
namespace app\index\controller;
//use stock\db\MSSql;
use stock\other\PageSql;
use \think\Request;
use think\Db;
use think\Session; 
class Goodsstore extends \app\index\controller\Common
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
        return $this->fetch();
    }

}    