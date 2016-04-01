<?php 
//说明支付宝配置接口文件
if(!defined('IN_WaiMai')) {
	exit('Access Denied');
}
 $setinfo = array(
        'name'=>'支付宝',
        'apiname'=>'alipay',
        'logourl'=>'alipay.gif',
        'forpay'=>1,
  ); 
 $plugsdata = array(
  	'0'=> array('title'=>'合作者ID',	'name'=>'partner',	'desc'=>''),
  	'1'=> array('title'=>'安全检验码key',	'name'=>'key',	'desc'=>''),
  	'2'=> array('title'=>'支付宝账号',	'name'=>'seller_email',	'desc'=>'') 
  );
   
?>