<!doctype html>  

<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>flot.tooltip plugin example page</title>
	<script src="../graphs/jquery-1.8.3.min.js"></script>
    <!--[if lte IE 8]><script src="../js/excanvas.min.js"></script><![endif]-->
	<script src="../js/jquery.flot.js"></script>
    <script src="../graphs/plugins/jquery.flot.pie.js"></script>
	<script src="../js/jquery.flot.tooltip.min.js"></script>
	<link href="../css_report_print.css" rel="stylesheet" type="text/css" media="print" />
    <link href="../css_report.css" rel="stylesheet" type="text/css" />
    <script language="javascript" type="text/javascript" src="../../scripts/datetimepicker.js"></script>
	<style type="text/css">
		body {font-family: sans-serif; font-size: 16px; margin: 50px; max-width: 800px;}
		h4, ul {margin: 0;}
		#flotTip 
		{
			padding: 3px 5px;
			background-color: #000;
			z-index: 100;
			color: #fff;
			box-shadow: 0 0 10px #555;
			opacity: .7;
			filter: alpha(opacity=70);
			border: 2px solid #fff;
			-webkit-border-radius: 4px;
			-moz-border-radius: 4px;
			border-radius: 4px;
		}
	</style>
</head>

<body>
    <h1>flot.tooltip plugin example page</h1>

    <div id="placeholder" style="width: 500px; height: 400px;"></div>

	<script>
	$(function () {

		var data = [
			{ label: "Series 0", data: 1 },
			{ label: "Series 1", data: 3 },
			{ label: "Series 2", data: 9 },
			{ label: "Series 3", data: 20 }
		];
		
		var plotObj = $.plot($("#placeholder"), data, {
			series: {
				pie: {
					show: true
				}
			},
			grid: {
				hoverable: true 
			},
			tooltip: true,
			tooltipOpts: {
				content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
				shifts: {
					x: 20,
					y: 0
				},
				defaultTheme: false
			}
		});
		
	});
	</script>

</body>
</html>
