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
	public function exchangjuan()
	{
		$this->checkmemberlogin();
		$card = trim(IFilter::act(IReq::get("card")));
		$password = trim(IFilter::act(IReq::get("password")));

		if (empty($card)) {
			$this->message("card_emptyjuancard");
		}

		if (empty($password)) {
			$this->message("card_emptyjuanpwd");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "juan where card ='" . $card . "'  and card_password = '" . $password . "' and endtime > " . time() . " and status = 0");

		if (empty($checkinfo)) {
			$this->message("card_emptyjuan");
		}

		if (0 < $checkinfo["uid"]) {
			$this->message("card_juanisuse");
		}

		$arr["uid"] = $this->member["uid"];
		$arr["status"] = 1;
		$arr["username"] = $this->member["username"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "juan", $arr, "card='" . $card . "'  and card_password = '" . $password . "' and endtime > " . time() . " and status = 0 and uid = 0");
		$mess["userid"] = $this->member["uid"];
		$mess["username"] = "";
		$mess["content"] = "绑定优惠劵" . $checkinfo["card"];
		$mess["addtime"] = time();
		$this->success("success");
	}

	public function exchangcard()
	{
		$this->checkmemberlogin();
		$card = trim(IFilter::act(IReq::get("card")));
		$password = trim(IFilter::act(IReq::get("password")));

		if (empty($card)) {
			$this->message("card_emptycard");
		}

		if (empty($password)) {
			$this->message("card_emptycardpwd");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "card where card ='" . $card . "'  and card_password = '" . $password . "' and uid =0 and status = 0");

		if (empty($checkinfo)) {
			$this->message("card_cardiuser");
		}

		$arr["uid"] = $this->member["uid"];
		$arr["status"] = 1;
		$arr["username"] = $this->member["username"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "card", $arr, "card ='" . $card . "'  and card_password = '" . $password . "' and uid =0 and status = 0");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`cost`=`cost`+" . $checkinfo["cost"], "uid ='" . $this->member["uid"] . "' ");
		$allcost = $this->member["cost"] + $checkinfo["cost"];
		$this->memberCls->addlog($this->member["uid"], 2, 1, $checkinfo["cost"], "充值卡充值", "使用充值卡" . $checkinfo["card"] . "充值" . $checkinfo["cost"] . "元", $allcost);
		$this->memberCls->addmemcostlog($this->member["uid"], $this->member["username"], $this->member["cost"], 1, $checkinfo["cost"], $allcost, "使用充值卡充值", ICookie::get("adminuid"), ICookie::get("adminname"));
		$this->success("success");
	}
}


