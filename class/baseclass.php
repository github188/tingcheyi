<?php 

/**
 * @class baseclass 
 * @描述   基础类
 */
class baseclass
{
	 public $mysql;
	 public $memberCls;
	 public $member;
	 public $pageCls;
	 public $admin;
	 public $digui;
	 function init(){
	 	     //主要是检测权限 
	 	     $controller = Mysite::$app->getController();
	 	     $this->mysql =  new mysql_class(); 
	 	     $this->memberCls = new memberclass($this->mysql); 
	 	     $this->member = $this->memberCls->getinfo();  
	 	     $this->pageCls = new page();
	 	     $this->admin =  $this->memberCls->getadmininfo();  
	 	     $this->digui = array();//递归处理数组
	 	     $data['member'] = $this->member; 
	 	     $data['admininfo'] = $this->admin; 
	 	     $checkmodule =  $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."module  where name='".$controller."' and install=1 limit 0,20");  
	 	     if(empty($checkmodule) && !in_array($controller,array('site','market'))){ 
	 	         $this->message('未安装此模版'); 
	 	     }  
	 	     $openid =   IFilter::act(IReq::get('openid'));  //openid='.$this->obj->FromUserName.'&='.$time.'&= 
		   	  $actime =  IFilter::act(IReq::get('actime')); 
		   	  if(!empty($openid) && !empty($actime)){
		   	  	if($controller == 'wxsite'){
		   	     $sign =  IFilter::act(IReq::get('sign')); 
		   	    $mycode = Mysite::$app->config['wxtoken'];
		   	    $checkstr = md5($mycode.$actime);
		   	    $checkstr = substr($checkstr,3,15); 
		   	     
		   	    if($checkstr == $sign && !empty($openid)){
		   	   	 
		   	   	  $checkinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where openid ='".$openid."' ");
		   	   	  if(!empty($checkinfo)){
		   	   	       ICookie::set('logintype','wx',86400);
		   	   	       ICookie::set('wxopenid',$openid,86400);
		   	   	       $backinfo = IFilter::act(IReq::get('backinfo')); 
		   	   	       if(empty($backinfo)){
		   	   	       $link = IUrl::creatUrl('wxsite/index'); 
		   	   	      }else{
		   	   	        	$newtr = '';
		   	   	         
		   	   	        	$testinfo = explode(',',$backinfo); 
		   	   	        	
                       foreach($testinfo as $key=>$value){
                       	if(!empty($value)){
                            $newtr .= chr($value);
                          }
                       }
                  
		   	   	      	$link = $newtr;
		   	   	       
		   	   	      	if(empty($link)){
		   	   	      		 $link = IUrl::creatUrl('wxsite/index'); 
		   	   	      	}
		   	   	      }
		   	   	       
		   	   	      $this->message('',$link);
		   	      } 
		   	    } 
		   	  }
		   	 }
		   	 
	 	     $action = Mysite::$app->getAction();  
	 	     $data['moduleid']= $checkmodule['id']; 
	 	     $data['moduleparent'] = $checkmodule['parent_id']; 
	 	     $id = intval(IFilter::act(IReq::get('id'))); 
	 	     $data['id'] = $id; 
	 	   
	 	     Mysite::$app->setdata($data);  
	 } 
	 public function checkadminlogin(){
	 	 $link = IUrl::creatUrl('member/adminlogin'); 
	 	 if($this->admin['uid'] == 0) $this->message('未登陆',$link); 
	 }
	 public function checkmemberlogin(){
	 	 $link = IUrl::creatUrl('member/login'); 
	 	 if($this->member['uid'] == 0) $this->message('未登陆',$link); 
	 }
	 public function checkshoplogin(){
	 	 $link = IUrl::creatUrl('member/shoplogin'); 
	 	 if($this->member['uid'] == 0&&$this->admin['uid'] == 0)  $this->message('未登陆',$link); 
	 	 $shopid = ICookie::get('adminshopid');
	 	 if(empty($shopid)) $this->message('未登陆',$link); 
	 }
	 public static function sqllink($where,$sqlkey,$sqlvalue,$fuhao){
	 	  if(empty($sqlvalue)){
	 	     return  $where;
	 	  }else{
	 	  	 if(empty($where)){
	 	  	   return  '`'.$sqlkey.'`'.$fuhao.'\''.$sqlvalue.'\'';
	 	  	 }else{
	 	  	 	 return $where.' and `'.$sqlkey.'`'.$fuhao.'\''.$sqlvalue.'\'';
	 	  	 }
	 	  }
	   
	 }
	 public static function message($msg,$link=''){
	 
	 		$datatype = IFilter::act(IReq::get('datatype')); 
	 		if($datatype == 'json'){
	 			 //languagecls
	 			 $lngcls = new languagecls();
	 			 $msg = $lngcls->show($msg);
	 			 echo json_encode(array('error'=>true,'msg'=>$msg));  
	       exit; 
	 		}else{   
         self::refunction($msg,$link);
	 		} 
   }
   public static function refunction($msg,$info=''){
   	  $newrul = empty($info)?Mysite::$app->config['siteurl']:$info;
	    header("Content-Type:text/html;charset=utf-8"); 
	    if(!empty($msg))
	    {
	    	 $lngcls = new languagecls();
	 			 $msg = $lngcls->show($msg);
			   echo '<script>alert(\''.$msg.'\');location.href=\''.$newrul.'\';</script>';
		  }else{
		     echo '<script>location.href=\''.$newrul.'\';</script>';
	  	}
      exit;
   }
   public static function success($msg,$link=''){
   	   $datatype = IFilter::act(IReq::get('datatype')); 
	 		if($datatype == 'json'){
	 			 echo json_encode(array('error'=>false,'msg'=>$msg)); 
	       exit; 
	 		}else{
	 			 self::refunction($msg,$link); 
	 		}
    	
   }
   
	 public static function shopIsopen($is_open,$starttime,$is_orderbefore,$nowhour){ 
		  $find = 0 ;
		  $hfind =0;
		  $gotime ='';
		  $opentype = 0;
		  $endtime = '';
		  $checkstart = '';
		  $checkend = '';
		  if($is_open == 0){
		  	   $opentype = 4;//店铺休息
		  }else{
		 	if(empty($starttime)){
		 		  $opentype = 1;//已打烊
		 	}else{
		 		$foo = explode('|',$starttime); 
		 		foreach($foo as $key=>$value){
		 			
		 			if(!empty($value)){
		 				$mytime = explode('-',$value);
		 			 
		 				if(count($mytime) > 1){
		 				
		 					$time1 = strtotime($mytime[0]);
		 					$time2 = strtotime($mytime[1]);
		 				 
		 					if($nowhour > $time1 && $nowhour < $time2){
		 						$find = 1;
		 						$opentype = 2;//营业中 
		 						$gotime = empty($gotime)?$mytime[0]:$gotime;
		 						$endtime = !empty($mytime[1])?strtotime($mytime[1]):$endtime;
		 					}
		 					if($nowhour < $time2){
		 						$hfind = 1;
		 						$gotime = empty($gotime)?$mytime[0]:$gotime; 
		 						$checkstart = empty($checkstart)?strtotime($mytime[0]):$checkstart; 
		 					  $checkend = !empty($mytime[1])?strtotime($mytime[1]):$checkend;
		 					} 
		 				}
		 			}
		 		}
		 		if($opentype == 0){
		 		   if($is_orderbefore == 1&& $hfind ==1){
		 			   $opentype = 3;//3接受预定 
		 		   }
		 		} 
		 	}
		 } 
		 return array('opentype'=>$opentype,'newstartime'=>$gotime,'endtime'=>$endtime,'startoktime'=>$checkstart,'startendtime'=>$checkend); 
	 }
	 public function pscost($shopinfo,$goodsnum=0,$tareaid=0){
  	$backdata = array('pscost'=>0,'pstype'=>0,'canps'=>0);
  	$locationtype = Mysite::$app->config['locationtype'];
  	if($locationtype == 1){
  	  //地图模式
  	  $valuelist = empty($shopinfo['pradiusvalue'])? unserialize(Mysite::$app->config['radiusvalue']):unserialize($shopinfo['pradiusvalue']);
  	   $lat = ICookie::get('lat');  
  	  if(!empty($lat)){ 
  	       	  $lng = ICookie::get('lng');   
  	       	  $lat = empty($lat)?0:$lat;
  	       	  $lng = empty($lng)?0:$lng;
  	       	  $shoplat = isset($shopinfo['lat'])?$shopinfo['lat']:0;
  	       	  $shoplng = isset($shopinfo['lng'])?$shopinfo['lng']:0;
  	       	  $juli =  $this->GetDistance($lat,$lng, $shoplat,$shoplng, 1); 
  	       	  $pradiusvalue =  unserialize($shopinfo['pradiusvalue']);
  	       	  $juliceshi = intval($juli/1000);
  	       	  if(is_array($valuelist)){
  	       	  foreach($valuelist as $key=>$value){
  	       	    if($juliceshi == $key){
  	       	        $backdata['pscost'] = $value;
  	       	        $backdata['canps'] = 1;
  	       	    }
  	       	  }
  	       	} 
  	  }
  	  
  	}else{
  	  //区域模式
  	  //pradiusvalue	pscost
  	  $backdata['pscost'] = $shopinfo['pscost'];
  	  $areaid = ICookie::get('myaddress');
	  $areaid = $tareaid==0?$areaid:$tareaid;
  	  if(!empty($areaid)){  
  	  	$checkareainfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."areashop where areaid = ".$areaid." and shopid = ".$shopinfo['id'].""); 
  	  	if(!empty($checkareainfo)) $backdata['canps'] = 1;
  	  }
  	}
  	$backdata['pstype'] = $shopinfo['sendtype']; 
  	return $backdata; 
  }
  function  getOpenPosttime($is_before,$starttime,$postdate,$minit,$befortime=0){ 
	    $backarray = array('is_opentime'=>0,'is_posttime'=>'','is_postdate'=>'');
		$maxnowdaytime = strtotime(date('Y-m-d',time()));
		$daynottime = 24*60*60 -1; 
		$findpostime = false; 
		$posttime = time();
  		$miniday = $maxnowdaytime;
  		$maxday = $miniday+$daynottime; 
  	 
  		 
  	    $findps = false;
		$timelist = !empty($postdate)?unserialize($postdate):array();
		$data['pstimelist'] = array();
		$checknow = time();
		 $whilestatic = $befortime;
		$nowwhiltcheck = 0; 
		while($whilestatic >= $nowwhiltcheck){ 
		
		 
		    $nowstartcheck = $nowwhiltcheck*86400;
			foreach($timelist as $key=>$value){
				$docheck = $nowstartcheck+$value['s']; 
				if($docheck== $minit){
					$findps = true;
					$tempt['s'] = date('H:i',$miniday+$value['s']);
					$tempt['e'] = date('H:i',$miniday+$value['e']);
					$backarray['is_posttime'] = $nowstartcheck+$miniday+$value['s'];
					$backarray['is_postdate'] =  $tempt['s'] .'-'.$tempt['e'];
					$checkdotime = $nowstartcheck+$miniday+$value['s'];
					if($checkdotime < $posttime){
						$backarray['is_opentime'] = 3; 
					}
					break;
				} 
			}
			$nowwhiltcheck = $nowwhiltcheck+1;
		}
		if($findps ==  false){
			$backarray['is_opentime'] = 2; 
		}
		 
		 return $backarray;
		 
		 
	}
  
  //发送通知信息
  
   public function checkpsinfo(){
	 	 $link = IUrl::creatUrl('member/login'); 
	 	 if($this->member['uid'] == 0) $this->message('未登陆',$link); 
	 	 $link = IUrl::creatUrl('member/base');
	 	 if($this->member['group'] != 2) $this->message('不是配送员',$link); 
	}
  
  
   function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
  {
     
    	 $earth = 6378.137;
    	 $pi = 3.1415926;
       $radLat1 = $lat1 * PI ()/ 180.0;   //PI()圆周率
       $radLat2 = $lat2 * PI() / 180.0;
       $a = $radLat1 - $radLat2;
       $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
       $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
       $s = $s * EARTH_RADIUS;
       $s = round($s * 1000);
       if ($len_type > 1)
       {
           $s /= 1000;
       }
	 
       return round($s, $decimal);
   } 
}
?>