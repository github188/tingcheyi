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
	public function area()
	{
		$selecttype = intval(IFilter::act(IReq::get("selecttype")));
		$tempselecttype = (in_array($selecttype, array(0, 1, 2, 3)) ? $selecttype : 0);
		$wherearray = array("", " where  addtime > " . strtotime("-1 month"), " where addtime > " . strtotime("-7 day"), " where addtime > " . strtotime(date("Y-m-d", time())));
		$arealist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "area where parent_id=0");
		$total = 0;
		$nowdata = array();

		foreach ($arealist as $key => $value ) {
			$where = (empty($wherearray[$tempselecttype]) ? " where FIND_IN_SET('" . $value["id"] . "',`areaids`)" : $wherearray[$tempselecttype] . " and FIND_IN_SET('" . $value["id"] . "',`areaids`)");
			$value["shuliang"] = $this->mysql->counts("select id from " . Mysite::$app->config["tablepre"] . "order " . $where . "    ");
			$nowdata[] = $value;
			$total = $total + $value["shuliang"];
		}

		$data["total"] = $total;
		$data["allshu"] = count($arealist);
		$data["arealist"] = $nowdata;
		$data["selecttype"] = $selecttype;
		Mysite::$app->setdata($data);
	}

	public function shop()
	{
		$selecttype = intval(IFilter::act(IReq::get("selecttype")));
		$tempselecttype = (in_array($selecttype, array(0, 1, 2, 3)) ? $selecttype : 0);
		$wherearray = array("", "  addtime > " . strtotime("-1 month"), "  addtime > " . strtotime("-7 day"), "  addtime > " . strtotime(date("Y-m-d", time())));
		$where1 = (empty($wherearray[$tempselecttype]) ? "" : " where " . $wherearray[$tempselecttype]);
		$where2 = (empty($wherearray[$tempselecttype]) ? "" : " and " . $wherearray[$tempselecttype]);
		$orderlist = $this->mysql->getarr("select count(id) as shuliang ,shopid from " . Mysite::$app->config["tablepre"] . "order  " . $where1 . "   group by shopid   order by shuliang desc  limit 0,10");
		$data["list"] = array();
		$data["newdata"] = array();

		foreach ($orderlist as $key => $value ) {
			if (0 < $value["shopid"]) {
				$shopinfo = $this->mysql->select_one("select  shopname,id from " . Mysite::$app->config["tablepre"] . "shop  where id=" . $value["shopid"] . " ");
				$value["det"] = $this->mysql->getarr("select count(id) as shuliang ,shopid from " . Mysite::$app->config["tablepre"] . "order where  shopid =" . $value["shopid"] . " " . $where2 . "  order by id desc  limit 0,10");
				$value["shopname"] = (isset($shopinfo["shopname"]) ? $shopinfo["shopname"] : "不存在");
				$data["list"][] = $value;
			}
		}

		$timearr = array("所有时间", "最近一月", "最近一周", "当天");
		$data["typeshow"] = $timearr[$tempselecttype];
		$data["selecttype"] = $selecttype;
		Mysite::$app->setdata($data);
	}

	public function goods()
	{
		$selecttype = intval(IFilter::act(IReq::get("selecttype")));
		$tempselecttype = (in_array($selecttype, array(0, 1, 2, 3)) ? $selecttype : 0);
		$wherearray = array("", "  ord.addtime > " . strtotime("-1 month"), "  ord.addtime > " . strtotime("-7 day"), "  ord.addtime > " . strtotime(date("Y-m-d", time())));
		$where1 = (empty($wherearray[$tempselecttype]) ? "" : " where " . $wherearray[$tempselecttype]);
		$where2 = (empty($wherearray[$tempselecttype]) ? "" : " and " . $wherearray[$tempselecttype]);
		$data["list"] = $this->mysql->getarr("select count(ordet.id) as shuliang ,ordet.goodsid,ordet.goodsname as shopname from " . Mysite::$app->config["tablepre"] . "orderdet  as ordet left join  " . Mysite::$app->config["tablepre"] . "order as ord on ordet.order_id = ord.id  " . $where1 . " group by ordet.goodsid   order by shuliang desc  limit 0,5");
		$data["selecttype"] = $selecttype;
		Mysite::$app->setdata($data);
	}

	public function user()
	{
		$selecttype = intval(IFilter::act(IReq::get("selecttype")));
		$tempselecttype = (in_array($selecttype, array(0, 1, 2, 3)) ? $selecttype : 0);
		$wherearray = array("", " where addtime > " . strtotime("-1 month"), " where addtime > " . strtotime("-7 day"), " where addtime > " . strtotime(date("Y-m-d", time())));
		$tempdata = $this->mysql->getarr("select count(id) as shuliang ,DATE_FORMAT(FROM_UNIXTIME(`addtime`),'%k') as month from " . Mysite::$app->config["tablepre"] . "order  " . $wherearray[$tempselecttype] . " group by month    order by month desc  limit 0,10");
		$list = array();

		if (is_array($tempdata)) {
			foreach ($tempdata as $key => $value ) {
				$list[$value["month"]] = $value["shuliang"];
			}
		}

		$data["list"] = $list;
		$data["selecttype"] = $selecttype;
		Mysite::$app->setdata($data);
	}
}


