<?php

require ('engine.php');
#print grab_value("select concat_ws(' ',team_type,position) as value from players where player=1");

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=grab_value('select team_name from teams where MD5(team) = \''.$_REQUEST['team'].'\'')?> - Dreadball Drafter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="//dreadball.paulsrants.com/bootstrap/js/bootstrap.js"></script>
    <link href="//dreadball.paulsrants.com/bootstrap/css/bootstrap.css" rel="stylesheet" media="all">
    <style type="text/css">
      body {
        padding-top: 0px;
        padding-bottom: 0px;
        margin:auto;
      }
      footer{display:block;}
		@media (max-width: 980px) {
		/* Enable use of floated navbar text */
		.navbar-text.pull-right {
		  float: none;
		  padding-left: 5px;
		  padding-right: 5px;
		}
		@media print {
		  a[href]:after {
		    content: none;
		  }
		  
		}
      }



    </style>
    <link href="//dreadball.paulsrants.com/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<!-- GA tracking -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-44284632-1', 'paulsrants.com');
  ga('send', 'pageview');

</script>
  </head>

  <body>
<?

	if(isset($_REQUEST['team'])){
		echo printable_team_sheet($_REQUEST['team']);
	}else{
		echo 'You dont have permission to be in the locker room. Get out.';
	}
?>
<div class="clearfix"></div>
        <p class="clearfix"><a href="/">Dreadball Team Manager</a> &copy; Paul Guise <?=date('Y')?> | <a href="http://www.manticgames.com/Shop-Home/DreadBall.html" target="_blank">Dreadball</a> &copy; <a href="http://www.manticgames.com/" target="_blank">Mantic Games</a> | 
        	<a href="/">Create your own Dreadball roster and more!</a></p>
</div>
  </body>
  </html>