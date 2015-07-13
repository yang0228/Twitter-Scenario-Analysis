var mel = "Melbourne";
var la = "Los Angeles";
var colors = [ 'gold', 'gray', '#76A7FA', '#C5A5CF', 'silver', '#13A1CB',
		'#E4D5A3', '#C86368', '#5F192C', '#6F8A79', '#E5E6D8', '#422833',
		'#728CB0', '#C296B6', '#EB77A6' ];

//split rawdata into two seprate array for each city.
function split_rawdata(rawdata){
	var ftuple1=[];
	var ftuple2=[];
	for(var i=0;i<rawdata.length;i++){
		var line=rawdata[i];
		if (line[1])
		ftuple1.push([line[0],line[1]]);
		if (line[2])
		ftuple2.push([line[0],line[2]]);
	}
	return [ftuple1,ftuple2];
}
function drawBarChart(rawdata,pos,useColumnChart,options,split) {
	// var rawdata=[
	// ['', 'Sales', 'Expenses'],
	// ['2004', 1000, 400],
	// ['2005', 1170, 460],
	// ['2006', 660, 1120],
	// ['2007', 1030, 540]
	// ];
	split = typeof split !== 'undefined' ? split : false;
	if(split){
		rdata=split_rawdata(rawdata);
		var data1=google.visualization.arrayToDataTable(rdata[0]);
		var data2=google.visualization.arrayToDataTable(rdata[1]);

		$("<div id='main_chart_1' class='main_chart_view'></div>").insertAfter("#main_chart");

		var chart1 = useColumnChart&rawdata.length < 3 ? new google.visualization.ColumnChart(
				document.getElementById(pos)): new google.visualization.BarChart(document.getElementById(pos));
		var chart2 = useColumnChart&rawdata.length < 3 ? new google.visualization.ColumnChart(
				document.getElementById(pos+"_1")): new google.visualization.BarChart(document.getElementById(pos+"_1"));

		chart1.draw(data1, {colors : get_colors(1)});
		chart2.draw(data2, {colors : get_colors(1)});
	}
	else{
	var data = google.visualization.arrayToDataTable(rawdata);

//	var options = {
//		// title: 'Company Performance',
//		// vAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
//		colors : color_for_city
//	};
	var chart = useColumnChart&rawdata.length < 3 ? new google.visualization.ColumnChart(
			document.getElementById(pos)): new google.visualization.BarChart(document.getElementById(pos));
	chart.draw(data, options);
	}
}

function drawPieChart(rawdata1, rawdata2, city_name1, city_name2) {
	// var data = google.visualization.arrayToDataTable([
	// ['Language', 'Speakers (in millions)'],
	// ['German', 5.85],
	// ['French', 1.66],
	// ['Italian', 0.316],
	// ['Romansh', 0.0791],
	// ]);
	var data = google.visualization.arrayToDataTable(rawdata1);
	var options = {
		// legend: 'none',
		pieSliceText : 'label',
		title : city_name1,
		pieStartAngle : 100,
	};

	var chart = new google.visualization.PieChart(document
			.getElementById('char1'));
	chart.draw(data, options);

	var data = google.visualization.arrayToDataTable(rawdata2);
	var options = {
		// legend: 'none',
		pieSliceText : 'label',
		title : city_name2,
		pieStartAngle : 100,
	};

	var chart = new google.visualization.PieChart(document
			.getElementById('char2'));
	chart.draw(data, options);

}

function arrayUnique(array) {
	var a = array.concat();
	for ( var i = 0; i < a.length; ++i) {
		for ( var j = i + 1; j < a.length; ++j) {
			if (a[i] === a[j])
				a.splice(j--, 1);
		}
	}

	return a;
}

function get_value_from_rows(key, rows) {
	for ( var i = 0; i < rows.length; i++) {
		if (rows[i].Key == key) {
			return rows[i].Value;
		}
	}
	return 0;
}

function select_senario(senario_name) {
	var snstr = "[id='" + senario_name + "']";
	$("li.active").removeClass("active");
	$(snstr).parent().addClass("active");
	qurl = '/scenario/' + senario_name;
	$
			.ajax({
				type : 'GET',
				url : qurl,
			})
			.done(
					function(data) {
						// handle revieved data.
						// alert(data);
						$("#topic_name").html(senario_name);

						var json = $.parseJSON(data);
						if(json.state!=1||json.data==null){
//							alert(json.err);
							$("#main_chart").html(json.err);
							return;
						}

						var col_first = [];
						var c1 = json.data[0];
						var c2 = json.data[1];
						var city1 = c1.city == 'mel' ? mel : la;
						var city2 = c2.city == 'la' ? la : mel;

						var c1_t_total=c1.total_t;
						var c2_t_total=c2.total_t;
						$("#total_t1").html("<p>"+city1+":</p> "+"<p>"+c1_t_total+ "</p>");
						$("#total_t2").html("<p>"+city2+":</p> "+"<p>"+c2_t_total+ "</p>");

						if (senario_name == "Language" || senario_name=="Terminal") {
							$("#main_chart")
									.html(
											'<div id="char1" style="width:900px; height:350px"></div><div id="char2" style="width:900px; height:350px"></div>');
							var rows1 = [];
							for ( var i = 0; i < c1.KeyValue.length; i++) {
								var key = c1.KeyValue[i].Key instanceof Array ? c1.KeyValue[i].Key[0]
										: c1.KeyValue[i].Key;
								rows1.push([ key, c1.KeyValue[i].Value ]);
							}
							var header = [ 'language', 'speaker' ];
							var rawdata1 = [ header ].concat(rows1);

							var rows2 = [];
							for ( var i = 0; i < c2.KeyValue.length; i++) {
								var key = c2.KeyValue[i].Key instanceof Array ? c2.KeyValue[i].Key[0]
										: c2.KeyValue[i].Key;
								rows2.push([ key, c2.KeyValue[i].Value ]);
							}
							var rawdata2 = [ header ].concat(rows2);
							drawPieChart(rawdata1, rawdata2, city1, city2);

						}
						else if (senario_name=="User location")
						{
							$("#main_chart").html('<div id="canvas" class="gmap"></div>');
							var loci=c1.Geo.concat(c2.Geo);
							$("#topic_name").html("User locations of the last 1000 tweeters");
							init_map(loci);
						}
						else {
							var header = [ '', city1,city2];

							// get all keys from both city1 and city2.
							for ( var i = 0; i < c1.KeyValue.length; i++) {
								var key = c1.KeyValue[i].Key instanceof Array ? c1.KeyValue[i].Key[0]
										: c1.KeyValue[i].Key;
								col_first.push(key);
							}
							for ( var i = 0; i < c2.KeyValue.length; i++) {
								var key = c2.KeyValue[i].Key instanceof Array ? c2.KeyValue[i].Key[0]
										: c2.KeyValue[i].Key;
								col_first.push(key);
							}
							var col_head = arrayUnique(col_first);


							// get relative value for each keys to build the
							// data for chart.
							var rows = [];
							for ( var i = 0; i < col_head.length; i++) {
								var head = col_head[i];
								if (senario_name=="Popular sports"){
									head1=head.replace("Interested in","");
									head1=head1.replace("teams","");
								}
								else{
									head1=head;
								}

//								if(["Friends follower","User created year"].indexOf(senario_name)!=-1){
//									rows.push([ head1,
//												get_value_from_rows(head, c1.KeyValue),
//												get_value_from_rows(head, c2.KeyValue) ]);
//								}
//								else{
//									rows.push([ head1,
//												get_value_from_rows(head, c1.KeyValue),
//												get_value_from_rows(head, c2.KeyValue) ]);
//								}
								rows.push([ head1,
										get_value_from_rows(head, c1.KeyValue),
										get_value_from_rows(head, c2.KeyValue) ]);
							}

							var rawdata = [ header ].concat(rows);

							if(["World Cup","Transport satisfication"].indexOf(senario_name)!=-1){

								drawBarChart(rawdata,'main_chart',1,{colors : get_colors(2)},false);
							}
							else{
								drawBarChart(rawdata,'main_chart',1,{colors : get_colors(1)},true);
							}

							if(senario_name=="Eat habits"){
								$(".subsection").css("visibility","visible").css("display","inline");
								$("#subtitle").html("Top suburbs in consuming meat/veg")
								$("#sub_chart").html('<div id="sub_chart1"></div><div id="sub_chart2"></div><div id="sub_chart3"></div><div id="sub_chart4"></div>');
								var c1_sub=json.data[2];
								var c2_sub=json.data[3];
								var c1_ks=Object.keys(c1_sub.data);
								var c2_ks=Object.keys(c2_sub.data)
								var rdata_c1=[];
								var rdata_c2=[];

								for( var i=0;i<c1_ks.length;i++){
//									var subs=Object.keys(c1_sub.data[c1_ks[i]]));
									rdata_c1.push([[''].concat(Object.keys(c1_sub.data[c1_ks[i]])),[c1_ks[i]].concat(get_values_by_keys(c1_sub.data[c1_ks[i]],Object.keys(c1_sub.data[c1_ks[i]])))]);
									rdata_c2.push([[''].concat(Object.keys(c2_sub.data[c2_ks[i]])),[c2_ks[i]].concat(get_values_by_keys(c2_sub.data[c2_ks[i]],Object.keys(c2_sub.data[c2_ks[i]])))]);
								}
								drawBarChart(rdata_c1[0],"sub_chart1",0,{title:city1,legend:"none",colors:get_colors(5)});
								drawBarChart(rdata_c1[1],"sub_chart2",0,{title:city1,legend:"none",colors:get_colors(5)});
								drawBarChart(rdata_c2[0],"sub_chart3",0,{title:city2,legend:"none",colors:get_colors(5)});
								drawBarChart(rdata_c2[1],"sub_chart4",0,{title:city2,legend:"none",colors:get_colors(5)});

//								var rdata_c1_1=[[''].concat(Object.keys(c1_sub.data[c1_ks[0]])),[c1_ks[0]].concat(get_values_by_keys(c1_sub.data[c1_ks[0]],Object.keys(c1_sub.data[c1_ks[0]])))];
//								var rdata_c1_2=[[''].concat(Object.keys(c1_sub.data[c1_ks[0]])),[c1_ks[0]].concat(get_values_by_keys(c1_sub.data[c1_ks[0]],Object.keys(c1_sub.data[c1_ks[0]])))];

							}
							if(senario_name=="Life attitude"){
								$(".subsection").css("visibility","visible").css("display","inline");
								$("#subtitle").html("Happiest suburbs")
								$("#sub_chart").html('<div id="sub_chart1"></div><div id="sub_chart2"></div>');
								var c1_subs=Object.keys(json.data[2].data);
								var c2_subs=Object.keys(json.data[3].data);
								var rdata_c1=[[''].concat(c1_subs),[''].concat(get_values_by_keys(json.data[2].data,c1_subs))];
								var rdata_c2=[[''].concat(c2_subs),[''].concat(get_values_by_keys(json.data[3].data,c2_subs))];

								drawBarChart(rdata_c1,"sub_chart1",0,{title:city1,legend:"none",colors:get_colors(5)});
								drawBarChart(rdata_c2,"sub_chart2",0,{title:city2,legend:"none",colors:get_colors(5)});

							}
						}

					});
}

function get_colors(num){
	var cs=[];
	var r = Math.floor((Math.random() * 10) % colors.length);
	cs.push(colors[r]);
	for(var i=0;i<num-1;i++){
		var r2;
		do {
			r2 = Math.floor((Math.random() * 10) % colors.length);
		} while (cs.indexOf(colors[r2])!=-1);
		cs.push(colors[r2]);
	}
	return cs;
}

function get_values_by_keys(obj,keys){
	var vs=[];
	for (var i=0; i<keys.length;i++){
		vs.push(obj[keys[i]]);
	}
	return vs;
}

function init() {
	$(document).ready(select_senario($("a.sidebar_item").first().attr("id")));
}
$("a.sidebar_item").click(function(event) {
	event.preventDefault();
	event.stopPropagation();
	select_senario($(this).attr("id"));
	$(".subsection").css("visibility","hidden").css("display","none");
	$("#main_chart_1").remove();
});



$("#nav_teamates").click(function(e){
	$(".subsection").css("visibility","hidden").css("display","none");
	$("#main_chart").empty();	$("#main_chart_1").remove();
	$("li.active").removeClass("active");
	$("#topic_name").html("Teammates");

	$("#main_chart").html("<table>" +
			"<tr><td>Kangbo Mo</td></tr>" +
			"<tr><td>Yang Yu</td></tr>" +
			"<tr><td>Jie Jin</td></tr>" +
			"<tr><td>Qingyang Hong</td></tr>" +
			"<tr><td>Ma Xiao</td></tr>" +
			"<tr><td>Xiantian Luo</td></tr>" +
			"</table>");
});

$("#nav_help").click(function(e){
	$(".subsection").css("visibility","hidden").css("display","none");
	$("#main_chart").empty();	$("#main_chart_1").remove();
	$("li.active").removeClass("active");
	$("#topic_name").html("For help");

	$("#main_chart").html("Please contact <a href='mailto:kmo@student.unimelb.edu.au'>kmo@student.unimelb.edu.au</a> if you get any trouble with using this website");
});

$("#search_txt").keydown(function(e){
	if(e.keyCode==13){
		e.preventDefault();
		e.stopPropagation();
		var username=($("#search_txt").val());
		if(!username) return;
		$(".subsection").css("visibility","hidden").css("display","none");
		$("#main_chart").empty();	$("#main_chart_1").remove();
		$("li.active").removeClass("active");
		$("#topic_name").html("Recent Twitts of "+username);

		var qurl='tweets/username/'+encodeURI(username);
		$.ajax({
			type : 'GET',
			url : qurl,
		})
		.done(function(data) {
			var json_data=$.parseJSON(data);
			if(json_data.length==0 || json_data["error"] || json_data["errors"]){
				$("#main_chart").html("None twitts could be trieved for this user!");
				return;
			}
			var html_str="<table border='1'><tbody><tr class='color2'><td>Content</td><td>Created at</td></tr>";
			for(var i=0;i<json_data.length;i++){
				var color_class=i%2==0?"color1":"color3";
				var html_line="<tr class='"+color_class+"'><td><p>"+json_data[i].text+"</p></td><td><p>"+json_data[i].created_at+"</p></td></tr>";
				html_str+=html_line;
			}
			html_str+="</tbody></table>";

			$("#main_chart").html(html_str);
		});
	}
});

function init_map(data) {
	var mapOptions = {
		center : new google.maps.LatLng(-37.81548,144.953071),
		zoom : 8,
		mapTypeId : google.maps.MapTypeId.HYBRID
	};
	var map = new google.maps.Map(document.getElementById("canvas"), mapOptions);

	//MarkerCluster
	var markers = [];
    for (var i = 0; i < data.length; i++) {
      var dataPhoto = data[i];
      var latLng = new google.maps.LatLng(dataPhoto.Latitude,
          dataPhoto.Longitude);
      var marker = new google.maps.Marker({
        position: latLng
      });
      markers.push(marker);
    }
    var markerCluster = new MarkerClusterer(map, markers);
}