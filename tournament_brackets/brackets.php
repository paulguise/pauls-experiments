<?php
$DBHOST = "localhost"; //mysql host name
$DBUSER = "username"; //database username
$DBPASS = "password"; //database password
$DBNAME = "bracket_db"; //database name

//Connect to mysql
mysql_connect($DBHOST, $DBUSER, $DBPASS) or die(mysql_error());
//Connect to database
mysql_select_db($DBNAME) or die(mysql_error());
?>

<html>
<head>
<title>jQuery Tournament Brackets</title>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.bracket.js"></script>
<script type="text/javascript" src="js/jquery.json-2.3.min.js"></script>

<?php 
if($_GET['tid'])
{
  $q = "SELECT * FROM lan_brackets WHERE tid = " . $_GET['tid'];
  $r = mysql_query($q) or die(mysql_error());
  $data = mysql_fetch_assoc($r);
  $json = $data['json'];
  if(!empty($json))
    echo '<script type="text/javascript">var autoCompleteData = '.$json.'</script>';
  else
    echo '<script type="text/javascript">var autoCompleteData = {
    teams : [["Devon", ""],["", ""]], results : []}</script>';
}
else
    echo '<script type="text/javascript">var autoCompleteData = {
    teams : [["Devon", ""],["", ""]], results : []}</script>';



if($_GET['secretMode'] == "inlanadminmode")
{ ?>
<script type="text/javascript" src="js/brackets.js"></script>
<?php }
else
{ ?>
<script type="text/javascript" src="js/brackets-rd.js"></script>
<?php } ?>




<link rel="stylesheet" type="text/css" href="css/jquery.bracket.css" />
</head>
<body>

<?php
if($_GET['secretMode'] == "inlanadminmode")
{
$q = "SELECT * FROM lan_tournaments";
$r = mysql_query($q) or die(mysql_error());
while($data = mysql_fetch_assoc($r))
{
  echo '<a href="brackets.php?secretMode=inlanadminmode&tid='.$data['id'].'">'.$data['name'].'</a><br />';
}
}
?>


<div id="autoComplete"></div>
<?php

if($_POST['data'] && $_GET['tid'] != 0 && $_GET['secretMode'] == "inlanadminmode")
{
  $tid = $_GET['tid'];
  $json = $_POST['data'];
  
  $q = "SELECT * FROM lan_brackets WHERE tid = " . $tid;
  $r = mysql_query($q) or die(mysql_error());
  
  if(mysql_num_rows($r) == 0)
    $q = "INSERT INTO lan_brackets (tid, json)
          VALUES ('".$tid."', '".$json."')";
  else
    $q = "UPDATE lan_brackets SET json = '".$json."' WHERE tid = " . $tid;
    
  $r = mysql_query($q) or die(mysql_error());
}

?>

</body>
</html>