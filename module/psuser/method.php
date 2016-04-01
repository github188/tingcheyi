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
	public function checkapp()
	{
		$uid = trim(IFilter::act(IReq::get("uid")));
		$pwd = trim(IFilter::act(IReq::get("pwd")));
		$member = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "' ");
		$backarr = array("uid" => 0);

		if (!empty($member)) {
			if ($member["password"] == md5($pwd)) {
				$backarr = $member;
			}
		}

		return $backarr;
	}

	public function applogin()
	{
		$uname = trim(IFilter::act(IReq::get("uname")));
		$pwd = trim(IFilter::act(IReq::get("pwd")));

		if (empty($uname)) {
			$this->message("用户名为空");
		}

		if (empty($pwd)) {
			$this->message("密码为空");
		}

		if (!$this->memberCls->login($uname, $pwd)) {
			$this->message($this->memberCls->ero());
		}

		$uid = $this->memberCls->getuid();
		$member = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "' ");
		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "locationpsy  where uid = '" . $uid . "' order by uid asc limit 0,1");

		if (empty($checkinfo)) {
			$data["uid"] = $uid;
			$data["lat"] = 0;
			$data["lng"] = 0;
			$data["addtime"] = time();
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "locationpsy", $data);
		}

		unset($member["password"]);
		$this->success($member);
	}

	public function waitorder()
	{
		$statusarr = array("新订单", "待发货", "待评价", "已完成", "关闭", "关闭");
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$todatay = strtotime(date("Y-m-d", time()));
		$endtime = $todatay + 86399;
		$where = " where posttime > " . $todatay . " and status > 0 and status < 3 and (psuid = 0 or psuid is null) and is_goshop = 0 and pstype = 0 and is_make < 2   ";
		$orderlist = $this->mysql->getarr("select id,addtime,posttime,dno,allcost,status,is_make,daycode,shopname,is_ping,shopaddress,buyeraddress from " . Mysite::$app->config["tablepre"] . "order " . $where . " order by posttime desc  ");
		$backdatalist = array();

		foreach ($orderlist as $key => $value ) {
			$value["showstatus"] = $statusarr[$value["status"]];
			$value["creattime"] = date("m-d H:i", $value["addtime"]);
			$value["dotime"] = date("m-d H:i", $value["posttime"]);
			$value["addtime"] = date("Y-m-d H:i:s", $value["addtime"]);

			if ($value["status"] == 1) {
				if ($value["is_make"] == 0) {
					$value["showstatus"] = "商家未制作";
				}
				else if ($value["is_make"] == 2) {
					$value["showstatus"] = "无效订单";
					$value["status"] = 4;
				}
				else if ($value["is_make"] == 1) {
					$value["showstatus"] = "商家已制作";
				}
			}
			else if ($value["status"] == 3) {
				if (empty($value["is_ping"])) {
					$value["showstatus"] = "待评价";
				}
			}

			$backdatalist[] = $value;
		}

		$this->success($backdatalist);
	}

	public function getmyorder()
	{
		$statusarr = array("新订单", "待发货", "待完成", "已完成", "关闭", "关闭");
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$todatay = strtotime(date("Y-m-d", time()));
		$endtime = $todatay + 86399;
		$where = " where posttime > " . $todatay . " and posttime < " . $endtime . " and status > 0 and status < 4  and is_goshop = 0 and pstype = 0  and psuid = '" . $backinfo["uid"] . "' and is_make < 2 ";
		$orderlist = $this->mysql->getarr("select id,addtime,posttime,dno,allcost,status,is_make,daycode,shopname,is_ping,shopaddress,buyeraddress from " . Mysite::$app->config["tablepre"] . "order " . $where . " order by posttime desc  ");
		$backdatalist = array();

		foreach ($orderlist as $key => $value ) {
			$value["showstatus"] = $statusarr[$value["status"]];
			$value["creattime"] = date("m-d H:i", $value["addtime"]);
			$value["dotime"] = date("m-d H:i", $value["posttime"]);
			$value["addtime"] = date("Y-m-d H:i:s", $value["addtime"]);

			if ($value["status"] == 1) {
				if ($value["is_make"] == 0) {
					$value["showstatus"] = "商家未制作";
				}
				else if ($value["is_make"] == 2) {
					$value["showstatus"] = "无效订单";
					$value["status"] = 4;
				}
				else if ($value["is_make"] == 1) {
					$value["showstatus"] = "商家已制作";
				}
			}

			$backdatalist[] = $value;
		}

		$this->success($backdatalist);
	}

	public function joinorder()
	{
	}

	public function sendorder()
	{
	}

	public function location()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$lat = trim(IFilter::act(IReq::get("lat")));
		$lng = trim(IFilter::act(IReq::get("lng")));

		if (!empty($lat)) {
			$data["lat"] = $lat;
			$data["lng"] = $lng;
			$data["addtime"] = time();
			$this->mysql->update(Mysite::$app->config["tablepre"] . "locationpsy", $data, "uid='" . $backinfo["uid"] . "'");
			$channelid = trim(IFilter::act(IReq::get("channelid")));
			$userid = trim(IFilter::act(IReq::get("userid")));

			if (!empty($userid)) {
				$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "apploginps where uid='" . $backinfo["uid"] . "' ");
				$Mdata["channelid"] = $channelid;
				$Mdata["userid"] = $userid;
				$Mdata["uid"] = $backinfo["uid"];
				$Mdata["addtime"] = time();

				if (!empty($checkinfo)) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "apploginps", $Mdata, "uid='" . $backinfo["uid"] . "'");
				}
				else {
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "apploginps", $Mdata);
				}
			}

			$this->success("操作成功");
		}

		$this->message("暂无订单");
	}

	public function psyapptj()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$showlisttype = intval(IFilter::act(IReq::get("showlisttype")));
		$starttime = 0;
		$endtime = 0;

		if ($showlisttype == 1) {
			$date = date("Y-m-d");
			$w = date("w", strtotime($date));
			$first = 1;
			$now_start = date("Y-m-d", strtotime("$date -" . ($w ? $w - $first : 6) . " days"));
			$now_end = date("Y-m-d", strtotime("$now_start +6 days"));
			$starttime = strtotime($now_start);
			$endtime = strtotime($now_end) + 86399;
		}
		else if ($showlisttype == 2) {
			$BeginDate = date("Y-m-01", strtotime(date("Y-m-d", time())));
			$enddata = date("Y-m-d", strtotime("$BeginDate +1 month -1 day"));
			$starttime = strtotime($BeginDate);
			$endtime = strtotime($enddata) + 86399;
		}
		else {
			$BeginDate = date("Y-m-d", time());
			$starttime = strtotime($BeginDate);
			$endtime = strtotime($BeginDate) + 86399;
		}

		$bkdata = array();
		$newdata["name"] = "开始时间";
		$newdata["value"] = date("Y-m-d H:i:s", $starttime);
		$bkdata[] = $newdata;
		$newdata["name"] = "结束时间";
		$newdata["value"] = date("Y-m-d H:i:s", $endtime);
		$bkdata[] = $newdata;
		$where2 .= " and  addtime  > " . $starttime . " and addtime < " . $endtime;
		$alltj = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $backinfo["uid"] . "'  and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
		$newdata["name"] = "总送订单数";
		$newdata["value"] = $alltj["shuliang"];
		$bkdata[] = $newdata;
		$newdata["name"] = "总金额";
		$newdata["value"] = round($alltj["doallcost"], 2);
		$bkdata[] = $newdata;
		$unline = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $backinfo["uid"] . "' and paytype ='outpay' and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
		$newdata["name"] = "线下交易订单数";
		$newdata["value"] = $unline["shuliang"];
		$bkdata[] = $newdata;
		$newdata["name"] = "线下交易总金额";
		$newdata["value"] = round($unline["doallcost"], 2);
		$bkdata[] = $newdata;
		$line = $this->mysql->select_one("select count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost,sum(shopcost) as shopcost, sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $backinfo["uid"] . "' and paytype !='outpay'  and paystatus =1 and shopcost > 0 and status = 3 " . $where2 . "   order by id asc  limit 0,1000");
		$newdata["name"] = "在线交易总单数";
		$newdata["value"] = $line["shuliang"];
		$bkdata[] = $newdata;
		$newdata["name"] = "在线交易总总金额";
		$newdata["value"] = round($line["doallcost"], 2);
		$bkdata[] = $newdata;
		$this->success($bkdata);
	}

	public function ordercontrol()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$dotype = trim(IFilter::act(IReq::get("dotype")));
		$orderid = intval(IFilter::act(IReq::get("orderid")));

		if (empty($orderid)) {
			$this->message("订单获取失败");
		}

		if ($dotype == "joinorder") {
			$data["psuid"] = $backinfo["uid"];
			$data["psusername"] = $backinfo["username"];
			$data["psemail"] = $backinfo["email"];
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $data, "id='" . $orderid . "' and (psuid = 0 or psuid is null)");
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "' ");

			if ($checkinfo["psuid"] == $backinfo["uid"]) {
				$this->success("抢单成功");
			}
			else {
				$this->message("未抢到该订单");
			}
		}
		else if ($dotype == "sendorder") {
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "' ");

			if ($checkinfo["psuid"] != $backinfo["uid"]) {
				$this->message("该订单不属于您");
			}

			if ($checkinfo["is_make"] == 2) {
				$this->message("商家不制作该订单");
			}

			if ($checkinfo["status"] != 1) {
				$this->message("该订单不在配送状态");
			}

			$data["status"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $data, "id='" . $orderid . "'");
			$this->success("操作成功");
		}
		else if ($dotype == "overorder") {
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "' ");

			if ($checkinfo["psuid"] != $backinfo["uid"]) {
				$this->message("该订单不属于您");
			}

			if ($checkinfo["is_make"] == 2) {
				$this->message("商家不制作该订单");
			}

			if ($checkinfo["status"] != 2) {
				$this->message("该订单不在配送状态");
			}

			$data["status"] = 3;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $data, "id='" . $orderid . "'");
			$this->success("操作成功");
		}
		else {
			$this->message("未定义的操作");
		}
	}

	public function appbuyerone()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$statusarr = array("新订单不可操作", "抢单配送", "确认发货", "确认完成", "已完成", "关闭");
		$paytypelist = array("货到支付", "在线支付");
		$shoptypearr = array("购卡", "养车", "其他");
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[$value["loginname"]] = $value["logindesc"];
			}
		}

		$orderid = trim(IFilter::act(IReq::get("orderid")));
		$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "' ");

		if (empty($order)) {
			$this->message("订单不存在");
		}

		$backdata["dno"] = $order["dno"];
		$backdata["addtime"] = date("Y-m-d H:i:s", $order["addtime"]);
		$backdata["id"] = $order["id"];
		$backdata["allcost"] = $order["allcost"];
		$backdata["shopcost"] = $order["shopcost"];
		$backdata["shopname"] = $order["shopname"];
		$backdata["showstatus"] = $statusarr[$order["status"]];
		$backdata["buyerphone"] = $order["buyerphone"];
		$backdata["shopphone"] = $order["shopphone"];
		$backdata["shopaddress"] = $order["shopaddress"];
		$backdata["buyeraddress"] = $order["buyeraddress"];
		$backdata["posttime"] = date("Y-m-d H:i:s", $order["posttime"]);
		$backdata["psstatus"] = 0;

		if ($order["status"] == 0) {
			$backdata["psstatus"] = 0;
		}
		else if ($order["status"] == 1) {
			if ($order["is_make"] == 0) {
				$backdata["psstatus"] = 1;

				if (0 < $order["psuid"]) {
					$backdata["psstatus"] = 2;
				}
			}
			else if ($order["is_make"] == 2) {
				$backdata["psstatus"] = 5;
			}
			else if ($order["is_make"] == 1) {
				$backdata["psstatus"] = 1;

				if (0 < $order["psuid"]) {
					$backdata["psstatus"] = 2;
				}
			}
		}
		else if ($order["status"] == 2) {
			$backdata["psstatus"] = 3;

			if ($order["is_make"] == 2) {
				$backdata["psstatus"] = 5;
			}
		}
		else if ($order["status"] == 3) {
			$backdata["psstatus"] = 4;
		}
		else {
			$backdata["psstatus"] = 5;
		}

		$backdata["showstatus"] = $statusarr[$backdata["psstatus"]];
		$backdata["is_ping"] = $order["is_ping"];
		$backdata["is_make"] = $order["is_make"];
		$backdata["status"] = $order["status"];
		$temlist = array();
		$dotem = (empty($order["paystatus"]) ? "未支付" : "已支付");
		$templist[]["mytext"] = "订单编号：" . $order["dno"];
		$templist[]["mytext"] = "买家电话：" . $order["buyerphone"];
		$temppaytype = (isset($paytypelist[$order["paytype"]]) ? $paytypelist[$order["paytype"]] : "未定义");
		$backdata["showpaytyepname"] = $temppaytype;
		$backdata["showpaydo"] = $dotem;
		$templist[]["mytext"] = "店铺电话：" . $order["shopphone"];
		$templist[]["mytext"] = "备注：" . $order["content"];

		if (0 < $order["bagcost"]) {
			$templist[]["mytext"] = "服务费：" . $order["bagcost"];
		}

		if (0 < $order["cxcost"]) {
			$templist[]["mytext"] = "促销优惠：" . $order["cxcost"];
		}

		if (0 < $order["yhjcost"]) {
			$templist[]["mytext"] = "优惠券抵扣：" . $order["yhjcost"];
		}

		if (0 < $order["shopps"]) {
			$templist[]["mytext"] = "配送费：" . $order["shopps"];
		}

		$backdata["itemlist"] = $templist;
		$templist = $this->mysql->getarr("select id,order_id,goodsname,goodscost,goodscount from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $orderid . "' ");
		$newdatalist = array();
		$shuliang = 0;

		foreach ($templist as $key => $value ) {
			$value["goodscost"] = $value["goodscost"];
			$newdatalist[] = $value;
			$shuliang += $value["goodscount"];
		}

		$backdata["det"] = $newdatalist;
		$this->success($backdata);
	}

	public function checkappMem()
	{
		$uid = trim(IFilter::act(IReq::get("uid")));
		$pwd = trim(IFilter::act(IReq::get("pwd")));
		$mapname = trim(IFilter::act(IReq::get("mapname")));
		$uid = (empty($uid) ? ICookie::get("appuid") : $uid);
		$pwd = (empty($pwd) ? ICookie::get("apppwd") : $pwd);
		$member = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "' ");
		$backarr = array("uid" => 0);

		if (!empty($member)) {
			if ($member["password"] == md5($pwd)) {
				if ($member["group"] == 2) {
					$backarr = $member;
					ICookie::set("appuid", $member["uid"], 86400);
					ICookie::set("apppwd", $pwd, 86400);
					ICookie::set("appmapname", $mapname, 86400);
				}
			}
		}

		return $backarr;
	}

	public function appuserinfo()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		unset($backinfo["password"]);
		$this->success($backinfo);
	}

	public function modify()
	{
		$backinfo = $this->checkappMem();

		if (empty($backinfo["uid"])) {
			$this->message("nologin");
		}

		$oldpwd = IFilter::act(IReq::get("oldpwd"));
		$newpwd = IFilter::act(IReq::get("newpwd"));
		$surepwd = IFilter::act(IReq::get("surepwd"));

		if (empty($oldpwd)) {
			$this->message("旧密码不能为空");
		}

		if (empty($newpwd)) {
			$this->message("新密码不能为空");
		}

		if ($newpwd != $surepwd) {
			$this->message("新密码和确认密码不一致");
		}

		if ($backinfo["password"] != md5($oldpwd)) {
			$this->message("旧密码错误");
		}

		$newdata["password"] = md5($newpwd);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $newdata, "uid='" . $backinfo["uid"] . "'");
		unset($backinfo["password"]);
		$this->success($backinfo);
	}

	public function mypsordertj()
	{
		$this->checkmemberlogin();
		$stime = IFilter::act(IReq::get("stime"));
		$etime = IFilter::act(IReq::get("etime"));
		$where2 = "";
		$nowdata = strtotime(date("Y-m-d", time()));
		$mintime = $nowdata;
		$maxtime = $nowdata + 86399;
		$stime = (empty($stime) ? $mintime : strtotime($stime . " 00:01"));
		$etime = (empty($etime) ? $maxtime : strtotime($etime . " 23:59"));
		$where2 .= " and  addtime  > " . $stime . " and addtime < " . $etime;
		$data["allorder"] = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $this->member["uid"] . "'  and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
		$data["unline"] = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $this->member["uid"] . "' and paytype ='outpay' and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
		$data["line"] = $this->mysql->select_one("select count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost,sum(shopcost) as shopcost, sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost,sum(allcost) as doallcost from " . Mysite::$app->config["tablepre"] . "order  where psuid = '" . $this->member["uid"] . "' and paytype !='outpay'  and paystatus =1 and shopcost > 0 and status = 3 " . $where2 . "   order by id asc  limit 0,1000");
		$data["stime"] = $stime;
		$data["etime"] = $etime;
		Mysite::$app->setdata($data);
	}

	public function mypsorder()
	{
		$this->checkmemberlogin();
		$stime = IFilter::act(IReq::get("stime"));
		$etime = IFilter::act(IReq::get("etime"));
		$where = "";
		$nowdata = strtotime(date("Y-m-d", time()));
		$mintime = $nowdata;
		$maxtime = $nowdata + 86399;
		$stime = (empty($stime) ? $mintime : strtotime($stime . " 00:01"));
		$etime = (empty($etime) ? $maxtime : strtotime($etime . " 23:59"));
		$where .= " and  posttime  > " . $stime . " and posttime < " . $etime;
		$this->setstatus();
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 8);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where psuid='" . $this->member["uid"] . "'   " . $where . " order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where psuid='" . $this->member["uid"] . "'    " . $where . " ");
		$pageinfo->setnum($shuliang);
		$data["pagecontent"] = $pageinfo->getpagebar();
		$data["pageall"] = $pageinfo->totalpage();
		$data["pagenow"] = (intval(IReq::get("page")) == 0 ? 1 : intval(IReq::get("page")));
		$data["allcount"] = $shuliang;
		$data["nowtime"] = time();
		$data["stime"] = $stime;
		$data["etime"] = $etime;
		$link = IUrl::creatUrl("psuser/mypsorder/stime/" . date("Y-m-d", $stime) . "/etime/" . date("Y-m-d", $etime) . "/page/@page@");
		$data["pagelink"] = $link;
		Mysite::$app->setdata($data);
	}

	public function setstatus()
	{
		$data["buyerstatus"] = array("待处理订单", "待发货", "订单已发货", "订单完成", "买家取消订单", "卖家取消订单");
		$paytypelist = array("outpay" => "货到支付", "open_acout" => "账号余额支付");
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[$value["loginname"]] = $value["logindesc"];
			}
		}

		$data["shoptype"] = array("购卡", "养车", "其他");
		$data["ordertypearr"] = array("网站", "网站", "电话", "微信", "APP", "手机网站", "卖家取消订单");
		$data["backarray"] = array("", "退款中..", "退款成功", "");
		$data["paytypearr"] = $paytypelist;
		Mysite::$app->setdata($data);
	}
}


