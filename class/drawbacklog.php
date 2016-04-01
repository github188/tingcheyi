<?php 

/**
 * @class drawbacklog
 * @brief 退款申请类 
 $obj = get_class($usercls); //获取类名
 print_r($obj); 
 */
class drawbacklog
{
	private $error = ''; 
	private $orderid = ''; 
	private $logtype = array('0'=>'退款到支付宝','1'=>'退款到账户');
	private $typeidlist = array('0','1');
	private $actionclas; 
	private $drawdata; 
	protected $mysql; 
	/**
	 *  @brief Brief
	 *  
	 *  @param [in] $mysql 数据库操作
	 *  @param [in] $usercls 传用户类 操作'
	 *  @return Return_Description
	 *  
	 *  @details Details
	 */
	function __construct($mysql,$usercls){
	 	$this->mysql =$mysql;  
		$this->actionclas = $usercls;//memberclass   //print_r($this->actionclas->getadmininfo());//获取管理员信息   getinfo//获取  
	}
	public function setsavedraw($data){  // 
	/* 			$drawdata = array();
				$drawdata['allcost'] = $checkorderinfo['allcost'];
				$drawdata['orderid'] = $checkorderinfo['id'];
				$drawdata['reason'] =  '商家取消订单';
				$drawdata['content'] = '商家取消订单,由于某原因不能制作次订单';
				$drawdata['typeid'] = 1;  //商家直接同意退款
				*/
		$this->drawdata = $data;
		return $this;
	}
	//添加退款记录
	public function save(){ 
	if($this->drawdata  == null ){
		$allcost =  IFilter::act(IReq::get('allcost')); 
		$orderid = intval(IFilter::act(IReq::get('orderid')));    // 订单号
		$data['reason'] = trim(IFilter::act(IReq::get('reason'))); //退款原因
		$data['content'] = trim(IFilter::act(IReq::get('content'))); //退款详细内容说明
		$typeid = intval(IFilter::act(IReq::get('typeid'))); //支付类型
	}else{
		$allcost =  $this->drawdata['allcost']; 
		$orderid =  $this->drawdata['orderid'];;    // 订单号
		$data['reason'] = $this->drawdata['reason'];; //退款原因
		$data['content'] = $this->drawdata['content'];; //退款详细内容说明
		$typeid = $this->drawdata['typeid'];; //支付类型
		
		
	}
		if(!in_array($typeid,$this->typeidlist)){
			$this->error = '未定义退款类型';
			return false;
		}
		if(empty($data['reason'])){
			$this->error = '未选择退款原因';
			return false;
		}
	 
		$orderinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."order where id='".$orderid."' "); 		
		if(empty($orderinfo)){
				$this->error = '订单不存在';
				return false;
		}  
	 
		if($orderinfo['allcost'] != $allcost ) { $this->error = "退款金额错误";return false; }
		$memberinfo = $this->GetMem();
			 
		if($orderinfo['buyeruid'] != $memberinfo['uid']){
				$this->error = '购买用户和用户不一致';
				return false;
		}
		if($orderinfo['paystatus'] != 1){
				$this->error = '该订单未支付';
				return false;
		}
		if($orderinfo['status'] < 1 && $orderinfo['status'] < 3){
				$this->error = '订单状态不能申请退款';
				return false;
		} 
		if($orderinfo['paytype'] == 0||empty($orderinfo['paytype'])){
				$this->error = '货到支付订单';
				return false;
		}
		if(!empty($orderinfo['is_reback'])){
				$this->error = '已申请退款';
				return false;
		}
		 
		 if(empty($data['content'])){
			 $this->error = '请填写退款详细内容说明';
			 return false;
		 } 
	 
		$checklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid='".$orderinfo['id']."' "); 
		if(!empty($checklog)){
			$this->error = '已申请过退款';
			return false;
		}
		$data['orderid'] = $orderinfo['id'];
		$data['shopid'] = $orderinfo['shopid'];
		$data['uid'] = $memberinfo['uid'];
		$data['username'] = $memberinfo['username'];
		$data['status'] = 0;
		$data['addtime'] = time();
		$data['cost'] = $orderinfo['allcost'];
		$data['admin_id'] = $orderinfo['admin_id'];
		$data['type'] = $typeid;
		$this->mysql->insert(Mysite::$app->config['tablepre'].'drawbacklog',$data);   
		$udata['is_reback'] = 1;
		$this->mysql->update(Mysite::$app->config['tablepre'].'order',$udata,"id='".$orderinfo['id']."'"); 
		$ordCls = new orderclass();
		$ordCls->writewuliustatus($orderinfo['id'],13,$orderinfo['paytype']);  // 管理员 操作配送发货
		return true;
	}
	//返回错误
	public function GetErr(){
		return $this->error;
	}  
	//返回退款类型
	public function gettype(){
		return $this->logtype;
		
	} 
	/**
	 *  @brief Brief
	 *  
	 *  @param [in] $type 操作类型  0-同意退款  1表示取消退款
	 *  			$role 操作者   admin-后台管理员  areaadmin-区域管理员  shop-店铺
	 *  			$roleid 操作者ID   $role=admin-后台管理ID   $role=areaadmin-区域管理员uid   $role=shop-店铺所有者ID 
	 *  @return true/false;
	 *  
	 *  @details Details
	*/
	public function control($type=0,$role='uid',$roleid='0',$orderid){  
		$drawbacklog = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."drawbacklog where orderid=".$orderid." order by  id desc  limit 0,2");
		if(empty($drawbacklog)){
				$this->error = '退款记录为空';
				return false;
		}
		if($drawbacklog['status'] != 0){
			$this->error = '退款记录已处理过';
			return false;
		}
		if($type == 0){
		    if($role == 'uid'){
				//店铺  同意
				if($drawbacklog['type']==0){
					$this->error = '退款到支付宝需要网站后台处理';
					return false;
				} 
			}
			$data['bkcontent'] = IReq::get('reasons');
			$data['status'] = 2;//
			$this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'");  
		   
		}elseif($type==1){
			$data['status'] = 3;//
			$this->mysql->update(Mysite::$app->config['tablepre'].'drawbacklog',$data,"id='".$drawbacklog['id']."'"); 
		}
		return true; 
	}  
	
	private function CkMem(){
		$memberinfo = $this->GetMem;
	}
	private function CkAdmin(){
		$memberinfo = $this->GetAdmin;
	}
	//返回用户信息
	private function GetMem(){
		return $this->actionclas->getinfo();
	}
	//放回 管理员信息
	private function GetAdmin(){
		return $this->actionclas->getadmininfo();
	}  
}
?>