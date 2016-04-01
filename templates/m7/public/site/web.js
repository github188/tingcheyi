/*define a array to mapping the navigation */
var menuNameArr = ["home", "experience", "experience", "experience", "experience", "about"];
/*define a global time to restrict mousewheel*/
var stime = getTime();
var gn = 0;
/*get Menu location*/
function getMenuSeriala(c) {
    return parseInt(c) + 1;
}
function getMenuSerialb(c) {
    return parseInt(c) - 1;
}
$(function () {
    /*refresh web& first load web*/
    firstPage();
    /* click by forward arrow */
    $(".forward").click(function () {
        var i = $(this).attr("id").substring(1);
        forwardClick(i);
    });
    /* click by navigation */
    $(".menus").click(function () {
        changeHref($(this).attr("id"));
    });
    /* click by experience number(navigation) */
    $(".w_hd").find("li").click(function () {
        changeHref($(this).attr("id"));
    });
    /* mouse wheel event */
    $('body').mousewheel(function (event, delta) {
        var etime = getTime();
        if (GetDateDiff(stime, etime, "second") >= 4 || gn == 0) {
            stime = etime;
            gn = 1;
            var i = "";
            $('.main_content').each(function () {
                if ($(this).attr("style") != undefined) {
                    if ($(this).attr("style").indexOf("0px") > 0) {
                        i = $(this).attr("id");
                    }
                }
            });
            if (i == "") {
                i = "m0";
            }
            i = i.substring(1);
            if (delta == "-1") {
                if (parseInt(i) < 5) {
                    forwardClick(i);
                }
            }
            if (delta == "1") {
                if (parseInt(i) > 0) {
                    backClick(i);
                }
            }
        }
    });
});

function firstPage() {
    $("#mc0").removeAttr("style");
    $(".eq_txt").animate({right: '0px'});
    $(".mover_img_s1").css("left","-950px").animate({ left: '0px' }, 1000);
    $("#mc0").animate({ right: '10%' }, 3000, function () {
        $(this).find("img").css({ "background": "url('./images/arrowr.png') no-repeat", "background-position": "right center" });
    });
}

function endPage() {
    $("#mc5").removeAttr("style");
    $("#mc5").animate({ right: '10%' }, 3000);
}

function forwardClick(i) {
    $(".eq_txt").removeAttr("style").removeClass("ico" + i).addClass("ico"+(parseInt(i) + 1));
    $("#m" + (parseInt(i) + 1)).removeAttr("style").css("left", "100%");
    $("#mb" + (parseInt(i) + 1)).removeAttr("style");
    $("#ms" + (parseInt(i) + 1)).removeAttr("style");
    $("#mc" + (parseInt(i) + 1)).removeAttr("style");
    $("#m" + i).animate({ left: '-100%' }, 500);
    $("#m" + (parseInt(i) + 1)).animate({ left: '0px' }, 500, function () {
        changeNav(getMenuSeriala(i));
    });
}

function backClick(i) {
    if (parseInt(i) - 1 == 0){
        $(".mover_img_s1").removeAttr("style").animate({ left: '0px' }, 1000);
    }
    $(".eq_txt").removeAttr("style").removeClass("ico" + i).addClass("ico" + (parseInt(i) - 1));
    $("#m" + (parseInt(i) - 1)).removeAttr("style").css("left", "-100%");
    $("#mb" + (parseInt(i) - 1)).removeAttr("style");
    $("#ms" + (parseInt(i) - 1)).removeAttr("style");
    $("#mc" + (parseInt(i) - 1)).removeAttr("style");
    $("#m" + i).animate({ left: '100%' }, 500);
    $("#m" + (parseInt(i) - 1)).animate({ left: '0px' }, 500, function () {
        changeNav(getMenuSerialb(i));
    });
}

/*change background*/
function changeHref(v) {
    var c = v.substring(v.length - 1);
    $("#m" + c).removeAttr("style").css("left", "0").siblings(".main_content").css("left", "-100%");
    changeNav(c);
}

/*the main program*/
function changeNav(c) {
    var tc = c;
    /*mapping the experience number(navigation)*/
    if (tc > 0 && tc < 5) {
        tc = 1;
        $(".w_hd").css("background", "url(./images/hd_icon" + c + ".png) no-repeat");
    } else {
        $(".w_hd").removeAttr("style");
    }
    /*mapping the navigation*/
    for (var i = 0; i < 6; i++) {
        if (tc == i) {
            $("#menu" + i).attr("src", "./images/" + menuNameArr[i] + "_hover.gif");
        } else {
            $("#menu" + i).attr("src", "./images/" + menuNameArr[i] + ".gif");
        }
    }
    /*mapping animate*/
    toMainAnimate(c);
}

/*this is animate program*/
function toMainAnimate(c) {
    if (c == 5) {
        $(".eq_txt").css("display", "none");
        $(".eq_d").css("display", "none");
    } else {
        $(".eq_d").removeAttr("style");
        $(".eq_txt").removeAttr("style").removeAttr("class").addClass("eq_txt ico" + c);
        $(".eq_txt").animate({ right: '0px' });
    }
    if (c == 0) { animateE0(c); }
    if (c == 1) { animateE1(c); }
    if (c == 2) { animateE2(c); }
    if (c == 3) { animateE3(c); }
    if (c == 4) { animateE4(c); }
    if (c == 5) { animateE5(c); }
}

function animateE0(c) {
    firstPage();
}

function animateE5(c) {
    endPage();
}

function animateE1(c) {
    $("#mb" + c).removeAttr("style");
    $("#ms" + c).removeAttr("style");
    $("#mc" + c).removeAttr("style");
    $("#i" + c).removeAttr("style");
    $("#mb" + c).animate({ 'top': '15%', 'opacity': '1', 'filter': 'alpha(opacity=1)', '-moz-opacity': '1' }, 1000);
    //$("#ms" + c).animate({ left: '50%' }, 500);
    //$("#ms" + c).animate({ left: '60%' }, 250);
    //$("#ms" + c).animate({ left: '57%' }, 130);
    $("#mc" + c).animate({ right: '20%' }, 3000, function () {
        $(this).find("img").css({ "background": "url('./images/arrowr.png') no-repeat", "background-position": "right center" });
    });
}
function animateE2(c) {
    $("#mb" + c).removeAttr("style");
    $("#ms" + c).removeAttr("style");
    $("#mc" + c).removeAttr("style");
    $("#i" + c).removeAttr("style");
    $("#mb" + c).animate({ 'bottom': '18%', 'opacity': '1', 'filter': 'alpha(opacity=1)', '-moz-opacity': '1' }, 1000);
    //$("#ms" + c).animate({ right: '50%' }, 500);
    //$("#ms" + c).animate({ right: '63%' }, 250);
    //$("#ms" + c).animate({ right: '60%' }, 130);
    $("#mc" + c).animate({ right: '20%' }, 3000, function () {
        $(this).find("img").css({ "background": "url('./images/arrowr.png') no-repeat", "background-position": "right center" });
    });
}
function animateE3(c) {
    $("#mb" + c).removeAttr("style");
    $("#ms" + c).removeAttr("style");
    $("#mc" + c).removeAttr("style");
    $("#i" + c).removeAttr("style");
    $("#mb" + c).animate({ 'left': '20%', 'opacity': '1', 'filter': 'alpha(opacity=1)', '-moz-opacity': '1' }, 1000);
    //$("#ms" + c).animate({ left: '50%' }, 500);
    //$("#ms" + c).animate({ left: '60%' }, 250);
    //$("#ms" + c).animate({ left: '57%' }, 130);
    $("#mc" + c).animate({ right: '20%' }, 3000, function () {
        $(this).find("img").css({ "background": "url('./images/arrowr.png') no-repeat", "background-position": "right center" });
    });
}
function animateE4(c) {
    $("#mb" + c).removeAttr("style");
    $("#ms" + c).removeAttr("style");
    $("#mc" + c).removeAttr("style");
    $("#i" + c).removeAttr("style");
    $("#mb" + c).animate({ 'right': '50%', 'opacity': '1', 'filter': 'alpha(opacity=1)', '-moz-opacity': '1' }, 1000);
    //$("#ms" + c).animate({ right: '50%' }, 500);
    //$("#ms" + c).animate({ right: '63%' }, 250);
    //$("#ms" + c).animate({ right: '60%' }, 130);
    $("#mc" + c).animate({ right: '20%' }, 3000, function () {
        $(this).find("img").css({ "background": "url('./images/arrowr.png') no-repeat", "background-position": "right center" });
    });
}

/*close sv*/
$("#closesv").click(function(){
	$(".sv").css("display","none");
});

/*click download*/
$("#eq_download_btn").click(function () {
    $(".sv").css("display", "block");
});

/*phone_slider*/
var int=self.setInterval("slider()",3000)
function slider()
  {
  	var id = $(".current1").attr("id");
  	id = id.substring(1);
	var nxtId = 1;
	if(parseInt(id)<3){ 
		nxtId = parseInt(id)+1;
	}
	
	$(".current1").animate({left:'-315px'},function(){$(this).removeClass("current1").removeAttr("style").addClass("current2")});

	$("#s"+nxtId).animate({left:'0px'},function(){ $(this).removeClass("current2").addClass("current1")});
  }


//GetDateDiff(start, end, "day")
/* 
* 获得时间差,时间格式为 年-月-日 小时:分钟:秒 或者 年/月/日 小时：分钟：秒 
* 其中，年月日为全格式，例如 ： 2010-10-12 01:00:00 
* 返回精度为：秒，分，小时，天 
*/
function GetDateDiff(startTime, endTime, diffType) {
    ////将xxxx-xx-xx的时间格式，转换为 xxxx/xx/xx的格式  
    //startTime = startTime.replace(/-/g, "/");
    //endTime = endTime.replace(/-/g, "/");
    //将计算间隔类性字符转换为小写  
    diffType = diffType.toLowerCase();
    var sTime = new Date(startTime); //开始时间  
    var eTime = new Date(endTime); //结束时间  
    //作为除数的数字  
    var divNum = 1;
    switch (diffType) {
        case "second":
            divNum = 1000;
            break;
        case "minute":
            divNum = 1000 * 60;
            break;
        case "hour":
            divNum = 1000 * 3600;
            break;
        case "day":
            divNum = 1000 * 3600 * 24;
            break;
        default:
            break;
    }
    return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum)); //17jquery.com  
}

function getTime() {
    var data = new Date();
    var year = data.getFullYear();  //获取年
    var month = data.getMonth() + 1;    //获取月
    var day = data.getDate(); //获取日
    var hours = data.getHours();
    var minutes = data.getMinutes();
    var second = data.getSeconds();
    return year + "/" + month + "/" + day + " " + hours + ":" + minutes + ":" + second;
}
console.log("随心去停车，易找天下位");