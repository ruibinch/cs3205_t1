<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
<div id="chart"></div>
<script>
Plotly.d3.json('/tmp/json2.json', function(json){
	var multix = Number(json[json["x_axis"]]["multiplier"]);
	var x;
	if (json.hasOwnProperty('sessionTime')) {
		x = json[json["x_axis"]]["values"].map(function(f){
    		return new Date(f + json["sessionTime"]);
    	});
	} else {
    	x = json[json["x_axis"]]["values"].map(function(f){
    		return f * multix;
    	});
	}
	var traces = json[json["y_axis"]]["data"].map(function(e){
		var multi = Number(json[json["y_axis"]]["multiplier"]);
		return {
			x: x,
			y: e["values"].map(function(f){
				return f * multi;
			}),
			name: e["name"],
			mode: "lines",
		};
	});
	var layout = {
		title: json["title"],
		xaxis: {
			title: json["x_axis"] + "/" + json[json["x_axis"]]["displayUnit"],
		},
		yaxis: {
			title: json["y_axis"] + "/" + json[json["x_axis"]]["displayUnit"],
		},
		showlegend: true
	}
	Plotly.newPlot('chart', traces, layout);
});

</script>