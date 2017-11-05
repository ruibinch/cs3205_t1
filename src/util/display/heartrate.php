<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<h1>Heart Rate</h1>
<div id="text"></div>
<script>
function htmlencode(s){
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(s));
    return div.innerHTML;
}
$(document).ready(function() {
    var data = $.get("<?php echo htmlspecialchars($fileurl)?>", function (data) {
        var json = jQuery.parseJSON(data);
        var content = htmlencode(json['content']);
    	$('#text').html(content);
    });
});
</script>