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
		$tempareacost = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "areashop  where   shopid = 0  limit 0,100");
		$tempids = array();
		$areacost = array();

		foreach ($tempareacost as $key => $value ) {
			$tempids[] = $value["areaid"];
		}

		$tempids = join(",", $tempids);
		$data["myarealist"] = array();

		if (!empty($tempids)) {
			$data["myarealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area  where  id in(" . $tempids . ")   limit 0,1000");
		}

		$data["area_grade"] = Mysite::$app->config["area_grade"];
		$nowaddressinfo = array();

		if (!empty($this->member["uid"])) {
			$nowaddressinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address where  userid =" . $this->member["uid"] . " and `default` =1 order by id desc  ");
		}

		$data["mydefaultadress"] = $nowaddressinfo;
		Mysite::$app->setdata($data);
	}

	public function newexchang()
	{
		$userid = (empty($this->member["uid"]) ? 0 : $this->member["uid"]);
		$username = IFilter::act(IReq::get("username"));
		$remark = IFilter::act(IReq::get("remark"));
		$area1 = intval(IReq::get("area1"));
		$area2 = intval(IReq::get("area2"));
		$area3 = intval(IReq::get("area3"));
		$mobile = IFilter::act(IReq::get("mobile"));
		$addressdet = IFilter::act(IReq::get("addressdet"));
		$goodsids = IFilter::act(IReq::get("goodsids"));
		$goodscount = IFilter::act(IReq::get("goodscount"));

		if (empty($goodsids)) {
			$this->message("礼品品列表中无任何礼品");
		}

		if ($userid == 0) {
			$this->message("未登陆不可兑换");
		}

		$goodsids = (is_array($goodsids) ? $goodsids : array($goodsids));
		$goodscount = (is_array($goodscount) ? $goodscount : array($goodscount));

		if (!IValidate::suremobi($mobile)) {
			$this->message("手机号码不正确");
		}

		if (empty($username)) {
			$this->message("联系人用户不能为空");
		}

		$info["goodscount"] = array();

		foreach ($goodsids as $key => $value ) {
			$info["goodscount"][$value] = $goodscount[$key];
		}

		$info["goodsids"] = $goodsids;
		$alllpin = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "gift where id in(" . join(",", $info["goodsids"]) . ")  order by id asc  ");

		if (empty($alllpin)) {
			$this->message("获取礼品失败");
		}

		$allarea = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where id in(" . $area1 . "," . $area2 . "," . $area3 . ") order by id asc  ");
		$temparea = "";

		foreach ($allarea as $key => $value ) {
			$temparea .= $value["name"];
		}

		$mydownscore = 0;

		foreach ($alllpin as $key => $value ) {
			if ($value["stock"] < intval($info["goodscount"][$value["id"]])) {
				$this->message($value["title"] . "库存数量不足");
			}

			if (intval($info["goodscount"][$value["id"]]) < 1) {
				$this->message($value["title"] . "兑换数量错误");
			}

			$mydownscore += intval($info["goodscount"][$value["id"]]) * $value["score"];
		}

		if ($this->member["score"] < $mydownscore) {
			$this->message("个人积分兑换不足");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score` = `score`-" . $mydownscore . "", "uid='" . $this->member["uid"] . "'");
		$acount = $this->member["score"] - $mydownscore;
		$this->memberCls->addlog($this->member["uid"], 1, 2, $mydownscore, "兑换礼品", "兑换ID为:" . join(",", $goodsids) . "的礼品,帐号积分" . $acount, $acount);
		$data["uid"] = $this->member["uid"];
		$data["addtime"] = time();
		$data["status"] = 0;
		$data["address"] = $temparea . $addressdet;
		$data["telphone"] = $mobile;
		$data["contactman"] = $username;

		foreach ($alllpin as $key => $value ) {
			if (0 < intval($info["goodscount"][$value["id"]])) {
				$data["giftid"] = $value["id"];
				$data["count"] = intval($info["goodscount"][$value["id"]]);
				$data["score"] = intval($info["goodscount"][$value["id"]]) * $value["score"];
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "giftlog", $data);
				$this->mysql->update(Mysite::$app->config["tablepre"] . "gift", "`stock` = `stock`-" . $data["count"] . ",`sell_count`=`sell_count`+" . $data["count"], "id='" . $value["id"] . "'");
			}
		}

		$this->success("操作成功");
	}

	public function exchang()
	{
		$this->checkmemberlogin();
		$userid = (empty($this->member["uid"]) ? 0 : $this->member["uid"]);

		if (empty($userid)) {
			$this->message("member_nologin");
		}

		$lipin_id = intval(IReq::get("lipin_id"));

		if (empty($lipin_id)) {
			$this->message("gift_empty");
		}

		$lipininfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "gift where id ='" . $lipin_id . "'  order by id asc  ");

		if (empty($lipininfo)) {
			$this->message("gift_empty");
		}

		if ($lipininfo["stock"] < 1) {
			$this->message("gift_emptystock");
		}

		$moren_addr = intval(IReq::get("address_id"));

		if (empty($moren_addr)) {
			$data["address"] = IFilter::act(IReq::get("address"));
			$data["contactman"] = IFilter::act(IReq::get("aboutname"));
			$data["telphone"] = IFilter::act(IReq::get("aboutphone"));
			$data["content"] = IFilter::act(IReq::get("content"));

			if (empty($data["contactman"])) {
				$this->message("emptycontact");
			}

			if (empty($data["telphone"])) {
				$this->message("errphone");
			}

			if (empty($data["address"])) {
				$this->message("emptyaddress");
			}
		}
		else {
			$addressinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address where  id = '" . $moren_addr . "' order by id desc  ");

			if (empty($moren_addr)) {
				$this->message("member_noexitaddress");
			}

			if ($addressinfo["userid"] != $userid) {
				$this->message("member_noexitaddress");
			}

			$data["address"] = $addressinfo["address"];
			$data["contactman"] = $addressinfo["contactname"];
			$data["telphone"] = $addressinfo["phone"];
			$data["content"] = IFilter::act(IReq::get("content"));
		}

		if ($this->member["score"] < $lipininfo["score"]) {
			$this->message("member_scoredown");
		}

		$ndata["score"] = $this->member["score"] - $lipininfo["score"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $ndata, "uid='" . $this->member["uid"] . "'");
		$data["giftid"] = $lipininfo["id"];
		$data["uid"] = $userid;
		$data["addtime"] = time();
		$data["status"] = 0;
		$data["count"] = 1;
		$data["score"] = $lipininfo["score"];
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "giftlog", $data);
		$this->memberCls->addlog($this->member["uid"], 1, 2, $lipininfo["score"], "兑换礼品", "兑换" . $lipininfo["title"] . "减少" . $lipininfo["score"] . "积分", $ndata["score"]);
		$lidata["stock"] = $lipininfo["stock"] - 1;
		$lidata["sell_count"] = $lipininfo["sell_count"] + 1;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "gift", $lidata, "id='" . $lipin_id . "'");
		$this->success("success");
	}

	public function usergift()
	{
		$this->checkmemberlogin();
		$this->logstat();
	}

	public function logstat()
	{
		$data["logstat"] = array("待处理", "已处理，配送中", "兑换完成", "兑换成功", "已取消兑换");
		Mysite::$app->setdata($data);
	}

	public function ungift()
	{
		$this->checkmemberlogin();
		$id = intval(IReq::get("id"));

		if (empty($id)) {
			$this->message("gift_emptygiftlog");
		}

		$info = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "giftlog where uid ='" . $this->member["uid"] . "' and id=" . $id . " ");

		if (empty($info)) {
			$this->message("gift_emptygiftlog");
		}

		if ($info["status"] != 0) {
			$this->message("gift_cantlogun");
		}

		$lipininfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "gift where id ='" . $info["giftid"] . "'  order by id asc  ");
		$titles = (isset($lipininfo["title"]) ? $lipininfo["title"] : $info["id"]);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "giftlog", array("status" => "4"), "id='" . $id . "'");
		$ndata["score"] = $this->member["score"] + $info["score"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`score` = `score`+" . $info["score"], "uid='" . $this->member["uid"] . "'");
		$this->memberCls->addlog($this->member["uid"], 1, 1, $info["score"], "取消兑换礼品", "取消兑换ID为:" . $id . "的礼品[" . $titles . "],帐号积分" . $ndata["score"], $ndata["score"]);
		$lidata["stock"] = $lipininfo["stock"] + $info["count"];
		$lidata["sell_count"] = $lipininfo["sell_count"] - $info["count"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "gift", $lidata, "id='" . $info["giftid"] . "'");
		$this->success("success");
	}
}


