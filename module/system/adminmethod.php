<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class method extends adminbaseclass
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

	public function saveotherset()
	{
		$siteinfo["addresslimit"] = intval(IReq::get("addresslimit"));
		$siteinfo["shoptypelimit"] = intval(IReq::get("shoptypelimit"));
		$siteinfo["shopgoodslimit"] = intval(IReq::get("shopgoodslimit"));
		$siteinfo["allowedcode"] = intval(IReq::get("allowedcode"));
		$siteinfo["allowedsendshop"] = intval(IReq::get("allowedsendshop"));
		$siteinfo["allowedsendbuyer"] = intval(IReq::get("allowedsendbuyer"));
		$siteinfo["ordercheckphone"] = intval(IReq::get("ordercheckphone"));
		$siteinfo["man_ispass"] = intval(IReq::get("man_ispass"));
		$siteinfo["open_acout"] = intval(IReq::get("open_acout"));
		$siteinfo["is_daopay"] = intval(IReq::get("is_daopay"));
		$siteinfo["noticeshopemail"] = intval(IReq::get("noticeshopemail"));
		$siteinfo["auto_send"] = intval(IReq::get("auto_send"));
		$siteinfo["regestercode"] = intval(IReq::get("regestercode"));
		$siteinfo["allowedguestbuy"] = intval(IReq::get("allowedguestbuy"));
		$siteinfo["allowed_is_make"] = intval(IReq::get("allowed_is_make"));
		$siteinfo["weixinpay"] = intval(IReq::get("weixinpay"));
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

	public function saveset()
	{
		$sitename = IReq::get("sitename");
		$description = IReq::get("description");
		$keywords = IReq::get("keywords");
		$beian = IReq::get("beian");
		$yjin = IReq::get("yjin");
		$yjin = round($yjin, 2);
		$cityname = trim(IReq::get("cityname"));
		$shoplogo = trim(IReq::get("shoplogo"));
		$userlogo = trim(IReq::get("userlogo"));
		$notice = trim(IReq::get("notice"));
		$sitelogo = trim(IReq::get("sitelogo"));
		$area_grade = intval(IReq::get("area_grade"));
		$metadata = trim(IReq::get("metadata"));
		$footerdata = trim(IReq::get("footerdata"));
		$guidetype = intval(IReq::get("guidetype"));
		$html5logo = trim(IReq::get("html5logo"));
		$baidumapkey = trim(IReq::get("baidumapkey"));
		$baidumapsecret = trim(IReq::get("baidumapsecret"));
		$siteinfo["baidulng"] = trim(IReq::get("baidulng"));
		$siteinfo["baidulat"] = trim(IReq::get("baidulat"));

		if (empty($sitename)) {
			$this->message("system_emptysitename");
		}

		if (empty($description)) {
			$this->message("system_emptydes");
		}

		if (empty($keywords)) {
			$this->message("system_emptyseo");
		}

		if (empty($cityname)) {
			$this->message("system_emptycity");
		}

		$siteinfo["litel"] = IReq::get("litel");
		$siteinfo["sitename"] = $sitename;
		$siteinfo["description"] = $description;
		$siteinfo["keywords"] = $keywords;
		$siteinfo["beian"] = $beian;
		$siteinfo["yjin"] = $yjin;
		$siteinfo["cityname"] = $cityname;
		$siteinfo["shoplogo"] = $shoplogo;
		$siteinfo["userlogo"] = $userlogo;
		$siteinfo["notice"] = $notice;
		$siteinfo["sitelogo"] = $sitelogo;
		$siteinfo["metadata"] = $metadata;
		$siteinfo["footerdata"] = $footerdata;
		$siteinfo["guidetype"] = $guidetype;
		$siteinfo["html5logo"] = $html5logo;
		$siteinfo["baidumapkey"] = $baidumapkey;
		$siteinfo["baidumapsecret"] = $baidumapsecret;
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$configs = new config("hopeconfig.php", hopedir);
		$tests = $config->getInfo();
		$this->success("success");
	}

	public function savesitebk()
	{
		$siteinfo["sitebk"] = IReq::get("userlogo");
		$siteinfo["is_water"] = IReq::get("is_water");
		$siteinfo["water_pos"] = IReq::get("water_pos");
		$siteinfo["logo_water"] = IReq::get("logo_water");
		$siteinfo["text_water"] = IReq::get("text_water");
		$siteinfo["size_water"] = IReq::get("size_water");
		$siteinfo["color_water"] = IReq::get("color_water");
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

	public function savetoplink()
	{
		$arrtypename = IReq::get("typename");
		$arrtypeurl = IReq::get("typeurl");
		$arrtypeorder = IReq::get("typeorder");

		if (empty($arrtypename)) {
			$this->message("empty_link");
		}

		if (is_array($arrtypename)) {
			$orderinfo = array();

			foreach ($arrtypename as $key => $value ) {
				if (isset($arrtypeorder[$key])) {
					$dokey = (!empty($arrtypeorder[$key]) ? $arrtypeorder[$key] : 0);
					array_push($orderinfo, $dokey);
				}
				else {
					array_push($orderinfo, 0);
				}
			}

			$orderinfo = array_unique($orderinfo);
			sort($orderinfo);
			$newinfo = array();

			foreach ($orderinfo as $key => $value ) {
				foreach ($arrtypename as $k => $v ) {
					if (isset($arrtypeorder[$k])) {
						$checkcode = (!empty($arrtypeorder[$k]) ? $arrtypeorder[$k] : 0);
					}
					else {
						$checkcode = 0;
					}

					if ($checkcode == $value) {
						$data["typename"] = $v;
						$data["typeurl"] = (isset($arrtypeurl[$k]) ? $arrtypeurl[$k] : "");
						$data["typeorder"] = $checkcode;
						$newinfo[] = $data;
					}
				}
			}
		}
		else {
			$newinfo["typename"] = $arrtypename;
			$newinfo["typeurl"] = $arrtypeurl;
			$newinfo["typeorder"] = $arrtypeorder;
		}

		$siteinfo["footlink"] = serialize($newinfo);
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

	public function savefootinfo()
	{
		$arrtypename = IReq::get("typename");
		$arrtypeurl = IReq::get("typeurl");
		$arrtypeorder = IReq::get("typeorder");

		if (empty($arrtypename)) {
			$this->message("empty_link");
		}

		if (is_array($arrtypename)) {
			$orderinfo = array();

			foreach ($arrtypename as $key => $value ) {
				if (isset($arrtypeorder[$key])) {
					$dokey = (!empty($arrtypeorder[$key]) ? $arrtypeorder[$key] : 0);
					array_push($orderinfo, $dokey);
				}
				else {
					array_push($orderinfo, 0);
				}
			}

			$orderinfo = array_unique($orderinfo);
			sort($orderinfo);
			$newinfo = array();

			foreach ($orderinfo as $key => $value ) {
				foreach ($arrtypename as $k => $v ) {
					if (isset($arrtypeorder[$k])) {
						$checkcode = (!empty($arrtypeorder[$k]) ? $arrtypeorder[$k] : 0);
					}
					else {
						$checkcode = 0;
					}

					if ($checkcode == $value) {
						$data["typename"] = $v;
						$data["typeurl"] = (isset($arrtypeurl[$k]) ? $arrtypeurl[$k] : "");
						$data["typeorder"] = $checkcode;
						$newinfo[] = $data;
					}
				}
			}
		}
		else {
			$newinfo["typename"] = $arrtypename;
			$newinfo["typeurl"] = $arrtypeurl;
			$newinfo["typeorder"] = $arrtypeorder;
		}

		$siteinfo["toplink"] = serialize($newinfo);
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

	public function savemodule()
	{
		$arr["name"] = IFilter::act(IReq::get("name"));
		$arr["cnname"] = IFilter::act(IReq::get("cnname"));
		$arr["install"] = 1;
		$is_main = intval(IFilter::act(IReq::get("is_main")));

		if (empty($arr["name"])) {
			$this->message("empty_filename");
		}

		if (empty($arr["cnname"])) {
			$this->message("empty_CNname");
		}

		if (empty($is_main)) {
			$this->message("module_nochoice");
		}

		$parent_id = intval(IFilter::act(IReq::get("parent_id")));

		if ($is_main == 1) {
			$arr["parent_id"] = 0;
		}
		else {
			$arr["parent_id"] = $parent_id;

			if (empty($parent_id)) {
				$this->message("module_noparent");
			}
		}

		$this->mysql->insert(Mysite::$app->config["tablepre"] . "module", $arr);
		$moduleid = $this->mysql->insertid();
		$menudata["name"] = "limitset";
		$menudata["cnname"] = "权限设置";
		$menudata["moduleid"] = $moduleid;
		$menudata["group"] = 1;
		$menudata["id"] = 0;
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "menu", $menudata);
		$limitdata["name"] = "limitset";
		$limitdata["cnname"] = "权限列表";
		$limitdata["moduleid"] = $moduleid;
		$limitdata["group"] = 1;
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "usrlimit", $limitdata);
		$limitdata["name"] = "savelimit";
		$limitdata["cnname"] = "保存权限设置";
		$this->mysql->insert(Mysite::$app->config["tablepre"] . "usrlimit", $limitdata);
		$this->success("success");
	}

	public function tempset()
	{
		$logindir = hopedir . "/templates";
		$dirArray[] = NULL;

		if (false != $handle = opendir($logindir)) {
			$i = 0;

			while (false !== $file = readdir($handle)) {
				if (($file != ".") && ($file != "..") && !strpos($file, ".")) {
					if (file_exists($logindir . "/" . $file . "/stro.txt")) {
						$license = file_get_contents($logindir . "/" . $file . "/stro.txt");
						$dirArray[$i]["instro"] = nl2br(str_replace(" ", "&nbsp;", htmlspecialchars($license, ENT_COMPAT, "UTF-8")));
						$dirArray[$i]["img"] = (file_exists($logindir . "/" . $file . "/instro.jpg") ? Mysite::$app->config["siteurl"] . "/templates/" . $file . "/instro.jpg" : "");
						$dirArray[$i]["filename"] = $file;
						$i++;
					}
				}
			}

			closedir($handle);
		}

		$data["apilist"] = $dirArray;
		Mysite::$app->setdata($data);
	}

	public function savetempset()
	{
		$tempname = IFilter::act(IReq::get("modulename"));

		if (empty($tempname)) {
			$this->message("module_emptyname");
		}

		$logindir = hopedir . "/templates";

		if (!file_exists($logindir . "/" . $tempname . "/stro.txt")) {
			$this->message("template_noexit");
		}

		$siteinfo["sitetemp"] = $tempname;
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		IFile::clearDir("templates_c");
		$this->success("success");
	}

	public function savemobiletempset()
	{
		$tempname = IFilter::act(IReq::get("mobilemodule"));

		if (empty($tempname)) {
			$this->message("module_emptyname");
		}

		$siteinfo["mobilemodule"] = $tempname;
		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		IFile::clearDir("templates_c");
		$this->success("success");
	}

	public function delmodule()
	{
		$id = intval(IFilter::act(IReq::get("id")));
		$checinfo = $this->mysql->select_one("select *  from " . Mysite::$app->config["tablepre"] . "module  where id= '" . $id . "'  ");

		if (empty($checinfo)) {
			$this->message("module_noexit");
		}

		if ($checinfo["parent_id"] == 0) {
			$sublist = $this->mysql->getarr("select *  from " . Mysite::$app->config["tablepre"] . "module  where parent_id= '" . $id . "'  ");

			foreach ($sublist as $key => $value ) {
				$this->mysql->delete(Mysite::$app->config["tablepre"] . "module", " id='" . $value["id"] . "'  ");
				$this->mysql->delete(Mysite::$app->config["tablepre"] . "menu", " moduleid='" . $value["id"] . "'  ");
				$this->mysql->delete(Mysite::$app->config["tablepre"] . "usrlimit", " moduleid='" . $value["id"] . "'  ");
			}
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "module", " id='" . $id . "'  ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "menu", " moduleid='" . $id . "'  ");
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "usrlimit", " moduleid='" . $id . "'  ");
		$this->success("success");
	}

	public function limitset()
	{
		$id = intval(IReq::get("id"));
		$data["groupid"] = intval(IReq::get("usergrade"));
		$data["groupinfo"] = array();

		if (0 < $data["groupid"]) {
			$data["groupinfo"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "group where id = " . $data["groupid"] . " ");
		}

		$modelist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "module  where install='1' limit 0,30");
		$templist = array();

		foreach ($modelist as $key => $value ) {
			$menufile = hopedir . "/module/" . $value["name"] . "/menudata.php";

			if (file_exists($menufile)) {
				$value["det"] = include $menufile;
				$temp_c = $this->mysql->getarr("select name from " . Mysite::$app->config["tablepre"] . "menu where moduleid='" . $value["id"] . "'  and `group`=" . $data["groupid"] . " ");
				$value["menuarray"] = array();

				if (is_array($temp_c)) {
					foreach ($temp_c as $k => $val ) {
						$value["menuarray"][] = $val["name"];
					}
				}
			}
			else {
				$value["det"] = array();
			}

			$templist[] = $value;
		}

		$data["modelist"] = $templist;
		Mysite::$app->setdata($data);
	}

	public function savelimit()
	{
		$groupid = intval(IReq::get("groupid"));

		if (empty($groupid)) {
			$this->message("member_group_noexit");
		}

		$groupinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "group where id = " . $groupid . " ");

		if (empty($groupinfo)) {
			$this->message("member_group_noexit");
		}

		if ($groupinfo["type"] != "admin") {
			$this->message("不是管理员不需要设置导航条");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "menu", "`group`=" . $groupid . "");
		$modelist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "module  where install='1' limit 0,30");

		foreach ($modelist as $key => $value ) {
			$menufile = hopedir . "/module/" . $value["name"] . "/menudata.php";

			if (file_exists($menufile)) {
				$getinfo = IFilter::act(IReq::get($value["name"]));

				if (is_array($getinfo)) {
					$munulist = include $menufile;

					foreach ($getinfo as $k => $val ) {
						if (isset($munulist[$val])) {
							$xieru["name"] = $val;
							$xieru["cnname"] = $munulist[$val];
							$xieru["moduleid"] = $value["id"];
							$xieru["group"] = $groupid;
							$xieru["id"] = $k;
							$this->mysql->insert(Mysite::$app->config["tablepre"] . "menu", $xieru);
						}
					}
				}
			}
		}

		$this->success("success");
	}
}

$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}

