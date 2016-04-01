<?php 

/**
 * @class Cart
 * @brief 天气预报
 */
class memberclass
{
	private $error = ''; 
	private $uid = 0;
	 
	 //普通用户登录
	 
	protected $mysql; 
	 function __construct($mysql)
	 {
	 	$this->mysql = $mysql;
	 }
	 
  function login($uname,$pwd){
  	 $md5c = md5($pwd);
	   $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where (email='".$uname."' or username='".$uname."' or phone='".$uname."') and password = '".$md5c."' ");
	   if(empty($userinfo)){
	   	  $this->error = 'member_nouseroremail';
	      return false;//and password  = '".$md5c."'
	   }elseif($userinfo['password']  !=$md5c){
	   	  $this->error = 'emptypwd';
	   	  return false;
	   }else{
	   //更新用户信息 
	     
	      $data['loginip'] = IClient::getIp();
	      $data['logintime'] = time();
	      $checktime = date('Y-m-d',time());
	      $checktime = strtotime($checktime);
	      if($userinfo['logintime'] < $checktime)
	      {
	      	   if(Mysite::$app->config['loginscore'] > 0)
	      	   {
	      	      $data['score'] = $userinfo['score']+Mysite::$app->config['loginscore'];
	      	    
	             $mess['content'] = '用户登陆赠送积分'.Mysite::$app->config['loginscore'].'总积分'.$data['score'];
	           
               $this->addlog($userinfo['uid'],1,1,Mysite::$app->config['loginscore'],'每天登陆',$mess['content'],$data['score']);
	          }
	          if(Mysite::$app->config['login_juan'] ==1){//login_data
	 	        //登陆赠送积分
	 	            
	 	            $nowtime = time();
	 	            $endtime =  $nowtime+Mysite::$app->config['login_juanday']*24*60*60; 
	 	            $checktime = date('Y-m-d',$nowtime);
	 	            if($checktime == Mysite::$app->config['login_data']){
	 	            $juandata['card'] = $nowtime.rand(100,999);
                $juandata['card_password'] =  substr(md5($juandata['card']),0,5);	
                $juandata['status'] = 1;// 状态，0未使用，1已绑定，2已使用，3无效	
                $juandata['creattime'] = $nowtime;// 制造时间	
                $juandata['cost'] = Mysite::$app->config['login_juancost'];// 优惠金额	
                $juandata['limitcost'] =  Mysite::$app->config['login_juanlimit'];// 购物车限制金额下限	
                $juandata['endtime'] = $endtime;// 失效时间	
                $juandata['uid'] = $userinfo['uid'];// 用户ID	
                $juandata['username'] = $userinfo['username'];// 用户名	
                $juandata['name'] = '登陆赠送积分';//  优惠券名称 
	 	            $this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$juandata); 
	 	          }
	 	            
	 	            
	 	 
	 	        }
	      }
	      $this->mysql->update(Mysite::$app->config['tablepre'].'member',$data,"uid='".$userinfo['uid']."'");	 
	      
	      
	      $logintype = ICookie::get('adlogintype');
	 	    $token = ICookie::get('adtoken');
	 	    $openid = ICookie::get('adopenid'); 
	 	    if(!empty($logintype)){
	 	 	       $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	 	     if(!empty($apiinfo)){ 
	 	 	   	     $this->mysql->update(Mysite::$app->config['tablepre'].'oauth',array('uid'=>$userinfo['uid']),"openid='".$openid."' and type = '".$logintype."'");  
	 	 	   	     ICookie::clear('adlogintype'); 
	             ICookie::clear('adtoken');  
	             ICookie::clear('adopenid'); 
	 	 	     }
	 	    }
	 	   
	      /*** ***/
	      ICookie::set('email',$userinfo['email'],86400);
        ICookie::set('memberpwd',$pwd,86400);
        ICookie::set('membername',$userinfo['username'],86400);
        ICookie::set('uid',$userinfo['uid'],86400);
        $this->uid = $userinfo['uid'];
        return true;
  	 }
  }
  //普通用户注册
  function regester($email,$tname,$password,$phone,$group,$userlogo='',$address='',$cost=0,$score=0){  
  	if(empty($phone)){
  	  $this->error = '手机号不能为空';
  	  return false;
  	}
    if(!empty($email)){
     	if(!(IValidate::email($email)))
     	{
     		$this->error = 'erremail';
     		return false; 
     	}
     	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where email='".$email."' ");
      if(!empty($userinfo))
      {
        	$this->error = 'exitemail';
     		return false; 
      } 
    }
    if(!empty($phone)){
    	if(!(IValidate::suremobi($phone)))
     	{
     		$this->error = 'errphone';
     		return false; 
     	}
     	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$phone."' ");
      if(!empty($userinfo))
      {
        	$this->error = 'exitphone';
     	  	return false; 
      } 
    	
    }
  	if(!(IValidate::len($tname,3,20)))
    {
  		 $this->error = 'member_usernamelent3to30';
  		 return false; 
  	}  
    if(!(IValidate::len($password,6,20)))
    {
     	$this->error = 'member_pwdlen6to20';
  		return false; 
    }  
    
     $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where username='".$tname."' ");
     if(!empty($userinfo)){
     	$this->error = 'member_repeatname';
  		return false; 
     }  
     $arr['username'] = $tname;
     $arr['phone'] = $phone;
     $arr['address'] = $address;
     $arr['password'] = md5($password);
     $arr['email'] = $email;
     $arr['creattime'] = time(); 
     $arr['score']  = $score == 0?Mysite::$app->config['regesterscore']:$score;
     $arr['logintime'] = time(); 
     $arr['logo'] = $userlogo;
     $arr['loginip'] = IClient::getIp();
     $arr['group'] = $group;
     $arr['cost'] = $cost; 
     $arr['parent_id'] = intval(ICookie::get('logincode'));  
     $this->mysql->insert(Mysite::$app->config['tablepre'].'member',$arr);   
     $this->uid = $this->mysql->insertid();
     
     if($arr['score'] > 0){
        $this->addlog($this->uid,1,1,$arr['score'],'注册送积分','注册送积分'.$arr['score'],$arr['score']);
     }
     
     
     $logintype = ICookie::get('adlogintype');
	 	 $token = ICookie::get('adtoken');
	 	 $openid = ICookie::get('adopenid'); 
	 	 if(!empty($logintype)){
	 	 	   $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	 	   if(!empty($apiinfo)){
	 	 	   	//更新
	 	 	   	  $tempuid = $this->uid;
	 	 	   	  $this->mysql->update(Mysite::$app->config['tablepre'].'oauth',array('uid'=>$tempuid),"openid='".$openid."' and type = '".$logintype."'"); 
	          ICookie::set('logintype',$logintype,86400);  
	 	 	   }
	 	 }
	 	 if(Mysite::$app->config['regester_juan'] ==1){
	 	   //注册送优惠券
	 	   $nowtime = time();
	 	   $endtime = $nowtime+Mysite::$app->config['regester_juanday']*24*60*60;
	 	   $juandata['card'] = $nowtime.rand(100,999);
       $juandata['card_password'] =  substr(md5($juandata['card']),0,5);	
       $juandata['status'] = 1;// 状态，0未使用，1已绑定，2已使用，3无效	
       $juandata['creattime'] = $nowtime;// 制造时间	
       $juandata['cost'] = Mysite::$app->config['regester_juancost'];// 优惠金额	
       $juandata['limitcost'] =  Mysite::$app->config['regester_juanlimit'];// 购物车限制金额下限	
       $juandata['endtime'] = $endtime;// 失效时间	
       $juandata['uid'] = $this->uid;// 用户ID	
       $juandata['username'] = $arr['username'];// 用户名	
       $juandata['name'] = '注册账号赠送优惠券';//  优惠券名称 
	 	   $this->mysql->insert(Mysite::$app->config['tablepre'].'juan',$juandata); 
	 	 
	 	 }
	 	 
	 	 
     
     
  	return true; 	
  }
  function modify($array,$uid){
  	    $savedata = $array;
      	$testinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");   
			  if(empty($testinfo)) 
			  {
			  	$this->error = 'member_noexit';
     		return false; 
			  } 
			  if(isset($array['password'])){
			  	 if(empty($array['password'])) 
			     {
			    	   unset($savedata['password']);
			     }else{
			  	    $savedata['password'] =md5($savedata['password']);
			     } 
			  }   
			  if(isset($array['phone']) && $array['phone'] != $testinfo['phone']){
			    //检测移动电话
			    $checkuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where phone='".$array['phone']."' ");
			    if(!empty($checkuser))
			    {
			        $this->error = 'exitphone';
     	      	return false; 
			     }  
			  }
			   if(isset($array['username']) && $array['username'] != $testinfo['username']){
			    //检测移动用户名
			    $checkuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where username='".$array['username']."' ");
			     if(!empty($checkuser)){
			      $this->error = 'member_repeatname';
     	      	return false; 
			     } 
			  }
			   if(isset($array['email']) && $array['email'] != $testinfo['email']){
			    //检测邮箱
			       $checkuser = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where email='".$array['email']."' ");
			       if(!empty($checkuser)){
			          $this->error = 'exitemail';
     	        	return false; 
			       } 
			  }
	     $this->mysql->update(Mysite::$app->config['tablepre'].'member',$savedata,"uid='".$uid."'");	 
	     return true; 	
  }
  function getuid(){
  	return $this->uid;
  } 
  //检测用户名是否注册
  function checkname($uname){ 
  	if(empty($uname)){
  			$this->error = 'member_emptyname';
  	return false;
  	}
  	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where username='".$uname."' ");
  	if(empty($userinfo)){
  	return true;
  	}else{
  			$this->error = 'member_repeatname';
  	return false;
  	}
  }
  function checkemail($uname){
  	if(empty($uname)){
  		  $this->error = 'erremail';
     	return false;
  	}
  	$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where email='".$uname."' ");
  	if(empty($userinfo)){
  	   return true;
  	}else{
  		$this->error = 'exitemail';
  	   return false;
  	}
  }
  //退出登录
  function  loginout(){
  	  ICookie::clear('membername'); 
	    ICookie::clear('memberpwd');  
	    ICookie::clear('email'); 
      ICookie::clear('uid'); 
      ICookie::clear('logintype');
  }
  //获取用户信息
	function getinfo(){
	     $uid = ICookie::get('uid');    
	 	   $password = ICookie::get('memberpwd');   
	 	   $logintype = ICookie::get('logintype');  
	 	   $userinfo = array();  
	 	    if(!empty($logintype)){
	 	  	if($logintype == 'wx'){
	 	  		 $wxopenid  = ICookie::get('wxopenid');  
	 	  		 $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."wxuser where openid='".$wxopenid."'  ");
	 	  		 $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$apiinfo['uid']."' ");  
					
				if(!empty($apiinfo) && empty($userinfo)){
					 //写入局到
					  
					    $arr['username'] = $apiinfo['openid'];
						$arr['phone'] = '';
					    $arr['carnumber'] = '';
						$arr['address'] = '';
						$arr['password'] = md5($apiinfo['openid']);
						$arr['email'] = '';
						$arr['creattime'] = time(); 
						$arr['score']  =0;
						$arr['logintime'] = time(); 
						$arr['logo'] = '';
						$arr['loginip'] ='';
						$arr['group'] = 10; 
						$arr['uid'] = $apiinfo['uid'];
						$this->mysql->insert(Mysite::$app->config['tablepre'].'member',$arr);  
						$cuid = $this->mysql->insertid();
						$this->mysql->update(Mysite::$app->config['tablepre'].'wxuser',array('uid'=>$cuid),"openid='".$apiinfo['openid']."'");								
						$userinfo = arr;
										
					 
				}
					

if($userinfo['username'] == $apiinfo['openid']){
					   $userinfo['username'] = '微信用户';
				  }
				/*   if($userinfo['username'] == $apiinfo['openid']){
					   $userinfo['username'] = '微信用户';
				  } */
				 
	 	  	}else{
	 	  	  $apiinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."otherlogin where loginname='".$logintype."'  ");
	 	  	  if(empty($apiinfo)){
	 	  		$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' and password  = '".md5($password)."'"); 
	 	  	  }else{
	 	  		$userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' "); 
	 	  	  } 
	 	  	}
	 	   }else{ 
	 	  	//获取微信消息
	 	  	 $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' and password  = '".md5($password)."'"); 
	 	   } 
	 	  if(empty($userinfo)||!isset($userinfo['uid'])){
	 	      $userinfo = array('uid'=>0,'username'=>'guest','group'=>'2');
	    }
	    //获取用户成长值等级
      $gradelink = hopedir.'/config/membergrade.php';
	    if(file_exists($gradelink)){
	         $tempinfo = include(hopedir.'/config/membergrade.php'); 
	         if($userinfo['uid'] == 0){
	           $userinfo['gradename'] = '游客'; 
	         }else{
	         	 $check =   false;
	         	 foreach($tempinfo as $key=>$value){
	         	     if($userinfo['total'] >= $value['from'] && $userinfo['total'] < $value['to']){
	         	     	 $check = true;
	         	     	 $userinfo['gradename'] = $value['gradename'];
	         	     	 $userinfo['gradeinstro'] = $value['from'].'-'.$value['to'];
	         	       break;
	         	     }
	         	 }
	         	 if($check == false){
	         	     $userinfo['gradename'] = '未定义';
	         	     	 $userinfo['gradeinstro'] = $userinfo['total'];
	         	 }
	         }
	    }else{
	       $userinfo['gradename'] = '未定义'; 
	    }
	    
	   
	    
	    
	    
	    
      return $userinfo;
	}	
	function getadmininfo(){
		  $adminname =  ICookie::get('adminname'); 
	    $adminpwd = ICookie::get('adminpwd');  
	    $adminuid = ICookie::get('adminuid');  
	    $userinfo = array(); 
	    if(!empty($adminuid)){
	 	  	  $userinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."admin where uid='".$adminuid."' and password  = '".md5($adminpwd)."'"); 
	 	  	  $userinfo['group'] = $userinfo['groupid'];
	 	  } 
	 	  if(empty($userinfo)||!isset($userinfo['uid'])){
	 	      $userinfo = array('uid'=>0,'username'=>'guest','group'=>'2');
	    }
	    return $userinfo;
	}  
	function findpwd($uname){
	  if(!(IValidate::email($uname))){
	  	$this->error='erremail';
	    return false;
	  } 
     $memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where email='".$uname."' ");
     if(empty($memberinfo))
     {
     	 $this->error='member_noexit';
	     return false;
     } 
     $title = '找回'.Mysite::$app->config['sitename'].'帐号密码'; 
     $sign = $this->mymd5($memberinfo['password'],$memberinfo['username']);
     $emaildata['findpwdurl'] = IUrl::creatUrl("member/resetpwd/uid/".$memberinfo['uid']."/username/".urlencode($memberinfo['username'])."/sign/".$sign);
     
     $default_tpl = new config('tplset.php',hopedir);  
	 	 $tpllist = $default_tpl->getInfo(); 
	 	 if(!isset($tpllist['emailfindtpl']) || empty($tpllist['emailfindtpl'])) {
	 	 	$this->error='管理员未设置邮箱发送信息,请联系客服';
	    return false;
	 	 } 
	 	 $emaildata['username'] = $memberinfo['username'];
	 	 $emaildata['email'] = $memberinfo['email']; 
	 	 $emaildata['sitename'] = Mysite::$app->config['sitename'];
	 	 $emaildata['siteurl'] = Mysite::$app->config['siteurl'];  
	 	 $smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
     $content =  Mysite::$app->statichtml($tpllist['emailfindtpl'],$emaildata); 
    
     $info = $smtp->send($memberinfo['email'], Mysite::$app->config['emailname'],$title,$content , "" , "HTML" , "" , "");  
     return true;
	}
	//邮箱找回密码
	function resetpwd($username,$sign,$uid,$newpwd,$newpwd2){
		
		 if(empty($uid)){
			 $this->error='member_noexit';
	     return false;
		 } 
     $memberinfo = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$uid."' ");
     if(empty($memberinfo)){
    	  $this->error='member_noexit';
	      return false;
     }   
     $checksign = $this->mymd5($memberinfo['password'],$memberinfo['username']);
     if($checksign != $sign)
     {
     	  $this->error='member_emailyan';
	      return false;
     }   
      if(!empty($newpwd))
      {
      	if($newpwd == $newpwd2)
      	{
      		  $arr['password'] = md5($newpwd);
      		  $this->mysql->update(Mysite::$app->config['tablepre'].'member',$arr,"uid='".$memberinfo['uid']."'");
      		  $this->error ='ok';
      		  return true;
      	}else{
      		$this->error='member_twopwdnoequale';
      		 
      	} 
      }
		
		return true;
		
	}
	/*
	*param 说明:
	  userid 用户ID   type 1表示积分2表示资金
	  addtype 1表示增加2表示减少
	  result 表示 结果
	*/
	function addlog($userid,$inputype=1,$addtype=1,$result=0,$logtitle='',$logcontent='',$allcost=0){
		if(empty($userid)){
		  $this->error('member_noexit');
		  return false;
		}
		$data['userid'] =  $userid;
		$data['type'] = $inputype;
		$data['addtype'] = $addtype;
		$data['result'] = $result;
		$data['addtime'] = time();
		$data['title'] = $logtitle;
		$data['content'] = $logcontent;  
		$data['acount'] = $allcost;
		$this->mysql->insert(Mysite::$app->config['tablepre'].'memberlog',$data);
		return true;
		//id userid   type                result     addtime
	}
	
	
	/*
	*param 说明:
	 id	uid 用户uid	username 用户名	cost 剩余金额	bdtype 变动类型 1为增加 2为减少	bdcost 本次充值/扣除金额	
	 curcost 当前金额	bdliyou 变动原因	czuid 操作用户uid	czusername 操作用户名称	addtime 操作日期
	*/
	function addmemcostlog($uid,$username,$cost,$bdtype,$bdcost,$curcost,$bdliyou,$czuid,$czusername){
		if(empty($uid)){
		  $this->error('member_noexit');
		  return false;
		}
		$data['uid'] =  $uid;
		$data['username'] =  $username;
		$data['cost'] =  $cost;
		$data['bdtype'] =  $bdtype;
		$data['bdcost'] =  $bdcost;
		$data['curcost'] =  $curcost;
		$data['bdliyou'] =  $bdliyou;
		$data['czuid'] =  $czuid;
		$data['czusername'] =  $czusername;
		$data['addtime'] =  time();
		
		$this->mysql->insert(Mysite::$app->config['tablepre'].'memcostlog',$data);
		return true;
 	}
	
	
	
	
	 function mymd5($string1,$string2)
  {
	//string1 = email,
	  $kk = md5($string1);
	  $kk = md5($kk.$string2);
	  return  substr($kk,6,15); 
  }
  public function ero(){
  	return $this->error;
  }
}
?>
