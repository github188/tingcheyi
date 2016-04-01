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
		$this->gettopcollect();
		$where = "  ";
		$where = $this->search(Mysite::$app->config["locationtype"]);
		$where = (empty($where) ? " where id > 0  and  is_open =1  and is_waimai =1" : $where . " and  is_open =1  and is_waimai =1");
		$locationtype = Mysite::$app->config["locationtype"];
		$data["goodstypedoid"] = array();
		$attrshop = array();
		$data["attrinfo"] = array();
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

		$goodstypeid = intval(IFilter::act(IReq::get("goodstype_" . $value["id"])));
		$data["goodstypeid"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  id = " . $goodstypeid . " ");

		if (0 < count($tempwhere)) {
			$where .= " and a.shopid in (select shopid from " . Mysite::$app->config["tablepre"] . "shopsearch where second_id in(" . join($tempwhere) . ")  ) ";
		}

		$data["searchgoodstype"] = $templist;
		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1 and type!='input' order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;

		if (!empty($shopsearch)) {
			$where .= (empty($where) ? " where shopname like '%" . $shopsearch . "%' " : " and shopname like '%" . $shopsearch . "%' ");
		}

		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by sort asc limit 0,100");
		$data["shopzongshu"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by sort asc limit 0,100");
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
					$firstday = strtotime(date("Y-m-01 00:00:00", strtotime(date("Y-m-d H:i:s"))));
					$lastday = strtotime(date("Y-m-d 00:00:00", strtotime("\".\$firstday.\" +1 month -1 day")));
					$shopcounts = $this->mysql->select_one("select count(id) as shuliang  from " . Mysite::$app->config["tablepre"] . "order\t where suretime >= " . $firstday . " and suretime <= " . $lastday . "  and status = 3 and  shopid = " . $value["id"] . "");

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
					$goodstype = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodstype where shopid = " . $value["id"] . " and cattype =1  order by orderid asc");

					foreach ($goodstype as $key => $goodstypevalue ) {

						$goods = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "goods where  typeid = " . $goodstypevalue["id"] . " and shopid=" . $value["id"] . "  ");
                        $goodstype[$key]["goods"]=$goods;
					}
                    $value["goodstype"] = $goodstype;

					$value["point"] = $zongtistart;
					$value["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = '" . $value["id"] . "' ");
					$value["collect"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "collect where  collecttype = 0 and uid = " . $this->member["uid"] . " and collectid  = '" . $value["id"] . "' ");
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
		Mysite::$app->setAction("index");
	}

	public function paotuiorder()
	{
		if (empty($this->member["uid"])) {
			$link = IUrl::creatUrl("order/guestorder");
			$this->refunction("", $link);
		}
		else if (!empty($this->member["uid"])) {
			$link = IUrl::creatUrl("order/usersorder");
			$this->refunction("", $link);
		}
	}

	public function appdown()
	{
	}

	public function mobileban()
	{
	}

	public function xiugaiaddress()
	{
		$locationtype = Mysite::$app->config["locationtype"];
		$psinfo["locationtype"] = $locationtype;
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

					$data["areainfo"] = $temp_areainfo . $data["areainfo"];
				}
			}

			if ((0 < $this->member["uid"]) && (0 < nowID)) {
				$data["myaddressslist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "address  where areaid" . $area_grade . "=" . $nowID . "");
			}
		}

		$addid = intval(IReq::get("addid"));
		$addinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address where id = " . $addid . " and userid = " . $this->member["uid"] . "  ");
		$data["addinfo"] = $addinfo;
		Mysite::$app->setdata($data);
	}

	public function indexlist()
	{
		$locationtype = Mysite::$app->config["locationtype"];
		$attrshop = array();
		$data["attrinfo"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_search =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$value["is_now"] = (isset($seardata[$value["id"]]) ? $seardata[$value["id"]] : 0);
			$data["attrinfo"][] = $value;
		}

		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$where = $this->search($locationtype);
		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;
		$where = (empty($where) ? " where is_waimai = 1" : $where . " and is_waimai=1");
		$pxid = intval(IFilter::act(IReq::get("pxid")));
		$pxid = (in_array($pxid, array(0, 1, 2)) ? $pxid : 0);
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

		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    " . $pxarray[$pxid] . " limit 0,100");
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

	public function app()
	{
	}

	public function gaodashang()
	{
		$arealist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = 0   order by id asc limit 0,50");
		$shopidarray = array();
		$indexarea = array();

		foreach ($arealist as $key => $value ) {
			$areadet = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $value["id"] . "   order by id asc limit 0,50");
			$areacom = array();

			foreach ($areadet as $k => $v ) {
				if ($v["is_com"] == 1) {
					$shopidlist = $this->mysql->getarr("select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $v["id"] . " and shopid > 0  group  by shopid   limit 0,50");
					$v["shopids"] = array();

					foreach ($shopidlist as $m => $t ) {
						$v["shopids"][] = $t["shopid"];
						$shopidarray[] = $t["shopid"];
					}

					$areacom[] = $v;
				}
			}

			$value["det"] = $areadet;
			$value["areacom"] = $areacom;
			$indexarea[] = $value;
		}

		$shoplist = array();

		if (0 < count($shopidarray)) {
			$temp = array_unique($shopidarray);
			$temp_str = join(",", $temp);

			if (!empty($temp_str)) {
				$temp_shoplist = $this->mysql->getarr("select b.id,b.shopname,b.shoplogo,b.point,b.pointcount,a.limitstro,a.personcost,b.starttime,a.limitcost from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where b.endtime > " . time() . "  and is_open = 1 and is_pass = 1 and a.shopid in(" . $temp_str . ") and a.is_com = 1 order  by sort  asc");

				foreach ($temp_shoplist as $key => $value ) {
					$shoplist[$value["id"]] = $value;
				}
			}
		}

		$data["indexarea"] = $indexarea;
		$data["shoplist"] = $shoplist;
		Mysite::$app->setdata($data);
	}

	public function dianwoba()
	{
		$areado = $this->mysql->getarr("select  * from " . Mysite::$app->config["tablepre"] . "area where parent_id = 0");
		$dotemp = array();

		foreach ($areado as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select  * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $value["id"] . "");
			$dotemp[] = $value;
		}

		$data["arealist"] = $dotemp;
		$data["recomshop"] = $this->mysql->getarr("select b.id,b.shortname,b.shopname,b.shoplogo,a.shopid from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where   b.is_open = 1 and b.is_pass = 1 and a.is_com =1 limit 0,32");
		Mysite::$app->setdata($data);
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
			$where .= (empty($where) ? " where id > 0 and endtime > " . time() . " and  SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094) " : " and id > 0 and endtime > " . time() . "  and SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094) ");
		}
		else {
			$nowadID = ICookie::get("myaddress");
			$myaddressname = ICookie::get("mapname");

			if (0 < $nowadID) {
				$where = (empty($where) ? " where id in(select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $nowadID . " ) " : $where . " and id in(select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $nowadID . " ) ");
			}

			$shopsearch = IFilter::act(IReq::get("shopsearch"));

			if (!empty($shopsearch)) {
				$where .= (empty($where) ? " where shopname like '%" . $shopsearch . "%' " : " and shopname like '%" . $shopsearch . "%' ");
			}

			$where .= (empty($where) ? " where id > 0 and endtime > " . time() . " " : " and id > 0 and endtime > " . time() . " ");
		}

		return $where;
	}

	public function ajaxshopinfo()
	{
		$shop_id = intval(IReq::get("shop_id"));
		$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where  id='" . $shop_id . "' ");

		if (empty($data["shopinfo"])) {
			echo "not find";
			exit();
		}

		$data["attr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0   order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["attr"][] = $value;
		}

		$nowhour = date("H:i:s", time());
		$data["nowhour"] = strtotime($nowhour);
		$checkinfo = $this->shopIsopen($data["shopinfo"]["is_open"], $data["shopinfo"]["starttime"], $data["shopinfo"]["is_orderbefore"], $nowhour);
		$data["shopinfo"]["opentype"] = $checkinfo["opentype"];
		$data["shopinfo"]["newstartime"] = $checkinfo["newstartime"];
		$data["shopinfo"]["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = '" . $data["shopinfo"]["id"] . "' ");
		Mysite::$app->setdata($data);
	}

	public function collect()
	{
		$nowhour = date("H:i:s", time());
		$data["nowhour"] = strtotime($nowhour);
		$data["mainattr"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where  cattype = 0 and parent_id = 0 and is_main =1  order by orderid asc limit 0,1000");

		foreach ($templist as $key => $value ) {
			$value["det"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = " . $value["id"] . " order by orderid asc  limit 0,20");
			$data["mainattr"][] = $value;
		}

		$data["signlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "goodssign where type = 'shop'  order by id asc limit 0,100 ");
		$this->gettopcollect();
		Mysite::$app->setdata($data);
	}

	public function gettopcollect()
	{
		$data["collectlist"] = "";

		if (!empty($this->member["uid"])) {
			$where = " where uid=" . $this->member["uid"] . "  and collecttype = '0' ";
			$shoucangl = $this->mysql->getarr("select collectid from " . Mysite::$app->config["tablepre"] . "collect " . $where . " order by id desc limit 0, 5");

			if (0 < count($shoucangl)) {
				$ids = "";

				foreach ($shoucangl as $key => $value ) {
					$ids .= $value["collectid"] . ",";
				}

				$ids = substr($ids, 0, strlen($ids) - 1);
				$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id and  FIND_IN_SET( id, '" . $ids . "' )   order by sort asc limit 0,100");
				$nowhour = date("H:i:s", time());
				$nowhour = strtotime($nowhour);
				$templist = array();

				if (is_array($list)) {
					foreach ($list as $keys => $values ) {
						if (0 < $values["id"]) {
							$checkinfo = $this->shopIsopen($values["is_open"], $values["starttime"], $values["is_orderbefore"], $nowhour);
							$values["opentype"] = $checkinfo["opentype"];
							$values["newstartime"] = $checkinfo["newstartime"];
							$values["attrdet"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopattr where  cattype = 0 and shopid = " . $values["id"] . "");
							$templist[] = $values;
						}
					}
				}

				$data["collectlist"] = $templist;
			}
		}

		Mysite::$app->setdata($data);
	}

	public function guide()
	{
		$areainfo = $this->mysql->getarr("select id,name,parent_id,lat,lng,is_com from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		$psset = Mysite::$app->config["psset"];

		if (!empty($psset)) {
			$locationtype = Mysite::$app->config["locationtype"];
			$licationmudule = Mysite::$app->config["licationmudule"];
			$psinfo["locationtype"] = $locationtype;

			if ($psinfo["locationtype"] == 1) {
				$surec = IFilter::act(IReq::get("surec"));
				$data["searchvalue"] = "";
				$data["result"] = array();
				$data["sitetitle"] = "确定我的位置";
				$arealist = $this->mysql->getarr("select id,name,parent_id,lat,lng,is_com from " . Mysite::$app->config["tablepre"] . "area where parent_id = 0 order by orderid asc ");
				$data["arealist"] = array();

				foreach ($arealist as $key => $value ) {
					$temparr = $this->getcoarr($value, $areainfo, $parentids);
					$value["comarea"] = $temparr;
					$data["arealist"][] = $value;
				}

				$cookmalist = ICookie::get("cookmalist");
				$cooklnglist = ICookie::get("cooklnglist");
				$cooklatlist = ICookie::get("cooklatlist");
				$data["cookmalist"] = (empty($cookmalist) ? array() : explode(",", $cookmalist));
				$data["cooklnglist"] = (empty($cooklnglist) ? array() : explode(",", $cooklnglist));
				$data["cooklatlist"] = (empty($cooklatlist) ? array() : explode(",", $cooklatlist));
				$cookmalist = ICookie::get("cookshuliang");
				$data["cookshuliang"] = (empty($cookmalist) ? array() : explode(",", $cookmalist));
				Mysite::$app->setdata($data);

				if ($licationmudule == 2) {
					Mysite::$app->setAction("baidumap");
				}
				else {
					Mysite::$app->setAction("baidusearchmap");
				}
			}
		}

		Mysite::$app->setdata($data);
	}

	public function getcoarr($nowarr, $arealist, $parentids)
	{
		$temparray = array();

		if (in_array($nowarr["id"], $parentids)) {
			foreach ($arealist as $key => $value ) {
				if ($value["parent_id"] == $nowarr["id"]) {
					$checkarray = $this->getcoarr($value, $arealist, $parentids);

					if (0 < count($checkarray)) {
						$temparray = array_merge($temparray, $checkarray);
					}
				}
			}
		}
		else if ($nowarr["is_com"] == 1) {
			$temparray[] = $nowarr;
		}

		return $temparray;
	}

	public function ajaxchangecity()
	{
		$areaid = intval(IFilter::act(IReq::get("areaid")));
		$backdata = array(
			"flag"     => 0,
			"nav"      => array(),
			"arealist" => array()
			);

		if (empty($areaid)) {
			$cityinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id =0  order by id asc limit 0,50");
			$backdata["nav"][] = $cityinfo;
			$backdata["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id =" . $cityinfo["id"] . "  order by id asc limit 0,50");
		}
		else {
			$checkareaid = $areaid;
			$dataareaids = array();
			$checkchild = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id =" . $areaid . "  order by id asc limit 0,50");

			if (empty($checkchild)) {
				$backdata["flag"] = 1;
				$cityinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $areaid . "'   order by id desc limit 0,50");
				ICookie::set("lng", $cityinfo["lng"], 2592000, "/", "");
				ICookie::set("lat", $cityinfo["lat"], 2592000, "/", "");
				ICookie::set("mapname", $cityinfo["name"], 2592000, "/", "");
				ICookie::set("myaddress", $areaid, 2592000, "/", "");
				$backdata["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id =" . $cityinfo["parent_id"] . "  order by id asc limit 0,50");
			}
			else {
				$backdata["arealist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id =" . $areaid . "  order by id asc limit 0,50");
			}

			while (0 < $checkareaid) {
				$cityinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $checkareaid . "'   order by id desc limit 0,50");

				if (empty($cityinfo)) {
					break;
				}

				if (in_array($checkareaid, $dataareaids)) {
					break;
				}

				$dataareaids[] = $checkareaid;
				$checkareaid = $cityinfo["parent_id"];
				$mianbaoxue[] = $cityinfo;
			}

			$mianbaoxue = array_reverse($mianbaoxue);
			$backdata["nav"] = $mianbaoxue;
		}

		$this->success($backdata);
	}

	public function newajaxshop()
	{
		$cpid = intval(IFilter::act(IReq::get("cpid")));
		$areaid = intval(IFilter::act(IReq::get("areaid")));
		$lng = trim(IFilter::act(IReq::get("lng")));
		$lat = trim(IFilter::act(IReq::get("lat")));
		$areapre = "";
		$shopareaid = 0;

		if (!empty($areaid)) {
			$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $areaid . "  order by id asc limit 0,50");
			$lng = ICookie::get("lng");
			$lat = ICookie::get("lat");
			$shopareaid = $areainfo["id"];
		}
		else if (!empty($lng)) {
			$myaddress = ICookie::get("myaddress");
			$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $myaddress . "  order by id asc limit 0,50");
			$shopareaid = $areainfo["id"];
		}
		else {
			$myaddress = ICookie::get("myaddress");
			$areainfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id =" . $myaddress . "  order by id asc limit 0,50");
			$lng = $areainfo["lng"];
			$lat = $areainfo["lat"];
			$shopareaid = $areainfo["id"];
		}

		if (empty($lng)) {
			$this->message("请联系站长,未标记该区域地图坐标");
		}

		$where = " where id > 0 and endtime > " . time() . "  ";
		$where .= " and id in(select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $shopareaid . " )";

		if (!empty($cpid)) {
			$where .= " and  id in(select shopid from " . Mysite::$app->config["tablepre"] . "shopattr where attrid = " . $cpid . " ) ";
		}

		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by b.id limit 0,1000");
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$shopdoid = array();

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				if (0 < $value["id"]) {
					$checkinfo = $this->shopIsopen($value["is_open"], $value["starttime"], $value["is_orderbefore"], $nowhour);
					$value["opentype"] = $checkinfo["opentype"];
					$value["newstartime"] = $checkinfo["newstartime"];
					$psinfo = $this->pscost($value, 1);
					$value["pscost"] = $psinfo["pscost"];
					$value["shoplogo"] = (empty($value["shoplogo"]) ? Mysite::$app->config["imgserver"] . Mysite::$app->config["shoplogo"] : Mysite::$app->config["imgserver"] . $value["shoplogo"]);
					$shopdoid[] = $value["id"];
					$templist[] = $value;
				}
			}
		}

		$this->success(array("areapre" => $areapre, "list" => $templist));
	}

	public function searchdet()
	{
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		Mysite::$app->setdata($data);
	}

	public function searchchild()
	{
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["parent_ids"] = $parentids;
		Mysite::$app->setdata($data);
	}

	public function setlocationlink()
	{
		$areaid = IFilter::act(IReq::get("areaid"));
		$arealist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id = " . $areaid . " order by orderid asc ");
		ICookie::set("lng", $arealist["lng"], 2592000);
		ICookie::set("lat", $arealist["lat"], 2592000);
		ICookie::set("mapname", $arealist["name"], 2592000);
		ICookie::set("myaddress", $areaid, 2592000);
		$cookmalist = ICookie::get("cookmalist");
		$cooklnglist = ICookie::get("cooklnglist");
		$cooklatlist = ICookie::get("cooklatlist");
		$check = explode(",", $cookmalist);

		if (!in_array($arealist["name"], $check)) {
			$cookmalist = (empty($cookmalist) ? $arealist["name"] . "," : $arealist["name"] . "," . $cookmalist);
			$cooklatlist = (empty($cooklatlist) ? $arealist["lat"] . "," : $arealist["lat"] . "," . $cooklatlist);
			$cooklnglist = (empty($cooklnglist) ? $arealist["lng"] . "," : $arealist["lng"] . "," . $cooklnglist);
			ICookie::set("cookmalist", $cookmalist, 2592000);
			ICookie::set("cooklatlist", $cooklatlist, 2592000);
			ICookie::set("cooklnglist", $cooklnglist, 2592000);
		}

		if (Mysite::$app->config["sitetemp"] == "dianwoba") {
			$link = IUrl::creatUrl("site/shoplist");
			$this->message("", $link);
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message("", $link);
		}
	}

	public function setuserlng()
	{
		$shopid = IFilter::act(IReq::get("shopid"));
		$lng = IFilter::act(IReq::get("lng"));
		$lat = IFilter::act(IReq::get("lat"));
		$mapname = IFilter::act(IReq::get("mapname"));
		$maptype = intval(IFilter::act(IReq::get("maptype")));
		$checklng = intval($lng);

		if (empty($checklng)) {
			$link = IUrl::creatUrl("site/guide");
			$this->message("", $link);
		}

		$checklat = intval($lat);

		if (empty($checklat)) {
			$link = IUrl::creatUrl("site/guide");
			$this->message("", $link);
		}

		ICookie::set("lng", $lng, 2592000);
		ICookie::set("lat", $lat, 2592000);
		ICookie::set("mapname", $mapname, 2592000);
		ICookie::clear("myaddress");
		$cookmalist = ICookie::get("cookmalist");
		$cooklnglist = ICookie::get("cooklnglist");
		$cooklatlist = ICookie::get("cooklatlist");
		$cookshuliang = ICookie::get("cookshuliang");
		$check = explode(",", $cookmalist);

		if (!in_array($mapname, $check)) {
			$cookmalist = (empty($cookmalist) ? $mapname . "," : $mapname . "," . $cookmalist);
			$cooklatlist = (empty($cooklatlist) ? $lat . "," : $lat . "," . $cooklatlist);
			$cooklnglist = (empty($cooklnglist) ? $lng . "," : $lng . "," . $cooklnglist);
			$shuliang = $this->mysql->counts("select b.id from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on b.id=a.shopid where b.is_open=1  and  endtime > " . time() . " and a.is_waimai =  1 and SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094) order by b.id asc limit 0,1000");
			$cookshuliang = (empty($cookshuliang) ? $shuliang . "," : $shuliang . "," . $cookshuliang);
			ICookie::set("cookmalist", $cookmalist, 2592000);
			ICookie::set("cooklatlist", $cooklatlist, 2592000);
			ICookie::set("cooklnglist", $cooklnglist, 2592000);
			ICookie::set("cookshuliang", $cookshuliang, 2592000);
		}

		if ($maptype == 2) {
			$link = IUrl::creatUrl("plate/index");
			$this->message("", $link);
		}
		else if ($maptype == 1) {
			$link = IUrl::creatUrl("market/index");
			$this->message("", $link);
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message("", $link);
		}
	}

	public function getCaptcha()
	{
		$width = (intval(IReq::get("w")) == 0 ? 130 : IFilter::act(IReq::get("w")));
		$height = (intval(IReq::get("h")) == 0 ? 45 : IFilter::act(IReq::get("h")));
		$wordLength = (intval(IReq::get("l")) == 0 ? 5 : IFilter::act(IReq::get("l")));
		$fontSize = (intval(IReq::get("s")) == 0 ? 25 : IReq::get("s"));
		$ValidateObj = new Captcha();
		$ValidateObj->width = $width;
		$ValidateObj->height = $height;
		$ValidateObj->maxWordLength = $wordLength;
		$ValidateObj->minWordLength = $wordLength;
		$ValidateObj->fontSize = $fontSize;
		$ValidateObj->CreateImage($text);
		exit();
	}

	public function getparentarea()
	{
		$findid = intval(IReq::get("areaid"));
		$defaultid = IFilter::act(IReq::get("defaultid"));
		$deids = (empty($defaultid) ? array() : explode(",", $defaultid));
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area  where  parent_id ='" . $findid . "' limit 0,100");
		$content = "";

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				$extentd = (in_array($value["id"], $deids) ? "selected" : "");
				$content .= "<option value=\"" . $value["id"] . "\" data=\"" . $value["id"] . "\" " . $extentd . ">" . $value["name"] . "</option>\t";
			}
		}

		echo $content;
		exit();
	}

	public function mapjson()
	{
		$searchvalue = IReq::get("searchvalue");
		$citycode = IReq::get("citycode");
		$cityname = IReq::get("cityname");
		$content = file_get_contents("http://api.map.baidu.com/place/v2/search?ak=" . Mysite::$app->config["baidumapkey"] . "&output=json&query=" . $searchvalue . "&page_size=10&page_num=0&scope=1&region=" . $cityname);
		echo $content;
		exit();
	}

	public function ajaxlngtlat()
	{
		$lng = IFilter::act(IReq::get("lng"));
		$lat = IFilter::act(IReq::get("lat"));
		$maptype = intval(IFilter::act(IReq::get("maptype")));
		$content = file_get_contents("http://api.map.baidu.com/geocoder/v2/?ak=" . Mysite::$app->config["baidumapkey"] . "&location=" . $lat . "," . $lng . "&output=json&pois=0");
		$backinfo = json_decode($content, true);
		$shuliang = $this->mysql->counts("select b.id from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on b.id=a.shopid where b.is_open=1  and  endtime > " . time() . " and a.is_goshop =  1 and SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094) order by b.id asc limit 0,1000");
		$data["store_num"] = $shuliang;
		$data["region_name"] = $backinfo["result"]["business"];
		$data["region_addr"] = $backinfo["result"]["formatted_address"];
		$data["lng"] = $lng;
		$data["lat"] = $lat;
		$data["city"] = $backinfo["addressComponent"]["city"];

		if (!empty($data["region_name"])) {
			$dizhishu = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "positionkey where  datacontent ='" . $data["region_name"] . "'");

			if ($dizhishu < 1) {
				$areasearch = new areasearch($this->mysql);
				$myshu = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "positionkey where  datatype =3 ");
				$nowid = $myshu + 1;
				$areasearch->setdata($data["region_name"], "3", $nowid, $lat, $lng);
				$areasearch->save();
			}
		}

		if (!empty($data["region_addr"])) {
			$dizhishu = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "positionkey where  datacontent ='" . $data["region_addr"] . "'");

			if ($dizhishu < 1) {
				$areasearch = new areasearch($this->mysql);
				$myshu = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "positionkey where  datatype =3 ");
				$nowid = $myshu + 1;
				$areasearch->setdata($data["region_addr"], "3", $nowid, $lat, $lng);
				$areasearch->save();
			}
		}

		$this->success($data);
		$this->success($data);
	}

	public function getsearmap()
	{
		$searchvalue = trim(IFilter::act(IReq::get("searchvalue")));
		$content = file_get_contents("http://api.map.baidu.com/place/v2/search?ak=" . Mysite::$app->config["baidumapkey"] . "&output=json&query=" . $searchvalue . "&page_size=12&page_num=0&scope=1&region=" . Mysite::$app->config["cityname"]);
		$list = json_decode($content, true);
		$backdata = array();

		if ($list["message"] == "ok") {
			if (1 < $list["total"]) {
				foreach ($list["results"] as $key => $value ) {
					$temp["datacontent"] = $value["name"];
					$temp["dataaddress"] = $value["address"];
					$temp["lng"] = $value["location"]["lng"];
					$temp["lat"] = $value["location"]["lat"];
					$temp["parent_id"] = 0;
					$backdata[] = $temp;
				}
			}
		}

		$this->success($backdata);
	}

	public function ajaxbaidu()
	{
		$searchvalue = IFilter::act(IReq::get("searchvalue"));
		$cityname = IFilter::act(IReq::get("cityname"));
		$pagenum = intval(IReq::get("pagenum"));
		$content = file_get_contents("http://api.map.baidu.com/place/v2/search?ak=" . Mysite::$app->config["baidumapkey"] . "&output=json&query=" . $searchvalue . "&page_size=12&page_num=" . $pagenum . "&scope=1&region=" . $cityname);
		$arealist = json_decode($content, true);

		if ($arealist["status"] == 0) {
			$tempval = $arealist["results"];
			$temparray = array();
			$bili = intval(Mysite::$app->config["servery"] / 1000);
			$bili = $bili * 0.009;

			foreach ($tempval as $key => $value ) {
				$lng = $value["location"]["lng"];
				$lat = $value["location"]["lat"];
				$shuliang = $this->mysql->counts("select b.id from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on b.id=a.shopid where   b.is_open=1  and  endtime > " . time() . " and a.is_waimai =  1 and   SQRT((`lat` -" . $lat . ") * (`lat` -" . $lat . " ) + (`lng` -" . $lng . " ) * (`lng` -" . $lng . " )) < (`pradius`*0.01094)  order by b.id asc limit 0,1000");
				$arealist["results"][$key]["shuliang"] = $shuliang;
			}
		}

		$arealist["pagenum"] = $pagenum;
		echo "searchbackdata(" . json_encode($arealist) . ")";
		exit();
	}

	public function addcart()
	{
		$smardb = new newsmcart();
		$shopid = intval(IReq::get("shopid"));
		$goods_id = intval(IReq::get("goods_id"));
		$gdtype = intval(IReq::get("gdtype"));
		$gdtype = (in_array($gdtype, array(1, 2)) ? $gdtype : 1);

		if (!in_array($gdtype, array(1, 2))) {
			$this->message("未定义的商品类型");
		}

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->SetGoodsType($gdtype)->AddGoods($goods_id)) {
			$this->success("添加购物车成功");
		}
		else {
			$this->message($smardb->getError());
		}

		$this->success($goods_id);
	}

	public function downcart()
	{
		$smardb = new newsmcart();
		$shopid = intval(IReq::get("shopid"));
		$goods_id = intval(IReq::get("goods_id"));
		$num = intval(IReq::get("num"));

		if ($shopid < 0) {
			$this->message("店铺获取失败");
		}

		if ($goods_id < 0) {
			$this->message("获取商品失败");
		}

		if ($num < 1) {
			$this->message("商品数量失败");
		}

		$gdtype = intval(IReq::get("gdtype"));
		$gdtype = (in_array($gdtype, array(1, 2)) ? $gdtype : 1);

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->SetGoodsType($gdtype)->DownGoods($goods_id)) {
			$this->success("添加购物车成功");
		}
		else {
			$this->message($smardb->getError());
		}

		$this->success("操作成功");
	}

	public function delcartgoods()
	{
		$smardb = new newsmcart();
		$shopid = intval(IReq::get("shopid"));
		$goods_id = intval(IReq::get("goods_id"));
		$gdtype = intval(IReq::get("gdtype"));
		$gdtype = (in_array($gdtype, array(1, 2)) ? $gdtype : 1);

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->SetGoodsType($gdtype)->DelGoods($goods_id)) {
			$this->success("添加购物车成功");
		}
		else {
			$this->message($smardb->getError());
		}

		$this->success("操作成功");
	}

	public function delshopcart()
	{
		$smardb = new newsmcart();
		$shopid = intval(IReq::get("shopid"));

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->DelShop()) {
			$this->success("添加购物车成功");
		}
		else {
			$this->message($smardb->getError());
		}
	}

	public function selectproduct()
	{
		$shopid = intval(IReq::get("shopid"));
		$goods_id = intval(IReq::get("goods_id"));
		$goodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id=" . $goods_id . " and shopid=" . $shopid . "");
		$data["productinfo"] = (!empty($goodsinfo) ? unserialize($goodsinfo["product_attr"]) : array());
		$smardb = new newsmcart();
		$nowselect = $smardb->setdb($this->mysql)->SetShopId($shopid)->FindInproduct($goods_id);
		$data["nowselect"] = $nowselect;
		$data["attrids"] = array();

		if (!empty($nowselect)) {
			$data["attrids"] = explode(",", $nowselect["attrids"]);
		}

		$productlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "product where goodsid=" . $goods_id . " and shopid=" . $shopid . "");
		$data["productlist"] = $productlist;
		$data["goodsinfo"] = $goodsinfo;
		Mysite::$app->setdata($data);
	}

	public function doselectproduct()
	{
		$shopid = intval(IReq::get("shopid"));
		$goods_id = intval(IReq::get("goods_id"));
		$fgg = trim(IReq::get("fgg"));
		$ggdetids = trim(IReq::get("ggdetids"));
		$type = intval(IReq::get("type"));

		if (empty($ggdetids)) {
			$this->message("选择规格");
		}

		if ($type == 1) {
			$ggdetids = explode(",", $ggdetids);
			sort($ggdetids);
			$productlist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "product where goodsid=" . $goods_id . "  and  `attrids` = '" . join(",", $ggdetids) . "' and shopid=" . $shopid . "");
			$zigoodsinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goods where id=" . $productlist["goodsid"] . "  and shopid=" . $shopid . "");

			if ($zigoodsinfo["is_cx"] == 1) {
				$cxdata = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "goodscx where goodsid=" . $zigoodsinfo["id"] . "  ");
				$newdata = getgoodscx($productlist["cost"], $cxdata);
				$productlist["zhekou"] = $newdata["zhekou"];
				$productlist["is_cx"] = $newdata["is_cx"];
				$productlist["oldcost"] = $productlist["cost"];
				$productlist["cost"] = $newdata["cost"];
			}

			$smardb = new newsmcart();
			$nowselect = $smardb->setdb($this->mysql)->SetShopId($shopid)->productone($productlist["id"]);
			$productlist["goodcartnum"] = $nowselect;
			$this->success($productlist);
		}
		else {
			$ggdetids = explode(",", $ggdetids);
			sort($ggdetids);
			$tempid = join(",", $ggdetids);
			$productlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "product where goodsid=" . $goods_id . "  and  FIND_IN_SET('" . $tempid . "',`attrids`) and shopid=" . $shopid . "");
			$canchoiceid = array();

			foreach ($productlist as $key => $value ) {
				if (0 < $value["stock"]) {
					$tempids = explode(",", $value["attrids"]);

					foreach ($tempids as $k => $v ) {
						if (!in_array($v, $canchoiceid)) {
							$canchoiceid[] = $v;
						}
					}
				}
			}

			$this->success(join(",", $canchoiceid));
		}
	}

	public function modifycart()
	{
		$this->message("此函数已禁止");
	}

	public function clearcart()
	{
		$smardb = new newsmcart();
		$smardb->setdb($this->mysql)->ClearCart();
		$this->success("清空所有商品成功");
	}

	public function smallcat()
	{
		$shopid = intval(IReq::get("shopid"));
		$shopinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'  ");

		if (empty($shopinfo)) {
			$link = IUrl::creatUrl("site/index");
			$this->message("店铺不存在", $link);
		}

		if ($shopinfo["endtime"] < time()) {
			$link = IUrl::creatUrl("site/index");
			$this->message("店铺已关门", $link);
		}

		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$data["shopinfo"] = $shopinfo;
		$data["shopopeninfo"] = $this->shopIsopen($shopinfo["is_open"], $shopinfo["starttime"], $shopinfo["is_orderbefore"], $nowhour);
		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
			$cxclass = new sellrule();
			$cxclass->setdata($shopid, $carinfo["sum"], 0);
			$cxinfo = $cxclass->getdata();
			$carinfo["cx"] = $cxinfo;
			$tempinfo = $this->pscost($shopinfo, $carinfo["count"]);
			$carinfo["pstype"] = $tempinfo["pstype"];
			$carinfo["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
			$carinfo["limitcost"] = $shopinfo["limitcost"];
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message($smardb->getError(), $link);
		}

		$data["carinfo"] = $carinfo;
		$data["shopid"] = $shopid;
		Mysite::$app->setdata($data);
	}

	public function smallcatding()
	{
		$shopid = intval(IReq::get("shopid"));
		$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
			$cxclass = new sellrule();
			$cxclass->setdata($shopid, $carinfo["sum"], 0);
			$cxinfo = $cxclass->getdata();
			$carinfo["cx"] = $cxinfo;
			$tempinfo = $this->pscost($shopcheckinfo, $carinfo["count"]);
			$carinfo["pstype"] = $tempinfo["pstype"];
			$carinfo["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
			$carinfo["limitcost"] = $shopcheckinfo["limitcost"];
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message($smardb->getError(), $link);
		}

		$data["cartinfo"] = $carinfo;
		$data["shopinfo"] = $shopcheckinfo;
		$data["cxdata"] = $carinfo["cx"];
		$data["shopid"] = $shopid;
		Mysite::$app->setdata($data);
	}

	public function marketcart()
	{
		$shopid = intval(IReq::get("shopid"));
		$where = " where shopid = '" . $shopid . "' ";
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
		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
			$cxclass = new sellrule();
			$cxclass->setdata($shopid, $carinfo["sum"], 1);
			$cxinfo = $cxclass->getdata();
			$carinfo["cx"] = $cxinfo;
			$tempinfo = $this->pscost($shopinfo, $carinfo["count"]);
			$carinfo["pstype"] = $tempinfo["pstype"];
			$carinfo["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
			$carinfo["limitcost"] = $shopinfo["limitcost"];
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message($smardb->getError(), $link);
		}

		$data["carinfo"] = $carinfo;
		$data["shopid"] = $shopid;
		Mysite::$app->setdata($data);
	}

	public function showcatax()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			$link = IUrl::creatUrl("site/index");
			$this->message("未选择对应店铺", $link);
		}

		$Cart = new smCart();
		$carinfo = $Cart->getMyCart();

		if (!isset($carinfo["list"][$shopid]["data"])) {
			$link = IUrl::creatUrl("site/index");
			$this->message("对应店铺购物车商品为空", $link);
		}

		$showtype = trim(IReq::get("showtype"));
		$data["showtype"] = $showtype;

		if ($showtype == "market") {
			$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}
		else {
			$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}

		if (empty($data["shopinfo"])) {
			$link = IUrl::creatUrl("site/index");
			$this->message("未选择对应店铺111", $link);
		}

		$data["shopid"] = $shopid;
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$data["cartinfo"] = $carinfo;
		$cxclass = new sellrule();

		foreach ($carinfo["list"] as $key => $value ) {
			if ($value["shopinfo"]["shoptype"] == "1") {
				$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $key . "'    ");
			}
			else {
				$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $key . "'    ");
			}

			$cxclass->setdata($key, $value["sum"], $value["shopinfo"]["shoptype"]);
			$cxinfo = $cxclass->getdata();
			$data["cartinfo"]["list"][$key]["cx"] = $cxinfo;
			$tempinfo = $this->pscost($shopcheckinfo, $value["count"]);
			$data["cartinfo"]["list"][$key]["pstype"] = $tempinfo["pstype"];
			$data["cartinfo"]["list"][$key]["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
		}

		$checkps = $this->pscost($data["shopinfo"], $carinfo["list"][$shopid]["count"]);

		if ($checkps["canps"] != 1) {
			$link = IUrl::creatUrl("site/guide");
			$this->message("该店铺不在配送范围内", $link);
		}

		$locationtype = Mysite::$app->config["locationtype"];
		$psinfo["locationtype"] = $locationtype;
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

			$checkareaid = $nowID;
			$dataareaids = array();
			$areadata = array();

			while (0 < $checkareaid) {
				$temp_check = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $checkareaid . "'   order by id desc limit 0,50");

				if (empty($temp_check)) {
					break;
				}

				if (in_array($checkareaid, $dataareaids)) {
					break;
				}

				$dataareaids[] = $checkareaid;
				$checkareaid = $temp_check["parent_id"];
				$areadata[] = $temp_check["name"];
			}

			$areadata = array_reverse($areadata);
			$data["areainfo"] = join("", $areadata);
		}

		$data["myaddressslist"] = array();

		if (0 < $this->member["uid"]) {
			$data["myaddressslist"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "address  where     userid = " . $this->member["uid"] . " and `default` =1 ");
		}

		$data["juanlist"] = array();

		if (!empty($this->member["uid"])) {
			$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid ='" . $this->member["uid"] . "'  and status = 1 and endtime > " . time() . "  order by id desc limit 0,20");
		}

		$data["paylist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id desc  ");
		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($data["shopinfo"]["postdate"]) ? unserialize($data["shopinfo"]["postdate"]) : array());
		$data["pstimelist"] = array();
		$checknow = time();
		$whilestatic = $data["shopinfo"]["befortime"];
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

	public function showcart()
	{
		$shopid = intval(IReq::get("shopid"));

		if (empty($shopid)) {
			$link = IUrl::creatUrl("site/index");
			$this->message("未选择对应店铺", $link);
		}

		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message($smardb->getError(), $link);
		}

		$showtype = trim(IReq::get("showtype"));

		if ($showtype == "market") {
			$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}
		else {
			$data["shopinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}

		if (empty($data["shopinfo"])) {
			$link = IUrl::creatUrl("site/index");
			$this->message("未选择对应店铺", $link);
		}

		$data["shopid"] = $shopid;
		$data["scoretocost"] = Mysite::$app->config["scoretocost"];
		$psset = Mysite::$app->config["psset"];

		if (empty($psset)) {
			$link = IUrl::creatUrl("site/index");
			$this->message("网站未设置配送方式，请联系管理员", $link);
		}

		$locationtype = Mysite::$app->config["locationtype"];
		$psinfo["locationtype"] = $locationtype;
		$checkps = $this->pscost($data["shopinfo"], $carinfo["count"]);

		if ($checkps["canps"] != 1) {
			$link = IUrl::creatUrl("site/guide");
			$this->message("该店铺不在配送范围内", $link);
		}

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

					$data["areainfo"] = $temp_areainfo . $data["areainfo"];
				}
			}

			if ((0 < $this->member["uid"]) && (0 < nowID)) {
				$data["myaddressslist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "address  where areaid" . $area_grade . "=" . $nowID . "");
			}
		}

		$data["paylist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id desc  ");
		$nowhout = strtotime(date("Y-m-d", time()));
		$timelist = (!empty($data["shopinfo"]["postdate"]) ? unserialize($data["shopinfo"]["postdate"]) : array());
		$data["pstimelist"] = array();
		$checknow = time();
		$whilestatic = $data["shopinfo"]["befortime"];
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

		if (!empty($this->member["uid"])) {
			$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid ='" . $this->member["uid"] . "'  and status = 1 and endtime > " . time() . "  order by id desc limit 0,20");
		}

		Mysite::$app->setdata($data);
	}

	public function smallcat2()
	{
		$shopid = intval(IReq::get("shopid"));
		$data["shopxinxi"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shop  where id = '" . $shopid . "'    ");
		$data["shopinfo"] = array();

		if ($data["shopxinxi"]["shoptype"] == 1) {
			$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopmarket as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}
		else {
			$shopcheckinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.shopid = '" . $shopid . "'    ");
		}

		$smardb = new newsmcart();
		$carinfo = array();

		if ($smardb->setdb($this->mysql)->SetShopId($shopid)->OneShop()) {
			$carinfo = $smardb->getdata();
			$cxclass = new sellrule();
			$cxclass->setdata($shopid, $carinfo["sum"], 0);
			$cxinfo = $cxclass->getdata();
			$carinfo["cx"] = $cxinfo;
			$tempinfo = $this->pscost($shopcheckinfo, $carinfo["count"]);
			$carinfo["pstype"] = $tempinfo["pstype"];
			$carinfo["pscost"] = ($cxinfo["nops"] == true ? 0 : $tempinfo["pscost"]);
			$carinfo["areacost"] = 0;
			$carinfo["limitcost"] = $shopcheckinfo["limitcost"];
		}
		else {
			$link = IUrl::creatUrl("site/index");
			$this->message($smardb->getError(), $link);
		}

		$data["cartinfo"] = $carinfo;
		$data["shopinfo"] = $shopcheckinfo;
		$data["shopid"] = $shopid;
		$data["juanlist"] = array();

		if (!empty($this->member["uid"])) {
			$data["juanlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "juan  where uid ='" . $this->member["uid"] . "'  and status = 1 and endtime > " . time() . "  order by id desc limit 0,20");
		}

		Mysite::$app->setdata($data);
	}

	public function ajaxareadata()
	{
		$areaid = intval(IReq::get("areaid"));
		$typeid = intval(IReq::get("typeid"));
		$arealist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $areaid . " order by orderid asc ");
		$this->success($arealist);
	}

	public function single()
	{
		$data["show"] = IFilter::act(IReq::get("show"));
		$data["id"] = intval(IFilter::act(IReq::get("id")));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "single where id ='" . $data["id"] . "' or code='" . $data["show"] . "' order by id asc ");

		if (empty($data["info"])) {
			$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "single   order by id asc ");
		}

		Mysite::$app->setdata($data);
	}

	public function news()
	{
		$data["id"] = intval(IFilter::act(IReq::get("id")));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "news where id ='" . $data["id"] . "'  order by id desc ");

		if (empty($data["info"])) {
			$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "news   order by id asc ");
		}

		Mysite::$app->setdata($data);
	}

	public function newstype()
	{
		$data["id"] = intval(IFilter::act(IReq::get("id")));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "newstype where id ='" . $data["id"] . "'  order by id asc ");

		if (empty($data["info"])) {
			$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "newstype where  parent_id > 0 order by id desc ");
		}

		Mysite::$app->setdata($data);
	}

	public function phonecode()
	{
		$phone = IFilter::act(IReq::get("phone"));

		if (!IValidate::suremobi($phone)) {
			echo "showmessage('手机号格式错误')";
			exit();
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "mobile  where phone='" . $phone . "' order by id desc ");

		if (!empty($checkinfo)) {
			$backtime = intval($checkinfo["addtime"]) - time();

			if (0 < $backtime) {
				echo "showsend('" . $phone . "'," . $backtime . ")";
				exit();
			}
		}

		$makecode = mt_rand(10000, 99999);
		$data["phone"] = $phone;
		$data["addtime"] = time() + 90;
		$data["code"] = $makecode;
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "mobile", $data);
		$contents = "【跑堂客】亲，下单验证码为:" . $makecode . "请您尽快完成验证吧，车位在向您奔跑！";
		$APIServer = "http://www.tingche.com/sendtophone.php?apiuid=" . Mysite::$app->config["apiuid"];
		$weblink = $APIServer . "&key=" . trim(Mysite::$app->config["sms86ac"]) . "&code=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&msgcontent=" . urlencode($contents) . "";
		$contentcccc = file_get_contents($weblink);
		logwrite("短信发送结果:" . $contentcccc);
		echo "showsend('" . $phone . "',90)";
		exit();
	}

	public function setphone()
	{
		$checksend = Mysite::$app->config["ordercheckphone"];

		if ($checksend == 1) {
			if (!empty($this->member["uid"])) {
				echo "removesend()";
				exit();
			}

			$phone = IFilter::act(IReq::get("phone"));

			if (IValidate::suremobi($phone)) {
				$checkphone = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "mobile where phone ='" . $phone . "'   order by addtime desc limit 0,50");

				if (!empty($checkphone)) {
					if ($checkphone["is_send"] == 1) {
						echo "removesend()";
						exit();
					}

					$bijiaotime = time() - 180;

					if ($bijiaotime < $checkphone["addtime"]) {
						$backtime = 180 - time() - $checkphone["addtime"];
						ICookie::set("phonecode", $checkphone["code"], $backtime);
						echo "showsend('" . $phone . "'," . $backtime . ")";
						exit();
					}
				}

				$data["code"] = mt_rand(10000, 99999);
				$data["phone"] = $phone;
				$data["addtime"] = time();
				$data["is_send"] = 0;
				ICookie::set("phonecode", $data["code"], 180);
				$default_tpl = new config("tplset.php", hopedir);
				$tpllist = $default_tpl->getInfo();
				if (!isset($tpllist["usercodetpl"]) || empty($tpllist["usercodetpl"])) {
					echo "alert('发送失败，请联系管理员设置模板')";
					exit();
				}
				else {
					$sendmobile = new mobile();
					$tempdata["code"] = $data["code"];
					$tempdata["sitename"] = Mysite::$app->config["sitename"];
					$contents = Mysite::$app->statichtml($tpllist["usercodetpl"], $tempdata);

					if (Mysite::$app->config["smstype"] == 2) {
						$APIServer = "http://www.sms-10086.cn/Service.asmx/sendsms?";
						$weblink = $APIServer . "zh=" . trim(Mysite::$app->config["sms86ac"]) . "&mm=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&nr=" . urlencode($contents) . "&dxlbid=27";
						$contentcccc = file_get_contents($weblink);
						logwrite("验证短信发送:" . $contentcccc);
					}
					else {
						$phoneids = array();
						$phoneids[] = $phone;
						$chekcinfo = $sendmobile->sendsms($phoneids, $contents);
						logwrite("验证短信发送:" . $chekcinfo);
					}
				}

				$this->mysql->insert(Mysite::$app->config["tablepre"] . "mobile", $data);
				echo "showsend('" . $phone . "',180)";
				exit();
			}
			else {
				echo "alert('不是手机号')";
				exit();
			}
		}
		else {
			echo "";
			exit();
		}
	}

	public function waitpay()
	{
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

		$data["orderinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id=" . $orderid . "  ");

		if (empty($data["orderinfo"])) {
			$this->message("订单数据获取失败");
		}

		if ((0 < $userid) && ($this->admin["uid"] == 0)) {
			if ($data["orderinfo"]["buyeruid"] != $userid) {
				$this->message("无查看权限2");
			}
		}

		$data["orderdetlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "orderdet where order_id=" . $orderid . "  order by id asc limit 0,50");
		$paytypelist = array("货到支付", "在线支付");
		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist  where type = 0 or type =1  order by id asc limit 0,50");
		$data["paylist"] = $paylist;
		$data["paytypearr"] = $paytypelist;
		$data["buyerstatus"] = array("等待处理", "订单处理中", "订单已发货", "订单完成", "订单已取消", "订单已取消");
		$weixin = array("flag" => 0, "msg" => "");
		$data["weixin"] = $weixin;
		Mysite::$app->setdata($data);
	}

	public function gotopay()
	{
		$errdata = array("paysure" => false, "reason" => "", "url" => "");
		$orderid = intval(IReq::get("orderid"));
		$payerrlink = IUrl::creatUrl("site/waitpay/orderid/" . $orderid);

		if ($orderid == 0) {
			$backurl = IUrl::creatUrl("site/index");
			$errdata["url"] = $backurl;
			$errdata["reason"] = "订单获取失败";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$payerrlink = IUrl::creatUrl("site/waitpay/orderid/" . $orderid);
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
			$errdata["reason"] = "订单不存在";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		if (0 < $userid) {
			if ($orderinfo["buyeruid"] != $userid) {
				$errdata["url"] = $payerrlink;
				$errdata["reason"] = "订单不属于您不能支付";
				$errdata["paysure"] = false;
				$this->showpayhtml($errdata);
			}
		}

		if ($orderinfo["paytype"] == 0) {
			if ($orderinfo["buyeruid"] != $userid) {
				$errdata["url"] = $payerrlink;
				$errdata["reason"] = "此订单是货到支付订单不可操作";
				$errdata["paysure"] = false;
				$this->showpayhtml($errdata);
			}
		}

		if (2 < $orderinfo["status"]) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "此订单已发货或者其他状态不可操作";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$paydotype = IFilter::act(IReq::get("paydotype"));
		$paylist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "paylist where  loginname = '" . $paydotype . "' and (type = 0 or type=1) order by id asc limit 0,50");

		if (empty($paylist)) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "不存在的支付类型";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		if ($orderinfo["paystatus"] == 1) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "订单已支付，不能重复付款";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$paydir = hopedir . "/plug/pay/" . $paydotype;

		if (!file_exists($paydir . "/pay.php")) {
			$errdata["url"] = $payerrlink;
			$errdata["reason"] = "支付文件不存在";
			$errdata["paysure"] = false;
			$this->showpayhtml($errdata);
		}

		$dopaydata = array("type" => "order", "upid" => $orderid, "cost" => $orderinfo["allcost"], "source" => 0, "paydotype" => $paydotype);
		include_once $paydir . "/pay.php";
		exit();
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

	public function updatearea()
	{
		$this->mysql->getarr("TRUNCATE TABLE  `xiaozu_areatoadd`");
		$this->mysql->getarr("TRUNCATE TABLE  `xiaozu_areashop`");
		$tempaa = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by id asc limit 0,2000");

		foreach ($tempaa as $key => $value ) {
			$temp["areaid"] = $value["id"];
			$temp["shopid"] = 0;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "areashop", $temp);
			$tk["areaid"] = $value["id"];
			$tk["shopid"] = 0;
			$tk["cost"] = 0;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "areatoadd", $tk);
		}

		$udata["cattype"] = 0;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "goodstype", $udata, " id > 0 ");
	}

	public function payback()
	{
		$paytype = trim(IFilter::act(IReq::get("paytype")));

		if (empty($paytype)) {
			$this->error("未定义的支付接口");
		}

		$paydir = hopedir . "/plug/pay/" . $paytype;

		if (!file_exists($paydir . "/back.php")) {
			$this->message("支付方式方式不存在");
		}

		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[] = $value["loginname"];
			}
		}

		if (!in_array($paytype, $paytypelist)) {
			$this->message("未定义的支付方式");
		}

		include_once $paydir . "/back.php";
	}

	public function noticeurl()
	{
		$paytype = trim(IFilter::act(IReq::get("paytype")));

		if (empty($paytype)) {
			$this->message("未定义的支付接口");
		}

		$paydir = hopedir . "/plug/pay/" . $paytype;

		if (!file_exists($paydir . "/notice.php")) {
			$this->message("支付方式方式不存在");
		}

		$paylist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist   order by id asc limit 0,50");

		if (is_array($paylist)) {
			foreach ($paylist as $key => $value ) {
				$paytypelist[] = $value["loginname"];
			}
		}

		if (!in_array($paytype, $paytypelist)) {
			$this->message("未定义的支付方式");
		}

		include_once $paydir . "/notice.php";
	}

	public function ceju()
	{
		$mi = $this->GetDistance(IFilter::act(IReq::get("lat")), IFilter::act(IReq::get("lng")), IFilter::act(IReq::get("dlat")), IFilter::act(IReq::get("dlng")), 1);
		$tempmi = $mi;
		$mi = (1000 < $mi ? round($mi / 1000, 2) . "km" : $mi . "m");
		$this->success($mi);
	}

	public function searchposition()
	{
		$position = IFilter::act(IReq::get("position"));
		$areainfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area   order by orderid asc");
		$parentids = array();

		foreach ($areainfo as $key => $value ) {
			$parentids[] = $value["parent_id"];
		}

		$parentids = array_unique($parentids);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where  id not in(" . join(",", $parentids) . ") and name like '%" . $position . "%' order by orderid asc  ");
		Mysite::$app->setdata($data);
	}

	public function makeshow()
	{
		$id = intval(IFilter::act(IReq::get("id")));
		$actime = IFilter::act(IReq::get("actime"));
		$sign = IFilter::act(IReq::get("sign"));
		$status = intval(IFilter::act(IReq::get("status")));

		if ($id < 1) {
			echo "获取失败";
			exit();
		}

		if (empty($actime)) {
			echo "检测不通过";
			exit();
		}

		if (empty($sign)) {
			echo "检测不通过";
			exit();
		}

		$orderinfo = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "order  where id= '" . $id . "'   ");

		if (empty($orderinfo)) {
			echo "订单获失败";
			exit();
		}

		$tempstr = md5($orderinfo["dno"] . $actime);
		$tempstr = substr($tempstr, 3, 15);
		$tempstr = md5($orderinfo["shopuid"] . $tempstr);
		$tempstr = substr($tempstr, 3, 15);

		if ($sign != $tempstr) {
			echo "验证不通过";
			exit();
		}

		if ($orderinfo["status"] != 1) {
			echo "订单状态不可操作制作与否";
			exit();
		}

		$dolink = IUrl::creatUrl("site/sendorder/id/" . $id . "/sign/" . $sign . "/actime/" . $actime);

		if (!empty($orderinfo["is_make"])) {
			echo "订单已处理过,不需再次操作<br>";

			if ($orderinfo["is_make"] == 1) {
				echo "已确认制作<br>";
				echo "若需要立即发货,<a href=\"" . $dolink . "\">点击发货</a>";
				exit();
			}
			else {
				echo "已取消制作该订单,等待管理员处理";
				exit();
			}
		}
		else if ($status == 1) {
			$newdata["is_make"] = 1;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $newdata, "id='" . $id . "'");
			echo "已确认制作<br>";
			echo "若需要立即发货,<a href=\"" . $dolink . "\">点击发货</a>";
			exit();
		}
		else if ($status == 2) {
			$newdata["is_make"] = 2;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $newdata, "id='" . $id . "'");
			echo "已取消制作该订单,等待管理员处理";
			exit();
		}
		else {
			echo "提交操作数据失败";
			exit();
		}

		exit();
	}

	public function sendorder()
	{
		$id = intval(IFilter::act(IReq::get("id")));
		$actime = IFilter::act(IReq::get("actime"));
		$sign = IFilter::act(IReq::get("sign"));
		$status = intval(IFilter::act(IReq::get("status")));

		if ($id < 1) {
			echo "获取失败";
			exit();
		}

		if (empty($actime)) {
			echo "检测不通过";
			exit();
		}

		if (empty($sign)) {
			echo "检测不通过";
			exit();
		}

		$orderinfo = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "order  where id= '" . $id . "'   ");

		if (empty($orderinfo)) {
			echo "订单获失败";
			exit();
		}

		$tempstr = md5($orderinfo["dno"] . $actime);
		$tempstr = substr($tempstr, 3, 15);
		$tempstr = md5($orderinfo["shopuid"] . $tempstr);
		$tempstr = substr($tempstr, 3, 15);

		if ($sign != $tempstr) {
			echo "验证不通过";
			exit();
		}

		if ($orderinfo["status"] != 1) {
			echo "订单状态已发货或者不能发货";
			exit();
		}

		if ($orderinfo["is_make"] != 1) {
			echo "订单制作状态错误";
			exit();
		}

		$newdata["status"] = 2;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "order", $newdata, "id='" . $id . "'");
		echo "操作成功";
		exit();
	}

	public function catalog()
	{
		$tempareaid = ICookie::get("myaddress");
		$areaid = 0;

		if (empty($tempareaid)) {
			$areaid = 2;
		}
		else {
			$dataareaids = array();

			while (0 < $tempareaid) {
				$areaid = $tempareaid;
				$temp_check = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id ='" . $tempareaid . "'   order by id desc limit 0,50");

				if (empty($temp_check)) {
					break;
				}

				if (in_array($temp_check["parent_id"], $dataareaids)) {
					break;
				}

				if ($temp_check["parent_id"] == 0) {
					break;
				}

				$dataareaids[] = $tempareaid;
				$tempareaid = $temp_check["parent_id"];
			}
		}

		$caiplist = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = 51   order by orderid asc limit 0,50");
		$arealist = $this->mysql->getarr("select id,name from " . Mysite::$app->config["tablepre"] . "area where parent_id = '" . $areaid . "'   order by id asc limit 0,50");
		$shoplist = $this->mysql->getarr("select id,shopname from " . Mysite::$app->config["tablepre"] . "shop where   id in(select shopid from " . Mysite::$app->config["tablepre"] . "areashop where areaid = " . $areaid . ") ");
		$tempshoplist = array();

		foreach ($shoplist as $k => $val ) {
			$temp_cp = $this->mysql->getarr("select attrid  from " . Mysite::$app->config["tablepre"] . "shopattr  where shopid = '" . $val["id"] . "'   order by attrid asc limit 0,50");
			$temp_cpids = array();

			foreach ($temp_cp as $key => $value ) {
				$temp_cpids[] = $value["attrid"];
			}

			$vk["cpids"] = join(",", $temp_cpids);
			$tempb = $this->mysql->getarr("select areaid  from " . Mysite::$app->config["tablepre"] . "areashop  where shopid = '" . $val["id"] . "'   order by areaid asc limit 0,100");
			$dotem = array();

			foreach ($tempb as $vc => $vb ) {
				$dotem[] = $vb["areaid"];
			}

			$vk["areaids"] = join(",", $dotem);
			$vk["id"] = $val["id"];
			$vk["shopname"] = $val["shopname"];
			$tempshoplist[] = $vk;
		}

		$data["shopdata"] = $tempshoplist;
		$data["caiplist"] = $caiplist;
		$data["arealist"] = $arealist;
		Mysite::$app->setdata($data);
	}

	public function changeshop()
	{
		$id = intval(IFilter::act(IReq::get("id")));
		$link = IUrl::creatUrl("site/index/");

		if ($id < 1) {
			$this->message("获取店铺ID失败", $link);
		}

		$grade = Mysite::$app->config["area_grade"];
		$temp_where = "";
		$doarea = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id =0) ");

		if ($grade == 1) {
			$where = " and areaid  in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id =0)";
		}
		else if ($grade == 2) {
			$where = " and areaid  in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id =0)) ";
		}
		else if ($grade == 3) {
			$where = " and areaid   in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id in(select id from " . Mysite::$app->config["tablepre"] . "area where parent_id =0))) ";
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "areatoadd where shopid=" . $id . " " . $where . "");

		if (empty($checkinfo)) {
			$this->message("获取店铺区域信息失败", $link);
		}

		$arealist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id = " . $checkinfo["areaid"] . " order by orderid asc ");

		if (empty($arealist)) {
			$this->message("获取店铺区域信息失败", $link);
		}

		ICookie::set("lng", $arealist["lng"], 2592000);
		ICookie::set("lat", $arealist["lat"], 2592000);
		ICookie::set("mapname", $arealist["name"], 2592000);
		ICookie::set("myaddress", $checkinfo["areaid"], 2592000);
		$cookmalist = ICookie::get("cookmalist");
		$cooklnglist = ICookie::get("cooklnglist");
		$cooklatlist = ICookie::get("cooklatlist");
		$check = explode(",", $cookmalist);

		if (!in_array($arealist["name"], $check)) {
			$cookmalist = (empty($cookmalist) ? $arealist["name"] . "," : $arealist["name"] . "," . $cookmalist);
			$cooklatlist = (empty($cooklatlist) ? $arealist["lat"] . "," : $arealist["lat"] . "," . $cooklatlist);
			$cooklnglist = (empty($cooklnglist) ? $arealist["lng"] . "," : $arealist["lng"] . "," . $cooklnglist);
			ICookie::set("cookmalist", $cookmalist, 2592000);
			ICookie::set("cooklatlist", $cooklatlist, 2592000);
			ICookie::set("cooklnglist", $cooklnglist, 2592000);
		}

		$link = IUrl::creatUrl("shop/index/id/" . $id);
		$this->message("", $link);
	}

	public function shoplist()
	{
		$data["cpid"] = intval(IFilter::act(IReq::get("cpid")));
		$data["qisong"] = intval(IFilter::act(IReq::get("qisong")));
		$data["renjun"] = intval(IFilter::act(IReq::get("renjun")));
		$data["orderby"] = intval(IFilter::act(IReq::get("orderby")));
		$psset = Mysite::$app->config["psset"];
		$locationtype = 0;
		$attrshop = array();
		$qisongarray = array("", " a.limitcost > 0 and a.limitcost < 51 ", " a.limitcost > 50 and a.limitcost < 101 ", " a.limitcost > 100   ");
		$renjunarray = array("", " a.personcost > 0 and a.personcost < 11 ", " a.personcost > 10 and a.personcost < 51  ", " a.personcost > 50");
		$orderarray = array("  sort asc", " a.personcost desc", " a.limitcost desc");
		$orderinfo = (in_array($data["orderby"], array(0, 1, 2)) ? $orderarray[$data["orderby"]] : "sort asc");
		$locationtype = Mysite::$app->config["locationtype"];
		$psinfo["locationtype"] = $locationtype;
		$where = $this->search($locationtype);
		$data["attrinfo"] = array();
		$templist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype where parent_id = 51 order by orderid asc  limit 0,20");

		foreach ($templist as $key => $value ) {
			$tempwhere = (empty($where) ? " where id in(select shopid from " . Mysite::$app->config["tablepre"] . "shopattr where attrid = " . $value["id"] . " ) " : $where . "  and   id in(select shopid from " . Mysite::$app->config["tablepre"] . "shopattr where attrid = " . $value["id"] . " ) ");
			$data["attrinfo"][] = $value;
		}

		if (!empty($data["cpid"])) {
			$where = (empty($where) ? " where id in(select shopid from " . Mysite::$app->config["tablepre"] . "shopattr where attrid = " . $data["cpid"] . " ) " : $where . "  and   id in(select shopid from " . Mysite::$app->config["tablepre"] . "shopattr where attrid = " . $data["cpid"] . " ) ");
		}

		if (in_array($data["qisong"], array(1, 2, 3))) {
			$where = (empty($where) ? " where " . $qisongarray[$data["qisong"]] : $where . " and " . $qisongarray[$data["qisong"]]);
		}

		if (in_array($data["renjun"], array(1, 2, 3))) {
			$where = (empty($where) ? " where " . $renjunarray[$data["renjun"]] : $where . " and " . $renjunarray[$data["renjun"]]);
		}

		$shopsearch = IFilter::act(IReq::get("shopsearch"));
		$data["shopsearch"] = $shopsearch;
		$this->pageCls->setpage(intval(IReq::get("page")), 10);
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "    order by " . $orderinfo . " limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id " . $where . "   ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar();
		$nowhour = date("H:i:s", time());
		$nowhour = strtotime($nowhour);
		$templist = array();
		$shopdoid = array();

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				if (0 < $value["id"]) {
					$checkinfo = $this->shopIsopen($value["is_open"], $value["starttime"], $value["is_orderbefore"], $nowhour);
					$value["opentype"] = $checkinfo["opentype"];
					$value["newstartime"] = $checkinfo["newstartime"];
					$psinfo = $this->pscost($value, 1);
					$value["pscost"] = $psinfo["pscost"];
					$shopdoid[] = $value["id"];
					$templist[] = $value;
				}
			}
		}

		$data["shopdoid"] = join(",", $shopdoid);
		$data["shoplist"] = $templist;
		$data["cpid"] = (empty($data["cpid"]) ? "default" : $data["cpid"]);
		$data["qisong"] = (empty($data["qisong"]) ? "default" : $data["qisong"]);
		$data["renjun"] = (empty($data["renjun"]) ? "default" : $data["renjun"]);
		$data["orderby"] = (empty($data["orderby"]) ? "default" : $data["orderby"]);
		$data["recomshop"] = $this->mysql->getarr("select b.id,b.shortname,b.shopname,b.shoplogo,a.shopid,b.address,b.lng,b.lat from " . Mysite::$app->config["tablepre"] . "shopfast as a left join " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id  where   b.is_open = 1 and b.is_pass = 1 and a.is_com =1 limit 0,10");
		Mysite::$app->setdata($data);

		if ($locationtype == 1) {
			Mysite::$app->setdata($data);
			Mysite::$app->setAction("mapshoplist");
		}
		else {
			Mysite::$app->setdata($data);
			Mysite::$app->setAction("shoplist");
		}
	}

	public function twojiguide()
	{
		$position = IFilter::act(IReq::get("position"));
		$list = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $position . " order by orderid asc  ");
		$data["list"] = array();

		foreach ($list as $key => $value ) {
			$value["sanjiguide"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id = " . $value["id"] . " order by orderid asc  ");
			$data["list"][] = $value;
		}

		Mysite::$app->setdata($data);
	}

	public function qitesetlocationlink()
	{
		$areaid = IFilter::act(IReq::get("areaid"));
		$arealist = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "area where id = " . $areaid . " order by orderid asc ");
		ICookie::set("lng", $arealist["lng"], 2592000);
		ICookie::set("lat", $arealist["lat"], 2592000);
		ICookie::set("mapname", $arealist["name"], 2592000);
		ICookie::set("myaddress", $areaid, 2592000);
		$cookmalist = ICookie::get("cookmalist");
		$cooklnglist = ICookie::get("cooklnglist");
		$cooklatlist = ICookie::get("cooklatlist");
		$check = explode(",", $cookmalist);

		if (!in_array($arealist["name"], $check)) {
			$cookmalist = (empty($cookmalist) ? $arealist["name"] . "," : $arealist["name"] . "," . $cookmalist);
			$cooklatlist = (empty($cooklatlist) ? $arealist["lat"] . "," : $arealist["lat"] . "," . $cooklatlist);
			$cooklnglist = (empty($cooklnglist) ? $arealist["lng"] . "," : $arealist["lng"] . "," . $cooklnglist);
			ICookie::set("cookmalist", $cookmalist, 2592000);
			ICookie::set("cooklatlist", $cooklatlist, 2592000);
			ICookie::set("cooklnglist", $cooklnglist, 2592000);
		}

		$link = IUrl::creatUrl("site/shoplist");
		$this->message("", $link);
	}

	public function fabupaotui()
	{
		$data["content"] = trim(IFilter::act(IReq::get("ptcontent")));
		$data["buyername"] = trim(IFilter::act(IReq::get("name")));
		$data["buyeraddress"] = trim(IFilter::act(IReq::get("address")));
		$data["buyerphone"] = trim(IFilter::act(IReq::get("phone")));
		$data["addtime"] = time();
		$data["ordertype"] = 1;
		$data["shoptype"] = 100;
		$data["buyeruid"] = $this->member["uid"];
		$data["dno"] = time() . rand(1000, 9999);
		$data["posttime"] = time();
		$data["ipaddress"] = "";
		$panduan = Mysite::$app->config["man_ispass"];
		$data["status"] = 0;
		if (($panduan != 1) && ($data["paytype"] == 0)) {
			$data["status"] = 1;
		}

		$ip_l = new iplocation();
		$ipaddress = $ip_l->getaddress($ip_l->getIP());

		if (isset($ipaddress["area1"])) {
			$data["ipaddress"] = $ipaddress["ip"] . mb_convert_encoding($ipaddress["area1"], "UTF-8", "GB2312");
		}

		if (!IValidate::len($data["content"], 5, 500)) {
			$this->message("内容太简单");
		}

		if (!IValidate::len($data["buyername"], 2, 10)) {
			$this->message("姓名太短");
		}

		if (!IValidate::len($data["buyeraddress"], 2, 100)) {
			$this->message("地址不详细");
		}

		if (!IValidate::suremobi($data["buyerphone"])) {
			$this->message("请输入正确的手机号");
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "order", $data);
		$orderid = $this->mysql->insertid();
		$checksend = Mysite::$app->config["man_ispass"];

		if ($checksend != 1) {
			$orderclass = new orderclass();
			$orderclass->sendmess($orderid);
		}

		$this->success("success!");
	}

	public function postmsgbypay()
	{
		$orderid = intval(IReq::get("orderid"));

		if (empty($orderid)) {
			echo "订单号错误";
			exit();
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id = " . $orderid . "   ");
		$orderCLs = new orderclass();
		$orderCLs->writewuliustatus($orderinfo["id"], 3, $orderinfo["paytype"]);

		if ($orderinfo["is_make"] == 1) {
			$orderCLs->writewuliustatus($orderinfo["id"], 4, $orderinfo["paytype"]);
			$auto_send = Mysite::$app->config["auto_send"];

			if ($auto_send == 1) {
				$this->writewuliustatus($orderid, 6, $orderinfo["paytype"]);
			}
		}

		$orderinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "order where id='" . $orderid . "'  ");
		$orderclass = new orderclass();
		$orderclass->sendmess($orderinfo["id"]);
		echo "success";
		exit();
	}

	public function acountpayaddlog()
	{
		$id = intval(IReq::get("id"));

		if (empty($orderid)) {
			echo "充值ID获取错误";
			exit();
		}

		$acountonlinelog = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "onlinelog where id = " . $id . "  and status = 1   ");
		$memberinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid = " . $acountonlinelog["upid"] . "   ");
		$cost = $memberinfo["cost"] + $acountonlinelog["cost"];
		$memberCls = new memberclass();
		$memberCls->addmemcostlog($memberinfo["uid"], $memberinfo["username"], $memberinfo["cost"], 1, $acountonlinelog["cost"], $cost, "在线充值", $memberinfo["uid"], $memberinfo["username"]);
		echo "success";
		exit();
	}
}


