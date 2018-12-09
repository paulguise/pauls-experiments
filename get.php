<?php

require ('engine.php');
#print grab_value("select concat_ws(' ',team_type,role) as value from players where player=1");


#all this page does is get data of every damn type. 

switch ($_REQUEST['a']) {
	case 'getteams':
		#grab a list of all the teams that are currently active.
		$connect = dbconnect();
		$teams = array();
		$q= "select * from team_types where active=1";
		$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
		while ($r = mysql_fetch_object($r_q)){
			$row = array();
			$row['value'] = $r->team_type;
			$row['text'] = $r->label.' - '. $r->species;
			$teams[] = $row;
		}
		echo json_encode($teams);
		break;
	
	case 'getexistingteams':
		$connect = dbconnect();
		$teams = array();
		$q= "select * from teams where user=".$_COOKIE['dbrcid'];
		$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
		while ($r = mysql_fetch_object($r_q)){
			$row = array();
			$row['value'] = $r->team;
			$row['text'] = $r->team_name;
			$teams[] = $row;
		}
		echo json_encode($teams);
		break;

	case 'getteamplayer':
		#grab a list of all the teams that are currently active.
		$connect = dbconnect();
		$players = array();
		$q= "select * from players where active=1 and team_type=".$_REQUEST['t'];
		$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
		while ($r = mysql_fetch_object($r_q)){
			$row = array();
			$row['value'] = $r->player;
			$row['text'] = player_pos($r->position) .' - '. $r->cost.'mc';
			$players[] = $row;
		}
		echo json_encode($players);
		break;

	case 'getability':
		#grab a list of all abilities available to a certain position
		$connect = dbconnect();
		$skills = array();
		$q= "select * from skills"; #where find_in_set('".$_REQUEST['p']."',available_to)
		$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
		while ($r = mysql_fetch_object($r_q)){
			$row = array();
			$row['id'] = $r->skill;
			$row['text'] = $r->name;
			$skills[] = $row;
		}
		echo json_encode($skills);
		break;

	case 'getteamsheet':
		echo filled_out_team_sheet($_REQUEST['selected_team']);
		break;

	case 'oldteam':
		echo filled_out_team_sheet($_REQUEST['team']);
		break;

	case 'getnewplayerrow':
		$newplayerdatarow = new_player_input_row_blank($_REQUEST['team_id'],$_REQUEST['value'],$_REQUEST['player_row_id']);
		echo json_encode($newplayerdatarow);
		break;

	case 'getteamcost':
		$teamcost = get_roster_total($_REQUEST['team_id']);
		echo $teamcost;
		break;

	case 'getrandomname':
		$newname = random_team_name_generator();
		echo $newname;
		break;

	case 'getplayerrankdata':
		$newdata = player_rank_json($_REQUEST['teamid'],$_REQUEST['playerid']);
		echo json_encode($newdata);
		break;

	case 'getplayerstat':
		$newdata = get_player_stats($_REQUEST['player']);
		echo json_encode($newdata);
		break;

	case 'getteamwltotals':
		$newdata = get_team_match_results_total($_REQUEST['teamid']);
		echo $newdata;
		break;

	case 'getteamstats':
		$newdata = get_team_statistics($_REQUEST['teamid']);
		echo $newdata;
		break;

	case 'getcoachcost':
		$newdata = team_coaches_cost($_REQUEST['team']);
		echo $newdata;
		break;

	default:
		$temp = 'ah ah ah, no funny business.';
		echo $temp;
		break;
}


?>