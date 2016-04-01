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
class method extends baseclass
{
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

	public function userupload()
	{
		$link = IUrl::creatUrl("member/login");
		if (($this->member["uid"] == 0) && ($this->admin["uid"] == 0)) {
			$this->message("未登陆", $link);
		}

		$_FILES["imgFile"] = $_FILES["head"];
		$type = IFilter::act(IReq::get("type"));

		if (empty($type)) {
			$this->message("未定义的操作");
		}

		$json = new Services_JSON();
		$uploadpath = "upload/user/";
		$filepath = "/upload/user/";
		$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "png"));
		$file = $upload->getfile();
		if (($upload->errno != 15) && ($upload->errno != 0)) {
			$this->message($upload->errmsg());
		}
		else {
			if ($type == "userlogo") {
				$arr["logo"] = $filepath . $file[0]["saveName"];
				$this->mysql->update(Mysite::$app->config["tablepre"] . "member", $arr, "uid = " . $this->member["uid"] . " ");
			}
			else if ($type == "goods") {
				$shopid = ICookie::get("adminshopid");
				$gid = intval(IFilter::act(IReq::get("gid")));
				$data["img"] = $filepath . $file[0]["saveName"];
				$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $gid . "' and shopid='" . $shopid . "'");
			}
			else if ($type == "shoplogo") {
				$shopid = ICookie::get("adminshopid");

				if (!empty($shopid)) {
					$data["shoplogo"] = $filepath . $file[0]["saveName"];
					$this->mysql->update(Mysite::$app->config["tablepre"] . "shop", $data, "id='" . $shopid . "'");
				}
			}

			$this->success($filepath . $file[0]["saveName"]);
		}
	}

	public function goodsupload()
	{
		$link = IUrl::creatUrl("member/login");
		if (($this->member["uid"] == 0) && ($this->admin["uid"] == 0)) {
			$this->message("未登陆", $link);
		}

		$type = IReq::get("type");
		$goodsid = intval(IReq::get("goodsid"));
		$shopid = ICookie::get("adminshopid");

		if ($shopid < 0) {
			echo "无权限操作";
			exit();
		}

		if (is_array($_FILES) && isset($_FILES["imgFile"])) {
			$json = new Services_JSON();
			$uploadpath = "upload/shop/";
			$filepath = "/upload/shop/";
			$upload = new upload($uploadpath, array("gif", "jpg", "jpge", "doc", "png"));
			$file = $upload->getfile();
			if (($upload->errno != 15) && ($upload->errno != 0)) {
				echo "<script>parent.uploaderror('" . json_encode($upload->errmsg()) . "');</script>";
			}
			else {
				if ((0 < $goodsid) && (0 < $shopid)) {
					$data["img"] = $filepath . $file[0]["saveName"];
					$this->mysql->update(Mysite::$app->config["tablepre"] . "goods", $data, "id='" . $goodsid . "' and shopid='" . $shopid . "'");
				}

				echo "<script>parent.uploadsucess('" . $filepath . $file[0]["saveName"] . "');</script>";
			}

			exit();
		}

		$imgurl = "";
		if ((0 < $goodsid) && ($type == "goods")) {
			$temp = $this->mysql->select_one("select img from " . Mysite::$app->config["tablepre"] . "goods where id='" . $goodsid . "' and shopid='" . $shopid . "'");
			$imgurl = $temp["img"];
		}

		Mysite::$app->setdata(array("type" => $type, "goodsid" => $goodsid, "imgurl" => $imgurl));
	}
}


