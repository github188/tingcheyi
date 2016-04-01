<?php 

/**
 * @class Cart
 * @brief 天气预报
 */
class orderclass
{
	private $error = ''; 
	private $orderid = ''; 
	 
	 //普通用户登录
	 
	protected $ordmysql; 
	function __construct()
	{
	 	$this->ordmysql =new mysql_class();  
	}
  //关闭订单
  
 
	
	
	
	
	//关闭订单通知
	function noticeclose($orderid,$reason){
		$orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
		if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass();  
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	        $tpllist = $default_tpl->getInfo(); 
	        $orderinfo['reason'] = $reason;
	        if(isset($tpllist['noticemake']) && !empty($tpllist['phoneunorder'])){
	        $emailcontent = Mysite::$app->statichtml($tpllist['phoneunorder'],$orderinfo);  
	        if(!empty($appcheck)){
	              
	              	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],'订单被关闭',$emailcontent,$messagetype=1);	          
	                logwrite('APP发送:'.$backinfo);
	        }
	        if(!empty($orderinfo['buyeruid']))
	        {
	              	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	              	   if(!empty($wxbuyer)){  
	              	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	              	          }else{
	              	       	    logwrite('微信客服发送错误:'.$wx_s->err());  
	              	         }
	              	        
	              	   }
	        }
	        $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	        if(IValidate::email($memberinfo['email'])){ 
	        	 $info = $smtp->send($shopinfo['email'], Mysite::$app->config['emailname'],'关闭订单',$emailcontent, "" , "HTML" , "" , "");  
	        }
	      }
	  }
	}
	//制作订单通知
	function noticemake($orderid){
		$orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	  if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass();  //appBuyclass
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	        $tpllist = $default_tpl->getInfo(); 
	        	     if(isset($tpllist['noticemake']) && !empty($tpllist['noticemake'])){
	        $emailcontent = Mysite::$app->statichtml($tpllist['noticemake'],$orderinfo);  
	        if(!empty($appcheck)){
	                logwrite('APP发送内容:'.$emailcontent);
	              	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],'商家确认制作该订单',$emailcontent,$messagetype=1);	          
	                logwrite('APP发送:'.$backinfo);
	        }
	        if(!empty($orderinfo['buyeruid']))
	        {
	              	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	              	   if(!empty($wxbuyer)){  
	              	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	              	          }else{
	              	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	              	         }
	              	        
	              	   }
	        }
	        $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	        if(IValidate::email($memberinfo['email'])){ 
	        	 $info = $smtp->send($memberinfo['email'], Mysite::$app->config['emailname'],'订单商家受理通知',$emailcontent, "" , "HTML" , "" , "");  
	        }
	      }
	  }
	}
	//不制作订单通知
	function noticeunmake($orderid){
		$orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	  if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass();  
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	        $tpllist = $default_tpl->getInfo(); 
	     if(isset($tpllist['noticeunmake']) && !empty($tpllist['noticeunmake'])){
	        $emailcontent = Mysite::$app->statichtml($tpllist['noticeunmake'],$orderinfo);  
	        if(!empty($appcheck)){
	              
	              	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],'订单取消提示',$emailcontent,$messagetype=1);	          
	                logwrite('APP发送:'.$backinfo);
	        }
	        if(!empty($orderinfo['buyeruid']))
	        {
	              	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	              	   if(!empty($wxbuyer)){  
	              	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	              	          }else{
	              	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	              	         }
	              	        
	              	   }
	        }
	        $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	        if(IValidate::email($memberinfo['email'])){ 
	        	 $info = $smtp->send($shopinfo['email'], Mysite::$app->config['emailname'],'不制作订单通知',$emailcontent, "" , "HTML" , "" , "");  
	        }
	      }
	  }
	}
	//退款成功通知
	function noticeback($orderid){
		$orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	  if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass(); 
	        $drawbacklog =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."drawbacklog  where orderid= '".$orderid."'   ");
	        $orderinfo['reason'] = $drawbacklog['bkcontent'];
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	        $tpllist = $default_tpl->getInfo(); 
	     if(isset($tpllist['noticeback']) && !empty($tpllist['noticeback'])){
	        $emailcontent = Mysite::$app->statichtml($tpllist['noticeback'],$orderinfo);  
	        if(!empty($appcheck)){
	              
	              	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],'退款成功',$emailcontent,$messagetype=1);	          
	                logwrite('APP发送:'.$backinfo);
	        }
	        if(!empty($orderinfo['buyeruid']))
	        {
	              	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	              	   if(!empty($wxbuyer)){  
	              	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	              	          }else{
	              	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	              	         }
	              	        
	              	   }
	        }
	        $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	        if(IValidate::email($memberinfo['email'])){ 
	        	 $info = $smtp->send($shopinfo['email'], Mysite::$app->config['emailname'],'退款成功通知',$emailcontent, "" , "HTML" , "" , "");  
	        }
	     }
	   }
	}
	//发货通知
	function noticesend($orderid){
		$orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	  if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass(); 
	        $drawbacklog =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."drawbacklog  where orderid= '".$orderid."'   ");
	        $orderinfo['reason'] = $drawbacklog['bkcontent'];
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	        $tpllist = $default_tpl->getInfo(); 
	        if(isset($tpllist['noticesend']) && !empty($tpllist['noticesend'])){
	            $emailcontent = Mysite::$app->statichtml($tpllist['noticesend'],$orderinfo);  
	            if(!empty($appcheck)){
	                  
	                  	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],"发货提示",$emailcontent,$messagetype=1);	          
	                    logwrite('APP发送:'.$backinfo);
	            }
	            if(!empty($orderinfo['buyeruid']))
	            {
	                  	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	                  	   if(!empty($wxbuyer)){  
	                  	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	                  	          }else{
	                  	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	                  	         }
	                  	        
	                  	   }
	            }
	            $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	            if(IValidate::email($memberinfo['email'])){ 
	            	 $info = $smtp->send($memberinfo['email'], Mysite::$app->config['emailname'],'发货通知',$emailcontent, "" , "HTML" , "" , "");  
	            }
	        }
	   }
	}
	
	
	
	//退款失败通知
	function noticeunback($orderid){
	  $orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	  if(!empty($orderinfo['buyeruid'])){
	      	$smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
	      	$wx_s = new wx_s(); 
	      	$appCls = new appbuyclass(); 
	        $drawbacklog =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."drawbacklog  where orderid= '".$orderid."'   ");
	        $orderinfo['reason'] = $drawbacklog['bkcontent'];
	      	//app信息
	      	$appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."appbuyerlogin where uid = '".$orderinfo['buyeruid']."'   "); 
	      	$default_tpl = new config('tplset.php',hopedir);  
	         $tpllist = $default_tpl->getInfo(); 
	        $emailcontent = Mysite::$app->statichtml($tpllist['emailorder'],$orderinfo);  
	        if(!empty($appcheck)){
	              
	              	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],"退款提示",$emailcontent,$messagetype=1);	          
	                logwrite('APP发送:'.$backinfo);
	        }
	        if(!empty($orderinfo['buyeruid']))
	        {
	              	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	              	   if(!empty($wxbuyer)){  
	              	         if($wx_s->sendmsg($emailcontent,$wxbuyer['openid'])){
	              	          }else{
	              	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	              	         }
	              	        
	              	   }
	        }
	        $memberinfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member where uid = '".$orderinfo['buyeruid']."'   "); 
	        if(IValidate::email($memberinfo['email'])){ 
	        	 $info = $smtp->send($shopinfo['email'], Mysite::$app->config['emailname'],'退款失败通知',$emailcontent, "" , "HTML" , "" , "");  
	        }
	  }
	}
  //发送下单通知
  function  sendmess($orderid){
  	   $smtp = new ISmtp(Mysite::$app->config['smpt'],25,Mysite::$app->config['emailname'],Mysite::$app->config['emailpwd'],false); 
  	   //$sendmobile = new mobile(); 
  	   $wx_s = new wx_s(); 
  	   $orderinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."order  where id= '".$orderid."'   ");
	     $orderdet =  $this->ordmysql->getarr("select *  from ".Mysite::$app->config['tablepre']."orderdet  where order_id= '".$orderid."'   ");  
	     $shopinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."shop  where id= '".$orderinfo['shopid']."'   "); 
		 $memberinfo =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."member  where uid= '".$orderinfo['buyeruid']."'   ");

	     $contents = '';
	     $checknotice =  isset($shopinfo['noticetype'])? explode(',',$shopinfo['noticetype']):array();
	     $contents = ''; 
	     
	     $orderpastatus = $orderinfo['paystatus'] == 1?'已支付':'未支付';
	     $orderpaytype = $orderinfo['paytype'] == 1?'在线支付':'货到支付';
		 $tempdata = array('orderinfo'=>$orderinfo,'orderdet'=>$orderdet,'sitename'=>Mysite::$app->config['sitename']);
		 $open_acouttempdata = array('orderinfo'=>$orderinfo,'orderdet'=>$orderdet,'sitename'=>Mysite::$app->config['sitename'],'memberinfo'=>$memberinfo);
		 
		 
		 
	   	$psCls = new apppsyclass();
 	  	if( $orderinfo['shoptype'] == '100' ){							
						$psCls->sendall('订单提醒','有新订单待送货'); 			
			}else{
				if($orderinfo['is_goshop'] == 0){
				
						$psCls->sendbytag('订单提醒','有新订单待送货','admin_id'.$orderinfo['admin_id']); 
				}
			}
	     /*发送APP到商家**/
	     if(in_array(3,$checknotice))
		   {
	       $appcheck =  $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."applogin where uid = '".$orderinfo['shopuid']."'   "); 
	       if(!empty($appcheck)){
	        	$appCls = new appclass(); 
	        	$backinfo = $appCls->sendmsg($appcheck['userid'],$appcheck['channelid'],Mysite::$app->config['sitename'].'订单提醒','您有新的订单,单号:'.$orderinfo['dno'],$messagetype=1);	          
	          logwrite('APP发送:'.$backinfo);
	       }
	     } 
	    
	     /*短信通知商家*/ 
	    if(in_array(1,$checknotice))
		{  
			if(Mysite::$app->config['allowedsendshop'] == 1){ 
		     	 if(IValidate::suremobi($orderinfo['shopphone']))
				{
	       	      //需要后加
				  
					$default_tpl = new config('tplset.php',hopedir);  
	        	      $tpllist = $default_tpl->getInfo(); 
	        	      if(!isset($tpllist['shopphonetpl']) || empty($tpllist['shopphonetpl']))
	        	      {
	        	         // logwrite('短信发送商家模板加载失败');
	        	      }else{    
	        	      	  $contents = Mysite::$app->statichtml($tpllist['shopphonetpl'],$tempdata);    
	        	           $APIServer = 'http://www.tingche.com/sendtophone.php?apiuid='.Mysite::$app->config['apiuid']; ; 
	        	          	 //&hm=135xxxxxxxx&=mydfsafdsxx444&code=fdsafdsafdsafdsa&msgcontent=urlencode(您要发送的内容);
	        	          	 if(strlen($contents) > 498){
	        	          	 	    $content1 = substr($contents,0,498);
	        	          	 	    $weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$orderinfo['shopphone'].'&msgcontent='.urlencode($content1).''; 
	        	          	 		  $contentcccc =  file_get_contents($weblink);  
	        	          	 		  $content2 = substr($contents,498,strlen($contents));
	        	          	 		     $weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$orderinfo['shopphone'].'&msgcontent='.urlencode($content2).''; 
	        	          	 		  $contentcccc =  file_get_contents($weblink);  
	        	          	 		  logwrite('短信商家发送结果:'.$contentcccc); 
	        	          	 
	        	          	 }else{
	        	          	 		$weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$orderinfo['shopphone'].'&msgcontent='.urlencode($contents).''; 
	        	          	 		$contentcccc =  file_get_contents($weblink);  
	        	          	 		logwrite('短信发送结果:'.$contentcccc); 
	        	          	 }
	             	  } 
				  
				  
				  
	       	 	     
				}else{
	       	 	   logwrite('短信发送商家'.$shopinfo['shopname'].'联系电话错误');
				}
				
				 
				
				
				
			} 
	    } 
	   
	   
	   
	   
	     //微信通知商家  此功能要每天访问网站微信一次
	      
	     
	    //打印机 通知商家 
	    
	  if(!empty($shopinfo['machine_code'])&&!empty($shopinfo['mKey'])){
	     	 
	     	  $temp_content = '';
	     	  foreach($orderdet as $km=>$vc){
                $temp_content .= $vc['goodsname'].'('.$vc['goodscount'].'份) \n ';
	         }
$msg = '商家:'.$shopinfo['shopname'].'
预订热线:'.Mysite::$app->config['litel'].' 
订单状态：'.$orderpaytype.',('.$orderpastatus.')
姓名：'.$orderinfo['buyername'].'
电话：'.$orderinfo['buyerphone'].'
地址：'.$orderinfo['buyeraddress'].'
下单时间：'.date('m-d H:i',$orderinfo['addtime']).'
配送时间:'.date('m-d H:i',$orderinfo['posttime']).' 
*******************************
'.$temp_content.' 
******************************* 

配送费：'.$orderinfo['shopps'].'元
合计：'.$orderinfo['allcost'].'元
※※※※※※※※※※※※※※
谢谢惠顾，欢迎下次光临
订单编号'.$orderinfo['dno'].'
备注'.$orderinfo['content'].'
'; 
	    $this->dosengprint($msg,$shopinfo['machine_code'],$shopinfo['mKey']);
	  }    
	     //邮件通知卖家
	     
	    if(in_array(2,$checknotice)){//同时使用邮件通知 
	      
	       if(Mysite::$app->config['noticeshopemail'] == 1){//同时使用邮件通知 
	       	 if(IValidate::email($shopinfo['email'])){ 
	       	 	  
	        	  	//surelink  
	        	  	//算方计算
	        	  $tempcontent = '<table align="center" width="100%"><tbody><tr> <td colspan="2" align="center"><h1><strong>'.Mysite::$app->config['sitename'].'订单信息</strong></h1><hr></td></tr>';
	        	  $tempcontent .= '<tr><td width="100"><strong>订单编号：</strong></td><td>'.$orderinfo['dno'].'</td></tr><tr><td><strong>店铺名称：</strong></td><td>'.$orderinfo['shopname'].'</td></tr>';
	        	  $tempcontent .= '<tr><td><strong>联系姓名：</strong></td><td>'.$orderinfo['buyername'].'</td></tr><tr><td><strong>联系电话：</strong></td><td>'.$orderinfo['buyerphone'].'</td></tr>';
	        	  $tempcontent .= '<tr><td valign="top"><strong>配送地址：</strong></td><td>'.$orderinfo['buyeraddress'].'</td></tr><tr><td><strong>下单时间：</strong></td><td>'.date('Y-m-d H:i:s',$orderinfo['addtime']).'</td></tr>';
              foreach($orderdet as $key=>$value){
              	$tempre = $key == 0?'<strong> 订单详情：</strong>':'';
     	          $tempcontent .= '<tr><td>'.$tempre.'</td><td>'.$value['goodsname'].','.$value['goodscount'].'份,'.$value['goodscost'].'元/份</td></tr>';
              }
 	        	  $tempcontent .= '<tr><td valign="top"><strong>备注：</strong></td><td>'.$orderinfo['content'].'</td></tr>';
 	        	  $tempcontent .= '<tr><td valign="top"><strong>配送时间：</strong></td><td>'.date('Y-m-d H:i:s',$orderinfo['posttime']).'</td></tr>';
	        	  $tempcontent .= '<tr><td><strong>总金额：</strong></td><td><span class="price">'.$orderinfo['allcost'].'元</span>'.$orderpaytype.',('.$orderpastatus.')</td></tr>'; 
	        	  $tempcontent .= '</tbody></table>';
	        	  $title = '您有一笔'.Mysite::$app->config['sitename'].'新订单';  
	        	   logwrite('商家'.$shopinfo['shopname'].'邮箱地址'.$shopinfo['email'].'错误');
               $info = $smtp->send($shopinfo['email'], Mysite::$app->config['emailname'],$title,$tempcontent, "" , "HTML" , "" , "");  
               
           }else{
           	 logwrite('商家'.$shopinfo['shopname'].'邮箱地址'.$shopinfo['email'].'错误');
           } 	
	       } 
	     } 
	      
	     
	     
	     
	    //微信通知买家有效
	     
	    if(!empty($orderinfo['buyeruid']))
	    {
	        	   $wxbuyer = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."wxuser  where uid= '".$orderinfo['buyeruid']."'   ");
	        	   if(!empty($wxbuyer)){ 
	        	   
	        	       $temp_content = '在'.Mysite::$app->config['sitename'].'下单成功'.'\n';
	        	       $temp_content = '店铺：'.$orderinfo['shopname'].'\n';
	        	       $temp_content .='下单时间：'.date('m-d H:i',$orderinfo['addtime']).'\n';
	        	       $temp_content .='配送时间：'.date('m-d H:i',$orderinfo['posttime']).'\n';
	        	       $temp_content .='支付方式'.$orderpaytype.','.$orderpastatus.' '.'\n';
	        	       $temp_content .='收货人:'.$orderinfo['buyername'].'\n';
	        	       $temp_content .='地址:'.$orderinfo['buyeraddress'].'\n';
	        	       $temp_content .='联系电话:'.$orderinfo['buyerphone'].'\n';
	        	       $temp_content .='单号:'.$orderinfo['dno'].'\n';
	        	       $temp_content .='总价:'.$orderinfo['allcost'].'元,配送费：'.$orderinfo['shopps'].'元\n';
	        	       $temp_content .='备注:'.$orderinfo['content'].'\n';
	        	       foreach($orderdet as $km=>$vc){
	        	       	 $temp_content .=$vc['goodsname'].'('.$vc['goodscount'].'份)\n';
	        	       }
	        	       $contents = $temp_content;
	        	       if(!empty($contents)){
	        	       	
	        	       	 $time = time();
	           	       $tempstr = md5(Mysite::$app->config['wxtoken'].$time);
	                   $tempstr = substr($tempstr,3,15);
	                   $dolink = Mysite::$app->config['siteurl'].'/index.php?ctrl=wxsite&action=ordershow&orderid='.$orderinfo['id'];
	                 
	                 
	                   $backinfo = '';
	                 if(!empty($dolink)){
		                    	$templink = $dolink;
		                     for($i=0;$i<strlen($templink);$i++){
	                           $backinfo .= ord($templink[$i]).',';
                         }
                   }
		               // $backinfo =  str_replace(array('"',',','&'),array('-','^','@'),json_encode($dolink));
		               //shopshoworder
		               $linkstr =  Mysite::$app->config['siteurl'].'/index.php?ctrl=wxsite&action=index&openid='.$wxbuyer['openid'].'&actime='.$time.'&sign='.$tempstr.'&backinfo='.$backinfo;
		               $contents .= '<a href=\''.trim($dolink).'\'>查看详情</a>';
	        	       	
	        	       
	        	       	
	        	         if($wx_s->sendmsg($contents,$wxbuyer['openid'])){
	        	          }else{
	        	       	 logwrite('微信客服发送错误:'.$wx_s->err());  
	        	         }
	        	       }
	        	       
	        	   }
	    }
	    
	    
	     
	   //短信通知买家有效
	       $contents = '';
	      
			if(Mysite::$app->config['allowedsendbuyer'] == 1)
		     {        
		     	 if(IValidate::suremobi($orderinfo['buyerphone']))
				{ 
	       	 
	       	 	
	       	 	    $default_tpl = new config('tplset.php',hopedir);  
					$tpllist = $default_tpl->getInfo(); 
					if(!isset($tpllist['userbuytpl']) || empty($tpllist['userbuytpl']))
					{
						logwrite('短信发送会员模版失败'); 
					}else{  
					if( $orderinfo['paytype_name'] != 'open_acout'  ){
						$contents = Mysite::$app->statichtml($tpllist['userbuytpl'],$tempdata);   
					}else{				   
						$contents = Mysite::$app->statichtml($tpllist['userbuytpl'],$open_acouttempdata);   
					}				   
      	      	    $APIServer = 'http://www.tingche.com/sendtophone.php?apiuid='.Mysite::$app->config['apiuid']; ; 
	        	           
	        	           
	        	          	 		$weblink = $APIServer.'&key='.trim(Mysite::$app->config['sms86ac']).'&code='.trim(Mysite::$app->config['sms86pd']).'&hm='.$orderinfo['buyerphone'].'&msgcontent='.urlencode($contents).''; 
	        	          	 		$contentcccc =  file_get_contents($weblink);  
	        	          	 		logwrite('短信发送结果:'.$contentcccc); 
           	   
					} 
	        	    
	        	     
     	 	       
				} 
	       } 
	        
  }
   
	 
  function request_by_other($remote_server, $post_string){
  	$context = array(   'http' => array( 
  	                              'method' => 'POST', 
                                 'header' => 'Content-type: application/x-www-form-urlencoded' .
                     
                                           '\r\n'.'User-Agent : Jimmy\'s POST Example beta' .
                     
                                           '\r\n'.'Content-length:' . strlen($post_string) + 8, 
                               'content' => '' . $post_string) 
                     );
                     
                       $stream_context = stream_context_create($context);
                      
                       $data = file_get_contents($remote_server, false, $stream_context);
                     
       return $data;
  }
  public function getorder(){
  	return $this->orderid;
  }
  public function ero(){
  	return $this->error;
  }
   public function dosengprint($msg,$machine_code,$mKey){
  	$xmlData = '<xml>
 <mKey><![CDATA['.$mKey.']]></mKey >
<machine_code><![CDATA['.$machine_code.']]></machine_code > 
<Content><![CDATA['.$msg.']]></Content >
</xml>';

//第一种发送方式，也是推荐的方式：
$url = 'http://app.waimairen.com/print.php';  //接收xml数据的文件
$header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
$ch = curl_init ($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
$response = curl_exec($ch);
if(curl_errno($ch)){
    print curl_error($ch);
}
curl_close($ch);  
  	
  }
  //订单处理 普通订单处理 
  
  function makenormal($info){
  	 //需要的公共数据 
  	 //$data['othercontent'] = $info['othercontent'];
  	// $data['cattype'] = $info['cattype'];//表示 是否是订台
     $data['areaids'] = $info['areaids'];
	   $data['admin_id'] = $info['shopinfo']['admin_id']; 
		 $data['shoptype'] = $info['shopinfo']['shoptype'];//: 0:普通订单，1订台订单 
		 //获取店铺商品总价  获取养车商品总价
		 $data['shopcost'] = $info['allcost'];
		 $data['shopps'] = $info['shopps']; 
		 $data['bagcost'] =  $info['bagcost'];
		 $data['ordertype'] = $info['ordertype']; 
		 //支付方式检测
		 $userid = $info['userid'];
		 $data['paytype'] = $info['paytype']; 
		 $data['cxids'] = '';
		 $data['cxcost'] = 0;
		 $zpin = array(); 
		 if($data['shopcost'] > 0){
		    $sellrule =new sellrule(); 
		    $sellrule->setdata($info['shopinfo']['id'],$data['shopcost'],$info['shopinfo']['shoptype']);
		    $ruleinfo = $sellrule->getdata();  
	      $data['cxcost'] = $ruleinfo['downcost'];
	      $data['cxids'] = $ruleinfo['cxids'];  
	      $zpin = $ruleinfo['zid'];//赠品
	      $data['shopps'] = $ruleinfo['nops'] == true?0:$data['shopps'];
	   }
	  //判断优惠劵
	  $allcost = $data['shopcost'];
	  $data['yhjcost'] = 0;
		$data['yhjids'] = ''; 
		$userid = $info['userid'];
		$juanid = $info['juanid']; 
	   if($juanid > 0 && $userid > 0){
	      $juaninfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."juan  where id= '".$juanid."' and uid='".$userid."'  and status = 1 and endtime > ".time()." ");
	   	  if(!empty($juaninfo)){
	   	  	 if($allcost >= $juaninfo['limitcost']){ 
	   	  	 	$data['yhjcost'] =  $juaninfo['cost']; 
	   	  	 	$juandata['status'] = 2;
	   	  	 	$juandata['usetime'] =  time(); 
	   	  	 	 $this->ordmysql->update(Mysite::$app->config['tablepre'].'juan',$juandata,"id='".$juanid."'");
	   	  	 	$data['yhjids'] = $juanid;
	   	  	 } 
	   	  } 
	   } 
	  //积分抵扣
	  $allcost = $allcost - $data['cxcost'] - $data['yhjcost'];
	  $data['scoredown'] = 0;
	  $dikou = $info['dikou'];
	  if(!empty($userid) && $dikou > 0 && Mysite::$app->config['scoretocost'] > 0 && $allcost > $dikou){
	    	 $checkuser= $this->ordmysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$userid."'  "); 
	    	 if(is_array($checkuser)){
	    	     $checkscore = $dikou*(intval(Mysite::$app->config['scoretocost']));
	    	    if($checkuser['score']  >= $checkscore){  
	    	   	  $data['scoredown'] =  $checkscore;
	    	 	    $this->ordmysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`-'.$checkscore,"uid ='".$userid."' ");	 
	    	    } 
	    	 }
	  }
	  $dikou = $data['scoredown'] > 0?$dikou:0;
	  $allcost = $allcost-$dikou;
	  $data['allcost'] = $allcost+$data['shopps']+$data['bagcost']; //订单应收费用  
		$data['pstype'] = $info['pstype'] ; 
		//检测店铺
	 
	  $data['shopuid'] = $info['shopinfo']['uid'];// 店铺UID	
	  $data['shopid'] =  $info['shopinfo']['id']; //店铺ID	
		$data['shopname'] = $info['shopinfo']['shopname']; //店铺名称	
	  $data['shopphone'] = $info['shopinfo']['phone']; //店铺电话
	  $data['shopaddress'] = $info['shopinfo']['address'];// 店铺地址	
	  $data['buyeraddress'] = $info['addressdet'];
	  $data['ordertype'] = $info['ordertype'];//来源方式;
	  $data['buyeruid'] = $userid;// 购买用户ID，0未注册用户	
		$data['buyername'] =  $info['username'];//购买热名称
		$data['buyerphone'] = $info['mobile'];// 联系电话   
		$panduan = Mysite::$app->config['man_ispass'];
		$data['status'] = 0;
		if($panduan != 1 && $data['paytype'] == 0){
			$data['status'] = 1;
		} 
	  $data['paystatus'] = 0;// 支付状态1已支付	
		$data['content'] = $info['remark'];// 订单备注	
	 
		//  daycode 当天订单序号
	  $data['ipaddress'] = $info['ipaddress'];	 
		$data['is_ping'] = 0;// 是否评价字段 1已评完 0未评	
		$data['addtime'] = time(); 	  
	    $data['posttime'] = $info['sendtime'];//: 配送时间  
	   $data['postdate'] = $info['postdate'];//配送时间段
	  $data['othertext'] = $info['othercontent'];//其他说明 	  
	  $data['is_make'] = Mysite::$app->config['allowed_is_make'] == 1?0:1;
	  //  :审核时间
	  $data['passtime'] = time();
	  if($data['status']  == 1){
	  	$data['passtime'] == 0;
	  } 
	  $data['buycode'] = substr(md5(time()),9,6);
	  $data['dno'] = time().rand(1000,9999);
	  $minitime = strtotime(date('Y-m-d',time()));
      $tj = $this->ordmysql->select_one("select daycode,id from ".Mysite::$app->config['tablepre']."order where shopid='".$info['shopid']."' and addtime > ".$minitime." order by id desc limit 0,1000");

	$data['daycode'] = empty($tj)?1:$tj['daycode']+1;


	  $this->ordmysql->insert(Mysite::$app->config['tablepre'].'order',$data);  //写主订单 
	  
	  $orderid = $this->ordmysql->insertid(); 
	  
	  
	  /* 写订单物流 状态 */
	  /* 第一步 提交成功 */
	  $this->writewuliustatus($orderid,1,$data['paytype']);
	  if($data['paytype'] == 0){
		    if($panduan == 0){
			  if($data['is_make'] == 1){
				  $this->writewuliustatus($orderid,4,$data['paytype']);  //订单审核后自动 商家接单
				  $auto_send = Mysite::$app->config['auto_send'];
				  if($auto_send == 1){
					 $this->writewuliustatus($orderid,6,$data['paytype']);//订单审核后自动 商家接单后自动发货
				  }
			  }
			}
	  }
	  
	  $this->orderid = $orderid; 
	  foreach($info['goodslist'] as $key=>$value){ 
	    $cmd['order_id'] = $orderid; 
	    $cmd['goodsid'] = isset($value['gg'])?$value['gg']['goodsid']:$value['id'];
	    $cmd['goodsname'] = isset($value['gg'])?$value['name']."【".$value['gg']['attrname']."】":$value['name'];
	    $cmd['goodscost'] = isset($value['gg'])?$value['gg']['cost']:$value['cost'];
	  	$cmd['goodscount'] = $value['count'];
	  	$cmd['shopid'] = $value['shopid'];
	  	$cmd['status'] = 0; 
	  	$cmd['is_send'] = 0;
		$cmd['have_det'] = $value['have_det'];
		$cmd['product_id'] = isset($value['gg'])?$value['gg']['id']:0;
	  	$this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$cmd);
			if(isset($value['gg'])){
				$this->ordmysql->update(Mysite::$app->config['tablepre'].'product','`stock`=`stock`-'.$cmd['goodscount'].' ',"id='".$cmd['product_id']."'"); 
				
				$this->ordmysql->update(Mysite::$app->config['tablepre'].'goods','`sellcount`=`sellcount`+'.$cmd['goodscount'].' ',"id='".$cmd['goodsid']."'"); 
				 
			}else{
					$this->ordmysql->update(Mysite::$app->config['tablepre'].'goods','`count`=`count`-'.$cmd['goodscount'].' ,`sellcount`=`sellcount`+'.$cmd['goodscount'],"id='".$cmd['goodsid']."'"); 
		
			}
	  }
	   
	  if(is_array($zpin)&& count($zpin) > 0){
	   
	     foreach($zpin as $key=>$value){
	  	    $datadet['order_id'] = $orderid;
	  	    $datadet['goodsid'] = $key;
	  	    $datadet['goodsname'] = $value['presenttitle'];
	  	    $datadet['goodscost'] = 0;
	  	    $datadet['goodscount'] = 1;
	  	    $datadet['shopid'] = $info['shopid'];
	  	    $datadet['status'] = 0; 
	  	    $datadet['is_send'] = 1;
			$datadet['have_det']=0;
			$datadet['product_id'] =0;
	  	    //更新促销规则中 此赠品的数量 
	  	    $this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$datadet);
	  	  	$this->ordmysql->update(Mysite::$app->config['tablepre'].'rule','`controlcontent`=`controlcontent`-1',"id='".$key."'");
	    } 
	  }
	  
	  $checkbuyer = Mysite::$app->config['allowedsendbuyer']; 
	  $checksend = Mysite::$app->config['man_ispass'];
	 	if($checksend != 1)
	 	{ 
	 		 if($data['status'] == 1){ 
          $this->sendmess($orderid);
       }
	  }
	  if($userid > 0){ 
	     $checkinfo =   $this->ordmysql->select_one("select * from ".Mysite::$app->config['tablepre']."address where userid='".$userid."'  ");   
	     if(empty($checkinfo)){
	        $addata['userid'] = $userid;
	        $addata['username'] = $data['buyername'];
	        $addata['address'] = $data['buyeraddress'];
	        $addata['phone'] = $data['buyerphone'];
	        $addata['contactname'] = $data['buyername'];
	        $addata['default'] = 1; 
	        $this->ordmysql->insert(Mysite::$app->config['tablepre'].'address',$addata);
	     } 
	  }
  
  }
  /*  
  * $orderid  订单Id
  * $step 订单物流状态 
  *
  *		function writewuliustatus($orderid,$step,$paytype){ }
  *
  *		  1 为订单提交成功 			2 为订单被管理员取消  			 3为在线支付成功    		 	4为商家确认制作  		5为商家取消订单   
  *		  6 配送发货  				7 分配给配送员（配送员已接单）   8配送元已取货   		 		9 已完成（已送达） 	10 用户已确认收货 
  *	      11用户已评价，完成订单   12用户自己取消订单（货到付款）    13用户取消订单，申请退款（在线支付）  
  *		  14 管理员同意退款给用户      15 管理员拒绝退款给用户
  *	  
  * $paytype 支付方式 1 为在线支付 0为货到付款
  *
  */
	function writewuliustatus($orderid,$step,$paytype){
		$statusdata['orderid']     =  $orderid;
		switch ($step){ 
			case 1 : 
				$statusdata['statustitle'] =  "订单已提交";
				$statusdata['ststusdesc']  =  "请耐心等待商家确认";
				break; 
			case 2 : 
				
				break; 
			case 3 :
				$statusdata['statustitle'] =  "在线支付成功";
				$statusdata['ststusdesc']  =  "订单已在线支付成功";
				break;
			case 4 :
				$statusdata['statustitle'] =  "商家已接单";
				$statusdata['ststusdesc']  =  "商家正在准备商品";
				break;
			case 5 :
				$statusdata['statustitle'] =  "商家取消订单";
				if($paytype == 1){ $statusdata['ststusdesc']  =  "金额将于2个工作日内退还到您的支付账户"; }				
				break;	
			case 6 :
				$statusdata['statustitle'] =  "商家已发货";
				$statusdata['ststusdesc']  =  "商品已经准备好"; 		
				break;
			case 7 :
				$statusdata['statustitle'] =  "配送员已接单";
				$statusdata['ststusdesc']  =  "配送员正赶往商家"; 		
				break;
			case 8 :
				$statusdata['statustitle'] =  "配送员已取货";
				$statusdata['ststusdesc']  =  "请耐心等待配送"; 		
				break;
	/* 		case 9 :
				$statusdata['statustitle'] =  "已送达";
				$statusdata['ststusdesc']  =  "请确认收货"; 		
				break; */
				case 9 :
				$statusdata['statustitle'] =  "已完成订单";
				$statusdata['ststusdesc']  =  "请评价订单"; 		
				break;
			case 10 :
				$statusdata['statustitle'] =  "已确认收货";
				$statusdata['ststusdesc']  =  "请评价订单"; 		
				break;
			case 11 :
				$statusdata['statustitle'] =  "已完成订单";
				$statusdata['ststusdesc']  =  "已评价"; 		
				break;
			case 12 :
				$statusdata['statustitle'] =  "已取消订单";
				$statusdata['ststusdesc']  =  "已取消订单"; 		
				break;
			case 13 :
				$statusdata['statustitle'] =  "申请退款处理中";
				$statusdata['ststusdesc']  =  "申请同意后,金额将于2个工作日内退还到您的支付账户"; 		
				break;
			case 14 :
				$statusdata['statustitle'] =  "退款成功";
				$statusdata['ststusdesc']  =  "您可以在您对应的支付账户中查看款"; 		
				break;
			case 15 :
				$statusdata['statustitle'] =  "拒绝退款";
				$statusdata['ststusdesc']  =  "经审核，您的条件不符合退款标准"; 		
				break;
			default : 
				$this->message('nodefined_func');
				break; 
		} 
		
		 $statusdata['addtime']     =  time();
	  $this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderstatus',$statusdata); 

	}
//后台 代课下单
 function houtaimakenormal($info){
  	 //需要的公共数据 
  	 //$data['othercontent'] = $info['othercontent'];
  	// $data['cattype'] = $info['cattype'];//表示 是否是订台
     $data['areaids'] = $info['areaids'];
	   $data['admin_id'] = $info['shopinfo']['admin_id']; 
		 $data['shoptype'] = $info['shopinfo']['shoptype'];//: 0:普通订单，1订台订单 
		 //获取店铺商品总价  获取养车商品总价
		 $data['shopcost'] = $info['allcost'];
		 $data['shopps'] = $info['shopps']; 
		 $data['bagcost'] =  $info['bagcost'];
		 $data['ordertype'] = $info['ordertype']; 
		 //支付方式检测
		 $userid = $info['userid'];
		 $data['paytype'] = $info['paytype']; 
		 $data['cxids'] = '';
		 $data['cxcost'] = 0;
		 $zpin = array(); 
		 if($data['shopcost'] > 0){
		    $sellrule =new sellrule(); 
		    $sellrule->setdata($info['shopinfo']['id'],$data['shopcost'],$info['shopinfo']['shoptype']);
		    $ruleinfo = $sellrule->getdata();  
	      $data['cxcost'] = $ruleinfo['downcost'];
	      $data['cxids'] = $ruleinfo['cxids'];  
	      $zpin = $ruleinfo['zid'];//赠品
	      $data['shopps'] = $ruleinfo['nops'] == true?0:$data['shopps'];
	   }
	  //判断优惠劵
	  $allcost = $data['shopcost'];
	  $data['yhjcost'] = 0;
		$data['yhjids'] = ''; 
		$userid = $info['userid'];
		$juanid = $info['juanid']; 
	   if($juanid > 0 && $userid > 0){
	      $juaninfo = $this->ordmysql->select_one("select *  from ".Mysite::$app->config['tablepre']."juan  where id= '".$juanid."' and uid='".$userid."'  and status = 1 and endtime > ".time()." ");
	   	  if(!empty($juaninfo)){
	   	  	 if($allcost >= $juaninfo['limitcost']){ 
	   	  	 	$data['yhjcost'] =  $juaninfo['cost']; 
	   	  	 	$juandata['status'] = 2;
	   	  	 	$juandata['usetime'] =  time(); 
	   	  	 	 $this->ordmysql->update(Mysite::$app->config['tablepre'].'juan',$juandata,"id='".$juanid."'");
	   	  	 	$data['yhjids'] = $juanid;
	   	  	 } 
	   	  } 
	   } 
	  //积分抵扣
	  $allcost = $allcost - $data['cxcost'] - $data['yhjcost'];
	  $data['scoredown'] = 0;
	  $dikou = $info['dikou'];
	  if(!empty($userid) && $dikou > 0 && Mysite::$app->config['scoretocost'] > 0 && $allcost > $dikou){
	    	 $checkuser= $this->ordmysql->select_one("select * from ".Mysite::$app->config['tablepre']."member where uid='".$userid."'  "); 
	    	 if(is_array($checkuser)){
	    	     $checkscore = $dikou*(intval(Mysite::$app->config['scoretocost']));
	    	    if($checkuser['score']  >= $checkscore){  
	    	   	  $data['scoredown'] =  $checkscore;
	    	 	    $this->ordmysql->update(Mysite::$app->config['tablepre'].'member','`score`=`score`-'.$checkscore,"uid ='".$userid."' ");	 
	    	    } 
	    	 }
	  }
	  $dikou = $data['scoredown'] > 0?$dikou:0;
	  $allcost = $allcost-$dikou;
	  $data['allcost'] = $allcost+$data['shopps']+$data['bagcost']; //订单应收费用  
		$data['pstype'] = $info['pstype'] ; 
		//检测店铺
	 
	  $data['shopuid'] = $info['shopinfo']['uid'];// 店铺UID	
	  $data['shopid'] =  $info['shopinfo']['id']; //店铺ID	
		$data['shopname'] = $info['shopinfo']['shopname']; //店铺名称	
	  $data['shopphone'] = $info['shopinfo']['phone']; //店铺电话
	  $data['shopaddress'] = $info['shopinfo']['address'];// 店铺地址	
	  $data['buyeraddress'] = $info['addressdet'];
	  $data['ordertype'] = $info['ordertype'];//来源方式;
	  $data['buyeruid'] = $userid;// 购买用户ID，0未注册用户	
		$data['buyername'] =  $info['username'];//购买热名称
		$data['buyerphone'] = $info['mobile'];// 联系电话   
		$panduan = Mysite::$app->config['man_ispass'];
		$data['status'] = 0;
		if($panduan != 1 && $data['paytype'] == 0){
			$data['status'] = 1;
		} 
	  $data['paystatus'] = 1;// 默认1已支付	
	  $data['paytype_name'] = 'open_acout';// 默认未余额支付
		$data['content'] = $info['remark'];// 订单备注	
	 
		//  daycode 当天订单序号
	  $data['ipaddress'] = $info['ipaddress'];	 
		$data['is_ping'] = 0;// 是否评价字段 1已评完 0未评	
		$data['addtime'] = time(); 	  
	    $data['posttime'] = $info['sendtime'];//: 配送时间  
	   $data['postdate'] = $info['postdate'];//配送时间段
	  $data['othertext'] = $info['othercontent'];//其他说明 	  
	  $data['is_make'] = Mysite::$app->config['allowed_is_make'] == 1?0:1;
	  //  :审核时间
	  $data['passtime'] = time();
	  if($data['status']  == 1){
	  	$data['passtime'] == 0;
	  } 
	  $data['buycode'] = substr(md5(time()),9,6);
	  $data['dno'] = time().rand(1000,9999);
	  $minitime = strtotime(date('Y-m-d',time()));
      $tj = $this->ordmysql->select_one("select daycode,id from ".Mysite::$app->config['tablepre']."order where shopid='".$info['shopid']."' and addtime > ".$minitime." order by id desc limit 0,1000");

	$data['daycode'] = empty($tj)?1:$tj['daycode']+1;

$data['status'] = 1;
	  $this->ordmysql->insert(Mysite::$app->config['tablepre'].'order',$data);  //写主订单 
	  
	  $orderid = $this->ordmysql->insertid(); 
	  
	  
	  /* 写订单物流 状态 */
	  /* 第一步 提交成功 */
	  $this->writewuliustatus($orderid,1,$data['paytype']);
	  if($data['paytype'] == 0){
		    if($panduan == 0){
			  if($data['is_make'] == 1){
				  $this->writewuliustatus($orderid,4,$data['paytype']);  //订单审核后自动 商家接单
				  $auto_send = Mysite::$app->config['auto_send'];
				  if($auto_send == 1){
					 $this->writewuliustatus($orderid,6,$data['paytype']);//订单审核后自动 商家接单后自动发货
				  }
			  }
			}
	  }
	  
	  $this->orderid = $orderid; 
	  foreach($info['goodslist'] as $key=>$value){ 
	    $cmd['order_id'] = $orderid; 
	    $cmd['goodsid'] = $value['id'];
	    $cmd['goodsname'] = $value['name'];
	    $cmd['goodscost'] = $value['cost'];
	  	$cmd['goodscount'] = $value['count'];
	  	$cmd['shopid'] = $value['shopid'];
	  	$cmd['status'] = 0; 
	  	$cmd['is_send'] = 0;
	  	$this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$cmd);
	    $this->ordmysql->update(Mysite::$app->config['tablepre'].'goods','`count`=`count`-'.$cmd['goodscount'].' ,`sellcount`=`sellcount`+'.$cmd['goodscount'],"id='".$cmd['goodsid']."'"); 
	  }
	   
	  if(is_array($zpin)&& count($zpin) > 0){
	   
	     foreach($zpin as $key=>$value){
	  		  $datadet['order_id'] = $orderid;
	  	    $datadet['goodsid'] = $key;
	  	    $datadet['goodsname'] = $value['presenttitle'];
	  	    $datadet['goodscost'] = 0;
	  	    $datadet['goodscount'] = 1;
	  	    $datadet['shopid'] = $info['shopid'];
	  	    $datadet['status'] = 0; 
	  	    $datadet['is_send'] = 1;
	  	    //更新促销规则中 此赠品的数量 
	  	    $this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$datadet);
	  	  	$this->ordmysql->update(Mysite::$app->config['tablepre'].'rule','`controlcontent`=`controlcontent`-1',"id='".$key."'");
	    } 
	  }
	  
	  $checkbuyer = Mysite::$app->config['allowedsendbuyer']; 
	  $checksend = Mysite::$app->config['man_ispass'];
	 	if($checksend != 1)
	 	{ 
	 		 if($data['status'] == 1){ 
          $this->sendmess($orderid);
       }
	  }
	  if($userid > 0){ 
	     $checkinfo =   $this->ordmysql->select_one("select * from ".Mysite::$app->config['tablepre']."address where userid='".$userid."'  ");   
	     if(empty($checkinfo)){
	        $addata['userid'] = $userid;
	        $addata['username'] = $data['buyername'];
	        $addata['address'] = $data['buyeraddress'];
	        $addata['phone'] = $data['buyerphone'];
	        $addata['contactname'] = $data['buyername'];
	        $addata['default'] = 1; 
	        $this->ordmysql->insert(Mysite::$app->config['tablepre'].'address',$addata);
	     } 
	  }
  
  }
	
	
  //预订订单
  function orderyuding($info){
  	 //$data['subtype'] = $info['subtype'];
  	 $data['is_goshop'] = $info['is_goshop'];
		 $data['areaids'] = $info['areaids'];
		 $data['admin_id'] = $info['shopinfo']['admin_id'];
		 $data['shopcost'] = $info['allcost'];//:店铺商品总价
		 $data['shopps'] = 0;//店铺配送费  
		 $data['shoptype'] = 0;//: 0:普通订单，1订台订单
		 $data['bagcost']=0;//:服务费 
		 //获取店铺商品总价  获取养车商品总价 
		 $data['paytype'] = $info['paytype'];
		 $data['cxids'] = '';
	   $data['cxcost'] = 0;
		 $data['yhjcost'] = 0;
	  	$data['yhjids'] = '';  
	  $data['scoredown'] = 0;
	  $data['allcost'] =$info['allcost']; //订单应收费用 
	  $data['shopuid'] =$info['shopinfo']['uid'];// 店铺UID	
		$data['shopid'] =  $info['shopinfo']['id']; //店铺ID	
		$data['shopname'] =$info['shopinfo']['shopname']; //店铺名称	
		$data['shopphone'] =  $info['shopinfo']['phone']; //店铺电话
		$data['shopaddress'] =$info['shopinfo']['address'];// 店铺地址	
		$data['pstype'] = $info['pstype'] ;
		$data['shoptype'] = 0; 
	  $data['buyeraddress'] = '';
	  $data['ordertype'] = $info['ordertype'];//来源方式;
	  $data['buyeruid'] = $info['userid'];// 购买用户ID，0未注册用户	
		$data['buyername'] =  $info['username'];//购买热名称
	  $data['buycarnumber'] =  $info['carnumber'];//购买热名称
		$data['buyerphone'] = $info['mobile'];// 联系电话    
	  $data['paystatus'] = 0;// 支付状态1已支付	
		$data['content'] = $info['remark'];// 订单备注	 
		//  daycode 当天订单序号
	  $data['ipaddress'] = $info['ipaddress'];	 
		$data['is_ping'] = 0;// 是否评价字段 1已评完 0未评	
		$data['addtime'] = time(); 	  
	 
	    $data['posttime'] = $info['sendtime'];//: 配送时间  
	   $data['postdate'] = $info['postdate'];//配送时间段
	  $data['othertext'] = $info['othercontent'];//其他说明 	 
	  //  :审核时间
	  $data['passtime'] = time(); 
	  $data['buycode'] = substr(md5(time()),9,6);
	  $data['dno'] = time().rand(1000,9999);
	  $minitime = strtotime(date('Y-m-d',time()));
	   $zpin = array();
		 if($info['subtype'] == 1){
		  // $this->message('订车位');
		   //
		  
		   
		 }elseif($info['subtype'] == 2){  
	 	     $data['shopcost'] = $data['shopcost']; 
	 	     $data['cxids'] = '';
		     $data['cxcost'] = 0; 
		     $cattype = $info['cattype'];
		     if($data['shopcost'] > 0){
		         $sellrule =new sellrule(); 
		         $sellrule->setdata($info['shopid'],$data['shopcost'],$info['shopinfo']['shoptype']);
		         $ruleinfo = $sellrule->getdata();  
	           $data['cxcost'] = $ruleinfo['downcost'];
	           $data['cxids'] = $ruleinfo['cxids'];  
	           $zpin = $ruleinfo['zid'];//赠品 
	       }
	       $data['allcost'] =   $data['shopcost'] - $data['cxcost'];  
		 }
		 $panduan = Mysite::$app->config['man_ispass'];
		 $data['status'] = 0;
		 if($panduan != 1 && $data['paytype'] == 0){
			  $data['status'] = 1;
		 }
		$data['is_make'] = Mysite::$app->config['allowed_is_make'] == 1?0:1;
	  $minitime = strtotime(date('Y-m-d',time()));
    $tj = $this->ordmysql->select_one("select count(id) as shuliang from ".Mysite::$app->config['tablepre']."order where shopid='".$info['shopid']."' and addtime > ".$minitime."  limit 0,1000");
	  $data['daycode'] = $tj['shuliang']+1; 
	  $this->ordmysql->insert(Mysite::$app->config['tablepre'].'order',$data);  //写主订单 
	  $orderid = $this->ordmysql->insertid(); 
	  
	  $this->orderid = $orderid; 
	  
	  
	    
	  /* 写订单物流 状态 */
	  /* 第一步 提交成功 */
	  $this->writewuliustatus($orderid,1,$data['paytype']);
	  if($data['paytype'] == 0){
		    if($panduan == 0){
			  if($data['is_make'] == 1){
				  $this->writewuliustatus($orderid,4,$data['paytype']);  //订单审核后自动 商家接单
				  $auto_send = Mysite::$app->config['auto_send'];
				  if($auto_send == 1){
					 $this->writewuliustatus($orderid,6,$data['paytype']);//订单审核后自动 商家接单后自动发货
				  }
			  }
			}
	  }
	  
	  
	  
	  
	  
	  if($info['subtype'] == 2){
	   foreach($info['goodslist'] as $key=>$value){ 
	    $cmd['order_id'] = $orderid; 
	    $cmd['goodsid'] = isset($value['gg'])?$value['gg']['goodsid']:$value['id'];
	    $cmd['goodsname'] = isset($value['gg'])?$value['name']."【".$value['gg']['attrname']."】":$value['name'];
	    $cmd['goodscost'] = isset($value['gg'])?$value['gg']['cost']:$value['cost'];
	  	$cmd['goodscount'] = $value['count'];
	  	$cmd['shopid'] = $value['shopid'];
	  	$cmd['status'] = 0; 
	  	$cmd['is_send'] = 0;
		$cmd['have_det'] = $value['have_det'];
		$cmd['product_id'] = isset($value['gg'])?$value['gg']['id']:0;
	  	$this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$cmd);
			if(isset($value['gg'])){
				$this->ordmysql->update(Mysite::$app->config['tablepre'].'product','`stock`=`stock`-'.$cmd['goodscount'].' ',"id='".$cmd['product_id']."'"); 
				
				$this->ordmysql->update(Mysite::$app->config['tablepre'].'goods','`sellcount`=`sellcount`+'.$cmd['goodscount'].' ',"id='".$cmd['goodsid']."'"); 
				 
			}else{
					$this->ordmysql->update(Mysite::$app->config['tablepre'].'goods','`count`=`count`-'.$cmd['goodscount'].' ,`sellcount`=`sellcount`+'.$cmd['goodscount'],"id='".$cmd['goodsid']."'"); 
		
			}
	  }
	    if(is_array($zpin)&& count($zpin) > 0){
	   
	      foreach($zpin as $key=>$value){
	  		  $datadet['order_id'] = $orderid;
	  	    $datadet['goodsid'] = $key;
	  	    $datadet['goodsname'] = $value['presenttitle'];
	  	    $datadet['goodscost'] = 0;
	  	    $datadet['goodscount'] = 1;
	  	    $datadet['shopid'] = $info['shopid'];
	  	    $datadet['status'] = 0; 
	  	    $datadet['is_send'] = 1;
			$datadet['have_det'] = 0;
			$datadet['product_id'] = 0;
	  	    //更新促销规则中 此赠品的数量 
	  	    $this->ordmysql->insert(Mysite::$app->config['tablepre'].'orderdet',$datadet);
	  	  	$this->ordmysql->update(Mysite::$app->config['tablepre'].'rule','`controlcontent`=`controlcontent`-1',"id='".$key."'");
	      } 
	    } 
	  }
	  
	  $checkbuyer = Mysite::$app->config['allowedsendbuyer']; 
	  $checksend = Mysite::$app->config['man_ispass'];
	 	if($checksend != 1)
	 	{ 
	 		 if($data['status'] == 1){ 
          $this->sendmess($orderid);
       }
	  }
  	
  	
  	
  }
  
  
  
       
       
       
       
}
?>
