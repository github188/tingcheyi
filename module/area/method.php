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
	public function adminpsset()
	{
		$data["pestypearr"] = array(1 => "网站统一配送费", 2 => "根据不同区域统一配送费", 3 => "不计算配送费", 4 => "百度地图根据距离测算配送费", 5 => "根据菜品数计算配送费");
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

	public function shopidtoad()
	{
		$this->checkshoplogin();
		$areaid = intval(IReq::get("areaid"));
		$types = IFilter::act(IReq::get("types"));
		$shopid = ICookie::get("adminshopid");

		if (!in_array($types, array("add", "del"))) {
			$this->message("nodefined_func");
		}

		$checkarea = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $areaid . "'   ");

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		if (empty($checkarea)) {
			$this->message("area_empty");
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");

		if ($types == "add") {
			$whiledata = $checkarea;
			$tempcheckid = array();

			while (!empty($whiledata)) {
				$checkarea = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "areashop where areaid ='" . $whiledata["id"] . "'  and shopid = '" . $shopid . "' ");

				if (0 < $shopinfo["admin_id"]) {
					if ($checkarea["admin_id"] != $shopinfo["admin_id"]) {
						$this->message("area_adminiderr");
					}
				}

				if (empty($areatocost)) {
					$parentinfo["shopid"] = $shopid;
					$parentinfo["areaid"] = $whiledata["id"];
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "areashop", $parentinfo);
				}

				$tempcheckid[] = $whiledata["id"];

				if ($whiledata["parent_id"] == 0) {
					break;
				}

				if (in_array($whiledata["parent_id"], $tempcheckid)) {
					break;
				}

				$whiledata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $whiledata["parent_id"] . "'   ");
			}
		}
		else {
			$whiledata = $checkarea;
			$tempcheckid = array();

			while (!empty($whiledata)) {
				$checkdeldata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "areashop where areaid in(select id from " . Mysite::$app->config["tablepre"] . "area where  parent_id ='" . $whiledata["id"] . "')  and shopid = '" . $shopid . "' ");

				if (!empty($checkdeldata)) {
					break;
				}

				$this->mysql->delete(Mysite::$app->config["tablepre"] . "areashop", "areaid ='" . $whiledata["id"] . "' and shopid = '" . $shopid . "'");
				$tempcheckid[] = $whiledata["id"];

				if ($whiledata["parent_id"] == 0) {
					break;
				}

				if (in_array($whiledata["parent_id"], $tempcheckid)) {
					break;
				}

				$whiledata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $whiledata["parent_id"] . "'   ");
			}
		}

		$this->success("success");
	}

	public function shoptoadcost()
	{
		$this->checkshoplogin();
		$areaid = intval(IReq::get("areaid"));
		$cost = IFilter::act(IReq::get("cost"));
		$cost = intval($cost * 10) / 10;

		if (empty($areaid)) {
			$this->message("area_empty");
		}

		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		$data["cost"] = $cost;
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");

		if ($shopinfo["shoptype"] == 0) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "areatoadd", $data, "shopid='" . $shopid . "' and areaid = '" . $areaid . "'");
		}
		else if ($shopinfo["shoptype"] == 1) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "areatomar", $data, "shopid='" . $shopid . "' and areaid = '" . $areaid . "'");
		}

		$this->success("success");
	}

	public function shoptoappcost()
	{
		$this->checkshoplogin();
		$gongli = intval(IReq::get("gongli"));
		$cost = intval(IFilter::act(IReq::get("cost")));
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");

		if ($shopinfo["shoptype"] == 0) {
			$fastfood = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");
			$pradius = (empty($fastfood["pradius"]) ? 1 : intval($fastfood["pradius"]));
			$tempdoid = (empty($fastfood["pradiusvalue"]) ? array() : unserialize($fastfood["pradiusvalue"]));
			$result = array();

			for ($i = 0; $i < $pradius; $i++) {
				if ($i == $gongli) {
					$result[$i] = $cost;
				}
				else {
					$result[$i] = (isset($tempdoid[$i]) ? $tempdoid[$i] : 0);
				}
			}

			$data["pradiusvalue"] = serialize($result);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopid . "' ");
		}
		else if ($shopinfo["shoptype"] == 1) {
			$fastfood = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid=" . $shopid . "  ");
			$pradius = (empty($fastfood["pradius"]) ? 1 : intval($fastfood["pradius"]));
			$tempdoid = (empty($fastfood["pradiusvalue"]) ? array() : unserialize($fastfood["pradiusvalue"]));
			$result = array();

			for ($i = 0; $i < $pradius; $i++) {
				if ($i == $gongli) {
					$result[$i] = $cost;
				}
				else {
					$result[$i] = (isset($tempdoid[$i]) ? $tempdoid[$i] : 0);
				}
			}

			$data["pradiusvalue"] = serialize($result);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopid . "' ");
		}

		$this->success("success");
	}

	public function useraddress()
	{
		$this->checkmemberlogin();
		$data["addressid"] = intval(IReq::get("addressid"));
		$data["area_grade"] = (empty(Mysite::$app->config["area_grade"]) ? 1 : Mysite::$app->config["area_grade"]);
		$data["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area  where  parent_id = 0 limit 0,100");
		$temparea = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   ");
		$areatoname = array();

		foreach ($temparea as $key => $value ) {
			$areatoname[$value["id"]] = $value["name"];
		}

		$areatoname[0] = "";
		$data["areatoname"] = $areatoname;
		$data["addresslimit"] = Mysite::$app->config["addresslimit"];
		Mysite::$app->setdata($data);
	}

	public function saveaddress()
	{
		$this->checkmemberlogin();
		$addressid = intval(IReq::get("addressid"));

		if (empty($addressid)) {
			$checknum = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "' ");

			if (Mysite::$app->config["addresslimit"] < $checknum) {
				$this->message("member_addresslimit");
			}

			$arr["userid"] = $this->member["uid"];
			$arr["username"] = $this->member["username"];
			$arr["bigadr"] = IFilter::act(IReq::get("bigadr"));
			$arr["lat"] = IFilter::act(IReq::get("lat"));
			$arr["lng"] = IFilter::act(IReq::get("lng"));
			$arr["detailadr"] = IFilter::act(IReq::get("detailadr"));
			$arr["address"] = IFilter::act(IReq::get("add_new"));
			$arr["phone"] = IFilter::act(IReq::get("phone"));
			$arr["carnumber"] = IFilter::act(IReq::get("carnumber"));
			$arr["otherphone"] = "";
			$arr["contactname"] = IFilter::act(IReq::get("contactname"));
			$arr["sex"] = IFilter::act(IReq::get("sex"));
			$arr["default"] = ($checknum == 0 ? 1 : 0);
			$arr["addtime"] = time();

			if (!IValidate::len($arr["contactname"], 2, 6)) {
				$this->message("contactlength");
			}

			if (!IValidate::len(IFilter::act(IReq::get("add_new")), 3, 50)) {
				$this->message("member_addresslength");
			}

			if (!IValidate::phone($arr["phone"])) {
				$this->message("errphone");
			}
			if (is_mobile_request()) {

				if (empty($arr["bigadr"]) || ($arr["bigadr"] == "点击选择地址")) {
					$this->message("请选择地址！");
				}

				if (empty($arr["detailadr"])) {
					$this->message("请填写详细地址！");
				}
				if (empty($arr["carnumber"])) {
					$this->message("请填写车牌号码！");
				}
			}

			if (!empty($arr["otherphone"]) && !IValidate::phone($arr["otherphone"])) {
				$this->message("errphone");
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "address", $arr);
			$this->success("success");
		}
		else {
			$arr["bigadr"] = IFilter::act(IReq::get("bigadr"));
			$arr["lat"] = IFilter::act(IReq::get("lat"));
			$arr["lng"] = IFilter::act(IReq::get("lng"));
			$arr["detailadr"] = IFilter::act(IReq::get("detailadr"));
			$arr["address"] = IFilter::act(IReq::get("add_new"));
			$arr["phone"] = IFilter::act(IReq::get("phone"));
			$arr["carnumber"] = IFilter::act(IReq::get("carnumber"));
			$arr["otherphone"] = "";
			$arr["contactname"] = IFilter::act(IReq::get("contactname"));
			$arr["sex"] = IFilter::act(IReq::get("sex"));
			$arr["addtime"] = time();

			if (!IValidate::len($arr["contactname"], 2, 6)) {
				$this->message("contactlength");
			}

			if (!IValidate::len(IFilter::act(IReq::get("add_new")), 3, 50)) {
				$this->message("member_addresslength");
			}

			if (!IValidate::phone($arr["phone"])) {
				$this->message("errphone");
			}
			if (is_mobile_request()) {
				if (empty($arr["bigadr"]) || ($arr["bigadr"] == "点击选择地址")) {
					$this->message("请选择地址！");
				}
				if (empty($arr["carnumber"])) {
					$this->message("请填写车牌号码！");
				}
				if (empty($arr["detailadr"])) {
					$this->message("请填写详细地址！");
				}
			}
			if (!empty($arr["otherphone"]) && !IValidate::phone($arr["otherphone"])) {
				$this->message("errphone");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "userid = " . $this->member["uid"] . " and id=" . $addressid . "");
			$this->success("success");
		}

		$this->success("success");
	}

	public function deladdress()
	{
		$this->checkmemberlogin();
		$uid = intval(IReq::get("addressid"));

		if (empty($uid)) {
			$this->message("member_noexitaddress");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "address", "id = '$uid' and  userid='" . $this->member["uid"] . "'");
		$this->success("success");
	}

	public function editaddress()
	{
		$this->checkmemberlogin();
		$what = trim(IFilter::act(IReq::get("what")));
		$addressid = intval(IReq::get("addressid"));

		if (empty($addressid)) {
			$this->message("member_noexitaddress");
		}

		if ($what == "default") {
			$arr["default"] = 0;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "userid='" . $this->member["uid"] . "'");
			$arr["default"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "id='" . $addressid . "' and userid='" . $this->member["uid"] . "' ");
			$this->success("success");
		}
		else if ($what == "addr") {
			$arr["address"] = IFilter::act(IReq::get("controlname"));

			if (!IValidate::len($arr["address"], 3, 50)) {
				$this->message("member_addresslength");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "id='" . $addressid . "' and userid='" . $this->member["uid"] . "' ");
			$this->success("success");
		}
		else if ($what == "phone") {
			$arr["phone"] = IFilter::act(IReq::get("controlname"));

			if (!IValidate::phone($arr["phone"])) {
				$this->message("errphone");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "id='" . $addressid . "' and userid='" . $this->member["uid"] . "' ");
			$this->success("success");
		}
		else if ($what == "bak_phone") {
			$arr["otherphone"] = IFilter::act(IReq::get("controlname"));

			if (!IValidate::phone($arr["otherphone"])) {
				$this->message("errphone");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "id='" . $addressid . "' and userid='" . $this->member["uid"] . "' ");
			$this->success("success");
		}
		else if ($what == "recieve_name") {
			$arr["contactname"] = IFilter::act(IReq::get("controlname"));

			if (!IValidate::len($arr["contactname"], 2, 6)) {
				$this->message("contactlength");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "id='" . $addressid . "' and userid='" . $this->member["uid"] . "' ");
			$this->success("success");
		}
		else {
			$this->message("nodefined_func");
		}
	}

	public function shopbaidumap()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  id = '" . $shopid . "' order by id asc");
		$data["dlng"] = (empty($shopinfo["lng"]) || ($shopinfo["lng"] == "0.000000") ? Mysite::$app->config["baidulng"] : $shopinfo["lng"]);
		$data["dlat"] = (empty($shopinfo["lat"]) || ($shopinfo["lat"] == "0.000000") ? Mysite::$app->config["baidulat"] : $shopinfo["lat"]);
		$data["baidumapkey"] = Mysite::$app->config["baidumapkey"];
		Mysite::$app->setdata($data);
	}

	public function savemapshoplocation()
	{
		$this->checkshoplogin();
		$data["lng"] = IReq::get("lng");
		$data["lat"] = IReq::get("lat");
		$shopid = ICookie::get("adminshopid");

		if (empty($data["lng"])) {
			$this->message("emptylng");
		}

		if (empty($data["lat"])) {
			$this->message("emptylat");
		}

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopid = ICookie::get("adminshopid");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  id = '" . $shopid . "' order by id asc");

		if (!empty($shopinfo)) {
			$areasearch = new areasearch($this->mysql);
			$areasearch->setdata($shopinfo["shopname"], "2", $shopinfo["id"], $data["lat"], $data["lng"]);
			$areasearch->del();
			$areasearch->save();
			$areasearch->setdata($shopinfo["address"], "2", $shopinfo["id"], $data["lat"], $data["lng"]);
			$areasearch->save();
		}

		$this->success("success");
	}

	public function setshoparea()
	{
		$this->checkshoplogin();
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		$this->getgodigui($areainfo, 0, 0);
		$data["arealist"] = $this->digui;
		Mysite::$app->setdata($data);
	}
}


