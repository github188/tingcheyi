<?php
/**
 *  @brief 购物车模块
 *    array(
              'goods'=>array(   //此处为单规格商品
			  商品id1=>数量,
                          商品ID2=>数量,
			  ),
              'ggoods'=>array(  //此处为有多规格时存方多规格id 
                           规格ID1=>数量,
                           规格id2=>数量,
			  ),
              'shopg'=>array(
                            店铺id1=>商品id集,
			)
              'shopgg'=>array(
                            店铺1=>规格id1的商品集
			)
 */ 
Class newsmcart{   
    private $shoptype;//私有变量店铺类型
	private $catstruct;//购物车结构
	private $cartname;//购物车名称
	private $cartnamepre ="ghcart";//购物车名称前缀
	private $goodstype;
	private $shopid;
	private $defualt_struct =  array('goods'=>array(),'ggoods'=>array(),'shopg'=>array(),'shopgg'=>array()); //默认的购物车结构 
	private $carinfo;//转换后的购物车结构
	private $mysql;
	private $errId='no_error';
	
	public function setdb($mysql){
		$this->mysql = $mysql;
		return $this;
	}
	
	public function SetShopId($shopid){
		$this->shopid = $shopid;
		return $this;
	}
	 
	public function SetGoodsType($goodstype){//1表示普通商品 2有规格商品
		$this->goodstype=$goodstype;
		return $this;
	}
	private function init(){
		// if($this->shoptype == null){
			// $this->errId = '未初始化店铺类型';
			// return false;
		// }else{
			$this->cartname = $this->cartnamepre.'_'; 
			$this->carinfo = $this->getMyCartStruct();
			$this->setMyCart($this->carinfo); 
			return true;
		// }
	}
	private function getMyCartStruct()
	{ 
	 	$cartValue = ICookie::get($this->cartname); 
		if($cartValue == null){
			return $this->defualt_struct;
		}else{
			$cartValue = JSON::decode(str_replace(array('&','$'),array('"',','),$cartValue));
			return $cartValue;
		}
	}
	public function setMyCart($goodsInfo)
	{ 
		$goodsInfo = str_replace(array('"',','),array('&','$'),JSON::encode($goodsInfo));
	    ICookie::set($this->cartname,$goodsInfo,'7200'); 
		return true;
	} 
	private function onegoods($goodsid){
		if($this->goodstype == 1){  
			$data = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id=".$goodsid."  "); 
		}else{ 
			$data = $this->mysql->select_one("select *,stock as count from ".Mysite::$app->config['tablepre']."product where id=".$goodsid."  "); 
		}
		return $data;
	}
	private function checkgoods($goodsid){
		
		
	}
	//检测是否是团购商品
	private function  isgroupon($goodsid){
		return true; 
	}
	//添加商品到购物车
	public function AddGoods($goodsid){////若按周限时商品则此处需增加 判断
		if($this->init()){
			if($this->goodstype == null){
				$this->errId = '未初始化商品类型';
				return false;
			}
			$goodsid = intval($goodsid);
			$goodsinfo = $this->onegoods($goodsid);
			if(empty($goodsinfo)){
				$this->errId = '商品不存在';
				return false;
			} 
			if($this->goodstype == 1){//正常商品
				 
			    if($goodsinfo['have_det'] == 1){
					$this->errId = '请选择规格添加商品';
					return false;
				}
				  
				$checkstock = 0;
				if(isset($this->carinfo['goods'][$goodsid])){
					$checkstock = $this->carinfo['goods'][$goodsid]+1;
					if($checkstock >$goodsinfo['count']){
						$this->errId = '商品库存不足';
						return false;
					}
				}else{
					if($goodsinfo['count'] < 1){
						$this->errId = '商品库存不足';
						return false;
					}
					$checkstock = 1;
				}
				 
				$this->carinfo['goods'][$goodsid] = $checkstock;
				if(!isset($this->carinfo['shopg'][$goodsinfo['shopid']])){
					$this->carinfo['shopg'][$goodsinfo['shopid']][] = $goodsid;
				}elseif(!in_array($goodsid,$this->carinfo['shopg'][$goodsinfo['shopid']])){
					$this->carinfo['shopg'][$goodsinfo['shopid']][] = $goodsid;
				}
				$this->setMyCart($this->carinfo);
				
				//允许添加  grouponid
			}else{//规格id1
			    $checkstock = 0;
				 
				if(isset($this->carinfo['ggoods'][$goodsid])){
					$checkstock = $this->carinfo['ggoods'][$goodsid]+1;
					if($checkstock >$goodsinfo['count']){
						$this->errId = '商品库存不足';
						return false;
					}
				}else{
					if($goodsinfo['count'] < 1){
						$this->errId = '商品库存不足';
						return false;
					}
					$checkstock = 1;
				}
				$fgoods =$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goods where id=".$goodsinfo['goodsid']."  ");
				//$fgoods = $this->rdb->ClearSet()->Table('goods')->Where(array('id'=>$goodsinfo['goodsid']))->One();
				if(empty($fgoods)){
					$this->errId = '商品规格对应商品不存在';
					return false;
				}
				 
				 
				$this->carinfo['ggoods'][$goodsid] = $checkstock;
				if(!isset($this->carinfo['shopgg'][$goodsinfo['shopid']])){ 
					$this->carinfo['shopgg'][$goodsinfo['shopid']][] = $goodsid;
				}elseif(!in_array($goodsid,$this->carinfo['shopgg'][$goodsinfo['shopid']])){ 
					$this->carinfo['shopgg'][$goodsinfo['shopid']][] = $goodsid;
				}
				$this->setMyCart($this->carinfo); 
			} 
			return true;
		}else{
			return false;
		} 
	}
	//删除商品从购物车
	public function DelGoods($goodsid){
		if($this->init()){
			if($this->goodstype == null){
				$this->errId = '未初始化商品类型';
				return false;
			}
			$goodsid = intval($goodsid); 
			$goodsinfo = $this->onegoods($goodsid);
			if($this->goodstype == 1){//正常商品
				$checkstock = 0;
				if(isset($this->carinfo['goods'][$goodsid])){ 
						unset($this->carinfo['goods'][$goodsid]);
						if(!empty($goodsinfo)){
							$newdata = array();
							foreach($this->carinfo['shopg'][$goodsinfo['shopid']] as $key=>$value){
								if($goodsid != $value){
									$newdata[] = $value;
								}
							}
							$this->carinfo['shopg'][$goodsinfo['shopid']] = $newdata; 
						} 
						$this->setMyCart($this->carinfo);  
				}  
				//允许添加  grouponid
			}else{//规格id1
			    $checkstock = 0;
				if(isset($this->carinfo['ggoods'][$goodsid])){ 
						unset($this->carinfo['ggoods'][$goodsid]);
						if(!empty($goodsinfo)){
							$newdata = array();
							foreach($this->carinfo['shopgg'][$goodsinfo['shopid']] as $key=>$value){
								if($goodsid != $value){
									$newdata[] = $value;
								}
							}
							$this->carinfo['shopgg'][$goodsinfo['shopid']] = $newdata; 
						}
						$this->setMyCart($this->carinfo);  
				}  
			} 
			return true;
		}else{
			return false;
		}
	}
	//商品在购物车中减少数量
	public function DownGoods($goodsid){
		if($this->init()){
			if($this->goodstype == null){
				$this->errId = '未初始化商品类型';
				return false;
			}
			$goodsid = intval($goodsid); 
			$goodsinfo = $this->onegoods($goodsid);
			if($this->goodstype == 1){//正常商品
				$checkstock = 0;
				if(isset($this->carinfo['goods'][$goodsid])){
					$checkstock = $this->carinfo['goods'][$goodsid]-1;
					if($checkstock > 0){
						 $this->carinfo['goods'][$goodsid] = $checkstock;
						 $this->setMyCart($this->carinfo); 
					}else{
						unset($this->carinfo['goods'][$goodsid]);
						if(!empty($goodsinfo)){
							$newdata = array();
							foreach($this->carinfo['shopg'][$goodsinfo['shopid']] as $key=>$value){
								if($goodsid != $value){
									$newdata[] = $value;
								}
							}
							$this->carinfo['shopg'][$goodsinfo['shopid']] = $newdata; 
							if(count($newdata)==0){
								unset($this->carinfo['shopg'][$goodsinfo['shopid']]);
							}
						} 
						$this->setMyCart($this->carinfo); 
					} 
				}  
				//允许添加  grouponid
			}else{//规格id1
			    $checkstock = 0;
				if(isset($this->carinfo['ggoods'][$goodsid])){
					$checkstock = $this->carinfo['ggoods'][$goodsid]-1;
					if($checkstock > 0){
						 $this->carinfo['ggoods'][$goodsid] = $checkstock;
						 $this->setMyCart($this->carinfo); 
					}else{
						unset($this->carinfo['ggoods'][$goodsid]);
						if(!empty($goodsinfo)){
							$newdata = array();
							foreach($this->carinfo['shopgg'][$goodsinfo['shopid']] as $key=>$value){
								if($goodsid != $value){
									$newdata[] = $value;
								}
							}
							$this->carinfo['shopgg'][$goodsinfo['shopid']] = $newdata; 
							if(count($newdata)==0){
								unset($this->carinfo['shopgg'][$goodsinfo['shopid']]);
							}
						}
						$this->setMyCart($this->carinfo); 
					} 
				}  
			} 
			return true;
		}else{
			return false;
		}
	}
	//清除某店铺商品从购物车中
	public function DelShop(){
		if($this->init()){
			if($this->shopid == null){
				$this->errId = '未初始化店铺';
				return false;
			}
			if(isset($this->carinfo['shopg'][$this->shopid])){
				foreach($this->carinfo['shopg'][$this->shopid] as $key=>$value){
					unset($this->carinfo['goods'][$value]);
				}
				unset($this->carinfo['shopg'][$this->shopid]);
			}
			if(isset($this->carinfo['shopgg'][$this->shopid])){
				foreach($this->carinfo['shopgg'][$this->shopid] as $key=>$value){
					unset($this->carinfo['ggoods'][$value]);
				} 
				unset($this->carinfo['shopgg'][$this->shopid]);
			}
			$this->setMyCart($this->carinfo);  
			return true;
		}else{
			return false;
		}
		
		
		
	}
	//清除某类型下的所有购物车
	public function ClearCart(){
		$this->carinfo =$this->defualt_struct;
		$this->setMyCart($this->carinfo);  
	}
	public function FindInproduct($goodsid){
		if($this->init()){   
		    //print_r($this->carinfo);  
					if(!isset($this->carinfo['shopgg'][$this->shopid])){
						return null;
					}
					$tempwhere = " shopid = '".$this->shopid."'  and id in(".join(',',$this->carinfo['shopgg'][$this->shopid]).") and goodsid=".$goodsid." " ;
					$havein = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."product  where ".$tempwhere." "); 
					if(!empty($havein)){
						$havein['count'] = $this->carinfo['ggoods'][$havein['id']];
					}
					return $havein; 
		}else{
			return null;
		}
	}
	
	public function productone($productid){
		if($this->init()){   
		    //print_r($this->carinfo); 
			
			if($this->goodstype == 1){//正常商品
				if(!isset($this->carinfo['shopg'][$this->shopid])){
					return 0;
				}  
				if(isset( $this->carinfo['goods'][$productid])){
					return  $this->carinfo['goods'][$productid];
				}else{
					return 0;
				}
			
			}else{ 
				if(!isset($this->carinfo['shopgg'][$this->shopid])){
					return 0;
				}  
				if(isset( $this->carinfo['ggoods'][$productid])){
					return  $this->carinfo['ggoods'][$productid];
				}else{
					return 0;
				}
			}
			 
				 
			 
		}else{
			return 0;
		}
	}
	
	//获取某个店铺类型下所有的购物车信息  获取店铺类型下的商品全信息
	public function ShopList(){
		if($this->init()){   
			$tempinfo = $this->carinfo['shopg'];
			$shpids = array_keys($this->carinfo['shopg']);
			$shpids2 =   array_keys($this->carinfo['shopgg']);
			$shopids = array_merge($shpids,$shpids2);
			 
			$shopids = array_flip(array_flip($shopids));  
			$backdata = array();
			
			foreach($shopids as $key=>$value){
				// 完整内容 	 
				if($this->SetShopId($value)->OneShop()){
					 $newdata  = $this->getdata(); 
					 $newdata['shopinfo']  = $this->mysql->select_one("select id,shopname,shoplogo,shortname,shoptype from ".Mysite::$app->config['tablepre']."shop   where id =  ".$value."   ");
					// $newdata['shopinfo']  = $this->rdb->ClearSet()->Table('shop')->Select(array('id','shopname','shoplogo','point','pointcount'))->Where(array('id'=>$value))->One();
				    
					 $backdata[] = $newdata;
					 
				}
			}
			$this->bkdata = $backdata;  
			return true;
		}else{
			return false;
		}
		
		
	}
	//获取某个店铺类型下所有的购物车统计 信息   店铺名 商品总数   商品总价的样式
	public function ShopTJList(){
		if($this->init()){  
			$tempinfo = $this->carinfo['shopg'];
			$shpids = array_keys($this->carinfo['shopg']);
			$shpids2 =   array_keys($this->carinfo['shopgg']);
			$shopids = array_merge($shpids,$shpids2);
			$shopids = array_flip(array_flip($shopids));  
			$backdata = array(); 
			foreach($shopids as $key=>$value){
				// 完整内容 	  
				if($this->SetShopId($value)->OneShop()){
					 $newdata['shopinfo']  = $this->mysql->select_one("select id,shopname,shoplogo,shortname,shoptype from ".Mysite::$app->config['tablepre']."shop   where id =  ".$value."   ");
				 
					 $tempinfo = $this->getdata();
					 $newdata['sum'] = $tempinfo['sum'];
					 $newdata['count'] = $tempinfo['count'];
					 $backdata[] = $newdata;
				}
			}
			$this->bkdata = $backdata;  
			return true;
		}else{
			return false;
		}
		
	}
	//获取某个店铺类型下某个店铺的购物车信息
	public function OneShop(){
		if($this->init()){
			if($this->shopid == null){
				$this->errId = '未初始化店铺';
				return false;
			} 
		 
			
		    $backdata = array('goodslist'=>array(),'sum'=>0,'count'=>0,'bagcost'=>0,'shopinfo'=>array());
			 $backdata['shopinfo']  = $this->mysql->select_one("select id,shopname,shoplogo,shortname,shoptype from ".Mysite::$app->config['tablepre']."shop   where id = (".$this->shopid.")  ");
				
		   //  $backdata['shopinfo']  = $this->rdb->ClearSet()->Table('shop')->Select(array('id','shopname','shoplogo','point','pointcount'))->Where(array('id'=>$this->shopid))->One();
				    
			$gglist = array();
			$goodsids = array();
			if(isset($this->carinfo['shopgg'][$this->shopid])){
				 
				  $tempwhere = " shopid = '".$this->shopid."'  and id in (".join(',',$this->carinfo['shopgg'][$this->shopid]).") " ;
				  
				  
				  $listtemp = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."product  where ".$tempwhere." ");
				 
				 // $listtemp = $this->rdb->ClearSet()->Table('goodsdet')->Where($tempwhere)->Page(0)->Size(1000)->Lst();
				 
				  $gglist = array(); 
				  foreach($listtemp as $key=>$value){
					  $gglist[$value['goodsid']][] = $value;
				  }
				  
				  
				  $goodsids  = array_keys($gglist); 
			} 
			if(isset($this->carinfo['shopg'][$this->shopid])){ 
				 $goodsids = array_merge($this->carinfo['shopg'][$this->shopid],$goodsids);
			}
			$goodslist = array();
			if(count($goodsids)> 0){//若按周限时商品则此处需增加 判断
				$tempwhere = " shopid = '".$this->shopid."'   and id in (".join(',',$goodsids).")   " ;
				 $goodslist = $this->mysql->getarr("select * from ".Mysite::$app->config['tablepre']."goods  where ".$tempwhere." ");
			     //$goodslist = $this->rdb->ClearSet()->Table('goods')->Select(array('id','name','count','cost','img','shopid','shoptype','bagcost','uinit','is_det','is_show','limitid','buyprentid','grouponid'))->Where($tempwhere)->Page(0)->Size(1000)->Lst();
			}
			 
			foreach($goodslist as $key=>$value){//若增加限时抢购  则需增加标识
				
				if(isset($gglist[$value['id']])){
					$childarray = $gglist[$value['id']];
					$temptc = $value;
					foreach($childarray as $k=>$v){
					 
						$temptc['gg'] = $v;
						$temptc['stock'] = $v['stock'];
						$temptc['count'] = $this->carinfo['ggoods'][$v['id']];
						$temptc['cost'] = $v['cost']; 
						
						  $temptc['cxinfo'] =  $this->goodscx($value);
						  if($temptc['cxinfo']['is_cx'] == 1 ){
								$temptc['cost'] = round( $temptc['cost']*$temptc['cxinfo']['zhekou']/10 , 2); 
						  }
						$backdata['goodslist'][] = $temptc;
						//    $value['cxinfo'] = $this->goodscx($value,$where,$value['count']);
					   
						$backdata['sum'] = $backdata['sum']+$temptc['count']*$temptc['cost'];
						$backdata['count'] = $backdata['count']+$temptc['count'];
						$backdata['bagcost'] = $backdata['bagcost']+$temptc['count']*$temptc['bagcost'];
					} 
				}else{
					$value['stock'] = $value['count'];
					$value['count'] = $this->carinfo['goods'][$value['id']];
					  $value['cxinfo'] = $this->goodscx($value);
						if($value['cxinfo']['is_cx'] == 1 ){
								$value['cost'] = $value['cxinfo']['cxcost']; 
						  }
					$backdata['goodslist'][] = $value;
			 	  
					$backdata['sum'] = $backdata['sum']+$value['count']*$value['cost'];
					$backdata['count'] = $backdata['count']+$value['count'];
					$backdata['bagcost'] = $backdata['bagcost']+$value['count']*$value['bagcost'];
				}
				
				/****还需排除正在团够的 现实限时抢购****/
				 
				
			}
			$this->bkdata = $backdata; 
			return true;
		}else{
			return false;
		} 
	}
	
	
private function goodscx($goodsinfo){
		//将规格转换为真实 数据 
		//array(1=>'买即送',2=>'限时直降',3=>'限时折扣',4=>'限时价');		cxtype
	/* 	 
		 if($valq['is_cx'] == 1){
				//测算促销 重新设置金额
					$cxdata = $this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$valq['id']."  ");
					$newdata = getgoodscx($valq['cost'],$cxdata);
					
					$valq['zhekou'] = $newdata['zhekou'];
					$valq['is_cx'] = $newdata['is_cx'];
					$valq['cost'] = $newdata['cost'];
				} */
		 
		$newarray = array('cxcost'=>0,'oldcost'=>$goodsinfo['cost'],'zhekou'=>0);	
		if($goodsinfo['is_cx'] == 1){		
			$cxdata =	$this->mysql->select_one("select * from ".Mysite::$app->config['tablepre']."goodscx where goodsid=".$goodsinfo['id']."  ");
			$newdata = getgoodscx($goodsinfo['cost'],$cxdata);
			$newarray['oldcost'] = $goodsinfo['cost'];
			$newarray['cxcost'] = $newdata['cost'];
			$newarray['zhekou'] = $newdata['zhekou'];
			$newarray['is_cx'] = $newdata['is_cx'];
		}	  
		return  $newarray;
	}
	
	public function getdata(){
		return $this->bkdata ==null?array():$this->bkdata;
	}
	public function getError()
	{
		return $this->errId;
	}
	
	
	 
}  