<?php 

/**
 * @class mobile
 * @brief 发送短信
 调用说明
 $sendmsg = new mobile();
 $check = $sendmsg->login();
 if($check == 'ok')
{
//获取账号余额代码

     $check->getBalance();
//发送短信代码
 sendmsg->sendsms(array(),'内容');
}

$sendmsg->endsend();//每次处理完成后需要退出
 */
class mobile
{
	 private $gwUrl = 'http://ib100.net.id12129.aliasl1.doctoryun.net/sendmsg.php?';//网关地址
   private $serialNumber; //序列号,请通过亿美销售人员获取 0SDK-EMY-0130-JBQMR
   private $password;//密码销售人员获取 
	 private $contrlname;
	 private $otherlink ='';
	 
	  function __construct(){ 	 
	  	header("Content-Type: text/html; charset=UTF-8");
	  	$this->serialNumber =  Mysite::$app->config['smacount'];
	  	$this->password =  Mysite::$app->config['smpassword'];  
   }
   
   /*
     login() 账号登陆 //判断是否获取正常标志位
    */
   function bklink(){ 
   	  $weblink = $this->gwUrl.'otype='.$this->contrlname.'&smacount='.$this->serialNumber.'&smpassword='.$this->password.$this->otherlink;  
    	$contentcccc =  file_get_contents($weblink);   
    	return $contentcccc;
   }
   function  login(){
   	  $this->contrlname = 'login';
   	  $this->otherlink = ''; 
     $code = $this->bklink();  
   	 return $this->checkcode($code); 
	 } 
	 function getBalance()
	 {
	 	  $this->contrlname = 'getBalance';
	 	  $this->otherlink = ''; 
	    $balance = $this->bklink();  
	   return $balance; 
	 }
	 function registDetailInfo($arr)
   {  
   	 if(is_array($arr)){
   	    $tempinfo = array();
   	    foreach($arr as $key=>$value){
   	  	   $tempinfo[] =  $key.'='.$value; 
   	    }
   	    $this->otherlink = '&'.join('&',$tempinfo);
   	 }
   	 $this->contrlname = 'registDetailInfo';
	  //  return $this->otherlink;
	   $code = $this->bklink();
	 
      return $this->checkcode($code);  
   }
   function chargeUp($cardId,$cardPass)
   { 
   	
   	 $this->otherlink = '&smcard='.$cardId.'&smpwd='.$cardPass;
   	 $this->contrlname = 'chargeUp'; 
	   $code = $this->bklink();
	   return $this->checkcode($code);  
   }
	 /*
	   sendsms()  发送短信  
	   param 说明
	      $phonenum 电话号码数组 array('159xxxxxxxx','159xxxxxxxx')
	      $content  发送内容
	  */
	 function sendsms($phonenum,$content){ 
	 	 //sendsms
	 	  $phonenumss = is_array($phonenum)? join(',',$phonenum):$phonenum;
	 	  $this->otherlink = '&phoneids='.$phonenumss.'&contents='.urlencode($content);
   	  $this->contrlname = 'sendsms';
	 	  $code = $this->bklink();
	 		return $this->checkcode($code); 
	 } 
	 
	 function checkcode($code)
	 {
	 	 if ($code!=null && $code=="0")
	   {
	   	    return 'ok';
	   	}else{
	   		 return $code;//this->chkError();
	   	}
	  }
	  
	 function chkError()
   { 
   	  $err = $this->client->getError();
	    if ($err)
	    { 
		     return  $err;
	    }else{
	    return ''; 
	    }
   }
	 /*
	     返回值说明
	     $statusCode!=null && $statusCode=="0"
	     $client->getError();//获取错误信息
	 */
	 function endsend(){ 
	 	    return true;//
	 } 
	 //注销账号
	function endcard(){
		 $this->otherlink = '';
     $this->contrlname = 'logout';
     $this->bklink(); 
  }

}
?>