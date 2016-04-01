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
class method extends adminbaseclass
{
	public function savegoodsattr()
	{
		$this->checkadminlogin();
		$arrtypename = IReq::get("typename");
		$arrtypename = (is_array($arrtypename) ? $arrtypename : array($arrtypename));
		$siteinfo["goodsattr"] = serialize($arrtypename);
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

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
			$this->mysql->update(Mysite::$app->config["tablepre"] . "member", array("admin_id" => intval(IReq::get("admin_id"))), "uid='" . $testinfo["uid"] . "'");

			if (!empty($shopinfo)) {
				$this->message("shop_repeatname");
			}

			$data["uid"] = $testinfo["uid"];
			$data["shoptype"] = intval(IReq::get("shoptype"));
			$data["admin_id"] = intval(IReq::get("admin_id"));
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
				$this->mysql->update(Mysite::$app->config["tablepre"] . "member", array("admin_id" => intval(IReq::get("admin_id"))), "uid='" . $uid . "'");
			}
			else {
				$this->message($this->memberCls->ero());
			}

			$sdata["uid"] = $uid;
			$sdata["maphone"] = $data["phone"];
			$sdata["addtime"] = time();
			$sdata["email"] = $data["email"];
			$sdata["shoptype"] = intval(IReq::get("shoptype"));
			$sdata["admin_id"] = intval(IReq::get("admin_id"));
			$nowday = 24 * 60 * 60 * 365;
			$sdata["endtime"] = time() + $nowday;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shop", $sdata);
			$this->success("success");
		}
		else {
			$this->message("system_err");
		}
	}

	public function shopbiaoqian()
	{
		$this->setstatus();
		$shopid = intval(IReq::get("id"));

		if (empty($shopid)) {
			echo "shop_noexit";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "shop_noexit";
			exit();
		}

		$fastfood = array();

		if ($shopinfo["shoptype"] == 0) {
			$fastfood = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");
		}
		else {
			$fastfood = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid=" . $shopid . "  ");
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
		$data["ztylist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "specialpage where  is_custom = 0 and showtype = 0 and is_show = 1 order by orderid asc ");
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
				$data["is_hui"] = intval(IReq::get("fis_hui"));
				$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopid . "'");
			}
		}
		else {
			$fastfood = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid=" . $shopid . "  ");

			if (0 < count($fastfood)) {
				$data["is_hui"] = intval(IReq::get("fis_hui"));
				$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopid . "'");
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

		$data["yjin"] = round($yjin, 2);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function adminshoppx()
	{
		$shopid = intval(IReq::get("id"));
		$data["sort"] = intval(IReq::get("pxid"));
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function shopactivetime()
	{
		$shopid = intval(IReq::get("shopid"));
		$mysetclosetime = intval(IReq::get("mysetclosetime"));
		$nowday = 24 * 60 * 60 * $mysetclosetime;
		$data["endtime"] = time() + $nowday;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success");
	}

	public function delshop()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("shop_noexit");
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

	public function delshoptype()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("shop_noexit");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "shoptype", " id in($ids) ");
		$this->success("success");
	}

	public function saveshoptype()
	{
		$id = intval(IReq::get("id"));
		$data["name"] = trim(IReq::get("name"));
		$data["type"] = trim(IReq::get("type"));
		$data["parent_id"] = 0;
		$data["cattype"] = intval(IReq::get("cattype"));
		$data["is_search"] = intval(IReq::get("is_search"));
		$data["is_search"] = intval(IReq::get("is_search"));
		$data["is_main"] = intval(IReq::get("is_main"));
		$data["is_admin"] = intval(IReq::get("is_admin"));
		$data["instro"] = IReq::get("instro");
		$data["orderid"] = IReq::get("orderid");

		if (empty($data["name"])) {
			$this->message("shop_emptyattr");
		}

		if (empty($data["type"])) {
			$this->message("shop_emptydatatype");
		}

		if (empty($id)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "shoptype", $data);
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shoptype", $data, "id='" . $id . "'");
		}

		$this->success("success");
	}

	public function saveshopdettype()
	{
		$id = IReq::get("id");

		if ($id < 0) {
			$this->message("system_err");
		}

		$ids = IReq::get("ids");
		$name = IReq::get("name");
		$instro = IReq::get("instro");
		$cattype = IReq::get("cattype");
		$ids = (is_array($ids) ? $ids : array($ids));
		$name = (is_array($name) ? $name : array($name));
		$instro = (is_array($instro) ? $instro : array($instro));
		$checkdo = true;
		$newdata = array();
		$delids = array();

		foreach ($name as $key => $value ) {
			if (empty($value)) {
				$checkdo = false;
				break;
			}

			$tempdata = array();
			$tempdata["name"] = $value;
			$tempdata["id"] = (isset($ids[$key]) ? $ids[$key] : 0);
			$tempdata["instro"] = (isset($instro[$key]) ? $instro[$key] : "");

			if (0 < $tempdata["id"]) {
				$delids[] = $tempdata["id"];
			}

			$newdata[] = $tempdata;
		}

		$notinids = join(",", $delids);

		if (!empty($notinids)) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shoptype", "parent_id = $id and id not in($notinids)");
		}
		else {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shoptype", "parent_id = $id");
		}

		if ($checkdo == false) {
			$this->message("system_err");
		}

		foreach ($newdata as $key => $value ) {
			$data["type"] = 0;
			$data["parent_id"] = $id;
			$data["cattype"] = $cattype;
			$data["is_search"] = 0;
			$data["is_main"] = 0;
			$data["is_admin"] = 0;
			$data["name"] = $value["name"];
			$data["instro"] = $value["instro"];

			if (0 < $value["id"]) {
				$this->mysql->update(Mysite::$app->config["tablepre"] . "shoptype", $data, "id='" . $value["id"] . "'");
			}
			else {
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "shoptype", $data);
			}
		}

		$this->success("success");
	}

	public function resetdefualt()
	{
		$shopid = IReq::get("shopid");
		ICookie::set("adminshopid", $shopid, 86400);
		$link = IUrl::creatUrl("shopcenter/useredit");
		$this->refunction("", $link);
	}

	public function savegoodssign()
	{
		$id = intval(IReq::get("uid"));
		$data["name"] = IReq::get("name");
		$data["imgurl"] = IReq::get("img");
		$data["type"] = IReq::get("typename");
		$data["instro"] = IReq::get("instro");
		$data["typevalue"] = IReq::get("typevalue");

		if (empty($data["name"])) {
			$this->message("shop_emptysignname");
		}

		if (empty($data["imgurl"])) {
			$this->message("shop_emptysignimg");
		}

		if (empty($id)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodssign", $data);
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodssign", $data, "id='" . $id . "'");
		}

		$this->success("success");
	}

	public function delgoodssign()
	{
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("shop_emptysign");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodssign", " id in($ids) ");
		$this->success("success");
	}

	public function adminshoplist()
	{
		$this->setstatus();
		$where = "";
		$data["shopname"] = trim(IReq::get("shopname"));
		$data["username"] = trim(IReq::get("username"));
		$data["phone"] = trim(IReq::get("phone"));

		if (!empty($data["shopname"])) {
			$where .= " and shopname like '%" . $data["shopname"] . "%'";
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
		$data["shoptype"] = array("停车", "养车");
		Mysite::$app->setdata($data);
	}

	public function adminshopwati()
	{
		$this->setstatus();
		$adminlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "admin where groupid='4' ");
		$temparr = array();

		foreach ($adminlist as $key => $value ) {
			$temparr[$value["uid"]] = $value["username"];
		}

		$data["adminlist"] = $temparr;
		Mysite::$app->setdata($data);
	}

	public function shoptype()
	{
		$this->setstatus();
	}

	public function addshop()
	{
		$this->setstatus();
		$adminlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "admin where groupid='4' ");
		$data["adminall"] = $adminlist;
		Mysite::$app->setdata($data);
	}

	public function setnotice()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			echo "店铺不存在";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select noticetype,IMEI,machine_code,mKey from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "店铺不存在";
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

		$data["noticetype"] = $tempvalue;
		$data["machine_code"] = IReq::get("machine_code");
		$data["mKey"] = IReq::get("mKey");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		echo "<script>parent.uploadsucess('');</script>";
		exit();
	}

	public function savelunadv()
	{
		$shopid = ICookie::get("adminshopid");
		$imglist = IFilter::act(IReq::get("imglist"));
		$links = IUrl::creatUrl("shop/shoplunadv");

		if (empty($imglist)) {
			$this->message("empty_img", $links);
		}

		$data["imglist"] = join(",", $imglist);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
		$this->success("success", $links);
	}

	public function goodslibrary()
	{
		$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate   order by orderid asc  ");
		$alllist = array();

		if (is_array($listtype)) {
			foreach ($listtype as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where typeid = '" . $value["id"] . "' order by good_order asc limit 0,1000  ");
				$alllist[] = $value;
			}
		}

		$data["list"] = $alllist;
		Mysite::$app->setdata($data);
	}

	public function doinputexcel()
	{
		$newfilename = IFilter::act(IReq::get("newfilename"));
		$curtypeid = intval(IFilter::act(IReq::get("curtypeid")));
		$typeidone = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate where id = " . $curtypeid . "  order by id asc  ");

		if (isset($_FILES["inputExcel"])) {
			$filename = time();
			$tmp_name = $_FILES["inputExcel"]["tmp_name"];
			$filepre = $_FILES["inputExcel"]["name"];
			$filepre = explode(".", $filepre);
			$filepre = $filepre[1];
			$uploadpath = hopedir . "upload/excel/";
			$newfilename = $uploadpath . $filename . "." . $filepre;
			$result = move_uploaded_file($tmp_name, $uploadpath . $filename . "." . $filepre);
			$excelclass = new phptoexcel();
			$newarray = $excelclass->getexcel($newfilename, array("goodsname", "goodscost", "goodsinstro"), array(0, 1, 2));

			foreach ($newarray as $key => $value ) {
				$data["name"] = $value["goodsname"];
				$data["cost"] = $value["goodscost"];
				$data["instro"] = $value["goodsinstro"];
				$data["typeid"] = $typeidone["id"];
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodslibrary", $data);
			}

			echo "<script>parent.closemydo();</script>";
			exit();
		}

		$data["newfilename"] = $newfilename;
		$data["curtypeid"] = $curtypeid;
		Mysite::$app->setdata($data);
	}

	public function savegoodstype()
	{
		$data["name"] = IFilter::act(IReq::get("name"));
		$data["orderid"] = intval(IReq::get("orderid"));

		if (!IValidate::len($data["name"], 1, 10)) {
			$this->message("goods_namelenth");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodslibrarycate", $data);
		$this->success("success");
	}

	public function editgoodstype()
	{
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

			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrarycate", $arr, "id='" . $addressid . "' ");
			$this->success("success");
		}
		else if ($what == "orderid") {
			$arr["orderid"] = intval(IReq::get("controlname"));
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrarycate", $arr, "id='" . $addressid . "'  ");
			$this->success("操作成功");
		}
		else if ($what == "allinfo") {
			$arr["name"] = IFilter::act(IReq::get("name"));
			$arr["orderid"] = intval(IFilter::act(IReq::get("orderid")));
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrarycate", $arr, "id='" . $addressid . "'  ");
			$this->success("success");
		}
		else {
			$this->message("nodefined_func");
		}
	}

	public function delgoodstype()
	{
		$uid = intval(IReq::get("addressid"));

		if (empty($uid)) {
			$this->message("goods_emptytype");
		}

		$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate where id = '$uid' ");

		if ($checkshuliang < 1) {
			$this->message("goods_emptytype");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodslibrary", "typeid = '$uid' ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodslibrarycate", "id = '$uid' ");
		$this->success("success");
	}

	public function addgoods()
	{
		$data["name"] = trim(IFilter::act(IReq::get("name")));
		$data["typeid"] = IFilter::act(IReq::get("typeid"));
		$data["cost"] = IFilter::act(IReq::get("cost"));
		$data["good_order"] = IFilter::act(IReq::get("good_order"));
		$data["img"] = "";

		if (!IValidate::len($data["name"], 2, 50)) {
			$this->message("goods_titlelenth");
		}

		$chekcount = $data["cost"] * 100;

		if ($data["cost"] < 1) {
			$this->message("goods_cost");
		}

		$data["instro"] = "";
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodslibrary", $data);
		$id = $this->mysql->insertid();
		$info = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where id = '$id'");

		if (empty($info)) {
			$this->message("goods_empty");
		}

		$this->success($info);
	}

	public function goodsone()
	{
		$id = intval(IFilter::act(IReq::get("gid")));

		if (empty($id)) {
			$this->message("goods_empty");
		}

		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where id=" . $id . "");

		if (empty($goodsinfo)) {
			$this->message("goods_empty");
		}

		$listtype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate   order by orderid asc  ");
		$data["goodsinfo"] = $goodsinfo;
		$data["listtype"] = $listtype;
		Mysite::$app->setdata($data);
	}

	public function savegoodsall()
	{
		$gid = intval(IFilter::act(IReq::get("gid")));
		$link = IUrl::creatUrl("adminpage/shop/module/goodslibrary");

		if (empty($gid)) {
			$this->message("goods_empty", $link);
		}

		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where  id=" . $gid . "");

		if (empty($goodsinfo)) {
			$this->message("goods_empty", $link);
		}

		$data["typeid"] = intval(IFilter::act(IReq::get("typeid")));
		$data["instro"] = IReq::get("instro");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrary", $data, "id='" . $gid . "' ");
		$data["id"] = $gid;
		$goodsinfo["typeid"] = $data["typeid"];
		echo "<script>parent.refreshgoods(" . json_encode($goodsinfo) . ");</script>";
		exit();
	}

	public function userupload()
	{
		$link = IUrl::creatUrl("member/login");
		if (($this->member["uid"] == 0) && ($this->admin["uid"] == 0)) {
			$this->message("未登陆", $link);
		}

		$_FILES["imgFile"] = $_FILES["head"];
		$json = new Services_JSON();
		$uploadpath = "upload/user/";
		$filepath = "/upload/user/";
		$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "png"));
		$file = $upload->getfile();
		if (($upload->errno != 15) && ($upload->errno != 0)) {
			$this->message($upload->errmsg());
		}
		else {
			$gid = intval(IFilter::act(IReq::get("gid")));
			$data["img"] = $filepath . $file[0]["saveName"];
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrary", $data, "id='" . $gid . "'");
			$this->success($filepath . $file[0]["saveName"]);
		}
	}

	public function delgoods()
	{
		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("goods_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodslibrary", "id = '$uid'");
		$this->success("success");
	}

	public function delgoodsimg()
	{
		$id = intval(IReq::get("id"));
		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodslibrary where id ='" . $id . "' ");

		if (empty($goodsinfo)) {
			$this->message("goods_empty");
		}

		if (!empty($goodsinfo["img"])) {
			IFile::unlink(hopedir . $goodsinfo["img"]);
			$udata["img"] = "";
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrary", $udata, "id='" . $id . "'");
		}

		$this->success("操作成功");
	}

	public function updategoods()
	{
		$controlname = trim(IFilter::act(IReq::get("controlname")));
		$goodsid = intval(IReq::get("goodsid"));
		$values = trim(IReq::get("values"));

		if (empty($goodsid)) {
			$this->message("goods_empty");
		}

		switch ($controlname) {
		case $controlname:
			if (!IValidate::len($values, 2, 50)) {
				$this->message("goods_titlelenth");
			}

			$data["name"] = $values;
			break;

		case $controlname:
			if (!IValidate::len($values, 0, 200)) {
				$this->message("goods_instrolenth");
			}

			$data["instro"] = $values;
			break;

		case $controlname:
			$values = $values * 100;
			$kk = intval($values);

			if ($kk < 0) {
				$this->message("goods_cost");
			}

			$data["cost"] = $values / 100;
			break;

		case $controlname:
			$values = $values;
			$kk = intval($values);

			if ($kk < 0) {
				$this->message("good_order");
			}

			$data["good_order"] = $values;
			break;

		case $controlname:
			$values = intval($values);

			if (empty($values)) {
				$this->message("goods_typeid");
			}

			$shopinfo = $this->shopinfo();
			$checkshuliang = 0;
			$checkshuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodslibrarycate where id = '$values' ");

			if ($checkshuliang < 1) {
				$this->message("goods_typeid");
			}

			$data["typeid"] = $values;
			break;

		default:
			$this->message("nodefined_func");
			break;
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrary", $data, "id='" . $goodsid . "' ");
		$this->success("success");
	}

	public function delkuimg()
	{
		$imglujing = trim(IReq::get("imglujing"));
		IFile::unlink(hopedir . $imglujing);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "imglist", "imageurl = '$imglujing'");
		$this->success("success");
	}

	public function uploadkuimggoods()
	{
		$gid = intval(IFilter::act(IReq::get("gid")));
		$data["img"] = trim(IFilter::act(IReq::get("imglujing")));
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goodslibrary", $data, "id='" . $gid . "'");
		$this->success("success");
	}

	public function selectmarketimg()
	{
		$data["gid"] = intval(IReq::get("gid"));
		$this->pageCls->setpage(intval(IReq::get("page")), 18);
		$total = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "imglist      ");
		$data["imglist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "imglist      order by addtime desc limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . " ");
		$link = IUrl::creatUrl("adminpage/shop/module/selectmarketimg/gid/" . $data["gid"]);
		$data["pagecontent"] = $this->pageCls->multi($total, 18, intval(IReq::get("page")), $link);
		$data["page"] = intval(IReq::get("page"));
		Mysite::$app->setdata($data);
	}

	public function showimglist()
	{
		$this->pageCls->setpage(intval(IReq::get("page")), 18);
		$total = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "imglist      ");
		$data["imglist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "imglist      order by addtime desc limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . " ");
		$link = IUrl::creatUrl("adminpage/shop/module/showimglist");
		$data["pagecontent"] = $this->pageCls->multi($total, 18, intval(IReq::get("page")), $link);
		$data["page"] = intval(IReq::get("page"));
		Mysite::$app->setdata($data);
	}

	public function hshowimglist()
	{
		$this->pageCls->setpage(intval(IReq::get("page")), 18);
		$total = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "imglist      ");
		$data["imglist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "imglist      order by addtime desc limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . " ");
		Mysite::$app->setdata($data);
	}

	public function showshopdetail()
	{
		$id = intval(IReq::get("id"));
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where  id='" . $id . "'  ");
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function goodsgg()
	{
		$pageshow = new page();
		$pageshow->setpage(IReq::get("page"), 10);
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where parent_id =0  order by orderid asc limit " . $pageshow->startnum() . ", " . $pageshow->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goodsgg  where parent_id = 0 ");
		$pageshow->setnum($shuliang);
		$memcostloglist = array();

		foreach ($templist as $key => $value ) {
			$tempc = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where parent_id =" . $value["id"] . "  order by orderid asc limit 0,30");
			$value["det"] = $tempc;
			$memcostloglist[] = $value;
		}

		$data["pagecontent"] = $pageshow->getpagebar();
		$data["gglist"] = $memcostloglist;
		Mysite::$app->setdata($data);
	}

	public function editgoodsgg()
	{
		$id = intval(IReq::get("id"));
		$data["gginfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where id =" . $id . " and parent_id = 0  order by orderid asc limit 0,30");
		$data["ggdet"] = array();

		if (!empty($data["gginfo"])) {
			$data["ggdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where parent_id =" . $id . "  order by orderid asc limit 0,30");
		}

		$data["shoptype"] = array("餐饮", "养车");
		Mysite::$app->setdata($data);
	}

	public function delgoodsgg()
	{
		$id = intval(IReq::get("id"));

		if ($id < 1) {
			$this->message("规格ID错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodsgg", "id = '$id'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodsgg", "parent_id = '$id'");
		$this->success("success");
	}

	public function savemaingg()
	{
		$id = intval(IReq::get("id"));

		if (0 < $id) {
			$gginfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where id =" . $id . " and parent_id = 0  order by orderid asc limit 0,30");

			if (empty($gginfo)) {
				$this->message("保存的规格ID 不是规格名属性");
			}
		}

		$data["name"] = trim(IFilter::act(IReq::get("name")));
		$data["orderid"] = intval(IFilter::act(IReq::get("orderid")));

		if (0 < $id) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodsgg", $data, "id='" . $id . "' ");
		}
		else {
			$data["parent_id"] = 0;
			$data["shoptype"] = intval(IFilter::act(IReq::get("shoptype")));
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodsgg", $data);
			$id = $this->mysql->insertid();
		}

		$link = IUrl::creatUrl("adminpage/shop/module/editgoodsgg/id/" . $id);
		$this->success("success", $link);
	}

	public function savechildgg()
	{
		$parent_id = intval(IReq::get("parent_id"));

		if ($parent_id < 1) {
			$this->message("所属规格不存在");
		}

		$maingg = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where id =" . $parent_id . " and parent_id = 0  order by orderid asc limit 0,30");

		if (empty($maingg)) {
			$this->message("所属规格不存在");
		}

		$id = intval(IReq::get("id"));
		$data["name"] = trim(IReq::get("name"));
		$data["orderid"] = trim(IReq::get("orderid"));

		if (0 < $id) {
			$childgg = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodsgg where id =" . $id . "    order by orderid asc limit 0,30");

			if ($childgg["parent_id"] != $maingg["id"]) {
				$this->message("编辑规格值与所属规格不一致");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "goodsgg", $data, "id='" . $id . "' ");
		}
		else {
			$data["parent_id"] = $maingg["id"];
			$data["shoptype"] = $maingg["shoptype"];
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "goodsgg", $data);
		}

		$this->success("success");
	}

	public function delgoodschildgg()
	{
		$id = intval(IReq::get("id"));

		if ($id < 1) {
			$this->message("规格属性错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodsgg", "id = '$id'");
		$this->success("success");
	}

	public function savesearchwords()
	{
		$this->checkadminlogin();
		$typename = IReq::get("typename");
		$typename = (is_array($typename) ? $typename : array($typename));
		$siteinfo["searchwords"] = serialize($typename);
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}
}


