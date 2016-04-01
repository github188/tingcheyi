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
	public function savesingle()
	{
		$id = IReq::get("uid");
		$data["addtime"] = strtotime(IReq::get("addtime") . " 00:00:00");
		$data["title"] = IReq::get("title");
		$data["content"] = IReq::get("content");
		$data["code"] = IReq::get("code");
		$data["seo_key"] = IFilter::act(IReq::get("seo_key"));
		$data["seo_content"] = IFilter::act(IReq::get("seo_content"));

		if (empty($id)) {
			$link = IUrl::creatUrl("adminpage/single/module/addsingle");

			if (empty($data["content"])) {
				$this->message("single_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("single_emptytitle", $link);
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "single", $data);
		}
		else {
			$link = IUrl::creatUrl("single/addsingle/id/" . $id);

			if (empty($data["content"])) {
				$this->message("single_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("single_emptytitle", $link);
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "single", $data, "id='" . $id . "'");
		}

		$link = IUrl::creatUrl("adminpage/single/module/singlelist");
		$this->success("success", $link);
	}

	public function delsingle()
	{
		$uid = IReq::get("id");
		$uid = (is_array($uid) ? $uid : array($uid));
		$ids = join(",", $uid);

		if (empty($ids)) {
			$this->message("single_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "single", "id in (" . $ids . ") ");
		$this->success("success");
	}
}


