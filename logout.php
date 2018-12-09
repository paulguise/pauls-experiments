<?php

//thank you for playing. goodbye.

if(isset($_COOKIE['dbrcid']) && $_COOKIE['dbrcid']>0){
	setcookie("dbrcid", "", time()-3600);
 	header('Location: index.php');
}else{

 	header('Location: roster.php');
}

?>