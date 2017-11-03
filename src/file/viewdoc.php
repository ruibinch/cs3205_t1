<?php
include_once '../util/jwt.php';
include_once '../util/csrf.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
$user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";

$uid = $result->uid;
$show = $_GET['show'];

$csrftoken = CSRFToken::generateToken($uid, "viewdoc");
setcookie("vcsrf", $csrftoken, time()+3600);
?>
<!DOCTYPE html>
<html>
<head>
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
<?php 
$listOfPatients = json_decode(file_get_contents("http://172.25.76.76/api/team1/treatment/therapist/" . $uid . "/true"))->treatments;

?>
</div>
	<div id="menu"
		style="position: fixed; width: 12%; height: 90%; left: 0; bottom: 0; overflow: scroll;">
		<ul style="width: 100%" class="menu_ul">
<?php
if ($user_type == 'therapist') {
    if ($show == 'all') {
        
    } else if ($show == 'notes') {
        
    } else if (!isset(json_decode(file_get_contents("http://172.25.76.76/api/team1/user/uid/".$show))->result)) {
        $listOfConsents = json_decode(file_get_contents("http://172.25.76.76/api/team1/consent/owner/" . $show . "/" . $uid))->consents;
        if (isset($listOfConsents)) {
            foreach ($listOfConsents as $consent) {
                if ($consent->status)
                    echo "<li><a class='menu_li_a' href='javascript:void()' onclick='viewfile(".$consent->rid.")'><strong>" . htmlentities($consent->title) . "</strong><br>" . htmlentities($consent->owner_firstname) . " " . htmlentities($consent->owner_lastname) . "<br><small>" . $consent->modifieddate . "</small></a></li>";
            }
        }
    }
} else {
    
}
?>
</ul>
	</div>
	<iframe name="display"
		style="position: fixed; width: 88%; height: 90%; right: 0; bottom: 0; overflow: hidden;">
	</iframe>
</div>
<script type="text/javascript">
function viewfile(rid) {
	document.getElementById().src='display.php?rid=' + $rid + '&csrf=' + $.cookie("vcsrf");
}
</script>
</html>