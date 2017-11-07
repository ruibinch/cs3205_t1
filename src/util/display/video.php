<!DOCTYPE html>
<html>
<body>

<video id="video" height="600" width= "800" preload="none" controls>
Your browser does not support video.
</video>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var xmlHTTP = new XMLHttpRequest();
    xmlHTTP.open('GET','<?php echo htmlspecialchars($fileurl)?>',true);

    xmlHTTP.responseType = 'arraybuffer';

    xmlHTTP.onload = function(e)
    {

        var arr = new Uint8Array(this.response);
        
        var raw = '';
        var i,j,subArray,chunk = 5000;
        for (i=0,j=arr.length; i<j; i+=chunk) {
           subArray = arr.subarray(i,i+chunk);
           raw += String.fromCharCode.apply(null, subArray);
        }

        var b64=btoa(raw);
        var dataURL="data:video/mp4;base64,"+b64;

        var source = document.createElement('source');
        source.setAttribute('src', dataURL);
        var video = document.getElementById("video");
        video.appendChild(source);
        video.play();
    };

    xmlHTTP.send();
});
</script>
</body>
</html>


