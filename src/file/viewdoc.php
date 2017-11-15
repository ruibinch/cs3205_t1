<?php
include_once '../util/jwt.php';
include_once '../util/csrf.php';
include_once '../util/ssl.php';
include_once '../util/logger.php';
$result = WebToken::verifyToken($_COOKIE["jwt"]);
$user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . 'api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";
$uid = $result->uid;

$csrftoken = CSRFToken::generateToken($uid, "viewdoc");
setcookie("vcsrf", $csrftoken, time() + 3600);
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<script type="text/javascript"
	src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<style type="text/css">
ul {
	list-style-type: none;
	margin: 0;
	padding: 0;
	overflow: hidden;
	background-color: #f1f1f1;
}

li {
	float: left;
}

li a {
	display: block;
	color: #000;
	text-align: center;
	padding: 14px 16px;
	text-decoration: none;
	height: 82px;
}

li a:hover {
	background-color: #999;
	color: white;
}
</style>
</head>
<div id="window" style="">
	<div id="header"
		style="position: fixed; width: 250px; height: 5%; left: 0; top: 0; overflow: hidden;">
		<ul>
			<li><a href='/main.php' style='text-align: center; width: 100%'>BACK
					TO MAIN</a></li>
		</ul>
	</div>
	<div id="menu"
		style="position: fixed; width: 250px; height: 95%; left: 0; bottom: 0; overflow: scroll;">
<?php
Log::recordTX($uid, "Info", "Opened file viewer");
// Get all file to be listed in file viewer
$resultOfConsents = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/consent/user/" . $uid));
$resultOfOwned = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/record/all/" . $uid));
if (! isset($resultOfConsents->consents)) {
    $listOfConsents = array();
    Log::recordTX($uid, "Error", "Wrong uid passed to api/team1/consent/user/ in viewdoc.php: " . $uid);
} else {
    $listOfConsents = $resultOfConsents->consents;
}
if (! isset($resultOfOwned->records)) {
    $listOfConsents = array();
    Log::recordTX($uid, "Error", "Wrong uid passed to api/team1/record/all/ in viewdoc.php: " . $uid);
} else {
    $listOfOwned = $resultOfOwned->records;
}

if (! isset($_GET['rid'])) {
    // Default: display all listed files
    foreach ($listOfConsents as $consent) {
        if ($consent->status) {
            if (strpos($consent->rid, '/') !== false) {
                Log::recordTX($uid, "Error", "Unrecognised rid: " . $consent->rid);
                continue;
            }
            $detail = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/record/" . $consent->rid));
            if (isset($detail->result))
                continue;
            
            if (strpos($detail->uid, '/') !== false) {
                Log::recordTX($uid, "Error", "Unrecognised uid: " . $detail->uid);
                continue;
            }
            $owner = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/user/uid/" . $detail->uid));
            echo "<ul>";
            echo "<li style='text-align: center; width=150px; word-wrap: break-word;'><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(" . htmlentities($consent->rid) . ")'><strong>" . htmlentities($detail->title) . "</strong><br>" . htmlentities($owner->firstname) . " " . htmlentities($owner->lastname) . "<br><small>" . htmlentities($detail->modifieddate) . "</small></a></li>";
            echo "<li style='text-align: center;'><a class='menu_li_a' href='javascript:void(0)' onclick='downloadfile(" . htmlentities($consent->rid) . ")'><img src='download.png' height=40 width=40></a></li>";
            echo "</ul>";
        }
    }
    foreach ($listOfOwned as $owned) {
        echo "<ul>";
        echo "<li style='text-align: center; width=150px; word-wrap: break-word;'><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(" . htmlentities($owned->rid) . ")'><strong>" . htmlentities($owned->title) . "</strong><br><small>" . htmlentities($owned->modifieddate) . "</small></a></li>";
        echo "<li style='text-align: center;'><a class='menu_li_a' href='javascript:void(0)' onclick='downloadfile(" . htmlentities($owned->rid) . ")'><img src='download.png' height=40 width=40></a></li>";
        echo "</ul>";
    }
} else {
    // Display only one file
    $rid = $_GET['rid'];
    foreach ($listOfConsents as $consent) {
        if ($consent->status && $consent->rid == $rid) {
            if (strpos($consent->rid, '/') !== false) {
                Log::recordTX($uid, "Error", "Unrecognised rid: " . $consent->rid);
                continue;
            }
            $detail = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/record/" . $consent->rid));
            if (strpos($detail->uid, '/') !== false) {
                Log::recordTX($uid, "Error", "Unrecognised uid: " . $detail->uid);
                continue;
            }
            $owner = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/../misc.ini")['server4'] . "api/team1/user/uid/" . $detail->uid));
            echo "<ul>";
            echo "<li style='text-align: center; width=150px; word-wrap: break-word;'><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(" . htmlentities($consent->rid) . ")'><strong>" . htmlentities($detail->title) . "</strong><br>" . htmlentities($owner->firstname) . " " . htmlentities($owner->lastname) . "<br><small>" . htmlentities($detail->modifieddate) . "</small></a></li>";
            echo "<li style='text-align: center;'><a class='menu_li_a' href='javascript:void(0)' onclick='downloadfile(" . htmlentities($consent->rid) . ")'><img src='download.png' height=40 width=40></a></li>";
            echo "</ul>";
        }
    }
    foreach ($listOfOwned as $owned) {
        if ($owned->rid == $rid) {
            echo "<ul>";
            echo "<li style='text-align: center; width=150px; word-wrap: break-word;'><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(" . htmlentities($owned->rid) . ")'><strong>" . htmlentities($owned->title) . "</strong><br><small>" . htmlentities($owned->modifieddate) . "</small></a></li>";
            echo "<li style='text-align: center;'><a class='menu_li_a' href='javascript:void(0)' onclick='downloadfile(" . htmlentities($owned->rid) . ")'><img src='download.png' height=40 width=40></a></li>";
            echo "</ul>";
        }
    }
}
?>
	</div>
	<div
		style="position: absolute; height: 100%; left: 250px; right: 0; top: 0; overflow: auto; margin-left: 0px; margin-right: 0px">
		<iframe id="display"
			style="position: absolute; width: 100%; height: 100%"> </iframe>
	</div>
</div>
<script type="text/javascript">
function viewfile(rid) {
	document.getElementById("display").src='display.php?rid=' + rid + '&csrf=' + $.cookie("vcsrf");
}
function downloadfile(rid) {
	document.getElementById("display").src='display.php?rid=' + rid + '&csrf=' + $.cookie("vcsrf") + "&method=download";
}
</script>
</html>
