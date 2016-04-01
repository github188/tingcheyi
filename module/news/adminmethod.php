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
		$link = IUrl::creatUrl("/adminpage/news/module/newslist");
		$this->refunction("", $link);
	}

	public function savenewstype()
	{
		$id = intval(IReq::get("uid"));
		$data["name"] = IReq::get("name");
		$data["orderid"] = intval(IReq::get("orderid"));
		$data["type"] = intval(IReq::get("type"));
		$data["parent_id"] = intval(IReq::get("parent_id"));
		$data["displaytype"] = intval(IReq::get("displaytype"));

		if (empty($data["name"])) {
			$this->message("news_emptynewstypename");
		}

		if (empty($id)) {
			$this->mysql->insert(Mysite::$app->config["tablepre"] . "newstype", $data);
		}
		else {
			$this->mysql->update(Mysite::$app->config["tablepre"] . "newstype", $data, "id='" . $id . "'");
		}

		$this->success("success", "");
	}

	public function delnews()
	{
		$id = IFilter::act(IReq::get("id"));

		if (empty($id)) {
			$this->message("news_empty");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);

		if (empty($ids)) {
			$this->message("news_empty");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "news", " id in($ids)");
		$this->success("success", "");
	}

	public function delnewstype()
	{
		$id = IFilter::act(IReq::get("id"));

		if (empty($id)) {
			$this->message("news_emptytype");
		}

		$ids = (is_array($id) ? join(",", $id) : $id);

		if (empty($ids)) {
			$this->message("news_emptytype");
		}

		$this->mysql->delete(Mysite::$app->config["tablepre"] . "newstype", " id in($ids)");
		$this->success("success", "");
	}

	public function addnews()
	{
		$id = intval(IReq::get("id"));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "news where id=" . $id . "  ");
		$data["typlist"] = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "newstype  order by orderid desc ");
		$temptypeid = array();

		if (!empty($id)) {
			$tempid = $data["info"]["typeid"];

			while (0 < $tempid) {
				$getstr = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "newstype where id=" . $tempid . "  ");

				if (!empty($getstr)) {
					$tempid = $getstr["parent_id"];
					$temptypeid[] = $getstr["id"];
				}
				else {
					$temptypeid[] = $tempid;
					$tempid = 0;
				}
			}

			$data["allids"] = $temptypeid;
		}
		else {
			$data["allids"] = array();
		}

		Mysite::$app->setdata($data);
	}

	public function savenews()
	{
		$id = IReq::get("uid");
		$data["addtime"] = strtotime(IReq::get("addtime") . " 00:00:00");
		$data["title"] = IReq::get("title");
		$data["content"] = IReq::get("content");
		$data["orderid"] = IReq::get("orderid");
		$data["typeid"] = IReq::get("typeid");
		$data["seo_key"] = IFilter::act(IReq::get("seo_key"));
		$data["seo_content"] = IFilter::act(IReq::get("seo_content"));

		if (empty($id)) {
			$link = IUrl::creatUrl("adminpage/news/module/addnews");

			if (empty($data["content"])) {
				$this->message("news_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("news_emptytitle", $link);
			}

			$this->mysql->insert(Mysite::$app->config["tablepre"] . "news", $data);
		}
		else {
			$link = IUrl::creatUrl("adminpage/news/module/addnews/id/" . $id);

			if (empty($data["content"])) {
				$this->message("news_emptycontent", $link);
			}

			if (empty($data["title"])) {
				$this->message("news_emptytitle", $link);
			}

			$this->mysql->update(Mysite::$app->config["tablepre"] . "news", $data, "id='" . $id . "'");
		}

		$link = IUrl::creatUrl("adminpage/news/module/newslist");
		$this->success("success", $link);
	}

	public function addnewstype()
	{
		$id = intval(IReq::get("id"));
		$data["info"] = $this->mysql->select_one("select * from " . Mysite::$app->config["tablepre"] . "newstype where id=" . $id . "  ");
		$mydatinfo = $this->mysql->getarr("select * from " . Mysite::$app->config["tablepre"] . "newstype ");
		$data["typeoption"] = $this->huannewtype($mydatinfo, 0, 0, $data["info"]["parent_id"]);
		Mysite::$app->setdata($data);
	}

	public function huannewtype($mydatinfo, $parent_id, $grade, $nowid = 0)
	{
		$htmlcontent = "";
		$tempshow = "";

		for ($i = 0; $i < $grade; $i++) {
			$tempshow .= "&nbsp&nbsp&nbsp&nbsp";
		}

		foreach ($mydatinfo as $key => $value ) {
			if ($value["parent_id"] == $parent_id) {
				if ($value["type"] == 2) {
					$onoption = ($nowid == $value["id"] ? "selected" : "");
					$htmlcontent .= "<option value=\"" . $value["id"] . "\" " . $onoption . ">" . $tempshow . $value["name"] . "</option>";
					$htmlcontent .= $this->huannewtype($mydatinfo, $value["id"], $grade + 1, $nowid);
				}
			}
		}

		return $htmlcontent;
	}
}


