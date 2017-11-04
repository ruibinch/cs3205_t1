<?php
include_once '../util/jwt.php';
include_once '../util/csrf.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
$user_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";
$uid = $result->uid;

$csrftoken = CSRFToken::generateToken($uid, "viewdoc");
setcookie("vcsrf", $csrftoken, time()+3600);
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<style type="text/css">
.menu_ul {
	list-style-type: none;
	margin: 0;
	padding: 0;
	width: 200px;
	background-color: #f1f1f1;
}

.menu_li_a {
	display: block;
	color: #000;
	padding: 8px 16px;
	text-decoration: none;
}

/* Change the link color on hover */
.menu_li_a:hover {
	background-color: #555;
	color: white;
}
</style>
</head>
<div id="window" style="">
	<div id="header"
		style="position: fixed; width: 100%; height: 10%; left: 0; top: 0; overflow: hidden;">
<a href='/main.php'>Back to main</a>
</div>
	<div id="menu"
		style="position: fixed; width: 12%; height: 90%; left: 0; bottom: 0; overflow: scroll;">
		<ul style="width: 100%" class="menu_ul">
<?php
echo ssl::get_content('https://cs3205-4.comp.nus.edu.sg/api/team1/user/uid/' . $result->uid);
$listOfConsents = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/consent/user/" . $uid))->consents;
$listOfOwned = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/all/".$uid))->records;
$rids = [];
foreach ($listOfConsents as $consent) {
    if ($consent->status) {
        $detail = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/" . $consent->rid));
        $owner = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/" . $detail->uid));
        echo "<li><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(".$consent->rid.")'><strong>" . htmlentities($detail->title) . "</strong><br>" . htmlentities($owner->firstname) . " " . htmlentities($owner->lastname) . "<br><small>" . $detail->modifieddate . "</small></a></li>";
    }
}
foreach ($listOfOwned as $owned) {
    echo "<li><a class='menu_li_a' href='javascript:void(0)' onclick='viewfile(".$owned->rid.")'><strong>" . htmlentities($owned->title) . "</strong><br><small>" . $owned->modifieddate . "</small></a></li>";
}

?>
</ul>
	</div>
	<iframe id="display"
		style="position: fixed; width: 88%; height: 90%; right: 0; bottom: 0; overflow: hidden;">
	</iframe>
</div>
<script type="text/javascript">
function viewfile(rid) {
	document.getElementById("display").src='display.php?rid=' + rid + '&csrf=' + $.cookie("vcsrf");
}
</script>
</html>