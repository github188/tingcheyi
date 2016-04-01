<?php
//停车预订系统
//by 贺江辉 版权所有 违法必究 QQ 522148648
?>
<?php
class IReq
{
	static public function get($key, $type = false)
	{
		if ($type == false) {
			if (isset($_GET[$key])) {
				return $_GET[$key];
			}
			else if (isset($_POST[$key])) {
				return $_POST[$key];
			}
			else {
				return NULL;
			}
		}
		else {
			if (($type == "get") && isset($_GET[$key])) {
				return $_GET[$key];
			}
			else {
				if (($type == "post") && isset($_POST[$key])) {
					return $_POST[$key];
				}
				else {
					return NULL;
				}
			}
		}
	}

	static public function set($key, $value, $type = "get")
	{
		if ($type == "get") {
			$_GET[$key] = $value;
		}
		else if ($type == "post") {
			$_POST[$key] = $value;
		}
	}
}

$domain1 = "192.168.0.111";
$domain2 = "test4.uguopai.com";
$LOCALDOMAIN = $_SERVER["HTTP_HOST"];
if ((strstr($LOCALDOMAIN, $domain1) == false) && (strstr($LOCALDOMAIN, $domain2) == false)) {
	exit("  ");
}

