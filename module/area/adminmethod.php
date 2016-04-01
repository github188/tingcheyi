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
	public function savearea()
	{
		$id = intval(IReq::get("uid"));
		$data["name"] = IReq::get("name");
		$data["orderid"] = intval(IReq::get("orderid"));
		$data["pin"] = IReq::get("pin");
		$data["parent_id"] = intval(IReq::get("parent_id"));
		$data["imgurl"] = IReq::get("imgurl");
		$data["is_com"] = intval(IReq::get("is_com"));

		if (empty($id)) {
			$data["lng"] = 0;
			$data["lat"] = 0;
			$link = IUrl::creatUrl("area/adminarealist");

			if (empty($data["name"])) {
				$this->message("area_emptyname", $link);
			}

			if (empty($data["pin"])) {
				$this->message("area_emptyfirdstword", $link);
			}

			if (0 < $data["parent_id"]) {
				$checkarea = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $data["parent_id"] . "");
				$data["admin_id"] = $checkarea["admin_id"];
			}

			if (!$this->mysql->insert(Mysite::$app->config["tablepre"] . "area", $data)) {
				$this->message("system_err");
			}
		}
		else {
			$link = IUrl::creatUrl("area/adminarealist/id/" . $id);

			if (empty($data["name"])) {
				$this->message("area_emptyname", $link);
			}

			if (empty($data["pin"])) {
				$this->message("area_emptyfirdstword", $link);
			}

			if (0 < $data["parent_id"]) {
				$checkarea = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $data["parent_id"] . "");
				$data["admin_id"] = $checkarea["admin_id"];
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "area", $data, "id='" . $id . "'");
		}

		$link = IUrl::creatUrl("area/adminarealist");
		$this->success("success", $link);
	}

	public function adminareacost()
	{
		$areaid = intval(IReq::get("areaid"));
		$type = trim(IReq::get("type"));
		$cost = IFilter::act(IReq::get("cost"));
		$cost = intval($cost * 10) / 10;

		if ($areaid < 1) {
			$this->message("area_iderr");
		}

		if (!in_array($type, array("add", "del", "updat"))) {
			$this->message("nodefined_func");
		}

		$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $areaid . "");

		if (empty($areainfo)) {
			$this->message("area_empty");
		}

		$insertdata["cost"] = $cost;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "areatoadd", $insertdata, " shopid = 0 and areaid ='" . $areaid . "'");
		$this->success("success");
	}

	public function adminpsset()
	{
		$data["pestypearr"] = array(1 => "网站统一配送费", 2 => "根据不同区域统一配送费", 3 => "不计算配送费", 4 => "百度地图根据距离测算配送费", 5 => "根据菜品数计算配送费");
		Mysite::$app->setdata($data);
	}

	public function savepsset()
	{
		$locationtype = intval(IFilter::act(IReq::get("locationtype")));
		$licationmudule = intval(IFilter::act(IReq::get("licationmudule")));

		if ($locationtype == 1) {
			$test = Mysite::$app->config["baidumapkey"];

			if (empty($test)) {
				$this->message("setmapsecrkey");
			}
		}

		$locationradius = intval(IFilter::act(IReq::get("locationradius")));
		$savearray["locationtype"] = $locationtype;
		$savearray["licationmudule"] = $licationmudule;
		$savearray["locationradius"] = $locationradius;
		$temparray = array();

		for ($i = 0; $i < $locationradius; $i++) {
			$temparray[$i] = intval(IFilter::act(IReq::get("radiusvalue" . $i)));
		}

		$savearray["radiusvalue"] = serialize($temparray);
		$savearray["psvalue"] = intval(IFilter::act(IReq::get("psvalue")));
		$config = new config("hopeconfig.php", hopedir);
		$config->write($savearray);
		$this->success("success");
	}

	public function adminarealist()
	{
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		$this->getgodigui($areainfo, 0, 0);
		$data["arealist"] = $this->digui;
		$adminlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "admin where groupid='4' ");
		$temparr = array();

		foreach ($adminlist as $key => $value ) {
			$temparr[$value["uid"]] = $value["username"];
		}

		$data["adminlist"] = $temparr;
		$data["adminall"] = $adminlist;
		Mysite::$app->setdata($data);
	}

	public function setareaadmin()
	{
		$areaid = intval(IReq::get("areaid"));
		$admin_id = intval(IReq::get("adminid"));

		if (empty($areaid)) {
			$this->message("area_empty");
		}

		$checkarea = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id = '" . $areaid . "' ");

		if (empty($checkarea)) {
			$this->message("area_empty");
		}

		if (0 < $checkarea["parent_id"]) {
			$this->message("area_isnotparent");
		}

		$this->getgodiguiupdata($areaid, $admin_id);
		$this->success("success");
	}

	public function getgodiguiupdata($areaid, $admin_id)
	{
		$this->mysql->update(Mysite::$app->config["tablepre"] . "area", array("admin_id" => $admin_id), "id='" . $areaid . "'");
		$arraylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = '" . $areaid . "' ");

		if (0 < count($arraylist)) {
			foreach ($arraylist as $key => $value ) {
				$this->getgodiguiupdata($value["id"], $admin_id);
			}
		}
	}

	public function setps()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			echo "店铺不存在";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "店铺不存在";
			exit();
		}

		$tempinfo = array();

		if ($shopinfo["shoptype"] == 0) {
			$tempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");
		}
		else {
			$tempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid=" . $shopid . "  ");
		}

		$data["shopinfo"] = $shopinfo;
		$data["shopdetinfo"] = $tempinfo;
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		$this->getgodigui($areainfo, 0, 0);
		$data["arealist"] = $this->digui;
		$data["shopvalues"] = array();

		if (empty($tempinfo["pradiusvalue"])) {
			$data["shopvalues"] = unserialize(Mysite::$app->config["radiusvalue"]);
		}
		else {
			$data["shopvalues"] = unserialize($tempinfo["pradiusvalue"]);
		}

		$data["dolenth"] = count($data["shopvalues"]);
		$choicelist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "areashop where shopid = " . $shopid . "   order by areaid asc limit 0,1000");
		$temp = array();

		foreach ($choicelist as $key => $value ) {
			$temp[] = $value["areaid"];
		}

		$data["choiceid"] = $temp;
		$adminlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "admin where groupid='4' ");
		$data["adminlist"] = $adminlist;
		Mysite::$app->setdata($data);
	}

	public function savesetps()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			echo "<script>parent.uploaderror('店铺ID不存在');</script>";
			exit();
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $shopid . "  ");

		if (empty($shopinfo)) {
			echo "<script>parent.uploaderror('店铺不存在');</script>";
			exit();
		}

		$tempinfo = array();

		if (empty($shopinfo["shoptype"])) {
			$tempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid=" . $shopid . "  ");
		}
		else {
			$tempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid=" . $shopid . "  ");
		}

		if (empty($tempinfo)) {
			echo "<script>parent.uploaderror('店铺数据不存在');</script>";
			exit();
		}

		$data["sendtype"] = intval(IReq::get("sendtype"));
		$data["pscost"] = intval(IReq::get("pscost"));
		$data["pradius"] = intval(IReq::get("pradius"));
		$tempdo = array();

		for ($i = 0; $i < $data["pradius"]; $i++) {
			$tempdo[$i] = intval(IReq::get("radiusvalue" . $i));
		}

		$data["pradiusvalue"] = serialize($tempdo);

		if (empty($shopinfo["shoptype"])) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopfast", $data, "shopid='" . $shopid . "'");
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "shopmarket", $data, "shopid='" . $shopid . "'");
		}

		$shopdata["pradiusa"] = intval(IReq::get("pradius"));
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $shopdata, "id='" . $shopid . "'");
		$admin_id = intval(IReq::get("admin_id"));
		$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", array("admin_id" => $admin_id), "id='" . $shopid . "'");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", array("admin_id" => $admin_id), "uid='" . $shopinfo["uid"] . "'");
		$areaids = IReq::get("areaids");
		$tempwhere = (empty($admin_id) ? "" : " and admin_id ='" . $admin_id . "'");

		if (is_array($areaids)) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "areashop", " shopid='" . $shopid . "'  ");
			$checkareais = $areaids;

			while (0 < count($checkareais)) {
				$checkarealist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where id in(" . join(",", $checkareais) . ") " . $tempwhere . "  order by id desc limit 0,1000");
				$checkareais = array();

				foreach ($checkarealist as $key => $value ) {
					$areadata["shopid"] = $shopid;
					$areadata["areaid"] = $value["id"];
					$this->mysql->insert(Mysite::$app->config["tablepre"] . "areashop", $areadata);

					if (0 < $value["parent_id"]) {
						$checkareais[] = $value["parent_id"];
					}
				}

				$checkareais = array_unique($checkareais);
			}
		}
		else if (Mysite::$app->config["locationtype"] != 1) {
			echo "<script>parent.uploaderror('配送区域未选择');</script>";
			exit();
		}

		echo "<script>parent.uploadsucess('');</script>";
		exit();
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

	public function baidumap()
	{
		$id = intval(IReq::get("id"));
		$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where  id = '$id' order by id asc");

		if (empty($areainfo)) {
			echo "获取信息失败";
			exit();
		}

		$is_name = 1;
		$data["arealng"] = 0;
		$data["arealat"] = 0;
		$data["nowcityname"] = "";

		if (empty($areainfo["parent_id"])) {
			$data["nowcityname"] = $areainfo["name"];
			$checklng = intval($areainfo["lng"]);

			if (!empty($checklng)) {
				$is_name = 2;
				$data["arealng"] = $areainfo["lng"];
				$data["arealat"] = $areainfo["lat"];
			}
		}
		else {
			$data["nowcityname"] = $areainfo["name"];
			$pinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where  id = '" . $areainfo["parent_id"] . "' order by id asc");

			if (empty($pinfo)) {
				echo "获取上级地址失败";
				exit();
			}

			$checklng = intval($areainfo["lng"]);

			if (!empty($checklng)) {
				$is_name = 2;
				$data["arealng"] = $areainfo["lng"];
				$data["arealat"] = $areainfo["lat"];
			}
			else {
				$checklng2 = intval($pinfo["lng"]);

				if (!empty($checklng2)) {
					$is_name = 2;
					$data["arealng"] = $pinfo["lng"];
					$data["arealat"] = $pinfo["lat"];
				}
				else {
					echo "未设置上级地址百度坐标";
					exit();
				}
			}
		}

		$data["arealng"] = ($data["arealng"] == 0 ? Mysite::$app->config["baidulng"] : $data["arealng"]);
		$data["arealat"] = ($data["arealat"] == 0 ? Mysite::$app->config["baidulat"] : $data["arealat"]);
		$data["is_name"] = $is_name;
		$data["myareaid"] = $id;
		$data["baidumapkey"] = Mysite::$app->config["baidumapkey"];
		Mysite::$app->setdata($data);
	}

	public function savemaplocation()
	{
		$id = intval(IReq::get("id"));

		if ($id < 1) {
			$this->json("area_empty");
		}

		$data["lng"] = IReq::get("lng");
		$data["lat"] = IReq::get("lat");

		if (empty($data["lng"])) {
			$this->message("emptylng");
		}

		if (empty($data["lat"])) {
			$this->message("emptylat");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "area", $data, "id='" . $id . "'");
		$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where  id = '" . $id . "' order by id asc");

		if (!empty($areainfo)) {
			$areasearch = new areasearch($this->mysql);
			$areasearch->setdata($areainfo["name"], "1", $id, $areainfo["lat"], $areainfo["lng"]);
			$areasearch->del();

			if ($areasearch->save()) {
			}
			else {
				$checkinfo = $areasearch->error();
			}
		}

		$this->success("success");
	}

	public function searchkey()
	{
		$data["searchvalue"] = "";
		$data["typearr"] = array("未定义", "区域地址", "店铺", "用户搜索");
		$where = "";
		$searchvalue = trim(IReq::get("searchvalue"));
		$typeid = intval(IReq::get("typeid"));
		$newlink = "";
		$data["typeid"] = "";

		if (!empty($searchvalue)) {
			$data["searchvalue"] = $searchvalue;
			$where = "  where   datacontent like '%" . $searchvalue . "%' ";
			$newlink = "/searchvalue/" . $searchvalue;
		}

		if (in_array($typeid, array(1, 2, 3))) {
			$data["typeid"] = $typeid;
			$where = (empty($where) ? "  where  datatype='" . $typeid . "' " : $where . "  and datatype='" . $typeid . "'");
			$newlink .= "/typeid/" . $typeid;
		}

		$link = IUrl::creatUrl("/adminpage/area/module/searchkey" . $newlink);
		$this->pageCls->setpage(IReq::get("page"), 15);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "positionkey    " . $where . " order by datatype,parent_id   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "positionkey    " . $where . "   ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function delsearch()
	{
		$datatype = intval(IReq::get("dataintype"));
		$parent_id = intval(IReq::get("parent_id"));
		$areasearch = new areasearch($this->mysql);
		$areasearch->setdata("", $datatype, $parent_id);
		$areasearch->del();
		$this->success("success");
	}

	public function clearsearch()
	{
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "positionkey", "datatype > 0");
		$this->success("success");
	}

	public function setshow()
	{
		$id = intval(IReq::get("id"));
		$type = intval(IReq::get("type"));

		if (empty($id)) {
			$this->message("area_empty");
		}

		$data["show"] = ($type != 1 ? 0 : 1);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "area", $data, "id='" . $id . "'");
		$this->success("success");
	}

	public function delarea()
	{
		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("area_empty");
		}

		$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where  parent_id = '" . $uid . "' order by id asc");

		if (!empty($areainfo)) {
			$this->message("area_isextchild");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "area", "id = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "areashop", "areaid = '$uid'");
		$this->success("success");
	}

	public function addarealist()
	{
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


