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
class method extends wxbaseclass
{
	public function postmsg()
	{
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			$this->message("订单ID错误");
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "'  ");

		if (empty($orderinfo)) {
			$this->message("订单不存在");
		}

		$orderclass = new orderclass();
		$orderclass->sendmess($orderinfo["id"]);
		$link = IUrl::creatUrl("wxsite/subshow/orderid/" . $orderid);
		$this->message("", $link);
	}

	public function choice()
	{
		$this->checkwxuser();
		$id = IFilter::act(IReq::get("id"));

		if (0 < $id) {
			$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $id . "  ");

			if (empty($checkinfo)) {
				$link = IUrl::creatUrl("wxsite/choice");
				$this->message("", $link);
			}

			$checkinfo2 = $this->mysql->select_one("select id,name,parent_id from " . Mysite::$app->config["tablepre"] . "area where parent_id=" . $id . "  ");

			if (empty($checkinfo2)) {
				ICookie::set("lng", $checkinfo["lng"], 2592000);
				ICookie::set("lat", $checkinfo["lat"], 2592000);
				ICookie::set("mapname", $checkinfo["name"], 2592000);
				ICookie::set("myaddress", $checkinfo["id"], 2592000);
				$cookmalist = ICookie::get("cookmalist");
				$cooklnglist = ICookie::get("cooklnglist");
				$cooklatlist = ICookie::get("cooklatlist");
				$link = IUrl::creatUrl("wxsite/shoplist/areaid/" . $checkinfo["id"]);
				$this->message("", $link);
			}
		}
	}

	public function appdown()
	{
	}

	public function saveloation()
	{
		$lat = IFilter::act(IReq::get("lat"));
		$lng = IFilter::act(IReq::get("lng"));
		$addressname = IFilter::act(IReq::get("addressname"));
		ICookie::set("lat", $lat);
		ICookie::set("lng", $lng);
		ICookie::set("addressname", $addressname);
		$this->success("success");
	}

	public function dwLocation()
	{
		ICookie::clear("lat");
		ICookie::clear("lng");
		ICookie::clear("addressname");
		$link = IUrl::creatUrl("wxsite/index");
		$this->message("", $link);
	}

	public function index()
	{
		$this->checkwxuser();
		$moretypelist = $this->mysql->getarr("select* from " . Mysite::$app->config["tablepre"] . "appadv where type=2  order by orderid  asc  limit 7 ");
		$moduleshow = $this->mysql->getarr("select* from " . Mysite::$app->config["tablepre"] . "appmudel where FIND_IN_SET( name , 'collect,newuser,gift') and is_display=1  order by orderid  asc  limit 3 ");
		$data["moduleshow"] = $moduleshow;
		$fourmoduleshow = $this->mysql->getarr("select* from " . Mysite::$app->config["tablepre"] . "appmudel where FIND_IN_SET( name , 'waimai,diancai,market,paotui') and is_display=1  order by orderid  asc  limit 4 ");
		$data["fourmoduleshow"] = $fourmoduleshow;
		$data["moretypelist"] = $moretypelist;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$addressname = ICookie::get("addressname");
		$lat = (empty($lat) ? 0 : $lat);
		$lng = (empty($lng) ? 0 : $lng);

		if (empty($addressname)) {
			$addressname = "";
		}

		$data["lat"] = $lat;
		$data["lng"] = $lng;
		$data["addressname"] = $addressname;
		$ztylist = $this->mysql->getarr("select* from " . Mysite::$app->config["tablepre"] . "specialpage where is_show=1  order by orderid  asc");
		$data["ztylist"] = $ztylist;
		Mysite::$app->setdata($data);

		if (Mysite::$app->config["mobilemodule"] == 2) {
			Mysite::$app->setAction("indexsy");
		}
		else {
			Mysite::$app->setAction("index");
		}
	}

	public function waimai()
	{
		$this->checkwxuser();
		ICookie::set("shopshowtype", "waimai", 2592000);
		$typeid = intval(IReq::get("typeid"));

		if (!empty($typeid)) {
			$link = IUrl::creatUrl("wxsite/shoplist/typeid/" . $typeid);
		}
		else {
			$link = IUrl::creatUrl("wxsite/shoplist/");
		}

		$this->message("", $link);
	}

	public function dingtai()
	{
		$this->checkwxuser();
		ICookie::set("shopshowtype", "dingtai", 2592000);

		if (!empty($typeid)) {
			$link = IUrl::creatUrl("wxsite/shoplist/typeid/" . $typeid);
		}
		else {
			$link = IUrl::creatUrl("wxsite/shoplist/");
		}

		$this->message("", $link);
	}

	public function marketlist()
	{
		$this->checkwxuser();
		ICookie::set("shopshowtype", "market", 2592000);
		$typeid = intval(IReq::get("typeid"));

		if (!empty($typeid)) {
			$link = IUrl::creatUrl("wxsite/shoplist/typeid/" . $typeid);
		}
		else {
			$link = IUrl::creatUrl("wxsite/shoplist/");
		}

		$this->message("", $link);
	}

	public function wmrtest()
	{
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		if (!in_array($shopshowtype, array("waimai", "market", "dingtai"))) {
			ICookie::set("shopshowtype", "waimai", 2592000);
			$shopshowtype = "waimai";
		}

		$areaid = IFilter::act(IReq::get("areaid"));

		if ($areaid <= 0) {
			ICookie::clear("myaddress");
		}

		$data["typeid"] = IFilter::act(IReq::get("typeid"));

		if ($shopshowtype == "market") {
			$templist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000");
			$data["caipin"] = array();

			if (!empty($templist)) {
				$data["caipin"] = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id='" . $templist["id"] . "'  ");
			}
		}
		else {
			$templist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000");
			$data["caipin"] = array();

			if (!empty($templist)) {
				$data["caipin"] = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id='" . $templist["id"] . "'  ");
			}
		}

		$data["shopshowtype"] = $shopshowtype;
		$shopsearch = IFilter::act(IReq::get("search_input"));
		$data["search_input"] = $shopsearch;
		$data["areaid"] = $areaid;
		Mysite::$app->setdata($data);
	}

	public function shoplist()
	{
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		if (!in_array($shopshowtype, array("waimai", "market", "dingtai"))) {
			ICookie::set("shopshowtype", "waimai", 2592000);
			$shopshowtype = "waimai";
		}

		$areaid = IFilter::act(IReq::get("areaid"));

		if ($areaid <= 0) {
			ICookie::clear("myaddress");
		}

		$data["typeid"] = IFilter::act(IReq::get("typeid"));

		if ($shopshowtype == "market") {
			$templist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000");
			$data["caipin"] = array();

			if (!empty($templist)) {
				$data["caipin"] = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id='" . $templist["id"] . "'  ");
			}
		}
		else {
			$templist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_search = 1 and type ='checkbox'  order by orderid asc limit 0,1000");
			$data["caipin"] = array();

			if (!empty($templist)) {
				$data["caipin"] = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id='" . $templist["id"] . "'  ");
			}
		}

		$data["shopshowtype"] = $shopshowtype;
		$shopsearch = IFilter::act(IReq::get("search_input"));
		$data["search_input"] = $shopsearch;
		$data["areaid"] = $areaid;
		Mysite::$app->setdata($data);
	}

	public function shoplistdata()
	{
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		if ($shopshowtype == "market") {
			$where = "";
			$shopsearch = IFilter::act(IReq::get("search_input"));
			$shopsearch = urldecode($shopsearch);

			if (!empty($shopsearch)) {
				$where = " and b.shopname like '%" . $shopsearch . "%' ";
			}

			$areaid = intval(IFilter::act(IReq::get("areaid")));
			$catid = intval(IReq::get("catid"));
			$order = intval(IReq::get("order"));
			$order = (in_array($order, array(1, 2, 3)) ? $order : 0);
			$qsjid = intval(IReq::get("qsjid"));
			$qsjid = (in_array($qsjid, array(1, 2, 3)) ? $qsjid : 0);
			$where2 = "";

			if (0 < $catid) {
				$where2 = "where sh.second_id = " . $catid;
			}

			$checkareaid = $areaid;

			if (0 < $checkareaid) {
				$where2 = (empty($where2) ? " where  ard.areaid = " . $checkareaid : $where2 . " and  ard.areaid = " . $checkareaid);
				$where = (empty($where2) ? $where : $where . " and b.id in(select ard.shopid from " . Mysite::$app->config["tablepre"] . "areashop as ard left join " . Mysite::$app->config["tablepre"] . "shopsearch  as sh  on ard.shopid = sh.shopid   " . $where2 . "  group by shopid  ) ");
			}
			else {
				$where = (empty($where2) ? $where : $where . " and b.id in(select sh.shopid from  " . Mysite::$app->config["tablepre"] . "shopsearch  as sh    " . $where2 . "  group by shopid  ) ");
			}

			$lng = 0;
			$lat = 0;

			if (0 < $checkareaid) {
				$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $checkareaid . "  ");

				if (!empty($areainfo)) {
					$lng = $areainfo["lng"];
					$lat = $areainfo["lat"];
				}
			}
			else {
				$lng = ICookie::get("lng");
				$lat = ICookie::get("lat");
				$lng = (empty($lng) ? 0 : $lng);
				$lat = (empty($lat) ? 0 : $lat);
			}

			$lng = trim($lng);
			$lat = trim($lat);
			$lng = (empty($lng) ? 0 : $lng);
			$lat = (empty($lat) ? 0 : $lat);
			$orderarray = array("  sort ASC       ", " (`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " ) ASC  ", " limitcost asc     ", " is_com desc ");
			$qsjarray = array("  ", " and limitcost < 5  ", " and limitcost >= 5 and limitcost <= 10 ", " and limitcost > 10 ");
			$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0    order by orderid asc limit 0,1000");
			$attra["input"] = 0;
			$attra["img"] = 0;
			$attra["checkbox"] = 0;

			foreach ($templist as $key => $value ) {
				if ($value["type"] == "input") {
					$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $value["id"]);
				}
				else if ($value["type"] == "img") {
					$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $value["id"]);
				}
				else if ($value["type"] == "checkbox") {
					$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $value["id"]);
				}
			}

			$pageinfo = new page();
			$pageinfo->setpage(intval(IReq::get("page")));
			$where .= $qsjarray[$qsjid];
			$where .= $qsjarray[$qsjid];
			$where .= " and shoptype=1 ";
			$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where  b.is_pass = 1  " . $where . "    order by " . $orderarray[$order] . " limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
			$nowhour = date("H:i:s", time());
			$nowhour = strtotime($nowhour);
			$templist = array();
			$cxclass = new sellrule();

			if (is_array($list)) {
				foreach ($list as $keys => $values ) {
					if (0 < $values["id"]) {
						$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
						$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
						$values["opentype"] = $checkinfo["opentype"];
						$values["newstartime"] = $checkinfo["newstartime"];
						$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 1 and shopid = " . $values["id"] . "");
						$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
						$checkps = $this->pscost($values, 1);
						$values["pscost"] = $checkps["pscost"];
						$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
						$tempmi = $mi;
						$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
						$values["juli"] = $mi;
						$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
						$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
						$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

						if (empty($shopcounts["shuliang"])) {
							$values["ordercount"] = 0;
						}
						else {
							$values["ordercount"] = $shopcounts["shuliang"];
						}

						$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
						$values["cxlist"] = array();

						foreach ($cxinfo as $k1 => $v1 ) {
							if (isset($cxarray[$v1["signid"]])) {
								$v1["imgurl"] = $cxarray[$v1["signid"]];
								$values["cxlist"][] = $v1;
							}
						}

						$zongpoint = $values["point"];
						$zongpointcount = $values["pointcount"];

						if ($zongpointcount != 0) {
							$shopstart = intval(round($zongpoint / $zongpointcount));
						}
						else {
							$shopstart = 0;
						}

						$values["point"] = $shopstart;
						$values["attrdet"] = array();

						foreach ($attrdet as $k => $v ) {
							if ($v["firstattr"] == $attra["input"]) {
								$values["attrdet"]["input"] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["img"]) {
								$values["attrdet"]["img"][] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["checkbox"]) {
								$values["attrdet"]["checkbox"][] = $v["value"];
							}
						}

						$templist[] = $values;
					}
				}
			}

			$data = $templist;
		}
		else {
			$where = "";
			$shopsearch = IFilter::act(IReq::get("search_input"));
			$shopsearch = urldecode($shopsearch);

			if (!empty($shopsearch)) {
				$where = " and b.shopname like '%" . $shopsearch . "%' ";
			}

			$areaid = intval(IFilter::act(IReq::get("areaid")));
			$catid = intval(IReq::get("catid"));
			$order = intval(IReq::get("order"));
			$order = (in_array($order, array(1, 2, 3)) ? $order : 0);
			$qsjid = intval(IReq::get("qsjid"));
			$qsjid = (in_array($qsjid, array(1, 2, 3)) ? $qsjid : 0);
			$where2 = "";

			if (0 < $catid) {
				$where2 = "where sh.second_id = " . $catid;
			}

			$checkareaid = $areaid;

			if (0 < $checkareaid) {
				$where2 = (empty($where2) ? " where  ard.areaid = " . $checkareaid : $where2 . " and  ard.areaid = " . $checkareaid);
				$where = (empty($where2) ? $where : $where . " and b.id in(select ard.shopid from " . Mysite::$app->config["tablepre"] . "areashop as ard left join " . Mysite::$app->config["tablepre"] . "shopsearch  as sh  on ard.shopid = sh.shopid   " . $where2 . "  group by shopid  ) ");
			}
			else {
				$where = (empty($where2) ? $where : $where . " and b.id in(select sh.shopid from  " . Mysite::$app->config["tablepre"] . "shopsearch  as sh    " . $where2 . "  group by shopid  ) ");
			}

			$lng = 0;
			$lat = 0;

			if (0 < $checkareaid) {
				$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $checkareaid . "  ");

				if (!empty($areainfo)) {
					$lng = $areainfo["lng"];
					$lat = $areainfo["lat"];
				}
			}
			else {
				$lng = ICookie::get("lng");
				$lat = ICookie::get("lat");
				$lng = (empty($lng) ? 0 : $lng);
				$lat = (empty($lat) ? 0 : $lat);
			}

			$lng = trim($lng);
			$lat = trim($lat);
			$lng = (empty($lng) ? 0 : $lng);
			$lat = (empty($lat) ? 0 : $lat);
			$orderarray = array("  sort ASC       ", " (`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " ) ASC   ", " limitcost asc ", " is_com desc ");
			$qsjarray = array("  ", " and limitcost < 5 ", " and limitcost >= 5 and limitcost <= 10 ", " and limitcost > 10 ");
			$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
			$attra["input"] = 0;
			$attra["img"] = 0;
			$attra["checkbox"] = 0;

			foreach ($templist as $key => $value ) {
				if ($value["type"] == "input") {
					$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $value["id"]);
				}
				else if ($value["type"] == "img") {
					$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $value["id"]);
				}
				else if ($value["type"] == "checkbox") {
					$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $value["id"]);
				}
			}

			$pageinfo = new page();
			$pageinfo->setpage(intval(IReq::get("page")));
			$where .= $qsjarray[$qsjid];
			$tempwhere = ($shopshowtype == "dingtai" ? " and is_goshop =1 " : " and is_waimai =1 ");
			$where .= " and shoptype =  0  ";
			$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where  b.is_pass = 1  " . $tempwhere . " " . $where . "    order by " . $orderarray[$order] . " limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
			$nowhour = date("H:i:s", time());
			$nowhour = strtotime($nowhour);
			$templist = array();
			$cxclass = new sellrule();

			if (is_array($list)) {
				foreach ($list as $keys => $values ) {
					if (0 < $values["id"]) {
						$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
						$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
						$values["opentype"] = $checkinfo["opentype"];
						$values["newstartime"] = $checkinfo["newstartime"];
						$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = " . $values["id"] . "");
						$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
						$checkps = $this->pscost($values, 1, $areaid);

						if ($shopshowtype == "dingtai") {
							$values["pscost"] = 0;
						}
						else {
							$values["pscost"] = $checkps["pscost"];
						}

						$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
						$tempmi = $mi;
						$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
						$values["juli"] = $mi;
						$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
						$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
						$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

						if (empty($shopcounts["shuliang"])) {
							$values["ordercount"] = 0;
						}
						else {
							$values["ordercount"] = $shopcounts["shuliang"];
						}

						$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
						$values["cxlist"] = array();

						foreach ($cxinfo as $k1 => $v1 ) {
							if (isset($cxarray[$v1["signid"]])) {
								$v1["imgurl"] = $cxarray[$v1["signid"]];
								$values["cxlist"][] = $v1;
							}
						}

						$zongpoint = $values["point"];
						$zongpointcount = $values["pointcount"];

						if ($zongpointcount != 0) {
							$shopstart = intval(round($zongpoint / $zongpointcount));
						}
						else {
							$shopstart = 0;
						}

						$values["point"] = $shopstart;
						$values["attrdet"] = array();

						foreach ($attrdet as $k => $v ) {
							if ($v["firstattr"] == $attra["input"]) {
								$values["attrdet"]["input"] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["img"]) {
								$values["attrdet"]["img"][] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["checkbox"]) {
								$values["attrdet"]["checkbox"][] = $v["value"];
							}
						}

						$templist[] = $values;
					}
				}
			}

			$data = $templist;
		}

		$datas = json_encode($data);
		echo "showmoreshop(" . $datas . ")";
		exit();
		$this->success($data);
	}

	public function indexshoplistdata()
	{
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$where = "";
		$lng = 0;
		$lat = 0;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$lng = trim($lng);
		$lat = trim($lat);
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$orderarray = array("   sort asc      ");
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$where .= $qsjarray[$qsjid];
		$where .= $qsjarray[$qsjid];
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 " . $where . "    order by " . $orderarray[0] . " limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "  ");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();

		if (is_array($list)) {
			foreach ($list as $keys => $values ) {
				if (0 < $values["id"]) {
					$templist111 = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where cattype = " . $values["shoptype"] . " and  parent_id = 0    order by orderid asc limit 0,1000");
					$attra = array();
					$attra["input"] = 0;
					$attra["img"] = 0;
					$attra["checkbox"] = 0;

					foreach ($templist111 as $key => $vall ) {
						if ($vall["type"] == "input") {
							$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $vall["id"]);
						}
						else if ($vall["type"] == "img") {
							$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $vall["id"]);
						}
						else if ($vall["type"] == "checkbox") {
							$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $vall["id"]);
						}
					}

					if ($values["shoptype"] == 1) {
						$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $values["id"] . "   ");
					}
					else {
						$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $values["id"] . "   ");
					}

					if (!empty($shopdet)) {
						$values = array_merge($values, $shopdet);
						$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
						$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
						$values["opentype"] = $checkinfo["opentype"];
						$values["newstartime"] = $checkinfo["newstartime"];
						$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $values["shoptype"] . " and shopid = " . $values["id"] . "");
						$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
						$checkps = $this->pscost($values, 1, $areaid);
						$values["pscost"] = $checkps["pscost"];
						$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
						$tempmi = $mi;
						$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
						$values["juli"] = $mi;
						$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
						$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
						$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

						if (empty($shopcounts["shuliang"])) {
							$values["ordercount"] = 0;
						}
						else {
							$values["ordercount"] = $shopcounts["shuliang"];
						}

						$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
						$values["cxlist"] = array();

						foreach ($cxinfo as $k1 => $v1 ) {
							if (isset($cxarray[$v1["signid"]])) {
								$v1["imgurl"] = $cxarray[$v1["signid"]];
								$values["cxlist"][] = $v1;
							}
						}

						$zongpoint = $values["point"];
						$zongpointcount = $values["pointcount"];

						if ($zongpointcount != 0) {
							$shopstart = intval(round($zongpoint / $zongpointcount));
						}
						else {
							$shopstart = 0;
						}

						$values["point"] = $shopstart;
						$values["attrdet"] = array();

						foreach ($attrdet as $k => $v ) {
							if ($v["firstattr"] == $attra["input"]) {
								$values["attrdet"]["input"] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["img"]) {
								$values["attrdet"]["img"][] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["checkbox"]) {
								$values["attrdet"]["checkbox"][] = $v["value"];
							}
						}

						$templist[] = $values;
					}
				}
			}
		}

		$data = $templist;
		$datas = json_encode($data);
		echo "showmoreshop(" . $datas . ")";
		exit();
		$this->success($data);
	}

	public function shopshow()
	{
		$areaid = ICookie::get("myaddress");
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		if (!empty($typelx)) {
			$data["typelx"] = $typelx;
		}
		else {
			if ($shopshowtype == "waimai") {
				$data["typelx"] = "wm";
			}

			if ($shopshowtype == "market") {
				$data["typelx"] = "mk";
			}

			if ($shopshowtype == "dingtai") {
				$data["typelx"] = "yd";
			}
		}

		$weekji = date("w");

		if ($shopshowtype == "market") {
			$id = intval(IReq::get("id"));
			$data["id"] = $id;
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $id . "' ");

			if (empty($shopinfo)) {
				$link = IUrl::creatUrl("wxsite/index");
				$this->message("", $link);
			}

			$shopdet = array();
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid='" . $id . "' ");
			$nowhour = date("H:i:s", time());
			$data["openinfo"] = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopdet["is_orderbefore"], $nowhour);
			$data["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $shopinfo["id"] . "' ");
			$data["shopinfo"] = $shopinfo;
			$data["shopdet"] = $shopdet;
			$goodstype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = " . $shopinfo["id"] . "   and parent_id = 0 order by orderid asc");
			$data["goodstype"] = array();
			$tempids = array();

			foreach ($goodstype as $key => $value ) {
				$soncate = array();
				$soncatearray = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid = " . $id . "   and parent_id = " . $value["id"] . "  order by orderid asc");

				foreach ($soncatearray as $key1 => $val ) {
					$val["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $id . "' and    FIND_IN_SET( " . $weekji . " , `weeks` )   and typeid =" . $val["id"] . " order by good_order asc ");
					$tempids[] = $val["id"];
					$soncate[] = $val;
				}

				$value["tempids"] = implode(",", $tempids);
				$value["soncate"] = $soncate;
				$data["goodstype"][] = $value;
			}

			$shopdet["id"] = $id;
			$shopdet["shoptype"] = 1;
			$newshoparray = array_merge($shopinfo, $shopdet);
			$tempinfo = $this->pscost($newshoparray, 1, $areaid);
			$backdata["pstype"] = $tempinfo["pstype"];
			$backdata["pscost"] = $tempinfo["pscost"];
			$data["psinfo"] = $backdata;
			$data["mainattr"] = array();
			$data["shopattr"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr  where  cattype = " . $shopinfo["shoptype"] . " and shopid = '" . $shopinfo["id"] . "'  order by firstattr asc limit 0,1000");
		}
		else {
			$id = intval(IReq::get("id"));
			$data["id"] = $id;
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $id . "' ");

			if (empty($shopinfo)) {
				$link = IUrl::creatUrl("wxsite/shoplist");
				$this->message("", $link);
			}

			$shopdet = array();

			if (empty($shopinfo["shoptype"])) {
				$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid='" . $id . "' ");
			}
			else if ($shopinfo["shoptype"] == 1) {
				$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid='" . $id . "' ");
			}

			$nowhour = date("H:i:s", time());
			$data["openinfo"] = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopdet["is_orderbefore"], $nowhour);
			$data["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $shopinfo["id"] . "' ");
			$data["shopinfo"] = $shopinfo;
			$data["shopdet"] = $shopdet;
			$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype  where shopid='" . $id . "' order by orderid asc  ");
			$data["goodstype"] = array();
			$wheretype = "";

			if ($shopshowtype == "dingtai") {
				$wheretype = "and is_dingtai = 1 and    FIND_IN_SET( " . $weekji . " , `weeks` )    ";
			}
			else {
				$wheretype = "and is_waisong = 1 and    FIND_IN_SET( " . $weekji . " , `weeks` )    ";
			}

			foreach ($templist as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $id . "'  " . $wheretype . "  and typeid =" . $value["id"] . "  order by good_order asc");
				$data["goodstype"][] = $value;
			}

			$shopdet["id"] = $id;
			$shopdet["shoptype"] = 1;
			$newshoparray = array_merge($shopinfo, $shopdet);
			$tempinfo = $this->pscost($newshoparray, 1, $areaid);
			$backdata["pstype"] = $tempinfo["pstype"];

			if ($shopshowtype == "dingtai") {
				$backdata["pscost"] = 0;
			}
			else {
				$backdata["pscost"] = $tempinfo["pscost"];
			}

			$data["psinfo"] = $backdata;
			$data["mainattr"] = array();
			$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $shopinfo["shoptype"] . " and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

			foreach ($templist as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
				$data["mainattr"][] = $value;
			}

			$data["shopattr"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr  where  cattype = " . $shopinfo["shoptype"] . " and shopid = '" . $shopinfo["id"] . "'  order by firstattr asc limit 0,1000");
		}

		$data["shopshowtype"] = $shopshowtype;
		$data["weekji"] = $weekji;
		Mysite::$app->setdata($data);

		if ($shopinfo["shoptype"] == 1) {
			Mysite::$app->setAction("mkshopshow");
		}
		else {
			Mysite::$app->setAction("shopshow");
		}
	}

	public function foodshow()
	{
		$shopshowtype = ICookie::get("shopshowtype");
		$data["shopshowtype"] = $shopshowtype;
		$id = intval(IReq::get("id"));
		$foodshow = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "goods where id= " . $id . "  ");
		$shopid = $foodshow["shopid"];
		$data["shopinfo"] = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "shop where id= " . $shopid . "  ");

		if ($shopshowtype == "market") {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}
		else {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}

		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($data["shopinfo"]["is_open"], $data["shopinfo"]["starttime"], $shopdet["is_orderbefore"], $nowhour);
		$data["opentype"] = $checkinfo["opentype"];
		$data["pointcount"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "comment   where shopid = " . $shopid . " and  goodsid  = " . $id . "   ");

		if ($foodshow["is_cx"] == 1) {
			$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $foodshow["id"] . "  ");
			$newdata = getgoodscx($foodshow["cost"], $cxdata);
			$foodshow["zhekou"] = $newdata["zhekou"];
			$foodshow["is_cx"] = $newdata["is_cx"];
			$foodshow["cost"] = $newdata["cost"];
		}

		$data["shopdet"] = $shopdet;
		$data["foodshow"] = $foodshow;
		$newshoparray = array_merge($data["shopinfo"], $shopdet);
		$tempinfo = $this->pscost($newshoparray, 1, $areaid);
		$backdata["pstype"] = $tempinfo["pstype"];
		$backdata["pscost"] = $tempinfo["pscost"];
		$data["psinfo"] = $backdata;
		$data["productinfo"] = (!empty($foodshow) ? unserialize($foodshow["product_attr"]) : array());
		$smardb = new newsmcart();
		$smardb->setdb($this->mysql)->SetShopId($shopid);
		$data["nowselect"] = array();

		if ($foodshow["have_det"] == 0) {
			$tempinfo = $smardb->SetGoodsType(1)->productone($id);
			$data["carnum"] = $tempinfo;
		}
		else {
			$nowselect = $smardb->FindInproduct($id);
			$data["nowselect"] = $nowselect;
			$data["carnum"] = $nowselect["count"];
		}

		$data["attrids"] = array();

		if (!empty($nowselect)) {
			$data["attrids"] = explode(",", $nowselect["attrids"]);
		}

		$productlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "product where goodsid=" . $id . " and shopid=" . $shopid . "");
		$data["productlist"] = $productlist;
		$temparray = $this->mysql->getarr("select * from  " . Mysite::$app->config["tablepre"] . "comment where goodsid=" . $id . " and shopid=" . $shopid . " order by addtime desc limit 10 ");
		$commentlist = array();

		foreach ($temparray as $key => $value ) {
			$memberinfo = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "member where uid = " . $value["uid"] . " ");
			$value["username"] = $memberinfo["username"];
			$commentlist[] = $value;
		}

		$data["commentlist"] = $commentlist;
		$shuliang = $this->mysql->select_one("select count(id) as zongshu , sum(point) as pointzongshu from  " . Mysite::$app->config["tablepre"] . "comment where goodsid=" . $id . " and shopid=" . $shopid . " order by addtime desc  ");
		$zongshu = $shuliang["zongshu"];
		$pointzongshu = $shuliang["pointzongshu"];

		if ($pointzongshu != 0) {
			$haoping = round($zongshu / $pointzongshu, 3) * 100;
		}
		else {
			$haoping = 0;
		}

		$data["haoping"] = $haoping;
		Mysite::$app->setdata($data);
	}

	public function getdetailinfo()
	{
		$typelx = IFilter::act(IReq::get("typelx"));
		$data["typelx"] = $typelx;
		$shopid = IFilter::act(IReq::get("shopid"));
		$shopinfo = $this->mysql->select_one("select *  from  " . Mysite::$app->config["tablepre"] . "shop  where id = " . $shopid . " ");

		if (empty($shopinfo)) {
			$this->message("获取店铺数据失败");
		}

		$data["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $shopinfo["id"] . "' ");
		$shopshowtype = $shopinfo["shoptype"];

		if ($shopshowtype == 1) {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}
		else {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}

		$zongpoint = $shopinfo["point"];
		$zongpointcount = $shopinfo["pointcount"];

		if ($zongpointcount != 0) {
			$shopstart = intval(round($zongpoint / $zongpointcount));
		}
		else {
			$shopstart = 0;
		}

		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $shopinfo["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
		$cxlist = array();

		foreach ($cxinfo as $k1 => $v1 ) {
			if (isset($cxarray[$v1["signid"]])) {
				$v1["imgurl"] = $cxarray[$v1["signid"]];
				$cxlist[] = $v1;
			}
		}

		$data["cxlist"] = $cxlist;
		$areaid = ICookie::get("myaddress");
		$newshoparray = array_merge($shopinfo, $shopdet);
		$tempinfo = $this->pscost($newshoparray, 1, $areaid);
		$backdata["pstype"] = $tempinfo["pstype"];
		$backdata["pscost"] = $tempinfo["pscost"];
		$data["psinfo"] = $backdata;
		$data["shopstart"] = $shopstart;
		$data["shopinfo"] = $shopinfo;
		$data["shopdet"] = $shopdet;
		Mysite::$app->setdata($data);
	}

	public function getshopcomment()
	{
		$typelx = IFilter::act(IReq::get("typelx"));
		$data["typelx"] = $typelx;
		$shopid = IFilter::act(IReq::get("shopid"));
		$shopinfo = $this->mysql->select_one("select *  from  " . Mysite::$app->config["tablepre"] . "shop  where id = " . $shopid . " ");
		$data["shopinfo"] = $shopinfo;
		$shopshowtype = $shopinfo["shoptype"];

		if ($shopshowtype == 1) {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}
		else {
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where a.shopid = " . $shopid . "   ");
		}

		$data["shopdet"] = $shopdet;

		if (empty($shopinfo)) {
			$this->message("获取店铺数据失败");
		}

		$data["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $shopinfo["id"] . "' ");

		if ($shopinfo["pointcount"] != 0) {
			$zongtistart = round($shopinfo["point"] / $shopinfo["pointcount"]);
			$zonghefen = round($shopinfo["point"] / $shopinfo["pointcount"], 1);
		}
		else {
			$zongtistart = 0;
			$zonghefen = 0;
		}

		if ($shopinfo["pointcount"] != 0) {
			$psfuwustart = round($shopinfo["psservicepoint"] / $shopinfo["psservicepointcount"]);
		}
		else {
			$psfuwustart = 0;
		}

		$data["zonghefen"] = $zonghefen;
		$data["zongtistart"] = $zongtistart;
		$data["psfuwustart"] = $psfuwustart;
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 5);
		$temparray = $this->mysql->getarr("select * from  " . Mysite::$app->config["tablepre"] . "comment where  shopid=" . $shopid . " order by addtime desc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
		$commentlist = array();

		foreach ($temparray as $key => $value ) {
			$memberinfo = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "member where uid = " . $value["uid"] . " ");
			$goodinfo = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "goods where id = " . $value["goodsid"] . " ");
			$value["username"] = $memberinfo["username"];
			$value["userlogo"] = $memberinfo["logo"];
			$value["goodname"] = $goodinfo["name"];
			$value["goodpoint"] = $goodinfo["point"];
			$commentlist[] = $value;
		}

		$data["commentlist"] = $commentlist;
		$shuliang = $this->mysql->select_one("select count(id) as zongshu , sum(point) as pointzongshu from  " . Mysite::$app->config["tablepre"] . "comment where   shopid=" . $shopid . " order by addtime desc  ");
		$zongshu = $shuliang["zongshu"];
		$pointzongshu = $shuliang["pointzongshu"];

		if ($pointzongshu != 0) {
			$haoping = round($zongshu / $pointzongshu, 3) * 100;
		}
		else {
			$haoping = 0;
		}

		$data["haoping"] = $haoping;
		Mysite::$app->setdata($data);
	}

	public function getshopmorecomment()
	{
		$typelx = IFilter::act(IReq::get("typelx"));
		$data["typelx"] = $typelx;
		$shopid = IFilter::act(IReq::get("shopid"));
		$goodid = IFilter::act(IReq::get("goodid"));
		$shopinfo = $this->mysql->select_one("select *  from  " . Mysite::$app->config["tablepre"] . "shop  where id = " . $shopid . " ");
		$data["shopinfo"] = $shopinfo;
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 5);

		if (!empty($goodid)) {
			$temparray = $this->mysql->getarr("select * from  " . Mysite::$app->config["tablepre"] . "comment where  shopid=" . $shopid . " and goodsid = " . $goodid . " order by addtime desc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
		}
		else {
			$temparray = $this->mysql->getarr("select * from  " . Mysite::$app->config["tablepre"] . "comment where  shopid=" . $shopid . " order by addtime desc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
		}

		$commentlist = array();

		foreach ($temparray as $key => $value ) {
			$memberinfo = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "member where uid = " . $value["uid"] . " ");
			$goodinfo = $this->mysql->select_one("select * from  " . Mysite::$app->config["tablepre"] . "goods where id = " . $value["goodsid"] . " ");
			$value["username"] = $memberinfo["username"];
			$value["userlogo"] = $memberinfo["logo"];
			$value["goodname"] = $goodinfo["name"];
			$value["goodpoint"] = $goodinfo["point"];
			$value["addtime"] = date("Y-m-d H:i", $value["addtime"]);
			$value["huifutime"] = date("Y-m-d H:i", $value["replytime"]);
			$commentlist[] = $value;
		}

		$data["commentlist"] = $commentlist;
		$datas = json_encode($data);
		echo "showmoreorder(" . $datas . ")";
		exit();
		Mysite::$app->setdata($data);
	}

	public function gowei()
	{
		$id = IFilter::act(IReq::get("id"));
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid='" . $this->member["uid"] . "' and endtime > " . time() . " and status = 1   ");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");

		if (empty($shopinfo)) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");
		}

		$nowtime = time();
		$timelist = array();
		$info = explode("|", $shopinfo["starttime"]);
		$info = (is_array($info) ? $info : array($info));
		$data["is_open"] = 0;
		if (($shopinfo["is_open"] == 0) || ($shopinfo["is_pass"] == 0)) {
			$data["is_open"] = 0;
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($shopinfo["postdate"]) ? unserialize($shopinfo["postdate"]) : array());
		$data["timelist"] = array();
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
					$tempt["s"] = $tempt["d"] . " " . $tempt["s"];
					$tempt["i"] = $value["i"];
					$data["timelist"][] = $tempt;
				}
			}

			$nowwhiltcheck = $nowwhiltcheck + 1;
		}

		$data["lat"] = ICookie::get("lat");
		$data["lng"] = ICookie::get("lng");
		$data["deaddress"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where userid = " . $this->member["uid"] . " and `default`=1   ");
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function goweishop()
	{
		$id = IFilter::act(IReq::get("id"));
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid='" . $this->member["uid"] . "' and endtime > " . time() . " and status = 1   ");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");

		if (empty($shopinfo)) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");
		}

		$nowtime = time();
		$timelist = array();
		$info = explode("|", $shopinfo["starttime"]);
		$info = (is_array($info) ? $info : array($info));
		$data["is_open"] = 0;
		if (($shopinfo["is_open"] == 0) || ($shopinfo["is_pass"] == 0)) {
			$data["is_open"] = 0;
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($shopinfo["postdate"]) ? unserialize($shopinfo["postdate"]) : array());
		$data["timelist"] = array();
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
					$tempt["s"] = $tempt["d"] . " " . $tempt["s"];
					$tempt["i"] = $value["i"];
					$data["timelist"][] = $tempt;
				}
			}

			$nowwhiltcheck = $nowwhiltcheck + 1;
		}

		$data["lat"] = ICookie::get("lat");
		$data["lng"] = ICookie::get("lng");
		$data["deaddress"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where userid = " . $this->member["uid"] . " and `default`=1   ");
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function shopgoodslist()
	{
		$id = IFilter::act(IReq::get("id"));
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $id . "' ");
		$data["shopinfo"] = $shopinfo;

		if (empty($shopinfo)) {
			$link = IUrl::creatUrl("wxsite/shoplist");
			$this->message("", $link);
		}

		$data["goodstype"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid='" . $id . "' ");
		$data["goodslist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $id . "' ");
		Mysite::$app->setdata($data);
	}

	public function shopcart()
	{
		$id = IFilter::act(IReq::get("id"));
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid='" . $this->member["uid"] . "' and endtime > " . time() . " and status = 1   ");
		$data["wxjuanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "wxuserjuan  where uid='" . $this->member["uid"] . "' and endtime > " . time() . " and lqstatus = 1 and status = 0   ");
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");

		if (empty($shopinfo)) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where shopid = " . $id . "   ");
		}

		$nowtime = time();
		$timelist = array();
		$info = explode("|", $shopinfo["starttime"]);
		$info = (is_array($info) ? $info : array($info));
		$data["is_open"] = 0;
		if (($shopinfo["is_open"] == 0) || ($shopinfo["is_pass"] == 0)) {
			$data["is_open"] = 0;
		}

		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($shopinfo["postdate"]) ? unserialize($shopinfo["postdate"]) : array());
		$data["timelist"] = array();
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
					$tempt["s"] = $tempt["d"] . " " . $tempt["s"];
					$tempt["i"] = $value["i"];
					$data["timelist"][] = $tempt;
				}
			}

			$nowwhiltcheck = $nowwhiltcheck + 1;
		}

		if (empty($this->member["uid"]) || ($this->member["uid"] == 0)) {
			$data["deaddress"] = array();
			$cdata["id"] = 0;
			$cdata["default"] = 1;
			$cdata["contactname"] = ICookie::get("wxguest_username");
			$cdata["phone"] = ICookie::get("wxguest_phone");
			$cdata["address"] = ICookie::get("wxguest_address");

			if (empty($cdata["contactname"])) {
				$data["deaddress"] = array();
			}
			else {
				$data["deaddress"] = $cdata;
			}
		}
		else {
			$data["deaddress"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where userid = " . $this->member["uid"] . " and `default`=1   ");
		}

		$data["lat"] = ICookie::get("lat");
		$data["lng"] = ICookie::get("lng");
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function getjuan()
	{
		$this->checkwxuser();
		$id = intval(IReq::get("id"));
		$wxuserjuan = $this->mysql->select_one("select a.*,b.cartdesrc from " . Mysite::$app->config["tablepre"] . "wxuserjuan as a left join " . Mysite::$app->config["tablepre"] . "wxjuan as b on a.juanid = b.id  where a.id='" . $id . "' and a.uid='" . $this->member["uid"] . "'  ");

		if (empty($wxuserjuan)) {
			$this->message("获取用户失败！");
		}

		$data["wxuserjuan"] = $wxuserjuan;
		Mysite::$app->setdata($data);
	}

	public function wxgetjuan()
	{
		$this->checkwxuser();
		$id = intval(IReq::get("id"));
		$wxuserjuan = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "wxuserjuan where id='" . $id . "'");

		if (empty($wxuserjuan)) {
			$this->message("获取优惠卷失败！");
		}

		$data["lqstatus"] = 1;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "wxuserjuan", $data, "id='" . $id . "'");
		$this->success("success");
	}

	public function member()
	{
		$this->checkwxweb();
		$this->checkwxuser();
		$wxuser = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "wxuser where uid='" . $this->member["uid"] . "'");
		$data["wxuserbangd"] = $wxuser["is_bang"];
		$data["juanshu"] = $this->mysql->counts("select *  from " . Mysite::$app->config["tablepre"] . "juan where uid='" . $this->member["uid"] . "'  and status = 1 order by id asc limit 0,50");
		$data["wxjuanshu"] = $this->mysql->counts("select *  from " . Mysite::$app->config["tablepre"] . "wxuserjuan where uid='" . $this->member["uid"] . "'  and lqstatus = 1 order by id asc limit 0,50");
		Mysite::$app->setdata($data);
	}

	public function bangdmem()
	{
		$wxuser = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "wxuser where uid='" . $this->member["uid"] . "'");
		$link = IUrl::creatUrl("wxsite/member");

		if (empty($wxuser)) {
			$this->message("未关注我们，不可绑定帐号", $link);
		}

		if ($wxuser["is_bang"] == 1) {
			$link = IUrl::creatUrl("wxsite/member");
			$this->message("", $link);
		}
	}

	public function wxbangduser()
	{
		$wxbanguser = trim(IReq::get("wxbanguser"));
		$wxbangpsw = trim(IReq::get("wxbangpsw"));
		$wxuser = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "wxuser where uid='" . $this->member["uid"] . "'");

		if (empty($wxuser)) {
			$this->message("未关注我们，不可绑定帐号");
		}

		if ($wxuser["is_bang"] == 1) {
			$this->message("已绑订帐号不可重复绑定");
		}

		if (empty($wxbanguser)) {
			$this->message("绑定帐号失败,帐号为空");
		}

		if (empty($wxbangpsw)) {
			$this->message("绑定帐号失败,密码为空");
		}

		$info = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where (email='" . $wxbanguser . "' or username='" . $wxbanguser . "') ");

		if (empty($info)) {
			$this->message("绑定帐号失败,帐号未查找到");
		}

		if (!empty($info["is_bang"])) {
			$this->message("帐号已绑定其他帐号");
		}

		if ($info["password"] != md5($wxbangpsw)) {
			$this->message("帐号绑订失败,密码错误");
		}

		$data["uid"] = $info["uid"];
		$data["is_bang"] = 1;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "wxuser", $data, "uid='" . $this->member["uid"] . "'");
		$temuser = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $wxuser["uid"] . "' ");
		$all["score"] = $temuser["score"] + $info["score"];
		$all["cost"] = $temuser["cost"] + $info["cost"];
		$all["is_bang"] = 1;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $all, "uid='" . $info["uid"] . "'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "member", "uid ='" . $wxuser["uid"] . "'");
		$this->success("绑定帐号成功");
	}

	public function paycart()
	{
	}

	public function payonline()
	{
		$this->checkmemberlogin();
		$paytype = "alimobile";
		$cost = intval(IReq::get("cost"));

		if ($cost < 10) {
			$this->message("card_limit");
		}

		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[] = $value["loginname"];
			}
		}

		if (!in_array($paytype, $paytypelist)) {
			$this->message("other_nodefinepay");
		}

		$paydir = hopedir . "/plug/pay/" . $paytype;

		if (!file_exists($paydir . "/pay.php")) {
			$this->message("other_notinstallapi");
		}

		$dopaydata = array("type" => "acount", "upid" => $this->member["uid"], "cost" => $cost);
		include_once $paydir . "/pay.php";
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
			$this->message("充值卡不存在,请在核对下");
		}

		$arr["uid"] = $this->member["uid"];
		$arr["status"] = 1;
		$arr["username"] = $this->member["username"];
		$this->mysql->update(Mysite::$app->config["tablepre"] . "card", $arr, "card ='" . $card . "'  and card_password = '" . $password . "' and uid =0 and status = 0");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", "`cost`=`cost`+" . $checkinfo["cost"], "uid ='" . $this->member["uid"] . "' ");
		$allcost = $this->member["cost"] + $checkinfo["cost"];
		$this->memberCls->addlog($this->member["uid"], 2, 1, $checkinfo["cost"], "充值卡充值", "使用充值卡" . $checkinfo["card"] . "充值" . $checkinfo["cost"] . "元", $allcost);
		$this->memberCls->addmemcostlog($this->member["uid"], $this->member["username"], $this->member["cost"], 1, $checkinfo["cost"], $allcost, $this->member["username"] . "使用充值卡充值", $this->member["uid"], $this->member["username"]);
		$this->success("success");
	}

	public function memcard()
	{
		$this->checkwxuser();
		$link = IUrl::creatUrl("wxsite/shoplist");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}

		$tarelist = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "'   order by id asc limit 0,50");
		$arelist = array();
		$areaid = array();

		foreach ($tarelist as $key => $value ) {
			$areaid[] = $value["areaid1"];
			$areaid[] = $value["areaid3"];
			$areaid[] = $value["areaid2"];
		}

		if (0 < count($areaid)) {
			$areaarr = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "area  where id in(" . join(",", $areaid) . ")  order by id asc limit 0,1000");

			foreach ($areaarr as $key => $value ) {
				$arelist[$value["id"]] = $value["name"];
			}
		}

		$data["arealist"] = $tarelist;
		$data["areaarr"] = $arelist;
		Mysite::$app->setdata($data);
	}

	public function address()
	{
		$this->checkwxuser();
		$link = IUrl::creatUrl("wxsite/shoplist");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}

		$tarelist = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "'   order by id asc limit 0,50");
		$arelist = array();
		$areaid = array();

		foreach ($tarelist as $key => $value ) {
			$areaid[] = $value["areaid1"];
			$areaid[] = $value["areaid3"];
			$areaid[] = $value["areaid2"];
		}

		if (0 < count($areaid)) {
			$areaarr = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "area  where id in(" . join(",", $areaid) . ")  order by id asc limit 0,1000");

			foreach ($areaarr as $key => $value ) {
				$arelist[$value["id"]] = $value["name"];
			}
		}

		$data["arealist"] = $tarelist;
		$data["areaarr"] = $arelist;
		Mysite::$app->setdata($data);
	}

	public function editaddress()
	{
		$link = IUrl::creatUrl("wxsite/index");
		$data["backtype"] = IFilter::act(IReq::get("backtype"));
		$data["shopid"] = IFilter::act(IReq::get("shopid"));
		$data["lat"] = ICookie::get("lat");
		$data["lng"] = ICookie::get("lng");
		Mysite::$app->setdata($data);

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}
	}

	public function bkaddress()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}

		$data["shopid"] = IFilter::act(IReq::get("shopid"));
		$data["backtype"] = IFilter::act(IReq::get("backtype"));
		$tarelist = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "'   order by id asc limit 0,50");
		$arelist = array();
		$data["arealist"] = $tarelist;
		Mysite::$app->setdata($data);
	}

	public function myajaxadlist()
	{
		if ($this->checkbackinfo()) {
			if ($this->member["uid"] == 0) {
				$this->message("未登陆获取地区信息失败");
			}
		}

		$data["shopid"] = IFilter::act(IReq::get("shopid"));
		$data["backtype"] = IFilter::act(IReq::get("backtype"));
		if (empty($this->member["uid"]) || ($this->member["uid"] == 0)) {
			$tarelist = array();
			$cdata["id"] = 0;
			$cdata["default"] = 1;
			$cdata["contactname"] = ICookie::get("wxguest_username");
			$cdata["phone"] = ICookie::get("wxguest_phone");
			$cdata["address"] = ICookie::get("wxguest_address");

			if (empty($cdata["contactname"])) {
				$tarelist = array();
			}
			else {
				$tarelist[] = $cdata;
			}
		}
		else {
			$tarelist = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "'   order by id asc limit 0,50");
		}

		$this->success($tarelist);
	}

	public function savemyaddress()
	{
		if ($this->member["uid"] == 0) {
			$username = IFilter::act(IReq::get("contactname"));
			$phone = IFilter::act(IReq::get("phone"));
			$address = IFilter::act(IReq::get("add_new"));
			ICookie::set("wxguest_username", $username, 86400);
			ICookie::set("wxguest_phone", $phone, 86400);
			ICookie::set("wxguest_address", $address, 86400);
			$this->success("success");
		}

		$addressid = intval(IReq::get("addressid"));

		if (empty($addressid)) {
			$checknum = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "address where userid='" . $this->member["uid"] . "' ");

			if (Mysite::$app->config["addresslimit"] < $checknum) {
				$this->message("member_addresslimit");
			}

			$arr["userid"] = $this->member["uid"];
			$arr["username"] = $this->member["username"];
			$arr["address"] = IFilter::act(IReq::get("add_new"));
			$arr["phone"] = IFilter::act(IReq::get("phone"));
			$arr["otherphone"] = "";
			$arr["contactname"] = IFilter::act(IReq::get("contactname"));
			$arr["sex"] = IFilter::act(IReq::get("sex"));
			$arr["default"] = 1;

			if (!IValidate::len(IFilter::act(IReq::get("add_new")), 3, 50)) {
				$this->message("member_addresslength");
			}

			if (!IValidate::phone($arr["phone"])) {
				$this->message("errphone");
			}

			if (!empty($arr["otherphone"]) && !IValidate::phone($arr["otherphone"])) {
				$this->message("errphone");
			}

			if (!IValidate::len($arr["contactname"], 2, 6)) {
				$this->message("contactlength");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", array("default" => 0), "userid = " . $this->member["uid"] . " ");
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "address", $arr);
			$this->success("success");
		}
		else {
			$arr["address"] = IFilter::act(IReq::get("add_new"));
			$arr["phone"] = IFilter::act(IReq::get("phone"));
			$arr["otherphone"] = "";
			$arr["contactname"] = IFilter::act(IReq::get("contactname"));
			$arr["sex"] = IFilter::act(IReq::get("sex"));
			$arr["default"] = 1;

			if (!IValidate::len(IFilter::act(IReq::get("add_new")), 3, 50)) {
				$this->message("member_addresslength");
			}

			if (!IValidate::phone($arr["phone"])) {
				$this->message("errphone");
			}

			if (!empty($arr["otherphone"]) && !IValidate::phone($arr["otherphone"])) {
				$this->message("errphone");
			}

			if (!IValidate::len($arr["contactname"], 2, 6)) {
				$this->message("contactlength");
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", array("default" => 0), "userid = " . $this->member["uid"] . " ");
			$this->mysql->update(Mysite::$app->config["tablepre"] . "address", $arr, "userid = " . $this->member["uid"] . " and id=" . $addressid . "");
			$this->success("success");
		}

		$this->success("success");
	}

	public function setmydefadid()
	{
		if ($this->checkbackinfo()) {
			if ($this->member["uid"] == 0) {
				$this->message("未登陆获取地区信息失败");
			}
		}

		$addressid = intval(IReq::get("addressid"));

		if (empty($addressid)) {
			$this->message("默认值错误");
		}

		$checkdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address where id='" . $addressid . "' and userid = '" . $this->member["uid"] . "' ");

		if (empty($checkdata)) {
			$this->message("该地址不属于你该账号");
		}

		$this->mysql->update(Mysite::$app->config["tablepre"] . "address", array("default" => 0), "userid = " . $this->member["uid"] . " ");
		$this->mysql->update(Mysite::$app->config["tablepre"] . "address", array("default" => 1), "userid = " . $this->member["uid"] . " and id=" . $addressid . "");
		$this->success("success");
	}

	public function order()
	{
		$this->checkwxweb();
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}
	}

	public function userorder()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}

		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 5);
		$datalist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype != 100  order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$temparray = array(0 => "购卡", 1 => "养车", 2 => "其他", 100 => "跑腿订单");
		$backdata = array();

		foreach ($datalist as $key => $value ) {
			$listdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $value["id"] . "'");
			$value["det"] = "";

			foreach ($listdet as $k => $v ) {
				$value["det"] .= $v["goodsname"] . ",";
			}

			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $value["shopid"] . "'");
			$value["shoplogo"] = $shopinfo["shoplogo"];
			$orderwuliustatus = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "orderstatus where   orderid = " . $value["id"] . " order by id desc limit 0,1 ");
			$value["orderwuliustatus"] = $orderwuliustatus["statustitle"];
			$value["addtime"] = date("Y-m-d H:i", $value["addtime"]);
			$backdata[] = $value;
		}

		$datas = json_encode($backdata);
		echo "showmoreorder(" . $datas . ")";
		exit();
		$this->success($data);
		$this->success($backdata);
	}

	public function ordershow()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$orderid = intval(IReq::get("orderid"));
		$orderwuliustatus = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderstatus where   orderid = " . $orderid . " order by addtime asc limit 0,10 ");
		$data["orderwuliustatus"] = $orderwuliustatus;

		if (!empty($orderid)) {
			$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");

			if (empty($order)) {
				$data["order"] = "";
				Mysite::$app->setdata($data);
			}
			else {
				$order["ps"] = $order["shopps"];
				$orderdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $order["id"] . "'");
				$order["cp"] = count($orderdet);
				$buyerstatus = array("等待处理", "预订成功处理中", "订单已发货", "订单完成", "订单已取消", "订单已取消");
				$paytypelist = array("货到支付", "在线支付");
				$paytypearr = $paytypelist;
				$order["is_acceptorder"] = $order["is_acceptorder"];
				$order["surestatus"] = $order["status"];
				$order["basetype"] = $order["paytype"];
				$order["basepaystatus"] = $order["paystatus"];
				$order["paytype"] = $order["paytype"];
				$order["paystatus"] = $order["paystatus"];
				$order["addtime"] = date("Y-m-d H:i:s", $order["addtime"]);
				$order["posttime"] = date("Y-m-d H:i:s", $order["posttime"]);
				$data["order"] = $order;
				$data["orderdet"] = $orderdet;
				Mysite::$app->setdata($data);
			}
		}
		else {
			$data["order"] = "";
			Mysite::$app->setdata($data);
		}
	}

	public function commentorder()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$link = IUrl::creatUrl("wxsite/order");
		$orderid = intval(IReq::get("orderid"));

		if (!empty($orderid)) {
			$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");

			if (empty($order)) {
				$data["order"] = "";
				Mysite::$app->setdata($data);
			}
			else {
				$data["order"] = $order;
				$orderdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $order["id"] . "'");
				$data["orderdet"] = $orderdet;
				$tempcoment = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "comment where orderid='" . $order["id"] . "'");
				$data["comment"] = array();

				foreach ($tempcoment as $key => $value ) {
					$data["comment"][$value["orderdetid"]] = $value;
				}

				Mysite::$app->setdata($data);
			}
		}
		else {
			$data["order"] = "";
			Mysite::$app->setdata($data);
		}
	}

	public function gift()
	{
		$this->checkwxweb();

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$link = IUrl::creatUrl("wxsite/index");
		$giftlog = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "memberlog where type = 1 ");
		$data["giftlog"] = $giftlog;
		Mysite::$app->setdata($data);
	}

	public function giftlog()
	{
		$data["logstat"] = array("待处理", "已处理，配送中", "已发货", "兑换成功", "已取消兑换");
		Mysite::$app->setdata($data);
	}

	public function dhgift()
	{
		$giftid = intval(IReq::get("giftid"));
		$giftinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "gift where id = " . $giftid . "    ");
		$data["giftinfo"] = $giftinfo;
		Mysite::$app->setdata($data);
	}

	public function giftlist()
	{
	}

	public function juan()
	{
		$wjuan = array(
			"shuliang" => 0,
			"list"     => array()
			);
		$ujuan = array(
			"shuliang" => 0,
			"list"     => array()
			);
		$ojuan = array(
			"shuliang" => 0,
			"list"     => array()
			);
		$nowtime = time();
		$data["nowtime"] = $nowtime;
		$wjuan["shuliang"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "juan    where uid = " . $this->member["uid"] . " and endtime > " . $nowtime . " and status = 1 ");
		$wjuan["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan where uid = " . $this->member["uid"] . " and endtime > " . $nowtime . " and status = 1 ");
		$ujuan["shuliang"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "juan    where uid = " . $this->member["uid"] . "  and status = 2 ");
		$ujuan["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan where uid = " . $this->member["uid"] . "   and status = 2 ");
		$ojuan["shuliang"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "juan    where uid = " . $this->member["uid"] . " and endtime < " . $nowtime . " and (status = 1 or status =3)");
		$ojuan["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan where uid = " . $this->member["uid"] . " and endtime < " . $nowtime . " and (status = 1 or status =3)  ");
		$pcjuan = array(
			"shuliang" => 0,
			"list"     => array()
			);
		$pcjuan["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan where status = 1 and  uid = " . $this->member["uid"] . " ");
		$pcjuan["shuliang"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "juan   where status = 1 and   uid = " . $this->member["uid"] . "  ");
		$wxujuan = array(
			"shuliang" => 0,
			"list"     => array()
			);
		$wxujuan["shuliang"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "wxuserjuan    where uid = " . $this->member["uid"] . " and endtime > " . $nowtime . " and lqstatus = 1 ");
		$wxujuan["list"] = $this->mysql->getarr("select a.*,b.cartdesrc from " . Mysite::$app->config["tablepre"] . "wxuserjuan as a left join " . Mysite::$app->config["tablepre"] . "wxjuan as b on a.juanid = b.id where a.uid = " . $this->member["uid"] . " and a.endtime > " . $nowtime . " and a.lqstatus = 1 ");
		$data["wxujuan"] = $wxujuan;
		$data["pcjuan"] = $pcjuan;
		$data["wjuan"] = $wjuan;
		$data["ujuan"] = $ujuan;
		$data["ojuan"] = $ojuan;
		Mysite::$app->setdata($data);
	}

	public function cart()
	{
		$shopid = intval(IReq::get("shopid"));
		$backdata = array();
		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
			$backdata["list"] = $carinfo["goodslist"];
			$backdata["sumcount"] = $carinfo["count"];
			$backdata["sum"] = $carinfo["sum"];

			if ($carinfo["shopinfo"]["shoptype"] == 1) {
				$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
			}
			else {
				$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
			}

			if ($shopcheckinfo["is_goshop"] == 1) {
				$backdata["bagcost"] = 0;
			}
			else {
				$backdata["bagcost"] = $carinfo["bagcost"];
			}

			$cxclass = new sellrule();
			$cxclass->setdata($shopid, $carinfo["sum"], $carinfo["shopinfo"]["shoptype"]);
			$cxinfo = $cxclass->getdata();
			$backdata["surecost"] = $cxinfo["surecost"];
			$backdata["downcost"] = $cxinfo["downcost"];
			$backdata["gzdata"] = (isset($cxinfo["gzdata"]) ? $cxinfo["gzdata"] : array());
			$areaid = ICookie::get("myaddress");
			$tempinfo = $this->pscost($shopcheckinfo, $backdata["sumcount"], $areaid);
			$backdata["pstype"] = $tempinfo["pstype"];
			$areaid = ICookie::get("myaddress");

			if ($shopcheckinfo["is_goshop"] == 1) {
				$backdata["pscost"] = 0;
			}
			else {
				$backdata["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
			}

			$backdata["canps"] = $tempinfo["canps"];
			$this->success($backdata);
		}
		else {
			$this->message(array());
		}
	}

	public function fabupaotui()
	{
		$this->checkwxuser();

		if ($this->member["uid"] == 0) {
			$this->message("未登陆");
		}

		$ptinfoset = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "paotuiset  ");
		$demandcontent = trim(IFilter::act(IReq::get("demandcontent")));
		$getaddress = trim(IReq::get("getaddress"));
		$getdetaddress = trim(IReq::get("getdetaddress"));
		$getlng = trim(IReq::get("getlng"));
		$getlat = trim(IReq::get("getlat"));
		$shouaddress = trim(IReq::get("shouaddress"));
		$shouetaddress = trim(IReq::get("shouetaddress"));
		$shoulng = trim(IReq::get("shoulng"));
		$shoulat = trim(IReq::get("shoulat"));
		$shouphone = trim(IReq::get("shouphone"));
		$minit = trim(IReq::get("minit"));
		$ptkg = trim(IReq::get("ptkg"));
		$ptkm = trim(IReq::get("ptkm"));
		$allkgcost = trim(IReq::get("allkgcost"));
		$allkmcost = trim(IReq::get("allkmcost"));
		$allcost = trim(IReq::get("allcost"));
		$pttype = trim(IReq::get("pttype"));
		$paytype = trim(IReq::get("paytype"));

		if (empty($demandcontent)) {
			$this->message("请简要填写需求内容");
		}

		if (empty($getaddress)) {
			$this->message("获取取货地址失败,请重新获取");
		}

		if (empty($getlng)) {
			$this->message("获取取货地址失败,请重新获取");
		}

		if (empty($getlat)) {
			$this->message("获取取货地址失败,请重新获取");
		}

		if (empty($shouaddress)) {
			$this->message("获取收货地址失败,请重新获取");
		}

		if (empty($shoulng)) {
			$this->message("获取收货地址失败,请重新获取");
		}

		if (empty($shoulat)) {
			$this->message("获取收货地址失败,请重新获取");
		}

		if (empty($shouphone)) {
			$this->message("请填写收货电话");
		}

		if (!IValidate::suremobi($shouphone)) {
			$this->message("请输入正确的手机号");
		}

		$tempdata = $this->getOpenPosttime($ptinfoset["is_ptorderbefore"], time(), $ptinfoset["postdate"], $minit, $ptinfoset["pt_orderday"]);

		if ($tempdata["is_opentime"] == 2) {
			$this->message("选择的配送时间段，店铺未设置");
		}

		if ($tempdata["is_opentime"] == 3) {
			$this->message("选择的配送时间段已超时");
		}

		$data["sendtime"] = $tempdata["is_posttime"];
		$data["postdate"] = $tempdata["is_postdate"];
		$data["pttype"] = $pttype;
		$data["content"] = $demandcontent;
		$data["shopaddress"] = $getaddress;
		$data["buyeraddress"] = $shouaddress;
		$data["buyerphone"] = $shouphone;
		$data["buyerphone"] = $shouphone;
		$data["addtime"] = time();
		$data["ordertype"] = 3;
		$data["shoptype"] = 100;
		$data["paytype"] = $paytype;
		$data["paystatus"] = 0;
		$data["ptkg"] = $ptkg;
		$data["ptkm"] = $ptkm;
		$data["allkgcost"] = $allkgcost;
		$data["allkmcost"] = $allkmcost;
		$data["allcost"] = $allcost;
		$data["dno"] = time() . rand(1000, 9999);
		$data["pstype"] = 1;
		$data["buyeruid"] = $this->member["uid"];
		$data["buyername"] = $this->member["username"];
		$juli = $this->GetDistance($getlat, $getlng, $shoulat, $shoulng, 1, 1);
		$tempmi = $juli;
		$juli = round($juli / 1000, 1);
		$tmpallkmcost = 0;

		if ($juli <= $ptinfoset["km"]) {
			$tmpallkmcost = $ptinfoset["kmcost"];
		}
		else {
			$addjuli = $juli - $ptinfoset["km"];
			$addnum = floor($addjuli / $ptinfoset["addkm"]);
			$addcost = $addnum * $ptinfoset["addkmcost"];
			$tmpallkmcost = $ptinfoset["kmcost"] + $addcost;
		}

		if ($juli != $ptkm) {
			$this->message("获取距离错误");
		}

		if ($tmpallkmcost != $allkmcost) {
			$this->message("获取距离总金额错误");
		}

		$tmpallkgcost = 0;

		if ($ptkg <= $ptinfoset["kg"]) {
			$tmpallkgcost = $ptinfoset["kgcost"];
		}
		else {
			$addkg = $ptkg - $ptinfoset["kg"];
			$addkgnum = floor($addkg / $ptinfoset["addkg"]);
			$addkgcost = $addkgnum * $ptinfoset["addkgcost"];
			$tmpallkgcost = $ptinfoset["kgcost"] + $addkgcost;
		}

		if ($tmpallkgcost != $allkgcost) {
			$this->message("获取重量总金额错误");
		}

		$panduan = Mysite::$app->config["man_ispass"];
		$data["status"] = 0;
		if (($panduan != 1) && ($data["paytype"] == 0)) {
			$data["status"] = 1;
		}

		$data["ipaddress"] = "";
		$ip_l = new iplocation();
		$ipaddress = $ip_l->getaddress($ip_l->getIP());

		if (isset($ipaddress["area1"])) {
			$data["ipaddress"] = $ipaddress["ip"] . mb_convert_encoding($ipaddress["area1"], "UTF-8", "GB2312");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "order", $data);
		$orderid = $this->mysql->insertid();
		$orderClass = new orderClass();
		$orderClass->writewuliustatus($orderid, 1, $data["paytype"]);

		if ($panduan != 1) {
			$orderclass = new orderclass();
			$orderclass->sendmess($orderid);
		}

		$this->success($orderid);
	}

	public function paotui()
	{
		$this->checkwxweb();
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}
	}

	public function paotuiorder()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}

		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 20);
		$datalist = $this->mysql->getarr("select id,shopname,is_goshop,allcost,content,addtime,status,is_ping,shoptype,is_reback from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and shoptype=100 order by id desc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$temparray = array("购卡", "养车", "其他");
		$backdata = array();

		foreach ($datalist as $key => $value ) {
			$listdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $value["id"] . "'");
			$value["det"] = "";

			foreach ($listdet as $k => $v ) {
				$value["det"] .= $v["goodsname"] . ",";
			}

			$value["shoptype"] = $temparray[$value["shoptype"]];
			$value["addtime"] = date("Y-m-d H:i", $value["addtime"]);
			$backdata[] = $value;
		}

		$this->success($backdata);
	}

	public function mypaotui()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("", $link);
		}
	}

	public function makeorder()
	{
		$this->checkwxuser();

		if ($this->checkbackinfo()) {
			if ($this->member["uid"] == 0) {
				$this->message("未登陆");
			}
		}

		if (empty($this->member["uid"]) || ($this->member["uid"] == 0)) {
			$addressinfo = NULL;
			$cdata["id"] = 0;
			$cdata["default"] = 1;
			$cdata["contactname"] = ICookie::get("wxguest_username");
			$cdata["phone"] = ICookie::get("wxguest_phone");
			$cdata["address"] = ICookie::get("wxguest_address");

			if (empty($cdata["contactname"])) {
			}
			else {
				$addressinfo = $cdata;
			}
		}
		else {
			$addressinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where userid = " . $this->member["uid"] . " and `default`=1   ");
		}

		if (empty($addressinfo)) {
			$this->message("未设置默认地址");
		}

		$info["username"] = $addressinfo["contactname"];
		$info["mobile"] = $addressinfo["phone"];
		$info["addressdet"] = $addressinfo["address"];
		$info["shopid"] = intval(IReq::get("shopid"));
		$info["remark"] = IFilter::act(IReq::get("remark"));
		$info["paytype"] = IFilter::act(IReq::get("paytype"));
		$info["dikou"] = intval(IReq::get("dikou"));
		$info["minit"] = IFilter::act(IReq::get("minit"));
		$info["juanid"] = intval(IReq::get("juanid"));

		if ($this->checkbackinfo()) {
			$info["ordertype"] = 3;
		}
		else {
			$info["ordertype"] = 5;
		}

		$peopleNum = IFilter::act(IReq::get("peopleNum"));
		$info["othercontent"] = "";

		if (empty($info["shopid"])) {
			$this->message("店铺ID错误");
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
			$this->message("对应店铺购物车商品为空");
		}

		if ($carinfo["shopinfo"]["shoptype"] == 1) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $info["shopid"] . "'    ");
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $info["shopid"] . "'    ");
		}

		if (empty($shopinfo)) {
			$this->message("店铺获取失败");
		}

		$areaid = ICookie::get("myaddress");
		$checkps = $this->pscost($shopinfo, $carinfo["count"], $areaid);
		$info["cattype"] = 0;

		if (empty($info["username"])) {
			$this->message("联系人不能为空");
		}

		if (!IValidate::suremobi($info["mobile"])) {
			$this->message("请输入正确的手机号");
		}

		if (empty($info["addressdet"])) {
			$this->message("详细地址为空");
		}

		$info["userid"] = (!isset($this->member["score"]) ? "0" : $this->member["uid"]);

		if (Mysite::$app->config["allowedguestbuy"] != 1) {
			if ($info["userid"] == 0) {
				$this->message("禁止游客下单");
			}
		}

		$info["ipaddress"] = "";
		$ip_l = new iplocation();
		$ipaddress = $ip_l->getaddress($ip_l->getIP());

		if (isset($ipaddress["area1"])) {
			$info["ipaddress"] = $ipaddress["ip"] . mb_convert_encoding($ipaddress["area1"], "UTF-8", "GB2312");
		}

		$info["areaids"] = "";

		if ($shopinfo["is_open"] != 1) {
			$this->message("店铺暂停营业");
		}

		$tempdata = $this->getOpenPosttime($shopinfo["is_orderbefore"], $shopinfo["starttime"], $shopinfo["postdate"], $info["minit"], $shopinfo["befortime"]);

		if ($tempdata["is_opentime"] == 2) {
			$this->message("选择的配送时间段，店铺未设置");
		}

		if ($tempdata["is_opentime"] == 3) {
			$this->message("选择的配送时间段已超时");
		}

		$info["sendtime"] = $tempdata["is_posttime"];
		$info["postdate"] = $tempdata["is_postdate"];
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

		$paytype = ($info["paytype"] == 1 ? 1 : 0);
		$info["shopinfo"] = $shopinfo;
		$info["allcost"] = $carinfo["sum"];
		$info["bagcost"] = $carinfo["bagcost"];
		$info["allcount"] = $carinfo["count"];
		$info["shopps"] = $checkps["pscost"];
		$info["goodslist"] = $carinfo["goodslist"];
		$info["pstype"] = $checkps["pstype"];
		$info["cattype"] = 0;

		foreach ($info["goodslist"] as $key => $value ) {
			if ($value["stock"] < $value["count"]) {
				$this->message("商品库存不足");
			}
		}

		$info["is_goshop"] = 0;

		if ($info["allcost"] < $shopinfo["limitcost"]) {
			$this->message("商品总价低于最小起送价" . $shopinfo["limitcost"]);
		}

		$orderclass = new orderclass();
		$orderclass->makenormal($info);
		$orderid = $orderclass->getorder();

		if ($info["userid"] == 0) {
			ICookie::set("orderid", $orderid, 86400);
		}

		$smardb->DelShop($info["shopid"]);
		$this->success($orderid);
		exit();
	}

	public function makeorder2()
	{
		$this->checkwxuser();

		if ($this->checkbackinfo()) {
			if ($this->member["uid"] == 0) {
				$this->message("未登陆");
			}
		}

		if (empty($this->member["uid"]) || ($this->member["uid"] == 0)) {
			$addressinfo = NULL;
			$cdata["id"] = 0;
			$cdata["default"] = 1;
			$cdata["contactname"] = ICookie::get("wxguest_username");
			$cdata["phone"] = ICookie::get("wxguest_phone");
			$cdata["address"] = ICookie::get("wxguest_address");

			if (empty($cdata["contactname"])) {
			}
			else {
				$addressinfo = $cdata;
			}
		}
		else {
			$addressinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where userid = " . $this->member["uid"] . " and `default`=1   ");
		}

		if (empty($addressinfo)) {
			$this->message("未设置默认地址");
		}

		$info["username"] = $addressinfo["contactname"];
		$info["mobile"] = $addressinfo["phone"];
		$info["addressdet"] = $addressinfo["address"];
		$subtype = intval(IReq::get("subtype"));
		$info["shopid"] = intval(IReq::get("shopid"));
		$info["remark"] = IFilter::act(IReq::get("content"));
		$info["paytype"] = IFilter::act(IReq::get("paytype"));

		if ($info["paytype"] == "") {
			$this->message("未开启任何支付方式，请联系管理员！");
		}

		$info["minit"] = IFilter::act(IReq::get("minit"));
		$info["juanid"] = intval(IReq::get("juanid"));

		if ($this->checkbackinfo()) {
			$info["ordertype"] = 3;
		}
		else {
			$info["ordertype"] = 5;
		}

		$peopleNum = IFilter::act(IReq::get("personcount"));

		if ($peopleNum < 1) {
			$this->message("选择车位座数");
		}

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
			$this->message("选择的配送时间段，店铺未设置");
		}

		if ($tempdata["is_opentime"] == 3) {
			$this->message("选择的配送时间段已超时");
		}

		$info["sendtime"] = $tempdata["is_posttime"];
		$info["postdate"] = $tempdata["is_postdate"];

		if ($info["paytype"] == "undefined") {
			$this->message("未开启任何支付方式，请联系管理员！");
		}

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
				$this->message("对应店铺购物车商品为空");
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
			$smardb->delshop($info["shopid"]);
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

	public function subshow()
	{
		$orderid = intval(IReq::get("orderid"));
		$userid = (empty($this->member["uid"]) ? 0 : $this->member["uid"]);
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			$this->message("订单获取失败");
		}

		if ($userid == 0) {
			$neworderid = ICookie::get("orderid");

			if ($orderid != $neworderid) {
				$this->message("订单无查看权限1");
			}
		}

		if ($orderid < 1) {
			$this->message("订单获取失败");
		}

		$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");
		$order["ps"] = $order["shopps"];

		if (empty($order)) {
			$this->message("订单获取失败");
		}

		$orderdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $order["id"] . "'");
		$order["cp"] = count($orderdet);
		$buyerstatus = array("等待处理", "预订成功处理中", "订单已发货", "订单完成", "订单已取消", "订单已取消");
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist where type = 0 or type=2  order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[$value["loginname"]] = $value["logindesc"];
			}
		}

		$data["paylist"] = $paylist;
		$data["order"] = $order;

		if ($this->checkbackinfo()) {
			if (($order["paystatus"] == 0) && ($order["paytype"] == 1)) {
				$wxopenid = ICookie::get("wxopenid");
				$weixindir = hopedir . "/plug/pay/weixin/";
				require_once $weixindir . "lib/WxPay.Api.php";
				require_once $weixindir . "WxPay.JsApiPay.php";
				$tools = new JsApiPay();
				$openId = $tools->GetOpenid();
				$input = new WxPayUnifiedOrder();
				$input->SetBody("支付订单" . $order["dno"]);
				$input->SetAttach($order["dno"]);
				$input->SetOut_trade_no($order["id"]);
				$input->SetTotal_fee($order["allcost"] * 100);
				$input->SetTime_start(date("YmdHis"));
				$input->SetTime_expire(date("YmdHis", time() + 600));
				$input->SetTimeStamp(time());
				$input->SetGoods_tag("预订");
				$input->SetNotify_url(Mysite::$app->config["siteurl"] . "/plug/pay/weixin/notify.php");
				$input->SetTrade_type("JSAPI");
				$input->SetOpenid($openId);
				$ordermm = WxPayApi::unifiedOrder($input);
				$jsApiParameters = $tools->GetJsApiParameters($ordermm);
				$data["wxdata"] = $jsApiParameters;
			}
		}

		Mysite::$app->setdata($data);

		if ($this->checkbackinfo()) {
			Mysite::$app->setAction("subshow");
		}
		else {
			Mysite::$app->setAction("mobilesubshow");
		}
	}

	public function shop()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$nowdata = date("Y-m-d", time());
		$mintime = strtotime($nowdata);
		$maxtime = strtotime($nowdata . " 23:59:59");
		$where = " and  posttime > " . $mintime . " and posttime < " . $maxtime;
		$tjlist = $this->mysql->getarr("select count(id) as shuliang,status from " . Mysite::$app->config["tablepre"] . "order where shopuid=" . $this->member["uid"] . " " . $where . "  group by status order by id asc limit 0,50");
		$data["tj"] = array();

		foreach ($tjlist as $key => $value ) {
			$data["tj"][$value["status"]] = $value["shuliang"];
		}

		Mysite::$app->setdata($data);
	}

	public function shopordert()
	{
	}

	public function shopordertoday()
	{
		$nowdata = date("Y-m-d", time());
		$mintime = strtotime($nowdata);
		$maxtime = strtotime($nowdata . " 23:59:59");
		$where = "  posttime > " . $mintime . " and posttime < " . $maxtime;
		$status = intval(IFilter::act(IReq::get("status")));
		$status = (in_array($status, array(1, 2, 3)) ? $status : 1);
		$where .= " and status =" . $status;
		$where .= " and shopuid =" . $this->member["uid"];
		$buyerstatus = array("等待处理", "等待发货", "已发货，待完成", "订单完成", "订单已取消", "订单已取消");
		$data["buyerstatus"] = $buyerstatus;
		$data["where"] = $where;
		$arraystatus = array(1 => "今日待发货订单", 2 => "今日已发货订单", 3 => "今日完成订单");
		$data["orderbt"] = $arraystatus[$status];
		Mysite::$app->setdata($data);
	}

	public function shopshoworder()
	{
		$this->checkwxuser();
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$orderid = intval(IReq::get("id"));

		if (!empty($orderid)) {
			$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where shopuid='" . $this->member["uid"] . "' and id = " . $orderid . "");

			if (empty($order)) {
				$data["order"] = "";
				Mysite::$app->setdata($data);
			}
			else {
				$order["ps"] = $order["shopps"];
				$orderdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id='" . $order["id"] . "'");
				$order["cp"] = count($orderdet);
				$buyerstatus = array("等待处理", "等待发货", "已发货，待完成", "订单完成", "订单已取消", "订单已取消");
				$paytypelist = array(0 => "货到支付", 1 => "在线支付", "weixin" => "微信支付");
				$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

				if (is_array($paylist)) {
					foreach ($paylist as $key => $value ) {
						$paytypelist[$value["loginname"]] = $value["logindesc"];
					}
				}

				$paytypearr = $paytypelist;
				$order["surestatus"] = $order["status"];
				$order["status"] = $buyerstatus[$order["status"]];
				$order["paytype"] = $paytypearr[$order["paytype"]];
				$order["paystatus"] = ($order["paystatus"] == 1 ? "已支付" : "未支付");
				$order["addtime"] = date("Y-m-d H:i:s", $order["addtime"]);
				$order["posttime"] = date("Y-m-d H:i:s", $order["posttime"]);
				$data["order"] = $order;
				$data["orderdet"] = $orderdet;
				Mysite::$app->setdata($data);
			}
		}
		else {
			$data["order"] = "";
			Mysite::$app->setdata($data);
		}
	}

	public function shopcontrol()
	{
		$this->checkmemberlogin();
		$controlname = trim(IFilter::act(IReq::get("controlname")));
		$orderid = intval(IReq::get("orderid"));
		$ordertempinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . "");

		if ($ordertempinfo["shopuid"] != $this->member["uid"]) {
			$this->message("您不能操作此订单");
		}

		$shopid = $ordertempinfo["shopid"];
		$shopinfo = $this->mysql->select_one("select uid from " . Mysite::$app->config["tablepre"] . "shop where id = " . $shopid . "");

		switch ($controlname) {
		case $controlname:
			$reason = trim(IFilter::act(IReq::get("reason")));

			if (empty($reason)) {
				$this->message("关闭理由不能为空");
			}

			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sellerunorder($shopinfo["uid"], $reason)) {
				$this->success("操作成功");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case $controlname:
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sendorder($shopinfo["uid"])) {
				$this->success("操作成功");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case $controlname:
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->shenhe($shopinfo["uid"])) {
				$this->success("操作成功");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case $controlname:
			$ordercontrol = new ordercontrol($orderid);

			if ($ordercontrol->sellerdelorder($shopinfo["uid"])) {
				$this->success("操作成功");
			}
			else {
				$this->message($ordercontrol->Error());
			}

			break;

		case $controlname:
			if ($ordertempinfo["status"] != 1) {
				$this->message("订单状态不可操作是否制作");
			}

			if (!empty($ordertempinfo["is_make"])) {
				$this->message("订单已设置过是否制作，如要取消 请联系网站客服");
			}

			$newdata["is_make"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $newdata, "id='" . $orderid . "'");
			$this->success("操作成功");
			break;

		case $controlname:
			if ($ordertempinfo["status"] != 1) {
				$this->message("订单状态不可操作是否制作");
			}

			if (!empty($ordertempinfo["is_make"])) {
				$this->message("订单已设置过是否制作，如要取消 请联系网站客服");
			}

			$newdata["is_make"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $newdata, "id='" . $orderid . "'");
			$this->success("操作成功");
			break;

		default:
			$this->message("未定义的操作");
			break;
		}
	}

	public function ajaxlocation()
	{
		$lat = IFilter::act(IReq::get("lat"));
		$lng = IFilter::act(IReq::get("lng"));
		$content = file_get_contents("http://api.map.baidu.com/geoconv/v1/?coords=" . $lng . "," . $lat . "&&from=1&to=5&ak=" . Mysite::$app->config["baidumapkey"]);
		$backinfo = json_decode($content, true);

		if ($backinfo["status"] == 0) {
			$data["lat"] = $backinfo["result"][0]["y"];
			$data["lng"] = $backinfo["result"][0]["x"];
			ICookie::set("lat", $backinfo["result"][0]["y"], 2592000);
			ICookie::set("lng", $backinfo["result"][0]["x"], 2592000);
			$this->success($data);
		}
		else {
			$this->message("失败");
		}
	}

	public function locationshop()
	{
		ICookie::clear("myaddress");
		$link = IUrl::creatUrl("wxsite/shoplist");
		$this->message("", $link);
	}

	public function getwxuaerlocation()
	{
		$lat = IFilter::act(IReq::get("lat"));
		$lng = IFilter::act(IReq::get("lng"));
		$content = file_get_contents("http://api.map.baidu.com/geocoder/v2/?ak=" . Mysite::$app->config["baidumapkey"] . "&location=" . $lat . "," . $lng . "&output=json&pois=0&coordtype=wgs84ll");
		$backinfo = json_decode($content, true);
		print_r($backinfo["result"]["addressComponent"]);

		if ($backinfo["status"] == 0) {
			$data["cityname"] = $backinfo["result"]["addressComponent"]["city"];
			$data["areaname"] = $backinfo["result"]["addressComponent"]["district"];
			$data["streetname"] = $backinfo["result"]["addressComponent"]["street"];
			$this->success($data);
		}
		else {
			$this->message("失败");
		}
	}

	public function checkwxuser()
	{
	}

	public function showpayhtml($data)
	{
		$tempcontent = "";

		if ($data["paysure"] == true) {
			$tempcontent = "<div style=\"margin-top:50px;background-color:#fff;\">\r\n\t\t\t <div style=\"height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;\">\r\n\t\t\t    <span style=\"background:url('http://" . Mysite::$app->config["siteurl"] . "/upload/images/order_ok.png') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;\"></span>\r\n\t\t\t\t<div style=\"position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;\">恭喜您，支付订单成功</div>\r\n\t\t\t\t\r\n\t\t\t    \r\n\t\t\t</div>\r\n\t\t\t<div style=\"width:80%;margin:0px auto;padding-top:10px;\"><font style=\"font-size:12px;\">单号:</font><span style=\"padding-left:20px;font-size:12px;display: inline-block;\">" . $data["reason"]["dno"] . "</span></div>\r\n\t\t\t<div style=\"width:80%;margin:0px auto;padding-top:10px;\"><font style=\"font-size:12px;\">总价:</font><span style=\"padding-left:20px;color:red;font-weight:bold;font-size:15px;\">￥" . $data["reason"]["allcost"] . "元</span></div> \r\n\t\t\t<div style=\"width:80%;margin:0px auto;padding-top:30px;text-align:right;\"><a href=\"" . $data["url"] . "\"><span style=\"font-size:20px;color:#fff;padding:5px;background-color:red;\">立即返回</span></a></div>\r\n\t   </div>";
		}
		else {
			$tempcontent = "<div style=\"margin-top:50px;background-color:#fff;\">\r\n\t\t\t <div style=\"height:30px;width:80%;padding-left:10%;padding-right:10%;padding-top:10%;\">\r\n\t\t\t    <span style=\"background:url('" . Mysite::$app->config["siteurl"] . "/upload/images/nocontent.png') left no-repeat;height:30px;width:30px;background-size:100% 100%;display: inline-block;\"></span>\r\n\t\t\t\t<div style=\"position:absolute;margin-left:50px;  margin-top: -30px; font-size: 20px;  font-weight: bold;  line-height: 20px;\">sorry,支付订单失败</div>\r\n\t\t\t\t\r\n\t\t\t    \r\n\t\t\t</div>\r\n\t\t\t<div style=\"width:80%;margin:0px auto;padding-top:10px;\"><font style=\"font-size:12px;\">原因:</font><span style=\"padding-left:20px;font-size:12px;display: inline-block;\">" . $data["reason"] . "</span></div> \r\n\t\t\t<div style=\"width:80%;margin:0px auto;padding-top:30px;text-align:right;\"><a href=\"" . $data["url"] . "\"><span style=\"font-size:20px;color:#fff;padding:5px;background-color:red;  cursor: pointer;\">立即返回</span></a></div>\r\n\t   </div>";
		}

		$html = "<!DOCTYPE html>\r\n<html>\r\n<head>\r\n   <meta charset=\"UTF-8\">  \r\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1,user-scalable=0\">\r\n\t<title>支付返回信息</title> \r\n\t \r\n\t \r\n \r\n <script>\r\n \t \r\n</script>\r\n\r\n \r\n</head>\r\n<body style=\"height:100%;width:100%;margin:0px;\"> \r\n   <div style=\"max-width:400px;margin:0px;margin:0px auto;min-height:300px;\"> " . $tempcontent . "    </div>\r\n\t \r\n</body>\r\n</html>";
		print_r($html);
		exit();
	}

	public function gotopay()
	{
		$orderid = intval(IReq::get("orderid"));
		$payerrlink = IUrl::creatUrl("wxsite/subshow/orderid/" . $orderid);
		$errdata = array("paysure" => false, "reason" => "", "url" => "");

		if (empty($orderid)) {
			$backurl = IUrl::creatUrl("wxsite/index");
			$errdata["url"] = $backurl;
			$errdata["reason"] = "订单获取失败";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$userid = (empty($this->member["uid"]) ? 0 : $this->member["uid"]);

		if ($userid == 0) {
			$neworderid = ICookie::get("orderid");

			if ($orderid != $neworderid) {
				$errdata["url"] = $payerrlink;
				$errdata["reason"] = "订单操作无权限";
				$errdata["paysure"] = false;
				$this->showpayhtml($errdata);
			}
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id=" . $orderid . "  ");

		if (empty($orderinfo)) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "订单数据获取失败";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		if (0 < $userid) {
			if ($orderinfo["buyeruid"] != $userid) {
				$errdata["url"] = $payerrlink;
				$errdata["reason"] = "订单不属于您";
				$errdata["paysure"] = false;
				$this->showpayhtml($errdata);
			}
		}

		if ($orderinfo["paytype"] == 0) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "此订单是货到支付订单不可操作";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		if (2 < $orderinfo["status"]) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "此订单已发货或者其他状态不可操作";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$paydotype = IFilter::act(IReq::get("paydotype"));
		$paylist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "paylist where  loginname = '" . $paydotype . "' and (type = 0 or type=2) order by id asc limit 0,50");

		if (empty($paylist)) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "不存在的支付类型";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		if ($orderinfo["paystatus"] == 1) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "此订单已支付";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$paydir = hopedir . "/plug/pay/" . $paydotype;

		if (!file_exists($paydir . "/pay.php")) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "支付方式文件不存在";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$dopaydata = array("type" => "order", "upid" => $orderid, "cost" => $orderinfo["allcost"], "source" => 2, "paydotype" => $paydotype);
		include_once $paydir . "/pay.php";
		exit();
	}

	public function drawbacklog()
	{
		$link = IUrl::creatUrl("wxsite/index");

		if ($this->member["uid"] == 0) {
			$this->message("未登陆", $link);
		}

		$orderid = intval(IReq::get("orderid"));

		if (!empty($orderid)) {
			$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where buyeruid='" . $this->member["uid"] . "' and id = " . $orderid . "");
			$data["order"] = $order;

			if (0 < $order["is_reback"]) {
				$drawbacklog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "drawbacklog where orderid='" . $order["id"] . "'  ");
				$data["drawbacklog"] = $drawbacklog;
			}

			Mysite::$app->setdata($data);
		}
		else {
			$data["order"] = "";
			Mysite::$app->setdata($data);
		}
	}

	public function savedrawbacklog()
	{
		if (empty($this->member["uid"])) {
			$this->message("member_nologin");
		}

		$drawbacklog = new drawbacklog($this->mysql, $this->memberCls);
		$check = $drawbacklog->save();
var_dump($check);
		if ($check == true) {
			$this->success("success");
		}
		else {
			$msg = $drawbacklog->GetErr();
			$this->message($msg);
		}
	}

	public function togethersay()
	{
		$this->checkwxuser();
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage();
		$data["signPackage"] = $signPackage;
		$togethersaylist1 = $this->mysql->getarr(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "member  as b  on a.uid = b.uid where a.is_top=0  and is_show=1   order by addtime desc ");
		$togethersaylist = array();

		foreach ($togethersaylist1 as $key => $value ) {
			$value["pingjiazongshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment where parentid  = " . $value["id"] . "  ");
			$value["zongzanshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxpjzan where commentid  = " . $value["id"] . "  ");
			$wxuserimages = $value["userimg"];
			$value["wxuserimgarr"] = explode("@", $wxuserimages);
			$togethersaylist[] = $value;
		}

		$data["togethersaylist"] = $togethersaylist;
		$togethersaylist2 = $this->mysql->getarr(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "admin  as b  on a.uid = b.uid where a.is_top=1  and is_show=1   order by addtime desc ");
		$togethersaycomlist = array();

		foreach ($togethersaylist2 as $key => $value ) {
			$value["pingjiazongshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment where parentid  = " . $value["id"] . "  ");
			$value["zongzanshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxpjzan where commentid  = " . $value["id"] . "  ");
			$wxuserimages = $value["userimg"];
			$value["wxuserimgarr"] = explode("@", $wxuserimages);
			$togethersaycomlist[] = $value;
		}

		$data["togethersaycomlist"] = $togethersaycomlist;
		Mysite::$app->setdata($data);
	}

	public function commentwxuser()
	{
		$this->checkwxuser();
		$id = intval(IFilter::act(IReq::get("id")));
		$data["id"] = $id;
		$checkinfo = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment where id = " . $id . "   ");

		if ($checkinfo["is_top"] == 0) {
			$wxcommentone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "member  as b  on a.uid = b.uid where a.id = " . $id . "  order by addtime desc ");
		}
		else {
			$wxcommentone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "admin  as b  on a.uid = b.uid where a.id = " . $id . "  order by addtime desc ");
		}

		$data["userimages"] = explode("@", $wxcommentone["userimg"]);
		$wxreplylist = $this->mysql->getarr(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment as a left join " . Mysite::$app->config["tablepre"] . "member  as b  on a.uid = b.uid where a.parentid = " . $wxcommentone["id"] . "  order by addtime desc ");
		$data["zongzanshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxpjzan where commentid  = " . $wxcommentone["id"] . "  ");
		$data["wxreplylist"] = $wxreplylist;
		$data["wxcommentone"] = $wxcommentone;
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage();
		$data["signPackage"] = $signPackage;
		$data["pingjiazongshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment where parentid  = " . $id . "  ");
		Mysite::$app->setdata($data);
	}

	public function saveuserpmes()
	{
		$this->checkwxuser();
		$uid = $this->member["uid"];
		$media_ids = trim(IFilter::act(IReq::get("media_ids")));
		$wxclass = new wx_s();
		$accessToken = $wxclass->gettoken();
		$mediaarr = explode(",", $media_ids);
		$filename = array();

		if (!empty($media_ids)) {
			if (is_array($mediaarr)) {
				foreach ($mediaarr as $key => $value ) {
					$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $accessToken . "&media_id=" . $value;
					$upwxfilename = $wxclass->saveMedia($url);
					$filename[] = $upwxfilename;
				}

				$filename = $filename;
				$data["userimg"] = implode("@", $filename);
			}
			else {
				$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $accessToken . "&media_id=" . $media_ids;
				$upwxfilename = $wxclass->saveMedia($url);
				$data["userimg"] = $upwxfilename;
			}
		}
		else {
			$data["userimg"] = "";
		}

		$data["usercontent"] = trim(IFilter::act(IReq::get("message")));
		$data["cityname"] = trim(IFilter::act(IReq::get("cityname")));
		$data["areaname"] = trim(IFilter::act(IReq::get("areaname")));
		$data["streetname"] = trim(IFilter::act(IReq::get("streetname")));
		$data["uid"] = $uid;
		$data["addtime"] = time();
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "wxcomment", $data);
		$this->success("success");
	}

	public function savehuifupj()
	{
		$this->checkwxuser();
		$uid = $this->member["uid"];
		$data["content"] = trim(IFilter::act(IReq::get("message")));
		$data["parentid"] = intval(IFilter::act(IReq::get("parentid")));
		$data["cityname"] = trim(IFilter::act(IReq::get("cityname")));
		$data["areaname"] = trim(IFilter::act(IReq::get("areaname")));
		$data["streetname"] = trim(IFilter::act(IReq::get("streetname")));
		$data["kejian"] = intval(IFilter::act(IReq::get("kejianvalue")));
		$data["uid"] = $uid;
		$data["addtime"] = time();

		if (empty($data["content"])) {
			$this->message("评价内容不能为空");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "wxreplycomment", $data);
		$this->success("success");
	}

	public function saveuserzanjia()
	{
		$data["uid"] = intval(IFilter::act(IReq::get("uid")));
		$data["commentid"] = intval(IFilter::act(IReq::get("commentid")));
		$pingjiaone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment where id =" . $data["commentid"] . " ");

		if (empty($pingjiaone)) {
			$this->message("获取评价对象错误");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "wxpjzan", $data);
		$this->success("success");
	}

	public function saveuserzanjian()
	{
		$data["uid"] = intval(IFilter::act(IReq::get("uid")));
		$data["commentid"] = intval(IFilter::act(IReq::get("commentid")));
		$pingjiaone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment where id =" . $data["commentid"] . " ");

		if (empty($pingjiaone)) {
			$this->message("获取评价对象错误");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "wxpjzan", $data);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "wxpjzan", "commentid ='" . $data["commentid"] . "' and uid = '" . $data["uid"] . "' ");
		$this->success("success");
	}

	public function savejubaowxuser()
	{
		$data["uid"] = intval(IFilter::act(IReq::get("uid")));
		$data["commentid"] = intval(IFilter::act(IReq::get("jubaoid")));
		$pingjiaone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment where id =" . $data["commentid"] . " ");

		if (empty($pingjiaone)) {
			$this->message("获取评价对象错误");
		}

		$getjubaowxuser = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxuserjubao where uid =" . $data["uid"] . " and commentid = " . $data["commentid"] . " ");

		if (!empty($getjubaowxuser)) {
			$this->message("你已经举报过啦~");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "wxuserjubao", $data);
		$this->success("success");
	}

	public function saveshanchuwxuser()
	{
		$uid = intval(IFilter::act(IReq::get("uid")));
		$shanchuid = intval(IFilter::act(IReq::get("shanchuid")));
		$pingjiaone = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment where id =" . $shanchuid . " and uid= " . $uid . " ");

		if (empty($pingjiaone)) {
			$this->message("获取评价对象错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "wxcomment", "uid ='" . $uid . "' and id = '" . $shanchuid . "' ");
		$this->success("success");
	}

	public function wxmsglist()
	{
		$uid = $this->member["uid"];
		$togethersaylist1 = $this->mysql->getarr(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "member  as b  on a.uid = b.uid where a.is_top=0 and a.uid = '" . $uid . "'  order by addtime desc ");
		$togethersaylist = array();

		foreach ($togethersaylist1 as $key => $value ) {
			$value["pingjiazongshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment where parentid  = " . $value["id"] . "  ");
			$value["zongzanshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxpjzan where commentid  = " . $value["id"] . "  ");
			$togethersaylist[] = $value;
		}

		$data["togethersaylist"] = $togethersaylist;
		$systemsaylist = $this->mysql->getarr(" select * from " . Mysite::$app->config["tablepre"] . "wxcomment as a left join " . Mysite::$app->config["tablepre"] . "member  as b  on a.uid = b.uid where a.is_top=1 order by addtime desc ");
		$systemmsg = array();

		foreach ($systemsaylist as $key => $value ) {
			$value["pingjiazongshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxreplycomment where parentid  = " . $value["id"] . "  ");
			$value["zongzanshu"] = $this->mysql->counts(" select * from " . Mysite::$app->config["tablepre"] . "wxpjzan where commentid  = " . $value["id"] . "  ");
			$systemmsg[] = $value;
		}

		$data["systemmsg"] = $systemmsg;
		Mysite::$app->setdata($data);
	}

	public function fabiaozhuti()
	{
		$wxclass = new wx_s();
		$signPackage = $wxclass->getSignPackage();
		$data["signPackage"] = $signPackage;
		Mysite::$app->setdata($data);
	}

	public function collectshopdata()
	{
		$typelx = IFilter::act(IReq::get("typelx"));

		if (!empty($typelx)) {
			if ($typelx == "wm") {
				ICookie::set("shopshowtype", "waimai", 2592000);
				$shopshowtype = "waimai";
			}

			if ($typelx == "mk") {
				ICookie::set("shopshowtype", "market", 2592000);
				$shopshowtype = "market";
			}

			if ($typelx == "yd") {
				ICookie::set("shopshowtype", "dingtai", 2592000);
				$shopshowtype = "dingtai";
			}
		}
		else {
			$shopshowtype = ICookie::get("shopshowtype");
		}

		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$where = "";
		$lng = 0;
		$lat = 0;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$lng = trim($lng);
		$lat = trim($lat);
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$orderarray = array("   sort asc      ");
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$where .= $qsjarray[$qsjid];
		$where .= $qsjarray[$qsjid];
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 " . $where . "    order by " . $orderarray[0] . " limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "  ");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();

		if (is_array($list)) {
			foreach ($list as $keys => $values ) {
				if (0 < $values["id"]) {
					$values["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $values["id"] . "' ");

					if (!empty($values["collect"])) {
						$templist111 = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where cattype = " . $values["shoptype"] . " and  parent_id = 0    order by orderid asc limit 0,1000");
						$attra = array();
						$attra["input"] = 0;
						$attra["img"] = 0;
						$attra["checkbox"] = 0;

						foreach ($templist111 as $key => $vall ) {
							if ($vall["type"] == "input") {
								$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $vall["id"]);
							}
							else if ($vall["type"] == "img") {
								$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $vall["id"]);
							}
							else if ($vall["type"] == "checkbox") {
								$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $vall["id"]);
							}
						}

						if ($values["shoptype"] == 1) {
							$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $values["id"] . "   ");
						}
						else {
							$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $values["id"] . "   ");
						}

						if (!empty($shopdet)) {
							$values = array_merge($values, $shopdet);
							$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
							$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
							$values["opentype"] = $checkinfo["opentype"];
							$values["newstartime"] = $checkinfo["newstartime"];
							$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $values["shoptype"] . " and shopid = " . $values["id"] . "");
							$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
							$checkps = $this->pscost($values, 1, $areaid);
							$values["pscost"] = $checkps["pscost"];
							$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
							$tempmi = $mi;
							$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
							$values["juli"] = $mi;
							$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
							$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
							$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

							if (empty($shopcounts["shuliang"])) {
								$values["ordercount"] = 0;
							}
							else {
								$values["ordercount"] = $shopcounts["shuliang"];
							}

							$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
							$values["cxlist"] = array();

							foreach ($cxinfo as $k1 => $v1 ) {
								if (isset($cxarray[$v1["signid"]])) {
									$v1["imgurl"] = $cxarray[$v1["signid"]];
									$values["cxlist"][] = $v1;
								}
							}

							$zongpoint = $values["point"];
							$zongpointcount = $values["pointcount"];

							if ($zongpointcount != 0) {
								$shopstart = intval(round($zongpoint / $zongpointcount));
							}
							else {
								$shopstart = 0;
							}

							$values["point"] = $shopstart;
							$values["attrdet"] = array();

							foreach ($attrdet as $k => $v ) {
								if ($v["firstattr"] == $attra["input"]) {
									$values["attrdet"]["input"] = $v["value"];
								}
								else if ($v["firstattr"] == $attra["img"]) {
									$values["attrdet"]["img"][] = $v["value"];
								}
								else if ($v["firstattr"] == $attra["checkbox"]) {
									$values["attrdet"]["checkbox"][] = $v["value"];
								}
							}

							$templist[] = $values;
						}
					}
				}
			}
		}

		$data = $templist;
		$datas = json_encode($data);
		echo "showmoreshop(" . $datas . ")";
		exit();
		$this->success($data);
	}

	public function saveshangjia()
	{
		$regagree = IFilter::act(IReq::get("regagree"));

		if (empty($regagree)) {
			$this->message("请阅读入驻协议后勾选接受！");
		}

		$username = IFilter::act(IReq::get("username"));
		$mobile = IFilter::act(IReq::get("mobile"));
		$qq = IFilter::act(IReq::get("qq"));
		$resname = IFilter::act(IReq::get("resname"));
		$addr = IFilter::act(IReq::get("addr"));

		if (empty($username)) {
			$this->message("姓名不能为空！");
		}

		if (!IValidate::len($username, 1, 50)) {
			$this->message("member_addresslength");
		}

		if (empty($mobile)) {
			$this->message("手机号不能为空！");
		}

		if (!IValidate::phone($mobile)) {
			$this->message("errphone");
		}

		if (empty($resname)) {
			$this->message("店铺名称不能为空！");
		}

		if (!IValidate::len($resname, 1, 50)) {
			$this->message("shop_shopnamelenth");
		}

		if (empty($addr)) {
			$this->message("店铺的详细地址不能为空！");
		}

		if (!IValidate::len($addr, 1, 255)) {
			$this->message("shop_addresslenth");
		}

		if (Mysite::$app->config["allowedcode"] == 1) {
			$Captcha = IFilter::act(IReq::get("Captcha"));
			if (empty($Captcha) || ($Captcha == "输入验证码")) {
				$this->message("验证码不能为空！");
			}

			if ($Captcha != ICookie::get("Captcha")) {
				$this->message("member_codeerr");
			}
		}

		$arr["username"] = $username;
		$arr["phone"] = $mobile;
		if (empty($qq) || ($qq == "请输入您的QQ(选填)")) {
			$arr["qq"] = "";
		}
		else {
			$arr["qq"] = $qq;
		}

		$arr["shopname"] = $resname;
		$arr["shopaddress"] = $addr;
		$arr["addtime"] = time();
		$arr["is_pass"] = "0";
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "messages", $arr);
		$this->success("shangjiasuccess");
	}

	public function login()
	{
		if (0 < $this->member["uid"]) {
			$link = IUrl::creatUrl("wxsite/member");
			$this->message("", $link);
		}
	}

	public function reg()
	{
		if (0 < $this->member["uid"]) {
			$link = IUrl::creatUrl("wxsite/member");
			$this->message("", $link);
		}
	}

	public function loginout()
	{
		$this->memberCls->loginout();
		$link = IUrl::creatUrl("wxsite/index");
		$this->message("", $link);
	}

	public function checkwxweb()
	{
		if (!strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger")) {
			if ($this->member["uid"] <= 0) {
				$link = IUrl::creatUrl("wxsite/login");
				$this->message("", $link);
			}
		}
	}

	public function checkbackinfo()
	{
		if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger")) {
			return true;
		}
		else {
			return false;
		}
	}

	public function shophui()
	{
		$this->checkwxuser();
		$shopsearch = IFilter::act(IReq::get("search_input"));
		$data["search_input"] = $shopsearch;
		$data["areaid"] = $areaid;
		Mysite::$app->setdata($data);
	}

	public function shophuilistdata()
	{
		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$where = "";
		$shopsearch = IFilter::act(IReq::get("search_input"));
		$shopsearch = urldecode($shopsearch);

		if (!empty($shopsearch)) {
			$where = " and b.shopname like '%" . $shopsearch . "%' ";
		}

		$areaid = intval(IFilter::act(IReq::get("areaid")));
		$catid = intval(IReq::get("catid"));
		$order = intval(IReq::get("order"));
		$order = (in_array($order, array(1, 2, 3)) ? $order : 0);
		$qsjid = intval(IReq::get("qsjid"));
		$qsjid = (in_array($qsjid, array(1, 2, 3)) ? $qsjid : 0);
		logwrite("获取店铺数");
		$orderarray = array(" sort asc ", " sort asc ", "limitcost asc", "is_com desc");
		$qsjarray = array("  ", "and limitcost < 5 ", "and limitcost >= 5 and limitcost <= 10", "and limitcost > 10");
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
		$attra["input"] = 0;
		$attra["img"] = 0;
		$attra["checkbox"] = 0;

		foreach ($templist as $key => $value ) {
			if ($value["type"] == "input") {
				$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $value["id"]);
			}
			else if ($value["type"] == "img") {
				$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $value["id"]);
			}
			else if ($value["type"] == "checkbox") {
				$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $value["id"]);
			}
		}

		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$where .= $qsjarray[$qsjid];
		logwrite("getdata sql:" . $where);
		$where .= "and  is_hui=1 and is_shophui=1";
		$shopxinxi = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 and " . time() . " < endtime and is_open =1   limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "");
		$list = array();

		foreach ($shopxinxi as $key => $value ) {
			$shoplists = array();

			if ($value["shoptype"] == 0) {
				$shopfast = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid = " . $value["id"] . " and  is_hui=1 and is_shophui=1 ");

				if (!empty($shopfast)) {
					$shoplists = array_merge($value, $shopfast);
					$list[] = $shoplists;
				}
			}
			else {
				$shopmarket = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid = " . $value["id"] . " and  is_hui=1 and is_shophui=1");

				if (!empty($shopmarket)) {
					$shoplists = array_merge($value, $shopmarket);
					$list[] = $shoplists;
				}
			}
		}

		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();

		if (is_array($list)) {
			foreach ($list as $keys => $values ) {
				if (0 < $values["id"]) {
					$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
					$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
					$values["opentype"] = $checkinfo["opentype"];
					$values["newstartime"] = $checkinfo["newstartime"];
					$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = " . $values["id"] . "");
					$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
					$checkps = $this->pscost($values, 1, $areaid);
					$values["pscost"] = $checkps["pscost"];
					$values["shopshui"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophui where  status = 1 and shopid = " . $values["id"] . "");
					$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
					$values["cxlist"] = array();

					foreach ($cxinfo as $k1 => $v1 ) {
						if (isset($cxarray[$v1["signid"]])) {
							$v1["imgurl"] = $cxarray[$v1["signid"]];
							$values["cxlist"][] = $v1;
						}
					}

					$zongpoint = $values["point"];
					$zongpointcount = $values["pointcount"];

					if ($zongpointcount != 0) {
						$shopstart = intval(round($zongpoint / $zongpointcount));
					}
					else {
						$shopstart = 0;
					}

					$values["point"] = $shopstart;
					$values["attrdet"] = array();

					foreach ($attrdet as $k => $v ) {
						if ($v["firstattr"] == $attra["input"]) {
							$values["attrdet"]["input"] = $v["value"];
						}
						else if ($v["firstattr"] == $attra["img"]) {
							$values["attrdet"]["img"][] = $v["value"];
						}
						else if ($v["firstattr"] == $attra["checkbox"]) {
							$values["attrdet"]["checkbox"][] = $v["value"];
						}
					}

					$templist[] = $values;
				}
			}
		}

		$data = $templist;
		$this->success($data);
	}

	public function subpayhui()
	{
		$orderid = intval(IReq::get("orderid"));
		$userid = (empty($this->member["uid"]) ? 0 : $this->member["uid"]);
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			$this->message("闪慧买单获取失败");
		}

		if ($orderid < 1) {
			$this->message("订单获取失败");
		}

		$order = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophuiorder where uid='" . $this->member["uid"] . "' and id = " . $orderid . "");

		if (empty($order)) {
			$this->message("订单获取失败");
		}

		$data["order"] = $order;
		if ($this->checkbackinfo() && ($order["paystatus"] != 1)) {
			$wxopenid = ICookie::get("wxopenid");
			$weixindir = hopedir . "/plug/pay/weixin/";
			require_once $weixindir . "lib/WxPay.Api.php";
			require_once $weixindir . "WxPay.JsApiPay.php";
			$tools = new JsApiPay();
			$openId = $tools->GetOpenid();
			logwrite("微信openid：" . $openId);
			$input = new WxPayUnifiedOrder();
			$input->SetBody("支付闪慧买单");
			$input->SetAttach("a");
			$input->SetOut_trade_no("a_" . $orderid);
			$input->SetTotal_fee($order["sjcost"] * 100);
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetTimeStamp(time());
			$input->SetGoods_tag("闪慧");
			$input->SetNotify_url(Mysite::$app->config["siteurl"] . "/plug/pay/weixin/notify.php");
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
			$ordermm = WxPayApi::unifiedOrder($input);
			$jsApiParameters = $tools->GetJsApiParameters($ordermm);
			$data["wxdata"] = $jsApiParameters;
		}

		Mysite::$app->setdata($data);
		Mysite::$app->setAction("subpayhui");
	}

	public function shophuishow()
	{
		$id = intval(IReq::get("id"));
		$list = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $id . "'");

		if (empty($list)) {
			$this->message("获取商家失败");
		}

		if ($list["shoptype"] == 0) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopfast as b on a.id = b.shopid where a.id='" . $id . "'");
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopmarket as b on a.id = b.shopid where a.id='" . $id . "'");
		}

		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function huisubshow()
	{
		$id = intval(IReq::get("id"));
		$list = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $id . "'");
		$data["shopid"] = $list["id"];

		if (empty($list)) {
			$this->message("获取商家失败");
		}

		if ($list["shoptype"] == 0) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopfast as b on a.id = b.shopid where a.id='" . $id . "'");
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopmarket as b on a.id = b.shopid where a.id='" . $id . "'");
		}

		$weeknum = date("w");
		$nowtime = time();

		if ($shopinfo["is_shophui"] == 1) {
			$shophuiinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophui where shopid = '" . $shopinfo["id"] . "' and status=1 ");

			if (!empty($shophuiinfo)) {
				if (!empty($shophuiinfo["limitweek"]) && !empty($shophuiinfo["limittimes"])) {
					$weekarray = explode(",", $shophuiinfo["limitweek"]);
					$datey = date("Y-m-d", $nowtime);
					$info = explode(",", $shophuiinfo["limittimes"]);
					$find = false;

					foreach ($info as $kc => $val ) {
						if (!empty($val)) {
							$checkinfo = explode("-", $val);

							if (!empty($checkinfo[1])) {
								$time1 = strtotime($datey . " " . $checkinfo[0] . ":00");
								$time2 = strtotime($datey . " " . $checkinfo[1] . ":00");
								if (($time1 < $nowtime) && ($nowtime < $time2)) {
									$find = true;
									break;
								}
							}
						}
					}

					if (in_array($weeknum, $weekarray) && ($shophuiinfo["starttime"] <= $nowtime) && ($nowtime <= $shophuiinfo["endtime"]) && ($find == true)) {
						$is_shophui = 1;
					}
					else {
						$is_shophui = 0;
					}
				}
				else {
					$is_shophui = 1;
				}
			}
			else {
				$shophuiinfo = "";
				$is_shophui = 0;
			}
		}
		else {
			$shophuiinfo = "";
			$is_shophui = 0;
		}

		$data["is_shophui"] = $is_shophui;
		$data["shophuiinfo"] = $shophuiinfo;
		$data["shopinfo"] = $shopinfo;
		Mysite::$app->setdata($data);
	}

	public function makeshophuiorder()
	{
		$uid = $this->member["uid"];

		if (0 < $uid) {
			$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "' ");
			$username = $memberinfo["username"];
		}

		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$huiid = intval(IFilter::act(IReq::get("huiid")));
		$xfcost = IFilter::act(IReq::get("xfcost"));
		$buyorderphone = trim(IFilter::act(IReq::get("buyorderphone")));
		$yhcost = IFilter::act(IReq::get("yhcost"));
		$sjcost = IFilter::act(IReq::get("sjcost"));
		$paytype = intval(IFilter::act(IReq::get("paytype")));

		if (empty($xfcost)) {
			$this->message("消费金额为空");
		}

		if (empty($buyorderphone)) {
			$this->message("买单人联系电话不能为空");
		}

		if (!IValidate::suremobi($buyorderphone)) {
			$this->message("买单人联系电话错误");
		}

		if (empty($sjcost)) {
			$this->message("买单金额为空");
		}

		$shopone = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id='" . $shopid . "' ");

		if (empty($shopone)) {
			$this->message("获取商户信息失败");
		}

		if ($shopone["shoptype"] == 0) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopfast as b on a.id = b.shopid where a.id='" . $shopid . "' ");
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop as a left join " . Mysite::$app->config["tablepre"] . "shopmarket as b on a.id = b.shopid where a.id='" . $shopid . "' ");
		}

		if ($shopinfo["is_shophui"] == 1) {
			if (0 < $huiid) {
				if (empty($yhcost)) {
					$this->message("优惠金额为空");
				}

				$shophuiinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shophui where id='" . $huiid . "' ");

				if (!empty($shophuiinfo)) {
					if ($shophuiinfo["controltype"] == 2) {
						$checkcost = $shophuiinfo["mjlimitcost"];

						if ($checkcost <= $xfcost) {
							$checkhuicost = floor($xfcost / $checkcost) * $shophuiinfo["controlcontent"];

							if ($checkhuicost != $yhcost) {
								$this->message("优惠金额获取失败");
							}
						}
						else {
							$this->messqge("消费金额未达到条件");
						}

						$data["huilimitcost"] = $shophuiinfo["mjlimitcost"];
					}

					if ($shophuiinfo["controltype"] == 3) {
						$checkcost = $shophuiinfo["limitzhekoucost"];

						if ($checkcost <= $xfcost) {
							$checkhuicost = $xfcost * ((100 - $shophuiinfo["controlcontent"]) / 100);

							if ($checkhuicost != $yhcost) {
								$this->message("优惠金额获取失败");
							}
						}
						else {
							$this->messqge("消费金额未达到条件");
						}

						$data["huilimitcost"] = $shophuiinfo["limitzhekoucost"];
					}

					$data["huiid"] = $shophuiinfo["id"];
					$data["huiname"] = $shophuiinfo["name"];
					$data["huitype"] = $shophuiinfo["controltype"];
					$data["huicost"] = $shophuiinfo["controlcontent"];
				}
				else {
					$data["huiid"] = "";
					$data["huiname"] = "";
					$data["huitype"] = "";
					$data["huilimitcost"] = "";
					$data["huicost"] = "";
				}
			}
		}

		$data["uid"] = $uid;
		$data["username"] = $username;
		$data["dno"] = time() . rand(1000, 9999);
		$data["shopid"] = $shopid;
		$data["shopname"] = $shopinfo["shopname"];
		$data["xfcost"] = $xfcost;
		$data["buyorderphone"] = $buyorderphone;
		$data["yhcost"] = $yhcost;
		$data["sjcost"] = $sjcost;

		if ($shopinfo["is_shgift"] == 1) {
			$data["givejifen"] = floor($sjcost / $shopinfo["sendgift"]);
		}
		else {
			$data["givejifen"] = 0;
		}

		$data["paytype"] = $paytype;
		$data["paystatus"] = 0;
		$data["status"] = 0;
		$data["addtime"] = time();
		$data["completetime"] = 0;
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "shophuiorder", $data);
		$orderid = $this->mysql->insertid();
		$this->success($orderid);
	}

	public function locationshop1111111111()
	{
		ICookie::clear("myaddress");
		$link = IUrl::creatUrl("wxsite/shoplist");
		$this->message("", $link);
	}

	public function getsearmap()
	{
		$searchvalue = trim(IFilter::act(IReq::get("searchvalue")));
		$content = file_get_contents("http://api.map.baidu.com/place/v2/search?ak=" . Mysite::$app->config["baidumapkey"] . "&output=json&query=" . $searchvalue . "&page_size=20&page_num=0&scope=1&region=" . Mysite::$app->config["cityname"]);
		$list = json_decode($content, true);
		$backdata = array();

		if ($list["message"] == "ok") {
			if (1 < $list["total"]) {
				foreach ($list["results"] as $key => $value ) {
					$temp["address"] = $value["name"];
					$temp["detaddress"] = $value["address"];
					$temp["lng"] = $value["location"]["lng"];
					$temp["lat"] = $value["location"]["lat"];
					$temp["parent_id"] = 0;
					$backdata[] = $temp;
				}
			}
		}

		$datas = json_encode($backdata);
		echo "showaddresslist(" . $datas . ")";
		exit();
		$this->success($backdata);
	}

	public function forgetpwd()
	{
		$regestercode = Mysite::$app->config["regestercode"];
		$checkcode = ICookie::get("regphonecode");
		$checkphone = ICookie::get("regphone");
		$checktime = ICookie::get("regtime");

		if (empty($regestercode)) {
			echo "noshow('不需要验证CODE')";
			exit();
		}

		if (!empty($checkcode)) {
			$backtime = $checktime - time();

			if (0 < $backtime) {
				echo "showsend('" . $checkphone . "'," . $backtime . ")";
				exit();
			}
		}

		if (!empty($this->member["uid"])) {
			echo "noshow('已登陆')";
			exit();
		}

		$phone = IFilter::act(IReq::get("phone"));

		if (empty($phone)) {
			echo "noshow('请填写手机号')";
			exit();
		}

		if (!IValidate::suremobi($phone)) {
			echo "noshow('手机格式错误')";
			exit();
		}

		$userinfoarray = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "member where phone='" . $phone . "' ");

		if (1 < count($userinfoarray)) {
			echo "noshow('此手机号绑定多个用户！')";
			exit();
		}

		$userinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where phone='" . $phone . "' ");

		if (empty($userinfo)) {
			echo "noshow('未找到此手机号的用户！')";
			exit();
		}

		$makecode = mt_rand(10000, 99999);
		$sendmobile = new mobile();
		$contents = "【" . Mysite::$app->config["sitename"] . "】您的验证码为：" . $makecode;
		$APIServer = "http://www.tingche.com/sendtophone.php?apiuid=" . Mysite::$app->config["apiuid"];
		$weblink = $APIServer . "&key=" . trim(Mysite::$app->config["sms86ac"]) . "&code=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&msgcontent=" . urlencode($contents) . "";
		$contentcccc = file_get_contents($weblink);
		logwrite("找回密码验证短信发送:" . $contentcccc);
		ICookie::set("getbackphonecode", $makecode, 90);
		ICookie::set("getbackphone", $phone, 90);
		$longtime = time() + 90;
		ICookie::set("regtime", $longtime, 90);
		echo "showsend('" . $phone . "',90,'" . $userinfo["uid"] . "')";
		exit();
	}

	public function fornextzhpwd()
	{
		$pwdyzm = intval(IFilter::act(IReq::get("pwdyzm")));
		$phoneyan = IFilter::act(IReq::get("phone"));
		$datauid = intval(IFilter::act(IReq::get("datauid")));

		if (empty($phoneyan)) {
			$this->message("请输入您的手机号");
		}

		$userinfoarray = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "member where phone='" . $phoneyan . "' ");

		if (1 < count($userinfoarray)) {
			$this->message("此手机号绑定多个用户！");
		}

		$userinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where phone='" . $phoneyan . "'   ");

		if (empty($userinfo)) {
			$this->message("未找到此手机号的用户！");
		}

		if (empty($pwdyzm)) {
			$this->message("请输入您收到的验证码");
		}

		if (!empty($phoneyan)) {
			$checkcode = ICookie::get("getbackphonecode");

			if ($pwdyzm != $checkcode) {
				$this->message("验证码错误");
			}
		}

		$lastuserinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $datauid . "'   ");

		if (empty($lastuserinfo)) {
			$this->message("未找到此用户！");
		}

		$this->success($lastuserinfo["uid"]);
	}

	public function forgetnextpwd()
	{
		$uid = intval(IFilter::act(IReq::get("id")));
		$data["uid"] = $uid;
		$userinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "'   ");

		if (empty($userinfo)) {
			$this->message("获取用户信息失败！");
		}

		Mysite::$app->setdata($data);
	}

	public function updatepwd()
	{
		$uid = intval(IFilter::act(IReq::get("uid")));
		$userinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "'   ");

		if (empty($userinfo)) {
			$this->message("获取用户信息失败！");
		}

		$pwd = IFilter::act(IReq::get("pwd"));
		$repwd = IFilter::act(IReq::get("repwd"));

		if (!IValidate::len($pwd, 6, 20)) {
			$this->message("member_pwdlen6to20");
		}

		if ($pwd != $repwd) {
			$this->message("member_twopwdnoequale");
		}

		$data["password"] = md5($pwd);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $data, "uid='" . $uid . "'");
		$this->success("success");
	}

	public function costlog()
	{
		$uid = $this->member["uid"];

		if (empty($uid)) {
			$this->message("获取用户信息失败");
		}

		$costloglist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "memberlog where userid = " . $uid . " and  type = 2  order by addtime desc ");
		$data["costloglist"] = $costloglist;
		Mysite::$app->setdata($data);
	}

	public function subbalancepay()
	{
		$cost = intval(IFilter::act(IReq::get("cost")));
		$data["cost"] = $cost;
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist where type = 0 or type=2  order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[$value["loginname"]] = $value["logindesc"];
			}
		}

		$data["paylist"] = $paylist;

		if ($this->checkbackinfo()) {
			if (($order["paystatus"] == 0) && ($order["paytype"] == 1)) {
				$wxopenid = ICookie::get("wxopenid");
				$weixindir = hopedir . "/plug/pay/weixin/";
				require_once $weixindir . "lib/WxPay.Api.php";
				require_once $weixindir . "WxPay.JsApiPay.php";
				$tools = new JsApiPay();
				$openId = $tools->GetOpenid();
				$dno = "acount_" . $this->member["uid"];
				$acountid = "acount_" . time();
				$input = new WxPayUnifiedOrder();
				$input->SetBody("支付订单" . $dno);
				$input->SetAttach($dno);
				$input->SetOut_trade_no($acountid);
				$input->SetTotal_fee($cost * 100);
				$input->SetTime_start(date("YmdHis"));
				$input->SetTime_expire(date("YmdHis", time() + 600));
				$input->SetTimeStamp(time());
				$input->SetGoods_tag("在线充值");
				$input->SetNotify_url(Mysite::$app->config["siteurl"] . "/plug/pay/weixin/notify.php");
				$input->SetTrade_type("JSAPI");
				$input->SetOpenid($openId);
				$ordermm = WxPayApi::unifiedOrder($input);
				$jsApiParameters = $tools->GetJsApiParameters($ordermm);
				$data["wxdata"] = $jsApiParameters;
			}
		}

		Mysite::$app->setdata($data);
	}

	public function catefoods()
	{
		$weekji = date("w");
		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$parentid = intval(IFilter::act(IReq::get("parentid")));
		$curcateid = intval(IFilter::act(IReq::get("curcateid")));
		$shoptype = intval(IFilter::act(IReq::get("shoptype")));

		if ($shoptype == 1) {
			$cateinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = " . $curcateid . " and shopid = " . $shopid . " ");
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $cateinfo["shopid"] . " ");
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid = " . $shopinfo["id"] . " ");
		}
		else {
			$cateinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodstype where id = " . $curcateid . " ");
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $cateinfo["shopid"] . " ");
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where shopid = " . $shopinfo["id"] . " ");
		}

		$data["shopinfo"] = $shopinfo;
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopdet["is_orderbefore"], $nowhour);
		$data["opentype"] = $checkinfo["opentype"];
		$type = intval(IFilter::act(IReq::get("type")));
		$catefoodslist = array();
		$detaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where typeid='" . $curcateid . "' and is_waisong = 1 and shopid = " . $shopid . " and    FIND_IN_SET( " . $weekji . " , `weeks` )    order by good_order asc ");

		foreach ($detaa as $keyq => $valq ) {
			if ($valq["is_cx"] == 1) {
				$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $valq["id"] . "  ");
				$newdata = getgoodscx($valq["cost"], $cxdata);
				$valq["zhekou"] = $newdata["zhekou"];
				$valq["is_cx"] = $newdata["is_cx"];
				$valq["cost"] = $newdata["cost"];
			}

			$catefoodslist[] = $valq;
		}

		$data["cateinfo"] = $cateinfo;
		$data["shopdet"] = $shopdet;
		$data["catefoodslist"] = $catefoodslist;
		Mysite::$app->setdata($data);
	}

	public function mkcatefoods()
	{
		$weekji = date("w");
		$shopid = intval(IFilter::act(IReq::get("shopid")));
		$curcateid = intval(IFilter::act(IReq::get("curcateid")));
		$shoptype = intval(IFilter::act(IReq::get("shoptype")));
		$type = intval(IFilter::act(IReq::get("type")));

		if ($shoptype == 1) {
			$parentid = intval(IFilter::act(IReq::get("parentid")));

			if (!empty($curcateid)) {
				$where = " and  id = " . $curcateid . "   ";
			}
			else {
				$where = "";
			}

			$soncatelist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where parent_id = " . $parentid . "  " . $where . " order by orderid asc ");

			foreach ($soncatelist as $key => $value ) {
				$temparray = array();
				$detaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where typeid='" . $value["id"] . "'  and shoptype = " . $shoptype . "  and shopid = " . $shopid . "  and    FIND_IN_SET( " . $weekji . " , `weeks` )    order by good_order asc ");

				foreach ($detaa as $keyq => $valq ) {
					if ($valq["is_cx"] == 1) {
						$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $valq["id"] . "  ");
						$newdata = getgoodscx($valq["cost"], $cxdata);
						$valq["zhekou"] = $newdata["zhekou"];
						$valq["is_cx"] = $newdata["is_cx"];
						$valq["cost"] = $newdata["cost"];
					}

					$temparray[] = $valq;
					$value["det"] = $temparray;
				}

				$catefoodslist[] = $value;
			}

			$parentcateinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = " . $parentid . " ");
			$cateinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where id = " . $curcateid . " ");
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop where id=" . $parentcateinfo["shopid"] . " ");
			$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where shopid = " . $shopinfo["id"] . " ");
		}

		$data["shopinfo"] = $shopinfo;
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopdet["is_orderbefore"], $nowhour);
		$data["opentype"] = $checkinfo["opentype"];
		$data["cateinfo"] = $cateinfo;
		$data["shopdet"] = $shopdet;
		$data["catefoodslist"] = $catefoodslist;
		Mysite::$app->setdata($data);
	}

	public function search()
	{
		$searchname = IFilter::act(IReq::get("searchname"));
		$data["searchname"] = $searchname;
		$uid = $this->member["uid"];

		if (0 < $uid) {
			$searchloglist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "searchlog where uid = " . $uid . " order by searchtime desc limit 0,10 ");
			$data["searchloglist"] = $searchloglist;
			Mysite::$app->setdata($data);
		}

		Mysite::$app->setdata($data);
	}

	public function searchresult()
	{
		$searchname = IFilter::act(IReq::get("searchname"));
		$uid = $this->member["uid"];

		if (0 < $uid) {
			$sdata["uid"] = $uid;
			$sdata["searchval"] = $searchname;
			$sdata["searchtime"] = time();
			$checksearch = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "searchlog where searchval = '" . $searchname . "' ");

			if (empty($checksearch)) {
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "searchlog", $sdata);
			}
		}

		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$where = "";
		$shopsearch = IFilter::act(IReq::get("searchname"));
		$shopsearch = urldecode($shopsearch);

		if (!empty($shopsearch)) {
			$where = " and shopname like '%" . $shopsearch . "%' ";
		}

		$lng = 0;
		$lat = 0;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$lng = trim($lng);
		$lat = trim($lat);
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 " . $where . " ");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();

		if (is_array($list)) {
			foreach ($list as $keys => $values ) {
				if (0 < $values["id"]) {
					$templist111 = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where cattype = " . $values["shoptype"] . " and  parent_id = 0    order by orderid asc limit 0,1000");
					$attra = array();
					$attra["input"] = 0;
					$attra["img"] = 0;
					$attra["checkbox"] = 0;

					foreach ($templist111 as $key => $vall ) {
						if ($vall["type"] == "input") {
							$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $vall["id"]);
						}
						else if ($vall["type"] == "img") {
							$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $vall["id"]);
						}
						else if ($vall["type"] == "checkbox") {
							$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $vall["id"]);
						}
					}

					if ($values["shoptype"] == 1) {
						$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $values["id"] . "   ");
					}
					else {
						$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $values["id"] . "   ");
					}

					$values = array_merge($values, $shopdet);
					$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
					$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
					$values["opentype"] = $checkinfo["opentype"];
					$values["newstartime"] = $checkinfo["newstartime"];
					$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $values["shoptype"] . " and shopid = " . $values["id"] . "");
					$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
					$checkps = $this->pscost($values, 1, $areaid);
					$values["pscost"] = $checkps["pscost"];
					$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
					$tempmi = $mi;
					$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
					$values["juli"] = $mi;
					$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
					$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
					$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

					if (empty($shopcounts["shuliang"])) {
						$values["ordercount"] = 0;
					}
					else {
						$values["ordercount"] = $shopcounts["shuliang"];
					}

					$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
					$values["cxlist"] = array();

					foreach ($cxinfo as $k1 => $v1 ) {
						if (isset($cxarray[$v1["signid"]])) {
							$v1["imgurl"] = $cxarray[$v1["signid"]];
							$values["cxlist"][] = $v1;
						}
					}

					$zongpoint = $values["point"];
					$zongpointcount = $values["pointcount"];

					if ($zongpointcount != 0) {
						$shopstart = intval(round($zongpoint / $zongpointcount));
					}
					else {
						$shopstart = 0;
					}

					$values["point"] = $shopstart;
					$values["attrdet"] = array();

					foreach ($attrdet as $k => $v ) {
						if ($v["firstattr"] == $attra["input"]) {
							$values["attrdet"]["input"] = $v["value"];
						}
						else if ($v["firstattr"] == $attra["img"]) {
							$values["attrdet"]["img"][] = $v["value"];
						}
						else if ($v["firstattr"] == $attra["checkbox"]) {
							$values["attrdet"]["checkbox"][] = $v["value"];
						}
					}

					$templist[] = $values;
				}
			}
		}

		$data["shopsearchlist"] = $templist;
		$weekji = date("w");
		$goodwhere = "";
		$goodssearch = IFilter::act(IReq::get("searchname"));
		$goodssearch = urldecode($goodssearch);

		if (!empty($goodssearch)) {
			$goodlistwhere = " and name like '%" . $goodssearch . "%' ";
		}

		$lng = 0;
		$lat = 0;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$goodwhere = (empty($goodwhere) ? "   and  SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradiusa`*0.01094) " : $goodwhere . " and SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradiusa`*0.01094) ");
		$lng = trim($lng);
		$lat = trim($lat);
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$templist11 = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");
		$attra["input"] = 0;
		$attra["img"] = 0;
		$attra["checkbox"] = 0;

		foreach ($templist11 as $key => $value ) {
			if ($value["type"] == "input") {
				$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $value["id"]);
			}
			else if ($value["type"] == "img") {
				$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $value["id"]);
			}
			else if ($value["type"] == "checkbox") {
				$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $value["id"]);
			}
		}

		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")));
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 " . $goodwhere . " ");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$goodssearchlist = array();
		$cxclass = new sellrule();

		if (is_array($list)) {
			foreach ($list as $keys => $vatt ) {
				if (0 < $vatt["id"]) {
					$detaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $vatt["id"] . "'  and shoptype = " . $vatt["shoptype"] . "  and    FIND_IN_SET( " . $weekji . " , `weeks` )  " . $goodlistwhere . "   order by good_order asc ");

					if (!empty($detaa)) {
						foreach ($detaa as $keyq => $valq ) {
							if ($valq["is_cx"] == 1) {
								$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $valq["id"] . "  ");
								$newdata = getgoodscx($valq["cost"], $cxdata);
								$valq["zhekou"] = $newdata["zhekou"];
								$valq["is_cx"] = $newdata["is_cx"];
								$valq["cost"] = $newdata["cost"];
							}

							if ($shoptype == 1) {
								$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $valq["shopid"] . "   ");
							}
							else {
								$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $valq["shopid"] . "   ");
							}

							$checkinfo = $this->shopIsopen($vatt["is_open"], $vatt["starttime"], $shopdet["is_orderbefore"], $nowhour);
							$valq["opentype"] = $checkinfo["opentype"];
							$temparray[] = $valq;
							$vakk = $temparray;
						}
					}

					$goodssearchlist = $vakk;
				}
			}
		}

		$data["goodssearchlist"] = $goodssearchlist;
		Mysite::$app->setdata($data);
	}

	public function qkmemsearchlog()
	{
		$uid = $this->member["uid"];

		if (0 < $uid) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "searchlog", "uid ='" . $uid . "'");
			$this->success("success");
		}
		else {
			$this->message("获取用户信息失败");
		}
	}

	public function pthelpme()
	{
		$pttype = intval(IReq::get("pttype"));
		$data["pttype"] = $pttype;
		$data["ptsetinfo"] = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "paotuiset   ");
		$postdate = $data["ptsetinfo"]["postdate"];
		$befortime = $data["ptsetinfo"]["pt_orderday"];
		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($postdate) ? unserialize($postdate) : array());
		$data["pstimelist"] = array();
		$checknow = time();
		$whilestatic = $befortime;
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

		Mysite::$app->setdata($data);
	}

	public function specialpage()
	{
		$id = intval(IReq::get("id"));
		$data["id"] = $id;
		$ztyinfo = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "specialpage where id = " . $id . "  ");
		$data["ztyinfo"] = $ztyinfo;
		$data["addressname"] = ICookie::get("addressname");
		if (($ztyinfo["is_custom"] == 1) && ($ztyinfo["showtype"] == 0) && (5 < $ztyinfo["cx_type"])) {
			if ($ztyinfo["cx_type"] == 6) {
				$link = IUrl::creatUrl("wxsite/waimai");
			}

			if ($ztyinfo["cx_type"] == 7) {
				$link = IUrl::creatUrl("wxsite/marketlist");
			}

			if ($ztyinfo["cx_type"] == 8) {
				$link = IUrl::creatUrl("wxsite/dingtai");
			}

			if ($ztyinfo["cx_type"] == 9) {
				$link = IUrl::creatUrl("wxsite/paotui");
			}

			$this->message("", $link);
		}

		$speciallist = $this->getztyshowlist($ztyinfo["is_custom"], $ztyinfo["showtype"], $ztyinfo["cx_type"], $ztyinfo["listids"]);
		$data["speciallist"] = $speciallist;
		Mysite::$app->setdata($data);
	}

	public function specialpagelistdata()
	{
		$id = intval(IReq::get("id"));
		$ztyinfo = $this->mysql->select_one(" select * from " . Mysite::$app->config["tablepre"] . "specialpage where id = " . $id . "  ");
		$data["ztyinfo"] = $ztyinfo;
		$data["addressname"] = ICookie::get("addressname");
		$speciallist = $this->getztyshowlist($ztyinfo["is_custom"], $ztyinfo["showtype"], $ztyinfo["cx_type"], $ztyinfo["listids"]);
		$data["speciallist"] = $speciallist;
		$datas = json_encode($data["speciallist"]);

		if ($ztyinfo["showtype"] == 0) {
			echo "showmorespeciallist(" . $datas . ")";
		}

		if ($ztyinfo["showtype"] == 1) {
			echo "showgoodsspeciallist(" . $datas . ")";
		}

		exit();
		$this->success($data);
		$this->success($backdata);
	}

	public function getztyshowlist($is_custom, $showtype, $cx_type, $listids)
	{
		$cxsignlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type='cx' order by id desc limit 0, 100");
		$cxarray = array();

		foreach ($cxsignlist as $key => $value ) {
			$cxarray[$value["id"]] = $value["imgurl"];
		}

		$weekji = date("w");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$cxclass = new sellrule();
		$where = "";
		$lng = 0;
		$lat = 0;
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$lng = trim($lng);
		$lat = trim($lat);
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 10);

		if ($showtype == 0) {
			if ($is_custom == 0) {
				if (!empty($listids)) {
					$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where id in (" . $listids . ") " . $where . "  order by sort asc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . "  ");
				}
				else {
					$list = array();
				}
			}

			if ($is_custom == 1) {
				switch ($cx_type) {
				case $cx_type:
					$ztywhere = "  and  is_recom = 1   ";
					$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 " . $ztywhere . "  " . $where . "   order by sort asc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
					break;

				case $cx_type:
					$list = $this->getdycxshops(2);
					break;

				case $cx_type:
					$list = $this->getdycxshops(3);
					break;

				case $cx_type:
					$list = $this->getdycxshops(4);
					break;

				case $cx_type:
					$list = $this->getdycxshops(1);
					break;

				default:
					$list = array();
					break;
				}
			}

			if (is_array($list)) {
				foreach ($list as $keys => $values ) {
					if (0 < $values["id"]) {
						$templist111 = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where cattype = " . $values["shoptype"] . " and  parent_id = 0    order by orderid asc limit 0,1000");
						$attra = array();
						$attra["input"] = 0;
						$attra["img"] = 0;
						$attra["checkbox"] = 0;

						foreach ($templist111 as $key => $vall ) {
							if ($vall["type"] == "input") {
								$attra["input"] = (0 < $attra["input"] ? $attra["input"] : $vall["id"]);
							}
							else if ($vall["type"] == "img") {
								$attra["img"] = (0 < $attra["img"] ? $attra["img"] : $vall["id"]);
							}
							else if ($vall["type"] == "checkbox") {
								$attra["checkbox"] = (0 < $attra["checkbox"] ? $attra["checkbox"] : $vall["id"]);
							}
						}

						$zongpoint = $values["point"];
						$zongpointcount = $values["pointcount"];

						if ($zongpointcount != 0) {
							$values["shopstart"] = intval(round($zongpoint / $zongpointcount));
						}
						else {
							$values["shopstart"] = 0;
						}

						if ($values["shoptype"] == 1) {
							$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $values["id"] . "   ");
						}
						else {
							$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $values["id"] . "   ");
						}

						if (!empty($shopdet)) {
							$values = array_merge($values, $shopdet);
						}

						$values["shoplogo"] = (empty($values["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $values["shoplogo"]);
						$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
						$values["opentype"] = $checkinfo["opentype"];
						$values["newstartime"] = $checkinfo["newstartime"];
						$attrdet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = " . $values["shoptype"] . " and shopid = " . $values["id"] . "");
						$cxclass->setdata($values["id"], 1000, $values["shoptype"]);
						$checkps = $this->pscost($values, 1, $areaid);
						$values["pscost"] = $checkps["pscost"];
						$mi = $this->GetDistance($lat, $lng, $values["lat"], $values["lng"], 1);
						$tempmi = $mi;
						$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
						$values["juli"] = $mi;
						$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
						$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("$firstday +1 month -1 day")));
						$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $values["id"] . "");

						if (empty($shopcounts["shuliang"])) {
							$values["ordercount"] = 0;
						}
						else {
							$values["ordercount"] = $shopcounts["shuliang"];
						}

						$cxinfo = $this->mysql->getarr("select name,id,signid from " . Mysite::$app->config["tablepre"] . "rule where   shopid = " . $values["id"] . " and status = 1 and starttime  < " . time() . " and endtime > " . time() . " ");
						$values["cxlist"] = array();

						foreach ($cxinfo as $k1 => $v1 ) {
							if (isset($cxarray[$v1["signid"]])) {
								$v1["imgurl"] = $cxarray[$v1["signid"]];
								$values["cxlist"][] = $v1;
							}
						}

						$zongpoint = $values["point"];
						$zongpointcount = $values["pointcount"];

						if ($zongpointcount != 0) {
							$shopstart = intval(round($zongpoint / $zongpointcount));
						}
						else {
							$shopstart = 0;
						}

						$values["point"] = $shopstart;
						$values["attrdet"] = array();

						foreach ($attrdet as $k => $v ) {
							if ($v["firstattr"] == $attra["input"]) {
								$values["attrdet"]["input"] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["img"]) {
								$values["attrdet"]["img"][] = $v["value"];
							}
							else if ($v["firstattr"] == $attra["checkbox"]) {
								$values["attrdet"]["checkbox"][] = $v["value"];
							}
						}

						$templist[] = $values;
					}
				}
			}
		}

		if ($showtype == 1) {
			if ($is_custom == 0) {
				if (!empty($listids)) {
					$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1  " . $where . "    order by id desc ");
				}
				else {
					$list = array();
				}
			}

			if ($is_custom == 1) {
				switch ($cx_type) {
				case $cx_type:
					$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1  " . $where . "   order by id desc ");
					break;

				default:
					$list = array();
					break;
				}
			}

			if (is_array($list)) {
				foreach ($list as $keys => $vatt ) {
					if (0 < $vatt["id"]) {
						if ($is_custom == 0) {
							$detaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where  id in (" . $listids . ")  and shopid='" . $vatt["id"] . "'  and shoptype = " . $vatt["shoptype"] . "  and    FIND_IN_SET( " . $weekji . " , `weeks` )  " . $goodlistwhere . "   order by good_order asc  limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
						}
						else {
							$detaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid='" . $vatt["id"] . "'  and shoptype = " . $vatt["shoptype"] . "  and    FIND_IN_SET( " . $weekji . " , `weeks` )  " . $goodlistwhere . "   order by good_order asc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
						}

						if (!empty($detaa)) {
							foreach ($detaa as $keyq => $valq ) {
								if ($valq["is_cx"] == 1) {
									$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $valq["id"] . "  ");
									$newdata = getgoodscx($valq["cost"], $cxdata);
									$valq["zhekou"] = $newdata["zhekou"];
									$valq["is_cx"] = $newdata["is_cx"];
									$valq["cost"] = $newdata["cost"];
								}

								if ($shoptype == 1) {
									$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket where  shopid = " . $valq["shopid"] . "   ");
								}
								else {
									$shopdet = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast where  shopid = " . $valq["shopid"] . "   ");
								}

								$checkinfo = $this->shopIsopen($vatt["is_open"], $vatt["starttime"], $shopdet["is_orderbefore"], $nowhour);
								$valq["opentype"] = $checkinfo["opentype"];
								$valq["shopname"] = $vatt["shopname"];

								if ($is_custom == 1) {
									if ($valq["is_cx"] == 1) {
										$templist[] = $valq;
									}
								}
								else {
									$templist[] = $valq;
								}
							}
						}
					}
				}
			}
		}

		$data = $templist;
		return $data;
	}

	public function getdycxshops($type)
	{
		$pageinfo = new page();
		$pageinfo->setpage(intval(IReq::get("page")), 10);
		$cxlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "rule where  status = 1 and controltype = " . $type . " and starttime  < " . time() . " and endtime > " . time() . " ");
		$shopids = array();

		foreach ($cxlist as $key => $value ) {
			$shopids[] = $value["shopid"];
		}

		$shopids = implode(",", array_unique($shopids));

		if (!empty($shopids)) {
			$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop where is_pass = 1 and  id in (" . $shopids . ")  order by sort asc limit " . $pageinfo->startnum() . ", " . $pageinfo->getsize() . " ");
		}
		else {
			$list = array();
		}

		return $list;
	}
}


echo "\t ";

