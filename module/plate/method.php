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
		$weekji = date("w");
		$desk = intval(IFilter::act(IReq::get("desk")));
		$desk = (in_array($desk, array(0, 1, 2, 3, 4)) ? $desk : 0);
		$data["desk"] = $desk;
		$areaids = intval(IFilter::act(IReq::get("areaids")));
		$data["areaids"] = $areaids;
		$areaid = intval(IFilter::act(IReq::get("areaid")));
		$data["areaid"] = $areaid;
		$locationtype = Mysite::$app->config["locationtype"];
		$data["goodstypedoid"] = array();
		$attrshop = array();
		$data["attrinfo"] = array();
		$where = " where is_goshop = 1 ";
		$tempwhere = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_search =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$value["is_now"] = (isset($seardata[$value["id"]]) ? $seardata[$value["id"]] : 0);
			$data["attrinfo"][] = $value;
			$doid = intval(IFilter::act(IReq::get("goodstype_" . $value["id"])));

			if (0 < $doid) {
				$data["goodstypedoid"][$value["id"]] = $doid;
				$tempwhere[] = $doid;
			}
		}

		$personarr = array("", " and a.personcount > 0 and a.personcount < 5 ", " and a.personcount > 4 and a.personcount < 9 ", " and a.personcount > 8 and a.personcount < 13 ", " and a.personcount > 12");
		$where .= $personarr[$desk];

		if (0 < count($tempwhere)) {
			$where .= " and a.shopid in (select shopid from " . Mysite::$app->config["tablepre"] . "shopsearch where second_id in(" . join($tempwhere) . ")  ) ";
		}

		if (0 < $areaids) {
			if (0 < $areaid) {
				$where .= " and a.shopid in (select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $areaid . " )";
			}
			else {
				$where .= " and a.shopid in (select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $areaids . " )";
			}
		}

		$data["searchgoodstype"] = $templist;
		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1 and type!='input' order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = 0  order by id asc limit 0,1000");
		$data["areadet"] = array();

		if (0 < $areaids) {
			$data["areadet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $areaids . " order by id asc limit 0,1000");
		}

		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by sort asc limit 0,100");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				if (0 < $value["id"]) {
					$checkinfo = $this->shopIsopen($value["is_open"], $value["starttime"], $value["is_orderbefore"], $nowhour);
					$value["opentype"] = $checkinfo["opentype"];
					$value["newstartime"] = $checkinfo["newstartime"];
					$ps = $this->pscost($value, 10);
					$value["pscost"] = $ps["pscost"];
					$value["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = '" . $value["id"] . "' ");
					$tempinfo = array();

					foreach ($value["attrdet"] as $keys => $valx ) {
						$tempinfo[] = $valx["attrid"];
					}

					$value["servertype"] = join(",", $tempinfo);
					$templist[] = $value;
				}
			}
		}

		$data["shoplist"] = $templist;
		Mysite::$app->setdata($data);
	}

	public function show()
	{
		$shop = trim(IFilter::act(IReq::get("id")));
		$weekji = date("w");
		$where = (0 < intval($shop) ? " where a.shopid = " . $shop : "where shortname='" . $shop . "'");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "   ");

		if (empty($shopinfo)) {
			$link = IUrl::creatUrl("site/index");
			$this->message("获取店铺信息失败", $link);
		}

		if ($shopinfo["endtime"] < time()) {
			$link = IUrl::creatUrl("site/index");
			$this->message("店铺已关门", $link);
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($shopinfo["postdate"]) ? unserialize($shopinfo["postdate"]) : array());
		$data["pstimelist"] = array();
		$checknow = time();
		$whilestatic = $shopinfo["befortime"];
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
					$tempt["i"] = $value["i"];
					$data["pstimelist"][] = $tempt;
				}
			}

			$nowwhiltcheck = $nowwhiltcheck + 1;
		}

		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$data["shopinfo"] = $shopinfo;
		$data["shopopeninfo"] = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopinfo["is_orderbefore"], $nowhour);
		$data["com_goods"] = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "goods where shopid = " . $shopinfo["id"] . " and is_com = 1   ");
		$goodstype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid = " . $shopinfo["id"] . " and cattype = " . $shopinfo["shoptype"] . " order by orderid asc");
		$data["goodstype"] = array();
		$tempids = array();

		foreach ($goodstype as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "goods where typeid = " . $value["id"] . " and is_dingtai = 1 and    FIND_IN_SET( " . $weekji . " , `weeks` )   and shopid=" . $shopinfo["id"] . "  ");
			$tempids[] = $value["id"];
			$data["goodstype"][] = $value;
		}

		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $shopinfo["shoptype"] . " and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["shopattr"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr  where  cattype = " . $shopinfo["shoptype"] . " and shopid = '" . $shopinfo["id"] . "'  order by firstattr asc limit 0,1000");
		$data["goodsattr"] = array();
		$goodsattr = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign  where  type = 'goods'  order by id asc limit 0,1000");

		foreach ($goodsattr as $key => $value ) {
			$data["goodsattr"][$value["id"]] = $value["imgurl"];
		}

		$data["psinfo"] = $this->pscost($shopinfo, 1);
		$sellrule = new sellrule();
		$sellrule->setdata($shopinfo["shopid"], 1000, $shopinfo["shoptype"]);
		$ruleinfo = $sellrule->getdata();
		$data["ruledata"] = array();
		if (isset($ruleinfo["cxids"]) && !empty($ruleinfo["cxids"])) {
			$data["ruledata"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "rule  where  id in(" . $ruleinfo["cxids"] . ")  order by id asc limit 0,1000");
		}

		$cximglist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign  where  type = 'cx'  order by id asc limit 0,1000");
		$data["ruleimg"] = array();

		foreach ($cximglist as $key => $value ) {
			$data["ruleimg"][$value["id"]] = $value["imgurl"];
		}

		$data["cxlist"] = $ruleinfo;
		$data["weekji"] = $weekji;
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$data["collect"] = array();

		if (!empty($this->memberinfo)) {
			$data["collect"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "collect where collectid=" . $shopinfo["id"] . " and collecttype = 0 and uid=" . $this->member["uid"] . " ");
		}

		$bzinfo = Mysite::$app->config["orderbz"];
		$data["bzlist"] = array();

		if (!empty($bzinfo)) {
			$data["bzlist"] = unserialize($bzinfo);
		}

		$addresslist = array();

		if (0 < $this->member["uid"]) {
			$addresslist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "address where userid=" . $this->member["uid"] . "  ");
		}

		$data["addresslist"] = $addresslist;
		$data["paylist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id desc  ");
		$data["juanlist"] = array();

		if (!empty($this->member["uid"])) {
			$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid ='" . $this->member["uid"] . "'  and status = 1 and endtime > " . time() . "  order by id desc limit 0,20");
		}

		Mysite::$app->setdata($data);
	}

	public function makeorder()
	{
		$subtype = intval(IReq::get("subtype"));
		$info["shopid"] = intval(IReq::get("shopid"));
		$info["remark"] = IFilter::act(IReq::get("content"));
		$info["paytype"] = IFilter::act(IReq::get("paytype"));
		$info["username"] = IFilter::act(IReq::get("contactname"));
		$info["carnumber"] = IFilter::act(IReq::get("contactcarnumber"));
		$info["mobile"] = IFilter::act(IReq::get("phone"));
		$info["addressdet"] = IFilter::act(IReq::get("addressdet"));
		$info["senddate"] = "";
		$info["minit"] = IFilter::act(IReq::get("minit"));
		$info["juanid"] = intval(IReq::get("juanid"));
		$info["ordertype"] = 1;
		$peopleNum = IFilter::act(IReq::get("personcount"));
		$info["othercontent"] = (empty($peopleNum) ? "" : serialize(array("人数" => $peopleNum)));
		$info["userid"] = (!isset($this->member["score"]) ? "0" : $this->member["uid"]);

		if (Mysite::$app->config["allowedguestbuy"] != 1) {
			if ($info["userid"] == 0) {
				$this->message("member_nologin");
			}
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $info["shopid"] . "'    ");

		if (empty($shopinfo)) {
			$this->message("店铺不存在");
		}

		$checksend = Mysite::$app->config["ordercheckphone"];

		if ($checksend == 1) {
			if (empty($this->member["uid"])) {
				$checkphone = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "mobile where phone ='" . $info["mobile"] . "'   order by addtime desc limit 0,50");

				if (empty($checkphone)) {
					$this->message("member_emailyan");
				}

				if (empty($checkphone["is_send"])) {
					$mycode = IFilter::act(IReq::get("phonecode"));

					if ($mycode == $checkphone["code"]) {
						$this->mysql->update(Mysite::$app->config["tablepre"] . "mobile", array("is_send" => 1), "phone='" . $info["mobile"] . "'");
					}
					else {
						$this->message("member_emailyan");
					}
				}
			}
		}

		if (empty($info["username"])) {
			$this->message("emptycontact");
		}

		if (!IValidate::suremobi($info["mobile"])) {
			$this->message("errphone");
		}

		$info["ipaddress"] = "";
		$ip_l = new iplocation();
		$ipaddress = $ip_l->getaddress($ip_l->getIP());

		if (isset($ipaddress["area1"])) {
			$info["ipaddress"] = $ipaddress["ip"] . mb_convert_encoding($ipaddress["area1"], "UTF-8", "GB2312");
		}

		$info["cattype"] = 0;

		if ($shopinfo["is_open"] != 1) {
			$this->message("店铺暂停营业");
		}

		$tempdata = $this->getOpenPosttime($shopinfo["is_orderbefore"], $shopinfo["starttime"], $shopinfo["postdate"], $info["minit"], $shopinfo["befortime"]);

		if ($tempdata["is_opentime"] == 2) {
			$this->message("选择的停车时间段，店铺未设置");
		}

		if ($tempdata["is_opentime"] == 3) {
			$this->message("选择的停车时间段已超时");
		}

		$info["sendtime"] = $tempdata["is_posttime"];
		$info["postdate"] = $tempdata["is_postdate"];
		$info["paytype"] = ($info["paytype"] == 1 ? 1 : 0);
		$info["areaids"] = "";
		$info["shopinfo"] = $shopinfo;

		if ($subtype == 1) {
			$info["allcost"] = 0;
			$info["bagcost"] = 0;
			$info["allcount"] = 0;
			$info["goodslist"] = array();
		}
		else {
			if (empty($info["shopid"])) {
				$this->message("shop_noexit");
			}

			$smardb = new newsmcart();
			$carinfo = array();

			if ($smardb->setdb($this->mysql)->SetShopId($info["shopid"])->OneShop()) {
				$carinfo = $smardb->getdata();
			}
			else {
				$this->message($smardb->getError());
			}

			if (count($carinfo["goodslist"]) == 0) {
				$this->message("shop_emptycart");
			}

			$info["allcost"] = $carinfo["sum"];
			$info["goodslist"] = $carinfo["goodslist"];
			$info["bagcost"] = 0;
			$info["allcount"] = 0;
		}

		$info["shopps"] = 0;
		$info["pstype"] = 0;
		$info["cattype"] = 1;
		$info["is_goshop"] = 1;
		$info["subtype"] = $subtype;
		$orderclass = new orderclass();
		$orderclass->orderyuding($info);
		$orderid = $orderclass->getorder();

		if ($info["userid"] == 0) {
			ICookie::set("orderid", $orderid, 86400);
		}

		if ($subtype == 2) {
			$smardb->DelShop($info["shopid"]);
		}

		$this->success($orderid);
		exit();
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
}


$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}

