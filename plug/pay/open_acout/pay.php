<?php 
if(!defined('IN_WaiMai')) {
	exit('Access Denied');
}
if(Mysite::$app->config['open_acout'] != 1){
			$errdata['url'] = $payerrlink;
				$errdata['reason'] = '网站未开启支付，不能支付s';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata); 
}
if($userid == 0){
		        $errdata['url'] = $payerrlink;
				$errdata['reason'] = '未登录账号不可使用余额支付';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);  
}
if($this->member['cost'] < $orderinfo['allcost']){
			    $errdata['url'] = $payerrlink;
				$errdata['reason'] = '账号余额不足支付此订单';
				$errdata['paysure'] = false;  
				$this->showpayhtml($errdata);   
}
/*
$paypwd = IFilter::act(IReq::get('paypwd'));
if(empty($this->member['safepwd'])){
 $link = IUrl::creatUrl('member/paylog/'); 
$this->message('余额支付请先设置支付密码',$link);  
} 
$checkmd5 = md5($paypwd);
if($checkmd5 != $this->member['safepwd']){
	$link = IUrl::creatUrl('site/waitpay/orderid/'.$orderid); 
$this->success('支付密码错误',$payerrlink);  
}*/
//更新用户数据
$this->mysql->update(Mysite::$app->config['tablepre'].'member','`cost`=`cost`-'.$orderinfo['allcost'],"uid ='".$this->member['uid']."' ");
//更新订单数据 
$orderdata['paystatus'] = 1;
if($orderinfo['status'] == 0){
   $orderdata['status'] = 1;
   
} 
$orderdata['paytype_name'] = $dopaydata['paydotype'];
$this->mysql->update(Mysite::$app->config['tablepre'].'order',$orderdata,"id ='".$orderid."' ");

$accost = $this->member['cost']-$orderinfo['allcost'];
$this->memberCls->addlog($this->member['uid'],2,2,$orderinfo['allcost'],'余额支付订单','支付订单'.$orderinfo['dno'].'帐号金额减少'.$orderinfo['allcost'].'元', $accost);
$this->memberCls->addmemcostlog($orderinfo['buyeruid'],$this->member['username'],$this->member['cost'],2,$orderinfo['allcost'],$accost,"下单余额消费",$this->member['uid'],$this->member['username']);


$orderCLs = new orderclass();

	 $orderCLs->writewuliustatus($orderinfo['id'],3,$orderinfo['paytype']);  //在线支付成功状态	 
	 if($orderinfo['is_make']  == 1 ){
		 $orderCLs->writewuliustatus($orderinfo['id'],4,$orderinfo['paytype']);  //商家自动确认接单	  
		  $auto_send = Mysite::$app->config['auto_send'];
			  if($auto_send == 1){
				 $orderCLs->writewuliustatus($orderinfo['id'],6,$orderinfo['paytype']);//订单审核后自动 商家接单后自动发货
			  }
	 }

$orderCLs->sendmess($orderid); 
$errdata['url'] = $payerrlink;
$errdata['reason'] = $orderinfo;
$errdata['paysure'] = true;  
$errdata['source'] = $dopaydata['source'];
$this->showpayhtml($errdata); 
exit;
?>