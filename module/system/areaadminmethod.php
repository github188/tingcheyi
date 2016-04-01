<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class method extends areaadminbaseclass
{
	public function index()
	{
		$mftime = strtotime(date("Y-m", time()));
		$metime = time();
		$dftime = strtotime(date("Y-m-d", time()));
		$detime = time();
		$tjdata["dayallorder"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order  where posttime > $dftime and posttime < $detime  ");
		$tjdata["dayworder"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order  where posttime > $dftime and posttime < $detime  and status = 0");
		$tjdata["dayporder"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order  where posttime > $dftime and posttime < $detime  and status > 0 and status < 4");
		$tjdata["monthallorder"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order  where posttime > $mftime and posttime < $metime  and status = 3");
		$tjdata["allorder"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order  where  status = 3");
		$tjdata["onlineshop"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shop  where  is_pass = 1");
		$tjdata["wshop"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shop  where  is_pass = 0");
		$tjdata["pmember"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "member ");
		$tjdata["market"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "order where shopid=0 ");
		$tjdata["marketg"] = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "goods where shopid=0 ");
		$data["tjdata"] = $tjdata;
		$data["serverurl"] = Mysite::$app->config["serverurl"];
		Mysite::$app->setdata($data);
	}
}


