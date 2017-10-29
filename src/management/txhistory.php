<?php
	//txhistory.php: shows transaction history. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
?>

<h1>Transaction History</h1>
<h3>Click any of the buttons to display relevant transaction history.</h3>

<button type="button" id="showAll">Display ALL Transactions</button><br/><br/>
<button type="button" id="showWarn">Display Suspicious Transactions</button><br/><br/>

<div class="contentField" id="loaderDiv">
	<br/>
	<div class="loader" id="loader"></div>
	<br/>
</div>

<div class="contentField" id="resultDiv">
</div>

<div class="contentField">
	<h3>This page is a stub. Intention is to search transaction by type, date/time, user (if possible).</h3>
</div>

<script>
	$(document).ready(function(){
		$( "#showAll" ).click(function() {
			$("#loaderDiv").show();
			$("#resultDiv").hide();
			e.preventDefault();
		});
	});
</script>