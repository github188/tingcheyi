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
class method extends areaadminbaseclass
{
	public function index()
	{
		$link = IUrl::creatUrl("adminpage/order/module/orderlist");
		$this->refunction("", $link);
	}

	public function adminfastfoods()
	{
		$data["shoplist"] = $this->mysql->getarr("select id,shopname  from " . Mysite::$app->config["tablepre"] . "shop where is_open = 1 and is_pass=1 and endtime > " . time() . " and admin_id = '" . $this->admin["uid"] . "' order by id limit 0,1000");
		Mysite::$app->setdata($data);
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

	public function orderlist()
	{
		$this->setstatus();
		$querytype = IReq::get("querytype");
		$searchvalue = IReq::get("searchvalue");
		$orderstatus = intval(IReq::get("orderstatus"));
		$starttime = IReq::get("starttime");
		$endtime = IReq::get("endtime");
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "  where ord.addtime > " . strtotime($starttime . " 00:00:00") . " and ord.addtime < " . strtotime($endtime . " 23:59:59");
		$data["starttime"] = $starttime;
		$data["endtime"] = $endtime;
		$newlink = "/starttime/" . $starttime . "/endtime/" . $endtime;
		$data["searchvalue"] = "";
		$data["querytype"] = "";

		if (!empty($querytype)) {
			if (!empty($searchvalue)) {
				$data["searchvalue"] = $searchvalue;
				$where .= " and " . $querytype . " LIKE '%" . $searchvalue . "%' ";
				$newlink .= "/searchvalue/" . $searchvalue . "/querytype/" . $querytype;
				$data["querytype"] = $querytype;
			}
		}

		$data["orderstatus"] = "";

		if (0 < $orderstatus) {
			if (4 < $orderstatus) {
				$where .= (empty($where) ? " where ord.status > 3 " : " and ord.status > 3 ");
			}
			else {
				$newstatus = $orderstatus - 1;
				$where .= (empty($where) ? " where ord.status =" . $newstatus : " and ord.status = " . $newstatus);
			}

			$data["orderstatus"] = $orderstatus;
			$newlink .= "/orderstatus/" . $orderstatus;
		}

		$link = IUrl::creatUrl("/adminpage/order/module/orderlist" . $newlink);
		$pageshow = new page();
		$pageshow->setpage(IReq::get("page"), 5);
		$where .= " and ord.admin_id = '" . $this->admin["uid"] . "'";
		$orderlist = $this->mysql->getarr("select ord.*,mb.username as acountname from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " order by ord.id desc limit " . $pageshow->startnum() . ", " . $pageshow->getsize() . "");
		$shuliang = $this->mysql->counts("select ord.*,mb.username as acountname from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " ");
		$pageshow->setnum($shuliang);
		$data["pagecontent"] = $pageshow->getpagebar($link);
		$data["list"] = array();

		if ($orderlist) {
			foreach ($orderlist as $key => $value ) {
				$value["detlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $value["id"] . " order by id desc ");
				$data["list"][] = $value;
			}
		}

		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		Mysite::$app->setdata($data);
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

	public function ordertoday()
	{
		$firstareain = IReq::get("firstarea");
		$secareain = IReq::get("secarea");
		$statustype = intval(IReq::get("statustype"));
		$dno = IReq::get("dno");
		$data["dno"] = $dno;
		$data["statustype"] = $statustype;
		$statustype = (in_array($statustype, array(1, 2, 3, 4, 5)) ? $statustype : 0);
		$statustypearr = array("", " and ord.status = 0 ", " and ord.status = 1  ", " and ord.status > 1 and ord.status < 4 ", " and ord.is_reback in(1,2)  ");
		$data["frinput"] = $firstareain;
		$this->setstatus();
		$nowday = date("Y-m-d", time());
		$where = "  where ord.posttime > " . strtotime($nowday . " 00:00:00") . " and ord.posttime < " . strtotime($nowday . " 23:59:59");

		if (!empty($firstareain)) {
			$where .= " and FIND_IN_SET('" . $firstareain . "',`areaids`)";
		}

		$where .= $statustypearr[$statustype];
		$where .= (empty($dno) ? "" : " and ord.dno ='" . $dno . "'");
		$where .= " and ord.admin_id ='" . $this->admin["uid"] . "'";
		$orderlist = $this->mysql->getarr("select ord.*,mb.username as acountname from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " order by ord.id desc limit 0,1000");
		$shuliang = $this->mysql->counts("select ord.*,mb.username as acountname from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " ");
		$data["list"] = array();

		if ($orderlist) {
			foreach ($orderlist as $key => $value ) {
				$value["detlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $value["id"] . " order by id desc ");
				$value["maijiagoumaishu"] = 0;

				if (0 < $value["buyeruid"]) {
					$value["maijiagoumaishu"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $value["buyeruid"] . "' and  status = 3 order by id desc");
				}

				$data["list"][] = $value;
			}
		}

		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where admin_id='" . $this->admin["uid"] . "'  order by orderid asc");
		$this->getgodigui($areainfo, 0, 0);
		$data["arealist"] = $this->digui;
		$data["showdet"] = intval(IReq::get("showdet"));
		$data["playwave"] = ICookie::get("playwave");
		Mysite::$app->setdata($data);
	}

	public function ordertotal()
	{
		$data["buyerstatus"] = array("待处理订单", "审核通过,待发货", "订单已发货", "订单完成", "买家取消订单", "卖家取消订单");
		$querytype = IReq::get("querytype");
		$searchvalue = IReq::get("searchvalue");
		$orderstatus = intval(IReq::get("orderstatus"));
		$starttime = IReq::get("starttime");
		$endtime = IReq::get("endtime");
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "  where ord.posttime > " . strtotime($starttime . " 00:00:00") . " and ord.posttime < " . strtotime($endtime . " 23:59:59");
		$data["starttime"] = $starttime;
		$data["endtime"] = $endtime;
		$data["querytype"] = "";
		$data["searchvalue"] = "";

		if (!empty($querytype)) {
			if (!empty($searchvalue)) {
				$data["searchvalue"] = $searchvalue;
				$where .= " and " . $querytype . " ='" . $searchvalue . "' ";
				$data["querytype"] = $querytype;
			}
		}

		$where = " and ord.admin_id='" . $this->admin["uid"] . "'";
		$data["list"] = $this->mysql->getarr("select count(ord.id) as shuliang,ord.status,sum(allcost) as allcost,sum(scoredown) as scorecost from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " group by ord.status order by ord.id desc limit 0, 10");
		Mysite::$app->setdata($data);
	}

	public function orderyjin()
	{
		$searchvalue = IReq::get("searchvalue");
		$starttime = trim(IReq::get("starttime"));
		$endtime = trim(IReq::get("endtime"));
		$newlink = "";
		$where = "";
		$where2 = "";
		$data["searchvalue"] = "";

		if (!empty($searchvalue)) {
			$data["searchvalue"] = $searchvalue;
			$where .= " where shopname = '" . $searchvalue . "' ";
			$newlink .= "/searchvalue/" . $searchvalue;
		}

		$data["starttime"] = "";

		if (!empty($starttime)) {
			$data["starttime"] = $starttime;
			$where2 .= " and  posttime > " . strtotime($starttime . " 00:00:01") . " ";
			$newlink .= "/starttime/" . $starttime;
		}

		$data["endtime"] = "";

		if (!empty($endtime)) {
			$data["endtime"] = $endtime;
			$where2 .= " and  posttime < " . strtotime($endtime . " 23:59:59") . " ";
			$newlink .= "/endtime/" . $endtime;
		}

		$link = IUrl::creatUrl("areaadminpage/order/module/orderyjin" . $newlink);
		$data["outlink"] = IUrl::creatUrl("areaadminpage/order/module/outtjorder/outtype/query" . $newlink);
		$data["outlinkch"] = IUrl::creatUrl("areaadminpage/order/module/outtjorder" . $newlink);
		$where = (empty($where) ? " where admin_id ='" . $this->admin["uid"] . "'" : $where . " and admin_id='" . $this->admin["uid"] . "'");
		$pageinfo = new page();
		$pageinfo->setpage(IReq::get("page"));
		$shoplist = $this->mysql->getarr("select id,shopname,yjin,shoptype from " . Mysite::$app->config["tablepre"] . "shop " . $where . "   order by id asc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$list = array();

		if (is_array($shoplist)) {
			foreach ($shoplist as $key => $value ) {
				if ($value["shoptype"] == 0) {
					$sendtype = $this->mysql->value(Mysite::$app->config["tablepre"] . "shopfast", "sendtype", "shopid = '" . $value["id"] . "'");
				}
				else if ($value["shoptype"] == 1) {
					$sendtype = $this->mysql->value(Mysite::$app->config["tablepre"] . "shopmarket", "sendtype", "shopid = '" . $value["id"] . "'");
				}

				$value["sendtype"] = (empty($sendtype) ? "网站配送" : "自送");
				$shoptj = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $value["id"] . "' and paytype ='outpay' and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
				$line = $this->mysql->select_one("select count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost,sum(shopcost) as shopcost, sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $value["id"] . "' and paytype !='outpay'  and paystatus =1 and shopcost > 0 and status = 3 " . $where2 . "   order by id asc  limit 0,1000");
				$value["orderNum"] = $shoptj["shuliang"] + $line["shuliang"];
				$scordedown = (!empty(Mysite::$app->config["scoretocost"]) ? $line["score"] / Mysite::$app->config["scoretocost"] : 0);
				$value["onlinescore"] = $scordedown;
				$value["online"] = ($line["shopcost"] + $line["pscost"] + $line["bagcost"]) - $line["cxcost"] - $line["yhcost"] - $scordedown;
				$scordedown = (!empty(Mysite::$app->config["scoretocost"]) ? $shoptj["score"] / Mysite::$app->config["scoretocost"] : 0);
				$value["unlinescore"] = $scordedown;
				$value["unline"] = ($shoptj["shopcost"] + $shoptj["pscost"] + $shoptj["bagcost"]) - $shoptj["cxcost"] - $shoptj["yhcost"] - $scordedown;
				$value["yhjcost"] = $line["yhcost"] + $shoptj["yhcost"];
				$value["cxcost"] = $line["cxcost"] + $shoptj["cxcost"];
				$value["score"] = $value["unlinescore"] + $value["onlinescore"];
				$value["bagcost"] = $line["bagcost"] + $shoptj["bagcost"];
				$value["pscost"] = $line["pscost"] + $shoptj["pscost"];
				$value["allcost"] = ($line["shopcost"] + $shoptj["shopcost"]) - $value["cxcost"];
				$value["goodscost"] = $line["shopcost"] + $shoptj["shopcost"];
				$yjinb = (empty($value["yjin"]) ? Mysite::$app->config["yjin"] : $value["yjin"]);
				$value["yb"] = $yjinb * 0.01;
				$value["yje"] = $value["yb"] * $value["allcost"];
				$value["outdetail"] = IUrl::creatUrl("areaadminpage/order/module/outdetail/outtype/query/shopid/" . $value["id"] . $newlink);
				$list[] = $value;
			}
		}

		$data["list"] = $list;
		$shuliang = $this->mysql->counts("select id from " . Mysite::$app->config["tablepre"] . "shop " . $where . "  ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function ordercontrol()
	{
		$id = intval(IReq::get("id"));
		$type = IReq::get("type");

		if (empty($id)) {
			$this->message("订单ID错误");
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $id . "'  ");

		if (empty($orderinfo)) {
			$this->message("订单获取失败");
		}

		if ($orderinfo["admin_id"] != $this->admin["uid"]) {
			$this->message("订单不属于您管理");
		}

		switch ($type) {
		case $type:
			$reasons = IReq::get("reasons");
			$suresend = IReq::get("suresend");

			if (empty($reasons)) {
				$this->message("关闭理由不能为空");
			}

			if (2 < $orderinfo["status"]) {
				$this->message("订单状态不可关闭");
			}

			if (!empty($orderinfo["buyeruid"])) {
				$detail = "";
				$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $orderinfo["buyeruid"] . "'   ");
				if (($orderinfo["paystatus"] == 1) && ($orderinfo["paytype"] != "outpay")) {
					$this->message("订单已支付，请在退款处理中关闭该订单");

					if (0 < $orderinfo["scoredown"]) {
						$memberscs = $memberinfo["score"] + $orderinfo["scoredown"];
						$this->memberCls->addlog($orderinfo["buyeruid"], 1, 1, $orderinfo["scoredown"], "取消订单", "管理员关闭订单" . $orderinfo["dno"] . "抵扣积分" . $orderinfo["scoredown"] . "返回帐号", $memberscs);
					}
				}
				else if (0 < $orderinfo["scoredown"]) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score`=`score`+" . $orderinfo["scoredown"], "uid ='" . $orderinfo["buyeruid"] . "' ");
					$memberscs = $memberinfo["score"] + $orderinfo["scoredown"];
					$this->memberCls->addlog($orderinfo["buyeruid"], 1, 1, $orderinfo["scoredown"], "取消订单", "管理员关闭订单" . $orderinfo["dno"] . "抵扣积分" . $orderinfo["scoredown"] . "返回帐号", $memberscs);
				}
			}

			$orderdata["status"] = 5;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $id . "'");
			$ordetinfo = $this->mysql->getarr("select ort.goodscount,go.id,go.sellcount,go.count as stroe from " . Mysite::$app->config["tablepre"] . "orderdet as ort left join  " . Mysite::$app->config["tablepre"] . "goods as go on go.id = ort.goodsid   where ort.order_id='" . $id . "' and  go.id > 0 ");

			foreach ($ordetinfo as $key => $value ) {
				$newdata["count"] = $value["stroe"] + $value["goodscount"];
				$newdata["sellcount"] = $value["sellcount"] - $value["goodscount"];
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $newdata, "id='" . $value["id"] . "'");
			}

			if ($suresend == 1) {
				$ordCls = new orderclass();
				$ordCls->noticeclose($id, $reasons);
			}

			break;

		case $type:
			if ($orderinfo["status"] != 0) {
				$this->message("订单状态不可通过审核");
			}

			if ((0 < $orderinfo["is_reback"]) && ($orderinfo["is_reback"] < 3)) {
				$this->message("订单退款中不可操作");
			}

			$checkstr = Mysite::$app->config["auto_send"];

			if ($checkstr == 1) {
				$orderdata["status"] = 2;
			}
			else {
				$orderdata["status"] = 1;
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $id . "'");

			if (Mysite::$app->config["man_ispass"] == 1) {
				$ordCls = new orderclass();
				$ordCls->sendmess($id);
			}

			break;

		case $type:
			if ((0 < $orderinfo["is_reback"]) && ($orderinfo["is_reback"] < 3)) {
				$this->message("订单退款中不可操作");
			}

			if ($orderinfo["status"] != 1) {
				$this->message("订单状态不可发货");
			}

			$orderdata["status"] = 2;
			$orderdata["sendtime"] = time();
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $id . "'");
			$ordCls = new orderclass();
			$ordCls->noticesend($id);
			break;

		case $type:
			if ((0 < $orderinfo["is_reback"]) && ($orderinfo["is_reback"] < 3)) {
				$this->message("订单退款中不可操作");
			}

			if ($orderinfo["status"] != 2) {
				$this->message("订单状态不可完成");
			}

			$orderdata["status"] = 3;
			$orderdata["suretime"] = time();
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $id . "'");

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

			break;

		case $type:
			if ($orderinfo["status"] < 4) {
				$this->message("订单状态不可删除");
			}

			$this->mysql->delete(Mysite::$app->config["tablepre"] . "order", "id = '$id'");
			break;

		case $type:
			$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid=" . $id . " order by  id desc  limit 0,2");

			if (empty($drawbacklog)) {
				$this->message("退款记录为空");
			}

			if ($drawbacklog["status"] != 0) {
				$this->message("退款记录状态不可操作");
			}

			if (2 < $orderinfo["status"]) {
				$this->message("订单状态" . $orderinfo["status"] . "不可操作退款");
			}

			$arr["is_reback"] = 2;
			$arr["status"] = 4;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $arr, "id='" . $id . "'");
			$data["bkcontent"] = IReq::get("reasons");
			$data["status"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "drawbacklog", $data, "id='" . $drawbacklog["id"] . "'");
			$ordCls = new orderclass();
			$ordCls->noticeback($id);
			break;

		case $type:
			$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid=" . $id . " order by  id desc  limit 0,2");

			if (empty($drawbacklog)) {
				$this->message("退款记录为空");
			}

			if ($drawbacklog["status"] != 0) {
				$this->message("退款记录状态不可操作");
			}

			if (2 < $orderinfo["status"]) {
				$this->message("订单状态不可操作退款");
			}

			$arr["is_reback"] = 3;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $arr, "id='" . $id . "'");
			$data["bkcontent"] = IReq::get("reasons");
			$data["status"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "drawbacklog", $data, "id='" . $drawbacklog["id"] . "'");
			$ordCls = new orderclass();
			$ordCls->noticeunback($id);
			break;

		case $type:
			if (2 < $orderinfo["status"]) {
				$this->message("order_baklogcantdoover");
			}

			if (!empty($order["psuid"])) {
				$this->message("order_setpsyuan");
			}

			$userid = intval(IReq::get("userid"));

			if (empty($userid)) {
				$this->message("order_emptypsyuan");
			}

			$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $userid . "' and `group` =2 ");

			if (empty($memberinfo)) {
				$this->message("order_emptypsyuan");
			}

			$orderdata["psuid"] = $memberinfo["uid"];
			$orderdata["psusername"] = $memberinfo["username"];
			$orderdata["psemail"] = $memberinfo["email"];
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $id . "'");
			break;

		default:
			$this->message("未定义的操作");
			break;
		}

		$this->success("操作成功");
	}

	public function ajaxcheckorder()
	{
		$data = array();
		$nowday = date("Y-m-d", time());
		$where = "  where ord.addtime > " . strtotime($nowday) . " and ord.addtime < " . strtotime($nowday . " 23:59:59");
		$where .= " and ord.status = 0 ";
		$firstarea = intval(IReq::get("firstarea"));

		if (!empty($firstareain)) {
			$where .= " and FIND_IN_SET('" . $firstareain . "',`areaids`)";
		}

		$shuliang = $this->mysql->counts("select ord.* from " . Mysite::$app->config["tablepre"] . "order as ord   " . $where . " ");

		if (0 < $shuliang) {
			$this->success("操作成功");
		}
		else {
			$this->message("操作成功");
		}
	}

	public function outorderlimit()
	{
		$outtype = IReq::get("outtype");

		if (!in_array($outtype, array("query", "ids"))) {
			header("Content-Type: text/html; charset=UTF-8");
			echo "查询条件错误";
			exit();
		}

		$where = "";

		if ($outtype == "ids") {
			$id = trim(IReq::get("id"));

			if (empty($id)) {
				header("Content-Type: text/html; charset=UTF-8");
				echo "查询条件不能为空";
				exit();
			}

			$doid = explode("-", $id);
			$id = join(",", $doid);
			$where .= " where ord.id in(" . $id . ") ";
		}
		else {
			$starttime = intval(IReq::get("starttime"));
			$endtime = intval(IReq::get("endtime"));
			$status = intval(IReq::get("status"));
			$where .= "  where ord.posttime > " . $starttime . " and ord.posttime < " . $endtime;

			if (!empty($status)) {
				$where .= " and ord.status =" . $status . "";
			}
			else {
				$where .= " and ord.status > 1  and ord.status < 4 ";
			}
		}

		$orderlist = $this->mysql->getarr("select ord.*,mb.username as acountname from " . Mysite::$app->config["tablepre"] . "order as ord left join  " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid = ord.buyeruid   " . $where . " order by ord.id desc limit 0,1000");
		$print_rdata = array();

		if ($orderlist) {
			foreach ($orderlist as $key => $value ) {
				$detlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $value["id"] . " order by order_id desc ");

				if (is_array($detlist)) {
					foreach ($detlist as $keys => $val ) {
						$newdata = array();
						$newdata["dno"] = $value["dno"];
						$newdata["shopname"] = $value["shopname"];
						$newdata["area1"] = $value["area1"];
						$newdata["area2"] = $value["area2"];
						$newdata["goodsname"] = $val["goodsname"];
						$newdata["goodscount"] = $val["goodscount"];
						$newdata["goodscost"] = $val["goodscost"];
						$newdata["buyerphone"] = $value["buyerphone"];
						$newdata["sendtime"] = $value["sendtime"];
						$newdata["buyeraddress"] = $value["buyeraddress"];
						$newdata["buyername"] = $value["buyername"];
						$newdata["content"] = $value["content"];
						$print_rdata[] = $newdata;
					}
				}
			}
		}

		$outexcel = new phptoexcel();
		$titledata = array("订单编号", "下单用户", "店铺名", "地址1", "地址2", "商品名称", "商品数量", "单价", "联系电话", "送货时间", "详细地址", "备注");
		$titlelabel = array("dno", "buyername", "shopname", "area1", "area2", "goodsname", "goodscount", "goodscost", "buyerphone", "sendtime", "buyeraddress", "content");
		$outexcel->out($titledata, $titlelabel, $print_rdata, "", "订单导出");
	}

	public function outtjorder()
	{
		$outtype = IReq::get("outtype");

		if (!in_array($outtype, array("query", "ids"))) {
			header("Content-Type: text/html; charset=UTF-8");
			echo "查询条件错误";
			exit();
		}

		$where = "";
		$where2 = "";

		if ($outtype == "ids") {
			$id = trim(IReq::get("id"));

			if (empty($id)) {
				header("Content-Type: text/html; charset=UTF-8");
				echo "查询条件不能为空";
				exit();
			}

			$doid = explode("-", $id);
			$id = join(",", $doid);
			$where .= " where id in(" . $id . ") ";
			$searchvalue = trim(IReq::get("searchvalue"));
			$where .= (!empty($searchvalue) ? " and shopname = '" . $searchvalue . "'" : "");
			$starttime = trim(IReq::get("starttime"));
			$where2 .= (!empty($starttime) ? " and  posttime > " . strtotime($starttime . " 00:00:01") . " " : "");
			$endtime = trim(IReq::get("endtime"));
			$where2 .= (!empty($endtime) ? " and  posttime < " . strtotime($endtime . " 23:59:59") . " " : "");
		}
		else {
			$searchvalue = trim(IReq::get("searchvalue"));
			$where .= (!empty($searchvalue) ? " where shopname = '" . $searchvalue . "'" : "");
			$starttime = trim(IReq::get("starttime"));
			$where2 .= (!empty($starttime) ? " and  posttime > " . strtotime($starttime . " 00:00:01") . " " : "");
			$endtime = trim(IReq::get("endtime"));
			$where2 .= (!empty($endtime) ? " and  posttime < " . strtotime($endtime . " 23:59:59") . " " : "");
		}

		$where = (empty($where) ? " where admin_id ='" . $this->admin["uid"] . "'" : $where . " and '" . $this->admin["uid"] . "'");
		$shoplist = $this->mysql->getarr("select id,shopname,yjin from " . Mysite::$app->config["tablepre"] . "shop " . $where . "   order by id asc  limit 0,2000");
		$list = array();

		if (is_array($shoplist)) {
			foreach ($shoplist as $key => $value ) {
				$sendtype = $this->mysql->value(Mysite::$app->config["tablepre"] . "shopfast", "sendtype", "shopid = '" . $value["id"] . "'");
				$value["sendtype"] = (empty($value["sendtype"]) ? "网站配送" : "自送");
				$shoptj = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $value["id"] . "' and paytype ='outpay' and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
				$line = $this->mysql->select_one("select count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost,sum(shopcost) as shopcost, sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $value["id"] . "' and paytype !='outpay'  and paystatus =1 and shopcost > 0 and status = 3 " . $where2 . "   order by id asc  limit 0,1000");
				$value["orderNum"] = $shoptj["shuliang"] + $line["shuliang"];
				$scordedown = (!empty(Mysite::$app->config["scoretocost"]) ? $line["score"] / Mysite::$app->config["scoretocost"] : 0);
				$value["onlinescore"] = $scordedown;
				$value["online"] = ($line["shopcost"] + $line["pscost"] + $line["bagcost"]) - $line["cxcost"] - $line["yhcost"] - $scordedown;
				$scordedown = (!empty(Mysite::$app->config["scoretocost"]) ? $shoptj["score"] / Mysite::$app->config["scoretocost"] : 0);
				$value["unlinescore"] = $scordedown;
				$value["unline"] = ($shoptj["shopcost"] + $shoptj["pscost"] + $shoptj["bagcost"]) - $shoptj["cxcost"] - $shoptj["yhcost"] - $scordedown;
				$value["yhjcost"] = $line["yhcost"] + $shoptj["yhcost"];
				$value["cxcost"] = $line["cxcost"] + $shoptj["cxcost"];
				$value["score"] = $value["unlinescore"] + $value["onlinescore"];
				$value["bagcost"] = $line["bagcost"] + $shoptj["bagcost"];
				$value["pscost"] = $line["pscost"] + $shoptj["pscost"];
				$value["allcost"] = ($line["shopcost"] + $shoptj["shopcost"]) - $value["cxcost"];
				$value["goodscost"] = $line["shopcost"] + $shoptj["shopcost"];
				$yjinb = (empty($value["yjin"]) ? Mysite::$app->config["yjin"] : $value["yjin"]);
				$value["yb"] = $yjinb * 0.01;
				$value["yje"] = $value["yb"] * $value["allcost"];
				$list[] = $value;
			}
		}

		$outexcel = new phptoexcel();
		$titledata = array("店铺名称", "配送方式", "订单数量", "线上支付", "线下支付", "优惠券", "店铺促销", "积分低扣金额", "配送费", "商品总价", "服务费", "佣金");
		$titlelabel = array("shopname", "sendtype", "orderNum", "online", "unline", "yhjcost", "cxcost", "score", "pscost", "goodscost", "bagcost", "yje");
		$outexcel->out($titledata, $titlelabel, $list, "", "商家结算");
	}

	public function outdetail()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			header("Content-Type: text/html; charset=UTF-8");
			echo "店铺获取失败";
			exit();
		}

		$shoplist = $this->mysql->select_one("select id,shopname,yjin,shoptype from " . Mysite::$app->config["tablepre"] . "shop  where id='" . $shopid . "' and admin_id='" . $this->admin["uid"] . "'  order by id asc  limit 0,2000");

		if (empty($shoplist)) {
			header("Content-Type: text/html; charset=UTF-8");
			echo "店铺获取失败";
			exit();
		}

		$where = "";
		$where2 = "";
		$starttime = trim(IReq::get("starttime"));
		$where2 .= (!empty($starttime) ? " and  posttime > " . strtotime($starttime . " 00:00:01") . " " : "");
		$endtime = trim(IReq::get("endtime"));
		$where2 .= (!empty($endtime) ? " and  posttime < " . strtotime($endtime . " 23:59:59") . " " : "");
		$orderlist = $this->mysql->getarr("select id,dno,allcost,bagcost,shopps,shopcost,addtime,posttime,pstype ,paytype,paystatus from " . Mysite::$app->config["tablepre"] . "order where shopid = '" . $shopid . "' and  status = 3 " . $where2 . " order by id asc  limit 0,2000");
		$list = array();

		if (is_array($orderlist)) {
			foreach ($orderlist as $key => $value ) {
				$detlist = $this->mysql->getarr("select goodsname,goodscount as shuliang from " . Mysite::$app->config["tablepre"] . "orderdet  where order_id = '" . $value["id"] . "' and shopid > 0  order by id asc  limit 0,5");
				$detinfo = "";

				if (is_array($detlist)) {
					foreach ($detlist as $keys => $val ) {
						$detinfo .= $val["goodsname"] . "/" . $val["shuliang"] . "份,";
					}
				}

				$value["content"] = $detinfo;
				$value["payname"] = ($value["paytype"] == "outpay" ? "货到支付" : "在线支付");
				$value["dotime"] = date("Y-m-d H:i:s", $value["addtime"]);
				$value["posttime"] = date("Y-m-d H:i:s", $value["posttime"]);
				$value["pstype"] = ($value["pstype"] == 0 ? "平台" : "自送");
				$list[] = $value;
			}
		}

		$outexcel = new phptoexcel();
		$titledata = array("订单编号", "订单总价", "配送类型", "店铺商品总价", "店铺配送费", "服务费", "订单详情", "支付方式", "下单时间", "配送时间");
		$titlelabel = array("dno", "allcost", "pstype", "shopcost", "shopps", "bagcost", "content", "payname", "dotime", "posttime");
		$outexcel->out($titledata, $titlelabel, $list, "", "商家结算详情" . $shoplist["shopname"]);
	}

	public function setstatus()
	{
		$data["buyerstatus"] = array("待处理订单", "待发货", "订单已发货", "订单完成", "买家取消订单", "卖家取消订单");
		$paytypelist = array("outpay" => "货到支付", "open_acout" => "账号余额支付", "weixin" => "微信支付");
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[$value["loginname"]] = $value["logindesc"];
			}
		}

		$data["shoptype"] = array("购卡", "养车", "其他");
		$data["ordertypearr"] = array("网站", "网站", "电话", "微信", "AndroidAPP", "手机网站", "iosApp", "后台客服下单", "商家后台下单", "html5手机站");
		$data["backarray"] = array("", "退款中..", "退款成功", "");
		$data["paytypearr"] = $paytypelist;
		Mysite::$app->setdata($data);
	}

	public function drawbacklog()
	{
		$pageinfo = new page();
		$pageinfo->setpage(IReq::get("page"));
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog  where admin_id = '" . $this->admin["uid"] . "' order by  id desc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog  where admin_id = '" . $this->admin["uid"] . "' order by  id desc");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		Mysite::$app->setdata($data);
	}

	public function showdrawbacklog()
	{
		$id = IFilter::act(IReq::get("id"));
		$link = IUrl::creatUrl("areaadminpage/order/module/drawbacklog");

		if (empty($id)) {
			$this->message("id获取失败", $link);
		}

		$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where id=" . $id . " and admin_id = '" . $this->admin["uid"] . "'  order by  id desc  limit 0,2");

		if (empty($drawbacklog)) {
			$this->message("退款申请获取失败", $link);
		}

		$data["oderinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id=" . $drawbacklog["orderid"] . " and admin_id = '" . $this->admin["uid"] . "'  order by  id desc  limit 0,2");
		$data["orderdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id=" . $drawbacklog["orderid"] . " order by  id desc  limit 0,2");
		$this->setstatus();
		$data["drawbacklog"] = $drawbacklog;
		Mysite::$app->setdata($data);
	}

	public function showcommlist()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("提交ID不能为空");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "comment where id='" . $id . "'  ");

		if (empty($checkinfo)) {
			$this->message("评价获取失败");
		}

		$data["is_show"] = ($checkinfo["is_show"] == 1 ? 0 : 1);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "comment", $data, "id='" . $id . "'");
		$this->success("操作成功");
	}
}


