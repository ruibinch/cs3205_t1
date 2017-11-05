<?php
	//txhistory.php: shows transaction history. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"]);
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
?>
<h1>Transaction History</h1>

<form id="showAll">
	<input type="hidden" name="mode" value="all">
	<input type="Submit" value="Display ALL Transactions">
</form>
<br/>

<div class="contentField" id="loaderDiv">
	<br/>
	<div class="loader" id="loader"></div>
	<br/>
</div>

<br/>
<div class="contentField" id="resultDiv">
</div>

<script>
	$(document).ready(function(){
		$('#showAll').on('submit', function(e){
			$("#resultDiv").hide();
			$("#loaderDiv").show();
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/management/txhandler.php',
				data: $('#showAll').serialize(),
				cache: false,
				success: function(result) {
					$("#loaderDiv").hide();
					$("#resultDiv").show();
					$("#resultDiv").html(result);
				},
				error: function() {
					alert('Warning: Server may be unavailable at this point in time.');
				}
			});
		});
	});
</script>

