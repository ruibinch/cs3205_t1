<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<div id="text"></div>
<script>
$.get("<?php echo htmlspecialchars($fileurl)?>", function (data) {
	$('#text').text(data);
});
</script>