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
	public function savemodulepaixu()
	{
		$orderid = intval(IFilter::act(IReq::get("orderid")));
		$name = trim(IFilter::act(IReq::get("name")));
		$data["orderid"] = $orderid;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "appmudel", $data, "name='" . $name . "'");
		$this->success("success");
	}

	public function appset()
	{
		$data["appmodulelist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "appmudel where  FIND_IN_SET( name , 'collect,newuser,gift')  order by orderid asc  limit 0,100");
		$catparent = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype  where  type='checkbox' order by cattype asc limit 0,100");
		$catlist = array();

		foreach ($catparent as $key => $value ) {
			$tempcat = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shoptype  where parent_id = '" . $value["id"] . "'  limit 0,100");

			foreach ($tempcat as $k => $v ) {
				$catlist[] = $v;
			}
		}

		$data["catarr"] = array("停车", "养车");
		$data["catlist"] = $catlist;
		$data["appadvlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "appadv   order by orderid asc   limit 0,100");
		$config = new config("appset.php", hopedir);
		$tempinfo = $config->getInfo();
		$data["appinfo"] = $tempinfo;
		Mysite::$app->setdata($data);
	}

	public function appmdimg()
	{
		$mudulename = trim(IFilter::act(IReq::get("mudulename")));
		$checkinfomd = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "appmudel where name='" . $mudulename . "' limit 0,100");

		if (empty($checkinfomd)) {
			$this->message("模块不存在");
		}

		$imgurl = trim(IFilter::act(IReq::get("imgurl")));

		if (empty($imgurl)) {
			$this->message("图片不存在");
		}

		$data["imgurl"] = $imgurl;
		$this->mysql->update(Mysite::$app->config["tablepre"] . "appmudel", $data, "name='" . $mudulename . "'");
		$this->success("成功");
	}

	public function docontroladv()
	{
		$ctrlname = trim(IFilter::act(IReq::get("ctrlname")));

		if ($ctrlname == "is_show") {
			$modulename = trim(IFilter::act(IReq::get("modulename")));

			if (empty($modulename)) {
				$this->message("模块不存在");
			}

			$checkmodule = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "appmudel where name='" . $modulename . "' limit 0,100");

			if (empty($checkmodule)) {
				$this->message("模块不存在");
			}

			$data["is_display"] = intval(IFilter::act(IReq::get("modulevalue")));
			$this->mysql->update(Mysite::$app->config["tablepre"] . "appmudel", $data, "name='" . $modulename . "'");
			$this->success("成功");
		}
		else if ($ctrlname == "is_install") {
			$modulename = trim(IFilter::act(IReq::get("modulename")));

			if (empty($modulename)) {
				$this->message("模块不存在");
			}

			$checkmodule = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "appmudel where name='" . $modulename . "' limit 0,100");

			if (empty($checkmodule)) {
				$this->message("模块不存在");
			}

			$data["is_install"] = intval(IFilter::act(IReq::get("modulevalue")));

			if ($data["is_install"] == 0) {
				$data["is_display"] = 0;
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "appmudel", $data, "name='" . $modulename . "'");
			$this->success("成功");
		}

		exit();
	}

	public function addgd()
	{
		$cattypeid = intval(IFilter::act(IReq::get("typeid")));
		$name = trim(IFilter::act(IReq::get("name")));
		$appposition = intval(IFilter::act(IReq::get("appposition")));
		$id = intval(IFilter::act(IReq::get("id")));
		$orderid = intval(IFilter::act(IReq::get("orderid")));

		if (empty($cattypeid)) {
			$this->message("未选择模块");
		}

		if (empty($name)) {
			$this->message("未录入名称");
		}

		if (empty($appposition)) {
			$this->message("未设置展示类型");
		}

		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "shoptype  where  id='" . $cattypeid . "' order by cattype asc limit 0,100");

		if (empty($checkinfo)) {
			$this->message("未查找到分类值");
		}

		if (0 < $id) {
			$checkinfo2 = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "appadv  where  param='" . $cattypeid . "' and  type = '" . $appposition . "' order by id asc limit 0,100");

			if ($checkinfo2 != 1) {
				$this->message("未添加的模块不能编辑");
			}
		}
		else {
			$checkinfo2 = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "appadv  where  param='" . $cattypeid . "' and  type = '" . $appposition . "' order by id asc limit 0,100");

			if (!empty($checkinfo2)) {
				$this->message("该值已添加");
			}
		}

		$data["orderid"] = $orderid;
		$data["name"] = $name;
		$data["type"] = $appposition;
		$data["img"] = trim(IFilter::act(IReq::get("imgurl")));

		if (empty($data["img"])) {
			$this->message("图片为空");
		}

		$data["activity"] = (empty($checkinfo["cattype"]) ? "waimai" : "market");
		$data["param"] = $cattypeid;

		if (0 < $id) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "appadv", $data, "id='" . $id . "'");
		}
		else {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "appadv", $data);
		}

		$this->success("成功");
	}

	public function addad()
	{
		$name = trim(IFilter::act(IReq::get("name")));
		$appposition = intval(IFilter::act(IReq::get("appposition")));
		$id = intval(IFilter::act(IReq::get("id")));

		if (empty($name)) {
			$this->message("未录入名称");
		}

		if (empty($appposition)) {
			$this->message("未设置展示类型");
		}

		$data["name"] = $name;
		$data["type"] = $appposition;
		$data["img"] = trim(IFilter::act(IReq::get("imgurl")));

		if (empty($data["img"])) {
			$this->message("图片为空");
		}

		$data["activity"] = "";
		$data["param"] = 0;

		if (0 < $id) {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "appadv", $data, "id='" . $id . "'");
		}
		else {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "appadv", $data);
		}

		$this->success("成功");
	}

	public function delappadv()
	{
		$id = intval(IFilter::act(IReq::get("id")));
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "appadv", "id  = '" . $id . "' ");
		$this->success("操作成功");
	}

	public function appindexshow()
	{
		$config = new config("appset.php", hopedir);
		$tempinfo = $config->getInfo();
		$type = trim(IFilter::act(IReq::get("type")));

		if ($type == "APPindex") {
			$APPindex = intval(IFilter::act(IReq::get("APPindex")));
			$tempinfo["APPindex"] = $APPindex;
		}
		else if ($type == "apppayonline") {
			$apppayonline = intval(IFilter::act(IReq::get("apppayonline")));
			$tempinfo["apppayonline"] = $apppayonline;
		}
		else if ($type == "apppayacount") {
			$apppayacount = intval(IFilter::act(IReq::get("apppayacount")));
			$tempinfo["apppayacount"] = $apppayacount;
		}
		else if ($type == "appdataver") {
			$appdataver = intval(IFilter::act(IReq::get("appdataver")));
			$tempinfo["appdataver"] = $appdataver;
		}
		else {
			$this->message("未定义的操作");
		}

		$config->write($tempinfo);
		$this->success("success");
	}

	public function saveappset()
	{
		$savedata["appsecret"] = trim(IFilter::act(IReq::get("appsecret")));
		$savedata["appuser"] = trim(IFilter::act(IReq::get("appuser")));
		$savedata["appsecret2"] = trim(IFilter::act(IReq::get("appsecret2")));
		$savedata["appuser2"] = trim(IFilter::act(IReq::get("appuser2")));
		$savedata["appuser3"] = trim(IFilter::act(IReq::get("appuser3")));
		$savedata["appsecret3"] = trim(IFilter::act(IReq::get("appsecret3")));
		$savedata["appvison1"] = intval(IFilter::act(IReq::get("appvison1")));
		$savedata["appdownload1"] = IFilter::act(IReq::get("appdownload1"));
		$savedata["appvison2"] = intval(IFilter::act(IReq::get("appvison2")));
		$savedata["appdownload2"] = IFilter::act(IReq::get("appdownload2"));
		$savedata["appvison3"] = intval(IFilter::act(IReq::get("appvison3")));
		$savedata["appdownload3"] = IFilter::act(IReq::get("appdownload3"));
		$config = new config("hopeconfig.php", hopedir);
		$config->write($savedata);
		$this->success("success");
	}

	public function buyerapp()
	{
		$where = "";
		$searchvalue = trim(IReq::get("searchvalue"));
		$data["searchvalue"] = "";
		$newlink = "";

		if (!empty($searchvalue)) {
			$data["searchvalue"] = $searchvalue;
			$where = "  where   b.username like '%" . $searchvalue . "%' ";
			$newlink = "/searchvalue/" . $searchvalue;
		}

		$link = IUrl::creatUrl("/adminpage/app/module/buyerapp" . $newlink);
		$this->pageCls->setpage(IReq::get("page"), 15);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "appbuyerlogin  as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid  " . $where . " order by a.addtime   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "appbuyerlogin as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid   " . $where . "   ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function delappbuyer()
	{
		$id = IFilter::act(IReq::get("id"));
		$ids = (is_array($id) ? join(",", $id) : $id);

		if (empty($ids)) {
			$this->message("删除id错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "appbuyerlogin", "uid in(" . $ids . ")");
		$this->success("success");
	}

	public function sendmsg()
	{
		$uid = intval(IFilter::act(IReq::get("uid")));
		$content = IFilter::act(IReq::get("content"));

		if (empty($uid)) {
			$this->message("用户UID不能为空");
		}

		if (empty($content)) {
			$this->message("信息内容不能为空");
		}

		$appcheck = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "appbuyerlogin where uid = '" . $uid . "'   ");

		if (empty($appcheck)) {
			$this->message("用户信息不存在");
		}

		$appCls = new appbuyclass();
		$backinfo = $appCls->sendmsg($appcheck["userid"], $appcheck["channelid"], "管理员通知", $content, $messagetype = 1);

		if ($backinfo == "ok") {
			$this->success("success");
		}
		else {
			$this->message($backinfo);
		}
	}

	public function shopapp()
	{
		$where = "";
		$searchvalue = trim(IReq::get("searchvalue"));
		$data["searchvalue"] = "";
		$newlink = "";

		if (!empty($searchvalue)) {
			$data["searchvalue"] = $searchvalue;
			$where = "  where     b.username like '%" . $searchvalue . "%' ";
			$newlink = "/searchvalue/" . $searchvalue;
		}

		$link = IUrl::creatUrl("/adminpage/app/module/shopapp" . $newlink);
		$this->pageCls->setpage(IReq::get("page"), 15);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "applogin  as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid  " . $where . " order by a.addtime   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "applogin as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid   " . $where . "   ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function delappshop()
	{
		$id = IFilter::act(IReq::get("id"));
		$ids = (is_array($id) ? join(",", $id) : $id);

		if (empty($ids)) {
			$this->message("删除id错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "applogin", "uid in(" . $ids . ")");
		$this->success("success");
	}

	public function sendshopmsg()
	{
		$uid = intval(IFilter::act(IReq::get("uid")));
		$content = IFilter::act(IReq::get("content"));

		if (empty($uid)) {
			$this->message("用户UID不能为空");
		}

		if (empty($content)) {
			$this->message("信息内容不能为空");
		}

		$appcheck = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "applogin where uid = '" . $uid . "'   ");

		if (empty($appcheck)) {
			$this->message("用户信息不存在");
		}

		$appCls = new appclass();
		$backinfo = $appCls->sendmsg($appcheck["userid"], $appcheck["channelid"], "管理员通知", $content, $messagetype = 1);

		if ($backinfo == "ok") {
			$this->success("success");
		}
		else {
			$this->message($backinfo);
		}
	}

	public function psapp()
	{
		$where = "";
		$searchvalue = trim(IReq::get("searchvalue"));
		$data["searchvalue"] = "";
		$newlink = "";

		if (!empty($searchvalue)) {
			$data["searchvalue"] = $searchvalue;
			$where = "  where     b.username like '%" . $searchvalue . "%' ";
			$newlink = "/searchvalue/" . $searchvalue;
		}

		$link = IUrl::creatUrl("/adminpage/app/module/psapp" . $newlink);
		$this->pageCls->setpage(IReq::get("page"), 15);
		$data["list"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "apploginps  as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid  " . $where . " order by a.addtime   limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "apploginps as a left join " . Mysite::$app->config["tablepre"] . "member as b on a.uid=b.uid   " . $where . "   ");
		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar($link);
		Mysite::$app->setdata($data);
	}

	public function delappps()
	{
		$id = IFilter::act(IReq::get("id"));
		$ids = (is_array($id) ? join(",", $id) : $id);

		if (empty($ids)) {
			$this->message("删除id错误");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "apploginps", "uid in(" . $ids . ")");
		$this->success("success");
	}

	public function sendpsmsg()
	{
		$uid = intval(IFilter::act(IReq::get("uid")));
		$content = IFilter::act(IReq::get("content"));

		if (empty($uid)) {
			$this->message("用户UID不能为空");
		}

		if (empty($content)) {
			$this->message("信息内容不能为空");
		}

		$appcheck = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "apploginps where uid = '" . $uid . "'   ");

		if (empty($appcheck)) {
			$this->message("用户信息不存在");
		}

		$appCls = new apppsyclass();
		$backinfo = $appCls->sendmsg($appcheck["userid"], $appcheck["channelid"], "管理员通知", $content, $messagetype = 1);

		if ($backinfo == "ok") {
			$this->success("success");
		}
		else {
			$this->message($backinfo);
		}
	}
}


