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
	public function saveshopbq()
	{
		$id = IReq::get("ids");
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			echo "<script>parent.uploaderror('店铺获取失败');</script>";
			exit();
		}

		$is_recom = intval(IReq::get("is_recom"));
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (!empty($shopinfo)) {
			$udata["is_recom"] = $is_recom;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $udata, "id='" . $shopid . "'");
		}

		if ($shopinfo["shoptype"] == 0) {
			$fastfood = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");

			if (0 < count($fastfood)) {
				$data["is_com"] = intval(IReq::get("fis_com"));
				$data["is_hot"] = intval(IReq::get("fis_hot"));
				$data["is_new"] = intval(IReq::get("fis_new"));
				$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopid . "'");
			}
		}

		$attrinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = '" . $shopinfo["shoptype"] . "' and parent_id = 0 and is_admin = 1  order by orderid desc limit 0,1000");
		$tempinfo = array();

		foreach ($attrinfo as $key => $value ) {
			$tempinfo[] = $value["id"];
		}

		if (0 < count($tempinfo)) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopattr", " shopid='" . $shopid . "' and firstattr in(" . join(",", $tempinfo) . ") ");

			foreach ($attrinfo as $key => $value ) {
				$attrdata["shopid"] = $shopid;
				$attrdata["cattype"] = $shopinfo["shoptype"];
				$attrdata["firstattr"] = $value["id"];
				$inputdata = IFilter::act(IReq::get("mydata" . $value["id"]));

				if ($value["type"] == "input") {
					$attrdata["attrid"] = 0;
					$attrdata["value"] = $inputdata;
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
				}
				else if ($value["type"] == "img") {
					$temp = array();
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));
					$ids = join(",", $temp);

					if (empty($ids)) {
						continue;
					}

					$tempattr = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . $ids . ")   order by orderid desc limit 0,1000");

					foreach ($tempattr as $ky => $val ) {
						$attrdata["attrid"] = $val["id"];
						$attrdata["value"] = $val["name"];
						$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
					}
				}
				else if ($value["type"] == "checkbox") {
					$temp = array();
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));
					$ids = join(",", $temp);

					if (empty($ids)) {
						continue;
					}

					$tempattr = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . $ids . ")   order by orderid desc limit 0,1000");

					foreach ($tempattr as $ky => $val ) {
						$attrdata["attrid"] = $val["id"];
						$attrdata["value"] = $val["name"];
						$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
					}
				}
				else if ($value["type"] = "radio") {
					$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . intval($inputdata) . ")   order by orderid desc limit 0,1000");

					if (empty($tempattr)) {
						continue;
					}

					$attrdata["attrid"] = $tempattr["id"];
					$attrdata["value"] = $tempattr["name"];
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
				}
				else {
					continue;
				}
			}

			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopsearch", " shopid='" . $shopid . "'  and parent_id in(" . join(",", $tempinfo) . ") ");

			foreach ($attrinfo as $key => $value ) {
				if (($value["is_search"] == 1) && ($value["type"] != "input")) {
					$inputdata = IFilter::act(IReq::get("mydata" . $value["id"]));
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));

					foreach ($temp as $ky => $val ) {
						$searchdata["shopid"] = $shopid;
						$searchdata["parent_id"] = $value["id"];
						$searchdata["cattype"] = $shopinfo["shoptype"];
						$searchdata["second_id"] = intval($val);

						if (0 < $val) {
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopsearch", $searchdata);
						}
					}
				}
			}
		}

		echo "<script>parent.uploadsucess('');</script>";
		exit();
	}

	public function useredit()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (0 < $shopid) {
			$data["shopname"] = IFilter::act(IReq::get("shopname"));

			if (!empty($data["shopname"])) {
				$data["phone"] = IFilter::act(IReq::get("phone"));
				$data["address"] = IFilter::act(IReq::get("address"));
				$data["shortname"] = IFilter::act(IReq::get("shortname"));
				$data["goodattrdefault"] = IFilter::act(IReq::get("goodattrdefault"));
				$data["email"] = IFilter::act(IReq::get("email"));
				$data["is_open"] = intval(IReq::get("is_open"));
				$data["is_onlinepay"] = intval(IReq::get("is_onlinepay"));
				$data["is_daopay"] = intval(IReq::get("is_daopay"));
				$starttime = IFilter::act(IReq::get("starttime"));
				$data["otherlink"] = IFilter::act(IReq::get("otherlink"));
				$data["IMEI"] = IFilter::act(IReq::get("IMEI"));
				$data["maphone"] = IFilter::act(IReq::get("maphone"));
				$data["daymoney"] = IFilter::act(IReq::get("daymoney"));
				$data["nightmoney"] = IFilter::act(IReq::get("nightmoney"));
				$link = IUrl::creatUrl("shopcenter/base");

				if (!IValidate::len($data["shopname"], 2, 50)) {
					$this->message("shop_shopnamelenth");
				}

				if (!IValidate::phone($data["phone"])) {
					$this->message("shop_dphone");
				}

				if (!IValidate::len($data["address"], 2, 50)) {
					$this->message("shop_addresslenth");
				}

				if (!IValidate::len($data["shortname"], 2, 10)) {
					$this->message("shop_urllenth");
				}

				if (!empty($data["shortname"])) {
					if (!IValidate::email($data["email"])) {
						$this->message("erremail");
					}
				}

				if (in_array($data["shortname"], array("shopcenter", "site", "admin", "member", "membercenter", "shop", "comment", "ask", "single", "gift", "news", "adv"))) {
					$this->message("访问地址已存在");
				}

				if (empty($data["goodattrdefault"])) {
					$this->message("默认商品单位不能为空！");
				}

				if (!IValidate::len($data["goodattrdefault"], 0, 10)) {
					$this->message("默认商品单位长度大于0小于10");
				}

				$checkcode = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id !='" . $shopid . "' and shortname ='" . $data["shortname"] . "' ");

				if (!empty($checkcode)) {
					$this->message("shop_exiturl");
				}

				$data["starttime"] = "";

				if (empty($starttime)) {
					$this->message("emptytime");
				}

				$starttime = explode(",", $starttime);

				if (!is_array($starttime)) {
					$this->message("errtime");
				}

				$checkshu = count($starttime);

				if (($checkshu % 4) != 0) {
					$this->message("errtime");
				}

				$newtime = array();

//				foreach ($starttime as $key => $value ) {
//                   if (($key % 6) ==0) {
//                        $data["starttime"] .= $value . "/";
//                    }
//                    else if (($key % 6) == 1) {
//                        $data["starttime"] .= $value . "#";
//                    }
//                    else if (($key % 6) == 2) {
//						$data["starttime"] .= $value . ":";
//					}
//					else if (($key % 6) == 3) {
//						$data["starttime"] .= $value . "-";
//					}
//					else if (($key % 6) == 4) {
//						$data["starttime"] .= $value . ":";
//					}
//					else if (($key % 6) ==5) {
//						$data["starttime"] .= $value . "|";
//					}
//
//				}
				foreach ($starttime as $key => $value ) {
					if (($key % 4) == 0) {
						$data["starttime"] .= $value . ":";
					}
					else if (($key % 4) == 1) {
						$data["starttime"] .= $value . "-";
					}
					else if (($key % 4) == 2) {
						$data["starttime"] .= $value . ":";
					}
					else if (($key % 4) == 3) {
						$data["starttime"] .= $value . "|";
					}
				}

				if (empty($data["starttime"])) {
					$this->message("shop_erroptime");
				}

				if ((count($newtime) % 2) == 1) {
					$this->message("errtime");
				}

				$data["starttime"] = substr($data["starttime"], 0, strlen($data["starttime"]) - 1);
				$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
				$Searchk = new searchkey($this->mysql);
				$checkiex = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid ='" . $shopid . "'  ");

				if (0 < $checkiex) {
					$Searchk->setdata(1, $shopid, $data["shopname"]);
					$Searchk->save();
				}

				$this->success("success");
			}
		}

		$data["newtimedata"] = array();
		Mysite::$app->setdata($data);
	}

	public function usershopfast()
	{
		$this->checkshoplogin();
		$data["sitetitle"] = "店铺设置";
		$shopid = ICookie::get("adminshopid");
		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");

		if ($checkinfo["shoptype"] == 0) {
			$data["shopfast"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid = '" . $shopid . "' ");

			if (empty($data["shopfast"])) {
				$udata["shopid"] = $shopid;
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopfast", $udata);
				$data["shopfast"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid = '" . $shopid . "' ");
			}
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($data["shopfast"]["postdate"]) ? unserialize($data["shopfast"]["postdate"]) : array());
		$data["pstimelist"] = array();

		foreach ($timelist as $key => $value ) {
			$tempt = array();
			$tempt["s"] = date("H:i", $nowhout + $value["s"]);
			$tempt["e"] = date("H:i", $nowhout + $value["e"]);
			$tempt["i"] = $value["i"];
			$data["pstimelist"][] = $tempt;
		}

		$attrinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $checkinfo["shoptype"] . " and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$data["attrlist"] = array();

		foreach ($attrinfo as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select id,name,instro from " . Mysite::$app->config["tablepre"] . "shoptype where   parent_id = " . $value["id"] . " order by id desc ");
			$data["attrlist"][] = $value;
		}

		$shopsetatt = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $checkinfo["shoptype"] . "   and shopid = '" . $shopid . "'  limit 0,1000");
		$myattr = array();

		foreach ($shopsetatt as $key => $value ) {
			$myattr[$value["firstattr"] . "-" . $value["attrid"]] = $value["value"];
		}

		$data["myattr"] = $myattr;
		$data["pestypearr"] = array(1 => "店铺统一配送费", 2 => "店铺区域设置配送费", 3 => "不计算配送费", 4 => "根据定位距离计算", 5 => "根据菜品数计算配送费");
		$data["defaultset"] = array("pstype" => "0", "psvalue1" => "0", "psvalue2" => "0", "psvalue3" => "0");
		Mysite::$app->setdata($data);
	}

	public function usershopmarket()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");
		$data["shopfast"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid = '" . $shopid . "' ");

		if (empty($data["shopfast"])) {
			$udata["shopid"] = $shopid;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopmarket", $udata);
			$data["shopfast"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid = '" . $shopid . "' ");
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($data["shopfast"]["postdate"]) ? unserialize($data["shopfast"]["postdate"]) : array());
		$data["pstimelist"] = array();

		foreach ($timelist as $key => $value ) {
			$tempt = array();
			$tempt["s"] = date("H:i", $nowhout + $value["s"]);
			$tempt["e"] = date("H:i", $nowhout + $value["e"]);
			$tempt["i"] = $value["i"];
			$data["pstimelist"][] = $tempt;
		}

		$attrinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $checkinfo["shoptype"] . " and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$data["attrlist"] = array();

		foreach ($attrinfo as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select id,name,instro from " . Mysite::$app->config["tablepre"] . "shoptype where   parent_id = " . $value["id"] . " order by id desc ");
			$data["attrlist"][] = $value;
		}

		$shopsetatt = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $checkinfo["shoptype"] . "   and shopid = '" . $shopid . "'  limit 0,1000");
		$myattr = array();

		foreach ($shopsetatt as $key => $value ) {
			$myattr[$value["firstattr"] . "-" . $value["attrid"]] = $value["value"];
		}

		$data["myattr"] = $myattr;
		$data["pestypearr"] = array(1 => "店铺统一配送费", 2 => "店铺区域设置配送费", 3 => "不计算配送费", 4 => "百度地图测算配送费", 5 => "根据菜品数计算配送费");
		$data["defaultset"] = array("pstype" => "0", "psvalue1" => "0", "psvalue2" => "0", "psvalue3" => "0");
		Mysite::$app->setdata($data);
	}

	public function shopinfo()
	{
		$shopid = ICookie::get("adminshopid");

		if (0 < $shopid) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "' ");
		}
		else {
			$shopinfo = "";
		}

		return $shopinfo;
	}

	public function savefastfood()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();

		if ($shopinfo["shoptype"] == 0) {
			$data["is_orderbefore"] = intval(IFilter::act(IReq::get("is_orderbefore")));
			$data["befortime"] = intval(IFilter::act(IReq::get("befortime")));
			$data["limitcost"] = intval(IFilter::act(IReq::get("limitcost")));
			$data["limitstro"] = IFilter::act(IReq::get("limitstro"));
			$data["personcost"] = intval(IFilter::act(IReq::get("personcost")));
			$data["maketime"] = intval(IFilter::act(IReq::get("maketime")));
			$data["is_waimai"] = intval(IFilter::act(IReq::get("is_waimai")));
			$data["is_goshop"] = intval(IFilter::act(IReq::get("is_goshop")));
			$data["personcount"] = intval(IFilter::act(IReq::get("personcount")));
			$data["arrivetime"] = IFilter::act(IReq::get("arrivetime"));
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid = '" . $shopinfo["id"] . "' ");

			if (!empty($checkinfo["sendtype"])) {
				$data["pradius"] = intval(IFilter::act(IReq::get("pradius")));
				$data["pscost"] = intval(IFilter::act(IReq::get("pscost")));
				$tempdo = array();

				for ($i = 0; $i < $data["pradius"]; $i++) {
					$tempdo[$i] = intval(IReq::get("radiusvalue" . $i));
				}

				$data["pradiusvalue"] = serialize($tempdo);
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopinfo["id"] . "'");
		}
		else if ($shopinfo["shoptype"] == 1) {
			$data["is_orderbefore"] = intval(IFilter::act(IReq::get("is_orderbefore")));
			$data["befortime"] = intval(IFilter::act(IReq::get("befortime")));
			$data["limitcost"] = intval(IFilter::act(IReq::get("limitcost")));
			$data["limitstro"] = IFilter::act(IReq::get("limitstro"));
			$data["maketime"] = intval(IFilter::act(IReq::get("maketime")));
			$data["arrivetime"] = IFilter::act(IReq::get("arrivetime"));
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid = '" . $shopinfo["id"] . "' ");

			if (!empty($checkinfo["sendtype"])) {
				$data["pradius"] = intval(IFilter::act(IReq::get("pradius")));
				$data["pscost"] = intval(IFilter::act(IReq::get("pscost")));
				$tempdo = array();

				for ($i = 0; $i < $data["pradius"]; $i++) {
					$tempdo[$i] = intval(IReq::get("radiusvalue" . $i));
				}

				$data["pradiusvalue"] = serialize($tempdo);
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopinfo["id"] . "'");
		}

		$attrinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $shopinfo["shoptype"] . " and parent_id = 0 and is_admin = 0  order by orderid desc limit 0,1000");
		$tempinfo = array();

		foreach ($attrinfo as $key => $value ) {
			$tempinfo[] = $value["id"];
		}

		if (0 < count($tempinfo)) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopattr", " shopid='" . $shopinfo["id"] . "' and firstattr in(" . join(",", $tempinfo) . ") ");

			foreach ($attrinfo as $key => $value ) {
				$attrdata["shopid"] = $shopinfo["id"];
				$attrdata["cattype"] = $shopinfo["shoptype"];
				$attrdata["firstattr"] = $value["id"];
				$inputdata = IFilter::act(IReq::get("mydata" . $value["id"]));

				if ($value["type"] == "input") {
					$attrdata["attrid"] = 0;
					$attrdata["value"] = $inputdata;
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
				}
				else if ($value["type"] == "img") {
					$temp = array();
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));
					$ids = join(",", $temp);

					if (empty($ids)) {
						continue;
					}

					$tempattr = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . $ids . ")   order by orderid desc limit 0,1000");

					foreach ($tempattr as $ky => $val ) {
						$attrdata["attrid"] = $val["id"];
						$attrdata["value"] = $val["name"];
						$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
					}
				}
				else if ($value["type"] == "checkbox") {
					$temp = array();
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));
					$ids = join(",", $temp);

					if (empty($ids)) {
						continue;
					}

					$tempattr = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . $ids . ")   order by orderid desc limit 0,1000");

					foreach ($tempattr as $ky => $val ) {
						$attrdata["attrid"] = $val["id"];
						$attrdata["value"] = $val["name"];
						$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
					}
				}
				else if ($value["type"] = "radio") {
					$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where id in(" . intval($inputdata) . ")   order by orderid desc limit 0,1000");

					if (empty($tempattr)) {
						continue;
					}

					$attrdata["attrid"] = $tempattr["id"];
					$attrdata["value"] = $tempattr["name"];
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopattr", $attrdata);
				}
				else {
					continue;
				}
			}

			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopsearch", " shopid='" . $shopinfo["id"] . "'  and parent_id in(" . join(",", $tempinfo) . ") ");

			foreach ($attrinfo as $key => $value ) {
				if (($value["is_search"] == 1) && ($value["type"] != "input")) {
					$inputdata = IFilter::act(IReq::get("mydata" . $value["id"]));
					$temp = (is_array($inputdata) ? $inputdata : array($inputdata));

					foreach ($temp as $ky => $val ) {
						$searchdata["shopid"] = $shopinfo["id"];
						$searchdata["parent_id"] = $value["id"];
						$searchdata["cattype"] = $shopinfo["shoptype"];
						$searchdata["second_id"] = intval($val);

						if (0 < $val) {
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "shopsearch", $searchdata);
						}
					}
				}
			}
		}

		$link = "";

		if (empty($shopinfo["shoptype"])) {
			$link = IUrl::creatUrl("shopcenter/usershopfast");
			$this->message("success", $link);
		}
		else if ($shopinfo["shoptype"] == 1) {
			$link = IUrl::creatUrl("shopcenter/usershopmarket");
			$this->message("success", $link);
		}
	}

	public function usershopnotice()
	{
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get("uid"));

		if (!empty($uid)) {
			$data["notice_info"] = IReq::get("notice_info");
			$shopid = ICookie::get("adminshopid");
			$link = IUrl::creatUrl("shopcenter/usershopnotice");
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
			$this->message("success", $link);
		}
	}

	public function usershopcxnotice()
	{
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get("uid"));

		if (!empty($uid)) {
			$data["cx_info"] = IReq::get("cx_info");
			$shopid = ICookie::get("adminshopid");
			$link = IUrl::creatUrl("shopcenter/usershopcxnotice");
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
			$this->message("success", $link);
		}
	}

	public function usershopinstro()
	{
		$this->checkshoplogin();
		$uid = IFilter::act(IReq::get("uid"));

		if (!empty($uid)) {
			$data["intr_info"] = IReq::get("intr_info");
			$shopid = ICookie::get("adminshopid");
			$link = IUrl::creatUrl("shopcenter/usershopinstro");
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
			$this->message("success", $link);
		}
	}

	public function goodslist()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$shoptype = $shopinfo["shoptype"];

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		if (empty($shoptype)) {
			$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid = '" . $shopid . "'  order by orderid asc  ");
		}
		else if ($shoptype == 1) {
			$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $shopid . "'  order by orderid asc  ");
		}

		$alllist = array();

		if (is_array($listtype)) {
			foreach ($listtype as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where typeid = '" . $value["id"] . "' and shopid=" . $shopid . " order by good_order asc limit 0,1000  ");
				$alllist[] = $value;
			}
		}

		$data["list"] = $alllist;
		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from " . Mysite::$app->config["tablepre"] . "goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();

		if (is_array($goodssign)) {
			foreach ($goodssign as $key => $value ) {
				$checksign[] = $value["id"];
			}
		}

		$data["goodssign"] = $goodssign;
		$data["checksign"] = $checksign;
		$data["showshu"] = count($goodssign);
		$data["jsondata"] = json_encode($goodssign);
		Mysite::$app->setdata($data);
	}

	public function marketgoodslist()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$shoptype = $shopinfo["shoptype"];

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$topid = intval(IFilter::act(IReq::get("topid")));
		$toplist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $shopid . "' and parent_id = 0 order by orderid asc  ");
		$topcheck = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $shopid . "' and parent_id = 0 and id=" . $topid . " order by orderid asc  ");
		$newtopid = (!empty($topcheck) ? $topid : 0);

		if ($newtopid == 0) {
			if (isset($toplist[0]["id"]) && (0 < count($toplist))) {
				$newtopid = $toplist[0]["id"];
			}
		}

		$data["toplist"] = $toplist;
		$data["topid"] = $newtopid;
		$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $shopid . "' and parent_id =" . $newtopid . " order by orderid asc  ");
		$alllist = array();

		if (is_array($listtype)) {
			foreach ($listtype as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where typeid = '" . $value["id"] . "' and shopid=" . $shopid . " order by id asc limit 0,1000  ");
				$alllist[] = $value;
			}
		}

		$data["list"] = $alllist;
		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from " . Mysite::$app->config["tablepre"] . "goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();

		if (is_array($goodssign)) {
			foreach ($goodssign as $key => $value ) {
				$checksign[] = $value["id"];
			}
		}

		$data["goodssign"] = $goodssign;
		$data["checksign"] = $checksign;
		$data["showshu"] = count($goodssign);
		$data["jsondata"] = json_encode($goodssign);
		Mysite::$app->setdata($data);
	}

	public function savegoodstype()
	{
		$data["name"] = IFilter::act(IReq::get("name"));
		$data["orderid"] = intval(IReq::get("orderid"));
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$data["shopid"] = $shopid;

		if (empty($data["shopid"])) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();

		if (!IValidate::len($data["name"], 1, 10)) {
			$this->message("goods_namelenth");
		}

		if (empty($shopinfo["shoptype"])) {
			$checkwaimai = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid = '" . $shopid . "'  order by shopid asc  ");
			$data["cattype"] = 0;

			if (empty($checkwaimai)) {
				$this->message("shop_noexit");
			}

			$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid='" . $shopid . "'");
			$checkshuliang += 1;

			if (Mysite::$app->config["shoptypelimit"] < $checkshuliang) {
				$this->message("goods_countlimit");
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodstype", $data);
		}
		else if ($shopinfo["shoptype"] == 1) {
			$checkwaimai = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid = '" . $shopid . "'  order by shopid asc  ");

			if (empty($checkwaimai)) {
				$this->message("shop_noexit");
			}

			$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid='" . $shopid . "'");
			$checkshuliang += 1;

			if (Mysite::$app->config["shoptypelimit"] < $checkshuliang) {
				$this->message("goods_countlimit");
			}

			$data["orderid"] = ($data["orderid"] == 0 ? $checkshuliang : $data["orderid"]);
			$parent_id = intval(IFilter::act(IReq::get("parent_id")));

			if (0 < $parent_id) {
				$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid='" . $shopid . "' and id=" . $parent_id . "");

				if (empty($checkshuliang)) {
					$this->message("goods_parentnoown");
				}
			}

			$data["parent_id"] = $parent_id;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "marketcate", $data);
		}

		$this->success("success");
	}

	public function editgoodstype()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$what = trim(IFilter::act(IReq::get("what")));
		$addressid = intval(IReq::get("addressid"));

		if (empty($addressid)) {
			$this->message("goods_emptytype");
		}

		if ($what == "name") {
			$arr["name"] = IFilter::act(IReq::get("controlname"));

			if (!IValidate::len($arr["name"], 2, 10)) {
				$this->message("gods_typelenth");
			}

			if (empty($shopinfo["shoptype"])) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goodstype", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}
			else if ($shopinfo["shoptype"] == 1) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "marketcate", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}

			$this->success("success");
		}
		else if ($what == "orderid") {
			$arr["orderid"] = intval(IReq::get("controlname"));

			if (empty($shopinfo["shoptype"])) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goodstype", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}
			else if ($shopinfo["shoptype"] == 1) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "marketcate", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}

			$this->success("操作成功");
		}
		else if ($what == "allinfo") {
			$arr["name"] = IFilter::act(IReq::get("name"));
			$arr["orderid"] = intval(IFilter::act(IReq::get("orderid")));
			$arr["cattype"] = intval(IFilter::act(IReq::get("cattype")));
			if (empty($shopinfo["shoptype"])) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goodstype", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}
			else if ($shopinfo["shoptype"] == 1) {
				$arr["parent_id"] = intval(IFilter::act(IReq::get("parent_id")));
				$this->mysql->update(Mysite::$app->config["tablepre"] . "marketcate", $arr, "id='" . $addressid . "' and shopid='" . $shopid . "' ");
			}

			$this->success("success");
		}
		else {
			$this->message("nodefined_func");
		}
	}

	public function savesellcounts()
	{
		$goodid = intval(IFilter::act(IReq::get("goodid")));
		$savesellcounts = intval(IFilter::act(IReq::get("savesellcounts")));

		if (empty($goodid)) {
			$this->message("获取商品失败！");
		}

		$goodinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id='" . $goodid . "'");

		if (empty($goodinfo)) {
			$this->message("获取商品失败！");
		}

		$data["sellcount"] = $savesellcounts;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $goodid . "'");
		$this->success("success");
	}

	public function goodsone()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$data["adminuid"] = ICookie::get("adminuid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$shoptype = $shopinfo["shoptype"];
		$id = intval(IFilter::act(IReq::get("gid")));

		if (empty($id)) {
			$this->message("goods_empty");
		}

		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $shopinfo["id"] . "' and id=" . $id . "");

		if (empty($goodsinfo)) {
			$this->message("goods_empty");
		}

		if (empty($shoptype)) {
			$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid = '" . $shopinfo["id"] . "'  order by orderid asc  ");
		}
		else if ($shoptype == 1) {
			$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = '" . $shopinfo["id"] . "'  and parent_id  != 0 order by orderid asc  ");
		}

		$goodssign = $this->mysql->getarr("select id,imgurl,name,instro from " . Mysite::$app->config["tablepre"] . "goodssign where type = 'goods'  order by id asc  ");
		$checksign = array();

		if (is_array($goodssign)) {
			foreach ($goodssign as $key => $value ) {
				$checksign[] = $value["id"];
			}
		}

		$cxinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where    goodsid=" . $id . "");

		if (!empty($cxinfo)) {
			$nowdate = strtotime(date("Y-m-d", time()));
			$cxinfo["cxstime1"] = date("H:i", $nowdate + $cxinfo["cxstime1"]);
			$cxinfo["cxetime1"] = date("H:i", $nowdate + $cxinfo["cxetime1"]);
			$cxinfo["cxstime2"] = (empty($cxinfo["cxstime2"]) ? $cxinfo["cxstime2"] : date("H:i", $nowdate + $cxinfo["cxstime2"]));
			$cxinfo["cxetime2"] = (empty($cxinfo["cxetime2"]) ? $cxinfo["cxetime2"] : date("H:i", $nowdate + $cxinfo["cxetime2"]));
		}

		$data["cxinfo"] = $cxinfo;
		$product_attr = (empty($goodsinfo["product_attr"]) ? array() : unserialize($goodsinfo["product_attr"]));
		$productlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "product where goodsid = '" . $goodsinfo["id"] . "'  order by id asc  ");
		$data["gglist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where shoptype = '" . $shoptype . "'  order by orderid asc limit 0,1000  ");
		$data["productlist"] = $productlist;
		$chilids = array();

		foreach ($productlist as $key => $value ) {
			$tepmids = explode(",", $value["attrids"]);

			foreach ($tepmids as $k => $value ) {
				$chilids[] = $value;
			}
		}

		$chilids = array_unique($chilids);
		$product_attrkey = array_keys($product_attr);
		sort($product_attrkey);
		$data["ggfids"] = join(",", $product_attrkey);
		$data["fdidsss"] = $chilids;
		$data["goodssign"] = $goodssign;
		$data["checksign"] = $checksign;
		$data["showshu"] = count($goodssign);
		$data["goodsinfo"] = $goodsinfo;
		$data["listtype"] = $listtype;
		Mysite::$app->setdata($data);
	}

	public function savegoodsall()
	{
		$this->checkshoplogin();
		$gid = intval(IFilter::act(IReq::get("gid")));
		$shopid = ICookie::get("adminshopid");
		$link = IUrl::creatUrl("shopcenter/goodsone/gid/" . $gid);

		if (empty($shopid)) {
			$this->message("emptycookshop", $link);
		}

		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit", $link);
		}

		$shoptype = $shopinfo["shoptype"];

		if (empty($gid)) {
			$this->message("goods_empty", $link);
		}

		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $shopinfo["id"] . "' and id=" . $gid . "");

		if (empty($goodsinfo)) {
			$this->message("goods_empty", $link);
		}

		$data["is_waisong"] = intval(IFilter::act(IReq::get("is_waisong")));
		$data["is_dingtai"] = intval(IFilter::act(IReq::get("is_dingtai")));
		$tempweek = IFilter::act(IReq::get("weeks"));
		$data["weeks"] = (is_array($tempweek) ? join(",", $tempweek) : $tempweek);
		$data["goodattr"] = IFilter::act(IReq::get("goodattr"));
		$data["is_new"] = intval(IFilter::act(IReq::get("is_new")));
		$data["is_hot"] = intval(IFilter::act(IReq::get("is_hot")));
		$data["is_com"] = intval(IFilter::act(IReq::get("is_com")));
		$temp = IFilter::act(IReq::get("cxids"));
		$data["signid"] = (is_array($temp) ? join(",", $temp) : $temp);
		$data["typeid"] = intval(IFilter::act(IReq::get("typeid")));
		$data["instro"] = IReq::get("instro");
		$have_det = intval(IFilter::act(IReq::get("have_det")));
		$data["have_det"] = 0;
		$data["product_attr"] = "";
		$idtonamearray = array();

		if ($have_det == 1) {
			$fggids = trim(IFilter::act(IReq::get("fggids")));

			if (!empty($fggids)) {
				$gglist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where  FIND_IN_SET( `id` , '" . $fggids . "' ) and parent_id = 0  order by orderid asc limit 0,1000  ");
				$product_attr = array();

				if (!empty($gglist)) {
					foreach ($gglist as $key => $value ) {
						$checkid = IFilter::act(IReq::get("choicegg" . $value["id"]));

						if (!empty($checkid)) {
							$checkid = (is_array($checkid) ? join(",", $checkid) : intval($checkid));
							$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where  FIND_IN_SET( `id` , '" . $checkid . "' ) and parent_id = " . $value["id"] . "  order by orderid asc limit 0,1000  ");
							$product_attr[$value["id"]] = $value;

							foreach ($value["det"] as $k => $v ) {
								$idtonamearray[$v["id"]] = $v["name"];
							}
						}
					}
				}

				if (0 < count($product_attr)) {
					$data["have_det"] = 1;
					$data["product_attr"] = serialize($product_attr);
					$goodsdetids = IFilter::act(IReq::get("goodsdetids"));
					$goodsdetids = (is_array($goodsdetids) ? join(",", $goodsdetids) : intval($goodsdetids));
					$this->mysql->delete(Mysite::$app->config["tablepre"] . "product", " `id` not in(" . $goodsdetids . ")  and `goodsid`=" . $gid . " ");
					$productlist = array();
					$gg_scost = IFilter::act(IReq::get("gg_scost"));
					$gg_sstock = IFilter::act(IReq::get("gg_sstock"));
					$gg_sids = IFilter::act(IReq::get("gg_sids"));
					$goodsdetids = IFilter::act(IReq::get("goodsdetids"));

					if (is_array($gg_scost)) {
						$data["count"] = 0;

						foreach ($gg_scost as $key => $value ) {
							if (isset($gg_sids[$key]) && !empty($gg_sids[$key])) {
								$tempids = $gg_sids[$key];
								$attr_ids = explode(",", $tempids);
								$attr_arr = array();

								foreach ($attr_ids as $k => $v ) {
									if (isset($idtonamearray[$v])) {
										$attr_arr[] = $idtonamearray[$v];
									}
								}

								$prodata["shopid"] = $shopid;
								$prodata["goodsid"] = $gid;
								$prodata["goodsname"] = $goodsinfo["name"];
								$prodata["attrname"] = join(",", $attr_arr);
								$prodata["attrids"] = $gg_sids[$key];
								$prodata["stock"] = (isset($gg_sstock[$key]) ? $gg_sstock[$key] : 0);
								$prodata["bagcost"] = $goodsinfo["bagcost"];
								$prodata["cost"] = $value;
								$prodata["id"] = $goodsdetids[$key];
								$productlist[] = $prodata;
								$data["cost"] = $value;
								$data["count"] = $data["count"] + $prodata["stock"];
							}
						}
					}

					foreach ($productlist as $key => $value ) {
						if (0 < $value["id"]) {
							$tempp = $value;
							unset($tempp["id"]);
							$this->mysql->update(Mysite::$app->config["tablepre"] . "product", $tempp, "id='" . $value["id"] . "'  ");
						}
						else {
							unset($value["id"]);
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "product", $value);
						}
					}
				}
			}
		}

		$data["descgoods"] = IReq::get("descgoods");
		$data["is_cx"] = intval(IFilter::act(IReq::get("is_cx")));

		if ($data["is_cx"] == 1) {
			$cxzhe = intval(IFilter::act(IReq::get("cxzhe")));
			if (($cxzhe < 1) || (100 < $cxzhe)) {
				$this->message("折扣比例设置错误", $link);
			}

			$cxdata["cxzhe"] = $cxzhe;
			$cxstarttime = trim(IFilter::act(IReq::get("cxstarttime")));

			if (empty($cxstarttime)) {
				$this->message("促销开始日期错误", $link);
			}

			$cxdata["cxstarttime"] = strtotime($cxstarttime);
			$ecxendttime = trim(IFilter::act(IReq::get("ecxendttime")));

			if (empty($ecxendttime)) {
				$this->message("促销结束日期错误", $link);
			}

			$cxdata["ecxendttime"] = strtotime($ecxendttime) + 86399;

			if ($cxdata["ecxendttime"] < $cxdata["cxstarttime"]) {
				$this->message("开始时间大于结束日期", $link);
			}

			$nowdate = date("Y-m-d", time());
			$nowtime = strtotime($nowdate);
			$cxstime1 = trim(IFilter::act(IReq::get("cxstime1")));

			if (empty($cxstime1)) {
				$this->message("第一个时间开始时间不能为空", $link);
			}

			$temptime = strtotime($nowdate . " " . $cxstime1);
			$cxdata["cxstime1"] = $temptime - $nowtime;
			$cxetime1 = trim(IFilter::act(IReq::get("cxetime1")));

			if (empty($cxetime1)) {
				$this->message("第一个时间结束时间不能为空", $link);
			}

			$temptime = strtotime($nowdate . " " . $cxetime1);
			$cxdata["cxetime1"] = $temptime - $nowtime;

			if ($cxdata["cxetime1"] < $cxdata["cxstime1"]) {
				$this->message("第一个时间段开始时间大于结束时间", $link);
			}

			$cxstime2 = trim(IFilter::act(IReq::get("cxstime2")));

			if (empty($cxstime2)) {
				$cxdata["cxstime2"] = 0;
				$cxdata["cxetime2"] = 0;
			}
			else {
				$temptime = strtotime($nowdate . " " . $cxstime2);
				$cxdata["cxstime2"] = $temptime - $nowtime;
				$cxetime2 = trim(IFilter::act(IReq::get("cxetime2")));

				if (empty($cxetime2)) {
					$this->message("第二个时间结束时间不能为空", $link);
				}

				$temptime = strtotime($nowdate . " " . $cxetime2);
				$cxdata["cxetime2"] = $temptime - $nowtime;

				if ($cxdata["cxetime2"] < $cxdata["cxstime2"]) {
					$this->message("第一个时间段开始时间大于结束时间", $link);
				}
			}

			$cxdata["goodsid"] = $gid;

			if ($goodsinfo["is_cx"] == 1) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goodscx", $cxdata, "goodsid='" . $gid . "' ");
			}
			else {
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodscx", $cxdata);
			}
		}
		else {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodscx", "goodsid = '$gid'");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $gid . "' and  shopid = '" . $shopinfo["id"] . "'");
		$data["id"] = $gid;
		$goodsinfo["typeid"] = $data["typeid"];
		$goodsinfo["have_det"] = $data["have_det"];
		echo "<script>parent.refreshgoods(" . json_encode($goodsinfo) . ");</script>";
		exit();
	}

	public function addgoods()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$data["name"] = trim(IFilter::act(IReq::get("name")));
		$data["typeid"] = IFilter::act(IReq::get("typeid"));
		$data["count"] = intval(IFilter::act(IReq::get("count")));
		$data["cost"] = IFilter::act(IReq::get("cost"));
//		$data["bagcost"] = IFilter::act(IReq::get("bagcost"));
		$data["begindate"] = IFilter::act(IReq::get("begindate"));
		$data["enddate"] = IFilter::act(IReq::get("enddate"));

		$data["good_order"] = IFilter::act(IReq::get("good_order"));
		$data["img"] = "";
		$data["signid"] = "";
		$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $shopid . "'");
		$checkshuliang += 1;

		if (Mysite::$app->config["shopgoodslimit"] < $checkshuliang) {
			$this->message("goods_limit");
		}

		if (!IValidate::len($data["name"], 2, 50)) {
			$this->message("goods_titlelenth");
		}

		$chekcount = $data["cost"] * 100;

		if ($data["cost"] < 0) {
			$this->message("goods_cost");
		}

		$data["shopid"] = $shopid;
		$data["uid"] = $this->member["uid"];
		$data["point"] = 0;
		$data["sellcount"] = 0;
		$data["instro"] = "";
		$data["daycount"] = 100;
		$data["shoptype"] = $shopinfo["shoptype"];
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "goods", $data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id = '$id'");

		if (empty($info)) {
			$this->message("goods_empty");
		}

		$this->success($info);
	}

    public function updategoods()
    {
        $this->checkshoplogin();
        $shopid = ICookie::get("adminshopid");

        if (empty($shopid)) {
            $this->message("emptycookshop");
        }

        $controlname = trim(IFilter::act(IReq::get("controlname")));
        $goodsid = intval(IReq::get("goodsid"));
        $values = trim(IReq::get("values"));

        if (empty($goodsid)) {
            $this->message("goods_empty");
        }

        switch ($controlname) {
            case "name":
                if (!IValidate::len($values, 2, 50)) {
                    $this->message("goods_titlelenth");
                }
                $data["name"] = $values;
                $cdata["goodsname"] = $data["name"];
                $this->mysql->update(Mysite::$app->config["tablepre"] . "product", $cdata, "goodsid='" . $goodsid . "' ");
                break;
//开始日期
            case "begindate":
                $data["begindate"] = $values;
                $cdata["begindate"] = $data["begindate"];
            //    $this->mysql->update(Mysite::$app->config["tablepre"] . "product", $cdata, "goodsid='" . $goodsid . "' ");
                break;
//结束日期
            case "enddate":
                $data["enddate"] = $values;
                $cdata["enddate"] = $data["enddate"];
          //      var_dump($cdata);
           //     $this->mysql->update(Mysite::$app->config["tablepre"] . "product", $cdata, "goodsid='" . $goodsid . "' ");
                break;
//商品数量
            case "count":
                $data["count"] = intval($values);
                $data["daycount"] = intval($values);
                break;
//简单说明
            case "instro":
                if (!IValidate::len($values, 0, 200)) {
                    $this->message("goods_instrolenth");
                }

                $data["instro"] = $values;
                break;
//商品价格
            case "cost":
                $values = $values * 100;
                $kk = intval($values);

                if ($kk < 0) {
                    $this->message("goods_cost");
                }

                $data["cost"] = $values / 100;
                break;

            case "bagcost":
                $values = $values * 100;
                $kk = intval($values);

                if ($kk < 0) {
                    $this->message("goods_bagcost");
                }

                $data["bagcost"] = $values / 100;
                $cdata["bagcost"] = $data["bagcost"];
                $this->mysql->update(Mysite::$app->config["tablepre"] . "product", $cdata, "goodsid='" . $goodsid . "' ");
                break;

            case "good_order":
                $values = $values;
                $kk = intval($values);

                if ($kk < 0) {
                    $this->message("good_order");
                }

                $data["good_order"] = $values;
                break;

            case "sellcount":
                $values = $values * 100;
                $kk = intval($values);

                if ($kk < 0) {
                    $this->message("goods_count");
                }

                $data["sellcount"] = $values / 100;
                break;

            case "typeid":
                $values = intval($values);

                if (empty($values)) {
                    $this->message("goods_typeid");
                }

                $shopinfo = $this->shopinfo();
                $checkshuliang = 0;

                if (empty($shopinfo["shoptype"])) {
                    $checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodstype where id = '$values' and shopid='" . $shopid . "'");
                }
                else if ($shopinfo["shoptype"] == 1) {
                    $checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = '$values' and shopid='" . $shopid . "'");
                }

                if ($checkshuliang < 1) {
                    $this->message("goods_typeid");
                }

                $data["typeid"] = $values;
                break;

            case "signid":
                if (empty($values)) {
                    $this->message("goods_sign");
                }

                $data["signid"] = $values;
                break;

            case "is_new":
                $data["is_new"] = $values;
                break;

            case "is_com":
                $data["is_com"] = $values;
                break;

            case "is_hot":
                $data["is_hot"] = $values;
                break;

            default:
                $this->message("nodefined_func");
                break;
        }

        $this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $goodsid . "' and  shopid = '" . $shopid . "'");
        $this->success("success");
    }

	public function delgoods()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("goods_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "id = '$uid' and  shopid='" . $shopid . "'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "product", "goodsid = '$uid' and  shopid='" . $shopid . "'");
		$this->success("success");
	}

	public function addcxrule()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$id = intval(IReq::get("id"));
		$this->setstatus();
		$data["cxsignlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$data["cxinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "rule where id = '" . $id . "'  and shopid =  " . $shopid . "  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);
	}

	public function savecxrule()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$cxid = intval(IReq::get("cxid"));
		$data["name"] = trim(IFilter::act(IReq::get("name")));
		$data["limitcontent"] = intval(IReq::get("limitcontent"));
		$controltype = intval(IReq::get("controltype"));
		$data["cattype"] = $shopinfo["shoptype"];

		if (empty($data["name"])) {
			$this->message("cx_titleerr");
		}

		$data["type"] = 1;
		$limittype = intval(IReq::get("limittype"));
		$data["limittype"] = (in_array($limittype, array("1,", "2", "3")) ? $limittype : 1);

		if ($data["limittype"] == 1) {
			$data["limittime"] = "";
		}
		else if ($data["limittype"] == 2) {
			$limittime = IFilter::act(IReq::get("limittime1"));

			if (!is_array($limittime)) {
				$this->message("errweek");
			}

			$data["limittime"] = join(",", $limittime);
		}
		else {
			$limittime2 = IFilter::act(IReq::get("limittime2"));
			$limittime22 = IFilter::act(IReq::get("limittime22"));
			if (empty($limittime2) || empty($limittime22)) {
				$this->message("errtime");
			}

			$limittime2 = (is_array($limittime2) ? $limittime2 : array($limittime2));
			$limittime22 = (is_array($limittime22) ? $limittime22 : array($limittime22));

			if (count($limittime2) != count($limittime22)) {
				$this->message("errtime");
			}

			$contents = "";

			foreach ($limittime2 as $key => $value ) {
				if (!empty($value) && !empty($limittime22[$key])) {
					$contents .= $value . "-" . $limittime22[$key] . ",";
				}
			}

			if (empty($contents)) {
				$this->message("errtime");
			}

			$data["limittime"] = substr($contents, 0, strlen($contents) - 1);
		}

		if (!in_array($controltype, array("1", "2", "3", "4"))) {
			$this->message("cx_typeerr");
		}

		$data["controltype"] = $controltype;
		$data["controlcontent"] = intval(IReq::get("controlcontent"));

		if ($controltype != 4) {
			if (empty($data["controlcontent"])) {
				$this->message("cx_typeerr");
			}
		}

		$data["presenttitle"] = ($controltype == 1 ? trim(IFilter::act(IReq::get("presenttitle"))) : "");
		$starttime = IFilter::act(IReq::get("starttime"));
		$endtime = IFilter::act(IReq::get("endtime"));

		if (empty($starttime)) {
			$this->message("cx_starttime");
		}

		if (empty($endtime)) {
			$this->message("cx_endtime");
		}

		$data["signid"] = intval(IReq::get("signid"));

		if (empty($data["signid"])) {
			$this->message("cx_signerr");
		}

		$data["starttime"] = strtotime($starttime . " 00:00:00");
		$data["endtime"] = strtotime($endtime . " 23:59:59");
		$data["status"] = intval(IReq::get("status"));
		$data["shopid"] = $shopid;

		if (empty($cxid)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "rule", $data);
		}
		else {
			unset($data["shpid"]);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "rule", $data, "id='" . $cxid . "' and shopid = '" . $shopid . "'");
		}

		$this->success("success");
	}

	public function delcxrule()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$cxid = intval(IReq::get("cxid"));

		if (empty($cxid)) {
			$this->message("cx_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "rule", "id='" . $cxid . "' and shopid = '" . $shopid . "'");
		$this->success("success");
	}

	public function saveshanhuigift()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("未获取到店铺信息！");
		}

		$is_shgift = intval(IReq::get("is_shgift"));
		$sendgift = intval(IReq::get("sendgift"));
		if (empty($sendgift) || ($sendgift == 0)) {
			$this->message("不能填写为0或者为空");
		}

		$data["is_shgift"] = $is_shgift;
		$data["sendgift"] = $sendgift;

		if ($shopinfo["shoptype"] == 0) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopinfo["id"] . "' ");
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopinfo["id"] . "' ");
		}

		$this->success("success");
	}

	public function makeerwei()
	{
		$this->checkshoplogin();
		$shopid = intval(IReq::get("shopid"));
		$wx_s = new wx_s();
		$ifmake = $wx_s->makeforever($shopid);

		if ($ifmake == true) {
			$wxhui_ewmurl = $wx_s->get_shopurl();
		}
		else {
			$this->message("生成二维码数据失败");
		}

		if (!empty($wxhui_ewmurl)) {
			$data["wxhui_ewmurl"] = $wxhui_ewmurl;
			print_r($wxhui_ewmurl);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "' ");
			$this->success($wxhui_ewmurl);
		}
		else {
			$this->message("生成二维码数据失败");
		}
	}

	public function setshophui()
	{
		$this->checkshoplogin();
		$data["shophuilist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shophui");
		$shopinfo = $this->shopinfo();
		$data["shopinfo"] = $shopinfo;

		if ($shopinfo["shoptype"] == 1) {
			$shopdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid = '" . $shopinfo["id"] . "'  ");
		}
		else {
			$shopdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid = '" . $shopinfo["id"] . "'  ");
		}

		$data["shopdata"] = $shopdata;
		Mysite::$app->setdata($data);
	}

	public function saveshanhui()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("未获取到店铺信息！");
		}

		$is_shophui = intval(IReq::get("is_shophui"));
		$data["is_shophui"] = $is_shophui;

		if ($shopinfo["shoptype"] == 0) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopinfo["id"] . "' ");
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopinfo["id"] . "' ");
		}

		$this->success("success");
	}

	public function saveshophui()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$cxid = intval(IReq::get("cxid"));
		$data["name"] = trim(IFilter::act(IReq::get("name")));
		$data["mjlimitcost"] = intval(IReq::get("mjlimitcost"));
		$data["limitzhekoucost"] = intval(IReq::get("limitzhekoucost"));
		$controltype = intval(IReq::get("controltype"));
		$data["cattype"] = $shopinfo["shoptype"];

		if (empty($data["name"])) {
			$this->message("闪惠标题不能为空！");
		}

		$limittype = intval(IReq::get("limittype"));
		$data["limittype"] = (in_array($limittype, array("1,", "2")) ? $limittype : 1);

		if ($data["limittype"] == 1) {
			$data["limittimes"] = "";
			$data["limitweek"] = "";
		}
		else {
			$limittime = IFilter::act(IReq::get("limittime1"));

			if (!is_array($limittime)) {
				$this->message("errweek");
			}

			$data["limitweek"] = join(",", $limittime);
			$limittime2 = IFilter::act(IReq::get("limittime2"));
			$limittime22 = IFilter::act(IReq::get("limittime22"));
			if (empty($limittime2) || empty($limittime22)) {
				$this->message("errtime");
			}

			$limittime2 = (is_array($limittime2) ? $limittime2 : array($limittime2));
			$limittime22 = (is_array($limittime22) ? $limittime22 : array($limittime22));

			if (count($limittime2) != count($limittime22)) {
				$this->message("errtime");
			}

			$contents = "";

			foreach ($limittime2 as $key => $value ) {
				if (!empty($value) && !empty($limittime22[$key])) {
					$contents .= $value . "-" . $limittime22[$key] . ",";
				}
			}

			if (empty($contents)) {
				$this->message("errtime");
			}

			$data["limittimes"] = substr($contents, 0, strlen($contents) - 1);
		}

		if (!in_array($controltype, array("2", "3"))) {
			$this->message("闪惠类型错误！");
		}

		$data["controltype"] = $controltype;
		$data["controlcontent"] = intval(IReq::get("controlcontent"));

		if ($controltype == 2) {
			if (empty($data["mjlimitcost"])) {
				$this->message("未设置每满费用金额");
			}
		}

		if ($controltype == 3) {
			if (empty($data["limitzhekoucost"])) {
				$this->message("未设置折扣限制金额");
			}
		}

		if ($controltype != 4) {
			if (empty($data["controlcontent"])) {
				$this->message("cx_typeerr");
			}
		}

		$starttime = IFilter::act(IReq::get("starttime"));
		$endtime = IFilter::act(IReq::get("endtime"));

		if (empty($starttime)) {
			$this->message("未设置闪惠开始时间");
		}

		if (empty($endtime)) {
			$this->message("未设置闪惠结束时间");
		}

		$data["starttime"] = strtotime($starttime . " 00:00:00");
		$data["endtime"] = strtotime($endtime . " 23:59:59");
		$data["status"] = intval(IReq::get("status"));
		$data["shopid"] = $shopid;

		if ($data["status"] == 1) {
			$checkhuistatus = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shophui where status = 1 and shopid = '" . $shopid . "' ");

			if (count($checkhuistatus) == 1) {
				$checkhuione = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophui where id = '" . $cxid . "' and  status = 1 ");

				if ($checkhuistatus[0]["id"] == $checkhuione["id"]) {
					unset($data["shpid"]);
					$this->mysql->update(Mysite::$app->config["tablepre"] . "shophui", $data, "id='" . $cxid . "' and shopid = '" . $shopid . "'");
				}
				else {
					$this->message("已开启闪惠规则,只能开启一种,不能兼得！");
				}
			}
			else if (1 < count($checkhuistatus)) {
				$this->message("已开启闪惠规则,只能开启一种,不能兼得！");
			}
		}

		if (empty($cxid)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shophui", $data);
		}
		else {
			unset($data["shpid"]);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shophui", $data, "id='" . $cxid . "' and shopid = '" . $shopid . "'");
		}

		$this->success("success");
	}

	public function addshophui()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$id = intval(IReq::get("id"));
		$this->setstatus();
		$data["cxinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophui where id = '" . $id . "'  and shopid =  " . $shopid . "  order by id desc limit 0, 100");
		Mysite::$app->setdata($data);
	}

	public function delshophui()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$cxid = intval(IReq::get("cxid"));

		if (empty($cxid)) {
			$this->message("cx_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shophui", "id='" . $cxid . "' and shopid = '" . $shopid . "'");
		$this->success("success");
	}

	public function savepx()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$pxids = IFilter::act(IReq::get("pxids"));
		$pxindex = IFilter::act(IReq::get("pxindex"));
		if (empty($pxids) || empty($pxindex)) {
			$this->message("system_err");
		}

		$testinfo = explode(",", $pxids);
		$test2 = explode(",", $pxindex);

		if (count($testinfo) != count($test2)) {
			$this->message("system_err");
		}

		foreach ($testinfo as $key => $value ) {
			if (0 < intval($value)) {
				$data["orderid"] = intval($test2[$key]);

				if (empty($shopinfo["shoptype"])) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "goodstype", $data, "id='" . $value . "'");
				}
				else if ($shopinfo["shoptype"] == 1) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "marketcate", $data, "id='" . $value . "'");
				}
			}
		}

		$this->success("success");
	}

	public function delgoodstype()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$uid = intval(IReq::get("addressid"));

		if (empty($uid)) {
			$this->message("goods_emptytype");
		}

		if (empty($shopinfo["shoptype"])) {
			$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodstype where id = '$uid' and shopid='" . $shopid . "'");

			if ($checkshuliang < 1) {
				$this->message("goods_emptytype");
			}

			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "typeid = '$uid' and  shopid='" . $shopid . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodstype", "id = '$uid' and  shopid='" . $shopid . "'");
		}
		else if ($shopinfo["shoptype"] == 1) {
			$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = '$uid' and shopid='" . $shopid . "'");

			if ($checkshuliang < 1) {
				$this->message("goods_emptytype");
			}

			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = '$uid' and shopid='" . $shopid . "'");

			if (empty($checkinfo["parent_id"])) {
				$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "marketcate where parent_id = '$uid' and shopid='" . $shopid . "'");

				if (0 < $checkshuliang) {
					$this->message("goods_typeexitchild");
				}
			}

			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "typeid = '$uid' and  shopid='" . $shopid . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "marketcate", "id = '$uid' and  shopid='" . $shopid . "'");
		}

		$this->success("success");
	}

	public function setstatus()
	{
		$data["buyerstatus"] = array("待处理订单", "待发货", "订单已发货", "订单完成", "买家取消订单", "卖家取消订单");
		$paytypelist = array("货到支付", "在线支付");
		$data["shoptype"] = array("购卡", "养车", "其他");
		$data["ordertypearr"] = array("网站", "网站", "电话", "微信", "AndroidAPP", "手机网站", "iosApp", "后台客服下单", "商家后台下单", "html5手机站");
		$data["backarray"] = array("", "退款中..", "退款成功", "拒绝退款");
		$data["payway"] = array("open_acout" => "余额支付", "weixin" => "微信支付", "alipay" => "支付宝", "alimobile" => "手机支付宝");
		$data["paytypearr"] = $paytypelist;
		Mysite::$app->setdata($data);
	}

	public function savegoodinstro()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$goodsid = intval(IFilter::act(IReq::get("goodsid")));
		$instro = IFilter::act(IReq::get("content"));

		if (empty($goodsid)) {
			echo "<script>parent.setinerror('产品ID获取失败');</script>";
			exit();
		}

		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			echo "<script>parent.setinerror('COOK失效，请重新登陆');</script>";
			exit();
		}

		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			echo "<script>parent.setinerror('COOK失效，请重新登陆');</script>";
			exit();
		}

		$data["instro"] = $instro;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $goodsid . "' and  shopid = '" . $shopinfo["id"] . "'");
		echo "<script>parent.setinsucess('保存成功');</script>";
		exit();
	}

	public function delgoodsimg()
	{
		$id = intval(IReq::get("id"));
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id ='" . $id . "' and shopid ='" . $shopid . "' ");

		if (empty($goodsinfo)) {
			$this->message("goods_empty");
		}

		if (!empty($goodsinfo["img"])) {
			IFile::unlink(hopedir . $goodsinfo["img"]);
			$udata["img"] = "";
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $udata, "id='" . $id . "'");
		}

		$this->success("操作成功");
	}

	public function alltoshopgoods()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$shoptype = $shopinfo["shoptype"];

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		$kutypeid = intval(IFilter::act(IReq::get("kutypeid")));
		$fenleiid = intval(IFilter::act(IReq::get("fenleiid")));
		$data["fenleiid"] = $fenleiid;
		$data["kutypeid"] = $kutypeid;
		$data["typelist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate order by orderid");
		$where = "";
		$where = (0 < $kutypeid ? " where typeid =" . $kutypeid : "");
		$this->pageCls->setpage(intval(IReq::get("page")), 10);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary  " . $where . "  limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary  " . $where . "   ");
		$checkids = array();

		if (is_array($data["list"])) {
			foreach ($data["list"] as $key => $value ) {
				$checkids[] = $value["id"];
			}
		}

		$checkstr = join(",", $checkids);
		$data["checkids"] = array();

		if (!empty($checkstr)) {
			$templistids = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods  where shopid = " . $shopid . " and shoptype = " . $shoptype . "    and parentid in(" . $checkstr . ")");

			if (is_array($templistids)) {
				foreach ($templistids as $key => $value ) {
					$data["checkids"][] = $value["parentid"];
				}
			}
		}

		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar();
		Mysite::$app->setdata($data);
	}

	public function adgoodstoshop()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");
		$shopinfo = $this->shopinfo();

		if (empty($shopinfo)) {
			$this->message("shop_noexit");
		}

		$shoptype = $shopinfo["shoptype"];
		$parentid = intval(IFilter::act(IReq::get("goodsid")));
		$fenleiid = intval(IFilter::act(IReq::get("fenleiid")));
		$yangshiid = intval(IFilter::act(IReq::get("yangshiid")));

		if ($parentid < 1) {
			$this->message("产品ID获取失败");
		}

		if ($shopid < 1) {
			$this->message("店铺获取失败");
		}

		$tempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where  id=" . $parentid . "   ");

		if (empty($tempinfo)) {
			$this->message("商品不存在");
		}

		if ($yangshiid == 1) {
			$checkshu = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goods  where  parentid =" . $parentid . "  and shopid = " . $shopid . "   ");

			if (0 < $checkshu) {
				$this->message("此商品已添加");
			}

			$data["shopid"] = $shopid;
			$data["parentid"] = $parentid;
			$data["typeid"] = $fenleiid;
			$data["name"] = $tempinfo["name"];
			$data["count"] = 100;
			$data["cost"] = $tempinfo["cost"];
			$data["img"] = $tempinfo["img"];
			$data["instro"] = $tempinfo["instro"];
			$data["shoptype"] = $shoptype;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "goods", $data);
			$data["id"] = $this->mysql->insertid();
			$this->success($data);
		}
		else {
			$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where parentid =" . $parentid . "  and shopid = " . $shopid . "   ");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "  parentid =" . $parentid . "  and shopid = " . $shopid . "  ");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "product", "  goodsid =" . $goodsinfo["id"] . "  and shopid = " . $shopid . "  ");
			$this->success("操作成功");
		}
	}

	public function uploadmarketimggoods()
	{
		$gid = intval(IFilter::act(IReq::get("gid")));
		$data["img"] = trim(IFilter::act(IReq::get("imglujing")));
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $gid . "'");
		$this->success("success");
	}

	public function shoporderlist()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$starttime = trim(IFilter::act(IReq::get("starttime")));
		$orderSource = intval(IReq::get("orderSource"));
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "";
		$where = "  and addtime > " . strtotime($starttime . " 00:00:00") . " and addtime < " . strtotime($starttime . " 23:59:59");
		$data["orderSource"] = $orderSource;
		$data["starttime"] = $starttime;
		$this->setstatus();
		$orderSourcetoarray = array(" and status > 0  ", " and ordertype !=2 and status > 0 and status < 4 and ( paytype = 0 or  paystatus=1)", " and ordertype =2 and status > 0 and status < 4 and ( paytype = 0 or  paystatus=1)", " and is_make = 0 and status > 0 and status < 3 and ( paytype = 0 or  paystatus=1)", " and status = 1 and is_make = 1 and ( paytype =0 or  paystatus=1)", " and status > 1 and status < 4  and ( paytype = 0 or  paystatus=1)  ", " and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 1 ", " and status = 4 and ( paytype = 1 or  paystatus=1) and is_reback = 2 ", " and status > 0 and status < 4  and ( paytype = 1 or  paystatus=1) and is_reback = 3 ");

		if (isset($orderSourcetoarray[$orderSource])) {
			$where .= "" . $orderSourcetoarray[$orderSource];
		}

		$orderlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where shopid='" . $shopid . "'  " . $where . " order by id desc limit 0,1000");
		$shuliang = $this->mysql->select_one("select count(id) as shuliang,sum(allcost) as allcost from " . Mysite::$app->config["tablepre"] . "order where shopid='" . $shopid . "' " . $where . " limit 0,1000");
		$data["tongji"] = $shuliang;
		$data["list"] = array();

		if ($orderlist) {
			foreach ($orderlist as $key => $value ) {
				$value["detlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $value["id"] . " order by id desc ");

				if (0 < $value["is_reback"]) {
					$value["drawbacklog"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where   orderid = " . $value["id"] . " limit 1 ");
				}

				$value["maijiagoumaishu"] = 0;

				if (0 < $value["buyeruid"]) {
					$value["maijiagoumaishu"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $value["buyeruid"] . "' and  status = 3 order by id desc");
				}

				$data["list"][] = $value;
			}
		}

		$daymintime = strtotime(date("Y-m-d", time()));
		$tempshu = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order where shopid='" . $shopid . "' and  status > 0  and  status <  4 and posttime > " . $daymintime . " limit 0,1000");
		$data["hidecount"] = $tempshu["shuliang"];
		$data["playwave"] = ICookie::get("playwave");
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

	public function shopcontrol()
	{
		$this->checkshoplogin();
		$controlname = trim(IFilter::act(IReq::get("controlname")));
		$orderid = intval(IReq::get("orderid"));
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->mysql->select_one("select uid from " . Mysite::$app->config["tablepre"] . "shop where id = " . $shopid . "");

		switch ($controlname) {
		case "unorder":
			if ((0 < $orderinfo["is_reback"]) && ($orderinfo["is_reback"] < 3)) {
				$this->message("order_baklogcantdoover");
			}

			$reason = trim(IFilter::act(IReq::get("reason")));

			if (empty($reason)) {
				$this->message("order_emptyclosereason");
			}

			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sellerunorder($shopinfo["uid"], $reason)) {
				$ordCls = new orderclass();
				$ordCls->noticeclose($orderid, $reason);
				$this->success("success");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case "makeorder":
			$checkorderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . " and shopid=" . $shopid . " ");

			if (empty($checkorderinfo)) {
				$this->message("order_noexit");
			}

			if ($checkorderinfo["status"] != 1) {
				$this->message("order_cantmake");
			}

			if (!empty($checkorderinfo["is_make"])) {
				$this->message("order_cantmake");
			}

			if ((0 < $checkorderinfo["is_reback"]) && ($checkorderinfo["is_reback"] < 3)) {
				$this->message("order_baklogcantdoover");
			}

			$udata["is_make"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $udata, "id='" . $orderid . "'");
			$ordCls = new orderclass();
			$ordCls->writewuliustatus($orderid, 4, $checkorderinfo["paytype"]);
			$ordCls->noticemake($orderid);
			$this->success("success");

			break;

		case "unmakeorder":
			$checkorderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . " and shopid=" . $shopid . " ");

			if (empty($checkorderinfo)) {
				$this->message("order_noexit");
			}

			if ((0 < $checkorderinfo["is_reback"]) && ($checkorderinfo["is_reback"] < 3)) {
				$this->message("order_baklogcantdoover");
			}

			if ($checkorderinfo["status"] != 1) {
				$this->message("order_cantunmake");
			}

			if ($checkorderinfo["is_goshop"] != 1) {
				if (!empty($checkorderinfo["is_make"])) {
					$this->message("order_cantunmake");
				}
			}

			$udata["is_make"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $udata, "id='" . $orderid . "'");
			$ordCls = new orderclass();
			$ordCls->writewuliustatus($orderid, 5, $checkorderinfo["paytype"]);
			$ordCls->noticeunmake($orderid);
			$this->success("success");
			break;

		case "sendorder":
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sendorder($shopinfo["uid"])) {
				$ordCls = new orderclass();
				$ordCls->noticesend($orderid);
				$ordCls->writewuliustatus($orderid, 6, $checkorderinfo["paytype"]);
				$this->success("success");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case "shenhe":
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->shenhe($shopinfo["uid"])) {
				$this->success("success");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case "delorder":
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sellerdelorder($shopinfo["uid"])) {
				$this->success("success");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case "wancheng":
			$checkorderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . " and shopid=" . $shopid . " ");
			if ((0 < $checkorderinfo["is_reback"]) && ($checkorderinfo["is_reback"] < 3)) {
				$this->message("order_baklogcantdoover");
			}

			if ($checkorderinfo["is_goshop"] != 1) {
				if ($checkorderinfo["status"] != 2) {
					$this->message("order_cantover");
				}
			}

			$ordCls = new orderclass();
			$ordCls->writewuliustatus($checkorderinfo["id"], 9, $checkorderinfo["paytype"]);
			$orderdata["is_acceptorder"] = 1;
			$orderdata["status"] = 3;
			$orderdata["suretime"] = time();
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $orderdata, "id='" . $orderid . "'");

			if (!empty($orderinfo["buyeruid"])) {
				$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $checkorderinfo["buyeruid"] . "'   ");

				if (!empty($memberinfo)) {
					$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`total`=`total`+" . $checkorderinfo["allcost"], "uid ='" . $checkorderinfo["buyeruid"] . "' ");
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
							$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`parent_id`=0", "uid ='" . $checkorderinfo["buyeruid"] . "' ");
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

			$this->success("success");
			break;

		case "reback":
			$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . " and shopid=" . $shopid . " ");

			if (empty($orderinfo)) {
				$this->message("订单不存在");
			}

			$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid=" . $orderid . " order by  id desc  limit 0,2");

			if (empty($drawbacklog)) {
				$this->message("order_emptybaklog");
			}

			if ($drawbacklog["status"] != 0) {
				$this->message("order_baklogcantdoover");
			}

			if (2 < $orderinfo["status"]) {
				$this->message("order_cantbak");
			}

			$data["type"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "drawbacklog", $data, "id='" . $drawbacklog["id"] . "'");
			$ordCls = new orderclass();
			$ordCls->noticeback($id);
			$this->success("success");
			break;

		case "unreback":
			$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . " and shopid=" . $shopid . " ");

			if (empty($orderinfo)) {
				$this->message("订单不存在");
			}

			$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid=" . $orderid . " order by  id desc  limit 0,2");

			if (empty($drawbacklog)) {
				$this->message("order_emptybaklog");
			}

			if ($drawbacklog["status"] != 0) {
				$this->message("order_baklogcantdoover");
			}

			if (2 < $orderinfo["status"]) {
				$this->message("order_cantbak");
			}

			$data["type"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "drawbacklog", $data, "id='" . $drawbacklog["id"] . "'");
			$ordCls = new orderclass();
			$ordCls->noticeunback($id);
			$this->success("success");
			break;

		default:
			$this->message("nodefined_func");
			break;
		}
	}

	public function shoptotal()
	{
		$this->checkshoplogin();
		$this->setstatus();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$year = IFilter::act(IReq::get("year"));
		$year = (empty($year) ? date("Y", time()) : $year);
		$month = IFilter::act(IReq::get("month"));
		$timelist = array();

		if (!empty($year)) {
			if (empty($month)) {
				$checknowtime = time();

				for ($i = 1; $i < 13; $i++) {
					$starttime = strtotime($year . "-" . $i . "-01");
					$endtime = strtotime("$year-$i-01 +1 month -1 day") + 86400;

					if ($starttime < $checknowtime) {
						$tempdata["name"] = $year . "-" . $i;
						$tempdata["starttime"] = $starttime;
						$tempdata["endtime"] = $endtime;
						$timelist[] = $tempdata;
					}
				}
			}
			else {
				$stime = strtotime($year . "-" . $month . "-01");
				$etime = strtotime("$year-$month-01 +1 month -1 day") + 86400;
				$checknowtime = time();
				while (($stime < $etime) && ($stime < $checknowtime)) {
					$tempdata["name"] = date("Y-m-d", $stime);
					$tempdata["starttime"] = $stime;
					$stime = $stime + 86400;
					$tempdata["endtime"] = $stime;
					$timelist[] = $tempdata;
				}
			}
		}
		else {
			$nowyear = date("Y", time());
			$nowyear = $nowyear + 1;

			for ($i = 10; 0 < $i; $i--) {
				$tempdata["name"] = $nowyear - $i;
				$tempdata["starttime"] = strtotime(($nowyear - $i) . "-01-01");
				$tempdata["endtime"] = strtotime(($nowyear - $i) . "-12-31") + 86400;
				$timelist[] = $tempdata;
			}
		}

		$data["year"] = $year;
		$data["month"] = (empty($month) ? "0" : $month);
		$data["startyear"] = date("Y", time());
		$list = array();
		$data["allsum"] = 0;
		$data["allnum"] = 0;

		if (is_array($timelist)) {
			foreach ($timelist as $key => $value ) {
				$where2 = "and posttime > " . $value["starttime"] . " and posttime < " . $value["endtime"];
				$shoptj = $this->mysql->select_one("select  count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost, sum(shopcost) as shopcost,sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $shopid . "' and paytype =0 and shopcost > 0 and status = 3 " . $where2 . " order by id asc  limit 0,1000");
				$line = $this->mysql->select_one("select count(id) as shuliang,sum(cxcost) as cxcost,sum(yhjcost) as yhcost,sum(shopcost) as shopcost, sum(scoredown) as score, sum(shopps)as pscost, sum(bagcost) as bagcost from " . Mysite::$app->config["tablepre"] . "order  where shopid = '" . $shopid . "' and paytype =1  and paystatus =1 and shopcost > 0 and status = 3 " . $where2 . "   order by id asc  limit 0,1000");
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
				$data["allsum"] += $value["allcost"];
				$data["allnum"] += $value["orderNum"];
				$value["goodscost"] = $line["shopcost"] + $shoptj["shopcost"];
				$yjinb = (empty($value["yjin"]) ? Mysite::$app->config["yjin"] : $value["yjin"]);
				$yjinb = intval($yjinb);
				$value["yb"] = $yjinb * 0.01;
				$value["yje"] = $value["yb"] * $value["allcost"];
				$list[] = $value;
			}
		}

		$data["list"] = $list;
		Mysite::$app->setdata($data);
	}

	public function ajaxcheckshoporder()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$daymintime = strtotime(date("Y-m-d", time()));
		$tempshu = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order where shopid='" . $shopid . "' and  status > 0  and  status <  4 and posttime > " . $daymintime . " limit 0,1000");
		$hidecount = $tempshu["shuliang"];
		$this->success($hidecount);
	}

	public function shopask()
	{
		$this->checkshoplogin();
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

	public function showcommlist()
	{
		$this->checkshoplogin();
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("order_noexitping");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "comment where id='" . $id . "'  ");

		if (empty($checkinfo)) {
			$this->message("order_noexitping");
		}

		$data["is_show"] = ($checkinfo["is_show"] == 1 ? 0 : 1);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "comment", $data, "id='" . $id . "'");
		$this->success("success");
	}

	public function delcommlist()
	{
		$this->checkshoplogin();
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("order_noexitping");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "comment", " id in($ids) ");
		$this->success("success");
	}

	public function delask()
	{
		$id = IFilter::act(IReq::get("id"));

		if (empty($id)) {
			$this->message("ask_empty");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$where = " id in($ids)";
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (!empty($shopid)) {
			$where = $where . " and shopid = " . $shopid;
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "ask", $where);
		$this->success("success");
	}

	public function backask()
	{
		$id = intval(IReq::get("askbackid"));

		if (empty($id)) {
			$this->message("ask_empty");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "ask where id='" . $id . "'  ");

		if (empty($checkinfo)) {
			$this->message("ask_empty");
		}

		if (!empty($checkinfo["replycontent"])) {
			$this->message("ask_isreplay");
		}

		$where = " id='" . $id . "' ";
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("ask_notownreplay");
		}

		if ($checkinfo["shopid"] != $shopid) {
			$this->message("ask_notownreplay");
		}

		$data["replycontent"] = IFilter::act(IReq::get("askback"));

		if (empty($data["replycontent"])) {
			$this->message("ask_emptyreplay");
		}

		$data["replytime"] = time();
		$this->mysql->update(Mysite::$app->config["tablepre"] . "ask", $data, $where);
		$this->success("success");
	}

	public function selectmarketimg()
	{
		$data["gid"] = intval(IReq::get("gid"));
		$this->pageCls->setpage(intval(IReq::get("page")), 18);
		$total = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "imglist      ");
		$data["imglist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "imglist      order by addtime desc limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . " ");
		$link = IUrl::creatUrl("shopcenter/selectmarketimg/gid/" . $data["gid"]);
		$data["pagecontent"] = $this->pageCls->multi($total, 18, intval(IReq::get("page")), $link);
		$data["page"] = intval(IReq::get("page"));
		Mysite::$app->setdata($data);
	}

	public function orderprint()
	{
		$orderid = intval(IReq::get("orderid"));
		$data["printtype"] = trim(IReq::get("printtype"));
		$this->setstatus();
		$data["orderinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order  where id ='" . $orderid . "' ");
		$data["orderdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $orderid . " order by id desc ");
		Mysite::$app->setdata($data);
	}

	public function savepostdate()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$starthour = intval(IFilter::act(IReq::get("starthour")));
		$startminit = intval(IFilter::act(IReq::get("startminit")));
		$endthour = intval(IFilter::act(IReq::get("endthour")));
		$endminit = intval(IFilter::act(IReq::get("endminit")));
		$instr = trim(IFilter::act(IReq::get("instr")));

		if ($shopinfo["shoptype"] == 0) {
			$tempshopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid = '" . $shopinfo["id"] . "' ");
		}
		else {
			$tempshopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid = '" . $shopinfo["id"] . "' ");
		}

		if (empty($tempshopinfo)) {
			$this->message("店铺不存在");
		}

		$bigetime = ($starthour * 60 * 60) + ($startminit * 60);
		$endtime = ($endthour * 60 * 60) + ($endminit * 60);

		if ($bigetime < 1) {
			$this->message("配送时间段起始时间必须从凌晨1分开始");
		}

		if ($endtime < $bigetime) {
			$this->message("开始时间段必须大于结束时间");
		}

		if (86400 <= $endtime) {
			$this->message("配送时间段结束时间最大值23:59");
		}

		$nowlist = (!empty($tempshopinfo["postdate"]) ? unserialize($tempshopinfo["postdate"]) : array());
		$checkshu = count($nowlist);

		if (0 < $checkshu) {
			$checknowendo = $nowlist[$checkshu - 1]["e"];

			if ($bigetime < $checknowendo) {
				$this->message("已设置配送时间段已包含提交的开始时间");
			}
		}

		$tempdata["s"] = $bigetime;
		$tempdata["e"] = $endtime;
		$tempdata["i"] = $instr;
		$nowlist[] = $tempdata;
		$savedata["postdate"] = serialize($nowlist);

		if ($shopinfo["shoptype"] == 0) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $savedata, "shopid='" . $shopinfo["id"] . "'");
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $savedata, "shopid='" . $shopinfo["id"] . "'");
		}

		$this->success("success");
	}

	public function delpostdate()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$shopinfo = $this->shopinfo();
		$nowdelid = intval(IFilter::act(IReq::get("id")));

		if ($shopinfo["shoptype"] == 0) {
			$tempshopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast  where shopid = '" . $shopinfo["id"] . "' ");
		}
		else {
			$tempshopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket  where shopid = '" . $shopinfo["id"] . "' ");
		}

		if (empty($tempshopinfo)) {
			$this->message("店铺不存在");
		}

		if (empty($tempshopinfo["postdate"])) {
			$this->message("未设置配送时间段");
		}

		$nowlist = unserialize($tempshopinfo["postdate"]);
		$newdata = array();

		foreach ($nowlist as $key => $value ) {
			if ($key != $nowdelid) {
				$newdata[] = $value;
			}
		}

		$savedata["postdate"] = serialize($newdata);

		if ($shopinfo["shoptype"] == 0) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $savedata, "shopid='" . $shopinfo["id"] . "'");
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $savedata, "shopid='" . $shopinfo["id"] . "'");
		}

		$this->success("success");
	}

	public function draworderset()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$starttime = trim(IFilter::act(IReq::get("starttime")));
		$orderSource = intval(IReq::get("orderSource"));
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "";
		$where = "  and b.addtime > " . strtotime($starttime . " 00:00:00") . " and b.addtime < " . strtotime($starttime . " 23:59:59");
		$data["orderSource"] = $orderSource;
		$data["starttime"] = $starttime;
		$this->setstatus();
		$orderSourcetoarray = array(" and   ( b.paytype = 1 or  b.paystatus=1) and b.is_reback > 0 ", " and   and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 1 ", " and   and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 2 ", " and   and ( b.paytype = 1 or  b.paystatus=1) and b.is_reback = 3 ");

		if (isset($orderSourcetoarray[$orderSource])) {
			$where .= "" . $orderSourcetoarray[$orderSource];
		}

		$draworderlist = $this->mysql->getarr("select a.*,b.id,b.is_reback,b.paystatus,b.paytype,b.status,b.addtime from " . Mysite::$app->config["tablepre"] . "drawbacklog as a left join " . Mysite::$app->config["tablepre"] . "order as b on a.orderid = b.id  where a.shopid='" . $shopid . "'  " . $where . " order by a.addtime desc limit 0,1000");
		$shuliang = $this->mysql->select_one("select a.*,b.id,b.is_reback,b.paystatus,b.paytype,b.status,b.addtime from " . Mysite::$app->config["tablepre"] . "drawbacklog as a left join " . Mysite::$app->config["tablepre"] . "order as b on a.orderid = b.id  where a.shopid='" . $shopid . "'  " . $where . " order by a.addtime desc limit 0,1000");
		$data["tongji"] = $shuliang;
		$data["list"] = array();

		if ($draworderlist) {
			foreach ($draworderlist as $key => $val ) {
				$val["orderone"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $val["orderid"] . "'  order by id desc limit 1");
				$val["detlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where   order_id = " . $val["orderid"] . " order by id desc ");
				$val["maijiagoumaishu"] = 0;

				if (0 < $val["buyeruid"]) {
					$val["maijiagoumaishu"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $val["buyeruid"] . "' and  status = 3 order by id desc");
				}

				$data["list"][] = $val;
			}
		}

		$daymintime = strtotime(date("Y-m-d", time()));
		$tempshu = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order where shopid='" . $shopid . "' and  status > 0  and  status <  4 and posttime > " . $daymintime . " limit 0,1000");
		$data["hidecount"] = $tempshu["shuliang"];
		$data["playwave"] = ICookie::get("playwave");
		Mysite::$app->setdata($data);
	}

	public function shophuiorder()
	{
		$this->checkshoplogin();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$starttime = trim(IFilter::act(IReq::get("starttime")));
		$orderSource = intval(IReq::get("orderSource"));
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "";
		$where = "  and addtime > " . strtotime($starttime . " 00:00:00") . " and addtime < " . strtotime($starttime . " 23:59:59");
		$data["orderSource"] = $orderSource;
		$data["starttime"] = $starttime;
		$orderSourcetoarray = array(" and status >= 0  and  paytype = 1 and  paystatus=0 ", " and  status = 0 and paytype = 1 and  paystatus=0", " and  status=1 and paytype = 1 and  paystatus=1 ");

		if (isset($orderSourcetoarray[$orderSource])) {
			$where .= "" . $orderSourcetoarray[$orderSource];
		}

		$orderlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shophuiorder where shopid='" . $shopid . "'  " . $where . " order by id desc limit 0,1000");
		$shuliang = $this->mysql->select_one("select count(id) as shuliang,sum(sjcost) as sjcost from " . Mysite::$app->config["tablepre"] . "shophuiorder where shopid='" . $shopid . "' " . $where . " limit 0,1000");
		$data["tongji"] = $shuliang;
		$data["list"] = array();

		if ($orderlist) {
			foreach ($orderlist as $key => $value ) {
				$value["maijiagoumaishu"] = 0;
				$value["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  id = " . $value["shopid"] . " order by id desc");
				$value["maijiagoumaishu"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shophuiorder where  status = 1 order by id desc");
				$data["list"][] = $value;
			}
		}

		$daymintime = strtotime(date("Y-m-d", time()));
		$tempshu = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "shophuiorder where shopid='" . $shopid . "' and  status > 0  and  status <  2 and completetime > " . $daymintime . " limit 0,1000");
		$data["hidecount"] = $tempshu["shuliang"];
		Mysite::$app->setdata($data);
	}

	public function shophuitotal()
	{
		$this->checkshoplogin();
		$this->setstatus();
		$shopid = ICookie::get("adminshopid");

		if (empty($shopid)) {
			$this->message("emptycookshop");
		}

		$year = IFilter::act(IReq::get("year"));
		$year = (empty($year) ? date("Y", time()) : $year);
		$month = IFilter::act(IReq::get("month"));
		$timelist = array();

		if (!empty($year)) {
			if (empty($month)) {
				$checknowtime = time();

				for ($i = 1; $i < 13; $i++) {
					$starttime = strtotime($year . "-" . $i . "-01");
					$endtime = strtotime("$year-$i-01 +1 month -1 day") + 86400;

					if ($starttime < $checknowtime) {
						$tempdata["name"] = $year . "-" . $i;
						$tempdata["starttime"] = $starttime;
						$tempdata["endtime"] = $endtime;
						$timelist[] = $tempdata;
					}
				}
			}
			else {
				$stime = strtotime($year . "-" . $month . "-01");
				$etime = strtotime("$year-$month-01 +1 month -1 day") + 86400;
				$checknowtime = time();
				while (($stime < $etime) && ($stime < $checknowtime)) {
					$tempdata["name"] = date("Y-m-d", $stime);
					$tempdata["starttime"] = $stime;
					$stime = $stime + 86400;
					$tempdata["endtime"] = $stime;
					$timelist[] = $tempdata;
				}
			}
		}
		else {
			$nowyear = date("Y", time());
			$nowyear = $nowyear + 1;

			for ($i = 10; 0 < $i; $i--) {
				$tempdata["name"] = $nowyear - $i;
				$tempdata["starttime"] = strtotime(($nowyear - $i) . "-01-01");
				$tempdata["endtime"] = strtotime(($nowyear - $i) . "-12-31") + 86400;
				$timelist[] = $tempdata;
			}
		}

		$data["year"] = $year;
		$data["month"] = (empty($month) ? "0" : $month);
		$data["startyear"] = date("Y", time());
		$list = array();
		$data["allsum"] = 0;
		$data["allnum"] = 0;

		if (is_array($timelist)) {
			foreach ($timelist as $key => $value ) {
				$where2 = "and completetime > " . $value["starttime"] . " and completetime < " . $value["endtime"];
				$shoptj = $this->mysql->select_one("select  count(id) as shuliang,sum(xfcost) as xfcost,sum(yhcost) as yhcost, sum(sjcost) as sjcost from " . Mysite::$app->config["tablepre"] . "shophuiorder  where shopid = '" . $shopid . "' and paytype =1 and paystatus =1 and status = 1 " . $where2 . " order by id asc  limit 0,1000");
				$value["orderNum"] = $shoptj["shuliang"];
				$value["xfcost"] = $shoptj["xfcost"];
				$value["yhcost"] = $shoptj["yhcost"];
				$value["sjcost"] = $shoptj["sjcost"];
				$data["allsum"] += $value["sjcost"];
				$data["allnum"] += $value["orderNum"];
				$list[] = $value;
			}
		}

		$data["list"] = $list;
		Mysite::$app->setdata($data);
	}

	public function savegoodsmoduletype()
	{
		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$moduletype = intval(IFilter::act(IReq::get("moduletype")));
		$shopinfo = $this->mysql->select_one("  select * from " . Mysite::$app->config["tablepre"] . "shop where id = " . $shopid . " ");

		if (empty($shopinfo)) {
			$this->message("获取店铺信息失败");
		}

		$data["goodlistmodule"] = $moduletype;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function getgoodssgg()
	{
	}

	public function backcomment()
	{
		$id = intval(IReq::get("askbackid"));

		if (empty($id)) {
			$this->message("获取失败");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "comment where id='" . $id . "'  ");

		if (empty($checkinfo)) {
			$this->message("评论不存在");
		}

		if (!empty($checkinfo["replycontent"])) {
			$this->message("已回复过");
		}

		$where = " id='" . $id . "' ";
		$data["replycontent"] = IFilter::act(IReq::get("askback"));

		if (empty($data["replycontent"])) {
			$this->message("请填写回复内容");
		}

		$data["replytime"] = time();
		$this->mysql->update(Mysite::$app->config["tablepre"] . "comment", $data, $where);
		$this->success("success");
	}
}


