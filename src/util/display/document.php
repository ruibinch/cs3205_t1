<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
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
        var title = htmlencode(json['title']);
        var notes = htmlencode(json['notes']);
    	$('#text').html("<h1>"+title+"</h1><br><p>"+notes+"</p>");
    });
});
</script>