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
	public function index()
	{
		$link = IUrl::creatUrl("member/memberlist");
		$this->refunction("", $link);
	}

	public function memberlist()
	{
		$data["username"] = trim(IReq::get("username"));
		$data["email"] = trim(IReq::get("email"));
		$data["groupid"] = 5;
		$data["phone"] = trim(IReq::get("phone"));
		$where = "";
		$where = $this->sqllink($where, "username", $data["username"], "=");
		$where = $this->sqllink($where, "email", $data["email"], "=");
		$where = $this->sqllink($where, "group", $data["groupid"], "=");
		$where = $this->sqllink($where, "phone", $data["phone"], "=");
		$data["where"] = $where;
		Mysite::$app->setdata($data);
	}

	public function memberlistshop()
	{
		$data["username"] = trim(IReq::get("username"));
		$data["email"] = trim(IReq::get("email"));
		$data["groupid"] = 3;
		$data["phone"] = trim(IReq::get("phone"));
		$where = "";
		$where = $this->sqllink($where, "username", $data["username"], "=");
		$where = $this->sqllink($where, "email", $data["email"], "=");
		$where = $this->sqllink($where, "group", $data["groupid"], "=");
		$where = $this->sqllink($where, "phone", $data["phone"], "=");
		$data["where"] = $where;
		Mysite::$app->setdata($data);
	}

	public function adminlist()
	{
		$grouplist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "group  ");
		$temp = array();

		foreach ($grouplist as $key => $value ) {
			$temp[$value["id"]] = $value["name"];
		}

		$data["grouplist"] = $temp;
		Mysite::$app->setdata($data);
	}

	public function saveadmin()
	{
		$uid = IReq::get("uid");
		$username = IReq::get("username");
		$password = IReq::get("password");
		$groupid = IReq::get("groupid");

		if (empty($uid)) {
			if (empty($username)) {
				$this->message("member_emptyname");
			}

			if (empty($username)) {
				$this->message("member_emptypwd");
			}

			$testinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "admin where username='" . $username . "' ");

			if (!empty($testinfo)) {
				$this->message("member_repeatname");
			}

			$arr["username"] = $username;
			$arr["password"] = md5($password);
			$arr["time"] = time();
			$arr["groupid"] = $groupid;
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "admin", $arr);
		}
		else {
			$testinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "admin where username='" . $username . "' ");

			if (empty($testinfo)) {
				$this->message("member_editfail");
			}

			if (!empty($password)) {
				$arr["password"] = md5($password);
			}

			$arr["groupid"] = $groupid;
			$this->mysql->update(Mysite::$app->config["tablepre"] . "admin", $arr, "uid='" . $uid . "'");
		}

		$this->success("success");
	}

	public function deladmin()
	{
		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("member_emptyuid");
		}

		if ($uid == 1) {
			$this->message("member_cantdel");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "admin", "uid = '$uid'");
		$this->success("success");
	}

	public function savemember()
	{
		$uid = intval(IReq::get("uid"));
		$data["username"] = IReq::get("username");
		$data["password"] = IReq::get("password");
		$data["phone"] = IReq::get("phone");
		$data["address"] = IReq::get("address");
		$data["email"] = IReq::get("email");
		$data["group"] = IReq::get("group");
		$data["score"] = IReq::get("score");

		if (0 < $uid) {
			$checkmem = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "member where uid='" . $uid . "' ");

			if (!empty($checkmem)) {
				$cost = $checkmem["cost"];
			}
			else {
				$this->message("获取用户信息失败");
			}
		}
		else {
			$cost = IReq::get("cost");
		}

		$is_zengjian = IReq::get("is_zengjian");

		if ($is_zengjian == 0) {
			$data["cost"] = $cost;
		}

		if ($is_zengjian == 1) {
			$yuecost = IReq::get("yuecost");
			$data["cost"] = $cost + $yuecost;
		}

		if ($is_zengjian == 2) {
			$yuecost = IReq::get("yuecost");
			$data["cost"] = $cost - $yuecost;
		}

		if (!IValidate::email($data["email"])) {
			$this->message("erremail");
		}

		if (!IValidate::phone($data["phone"])) {
			$this->message("errphone");
		}

		if (empty($data["username"])) {
			$this->message("member_emptyname");
		}

		if (empty($uid)) {
			if ($this->memberCls->regester($data["email"], $data["username"], $data["password"], $data["phone"], $data["group"], "", "", $data["cost"], $data["score"])) {
				$this->success("success");
			}
			else {
				$this->message($this->memberCls->ero());
			}
		}
		else if ($this->memberCls->modify($data, $uid)) {
			$is_phonenotice = IReq::get("is_phonenotice");
			$notice_content = trim(IReq::get("notice_content"));
			$bdliyou = ($is_phonenotice == 0 ? "管理员直接操作变动" : $notice_content);

			if (0 < $is_zengjian) {
				$this->memberCls->addmemcostlog($uid, $checkmem["username"], $checkmem["cost"], $is_zengjian, $yuecost, $data["cost"], $bdliyou, ICookie::get("adminuid"), ICookie::get("adminname"));
			}

			if ($is_phonenotice == 1) {
				$this->fasongmsg($notice_content, $data["phone"]);
				$this->success("success");
			}
		}
		else {
			$this->message($this->memberCls->ero());
		}

		$this->success("操作成功");
	}

	public function fasongmsg($notice_content, $phone)
	{
		$contents = $notice_content;
		$APIServer = "http://www.tingche.com/sendtophone.php?apiuid=" . Mysite::$app->config["apiuid"];

		if (498 < strlen($contents)) {
			$content1 = substr($contents, 0, 498);
			$weblink = $APIServer . "&key=" . trim(Mysite::$app->config["sms86ac"]) . "&code=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&msgcontent=" . urlencode($content1) . "";
			$contentcccc = file_get_contents($weblink);
			$content2 = substr($contents, 498, strlen($contents));
			$weblink = $APIServer . "&key=" . trim(Mysite::$app->config["sms86ac"]) . "&code=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&msgcontent=" . urlencode($content2) . "";
			$contentcccc = file_get_contents($weblink);
			logwrite("短信商家发送结果:" . $contentcccc);
		}
		else {
			$weblink = $APIServer . "&key=" . trim(Mysite::$app->config["sms86ac"]) . "&code=" . trim(Mysite::$app->config["sms86pd"]) . "&hm=" . $phone . "&msgcontent=" . urlencode($contents) . "";
			$contentcccc = file_get_contents($weblink);
			logwrite("短信发送结果:" . $contentcccc);
		}
	}

	public function delmember()
	{
		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("member_emptyuid");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "member", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "oauth", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "giftlog", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "address", "userid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "comment", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "collect", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "card", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "ask", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "juan", "uid = '$uid'");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "memberlog", "userid = '$uid'");
		$testinfo = $this->mysql->select_one("select id from " . Mysite::$app->config["tablepre"] . "shop where uid='" . $uid . "' ");

		if (!empty($testinfo)) {
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shop", "id = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopfast", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopattr", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "shopsearch", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "areatoadd", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "areashop", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goods", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "goodstype", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "order", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "orderdet", "shopid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "searkey", " type=1 and goid = '" . $testinfo["id"] . "'");
			$this->mysql->delete(Mysite::$app->config["tablepre"] . "rule", "shopid = '" . $testinfo["id"] . "'");
		}

		$this->success("success");
	}

	public function savegroup()
	{
		$id = intval(IReq::get("id"));
		$data["name"] = IReq::get("name");
		$type = IReq::get("type");

		if (empty($data["name"])) {
			$this->message("member_group_noexit");
		}

		$data["type"] = ($type == 1 ? "admin" : "font");

		if (empty($id)) {
			$testinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "group where name='" . $data["name"] . "' ");

			if (!empty($testinfo)) {
				$this->message("member_group_repeat");
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "group", $data);
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "group", $data, "id='" . $id . "'");
		}

		$this->success("success");
	}

	public function delgroup()
	{
		$uid = intval(IReq::get("id"));

		if (empty($uid)) {
			$this->message("member_emptyuid");
		}

		if (in_array($uid, array(1, 2, 3, 4, 5))) {
			$this->message("member_group_system");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "group", "id = '$uid'");
		$this->success("success");
	}

	public function adminloginout()
	{
		ICookie::clear("adminname");
		ICookie::clear("adminpwd");
		ICookie::clear("adminuid");
		ICookie::clear("adminshopid");
		$link = IUrl::creatUrl("member/adminlogin");
		$this->refunction("", $link);
	}

	public function adminmodify()
	{
		$oldpwd = trim(IReq::get("oldpwd"));
		$pwd = trim(IReq::get("pwd"));

		if (empty($oldpwd)) {
			$this->message("emptyoldpwd");
		}

		if (empty($pwd)) {
			$this->message("emptynewpwd");
		}

		if ($this->admin["password"] != md5($oldpwd)) {
			$this->message("oldpwderr");
		}

		$arr["password"] = md5($pwd);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "admin", $arr, "uid='" . $this->admin["uid"] . "'");
		$this->success("success");
	}

	public function membergrade()
	{
		$configs = new config("membergrade.php", hopedir);
		$data["membergrade"] = $configs->getInfo();
		Mysite::$app->setdata($data);
	}

	public function savemembergrade()
	{
		$gradename = IFilter::act(IReq::get("gradename"));
		$from = IFilter::act(IReq::get("from"));
		$to = IFilter::act(IReq::get("to"));

		if (!is_array($gradename)) {
			$this->message("member_grade_formaterr");
		}

		if (count($gradename) != count($from)) {
			$this->message("member_grade_counterr");
		}

		if (count($gradename) != count($to)) {
			$this->message("member_grade_jifenerr");
		}

		$newarray = array();

		foreach ($gradename as $key => $value ) {
			$temp["gradename"] = $value;
			$temp["from"] = $from[$key];
			$temp["to"] = $to[$key];
			$newarray[] = $temp;
		}

		$configData = var_export($newarray, true);
		$configStr = "<?php return $configData?>";
		$fileObj = new IFile(hopedir . "/config/membergrade.php", "w+");
		$fileObj->write($configStr);
		$this->success("success");
	}

	public function gradeinstro()
	{
		$configs = new config("membergrade.php", hopedir);
		$data["membergrade"] = $configs->getInfo();
		$data["perlong"] = intval(900 / count($data["membergrade"]));
		Mysite::$app->setdata($data);
	}

	public function setmemsafepwd()
	{
		$uid = intval($this->admin["uid"]);
		$data["id"] = $uid;
		$testinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "safepwd where adminuid='" . $uid . "' ");
		print_r($testinfo);
		Mysite::$app->setdata($data);
	}

	public function savememsafepwd()
	{
		$uid = IFilter::act(IReq::get("uid"));
		$checksafeinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "safepwd where adminuid='" . $uid . "' ");

		if (!empty($checksafeinfo)) {
			$this->message("管理员已设置过验证密码");
		}

		$pwd = IFilter::act(IReq::get("password"));

		if (empty($pwd)) {
			$this->message("验证密码为空！");
		}

		$data["adminuid"] = $uid;
		$data["password"] = md5($pwd);
		$data["addtime"] = time();
		$data["type"] = 0;
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "safepwd", $data);
		$this->success("success");
	}

	public function xiugaimemsafepwd()
	{
		$oldpwd = trim(IReq::get("oldpwd"));
		$md5oldpwd = md5(trim(IReq::get("oldpwd")));
		$newpwd = trim(IReq::get("newpwd"));

		if (empty($oldpwd)) {
			$this->message("旧密码不能为空！");
		}

		$checksafeinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "safepwd where adminuid='" . $this->admin["uid"] . "'   ");

		if (empty($checksafeinfo)) {
			$this->message("用户不存在！");
		}

		if ($checksafeinfo["password"] != md5($oldpwd)) {
			$this->message("旧密码不正确！");
		}

		if (empty($newpwd)) {
			$this->message("新密码不能为空！");
		}

		$arr["password"] = md5($newpwd);
		$this->mysql->update(Mysite::$app->config["tablepre"] . "safepwd", $arr, "adminuid='" . $this->admin["uid"] . "'");
		$this->success("success");
	}

	public function memcostloglist()
	{
		$querytype = IReq::get("querytype");
		$searchvalue = IReq::get("searchvalue");
		$starttime = IReq::get("starttime");
		$endtime = IReq::get("endtime");
		$nowday = date("Y-m-d", time());
		$starttime = (empty($starttime) ? $nowday : $starttime);
		$endtime = (empty($endtime) ? $nowday : $endtime);
		$where = "  where addtime > " . strtotime($starttime . " 00:00:00") . " and addtime < " . strtotime($endtime . " 23:59:59");
		$data["starttime"] = $starttime;
		$data["endtime"] = $endtime;
		$newlink = "/starttime/" . $starttime . "/endtime/" . $endtime;
		$data["searchvalue"] = "";
		$data["querytype"] = "";

		if (!empty($querytype)) {
			if (!empty($searchvalue)) {
				$data["searchvalue"] = $searchvalue;
				$where .= " and " . $querytype . " LIKE '%" . $searchvalue . "%' ";
				$newlink .= "/searchvalue/" . $searchvalue . "/querytype/" . $querytype;
				$data["querytype"] = $querytype;
			}
		}

		$link = IUrl::creatUrl("/adminpage/member/module/memcostloglist" . $newlink);
		$pageshow = new page();
		$pageshow->setpage(IReq::get("page"), 10);
		$memcostloglist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "memcostlog " . $where . "  order by addtime desc limit " . $pageshow->startnum() . ", " . $pageshow->getsize() . "");
		$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "memcostlog  " . $where . " ");
		$pageshow->setnum($shuliang);
		$data["pagecontent"] = $pageshow->getpagebar($link);
		$data["memcostloglist"] = $memcostloglist;
		Mysite::$app->setdata($data);
	}
}


