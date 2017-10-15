<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<div id="chart"></div>
<script>

Plotly.d3.csv('<?php echo htmlspecialchars($fileurl)?>', function(err, rows) {

	console.log(rows);
	var json;
	$.ajax({
	     url: '<?php echo htmlspecialchars($jsonurl)?>',
	     type: "GET",
	     dataType: "JSON",
	     success: function(json){
	        console.log(json);
	       	if (!json.hasOwnProperty("headers") || !json["headers"].hasOwnProperty("x")) {
	    		document.getElementById("chart").innerHTML = "Wrong file format";
	    		throw new Error("Wrong JSON file format");
	    	}
	    	
	    	var xheader = json["headers"]["x"];
	    	var x = [];
	    	rows.forEach(function(entry) {
	    		x.push(parseInt(entry[xheader]));
	    	});

	    	var traces = [];
	    	for (var k in json["headers"]) {
	    		if (k != "x") {
	    			var trace = {};
	    			trace["y"] = [];
	    			rows.forEach(function(entry) {
	    				trace["y"].push(parseInt(entry[k]));
	    			});
	    			trace["x"] = x;
	    			trace["mode"] = 'lines';
	    			trace["name"] = escape(k);
	    			traces.push(trace);
	    		}
	    	}

	    	var layout = {
	    		title : escape(json["title"])
	    	};

			console.log(traces);
	    	
	    	Plotly.newPlot("chart", traces, layout);
	     }
	});
	

});

</script>