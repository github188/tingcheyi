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
	public function saveshop()
	{
		$subtype = intval(IReq::get("subtype"));
		$id = intval(IReq::get("uid"));

		if (!in_array($subtype, array(1, 2))) {
			$this->message("system_err");
		}

		if ($subtype == 1) {
			$username = IReq::get("username");

			if (empty($username)) {
				$this->message("member_emptyname");
			}

			$testinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where username='" . $username . "'  ");

			if (empty($testinfo)) {
				$this->message("member_noexit");
			}

			if ($testinfo["admin_id"] != $this->admin["uid"]) {
				$this->message("shop_noownadmin");
			}

			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "usrlimit where  `group`='" . $testinfo["group"] . "' and  name='editshop' ");

			if (empty($shopinfo)) {
				$this->message("member_noownshop");
			}

			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  uid='" . $testinfo["uid"] . "' ");

			if (!empty($shopinfo)) {
				$this->message("member_isbangshop");
			}

			$data["shopname"] = IReq::get("shopname");

			if (empty($data["shopname"])) {
				$this->message("shop_emptyname");
			}

			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  shopname='" . $data["shopname"] . "'  ");

			if (!empty($shopinfo)) {
				$this->message("shop_repeatname");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "member", array("admin_id" => $this->admin["uid"]), "uid='" . $testinfo["uid"] . "'");
			$data["uid"] = $testinfo["uid"];
			$data["admin_id"] = $this->admin["uid"];
			$data["shoptype"] = intval(IReq::get("shoptype"));
			$nowday = 24 * 60 * 60 * 365;
			$data["endtime"] = time() + $nowday;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shop", $data);
			$this->success("success");
		}
		else if ($subtype == 2) {
			$data["username"] = IReq::get("username");
			$data["phone"] = IReq::get("maphone");
			$data["email"] = IReq::get("email");
			$data["password"] = IReq::get("password");
			$sdata["shopname"] = IReq::get("shopname");

			if (empty($sdata["shopname"])) {
				$this->message("shop_emptyname");
			}

			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  shopname='" . $sdata["shopname"] . "'  ");

			if (!empty($shopinfo)) {
				$this->message("shop_repeatname");
			}

			$password2 = IReq::get("password2");

			if ($password2 != $data["password"]) {
				$this->message("member_twopwdnoequale");
			}

			$uid = 0;

			if ($this->memberCls->regester($data["email"], $data["username"], $data["password"], $data["phone"], 3)) {
				$uid = $this->memberCls->getuid();
				$this->mysql->update(Mysite::$app->config["tablepre"] . "member", array("admin_id" => $this->admin["uid"]), "uid='" . $uid . "'");
			}
			else {
				$this->message($this->memberCls->ero());
			}

			$sdata["uid"] = $uid;
			$sdata["maphone"] = $data["phone"];
			$sdata["addtime"] = time();
			$sdata["email"] = $data["email"];
			$sdata["shoptype"] = intval(IReq::get("shoptype"));
			$nowday = 24 * 60 * 60 * 365;
			$sdata["endtime"] = time() + $nowday;
			$sdata["admin_id"] = $this->admin["uid"];
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shop", $sdata);
			$this->success("success");
		}
		else {
			$this->message("nodefined_func");
		}
	}

	public function shopbiaoqian()
	{
		$this->setstatus();
		$shopid = intval(IReq::get("id"));

		if (empty($shopid)) {
			echo "店铺不存在";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "店铺不存在";
			exit();
		}

		if ($shopinfo["admin_id"] != $this->admin["uid"]) {
			echo "该店铺不属于您管理";
			exit();
		}

		$fastfood = array();

		if ($shopinfo["shoptype"] == 0) {
			$fastfood = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");
		}

		$attrinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype='" . $shopinfo["shoptype"] . "' and  parent_id = 0 and is_admin = 1  order by orderid desc limit 0,1000");
		$data["attrlist"] = array();

		foreach ($attrinfo as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select id,name,instro from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype='" . $shopinfo["shoptype"] . "' and   parent_id = " . $value["id"] . " order by id desc ");
			$data["attrlist"][] = $value;
		}

		$shopsetatt = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where    cattype='" . $shopinfo["shoptype"] . "' and  shopid = '" . $shopid . "'  limit 0,1000");
		$myattr = array();

		foreach ($shopsetatt as $key => $value ) {
			$myattr[$value["firstattr"] . "-" . $value["attrid"]] = $value["value"];
		}

		$data["myattr"] = $myattr;
		$data["fastfood"] = $fastfood;
		$data["shopid"] = $shopid;
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

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

		if ($shopinfo["admin_id"] != $this->admin["uid"]) {
			echo "<script>parent.uploaderror('该店铺不属于您管理');</script>";
			exit();
		}

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

	public function passhop()
	{
		$id = intval(IReq::get("id"));
		$data["is_pass"] = 1;

		if (empty($id)) {
			$this->message("shop_noexit");
		}

		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $id . " ");

		if (empty($tempattr)) {
			$this->message("shop_noexit");
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $id . "'");
		$cdata["group"] = 3;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $cdata, "uid='" . $tempattr["uid"] . "'");
		$this->success("success");
	}

	public function savesetshopyjin()
	{
		$yjin = IReq::get("yjin");
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			$this->message("shop_noexit");
		}

		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $shopid . " ");

		if (empty($tempattr)) {
			$this->message("shop_noexit");
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin");
		}

		$data["yjin"] = $yjin;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function adminshoppx()
	{
		$shopid = intval(IReq::get("id"));
		$data["sort"] = intval(IReq::get("pxid"));
		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $shopid . " ");

		if (empty($tempattr)) {
			$this->message("shop_noexit");
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function shopactivetime()
	{
		$shopid = intval(IReq::get("shopid"));
		$mysetclosetime = intval(IReq::get("mysetclosetime"));
		$nowday = 24 * 60 * 60 * $mysetclosetime;
		$data["endtime"] = time() + $nowday;
		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $shopid . " ");

		if (empty($tempattr)) {
			$this->message("shop_noexit");
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function delshop()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("shop_noexit");
		}

		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $id . " ");

		if (empty($tempattr)) {
			$this->message("shop_noexit");
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shop", "id in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodstype", "shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopmarket", " shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopfast", " shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopattr", " shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopsearch", " shopid in($ids)");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "areatoadd", " shopid  in($ids) ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "searkey", " goid  in($ids)   ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "areamarket", " shopid  in($ids) ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "areatomar", " shopid  in($ids) ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "marketcate", " shopid  in($ids) ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopmarket", " shopid  in($ids) ");
		$this->success("success");
	}

	public function resetdefualt()
	{
		$shopid = IReq::get("shopid");
		$tempattr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $shopid . " ");
		$link = IUrl::creatUrl("areaadminpage/shop");

		if (empty($tempattr)) {
			$this->message("shop_noexit", $link);
		}

		if ($tempattr["admin_id"] != $this->admin["uid"]) {
			$this->message("shop_noownadmin", $link);
		}

		ICookie::set("adminshopid", $shopid, 86400);
		$link = IUrl::creatUrl("shopcenter/useredit");
		$this->refunction("", $link);
	}

	public function adminshoplist()
	{
		$this->setstatus();
		$where = " and admin_id = '" . $this->admin["uid"] . "'";
		$data["shopname"] = trim(IReq::get("shopname"));
		$data["username"] = trim(IReq::get("username"));
		$data["phone"] = trim(IReq::get("phone"));

		if (!empty($data["shopname"])) {
			$where .= " and shopname='" . $data["shopname"] . "'";
		}

		if (!empty($data["username"])) {
			$where .= " and uid in(select uid from " . Mysite::$app->config["tablepre"] . "member where username='" . $data["username"] . "')";
		}

		if (!empty($data["phone"])) {
			$where .= " and phone='" . $data["phone"] . "'";
		}

		$data["where"] = $where;
		Mysite::$app->setdata($data);
	}

	public function setstatus()
	{
		$data["shoptype"] = array("购卡", "养车");
		Mysite::$app->setdata($data);
	}

	public function adminshopwati()
	{
		$this->setstatus();
	}

	public function addshop()
	{
		$this->setstatus();
	}

	public function setnotice()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			echo "店铺不存在";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select noticetype,IMEI,machine_code,mKey,admin_id from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "店铺不存在";
			exit();
		}

		if ($shopinfo["admin_id"] != $this->admin["uid"]) {
			echo "该店铺不属于您管理";
			exit();
		}

		$data["IMEI"] = $shopinfo["IMEI"];
		$data["shopid"] = $shopid;
		$data["machine_code"] = $shopinfo["machine_code"];
		$data["mKey"] = $shopinfo["mKey"];
		$data["noticetype"] = explode(",", $shopinfo["noticetype"]);
		Mysite::$app->setdata($data);
	}

	public function saveshoppnotice()
	{
		$pstype = IReq::get("pstype");
		$shopid = intval(IReq::get("shopid"));
		$data["IMEI"] = IReq::get("IMEI");

		if (empty($shopid)) {
			echo "<script>parent.uploaderror('店铺获取失败');</script>";
			exit();
		}

		$tempvalue = "";

		if (is_array($pstype)) {
			$tempvalue = join(",", $pstype);
		}

		$shopinfo = $this->mysql->select_one("select noticetype,IMEI,machine_code,mKey,admin_id from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "<script>parent.uploaderror('店铺不存在');</script>";
			exit();
		}

		if ($shopinfo["admin_id"] != $this->admin["uid"]) {
			echo "<script>parent.uploaderror('该店铺不属于您管理');</script>";
			exit();
		}

		$data["noticetype"] = $tempvalue;
		$data["machine_code"] = IReq::get("machine_code");
		$data["mKey"] = IReq::get("mKey");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		echo "<script>parent.uploadsucess('');</script>";
		exit();
	}
}


