<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class myapp
{
	public $name = "My Application";
	public $defaultController = "site";
	public $defaultAction = "index";
	private $timezone = "Asia/Shanghai";
	private $controller;
	private $Taction;
	private $renderData = array();

	public function __construct($config)
	{
		if (is_string($config)) {
			$config = require $config;
		}

		if (is_array($config)) {
			$this->config = $config;
		}
		else {
			$this->config = array();
		}

		if (!isset($_SERVER["DOCUMENT_ROOT"])) {
			if (isset($_SERVER["SCRIPT_FILENAME"])) {
				$_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);
			}
			else if (isset($_SERVER["PATH_TRANSLATED"])) {
				$_SERVER["DOCUMENT_ROOT"] = dirname(rtrim($_SERVER["PATH_TRANSLATED"], "/\\"));
			}
		}

		if ($web = true) {
			$script_dir = trim(dirname($_SERVER["SCRIPT_NAME"]), "/\\");

			if ($script_dir != "") {
				$script_dir .= "/";
			}

			$basePath = rtrim($_SERVER["DOCUMENT_ROOT"], "/\\") . "/" . $script_dir;
			$this->config["basePath"] = $basePath;
			$this->setBasePath($basePath);
		}
	}

	public function getkey()
	{
		return "";
	}

	public function run($setindex = "")
	{
		IUrl::beginUrl();
		$controller = IUrl::getInfo("ctrl");
		$action = IUrl::getInfo("action");
		$Taction = (empty($action) ? $this->defaultAction : $action);
		$info = (isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER["HTTP_X_FORWARDED_HOST"] : (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : ""));
		$sitekey = (isset($this->config["sitekey"]) ? $this->config["sitekey"] : "");

		if ($controller === NULL) {
			$controller = $this->defaultController;
		}

		$this->controller = $controller;
		$this->Taction = $Taction;
		if (($controller == "site") && ($Taction == "index")) {
			if (is_mobile_request()) {
				$this->controller = "wxsite";
			}
		}

		spl_autoload_register("Mysite::autoload");
		$filePath = hopedir . "/lib/Smarty/libs/Smarty.class.php";

		if (!class_exists("smarty")) {
			include_once $filePath;
		}

		if ($controller == "adminpage") {
			$smarty = new Smarty();
			$smarty->assign("siteurl", Mysite::$app->config["siteurl"]);
			$smarty->cache_lifetime = 60 * 60 * 24;
			$smarty->caching = false;
			$smarty->template_dir = hopedir . "/templates/";
			$smarty->compile_dir = hopedir . "/templates_c/adminpage";
			$smarty->cache_dir = hopedir . "/smarty_cache";
			$smarty->left_delimiter = "<{";
			$smarty->right_delimiter = "}>";
			$module = IUrl::getInfo("module");
			$module = (empty($module) ? "index" : $module);
			$doaction = (Mysite::$app->getAction() == "index" ? "system" : Mysite::$app->getAction());
			$this->Taction = $doaction;
			$this->siteset();

			if (!file_exists(hopedir . "/module/" . Mysite::$app->getAction() . "/adminmethod.php")) {
			}
			else {
				include hopedir . "/module/" . Mysite::$app->getAction() . "/adminmethod.php";
				$method = new method();
				$method->init();

				if (method_exists($method, $module)) {
					call_user_func(array($method, $module));
				}
			}

			$datas = $this->getdata();

			if (is_array($datas)) {
				foreach ($datas as $key => $value ) {
					$smarty->assign($key, $value);
				}
			}

			$nowID = ICookie::get("myaddress");
			$lng = ICookie::get("lng");
			$lat = ICookie::get("lat");
			$mapname = ICookie::get("mapname");
			$adminshopid = ICookie::get("adminshopid");
			$smarty->assign("myaddress", $nowID);
			$smarty->assign("mapname", $mapname);
			$smarty->assign("adminshopid", $adminshopid);
			$smarty->assign("lng", $lng);
			$smarty->assign("lat", $lat);
			$smarty->assign("controlname", Mysite::$app->getController());
			$smarty->assign("Taction", Mysite::$app->getAction());
			$smarty->assign("urlshort", Mysite::$app->getController() . "/" . Mysite::$app->getAction());
			$templtepach = hopedir . "/templates/adminpage/" . Mysite::$app->getAction() . "/" . $module . ".html";

			if (file_exists($templtepach)) {
			}
			else if (file_exists(hopedir . "/module/" . Mysite::$app->getAction() . "/adminpage/" . $module . ".html")) {
				$smarty->compile_dir = hopedir . "/templates_c/adminpage/" . Mysite::$app->getAction();
				$templtepach = hopedir . "/module/" . Mysite::$app->getAction() . "/adminpage/" . $module . ".html";
			}
			else {
				logwrite("模板不存在 ");
				$smarty->assign("msg", "模板文件不存在");
				$smarty->assign("sitetitle", "错误提示");
				$errorlink = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
				$smarty->assign("errorlink", $errorlink);
				$templtepach = hopedir . "/templates/adminpage/public/error.html";
			}

			$smarty->assign("tmodule", $module);
			$smarty->assign("tempdir", "adminpage");
			$smarty->registerPlugin("function", "ofunc", "FUNC_function");
			$smarty->registerPlugin("block", "oblock", "FUNC_block");
			$smarty->display($templtepach);
			exit();
		}
		else if ($controller == "areaadminpage") {
			$smarty = new Smarty();
			$smarty->assign("siteurl", Mysite::$app->config["siteurl"]);
			$smarty->cache_lifetime = 60 * 60 * 24;
			$smarty->caching = false;
			$smarty->template_dir = hopedir . "/templates/";
			$smarty->compile_dir = hopedir . "/templates_c/areaadminpage";
			$smarty->cache_dir = hopedir . "/smarty_cache";
			$smarty->left_delimiter = "<{";
			$smarty->right_delimiter = "}>";
			$module = IUrl::getInfo("module");
			$module = (empty($module) ? "index" : $module);
			$doaction = (Mysite::$app->getAction() == "index" ? "system" : Mysite::$app->getAction());
			$this->Taction = $doaction;
			$this->siteset();

			if (!file_exists(hopedir . "/module/" . Mysite::$app->getAction() . "/areaadminmethod.php")) {
			}
			else {
				include hopedir . "/module/" . Mysite::$app->getAction() . "/areaadminmethod.php";
				$method = new method();
				$method->init();

				if (method_exists($method, $module)) {
					call_user_func(array($method, $module));
				}
			}

			$datas = $this->getdata();

			if (is_array($datas)) {
				foreach ($datas as $key => $value ) {
					$smarty->assign($key, $value);
				}
			}

			$nowID = ICookie::get("myaddress");
			$lng = ICookie::get("lng");
			$lat = ICookie::get("lat");
			$mapname = ICookie::get("mapname");
			$adminshopid = ICookie::get("adminshopid");
			$smarty->assign("myaddress", $nowID);
			$smarty->assign("mapname", $mapname);
			$smarty->assign("adminshopid", $adminshopid);
			$smarty->assign("lng", $lng);
			$smarty->assign("lat", $lat);
			$smarty->assign("controlname", Mysite::$app->getController());
			$smarty->assign("Taction", Mysite::$app->getAction());
			$smarty->assign("urlshort", Mysite::$app->getController() . "/" . Mysite::$app->getAction());
			$templtepach = hopedir . "/templates/areaadminpage/" . Mysite::$app->getAction() . "/" . $module . ".html";

			if (file_exists($templtepach)) {
			}
			else if (file_exists(hopedir . "/module/" . Mysite::$app->getAction() . "/areaadminpage/" . $module . ".html")) {
				$smarty->compile_dir = hopedir . "/templates_c/areaadminpage/" . Mysite::$app->getAction();
				$templtepach = hopedir . "/module/" . Mysite::$app->getAction() . "/areaadminpage/" . $module . ".html";
			}
			else {
				logwrite("模板不存在 ");
				$smarty->assign("msg", "模板文件不存在");
				$smarty->assign("sitetitle", "错误提示");
				$errorlink = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
				$smarty->assign("errorlink", $errorlink);
				$templtepach = hopedir . "/templates/areaadminpage/public/error.html";
			}

			$smarty->assign("tmodule", $module);
			$smarty->assign("tempdir", "areaadminpage");
			$smarty->registerPlugin("function", "ofunc", "FUNC_function");
			$smarty->registerPlugin("block", "oblock", "FUNC_block");
			$smarty->display($templtepach);
			exit();
		}
		else {
			$smarty = new Smarty();
			$smarty->assign("siteurl", Mysite::$app->config["siteurl"]);
			$smarty->cache_lifetime = 60 * 60 * 24;
			$smarty->caching = false;
			$smarty->template_dir = hopedir . "/templates";
			$smarty->compile_dir = hopedir . "/templates_c/" . Mysite::$app->config["sitetemp"];
			$smarty->cache_dir = hopedir . "/smarty_cache";
			$smarty->left_delimiter = "<{";
			$smarty->right_delimiter = "}>";
			$this->siteset();

			if (!file_exists(hopedir . "module/" . Mysite::$app->getController() . "/method.php")) {
				$this->setController = "site";
				$this->setAction = "error";
			}
			else {
				include hopedir . "module/" . Mysite::$app->getController() . "/method.php";
				$method = new method();
				$method->init();

				if (method_exists($method, $Taction)) {
					call_user_func(array($method, $Taction));
				}
			}

			$datas = $this->getdata();

			if (is_array($datas)) {
				foreach ($datas as $key => $value ) {
					$smarty->assign($key, $value);
				}
			}

			$nowID = ICookie::get("myaddress");
			$lng = ICookie::get("lng");
			$lat = ICookie::get("lat");
			$mapname = ICookie::get("mapname");
			$adminshopid = ICookie::get("adminshopid");
			$smarty->assign("myaddress", $nowID);
			$smarty->assign("mapname", $mapname);
			$smarty->assign("adminshopid", $adminshopid);
			$smarty->assign("lng", $lng);
			$smarty->assign("lat", $lat);
			$smarty->assign("controlname", Mysite::$app->getController());
			$smarty->assign("Taction", Mysite::$app->getAction());
			$smarty->assign("urlshort", Mysite::$app->getController() . "/" . Mysite::$app->getAction());
			$templtepach = hopedir . "/templates/" . Mysite::$app->config["sitetemp"] . "/" . Mysite::$app->getController() . "/" . Mysite::$app->getAction() . ".html";

			if (file_exists($templtepach)) {
			}
			else if (file_exists(hopedir . "/module/" . Mysite::$app->getController() . "/template/" . Mysite::$app->getAction() . ".html")) {
				$smarty->compile_dir = hopedir . "/templates_c/system";
				$templtepach = hopedir . "/module/" . Mysite::$app->getController() . "/template/" . Mysite::$app->getAction() . ".html";
			}
			else {
				logwrite("模板不存在 ");
				$smarty->assign("msg", "模板文件不存在");
				$smarty->assign("sitetitle", "错误提示");
				$errorlink = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
				$smarty->assign("errorlink", $errorlink);
				$templtepach = hopedir . "/templates/" . Mysite::$app->config["sitetemp"] . "/public/error.html";
			}

			$smarty->assign("tempdir", Mysite::$app->config["sitetemp"]);
			$smarty->registerPlugin("function", "ofunc", "FUNC_function");
			$smarty->registerPlugin("block", "oblock", "FUNC_block");
			$smarty->display($templtepach);
		}
	}

	static public function statichtml($htmlcontent, $datas)
	{
		$filePath = hopedir . "/lib/Smarty/libs/Smarty.class.php";

		if (!class_exists("smarty")) {
			include_once $filePath;
		}

		$tpl = new Smarty();
		$tpl->cache_lifetime = 0;
		$tpl->caching = false;
		$tpl->template_dir = hopedir . "/templates";
		$tpl->compile_dir = hopedir . "/templates_c";
		$tpl->cache_dir = hopedir . "/smarty_cache";
		$tpl->left_delimiter = "{";
		$tpl->right_delimiter = "}";

		if (is_array($datas)) {
			foreach ($datas as $key => $value ) {
				$tpl->assign($key, $value);
			}
		}

		$content = $tpl->fetch("string:" . $htmlcontent);
		return $content;
	}

	public function siteset()
	{
		$config = new config("hopeconfig.php", hopedir);
		$this->setdata($config->getInfo());
	}

	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
	}

	public function getBasePath()
	{
		return $this->basePath;
	}

	public function getController()
	{
		return $this->controller;
	}

	public function setController($controller)
	{
		$this->controller = $controller;
	}

	public function setAction($action)
	{
		$this->Taction = $action;
	}

	public function getAction()
	{
		return $this->Taction;
	}

	public function setdata($data)
	{
		$tempdata = $this->getdata();
		$tempdata = array_merge($tempdata, $data);
		$this->renderData = $tempdata;
	}

	public function getdata()
	{
		return $this->renderData;
	}
}

$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}

