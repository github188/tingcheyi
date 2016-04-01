<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}
class method extends baseclass
{
	public function index()
	{
		if (empty($this->member["uid"])) {
			$link = IUrl::creatUrl("order/guestorder");
			$this->refunction("", $link);
		}
		else if (!empty($this->member["uid"])) {
			$link = IUrl::creatUrl("order/usersorder");
			$this->refunction("", $link);
		}
	}

	public function printbyshop()
	{
		$shopid = intval(IFilter::act(IReq::get("shopid")));

		if (empty($shopid)) {
			echo "店铺ID错误";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id = " . $shopid . "   ");

		if (empty($shopinfo)) {
			echo "店铺信息获取失败";
			exit();
		}

		$data["contactname"] = trim(IFilter::act(IReq::get("contactname")));
		$data["phone"] = trim(IFilter::act(IReq::get("phone")));
		$data["address"] = trim(IFilter::act(IReq::get("address")));
		$data["shopinfo"] = $shopinfo;
		$ids = IFilter::act(IReq::get("ids"));

		if (empty($ids)) {
			echo "商品ID错误";
			exit();
		}

		$num = IFilter::act(IReq::get("nums"));

		if (empty($num)) {
			echo "商品数量错误";
			exit();
		}

		$tempids = explode(",", $ids);
		$tempnum = explode(",", $num);

		if (count($tempids) != count($tempnum)) {
			echo "商品数量和商品ID不一致";
		}

		$newid = array();
		$idtonum = array();

		foreach ($tempids as $key => $value ) {
			if (!empty($value)) {
				$check1 = intval($value);
				$check2 = intval($tempnum[$key]);
				if ((0 < $check1) && (0 < $check2)) {
					$newid[] = $value;
					$idtonum[$value] = $check2;
				}
			}
		}

		$whereid = join(",", $newid);

		if (empty($whereid)) {
			echo "数据错误";
			exit();
		}

		$orderlist = $this->mysql->getarr("select id,name,cost,bagcost from " . Mysite::$app->config["tablepre"] . "goods where shopid =" . $shopid . " and id in(" . $whereid . ") ");
		$data["goodslist"] = array();
		$sumcost = 0;
		$bagcost = 0;

		foreach ($orderlist as $key => $value ) {
			$value["shuliang"] = $idtonum[$value["id"]];
			$sumcost += $value["cost"] * intval($idtonum[$value["id"]]);
			$value["xiaoij"] = $value["cost"] * intval($idtonum[$value["id"]]);
			$bagcost += $value["bagcost"] * intval($idtonum[$value["id"]]);
			$data["goodslist"][] = $value;
		}

		$data["bagcost"] = $bagcost;
		$data["sumcost"] = $sumcost;
		Mysite::$app->setdata($data);
	}

	public function fastfoodshop()
	{
		$id = IFilter::act(IReq::get("shopid"));
		$shopinfo = $this->mysql->select_one("select id,shopname,starttime,shoptype,address,phone from " . Mysite::$app->config["tablepre"] . "shop  where   id = " . $id . " order by id desc ");

		if (empty($shopinfo)) {
			echo "店铺数据为空";
			exit();
		}

		if ($shopinfo["shoptype"] == 0) {
			$shoptype = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "goodstype where shopid='" . $id . "' order by orderid asc ");
			$data["shopdet"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid='" . $id . "' ");
		}
		else {
			$shoptype = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $id . "' and parent_id != 0 order by orderid asc  ");
			$data["shopdet"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid='" . $id . "' ");
		}

		if (empty($data["shopdet"])) {
			echo "店铺未设置商品详情";
			exit();
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($data["shopdet"]["postdate"]) ? unserialize($data["shopdet"]["postdate"]) : array());
		$data["pstimelist"] = array();
		$checknow = time();
		$whilestatic = $data["shopdet"]["befortime"];
		$nowwhiltcheck = 0;

		while ($nowwhiltcheck <= $whilestatic) {
			$startwhil = $nowwhiltcheck * 86400;

			foreach ($timelist as $key => $value ) {
				$stime = $startwhil + $nowhout + $value["s"];
				$etime = $startwhil + $nowhout + $value["e"];

				if ($checknow < $stime) {
					$tempt = array();
					$tempt["value"] = $value["s"] + $startwhil;
					$tempt["s"] = date("H:i", $nowhout + $value["s"]);
					$tempt["e"] = date("H:i", $nowhout + $value["e"]);
					$tempt["d"] = date("Y-m-d", $stime);
					$tempt["s"] = $tempt["d"] . " " . $tempt["s"];
					$tempt["i"] = $value["i"];
					$data["pstimelist"][] = $tempt;
				}
			}

			$nowwhiltcheck = $nowwhiltcheck + 1;
		}

		$goodslist = array();
		$tempgoodslist = $this->mysql->getarr("select id,name,cost,bagcost,count,typeid,have_det from " . Mysite::$app->config["tablepre"] . "goods where   shopid=" . $id . " order by id asc limit 0,1000  ");

		foreach ($tempgoodslist as $key => $value ) {
			if ($value["have_det"] == 1) {
				$detlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "product where   shopid=" . $id . " and goodsid= " . $value["id"] . " ");

				foreach ($detlist as $k => $v ) {
					$newtemp = $value;
					$newtemp["product_id"] = $v["id"];
					$newtemp["name"] = $value["name"] . "【" . $v["attrname"] . "】";
					$newtemp["cost"] = $v["cost"];
					$goodslist[] = $newtemp;
				}
			}
			else {
				$value["product_id"] = 0;
				$goodslist[] = $value;
			}
		}

		$data["shop"] = $shopinfo;
		$data["goodstype"] = $shoptype;
		$data["goods"] = $goodslist;
		Mysite::$app->setdata($data);
	}

	public function areashow()
	{
		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$shoptype = "shop";
		$shopset = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid=" . $shopid . "");

		if (empty($shopset)) {
			$shoptype = "market";
			$shopset = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid=" . $shopid . "");
		}

		$shoparea = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "areashop  where shopid=" . $shopid . "");

		if (empty($shoparea)) {
			echo "店铺区域数据不存在";
			exit();
		}

		$where = "";
		$tempids = array();

		foreach ($shoparea as $key => $value ) {
			$tempids[] = $value["areaid"];
		}

		$where = join(",", $tempids);

		if (empty($where)) {
			echo "店铺区域ID值获取失败";
			exit();
		}

		$id = IFilter::act(IReq::get("id"));
		$parent_id = 0;

		if (0 < $id) {
			$checkinfo2 = $this->mysql->select_one("select id,name,parent_id from " . Mysite::$app->config["tablepre"] . "area where parent_id=" . $id . "  and id in(" . $where . ") ");

			if (empty($checkinfo2)) {
				$areainfo = "";
				$areaid = $id;

				for ($i = 0; $i < 10; $i++) {
					$getarea = $this->mysql->select_one("select id,name,parent_id from " . Mysite::$app->config["tablepre"] . "area where id=" . $id . " limit 0,1");

					if (empty($getarea)) {
						break;
					}

					$areainfo = $getarea["name"] . $areainfo;

					if ($getarea["parent_id"] == 0) {
						break;
					}

					$id = $getarea["parent_id"];
				}

				echo "<script>parent.setarea('" . $areainfo . "','" . $areaid . "');</script>";
				exit();
			}

			$check1 = $this->mysql->select_one("select id,name,parent_id from " . Mysite::$app->config["tablepre"] . "area where  id=" . $id);
			$parent_id = $check1["parent_id"];
		}

		$data["parent_id"] = $parent_id;
		$data["id"] = (empty($id) ? "0" : $id);
		$data["where"] = $where;
		$data["shopid"] = $shopid;
		Mysite::$app->setdata($data);
	}

	static public function checkshopopentime($is_orderbefore, $posttime, $starttime)
	{
		$maxnowdaytime = strtotime(date("Y-m-d", time()));
		$daynottime = (24 * 60 * 60) - 1;
		$findpostime = false;

		for ($i = 0; $i <= $is_orderbefore; $i++) {
			if ($findpostime == false) {
				$miniday = $maxnowdaytime + ($daynottime * $i);
				$maxday = $miniday + $daynottime;
				$tempinfo = explode("|", $starttime);

				foreach ($tempinfo as $key => $value ) {
					if (!empty($value)) {
						$temp2 = explode("-", $value);

						if (1 < count($temp2)) {
							$minbijiaotime = date("Y-m-d", $miniday);
							$minbijiaotime = strtotime($minbijiaotime . " " . $temp2[0] . ":00");
							$maxbijiaotime = date("Y-m-d", $maxday);
							$maxbijiaotime = strtotime($maxbijiaotime . " " . $temp2[1] . ":00");
							if (($minbijiaotime < $posttime) && ($posttime < $maxbijiaotime)) {
								$findpostime = true;
								break;
							}
						}
					}
				}
			}
		}

		return $findpostime;
	}

	public function makeorder()
	{
		$info["shopid"] = intval(IReq::get("shopid"));
		$info["remark"] = IFilter::act(IReq::get("remark"));
		$info["paytype"] = 0;
		$info["dikou"] = 0;
		$info["username"] = IFilter::act(IReq::get("contactname"));
		$info["mobile"] = IFilter::act(IReq::get("phone"));
		$info["addressdet"] = IFilter::act(IReq::get("address"));
		$info["senddate"] = IFilter::act(IReq::get("senddate"));
		$info["minit"] = IFilter::act(IReq::get("minit"));
		$info["juanid"] = 0;
		$info["ordertype"] = 7;
		$info["othercontent"] = "";
		$ids = IFilter::act(IReq::get("ids"));

		if (empty($ids)) {
			$this->message("goods_empty");
		}

		$num = IFilter::act(IReq::get("nums"));

		if (empty($num)) {
			$this->message("goods_count");
		}

		$tempids = explode(",", $ids);
		$tempnum = explode(",", $num);

		if (count($tempids) != count($tempnum)) {
			$this->message("goods_counttoid");
		}

		$newid = array();
		$idtonum = array();

		foreach ($tempids as $key => $value ) {
			if (!empty($value)) {
				$check1 = intval($value);
				$check2 = intval($tempnum[$key]);
				if ((0 < $check1) && (0 < $check2)) {
					$newid[] = $value;
					$idtonum[$value] = $check2;
				}
			}
		}

		$whereid = join(",", $newid);

		if (empty($whereid)) {
			$this->message("shop_emptycart");
		}

		$goodslist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid =" . $info["shopid"] . " and id in(" . $whereid . ") ");
		$goodsdata = array();
		$bagsum = 0;
		$goodssum = 0;
		$goodsnum = 0;

		foreach ($goodslist as $key => $value ) {
			$value["shuliang"] = $idtonum[$value["id"]];
			$value["count"] = $idtonum[$value["id"]];
			$goodssum += $value["cost"] * intval($idtonum[$value["id"]]);
			$value["xiaoij"] = $value["cost"] * intval($idtonum[$value["id"]]);
			$bagsum += $value["bagcost"] * intval($idtonum[$value["id"]]);
			$value["count"] = $value["shuliang"];
			$goodsnum += $value["shuliang"];
			$goodsdata[] = $value;
		}

		$shop = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id = " . $info["shopid"] . "   ");

		if (empty($info["shopid"])) {
			$this->message("shop_noexit");
		}

		if ($shop["shoptype"] == 1) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $info["shopid"] . "'    ");
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $info["shopid"] . "'    ");
		}

		$nowID = intval(IFilter::act(IReq::get("areaid")));

		if (empty($nowID)) {
			$this->message("area_empty");
		}

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$checkps = $this->pscost($shopinfo, $goodsnum, $nowID);
		$info["cattype"] = 0;

		if (empty($info["username"])) {
			$this->message("emptycontact");
		}

		if (empty($info["addressdet"])) {
			$this->message("emptyaddress");
		}

		$info["userid"] = 0;
		$info["ipaddress"] = "";
		$ip_l = new iplocation();
		$ipaddress = $ip_l->getaddress($ip_l->getIP());

		if (isset($ipaddress["area1"])) {
			$info["ipaddress"] = $ipaddress["ip"] . mb_convert_encoding($ipaddress["area1"], "UTF-8", "GB2312");
		}

		$checkareaid = $nowID;
		$dataareaids = array();

		while (0 < $checkareaid) {
			$temp_check = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $checkareaid . "'   order by id desc limit 0,50");

			if (empty($temp_check)) {
				break;
			}

			if (in_array($checkareaid, $dataareaids)) {
				break;
			}

			$dataareaids[] = $checkareaid;
			$checkareaid = $temp_check["parent_id"];
		}

		$info["areaids"] = join(",", $dataareaids);
		$info["userid"] = 0;
		$userid = 0;

		if ($shopinfo["is_open"] != 1) {
			$this->message("店铺暂停营业");
		}

		$tempdata = $this->getOpenPosttime($shopinfo["is_orderbefore"], $shopinfo["starttime"], $shopinfo["postdate"], $info["minit"], $shopinfo["befortime"]);

		if ($tempdata["is_opentime"] == 2) {
			$this->message("选择的配送时间段，店铺未设置");
		}

		if ($tempdata["is_opentime"] == 3) {
			$this->message("选择的配送时间段已超时");
		}

		$info["sendtime"] = $tempdata["is_posttime"];
		$info["postdate"] = $tempdata["is_postdate"];
		$info["shopinfo"] = $shopinfo;
		$info["allcost"] = $goodssum;
		$info["bagcost"] = $bagsum;
		$info["allcount"] = $goodsnum;
		$info["shopps"] = $checkps["pscost"];
		$info["goodslist"] = $goodsdata;
		$info["pstype"] = $checkps["pstype"];
		$info["cattype"] = 0;
		$info["is_goshop"] = 0;

		if ($info["allcost"] < $shopinfo["limitcost"]) {
			$this->message("商品总价低于最小起送价" . $shopinfo["limitcost"]);
		}

		$orderclass = new orderclass();
		$orderclass->makenormal($info);
		$orderid = $orderclass->getorder();

		if ($userid == 0) {
			ICookie::set("orderid", $orderid, 86400);
		}

		$link = IUrl::creatUrl("site/waitpay/orderid/" . $orderid);
		$this->message("", $link);
		exit();
	}

	public function wavecontrol()
	{
		$type = IReq::get("type");

		if ($type == "closewave") {
			ICookie::set("playwave", 2, 2592000);
		}
		else {
			ICookie::set("playwave", 0, 2592000);
		}

		$this->success("成功");
	}

	public function getgodigui($arraylist, $nowid, $nowkey)
	{
		if (0 < count($arraylist)) {
			foreach ($arraylist as $key => $value ) {
				if ($value["parent_id"] == $nowid) {
					$value["space"] = $nowkey;
					$donextkey = $nowkey + 1;
					$donextid = $value["id"];
					$this->digui[] = $value;
					$this->getgodigui($arraylist, $donextid, $donextkey);
				}
			}
		}
	}

	public function setstatus()
	{
		$data["buyerstatus"] = array("待处理订单", "待发货", "订单已发货", "订单完成", "买家取消订单", "卖家取消订单");
		$paytypelist = array("货到支付", "在线支付");
		$data["shoptype"] = array("购卡", "养车", "其他");
		$data["ordertypearr"] = array("网站", "网站", "电话", "微信", "AndroidAPP", "手机网站", "iosApp", "后台客服下单", "商家后台下单", "html5手机站");
		$data["backarray"] = array("", "退款中..", "退款成功", "");
		$data["paytypearr"] = $paytypelist;
		Mysite::$app->setdata($data);
	}

	public function usersorder()
	{
		$this->checkmemberlogin();
		$data["actiondo"] = "order";
		$orderdatediff = intval(IReq::get("orderdatediff"));
		$stime = IFilter::act(IReq::get("stime"));
		$etime = IFilter::act(IReq::get("etime"));
		$status = intval(IReq::get("status"));
		$where = "";

		if ($orderdatediff == 1) {
			$etime = time() - 2592000;
			$stime = time() - (2592000 * 3);
		}
		else {
			$stime = (empty($stime) ? time() - 2592000 : strtotime($stime . " 00:01"));
			$etime = (empty($etime) ? time() : strtotime($etime . " 23:59"));
		}

		if ($status == 1) {
			$where .= " and status > 0 and status < 4";
		}
		else if ($status == 2) {
			$where .= " and status = 3 and is_ping = 1";
		}

		$oldtime = time() - 2592000;
		$where .= " and  addtime  > " . $stime . " and addtime < " . $etime;
		$this->setstatus();
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 8);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=0 " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=0   " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		$data["nowtime"] = time();
		$data["stime"] = $stime;
		$data["etime"] = $etime;
		$data["status"] = $status;
		$data["orderdatediff"] = $orderdatediff;
		$status = (empty($status) ? 5 : $status);
		$link = IUrl::creatUrl("member/order/status/" . $status . "/stime/" . date("Y-m-d", $stime) . "/etime/" . date("Y-m-d", $etime) . "/orderdatediff/" . $orderdatediff . "/page/@page@");
		$data["pagelink"] = $link;
		Mysite::$app->setdata($data);
	}

	public function usersptorder()
	{
		$this->checkmemberlogin();
		$data["actiondo"] = "order";
		$orderdatediff = intval(IReq::get("orderdatediff"));
		$stime = IFilter::act(IReq::get("stime"));
		$etime = IFilter::act(IReq::get("etime"));
		$status = intval(IReq::get("status"));
		$where = "";

		if ($orderdatediff == 1) {
			$etime = time() - 2592000;
			$stime = time() - (2592000 * 3);
		}
		else {
			$stime = (empty($stime) ? time() - 2592000 : strtotime($stime . " 00:01"));
			$etime = (empty($etime) ? time() : strtotime($etime . " 23:59"));
		}

		if ($status == 1) {
			$where .= " and status > 0 and status < 4";
		}
		else if ($status == 2) {
			$where .= " and status = 3 and is_ping = 1";
		}

		$oldtime = time() - 2592000;
		$where .= " and  addtime  > " . $stime . " and addtime < " . $etime;
		$this->setstatus();
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 8);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=100 " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=100   " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		$data["nowtime"] = time();
		$data["stime"] = $stime;
		$data["etime"] = $etime;
		$data["status"] = $status;
		$data["orderdatediff"] = $orderdatediff;
		$status = (empty($status) ? 5 : $status);
		$link = IUrl::creatUrl("member/order/status/" . $status . "/stime/" . date("Y-m-d", $stime) . "/etime/" . date("Y-m-d", $etime) . "/orderdatediff/" . $orderdatediff . "/page/@page@");
		$data["actiondo"] = "usersptorder";
		$data["pagelink"] = $link;
		Mysite::$app->setdata($data);
	}

	public function userorderdet()
	{
		$this->checkmemberlogin();
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			$this->message("order_noexit");
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "'  ");

		if (empty($orderinfo)) {
			$this->message("order_noexit");
		}

		$orderinfo["addtime"] = date("Y-m-d H:i:s", $orderinfo["addtime"]);
		$orderinfo["posttime"] = date("Y-m-d H:i:s", $orderinfo["posttime"]);
		$orderinfo["suretime"] = ($orderinfo["suretime"] < 1 ? "--" : date("Y-m-d H:i:s", $orderinfo["suretime"]));
		$orderinfo["pscost"] = $orderinfo["shopps"];
		$orderinfo["goodscost"] = $orderinfo["shopcost"];
		$orderinfo["excontent"] = $orderinfo["content"];
		$orderinfo["status"] = $orderinfo["status"];

		if (!empty($orderinfo["othertext"])) {
			$tempinfo = unserialize($orderinfo["othertext"]);
			$orderinfo["excontent"] .= ",其他要求：";

			foreach ($tempinfo as $key => $value ) {
				$orderinfo["excontent"] .= $key . ":" . $value . ",";
			}
		}

		$orderdetinfo = $this->mysql->getarr("select *,goodscount*goodscost as sum from " . Mysite::$app->config["tablepre"] . "orderdet    where  order_id='" . $orderid . "'  ");
		$backinfo["order"] = $orderinfo;
		$tempwuliu = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderstatus where   orderid = " . $orderid . " order by addtime asc   ");
		$orderwuliustatus = array();

		foreach ($tempwuliu as $key => $value ) {
			$value["addtime"] = date("m-d H:i", $value["addtime"]);
			$orderwuliustatus[] = $value;
		}

		$backinfo["orderwuliustatus"] = $orderwuliustatus;
		$backinfo["orderdet"] = $orderdetinfo;
		$this->success($backinfo);
	}

	public function usermorder()
	{
		$this->checkmemberlogin();
		$orderdatediff = intval(IReq::get("orderdatediff"));
		$stime = IFilter::act(IReq::get("stime"));
		$etime = IFilter::act(IReq::get("etime"));
		$status = intval(IReq::get("status"));
		$where = "";

		if ($orderdatediff == 1) {
			$etime = time() - 2592000;
			$stime = time() - (2592000 * 3);
		}
		else {
			$stime = (empty($stime) ? time() - 2592000 : strtotime($stime . " 00:01"));
			$etime = (empty($etime) ? time() : strtotime($etime . " 23:59"));
		}

		if ($status == 1) {
			$where .= " and status > 0 and status < 4";
		}
		else if ($status == 2) {
			$where .= " and status = 3 and is_ping = 1";
		}

		$oldtime = time() - 2592000;
		$where .= " and  addtime  > " . $stime . " and addtime < " . $etime;
		$pageinfo = new page();
		$this->setstatus();
		$pageinfo->setpage(intval(IReq::get("page")), 8);
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=1 " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$data["list"] = array();

		foreach ($list as $key => $value ) {
			$orderwuliustatus = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "orderstatus where   orderid = " . $value["id"] . " order by id desc limit 0,1 ");
			$value["orderwuliustatus"] = $orderwuliustatus["statustitle"];
			$data["list"][] = $value;
		}

		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and  shoptype=1   " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		$data["nowtime"] = time();
		$data["stime"] = $stime;
		$data["etime"] = $etime;
		$data["status"] = $status;
		$data["orderdatediff"] = $orderdatediff;
		$status = (empty($status) ? 5 : $status);
		$link = IUrl::creatUrl("order/usermorder/status/" . $status . "/stime/" . date("Y-m-d", $stime) . "/etime/" . date("Y-m-d", $etime) . "/orderdatediff/" . $orderdatediff . "/page/@page@");
		$data["actiondo"] = "ordermarket";
		$data["pagelink"] = $link;
		Mysite::$app->setdata($data);
	}

	public function userunorder()
	{
		$this->checkmemberlogin();
		$orderid = intval(IReq::get("orderid"));
		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "'  ");
		$ordercontrol = new ordercontrol($orderid);

		if (empty($this->member["uid"])) {
			$this->message("member_nologin");
		}

		if ($ordercontrol->buyerunorder($this->member["uid"])) {
			$ordCls = new orderclass();

			if ($orderinfo["paytype"] == 0) {
				$ordCls->writewuliustatus($orderinfo["id"], 12, $orderinfo["paytype"]);
			}

			$this->success("success");
		}
		else {
			$this->message($ordercontrol->Error());
		}
	}

	public function acceptorder()
	{
		$this->checkmemberlogin();
		$orderid = intval(IReq::get("orderid"));
		$ordercontrol = new ordercontrol($orderid);

		if (empty($this->member["uid"])) {
			$this->message("member_nologin");
		}

		if ($ordercontrol->acceptorder($this->member["uid"])) {
			$this->success("success");
		}
		else {
			$this->message($ordercontrol->Error());
		}
	}

	public function userdelorder()
	{
		$this->checkmemberlogin();
		$orderid = intval(IReq::get("orderid"));
		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "'  ");

		if ($orderinfo["status"] < 4) {
			$this->message("order_cantdel");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "order", "id = " . $orderinfo["id"] . " ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "orderdet", "order_id = " . $orderinfo["id"] . " ");
		$this->success("success");
	}

	public function waitpiont()
	{
		$this->checkmemberlogin();
		$this->setstatus();
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$showtime = time() - (7 * 24 * 60 * 60);
		$where = " and   (status = 3 or status =2) and is_ping = 0 and posttime >" . $showtime;
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "'  " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		Mysite::$app->setdata($data);
	}

	public function overpiont()
	{
		$this->checkmemberlogin();
		$this->setstatus();
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$showtime = time() - (7 * 24 * 60 * 60);

		if (empty($this->member["uid"])) {
			$this->message("member_nologin");
		}

		$where = " and   status = 3 and is_ping = 1 and posttime >" . $showtime;
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "'  " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		Mysite::$app->setdata($data);
	}

	public function ordercomdet()
	{
		$this->checkmemberlogin();
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			$this->message("order_noexit");
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "' and buyeruid = '" . $this->member["uid"] . "' ");

		if (empty($orderinfo)) {
			$this->message("order_noexit");
		}

		if (!in_array($orderinfo["status"], array(2, 3))) {
			$this->message("empty_ping");
		}

		$orderinfo["addtime"] = date("Y-m-d H:i:s", $orderinfo["addtime"]);
		$orderinfo["posttime"] = date("Y-m-d H:i:s", $orderinfo["posttime"]);
		$orderinfo["suretime"] = ($orderinfo["suretime"] < 1 ? "--" : date("Y-m-d H:i:s", $orderinfo["suretime"]));
		$orderinfo["pscost"] = $orderinfo["shopps"];
		$orderinfo["goodscost"] = $orderinfo["shopcost"];
		$orderinfo["excontent"] = $orderinfo["content"];
		$statusarray = array("预定中", "已预定", "配送", "完成", "取消", "取消");
		$orderinfo["status"] = $statusarray[$orderinfo["status"]];

		if (!empty($orderinfo["othertext"])) {
			$tempinfo = unserialize($orderinfo["othertext"]);
			$orderinfo["excontent"] .= ",其他要求：";

			foreach ($tempinfo as $key => $value ) {
				$orderinfo["excontent"] .= $key . ":" . $value . ",";
			}
		}

		$orderdetinfo = $this->mysql->getarr("select *,goodscount*goodscost as sum from " . Mysite::$app->config["tablepre"] . "orderdet    where  order_id='" . $orderid . "'  ");
		$temparray = array();

		foreach ($orderdetinfo as $key => $value ) {
			$value["comment"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "comment where orderid='" . $orderid . "' and orderdetid = '" . $value["id"] . "' ");
			$temparray[] = $value;
		}

		$backinfo["order"] = $orderinfo;
		$backinfo["orderdet"] = $temparray;
		$this->success($backinfo);
	}

	public function saveping()
	{
		$this->checkmemberlogin();
		$orderdetid = intval(IReq::get("orderdetid"));
		$point = intval(IReq::get("point"));
		$pointcontent = trim(IFilter::act(IReq::get("pointcontent")));
		$data["point"] = (in_array($point, array(1, 2, 3, 4, 5)) ? $point : 5);
		$orderdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "orderdet where id='" . $orderdetid . "'  ");

		if (empty($orderdet)) {
			$this->message("order_noexit");
		}

		if ($orderdet["status"] == 1) {
			$this->message("order_isping");
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderdet["order_id"] . "' and buyeruid = '" . $this->member["uid"] . "'  and (status = 2 or status = 3) ");

		if (empty($orderinfo)) {
			$this->message("order_cantping");
		}

		if ($orderinfo["is_ping"] == 1) {
			$this->message("order_isping");
		}

		if ($orderinfo["status"] == 2) {
			$umdata["status"] = 3;
			$umdata["suretime"] = time();
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $umdata, "id='" . $orderinfo["id"] . "'");

			if (!empty($orderinfo["buyeruid"])) {
				$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $orderinfo["buyeruid"] . "'   ");

				if (!empty($memberinfo)) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`total`=`total`+" . $orderinfo["allcost"], "uid ='" . $orderinfo["buyeruid"] . "' ");
				}

				if (0 < $memberinfo["parent_id"]) {
					$upuser = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $memberinfo["parent_id"] . "'   ");

					if (!empty($upuser)) {
						if (Mysite::$app->config["tui_juan"] == 1) {
							$nowtime = time();
							$endtime = $nowtime + (Mysite::$app->config["tui_juanday"] * 24 * 60 * 60);
							$juandata["card"] = $nowtime . rand(100, 999);
							$juandata["card_password"] = substr(md5($juandata["card"]), 0, 5);
							$juandata["status"] = 1;
							$juandata["creattime"] = $nowtime;
							$juandata["cost"] = Mysite::$app->config["tui_juancost"];
							$juandata["limitcost"] = Mysite::$app->config["tui_juanlimit"];
							$juandata["endtime"] = $endtime;
							$juandata["uid"] = $upuser["uid"];
							$juandata["username"] = $upuser["username"];
							$juandata["name"] = "推荐送优惠券";
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "juan", $juandata);
							$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`parent_id`=0", "uid ='" . $orderinfo["buyeruid"] . "' ");
							$logdata["uid"] = $upuser["uid"];
							$logdata["childusername"] = $memberinfo["username"];
							$logdata["titile"] = "推荐送优惠券";
							$logdata["addtime"] = time();
							$logdata["content"] = "被推荐下单完成";
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "sharealog", $logdata);
						}
					}
				}
			}
		}

		$data["orderid"] = $orderinfo["id"];
		$data["orderdetid"] = $orderdetid;
		$data["shopid"] = $orderinfo["shopid"];
		$data["goodsid"] = $orderdet["goodsid"];
		$data["uid"] = $this->member["uid"];
		$data["content"] = $pointcontent;
		$data["point"] = $point;
		$data["addtime"] = time();
		$udata["status"] = 1;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "orderdet", $udata, "id='" . $orderdetid . "'");
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "comment", $data);
		$issong = 1;

		if (0 < intval(Mysite::$app->config["commentday"])) {
			$uptime = Mysite::$app->config["commentday"] * 24 * 60 * 60;
			$uptime = $orderinfo["addtime"] + $uptime;

			if (time() < $uptime) {
				$issong = 1;
			}
			else {
				$issong = 0;
			}
		}

		$fscoreadd = 0;
		if ((0 < intval(Mysite::$app->config["commenttype"])) && ($issong == 1)) {
			$scoreadd = Mysite::$app->config["commenttype"];
			$checktime = date("Y-m-d", time());
			$checktime = strtotime($checktime);
			$checklog = $this->mysql->select_one("select sum(result) as jieguo from " . Mysite::$app->config["tablepre"] . "memberlog where type = 1 and   userid = " . $this->member["uid"] . " and addtype =1 and  addtime > " . $checktime);

			if (0 < Mysite::$app->config["maxdayscore"]) {
				$checkguo = $checklog["jieguo"] + $scoreadd;

				if ($checkguo < Mysite::$app->config["maxdayscore"]) {
				}
				else if ($checklog["jieguo"] < Mysite::$app->config["maxdayscore"]) {
					$scoreadd = Mysite::$app->config["maxdayscore"] - $checklog["jieguo"];
				}
				else {
					$scoreadd = 0;
				}
			}

			if (0 < $scoreadd) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score`=`score`+" . $scoreadd, "uid='" . $this->member["uid"] . "'");
				$fscoreadd = $scoreadd;
				$memberallcost = $this->member["score"] + $scoreadd;
				$this->memberCls->addlog($this->member["uid"], 1, 1, $scoreadd, "评价商品", "评价商品" . $orderdet["goodsname"] . "获得" . $scoreadd . "积分", $memberallcost);
			}
		}

		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $orderinfo["id"] . "' and status = 0");

		if ($shuliang < 1) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", "`is_ping`=1", "id='" . $orderinfo["id"] . "'");
			$ordCls = new orderclass();
			$ordCls->writewuliustatus($orderinfo["id"], 11, $orderinfo["paytype"]);
			if ((0 < intval(Mysite::$app->config["commentscore"])) && ($issong == 1)) {
				$scoreadd = intval(Mysite::$app->config["commentscore"]) * $orderinfo["allcost"];
				$checktime = date("Y-m-d", time());
				$checktime = strtotime($checktime);
				$checklog = $this->mysql->select_one("select sum(result) as jieguo from " . Mysite::$app->config["tablepre"] . "memberlog where type = 1 and   userid = " . $this->member["uid"] . " and addtype =1 and  addtime > " . $checktime);

				if (0 < Mysite::$app->config["maxdayscore"]) {
					$checkguo = $checklog["jieguo"] + $scoreadd;

					if ($checkguo < Mysite::$app->config["maxdayscore"]) {
					}
					else if ($checklog["jieguo"] < Mysite::$app->config["maxdayscore"]) {
						$scoreadd = Mysite::$app->config["maxdayscore"] - $checklog["jieguo"];
					}
					else {
						$scoreadd = 0;
					}
				}

				if (0 < $scoreadd) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score`=`score`+" . $scoreadd, "uid='" . $this->member["uid"] . "'");
					$memberallcost = $this->member["score"] + $scoreadd + $fscoreadd;
					$this->memberCls->addlog($this->member["uid"], 1, 1, $scoreadd, "评价完订单", "评价完订单" . $orderinfo["dno"] . "奖励，" . $scoreadd . "积分", $memberallcost);
				}
			}
		}

		$newpoint["point"] = 5;
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "comment where shopid='" . $orderinfo["shopid"] . "' ");
		$scorall = $this->mysql->select_one("select sum(point) as allpoint from " . Mysite::$app->config["tablepre"] . "comment where shopid='" . $orderinfo["shopid"] . "' ");

		if (0 < $shuliang) {
			$newpoint["point"] = intval($scorall["allpoint"] / $shuliang);
		}

		$newpoint["pointcount"] = $shuliang;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $newpoint, "id='" . $orderinfo["shopid"] . "'");
		$newpoint["point"] = 5;
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "comment where goodsid='" . $orderdet["goodsid"] . "' ");
		$scorall = $this->mysql->select_one("select sum(point) as allpoint from " . Mysite::$app->config["tablepre"] . "comment where goodsid='" . $orderdet["goodsid"] . "' ");

		if (0 < $shuliang) {
			$newpoint["point"] = intval($scorall["allpoint"] / $shuliang);
		}

		$newpoint["pointcount"] = $shuliang;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $newpoint, "id='" . $orderdet["goodsid"] . "'");
		$this->success("success");
	}

	public function yijianping()
	{
		$orderid = intval(IFilter::act(IReq::get("orderid")));
		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");

		if ($orderinfo["is_ping"] == 1) {
			$this->message("order_isping");
		}

		if (empty($orderinfo)) {
			$this->message("获取此订单失败");
		}

		$orderdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $orderinfo["id"] . "'");
		$data["orderid"] = $orderinfo["id"];
		$data["shopid"] = $orderinfo["shopid"];

		if (empty($this->member["uid"])) {
			$this->message("获取用户失败");
		}

		$data["uid"] = $this->member["uid"];
		$data["addtime"] = time();
		$data["is_show"] = 0;
		$shoppointnum = trim(IFilter::act(IReq::get("shoppointnum")));
		$shopsudupointnum = intval(IFilter::act(IReq::get("shopsudupointnum")));

		if (empty($shoppointnum)) {
			$this->message("请评论总体评价");
		}

		if (empty($shopsudupointnum)) {
			$this->message("请评论配送服务");
		}

		foreach ($orderdet as $key => $value ) {
			$data["point"] = intval(IFilter::act(IReq::get("goodsid_" . $value["id"])));
			$data["content"] = trim(IFilter::act(IReq::get("content_" . $value["id"])));
			$data["orderdetid"] = $value["id"];
			$data["goodsid"] = $value["goodsid"];
			if (!empty($data["point"]) || !empty($data["content"])) {
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "comment", $data);
				$udata["status"] = 1;
				$this->mysql->update(Mysite::$app->config["tablepre"] . "orderdet", $udata, "id='" . $value["id"] . "'");
				$goodinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id='" . $value["goodsid"] . "'  ");
				$goodpointcount = $goodinfo["pointcount"];
				$goodnewpoint["point"] = intval($goodinfo["point"] + $data["point"]);
				$goodnewpoint["pointcount"] = intval($goodpointcount + 1);
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $goodnewpoint, "id='" . $value["goodsid"] . "'");
				$issong = 1;

				if (0 < intval(Mysite::$app->config["commentday"])) {
					$uptime = Mysite::$app->config["commentday"] * 24 * 60 * 60;
					$uptime = $orderinfo["addtime"] + $uptime;

					if (time() < $uptime) {
						$issong = 1;
					}
					else {
						$issong = 0;
					}
				}

				$fscoreadd = 0;
				if ((0 < intval(Mysite::$app->config["commenttype"])) && ($issong == 1)) {
					$scoreadd = Mysite::$app->config["commenttype"];
					$checktime = date("Y-m-d", time());
					$checktime = strtotime($checktime);
					$checklog = $this->mysql->select_one("select sum(result) as jieguo from " . Mysite::$app->config["tablepre"] . "memberlog where type = 1 and   userid = " . $this->member["uid"] . " and addtype =1 and  addtime > " . $checktime);

					if (0 < Mysite::$app->config["maxdayscore"]) {
						$checkguo = $checklog["jieguo"] + $scoreadd;

						if ($checkguo < Mysite::$app->config["maxdayscore"]) {
						}
						else if ($checklog["jieguo"] < Mysite::$app->config["maxdayscore"]) {
							$scoreadd = Mysite::$app->config["maxdayscore"] - $checklog["jieguo"];
						}
						else {
							$scoreadd = 0;
						}
					}

					if (0 < $scoreadd) {
						$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score`=`score`+" . $scoreadd, "uid='" . $this->member["uid"] . "'");
						$fscoreadd = $scoreadd;
						$memberallcost = $this->member["score"] + $scoreadd;
						$this->memberCls->addlog($this->member["uid"], 1, 1, $scoreadd, "评价商品", "评价商品" . $orderdet["goodsname"] . "获得" . $scoreadd . "积分", $memberallcost);
					}
				}
			}
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "order", "`is_ping`=1", "id='" . $orderinfo["id"] . "'");
		$ordCls = new orderclass();
		$ordCls->writewuliustatus($orderinfo["id"], 11, $orderinfo["paytype"]);
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $orderinfo["id"] . "' and status = 0");

		if ($shuliang < 1) {
			if ((0 < intval(Mysite::$app->config["commentscore"])) && ($issong == 1)) {
				$scoreadd = intval(Mysite::$app->config["commentscore"]) * $orderinfo["allcost"];
				$checktime = date("Y-m-d", time());
				$checktime = strtotime($checktime);
				$checklog = $this->mysql->select_one("select sum(result) as jieguo from " . Mysite::$app->config["tablepre"] . "memberlog where type = 1 and   userid = " . $this->member["uid"] . " and addtype =1 and  addtime > " . $checktime);

				if (0 < Mysite::$app->config["maxdayscore"]) {
					$checkguo = $checklog["jieguo"] + $scoreadd;

					if ($checkguo < Mysite::$app->config["maxdayscore"]) {
					}
					else if ($checklog["jieguo"] < Mysite::$app->config["maxdayscore"]) {
						$scoreadd = Mysite::$app->config["maxdayscore"] - $checklog["jieguo"];
					}
					else {
						$scoreadd = 0;
					}
				}

				if (0 < $scoreadd) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score`=`score`+" . $scoreadd, "uid='" . $this->member["uid"] . "'");
					$memberallcost = $this->member["score"] + $scoreadd + $fscoreadd;
					$this->memberCls->addlog($this->member["uid"], 1, 1, $scoreadd, "评价完订单", "评价完订单" . $orderinfo["dno"] . "奖励，" . $scoreadd . "积分", $memberallcost);
				}
			}
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $orderinfo["shopid"] . "' ");
		$shuliang = $shopinfo["point"];
		$pointcount = $shopinfo["pointcount"];
		$psservicepoint = $shopinfo["psservicepoint"];
		$psservicepointcount = $shopinfo["psservicepointcount"];
		$newpoint["point"] = intval($shoppointnum + $shuliang);
		$newpoint["pointcount"] = intval($pointcount + 1);
		$newpoint["psservicepoint"] = intval($psservicepoint + $psservicepointcount);
		$newpoint["psservicepointcount"] = intval($psservicepointcount + 1);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $newpoint, "id='" . $orderinfo["shopid"] . "'");
		$this->success("success");
	}

	public function guestorderlist()
	{
		$this->setstatus();
		$phone = IFilter::act(IReq::get("phone"));
		$link = IUrl::creatUrl("order/guestorder");
		$Captcha = IFilter::act(IReq::get("Captcha"));
		$type = IFilter::act(IReq::get("type"));

		if (Mysite::$app->config["allowedcode"] == 1) {
			if ($Captcha != ICookie::get("Captcha")) {
				$this->message("member_codeerr", $link);
			}
		}

		if (!IValidate::suremobi($phone)) {
			$this->message("errphone");
		}

		$data["phone"] = $phone;
		$data["Captcha"] = $Captcha;
		$data["where"] = " buyerphone = '" . $phone . "'";

		if (empty($type)) {
			$data["where"] .= " and shoptype=0";
		}
		else if ($type == 1) {
			$data["where"] .= " and shoptype=1";
		}
		else if ($type == 100) {
			$data["where"] .= " and shoptype=100";
		}

		$data["type"] = $type;
		Mysite::$app->setdata($data);
	}

	public function commentshop()
	{
		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$type = trim(IFilter::act(IReq::get("type")));
		$data["list"] = array();

		if ($type == "shop") {
			$this->pageCls->setpage(intval(IReq::get("page")), 3);
			$data["list"] = $this->mysql->getarr("select a.*,b.username,b.logo,c.name from " . Mysite::$app->config["tablepre"] . "comment as a left join  " . Mysite::$app->config["tablepre"] . "member as b on a.uid = b.uid left join " . Mysite::$app->config["tablepre"] . "goods as c on a.goodsid = c.id  where a.shopid=" . $shopid . " and a.is_show  =0 order by a.id desc   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
			$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "comment   where shopid=" . $shopid . "  and is_show  =0 ");
			$this->pageCls->setnum($shuliang);
			$data["pagecontent"] = $this->pageCls->ajaxbar("getPingjia");
		}

		Mysite::$app->setdata($data);
	}

	public function drawuserorder()
	{
		$orderid = intval(IReq::get("orderid"));

		if (!empty($orderid)) {
			$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");
			$data["order"] = $order;

			if (0 < $order["is_reback"]) {
				$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid='" . $order["id"] . "'  ");
				$data["drawbacklog"] = $drawbacklog;
			}

			Mysite::$app->setdata($data);
		}
		else {
			$data["order"] = "";
			Mysite::$app->setdata($data);
		}
	}

	public function savedrawbacklog()
	{
		if (empty($this->member["uid"])) {
			$this->message("member_nologin");
		}

		$drawbacklog = new drawbacklog($this->mysql, $this->memberCls);
		$check = $drawbacklog->save();

		if ($check == true) {
			$this->success("success");
		}
		else {
			$msg = $drawbacklog->GetErr();
			$this->message($msg);
		}
	}
}


