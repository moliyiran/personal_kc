<?php
namespace stock\other;

class PageSQL{

	public $url='';
	public $count=0;
	public $page_size=30;
	public $page_show=9;
	public $now_page='';
	public $page_num;
	public $last_page;

	public function __construct($url,$count,$now_page='1',$page_size=30,$page_show=9){
		$this->url=$url;
		$this->page_size=$page_size;
		$this->page_show=$page_show;
		$this->count=$count;
		$this->now_page=$now_page;
	}
	public function get_page_num(){
		$this->page_num=floor(($this->count-1)/$this->page_size)+1;
		return $this->page_num;
	}
	public function switch_now_page(){
		if($this->now_page<1){
			$this->now_page=1;
		}else if($this->now_page>$this->page_num){
			$this->now_page=$this->page_num;
		}		
	}
	public function page_str(){
		$this->get_page_num();
		$this->switch_now_page();
		$first_str='<li><a href="'.$this->url.'/page/1">first</a></li>';
		
		$middle_str=$this->middle_str();
		return $first_str.$middle_str;
	}
	private function middle_str(){
		$result_str='';
		$b_str='';
		$e_str='';
		$now_str='<li><a style="color:grey;">'.$this->now_page.'</a></li>';
		$page_fact_num=0;
		if($this->now_page>1){
			$b_count=1;
			$b_arr=array();
			for($i=$this->now_page-1;$i>0;$i--){
				if($b_count<5){
					array_unshift($b_arr,'<li><a href="'.$this->url.'/page/'.$i.'">'.$i.'</a></li>');
				}else{
					break;
				}
				$b_count++;
			}
			$b_str=implode('', $b_arr);
		}
		if($this->now_page<$this->page_num){
			$end_num=$this->page_num-$this->now_page;
			$end_num=$end_num>4 ? 4 : $end_num; 

			for ($i=$this->now_page+1; $i < $this->now_page+$end_num+1; $i++) { 
				$e_str.='<li><a href="'.$this->url.'/page/'.$i.'">'.$i.'</a></li>';
				$page_fact_num=$i;
			}			
		}
		$more_str='';
		if($this->page_num>$page_fact_num){
			$more_str='<li><a href="'.$this->url.'/page/'.($page_fact_num+1).'">More>></a></li>';
		}
		$last_str='<li><a href="'.$this->url.'/page/'.$this->page_num.'">last</a></li>';
		$result_str=$b_str.$now_str.$e_str.$more_str.$last_str.'<li><a>Total:'.$this->count.'/'.$this->page_num.'pages</a></li>';
		return $result_str;
		
	}

}	