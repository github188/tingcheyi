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
		$where = "";
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$locationtype = Mysite::$app->config["locationtype"];
		$where = $this->search($locationtype);
		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;
		$data["goodstypedoid"] = array();
		$attrshop = array();
		$data["attrinfo"] = array();
		$tempwhere = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_search =1  order by orderid asc limit 0,1000");

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

		$goodstypeid = intval(IFilter::act(IReq::get("goodstype_" . $value["id"])));
		$data["goodstypeid"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  id = " . $goodstypeid . " ");

		if (0 < count($tempwhere)) {
			$where .= " and a.shopid in (select shopid from " . Mysite::$app->config["tablepre"] . "shopsearch where second_id in(" . join($tempwhere) . ")  ) ";
		}

		$data["searchgoodstype"] = $templist;
		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_main =1 and type!='input' order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = 0  order by id asc limit 0,1000");
		$data["areadet"] = array();
		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;

		if (!empty($shopsearch)) {
			$where .= (empty($where) ? " where shopname like '%" . $shopsearch . "%' " : " and shopname like '%" . $shopsearch . "%' ");
		}

		$where .= "  and shoptype = 1 ";
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by sort asc limit 0,100");
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
					$value["juli"] = $this->GetDistance($lat, $lng, $value["lat"], $value["lng"], 2, 2) . "公里";
					$value["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 1 and shopid = '" . $value["id"] . "' ");
					$shopcounts = $this->mysql->select_one("select sum(sellcount) as shuliang    from " . Mysite::$app->config["tablepre"] . "goods\t where  shopid = " . $value["id"] . "");

					if (empty($shopcounts["shuliang"])) {
						$value["sellcount"] = 0;
					}
					else {
						$value["sellcount"] = $shopcounts["shuliang"];
					}

					if ($value["pointcount"] != 0) {
						$zongtistart = round($value["point"] / $value["pointcount"]);
					}
					else {
						$zongtistart = 0;
					}

					$value["point"] = $zongtistart;
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

	public function indexlist()
	{
		$locationtype = Mysite::$app->config["locationtype"];
		$attrshop = array();
		$data["attrinfo"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_search =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$value["is_now"] = (isset($seardata[$value["id"]]) ? $seardata[$value["id"]] : 0);
			$data["attrinfo"][] = $value;
		}

		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$where = $this->search($locationtype);
		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;
		$pxid = intval(IFilter::act(IReq::get("pxid")));
		$pxid = (in_array($pxid, array(0, 1, 2)) ? 0 : 0);
		$lng = ICookie::get("lng");
		$lat = ICookie::get("lat");
		$lng = (empty($lng) ? 0 : $lng);
		$lat = (empty($lat) ? 0 : $lat);
		$pxarray = array(" order by sort asc ", " order by sellcount desc", " order by (`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " ) ASC   ");
		$cxid = IFilter::act(IReq::get("cxid"));

		if (is_array($cxid)) {
			$where = $where . "  and shopid in( select shopid from " . Mysite::$app->config["tablepre"] . "shopsearch where  second_id in(" . join(",", $cxid) . "))  ";
		}
		else if (!empty($cxid)) {
			$where = $where . "  and shopid in( select shopid from " . Mysite::$app->config["tablepre"] . "shopsearch where  second_id = " . $cxid . ")   ";
		}

		$qsj = intval(IFilter::act(IReq::get("limitcost")));

		if (0 < $qsj) {
			$where = $where . "   and  a.limitcost > " . $qsj . " ";
		}

		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    " . $pxarray[$pxid] . " limit 0,100");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				if (0 < $value["id"]) {
					$checkinfo = $this->shopIsopen($value["is_open"], $value["starttime"], $value["is_orderbefore"], $nowhour);
					$value["opentype"] = $checkinfo["opentype"];
					$value["newstartime"] = $checkinfo["newstartime"];
					$value["juli"] = $this->GetDistance($lat, $lng, $value["lat"], $value["lng"], 2, 2) . "公里";
					$ps = $this->pscost($value, 10);
					$value["pscost"] = $ps["pscost"];
					$value["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 1 and shopid = '" . $value["id"] . "' ");
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

	public function ajaxshopinfo()
	{
		$shop_id = intval(IReq::get("shop_id"));
		$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where  id='" . $shop_id . "' ");

		if (empty($data["shopinfo"])) {
			echo "not find";
			exit();
		}

		$data["attr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 1 and parent_id = 0   order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["attr"][] = $value;
		}

		$nowhour = date("H:i:s", time());
		$data["nowhour"] = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($data["shopinfo"]["is_open"], $data["shopinfo"]["starttime"], $data["shopinfo"]["is_orderbefore"], $nowhour);
		$data["shopinfo"]["opentype"] = $checkinfo["opentype"];
		$data["shopinfo"]["newstartime"] = $checkinfo["newstartime"];
		$data["shopinfo"]["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 1 and shopid = '" . $data["shopinfo"]["id"] . "' ");
		Mysite::$app->setdata($data);
	}

	public function shopshow()
	{
		$weekji = date("w");
		$psset = Mysite::$app->config["psset"];
		$locationtype = Mysite::$app->config["locationtype"];
		$id = intval(IFilter::act(IReq::get("id")));

		if (empty($id)) {
			$link = IUrl::creatUrl("market/index");
			$this->message("数据获取失败", $link);
		}

		$where = " where shopid = '" . $id . "' ";
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "      limit 0,100");
		$data["findtype"] = 0;

		if (empty($shopinfo)) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id    order by sort asc limit 0,100");
			$data["findtype"] = 1;
		}

		$data["shopinfo"] = $shopinfo;
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$data["shopinfo"] = $shopinfo;
		$data["shopopeninfo"] = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopinfo["is_orderbefore"], $nowhour);
		$data["collect"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $shopinfo["id"] . "' ");
		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = " . $shopinfo["shoptype"] . " and parent_id = 0  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["shopattr"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr  where  cattype = " . $shopinfo["shoptype"] . " and shopid = '" . $shopinfo["id"] . "'  order by firstattr asc limit 0,1000");
		$data["catlist"] = $this->setcatlist($shopinfo["id"]);
		$data["collectlist"] = "";

		if (0 < $this->member["uid"]) {
			$collectlist = $this->mysql->getarr("select collectid from " . Mysite::$app->config["tablepre"] . "collect where uid = " . $this->member["uid"] . " and  shopuid =" . $shopinfo["id"] . " and collecttype = 1   limit 0,1000");
			$temparr = array();

			foreach ($collectlist as $key => $value ) {
				$temparr[] = $value["collectid"];
			}

			$data["collectlist"] = join(",", $temparr);
			$data["collectlist"] .= ",";
		}

		$data["defaultgoods"] = "/upload/images/default.jpg";
		$data["weekji"] = date("w");
		Mysite::$app->setdata($data);
	}

	public function dosearch()
	{
		$psset = Mysite::$app->config["psset"];
		$locationtype = 0;

		if (!empty($psset)) {
			$psinfo = unserialize($psset);
			$locationtype = $psinfo["locationtype"];
		}

		$id = intval(IFilter::act(IReq::get("searchshopid")));
		$catinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where   id =" . $id . "  order by orderid asc limit 0,100");
		$data["catinfo"] = $catinfo;
		$data["findtype"] = 0;

		if (empty($catinfo)) {
			$where = $this->search($locationtype);
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "     limit 0,100");

			if (empty($shopinfo)) {
				$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id    order by sort asc limit 0,100");
				$data["findtype"] = 1;
			}
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where b.id=" . $catinfo["shopid"] . "  order by sort asc limit 0,100");
		}

		$search_inputdo = IFilter::act(IReq::get("search_inputdo"));
		$searchshopid = intval(IReq::get("searchshopid"));
		$datawhere = "";

		if (!empty($search_inputdo)) {
			$datawhere = "  name like '%" . $search_inputdo . "%'   and shopid = '" . $searchshopid . "'";
		}

		$data["searchname"] = $search_inputdo;
		$data["where"] = $datawhere;
		$data["shopinfo"] = $shopinfo;
		$data["catlist"] = $this->setcatlist($shopinfo["shopid"]);
		Mysite::$app->setdata($data);
	}

	public function showgoods()
	{
		$psset = Mysite::$app->config["psset"];
		$locationtype = 0;

		if (!empty($psset)) {
			$psinfo = unserialize($psset);
			$locationtype = $psinfo["locationtype"];
		}

		$id = intval(IFilter::act(IReq::get("id")));
		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where   id =" . $id . " and shoptype = 1 order by id asc limit 0,100");

		if (empty($goodsinfo)) {
			$link = IUrl::creatUrl("market/index");
			$this->message("数据获取失败", $link);
		}

		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where b.id = " . $goodsinfo["shopid"] . "    order by sort asc limit 0,100");
		$data["findtype"] = 0;

		if (empty($shopinfo)) {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id    order by sort asc limit 0,100");
			$data["findtype"] = 1;
		}

		$data["catinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where   id =" . $goodsinfo["typeid"] . "  order by orderid asc limit 0,100");
		$data["relatcatlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where   parent_id =" . $data["catinfo"]["parent_id"] . "  order by orderid asc limit 0,100");
		$data["topcatinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where   id =" . $data["catinfo"]["parent_id"] . "  order by orderid asc limit 0,100");
		$data["hoptgoods"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where   typeid =" . $data["catinfo"]["id"] . " and shopid = " . $goodsinfo["shopid"] . " and is_hot =1 order by good_order asc limit 0,100");
		$this->pageCls->setpage(intval(IReq::get("page")), 5);
		$data["commentlist"] = $this->mysql->getarr("select a.*,b.username,b.logo,c.name from " . Mysite::$app->config["tablepre"] . "comment as a left join  " . Mysite::$app->config["tablepre"] . "member as b on a.uid = b.uid left join " . Mysite::$app->config["tablepre"] . "goods as c on a.goodsid = c.id  where a.shopid=" . $shopinfo["id"] . "  and  a.goodsid =" . $id . " and a.is_show  =0 order by a.id desc   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "comment   where shopid=" . $goodsinfo["shopid"] . "  and is_show  =0 ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->ajaxbar("getPingjia");
		$data["goodsinfo"] = $goodsinfo;
		$data["shopinfo"] = $shopinfo;
		$data["defaultgoods"] = "/upload/images/default.jpg";
		Mysite::$app->setdata($data);
	}

	public function cat()
	{
		$locationtype = Mysite::$app->config["locationtype"];
		$id = intval(IFilter::act(IReq::get("id")));
		$catinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "marketcate where   id =" . $id . "  order by orderid asc limit 0,100");
		$data["catinfo"] = $catinfo;
		$data["findtype"] = 0;

		if (empty($catinfo)) {
			$where = $this->search($locationtype);
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "     limit 0,100");

			if (empty($shopinfo)) {
				$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id    order by sort asc limit 0,100");
				$data["findtype"] = 1;
			}
		}
		else {
			$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where b.id=" . $catinfo["shopid"] . "  order by sort asc limit 0,100");
		}

		$datawhere = "";

		if (!empty($catinfo)) {
			if (0 < $catinfo["parent_id"]) {
				$datawhere = " shopid = " . $catinfo["shopid"] . " and typeid = " . $id;
			}
			else {
				$tempids = $this->mysql->getarr("select id from " . Mysite::$app->config["tablepre"] . "marketcate where shopid='" . $shopinfo["id"] . "' and parent_id = " . $id . "  order by orderid asc limit 0,100");
				$temp_c = array();

				if (is_array($tempids)) {
					foreach ($tempids as $key => $value ) {
						$temp_c[] = $value["id"];
					}
				}

				$trmp_str = join(",", $temp_c);

				if (!empty($trmp_str)) {
					$datawhere = " shopid = " . $catinfo["shopid"] . " and typeid in(" . $trmp_str . ")";
				}
				else {
					$datawhere = " shopid = " . $catinfo["shopid"];
				}
			}
		}

		$data["where"] = $datawhere;
		$data["shopinfo"] = $shopinfo;
		$data["catlist"] = $this->setcatlist($shopinfo["shopid"]);
		Mysite::$app->setdata($data);
	}

	public function setcatlist($shopid)
	{
		$temp = array();
		$catlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid='" . $shopid . "' and parent_id = 0  order by orderid asc limit 0,100");

		if (is_array($catlist)) {
			foreach ($catlist as $key => $value ) {
				$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "marketcate where shopid='" . $shopid . "' and parent_id =" . $value["id"] . "  order by orderid asc limit 0,100");
				$value["ids"] = "";
				$value["shuliang"] = 0;

				if (is_array($value["det"])) {
					$temc = array();

					foreach ($value["det"] as $k => $v ) {
						$temc[] = $v["id"];
						$value["det"][$k]["shuliang"] = $this->mysql->counts("select id from " . Mysite::$app->config["tablepre"] . "goods where shoptype='1' and is_waisong = 1  and  typeid ='" . $v["id"] . "' ");
						$value["shuliang"] += $value["det"][$k]["shuliang"];
					}

					$value["ids"] = join(",", $temc);
				}

				$temp[] = $value;
			}
		}

		return $temp;
	}

	public function search($locationtype)
	{
		$where = "";

		if ($locationtype == 1) {
			$nowadID = ICookie::get("myaddress");
			$myaddressname = ICookie::get("mapname");
			$lng = ICookie::get("lng");
			$lat = ICookie::get("lat");
			$lng = (empty($lng) ? 0 : $lng);
			$lat = (empty($lat) ? 0 : $lat);
			$shopsearch = IFilter::act(IReq::get("shopsearch"));
			$data["shopsearch"] = $shopsearch;

			if (!empty($shopsearch)) {
				$where .= (empty($where) ? " where shopname like '%" . $shopsearch . "%' " : " and shopname like '%" . $shopsearch . "%' ");
			}

			$bili = intval(Mysite::$app->config["servery"] / 1000);
			$bili = $bili * 0.009;
			$where .= (empty($where) ? " where id > 0 and endtime > " . time() . " and  SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094)  " : " and id > 0 and endtime > " . time() . "  and SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094)  ");
		}
		else {
			$nowadID = ICookie::get("myaddress");
			$myaddressname = ICookie::get("mapname");

			if (0 < $nowadID) {
				$where = (empty($where) ? " where id in(select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $nowadID . " ) " : $where . " and id in(select shopid from " . Mysite::$app->config["tablepre"] . "areamarket where areaid = " . $nowadID . " ) ");
			}

			$shopsearch = IFilter::act(IReq::get("shopsearch"));

			if (!empty($shopsearch)) {
				$where .= (empty($where) ? " where shopname like '%" . $shopsearch . "%' " : " and shopname like '%" . $shopsearch . "%' ");
			}

			$where .= (empty($where) ? " where id > 0 and endtime > " . time() . " " : " and id > 0 and endtime > " . time() . " ");
		}

		return $where;
	}

	public function cart()
	{
		$data["sitetitle"] = "购物车";
		$gooids = $_COOKIE["market_id"];
		$market_count = $_COOKIE["market_count"];

		if (empty($gooids)) {
			$this->message("购物车商品为空");
		}

		$gidinfo = explode(",", $gooids);
		$gidconut = explode(",", $market_count);
		$tempids = array();

		foreach ($gidinfo as $key => $value ) {
			if (0 < intval($value)) {
				$tempids[$value] = $gidconut[$key];
			}
		}

		$cartlist = array();
		$goodsshu = 0;
		$query = join(",", array_keys($tempids));

		if (!empty($query)) {
			$goodsinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goods where id in(" . $query . ") and shopid =0");

			foreach ($goodsinfo as $key => $value ) {
				$value["buycount"] = $tempids[$value["id"]];
				$value["sum"] = $value["buycount"] * $value["cost"];
				$cartlist[] = $value;
				$goodsshu += $value["buycount"];
			}
		}

		$data["cartlist"] = $cartlist;
		$checkps = $this->pscost(array("shopid" => 0), $goodsshu);

		if ($checkps["canps"] != 1) {
			$link = IUrl::creatUrl("site/guide");
			$this->message("该店铺不在配送范围内", $link);
		}

		$data["pscost"] = $checkps["pscost"];
		$psinfo = unserialize(Mysite::$app->config["psset"]);
		$data["areainfo"] = "";
		$nowID = ICookie::get("myaddress");
		$data["locationtype"] = $psinfo["locationtype"];

		if ($psinfo["locationtype"] == 1) {
			$data["areainfo"] = ICookie::get("mapname");

			if (empty($data["areainfo"])) {
				$link = IUrl::creatUrl("site/guide");
				$this->message("请先选择您所在区域在进行下单", $link);
			}
		}
		else {
			$data["areainfo"] = ICookie::get("mapname");

			if (empty($nowID)) {
				$link = IUrl::creatUrl("site/guide");
				$this->message("请先选择您所在区域在进行下单", $link);
			}
		}

		$data["myaddressslist"] = array();
		$tempre = "";

		if (!empty($nowID)) {
			$area_grade = Mysite::$app->config["area_grade"];
			$temp_areainfo = "";

			if (1 < $area_grade) {
				$areainfocheck = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $nowID . "");

				if (!empty($areainfocheck)) {
					$areainfocheck1 = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $areainfocheck["parent_id"] . "");

					if (!empty($areainfocheck1)) {
						$temp_areainfo = $areainfocheck1["name"];

						if (2 < $area_grade) {
							$areainfocheck2 = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id=" . $areainfocheck1["parent_id"] . "");

							if (!empty($areainfocheck2)) {
								$temp_areainfo = $areainfocheck2["name"] . $temp_areainfo;
							}
						}
					}

					$tempre = $temp_areainfo . $tempre;
				}
			}

			if (0 < $this->member["uid"]) {
				$data["myaddressslist"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where areaid" . $area_grade . "=" . $nowID . "");
			}
		}

		if (isset($data["myaddressslist"]["address"])) {
			$data["areainfo"] = $tempre . $data["myaddressslist"]["address"];
		}
		else {
			$data["areainfo"] = $tempre . $data["areainfo"];
		}

		$data["open_acout"] = Mysite::$app->config["open_acout"];
		$data["paylist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id desc  ");
		$data["starttime"] = Mysite::$app->config["marketstarttime"];
		$data["marketlong"] = Mysite::$app->config["marketlong"];
		$data["juanlist"] = array();

		if (!empty($this->member["uid"])) {
			$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid ='" . $this->member["uid"] . "'  and status = 1 and endtime > " . time() . "  order by id desc limit 0,20");
		}

		Mysite::$app->setdata($data);
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

	public function order()
	{
		$this->checkshoplogin();
	}

	public function goodslist()
	{
		$this->checkadminlogin();
	}
}


