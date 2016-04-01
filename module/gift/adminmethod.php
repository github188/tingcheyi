<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class method extends adminbaseclass
{
	public function savegifttype()
	{
		$id = intval(IReq::get("uid"));
		$data["name"] = IReq::get("name");
		$data["orderid"] = intval(IReq::get("orderid"));

		if (empty($data["name"])) {
			$this->message("gift_emptytypename");
		}

		if (empty($id)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "gifttype", $data);
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "gifttype", $data, "id='" . $id . "'");
		}

		$this->success("success");
	}

	public function delgifttype()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("gift_emptytype");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "gifttype", "id in($ids)");
		$this->success("success");
	}

	public function savegift()
	{
		$id = IReq::get("uid");
		$data["title"] = IReq::get("title");
		$data["content"] = IReq::get("content");
		$data["market_cost"] = intval(IReq::get("market_cost"));
		$data["typeid"] = IReq::get("typeid");
		$data["score"] = intval(IReq::get("score"));
		$data["stock"] = intval(IReq::get("stock"));
		$data["img"] = IReq::get("img");
		$data["sell_count"] = intval(IReq::get("sell_count"));

		if (empty($id)) {
			$link = IUrl::creatUrl("adminpage/gift/module/addgift");

			if (empty($data["content"])) {
				$this->message("gift_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("gift_emptytitle", $link);
			}

			if (empty($data["score"])) {
				$this->message("gift_emptyscore", $link);
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "gift", $data);
		}
		else {
			$link = IUrl::creatUrl("adminpage/gift/module/addgift/id/" . $id);

			if (empty($data["content"])) {
				$this->message("gift_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("gift_emptytitle", $link);
			}

			if (empty($data["score"])) {
				$this->message("gift_emptyscore", $link);
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "gift", $data, "id='" . $id . "'");
		}

		$link = IUrl::creatUrl("adminpage/gift/module/giftlist");
		$this->message("success", $link);
	}

	public function delgift()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("gift_empty");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "gift", "id in($ids)");
		$this->success("success");
	}

	public function logstat()
	{
		$data["logstat"] = array("待处理", "已处理，配送中", "兑换完成", "兑换成功", "已取消兑换");
		Mysite::$app->setdata($data);
	}

	public function giftlog()
	{
		$orderstatus = intval(IReq::get("orderstatus"));
		$starttime = trim(IReq::get("starttime"));
		$endtime = trim(IReq::get("endtime"));
		$newlink = "";
		$where = "";
		$data["orderstatus"] = "";

		if (0 < $orderstatus) {
			$chastatus = $orderstatus - 1;
			$data["orderstatus"] = $orderstatus;
			$where .= " and  gg.status = '" . $chastatus . "' ";
			$newlink .= "/orderstatus/" . $orderstatus;
		}

		$data["starttime"] = "";

		if (!empty($starttime)) {
			$data["starttime"] = $starttime;
			$where .= " and  gg.addtime > " . strtotime($starttime . " 00:00:01") . " ";
			$newlink .= "/starttime/" . $starttime;
		}

		$data["endtime"] = "";

		if (!empty($endtime)) {
			$data["endtime"] = $endtime;
			$where .= " and  gg.addtime < " . strtotime($endtime . " 23:59:59") . " ";
			$newlink .= "/endtime/" . $endtime;
		}

		$link = IUrl::creatUrl("adminpage/gift/module/giftlog" . $newlink);
		$data["outlink"] = IUrl::creatUrl("adminpage/gift/module/outgiftlog/outtype/query" . $newlink);
		$this->pageCls->setpage(IReq::get("page"));
		$data["list"] = $this->mysql->getarr("select gg.*,gf.title,mb.username from " . Mysite::$app->config["tablepre"] . "giftlog as gg left join " . Mysite::$app->config["tablepre"] . "gift as gf on gf.id = gg.giftid  left join " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid=gg.uid where  gg.id > 0  " . $where . " order by gg.id desc  limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select gg.* from " . Mysite::$app->config["tablepre"] . "giftlog as gg where  gg.id > 0 " . $where . " ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function delgiftlog()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("gift_emptygiftlog");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "giftlog", " id in($ids) ");
		$this->success("success");
	}

	public function outgiftlog()
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
			$where .= " and gg.id in(" . $id . ") ";
		}
		else {
			$orderstatus = intval(IReq::get("orderstatus"));
			$where .= (0 < $orderstatus ? " and   gg.status = '" . ($orderstatus - 1) . "' " : "");
			$starttime = trim(IReq::get("starttime"));
			$where .= (!empty($starttime) ? " and   gg.addtime > " . strtotime($starttime . " 00:00:01") . " " : "");
			$endtime = trim(IReq::get("endtime"));
			$where .= (!empty($endtime) ? " and   gg.addtime > " . strtotime($endtime . " 23:59:59") . " " : "");
		}

		$outexcel = new phptoexcel();
		$titledata = array("礼品名称", "用户名", "用户地址", "联系电话", "联系人");
		$titlelabel = array("title", "username", "address", "telphone", "contactman");
		$datalist = $this->mysql->getarr("select gf.title,mb.username,gg.address,gg.telphone,gg.contactman from " . Mysite::$app->config["tablepre"] . "giftlog as gg left join " . Mysite::$app->config["tablepre"] . "gift as gf on gf.id = gg.giftid  left join " . Mysite::$app->config["tablepre"] . "member as mb on mb.uid=gg.uid where  gg.id > 0  " . $where . " order by gg.id desc  limit 0,2000");
		$outexcel->out($titledata, $titlelabel, $datalist, "", "积分兑换导出结果");
	}

	public function exgift()
	{
		$id = intval(IReq::get("id"));
		$type = IReq::get("type");

		if (empty($id)) {
			$this->message("gift_empty");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "giftlog where id=" . $id . "  ");

		if (empty($checkinfo)) {
			$this->message("gift_emptygiftlog");
		}

		$giftinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "gift where id=" . $checkinfo["giftid"] . "  ");

		if (empty($giftinfo)) {
			$this->message("gift_empty");
		}

		$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid=" . $checkinfo["uid"] . "  ");

		switch ($type) {
		case $type:
			if ($checkinfo["status"] != 0) {
				$this->message("gift_cantlogun");
			}

			$data["status"] = 4;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", $data, "id='" . $id . "'");

			if (!empty($memberinfo)) {
				$ndata["score"] = $memberinfo["score"] + $checkinfo["score"];
				$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score` = `score`+" . $checkinfo["score"], "uid='" . $memberinfo["uid"] . "'");
				$this->memberCls->addlog($memberinfo["uid"], 1, 1, $checkinfo["score"], "取消兑换礼品", "管理员取消兑换ID为:" . $giftinfo["id"] . "的礼品[" . $giftinfo["title"] . "],帐号积分" . $ndata["score"], $ndata["score"]);
			}

			$gdata["sell_count"] = $giftinfo["sell_count"] - $checkinfo["count"];
			$gdata["stock"] = $giftinfo["stock"] + $checkinfo["count"];
			$this->mysql->update(Mysite::$app->config["tablepre"] . "gift", $gdata, "id='" . $giftinfo["id"] . "'");
			break;

		case $type:
			if ($checkinfo["status"] != 0) {
				$this->message("gift_cantlogpass");
			}

			$data["status"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", $data, "id='" . $id . "'");
			break;

		case $type:
			if ($checkinfo["status"] != 1) {
				$this->message("gift_cantlogunpass");
			}

			$data["status"] = 0;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", $data, "id='" . $id . "'");
			break;

		case $type:
			if ($checkinfo["status"] != 1) {
				$this->message("gift_cantlogsend");
			}

			$data["status"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", $data, "id='" . $id . "'");
			break;

		case $type:
			if ($checkinfo["status"] != 2) {
				$this->message("gift_cantlogover");
			}

			$data["status"] = 3;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", $data, "id='" . $id . "'");
			break;

		default:
			$this->message("nodefined_func");
			break;
		}

		$this->success("success");
	}
}

$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}

