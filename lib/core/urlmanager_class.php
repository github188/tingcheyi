<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class IUrl
{
	const UrlNative = 1;
	const UrlPathinfo = 2;
	const UrlDiy = 3;
	const UrlCtrlName = "ctrl";
	const UrlActionName = "action";
	const UrlModuleName = "module";
	const Anchor = "/#&";
	const QuestionMarkKey = "?";

	static 	private $urlRoute = array();

	static public function getInfo($key)
	{
		$arr = array("ctrl" => self::UrlCtrlName, "action" => self::UrlActionName, "module" => self::UrlModuleName);

		if (isset($arr[$key])) {
			return IReq::get($arr[$key]);
		}

		return NULL;
	}

	static public function convertUrl($url, $from, $to)
	{
		if ($from == $to) {
			return $url;
		}

		$urlArray = "";
		$fun_re = false;


		switch($from)
		{
			case self::UrlNative :
				$urlTmp = parse_url($url);
				$urlArray = self::queryStringToArray($urlTmp);
				break;
			case self::UrlPathinfo :
				$urlArray = self::pathinfoToArray($url);
				break;
			case self::UrlDiy :
				$urlArray = self::diyToArray($url);
				break;
			default:
				return $fun_re;
				break;
		}

		switch($to)
		{
			case self::UrlNative :
				$fun_re = self::urlArrayToNative($urlArray);
				break;
			case self::UrlPathinfo :
				$fun_re = self::urlArrayToPathinfo($urlArray);
				break;
			case self::UrlDiy:
				$fun_re = self::urlArrayToDiy($urlArray);
				break;
		}


		return $fun_re;
	}

	static public function queryStringToArray($url)
	{
		if (!is_array($url)) {
			$url = parse_url($url);
		}

		$query = (isset($url["query"]) ? explode("&", $url["query"]) : array());
		$re = array();

		foreach ($query as $value ) {
			$tmp = explode("=", $value);

			if (count($tmp) == 2) {
				$re[$tmp[0]] = $tmp[1];
			}
		}

		$re = self::sortUrlArray($re);
		isset($url["fragment"]) && ($re[self::Anchor] = $url["fragment"]);
		return $re;
	}

	static public function pathinfoToArray($url)
	{
		$data = array();
		preg_match("!^(.*?)?(\?[^#]*?)?(#.*)?$!", $url, $data);
		$re = array();
		if (isset($data[1]) && trim($data[1], "/ ")) {
			$string = explode("/", trim($data[1], "/ "));
			$key = NULL;
			$i = 1;

			foreach ($string as $value ) {
				if ($i <= 2) {
					$tmpKey = ($i == 1 ? self::UrlCtrlName : self::UrlActionName);
					$re[$tmpKey] = $value;
					$i++;
					continue;
				}

				if ($key === NULL) {
					$key = $value;
					$re[$key] = "";
				}
				else {
					$re[$key] = $value;
					$key = NULL;
				}
			}
		}

		if (isset($data[2]) || isset($data[3])) {
			$re[self::QuestionMarkKey] = ltrim($data[2], "?");
		}

		if (isset($data[3])) {
			$re[self::Anchor] = ltrim($data[3], "#");
		}

		$re = self::sortUrlArray($re);
		return $re;
	}

	static public function diyToArray($url)
	{
		return self::decodeRouteUrl($url);
	}

	static private function sortUrlArray($re)
	{
		$fun_re = array();
		isset($re[self::UrlCtrlName]) && ($fun_re[self::UrlCtrlName] = $re[self::UrlCtrlName]);
		isset($re[self::UrlActionName]) && ($fun_re[self::UrlActionName] = $re[self::UrlActionName]);
		unset($re[self::UrlCtrlName]);
		unset($re[self::UrlActionName]);
		ksort($re);
		$fun_re = array_merge($fun_re, $re);
		return $fun_re;
	}

	static private function urlArrayToPathinfo($arr)
	{
		$re = "";
		$ctrl = (isset($arr[self::UrlCtrlName]) ? $arr[self::UrlCtrlName] : "");
		$action = (isset($arr[self::UrlActionName]) ? $arr[self::UrlActionName] : "");
		($ctrl != "") && ($re .= "/$ctrl");
		($action != "") && ($re .= "/$action");
		$fragment = (isset($arr[self::Anchor]) ? $arr[self::Anchor] : "");
		$questionMark = (isset($arr[self::QuestionMarkKey]) ? $arr[self::QuestionMarkKey] : "");
		unset($arr[self::UrlCtrlName]);
		unset($arr[self::UrlActionName]);
		unset($arr[self::Anchor]);

		foreach ($arr as $key => $value ) {
			$re .= "/$key/$value";
		}

		if ($questionMark != "") {
			$re .= "?" . $questionMark;
		}

		($fragment != "") && ($re .= "#$fragment");
		return $re;
	}

	static private function urlArrayToNative($arr)
	{
		$re = "/";
		$re .= self::getIndexFile();
		$fragment = (isset($arr[self::Anchor]) ? $arr[self::Anchor] : "");
		$questionMark = (isset($arr[self::QuestionMarkKey]) ? $arr[self::QuestionMarkKey] : "");
		unset($arr[self::Anchor]);
		unset($arr[self::QuestionMarkKey]);

		if (count($arr)) {
			$tmp = array();

			foreach ($arr as $key => $value ) {
				$tmp[] = "$key=$value";
			}

			$tmp = implode("&", $tmp);
			$re .= "?$tmp";
		}

		if (count($arr) && ($questionMark != "")) {
			$re .= "&" . $questionMark;
		}
		else if ($questionMark != "") {
			$re .= "?" . $questionMark;
		}

		if ($fragment != "") {
			$re .= "#$fragment";
		}

		return $re;
	}

	static private function getRouteCache()
	{
		if (self::$urlRoute === false) {
			return NULL;
		}

		if (self::$urlRoute) {
			return self::$urlRoute;
		}

		$routeList = (isset(Mysite::$app->config["urlRoute"]) ? Mysite::$app->config["urlRoute"] : array());

		if (empty($routeList)) {
			self::$urlRoute = false;
			return NULL;
		}

		$cacheRoute = array();

		foreach ($routeList as $key => $val ) {
			if (is_array($val)) {
				continue;
			}

			$tempArray = explode("/", trim($val, "/"), 3);

			if ($tempArray < 2) {
				continue;
			}

			$level = 3;
			if (($tempArray[0] != "<" . self::UrlCtrlName . ">") && ($tempArray[1] != "<" . self::UrlActionName . ">")) {
				$level = 0;
			}
			else {
				if (($tempArray[0] == "<" . self::UrlCtrlName . ">") && ($tempArray[1] != "<" . self::UrlActionName . ">")) {
					$level = 1;
				}
				else {
					if (($tempArray[0] != "<" . self::UrlCtrlName . ">") && ($tempArray[1] == "<" . self::UrlActionName . ">")) {
						$level = 2;
					}
				}
			}

			$cacheRoute[$level][$key] = $val;
		}

		if (empty($cacheRoute)) {
			self::$urlRoute = false;
			return NULL;
		}

		ksort($cacheRoute);
		self::$urlRoute = $cacheRoute;
		return self::$urlRoute;
	}

	static private function urlArrayToDiy($arr)
	{
		if (!isset($arr[self::UrlCtrlName]) || !isset($arr[self::UrlActionName]) || !$routeList = self::getRouteCache()) {
			return false;
		}

		foreach ($routeList as $level => $regArray ) {
			foreach ($regArray as $regPattern => $value ) {
				$urlArray = explode("/", trim($value, "/"), 3);
				if (($level == 0) && (($arr[self::UrlCtrlName] . "/" . $arr[self::UrlActionName]) != $urlArray[0] . "/" . $urlArray[1])) {
					continue;
				}
				else {
					if (($level == 1) && ($arr[self::UrlActionName] != $urlArray[1])) {
						continue;
					}
					else {
						if (($level == 2) && ($arr[self::UrlCtrlName] != $urlArray[0])) {
							continue;
						}
					}
				}

				$url = self::parseRegPattern($arr, array($regPattern => $value));

				if ($url) {
					return $url;
				}
			}
		}

		return false;
	}

	static private function parseRegPattern($urlArray, $regArray)
	{
		$regPattern = key($regArray);
		$value = current($regArray);

		if (preg_match_all("%<\w+?:.*?>%", $regPattern, $customRegMatch)) {
			$regInfo = array();

			foreach ($customRegMatch[0] as $val ) {
				$val = trim($val, "<>");
				$regTemp = explode(":", $val, 2);
				$regInfo[$regTemp[0]] = $regTemp[1];
			}

			$replaceArray = array();

			foreach ($regInfo as $key => $val ) {
				if (strpos($val, "%") !== false) {
					$val = str_replace("%", "\%", $val);
				}

				if (isset($urlArray[$key]) && preg_match("%$val%", $urlArray[$key])) {
					$replaceArray[] = $urlArray[$key];
					unset($urlArray[$key]);
				}
				else {
					return false;
				}
			}

			$url = str_replace($customRegMatch[0], $replaceArray, $regPattern);
		}
		else {
			$url = $regPattern;
		}

		$paramArray = self::pathinfoToArray($value);
		$questionMarkKey = (isset($urlArray[self::QuestionMarkKey]) ? $urlArray[self::QuestionMarkKey] : "");
		$anchor = (isset($urlArray[self::Anchor]) ? $urlArray[self::Anchor] : "");
		unset($urlArray[self::UrlCtrlName]);
		unset($urlArray[self::UrlActionName]);
		unset($urlArray[self::Anchor]);
		unset($urlArray[self::QuestionMarkKey]);

		foreach ($urlArray as $key => $rs ) {
			if (!isset($paramArray[$key])) {
				$questionMarkKey .= "&" . $key . "=" . $rs;
			}
		}

		$url .= ($questionMarkKey ? "?" . trim($questionMarkKey, "&") : "");
		$url .= ($anchor ? "#" . $anchor : "");
		return $url;
	}

	static private function decodeRouteUrl($url)
	{
		$url = trim($url, "/");
		$urlArray = array();
		$routeList = self::getRouteCache();

		if (!$routeList) {
			return $urlArray;
		}

		foreach ($routeList as $level => $regArray ) {
			foreach ($regArray as $regPattern => $value ) {
				$exeUrlArray = explode("/", $value);
				$regPatternReplace = preg_replace("%<\w+?:(.*?)>%", "(\$1)", $regPattern);

				if (strpos($regPatternReplace, "%") !== false) {
					$regPatternReplace = str_replace("%", "\%", $regPatternReplace);
				}

				if (preg_match("%$regPatternReplace%", $url, $matchValue)) {
					$matchAll = array_shift($matchValue);

					if ($matchAll != $url) {
						continue;
					}

					if ($matchValue) {
						preg_match_all("%<\w+?:.*?>%", $regPattern, $matchReg);

						foreach ($matchReg[0] as $key => $val ) {
							$val = trim($val, "<>");
							$tempArray = explode(":", $val, 2);
							$urlArray[$tempArray[0]] = (isset($matchValue[$key]) ? $matchValue[$key] : "");
						}

						if ((isset($urlArray[self::UrlCtrlName]) && !preg_match("%^\w+$%", $urlArray[self::UrlCtrlName])) || (isset($urlArray[self::UrlActionName]) && !preg_match("%^\w+$%", $urlArray[self::UrlActionName]))) {
							$urlArray = array();
							continue;
						}

						foreach ($exeUrlArray as $key => $val ) {
							$paramName = trim($val, "<>");
							if (($val != $paramName) && isset($urlArray[$paramName])) {
								$exeUrlArray[$key] = $urlArray[$paramName];
							}
						}
					}

					$paramArray = self::pathinfoToArray(join("/", $exeUrlArray));
					$urlArray = array_merge($urlArray, $paramArray);

					return $urlArray;
				}
			}
		}

		return $urlArray;
	}

	static public function tidy($url)
	{
		return preg_replace("![/\\\\]{2,}!", "/", $url);
	}

	static public function creatUrl($url = "")
	{
		if (preg_match("!^[a-z]+://!i", $url)) {
			return $url;
		}

		if (empty($url)) {
			$returndata = Mysite::$app->config["siteurl"];
		}
		else if (Mysite::$app->config["is_base"] == "on") {
			if (Mysite::$app->config["is_static"] == 1) {
				$dolink = explode("/", $url);
				$newlink = "";

				foreach ($dolink as $key => $value ) {
					if ($key < 1) {
						continue;
					}
					else if ($key == 1) {
						$newlink = (empty($value) ? $newlink : $newlink . "/" . $value . "/action");
					}
					else {
						$newlink = (empty($value) ? $newlink : $newlink . "/" . $value);
					}
				}

				$returndata = Mysite::$app->config["siteurl"] . $newlink;
			}
			else if (Mysite::$app->config["is_static"] == 2) {
				$dolink = explode("/", $url);
				$newlink = "";

				foreach ($dolink as $key => $value ) {
					if ($key < 1) {
						continue;
					}
					else if ($key == 1) {
						$newlink = (empty($value) ? $newlink : $newlink . "/" . $value . "/action");
					}
					else {
						$newlink = (empty($value) ? $newlink : $newlink . "/" . $value);
					}
				}

				$returndata = Mysite::$app->config["siteurl"] . "/index.php" . $newlink;
			}
			else {
				$dolink = explode("/", $url);
				$findkey = 0;

				foreach ($dolink as $key => $value ) {
					if (!empty($value)) {
						if ($findkey == 0) {
						}
						else if ($findkey == 1) {
							$returndata = Mysite::$app->config["siteurl"] . "/index.php?ctrl=" . $value;
						}
						else {
							$returndata .= (($findkey % 2) == 0 ? "&" . $value : "=" . $value);
						}

						$findkey++;
					}
				}
			}
		}
		else if (Mysite::$app->config["is_static"] == 1) {
			$returndata = Mysite::$app->config["siteurl"] . "/" . $url;
		}
		else if (Mysite::$app->config["is_static"] == 2) {
			$returndata = Mysite::$app->config["siteurl"] . "/index.php/" . $url;
		}
		else {
			$dolink = explode("/", $url);
			$findkey = 0;

			foreach ($dolink as $key => $value ) {
				if (!empty($value)) {
					if ($findkey == 0) {
						$returndata = Mysite::$app->config["siteurl"] . "/index.php?ctrl=" . $value;
					}
					else if ($findkey == 1) {
						$returndata .= "&action=" . $value;
					}
					else {
						$returndata .= (($findkey % 2) == 0 ? "&" . $value : "=" . $value);
					}

					$findkey++;
				}
			}
		}

		return $returndata;
	}

	static public function getHost($protocol = "http")
	{
		$host = (isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER["HTTP_X_FORWARDED_HOST"] : (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : ""));
		$baseUrl = $protocol . "://" . $host;
		return $baseUrl;
	}

	static public function getPhpSelf()
	{
		$re = explode("/", $_SERVER["SCRIPT_NAME"]);
		return end($re);
	}

	static public function getEntryUrl()
	{
		return self::getHost() . $_SERVER["SCRIPT_NAME"];
	}

	static public function getIndexFile()
	{
		if (!isset($_SERVER["SCRIPT_NAME"])) {
			return "index.php";
		}
		else {
			return basename($_SERVER["SCRIPT_NAME"]);
		}
	}

	static public function getRefRoute()
	{
		if (isset($_SERVER["HTTP_REFERER"]) && ((self::getEntryUrl() & $_SERVER["HTTP_REFERER"]) == self::getEntryUrl())) {
			return substr($_SERVER["HTTP_REFERER"], strlen(self::getEntryUrl()));
		}
		else {
			return "";
		}
	}

	static public function getScriptDir()
	{
		$re = trim(dirname($_SERVER["SCRIPT_NAME"]), "\\");

		if ($re != "/") {
			$re = $re . "/";
		}

		return $re;
	}

	static public function getUrl()
	{
		if (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
			$requestUri = $_SERVER["HTTP_X_REWRITE_URL"];
		}
		else {
			if (isset($_SERVER["IIS_WasUrlRewritten"]) && ($_SERVER["IIS_WasUrlRewritten"] == "1") && isset($_SERVER["UNENCODED_URL"]) && ($_SERVER["UNENCODED_URL"] != "")) {
				$requestUri = $_SERVER["UNENCODED_URL"];
			}
			else {
				if (isset($_SERVER["SERVER_SOFTWARE"]) && (strpos($_SERVER["SERVER_SOFTWARE"], "Apache") !== false)) {
					$requestUri = $_SERVER["PHP_SELF"];
				}
				else if (isset($_SERVER["REQUEST_URI"])) {
					$requestUri = $_SERVER["REQUEST_URI"];
				}
				else if (isset($_SERVER["ORIG_PATH_INFO"])) {
					$requestUri = $_SERVER["ORIG_PATH_INFO"];

					if (!empty($_SERVER["QUERY_STRING"])) {
						$requestUri .= "?" . $_SERVER["QUERY_STRING"];
					}
				}
				else {
					exit("getUrl is error");
				}
			}
		}

		return self::getHost() . $requestUri;
	}

	static public function getUri()
	{
		if (!isset($_SERVER["REQUEST_URI"]) || ($_SERVER["REQUEST_URI"] == "")) {
			if (isset($_SERVER["HTTP_X_ORIGINAL_URL"])) {
				$_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_ORIGINAL_URL"];
			}
			else if (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
				$_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_REWRITE_URL"];
			}
			else {
				if (!isset($_SERVER["PATH_INFO"]) && isset($_SERVER["ORIG_PATH_INFO"])) {
					$_SERVER["PATH_INFO"] = $_SERVER["ORIG_PATH_INFO"];
				}

				if (isset($_SERVER["PATH_INFO"])) {
					if ($_SERVER["PATH_INFO"] == $_SERVER["SCRIPT_NAME"]) {
						$_SERVER["REQUEST_URI"] = $_SERVER["PATH_INFO"];
					}
					else {
						$_SERVER["REQUEST_URI"] = $_SERVER["SCRIPT_NAME"] . $_SERVER["PATH_INFO"];
					}
				}

				if (isset($_SERVER["QUERY_STRING"]) && !empty($_SERVER["QUERY_STRING"])) {
					$_SERVER["REQUEST_URI"] .= "?" . $_SERVER["QUERY_STRING"];
				}
			}
		}

		return $_SERVER["REQUEST_URI"];
	}

	static public function beginUrl($url = "")
	{
		$obj = IServerVars::factory($_SERVER["SERVER_SOFTWARE"]);
		$url = (!empty($url) ? $url : $obj->realUri());
		preg_match("/\.php(.*)/", $url, $phpurl);
		if (!isset($phpurl[1]) || !$phpurl[1]) {
			if ($url != "") {
				$phpurl = array(1 => "?");
			}
			else {
				return NULL;
			}
		}

		$url = $phpurl[1];
		$urlArray = array();
		$rewriteRule = (isset(Mysite::$app->config["rewriteRule"]) ? Mysite::$app->config["rewriteRule"] : "native");

		if ($rewriteRule != "native") {
			$urlArray = self::decodeRouteUrl($url);
		}

		if ($urlArray == array()) {
			if ($url[0] == "?") {
				$urlArray = $_GET;
			}
			else {
				$urlArray = self::pathinfoToArray($url);
			}
		}

		if (isset($urlArray[self::UrlCtrlName])) {
			$tmp = explode("-", $urlArray[self::UrlCtrlName]);

			if (count($tmp) == 2) {
				IReq::set("module", $tmp[0]);
				IReq::set(self::UrlCtrlName, $tmp[1]);
			}
			else {
				IReq::set(self::UrlCtrlName, $urlArray[self::UrlCtrlName]);
			}
		}

		if (isset($urlArray[self::UrlActionName])) {
			IReq::set(self::UrlActionName, $urlArray[self::UrlActionName]);

			if (IReq::get("action") == "run") {
				IReq::set("action", NULL);
			}
		}

		unset($urlArray[self::UrlActionName]);
		unset($urlArray[self::UrlActionName]);
		unset($urlArray[self::Anchor]);

		foreach ($urlArray as $key => $value ) {
			IReq::set($key, $value);
		}
	}

	static public function getRelative($path_a, $path_b)
	{
		$path_a = strtolower(str_replace("\\", "/", $path_a));
		$path_b = strtolower(str_replace("\\", "/", $path_b));
		$arr_a = explode("/", $path_a);
		$arr_b = explode("/", $path_b);
		$i = 0;

		while (true) {
			if ($arr_a[$i] == $arr_b[$i]) {
				$i++;
			}
			else {
				break;
			}
		}

		$len_b = count($arr_b);
		$len_a = count($arr_a);

		if (!$arr_b[$len_b - 1]) {
			$len_b = $len_b - 1;
		}

		if (!$len_a[$len_a - 1]) {
			$len_a = $len_a - 1;
		}

		$len = ($len_a < $len_b ? $len_b : $len_a);
		$str_a = "";
		$str_b = "";

		for ($j = $i; $j < $len; $j++) {
			if (isset($arr_a[$j])) {
				$str_a .= $arr_a[$j] . "/";
			}

			if (isset($arr_b[$j])) {
				$str_b .= "../";
			}
		}

		return $str_b . $str_a;
	}
}

interface IIServerVars
{
	public function requestUri();

	public function realUri();
}

$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}
class IServerVars implements IIServerVars
{
	static public function factory($server_type)
	{
		$obj = NULL;
		$type = array("apache" => "IServerVars_Apache", "iis" => "IServerVars_IIS", "nginx" => "IServerVars_Nginx");

		foreach ($type as $key => $value ) {
			if (stripos($server_type, $key) !== false) {
				$obj = new $value($server_type);
				break;
			}
		}

		if ($obj === NULL) {
			return new IServerVars();
		}
		else {
			return $obj;
		}
	}

	public function requestUri()
	{
		return $_SERVER["REQUEST_URI"];
	}

	public function realUri()
	{
		return $_SERVER["REQUEST_URI"];
	}
}

class IServerVars_Apache implements IIServerVars
{
	public function __construct($server_type)
	{
	}

	public function requestUri()
	{
		return $_SERVER["REQUEST_URI"];
	}

	public function realUri()
	{
		return $_SERVER["PHP_SELF"];
	}
}

class IServerVars_IIS implements IIServerVars
{
	public function __construct($server_type)
	{
	}

	public function requestUri()
	{
		$re = "";

		if (isset($_SERVER["REQUEST_URI"])) {
			$re = $_SERVER["REQUEST_URI"];
		}
		else if (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
			$re = $_SERVER["HTTP_X_REWRITE_URL"];
		}
		else {
			if (isset($_SERVER["SCRIPT_NAME"]) && isset($_SERVER["QUERY_STRING"])) {
				$re = $_SERVER["SCRIPT_NAME"] . "?" . $_SERVER["QUERY_STRING"];
			}
		}

		return $re;
	}

	public function realUri()
	{
		$re = "";

		if (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
			$re = (isset($_SERVER["ORIG_PATH_INFO"]) ? $_SERVER["ORIG_PATH_INFO"] : $_SERVER["HTTP_X_REWRITE_URL"]);
		}
		else {
			if (isset($_SERVER["PATH_INFO"]) && ($_SERVER["PATH_INFO"] != "")) {
				$re = $_SERVER["PATH_INFO"];
			}
			else {
				if (isset($_SERVER["SCRIPT_NAME"]) && isset($_SERVER["QUERY_STRING"])) {
					$re = $_SERVER["SCRIPT_NAME"] . "?" . $_SERVER["QUERY_STRING"];
				}
			}
		}

		return $re;
	}
}

class IServerVars_Nginx implements IIServerVars
{
	public function __construct($server_type)
	{
	}

	public function requestUri()
	{
		$re = "";

		if (isset($_SERVER["REQUEST_URI"])) {
			$re = $_SERVER["REQUEST_URI"];
		}

		return $re;
	}

	public function realUri()
	{
		$re = "";

		if (isset($_SERVER["DOCUMENT_URI"])) {
			$re = $_SERVER["DOCUMENT_URI"];
		}
		else if (isset($_SERVER["REQUEST_URI"])) {
			$re = $_SERVER["REQUEST_URI"];
		}

		return $re;
	}
}


