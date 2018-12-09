<?php


require ('engine.php');

$thisconnection = dbconnect();

$r = mysql_query("delete from cust_shopping_cart where date_added <='2013-07-30 00:01:00'") or die(mysql_error() . print_db_error($q,__FUNCTION__));

#$r = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

print 'done';
?>