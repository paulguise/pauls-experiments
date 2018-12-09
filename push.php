<?php

require ('engine.php');
#print grab_value("select concat_ws(' ',team_type,role) as value from players where player=1");

//here is where we put all the data.

switch ($_REQUEST['a']) {
	case 'newteam':
		#grab a list of all the teams that are currently active.
		echo new_team($_REQUEST['team_type'],$_REQUEST['team_name']);
		#echo json_encode($teams);
		break;

	case 'playerdata':
		#grab a list of all the teams that are currently active.
		echo update_player_data($_REQUEST['name'],$_REQUEST['value'],$_REQUEST['pk']);
		#echo json_encode($teams);
		break;

	case 'teamdata':
		#grab a list of all the teams that are currently active.
		echo update_team_data($_REQUEST['name'],$_REQUEST['value'],$_REQUEST['pk']);
		#echo json_encode($teams);
		break;

	case 'retire_player':
		#grab a list of all the teams that are currently active.
		echo json_encode(retire_player($_REQUEST['team_member'],$_REQUEST['action']));
		#echo json_encode($teams);
		break;

	case 'retire_team':
		#grab a list of all the teams that are currently active.
		echo retire_team($_REQUEST['teamid'],$_REQUEST['action']);
		#echo json_encode($teams);
		break;

	case 'sendmessage':
		#send an email to me
		echo send_message($_REQUEST['message']);
		break;

	case 'userdata':
		#send an email to me
		echo update_user_info($_REQUEST['name'],$_REQUEST['value'],$_REQUEST['pk']);
		break;


	case 'createfacebook':
		#send an email to me
		echo create_facebook_account($_REQUEST['first_name'],$_REQUEST['email'],$_REQUEST['id']);
		break;


	case 'updatefacebook':
		#send an email to me
		echo update_facebook_account($_REQUEST['id']);
		break;

	case 'recordresult':
		#record a win,loss, or draw
		echo record_team_result($_REQUEST['result_team_id'],$_REQUEST['final_score'],$_REQUEST['match_result'],$_REQUEST['result_notes'],$_REQUEST['opponent_team']);
		break;

	default:
		$temp = 'ah ah ah, no funny business.';
		echo $temp;
		break;
}


?>