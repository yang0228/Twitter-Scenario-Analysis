<?php
include_once 'conf.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<!--     <link rel="shortcut icon" href="./ico/favicon.ico"> -->

<title>Global City Twittering Comparison for Cluster and Cloud Computing
	Assignment 2</title>

<!-- Bootstrap core CSS -->
<link href="./css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="./css/dashboard.css" rel="stylesheet">
<link href="./css/my.css" rel="stylesheet">

<!-- Just for debugging purposes. Don't actually copy this line! -->
<!--[if lt IE 9]><script src="./assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span> <span
						class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Global City Twittering Comparison</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-right">
					<li><a id="nav_report" href="report.pdf">Report</a></li>
					<li><a id="nav_teamates" href="#">Teammates</a></li>
					<li><a id="nav_help" href="#">Help</a></li>
				</ul>
				<form class="navbar-form navbar-right" id="search_from" method="get">
					<input type="text" class="form-control" id="search_txt"
						placeholder="Search user...">
				</form>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 col-md-2 sidebar">
				<h4>Scenarios:</h4>
				<ul class="nav nav-sidebar">
            <?php foreach ($side_navbar_items as $key=>$val){ ?>
            <li><a href="" class="sidebar_item" id="<?php echo $key;?>"><?php echo $key;?></a></li>
            <?php };?>
          </ul>
				<div><h5>Total Tweets collected:</h5>
					<div id="total_t1"></div>
					<div id="total_t2"></div>
				</div>
			</div>

			<!-- main content area -->
			<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
				<h1 class="" id="topic_name">Topic</h1>
				<div id="main_chart" class="main_chart_view"></div>


				<!-- subsection, up to 2 subsections -->
				<div class="subsection">

					<h2 class="sub-header" id="subtitle">Subsection title</h2>
					<div class="sub_chart_view" id="sub_chart"></div>

				</div>
				<!-- subsection end -->
			</div>
			<!-- main content area end-->
		</div>
	</div>

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script
		src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="./js/bootstrap.min.js"></script>
	<script src="./js/docs.min.js"></script>

	<script type="text/javascript" src="./js/markerclusterer.js"></script>
	<!-- my.js include all the google charts code for visualization-->
	<script src="./js/my.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(init);
    </script>

	<script type="text/javascript"
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCkAx_b1g3lX8c9VKIEQP9DDAFXx_H0hZk&sensor=false&language=en"></script>
</body>
</html>
