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
	public function uploadapp()
	{
		$func = IFilter::act(IReq::get("func"));
		$obj = IReq::get("obj");
		$uploaddir = IFilter::act(IReq::get("dir"));
		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$uploaddir = (empty($uploaddir) ? "goods" : $uploaddir);
			$json = new Services_JSON();
			$uploadpath = "upload/" . $uploaddir . "/";
			$filepath = "/upload/" . $uploaddir . "/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				echo "<script>parent." . $func . "(true,'" . $obj . "','" . json_encode($upload->errmsg()) . "');</script>";
			}
			else {
				echo "<script>parent." . $func . "(false,'" . $obj . "','" . $filepath . $file[0]["saveName"] . "');</script>";
			}

			exit();
		}

		$data["obj"] = $obj;
		$data["uploaddir"] = $uploaddir;
		$data["func"] = $func;
		Mysite::$app->setdata($data);
	}

	public function piliang()
	{
		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$uploaddir = "pliang";
			$json = new Services_JSON();
			$uploadpath = "upload/" . $uploaddir . "/";
			$filepath = "/upload/" . $uploaddir . "/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				$this->message($upload->errmsg());
			}
			else {
				$data["imagename"] = $file[0]["saveName"];
				$data["imageurl"] = $filepath . $file[0]["saveName"];
				$data["addtime"] = time();
				$this->mysql->insert(Mysite::$app->config["tablepre"] . "imglist", $data);
				$this->success($file[0]["saveName"]);
			}
		}
		else {
			$this->message("未定义的上传类型");
		}
	}

	public function glyuploadmoreimg()
	{
		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$uploaddir = "wximages";
			$json = new Services_JSON();
			$uploadpath = "upload/" . $uploaddir . "/";
			$filepath = "/upload/" . $uploaddir . "/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			$uploadimages = array();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				$this->message($upload->errmsg());
			}
			else {
				$data["imagename"] = $file[0]["saveName"];
				$data["imageurl"] = $filepath . $file[0]["saveName"];
				$data["addtime"] = time();
				$uploadimages[] = $file[0]["saveName"];
			}
		}
		else {
			$this->message("未定义的上传类型");
		}
	}

	public function adminupload()
	{
		$func = IFilter::act(IReq::get("func"));
		$obj = IReq::get("obj");
		$uploaddir = IFilter::act(IReq::get("dir"));
		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$uploaddir = (empty($uploaddir) ? "goods" : $uploaddir);
			$json = new Services_JSON();
			$uploadpath = "upload/" . $uploaddir . "/";
			$filepath = "/upload/" . $uploaddir . "/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				echo "<script>parent." . $func . "(true,'" . $obj . "','" . json_encode($upload->errmsg()) . "');</script>";
			}
			else {
				echo "<script>parent." . $func . "(false,'" . $obj . "','" . $filepath . $file[0]["saveName"] . "');</script>";
			}

			exit();
		}

		$data["obj"] = $obj;
		$data["uploaddir"] = $uploaddir;
		$data["func"] = $func;
		Mysite::$app->setdata($data);
	}

	public function adminsayupload()
	{
		$func = IFilter::act(IReq::get("func"));
		$obj = IReq::get("obj");
		$uploaddir = IFilter::act(IReq::get("dir"));
		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$uploaddir = (empty($uploaddir) ? "wximages" : $uploaddir);
			$json = new Services_JSON();
			$uploadpath = "upload/" . $uploaddir . "/";
			$filepath = "/upload/" . $uploaddir . "/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				echo "<script>parent." . $func . "(true,'" . $obj . "','" . json_encode($upload->errmsg()) . "');</script>";
			}
			else {
				echo "<script>parent." . $func . "(false,'" . $obj . "','" . $filepath . $file[0]["saveName"] . "');</script>";
			}

			exit();
		}

		$data["obj"] = $obj;
		$data["uploaddir"] = $uploaddir;
		$data["func"] = $func;
		Mysite::$app->setdata($data);
	}

	public function saveupload()
	{
		$json = new Services_JSON();
		$uploadpath = "upload/goods/";
		$filepath = "/upload/goods/";
		$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "png"));
		$file = $upload->getfile();
		if (($upload->errno != 15) && ($upload->errno != 0)) {
			$msg = $json->encode(array("error" => 1, "message" => $upload->errmsg()));
		}
		else {
			$msg = $json->encode(array("error" => 0, "url" => $filepath . $file[0][saveName], "trueurl" => $upload->returninfo["name"]));
		}

		echo $msg;
		exit();
	}

	public function paylist()
	{
		$logindir = plugdir . "/pay";
		$dirArray[] = NULL;

		if (false != $handle = opendir($logindir)) {
			$i = 0;

			while (false !== $file = readdir($handle)) {
				if (($file != ".") && ($file != "..") && !strpos($file, ".")) {
					if (file_exists($logindir . "/" . $file . "/set.php")) {
						require_once $logindir . "/" . $file . "/set.php";
						$dirArray[$i]["data"] = $setinfo;
						$dirArray[$i]["filename"] = $file;
						$i++;
					}
				}
			}

			closedir($handle);
		}

		$data["apilist"] = $dirArray;
		$exlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "paylist    order by id desc  limit 0, 50 ");
		$mlist = array();

		if (is_array($exlist)) {
			foreach ($exlist as $key => $value ) {
				if (!empty($value["logindesc"])) {
					$mlist[] = $value["logindesc"];
				}
			}
		}

		$data["installlist"] = $mlist;
		Mysite::$app->setdata($data);
	}

	public function installpay()
	{
		$idtype = IReq::get("idtype");
		$logindir = plugdir . "/pay";

		if (!file_exists($logindir . "/" . $idtype . "/set.php")) {
			$data["info"] = "安装文件不存在";
		}
		else {
			include_once $logindir . "/" . $idtype . "/set.php";
			$data["info"] = $plugsdata;
		}

		$getinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "paylist where loginname='" . $idtype . "'  ");
		$data["setinfo"] = json_decode($getinfo["temp"], true);
		$data["idtype"] = $idtype;
		Mysite::$app->setdata($data);
	}

	public function savepay()
	{
		$idtype = IReq::get("idtype");
		$logindir = plugdir . "/pay";

		if (!file_exists($logindir . "/" . $idtype . "/save.php")) {
			$data["info"] = "设置文件不存在";
		}
		else {
			$appid = IReq::get("appid");
			include_once $logindir . "/" . $idtype . "/save.php";
		}

		exit();
	}

	public function delpay()
	{
		$idtype = IReq::get("idtype");

		if (empty($idtype)) {
			$this->message("other_emptyapi");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "paylist", "loginname = '$idtype'");
		$this->success("success");
	}

	public function loginlist()
	{
		$logindir = plugdir . "/login";
		$dirArray[] = NULL;

		if (false != $handle = opendir($logindir)) {
			$i = 0;

			while (false !== $file = readdir($handle)) {
				if (($file != ".") && ($file != "..") && !strpos($file, ".")) {
					if (file_exists($logindir . "/" . $file . "/set.php")) {
						require_once $logindir . "/" . $file . "/set.php";
						$dirArray[$i]["data"] = $setinfo;
						$dirArray[$i]["filename"] = $file;
						$i++;
					}
				}
			}

			closedir($handle);
		}

		$data["apilist"] = $dirArray;
		$exlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "otherlogin    order by id desc  limit 0, 50 ");
		$mlist = array();

		if (is_array($exlist)) {
			foreach ($exlist as $key => $value ) {
				if (!empty($value["logindesc"])) {
					$mlist[] = $value["logindesc"];
				}
			}
		}

		$data["installlist"] = $mlist;
		Mysite::$app->setdata($data);
	}

	public function installlogin()
	{
		$idtype = IReq::get("idtype");
		$logindir = plugdir . "/login";

		if (!file_exists($logindir . "/" . $idtype . "/set.php")) {
			$data["info"] = "安装文件不存在";
		}
		else {
			include_once $logindir . "/" . $idtype . "/set.php";
			$data["info"] = $mkdata;
		}

		$getinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "otherlogin where loginname='" . $idtype . "'  ");
		$data["setinfo"] = json_decode($getinfo["temp"], true);
		$data["idtype"] = $idtype;
		Mysite::$app->setdata($data);
	}

	public function dellogin()
	{
		$idtype = IReq::get("idtype");

		if (empty($idtype)) {
			$this->message("other_emptyapi");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "otherlogin", "loginname = '$idtype'");
		$this->success("success");
	}

	public function savelogin()
	{
		$idtype = IReq::get("idtype");
		$logindir = plugdir . "/login";

		if (!file_exists($logindir . "/" . $idtype . "/save.php")) {
			$data["info"] = "设置文件不存在";
		}
		else {
			$appid = IReq::get("appid");
			include_once $logindir . "/" . $idtype . "/save.php";
		}

		exit();
	}

	public function othertpl()
	{
		$default_tpl = new config("tplset.php", hopedir);
		$data["info"] = $default_tpl->getInfo();
		$data["allowedsendshop"] = Mysite::$app->config["allowedsendshop"];
		$data["allowedsendbuyer"] = Mysite::$app->config["allowedsendbuyer"];
		$data["ordercheckphone"] = Mysite::$app->config["ordercheckphone"];
		$list = array("emailfindtpl" => "找回密码邮箱模板", "usercodetpl" => "用户验证码模版", "phoneunorder" => "后台关闭订单短信通知模板", "noticeunback" => "退款失败通知", "noticeback" => "退款成功通知", "noticemake" => "商家制作订单通知", "noticeunmake" => "商家不制作订单通知", "noticesend" => "发货通知", "shopphonetpl" => "下单通知商家模版", "userbuytpl" => "下单通知用户模版");

		if ($data["allowedsendshop"] != 1) {
			unset($list["shopphonetpl"]);
		}

		if ($data["allowedsendbuyer"] != 1) {
			unset($list["userbuytpl"]);
		}

		if ($data["ordercheckphone"] != 1) {
			unset($list["usercodetpl"]);
		}

		$data["list"] = $list;
		Mysite::$app->setdata($data);
	}

	public function edittpl()
	{
		$tplname = IReq::get("tplname");
		$default_tpl = new config("tplset.php", hopedir);
		$tpllist = $default_tpl->getInfo();
		$list = array("emailfindtpl" => "找回密码邮箱模板", "shopphonetpl" => "下单通知商家模版", "userbuytpl" => "下单通知用户模版", "usercodetpl" => "用户验证码模版", "emailorder" => "商家邮件模版", "phoneunorder" => "后台关闭订单短信通知模板", "usercodetpl" => "用户验证码模版", "noticeunback" => "退款失败通知", "noticeback" => "退款成功通知", "noticemake" => "商家制作订单通知", "noticeunmake" => "商家不制作订单通知", "noticesend" => "发货通知");
		$info = array_keys($list);

		if (!in_array($tplname, $info)) {
			header("Content-Type:text/html;charset=utf-8");
			echo "编辑模板错误";
			exit();
		}

		$data["tplname"] = $tplname;

		if (isset($tpllist[$tplname])) {
			$data["tplcontent"] = htmlspecialchars_decode($tpllist[$tplname]);
		}

		Mysite::$app->setdata($data);
	}

	public function savetpl()
	{
		$tplname = trim(IReq::get("tplname"));
		$tplcontent = trim(IReq::get("tplcontent"));
		$list = array("emailfindtpl" => "找回密码邮箱模板", "shopphonetpl" => "下单通知商家模版", "userbuytpl" => "下单通知用户模版", "usercodetpl" => "用户验证码模版", "emailorder" => "商家邮件模版", "phoneunorder" => "后台关闭订单短信通知模板", "usercodetpl" => "用户验证码模版", "noticeunback" => "退款失败通知", "noticeback" => "退款成功通知", "noticemake" => "商家制作订单通知", "noticeunmake" => "商家不制作订单通知", "noticesend" => "发货通知");
		$info = array_keys($list);

		if (!in_array($tplname, $info)) {
			echo "不在操作模板内";
			exit();
		}

		$siteinfo[$tplname] = stripslashes($tplcontent);
		$default_tpl = new config("tplset.php", hopedir);
		$default_tpl->write($siteinfo);
		echo "<meta charset='utf-8' />";
		echo "<script>parent.uploadsucess('设置成功');</script>";
		exit();
	}

	public function cleartpl()
	{
		IFile::clearDir("templates_c");
		$link = IUrl::creatUrl("/adminpage/system/module/index");
		$this->refunction("清除缓存文件成功", $link);
	}

	public function emailsetsave()
	{
		$start_smtp = IReq::get("start_smtp");

		if ($start_smtp == 1) {
			$siteinfo["smpt"] = IReq::get("smpt");
			$siteinfo["emailname"] = IReq::get("emailname");
			$siteinfo["emailpwd"] = IReq::get("emailpwd");
		}
		else {
			$siteinfo["smpt"] = "";
			$siteinfo["emailname"] = "";
			$siteinfo["emailpwd"] = "";
		}

		$config = new config("hopeconfig.php", hopedir);
		$config->write($siteinfo);
		$this->success("success");
	}

	public function smssetsave()
	{
		$config = new config("hopeconfig.php", hopedir);
		$siteinfo["smstype"] = IReq::get("smstype");
		$siteinfo["apiuid"] = intval(IReq::get("sms86id"));
		$siteinfo["sms86ac"] = IReq::get("sms86ac");
		$siteinfo["sms86pd"] = IReq::get("sms86pd");
		$siteinfo["smstype"] = IReq::get("smstype");

		if (empty($siteinfo["sms86ac"])) {
			$this->message("acout_empty");
		}

		if (empty($siteinfo["sms86pd"])) {
			$this->message("emptykey");
		}

		$config->write($siteinfo);
		$this->success("success");
	}

	public function smgetbalance()
	{
		$sendmobile = new mobile();
		$info = $sendmobile->getBalance();
		echo $info;
		exit();
	}

	public function smtopay()
	{
		$smcard = trim(IReq::get("smcard"));
		$smpwd = trim(IReq::get("smpwd"));

		if (empty($smcard)) {
			$this->message("acout_empty");
		}

		if (empty($smpwd)) {
			$this->message("emptykey");
		}

		$sendmobile = new mobile();
		$checkinfo = $sendmobile->chargeUp($smcard, $smpwd);

		if ($checkinfo != "ok") {
			$this->message($checkinfo);
		}

		$this->success("success");
	}

	public function basedata()
	{
		$data["dirname"] = time();
		$data["list"] = $this->mysql->gettales();
		Mysite::$app->setdata($data);
	}

	public function savesqldata()
	{
		$tabelname = IReq::get("tabelname");
		$dirname = IReq::get("dirname");
		IFile::mkdir(hopedir . "/dbbak/" . $dirname);
		$info = $this->mysql->getarr("show create table `$tabelname`");
		$sqldata[] = $info[0]["Create Table"];
		$list = $this->mysql->getarr("select * from " . $tabelname . "      limit 0, 20000 ");

		if (is_array($list)) {
			foreach ($list as $key => $value ) {
				$keys = array_keys($value);
				$key = array_map("addslashes", $keys);
				$key = join("`,`", $key);
				$keys = "`" . $key . "`";
				$vals = array_values($value);
				$vals = array_map("addslashes", $vals);
				$vals = join("','", $vals);
				$vals = "'" . $vals . "'";
				$sqldata[] = "INSERT INTO `$tabelname`($keys) VALUES($vals)";
			}
		}

		$configData = var_export($sqldata, true);
		$configStr = "<?php return $configData?>";
		$fp = fopen(hopedir . "/dbbak/" . $dirname . "/" . $tabelname . ".php", "w");
		fputs($fp, $configStr);
		fclose($fp);
		$this->success("success");
	}

	public function rebkdata()
	{
		$list = array();
		$handler = opendir(hopedir . "/dbbak");

		while (($filename = readdir($handler)) !== false) {
			if (($filename != ".") && ($filename != "..")) {
				$list[] = $filename;
			}
		}

		closedir($handler);
		$data["list"] = $list;
		$data["tablist"] = $this->mysql->gettales();
		$detfault = array_values($data["tablist"]);
		$data["deftb"] = $detfault[0];
		Mysite::$app->setdata($data);
	}

	public function savebkdata()
	{
		$tabelname = IReq::get("tabelname");

		if (empty($tabelname)) {
			$this->message("other_emptytablepass");
		}

		$dirname = IReq::get("dirname");

		if (empty($dirname)) {
			$this->message("other_emptyfilenamepass");
		}

		if (!file_exists(hopedir . "/dbbak/" . $dirname)) {
			$this->message("fileexit");
		}

		if (!file_exists(hopedir . "/dbbak/" . $dirname . "/" . $tabelname . ".php")) {
			$this->message("other_emptyfilenamepass");
		}

		$this->mysql->query("DROP TABLE  `" . $tabelname . "`");
		$infos = include hopedir . "/dbbak/" . $dirname . "/" . $tabelname . ".php";

		if (is_array($infos)) {
			foreach ($infos as $key => $value ) {
				$this->mysql->query($value);
			}
		}

		$this->success("success");
	}

	public function debkfile()
	{
		$dirname = IReq::get("dirname");

		if (empty($dirname)) {
			$this->message("empty_filename");
		}

		IFile::clearDir(hopedir . "/dbbak/" . $dirname);
		IFile::rmdir(hopedir . "/dbbak/" . $dirname);
		$this->success("success");
	}

	public function errlog()
	{
		$list = array();
		$handler = opendir(hopedir . "/log");

		while (($filename = readdir($handler)) !== false) {
			if (($filename != ".") && ($filename != "..")) {
				$list[] = $filename;
			}
		}

		closedir($handler);
		$data["list"] = $list;
		Mysite::$app->setdata($data);
	}

	public function delerrlog()
	{
		$dirname = IReq::get("dirname");

		if (empty($dirname)) {
			$this->message("empty_filename");
		}

		IFile::unlink(hopedir . "/log/" . $dirname);
		$this->success("success");
	}

	public function download()
	{
		$dirname = IReq::get("dirname");

		if (empty($dirname)) {
			echo "文件不存在";
			exit();
		}

		if (!file_exists(hopedir . "log/" . $dirname)) {
			echo "文件不存在";
			exit();
		}

		$file = fopen(hopedir . "/log/" . $dirname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize(hopedir . "/log/" . $dirname));
		Header("Content-Disposition: attachment; filename=" . $dirname);
		echo fread($file, filesize(hopedir . "/log/" . $dirname));
		fclose($file);
		exit();
	}

	public function addspecialpage()
	{
		$id = intval(IReq::get("id"));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "specialpage where id=" . $id . "  ");
		Mysite::$app->setdata($data);
	}

	public function savespecialpage()
	{
		$id = IReq::get("ztyid");
		$data["name"] = IReq::get("name");
		$data["indeximg"] = IReq::get("indeximg");
		$data["imgwidth"] = IReq::get("imgwidth");
		$data["imgheight"] = IReq::get("imgheight");
		$data["specialimg"] = IReq::get("specialimg");
		$data["color"] = IReq::get("color");
		$data["is_custom"] = intval(IReq::get("is_custom"));
		$data["showtype"] = intval(IReq::get("showtype"));
		$shopcx_type = intval(IReq::get("shopcx_type"));
		$goodscx_type = intval(IReq::get("goodscx_type"));
		$data["addtime"] = time();

		if ($data["showtype"] == 0) {
			$data["cx_type"] = $shopcx_type;
		}

		if ($data["showtype"] == 1) {
			$data["cx_type"] = $goodscx_type;
		}

		$data["is_show"] = intval(IReq::get("is_show"));
		$data["orderid"] = intval(IReq::get("orderid"));
		$data["ruleintro"] = IReq::get("ruleintro");

		if (empty($data["name"])) {
			$this->message("名称不能为空");
		}

		if (empty($data["indeximg"])) {
			$this->message("首页显示图片不能为空");
		}

		if (empty($data["color"])) {
			$this->message("专题页背景色调不能为空");
		}

		if (($data["is_custom"] != 0) && ($data["is_custom"] != 1)) {
			$this->message("请选择活动格式");
		}

		if (($data["showtype"] != 0) && ($data["showtype"] != 1)) {
			$this->message("请选择活动针对对象");
		}

		if ($data["is_custom"] == 1) {
			if (($data["cx_type"] <= 0) && (1 < $data["cx_type"])) {
				$this->message("请选择对象对应活动类型");
			}
		}

		if ($data["is_custom"] == 0) {
			$data["cx_type"] = 0;
		}

		if (empty($id)) {
			$link = IUrl::creatUrl("adminpage/other/module/specialpage");
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "specialpage", $data);
			$this->success("success", $link);
		}
		else {
			$link = IUrl::creatUrl("adminpage/other/module/specialpage/id/" . $id);
			$this->mysql->update(Mysite::$app->config["tablepre"] . "specialpage", $data, "id='" . $id . "'");
			$this->success("success", $link);
		}

		$link = IUrl::creatUrl("adminpage/other/module/specialpage");
	}

	public function delspecialpage()
	{
		$this->message("您暂无权限删除，请联系管理员");
		$id = IReq::get("id");

		if (empty($id)) {
			$this->message("未选中");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);
		$this->mysql->delete(Mysite::$app->config["tablepre"] . "specialpage", " id in($ids) ");
		$this->success("success");
	}

	public function setstatus()
	{
		$data["shoptype"] = array("购卡", "养车");
		Mysite::$app->setdata($data);
	}

	public function selectztyobj()
	{
		$this->setstatus();
		$where = "";
		$goodswhere = "";
		$data["shopname"] = trim(IReq::get("shopname"));
		$data["name"] = trim(IReq::get("name"));
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

		if (!empty($data["shopname"])) {
			$goodswhere .= " and shopname like '%" . $data["shopname"] . "%'";
		}

		if (!empty($data["name"])) {
			$goodswhere .= " and name like '%" . $data["name"] . "%'";
		}

		$id = IReq::get("id");
		$data["id"] = $id;
		$ztyinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "specialpage where id=" . $id . "  ");
		$data["ztyinfo"] = $ztyinfo;
		$this->pageCls->setpage(intval(IReq::get("page")), 60);

		if ($ztyinfo["showtype"] == 0) {
			$selectlist = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "shop  where is_pass = 1 " . $where . "  order by sort asc  limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . "  ");
			$shuliang = $this->mysql->counts("select * from " . Mysite::$app->config["tablepre"] . "shop  where is_pass = 1 " . $where . " ");
		}

		if ($ztyinfo["showtype"] == 1) {
			$selectlist = $this->mysql->getarr("select a.*,b.shopname from " . Mysite::$app->config["tablepre"] . "goods as a left join  " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.id > 0 " . $goodswhere . "  order by good_order asc  limit " . $this->pageCls->startnum() . ", " . $this->pageCls->getsize() . " ");
			$shuliang = $this->mysql->counts("select a.*,b.shopname from " . Mysite::$app->config["tablepre"] . "goods as a left join  " . Mysite::$app->config["tablepre"] . "shop as b  on a.shopid = b.id where a.id > 0 " . $goodswhere . "  ");
		}

		$this->pageCls->setnum($shuliang);
		$data["pagecontent"] = $this->pageCls->getpagebar();
		$data["selectlist"] = $selectlist;
		Mysite::$app->setdata($data);
	}

	public function saveselectztyobj()
	{
		$id = IReq::get("id");
		$zjtype = IReq::get("zjtype");
		$selectobjids = IReq::get("selectobjids");
		$temparray = explode(",", $selectobjids);
		$checkinfo = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "specialpage where id=" . $id . "  ");

		if (!empty($checkinfo)) {
			$yuanlaiids = explode(",", $checkinfo["listids"]);
			$tempids = array_diff($yuanlaiids, $temparray);

			if ($zjtype == 1) {
				foreach ($temparray as $key => $value ) {
					$tempids[] = $value;
				}
			}

			$templistids = array();

			foreach ($tempids as $key => $value ) {
				if (!empty($value)) {
					$templistids[] = $value;
				}
			}

			$data["listids"] = (count(0 < $templistids) ? join(",", $templistids) : "");
			$this->mysql->update(Mysite::$app->config["tablepre"] . "specialpage", $data, "id='" . $id . "'");
			$this->success("success");
		}
	}
}


