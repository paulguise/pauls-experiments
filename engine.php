<?php

/*


	Main Functions for the Dreadball(tm) roster creator. Copyright Paul Guise (the creator only that is) 2013.



	Basic functions only for now. Connecting to DB, getting single or multiple values, basic housekeeping and ease of use stuff. 


*/
/*
***************************************************************************
*********** Database Interaction Specific Items ***************************
***************************************************************************
*/
function dbconnect(){
	global $dbconnect;
	if(!$dbconnect){
		#/*
		//live info
		$host 		= 'pau1022303121211.db.6506973.hostedresource.com';
		$db 		= 'pau1022303121211';
		$user 		= 'pau1022303121211';
		$password	= 'f066c366fcbc';
		#*/
		
		//local info
		/*
		$host 		= 'localhost';
		$db 		= 'dreadball';
		$user 		= 'root';
		$password	= 'root';
		#*/
		
		$connection = mysql_connect($host, $user, $password) or die("Can not connect to database");
		$selected = mysql_select_db($db,$connection) or die("Could not load the desired db");
		return $dbconnect;
	}
} 

function print_db_error($q,$f){
	$temp = '<br /><br />
		ENGINE MALFUNCTION : '.$f.'<br /><br />
		SQL: '.$q;
	return $temp;
}

function grab_value($q){
	$thisconnection = dbconnect();
	$r = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $row = mysql_fetch_assoc($r) ) {
		foreach($row as $key=>$value){
			return $value;
		};
	};
};

function push_value($q){;
	$thisconnection = dbconnect();
	$r = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	$temp = mysql_insert_id();
	return $temp;
};

function create_facebook_account($first_name,$email_address,$fbid){
	//we will recieve a first name, email, and id value. 

	//first, check to see if there is an id already for this person
	$check_fbid = grab_value("select user from users where facebook='".$fbid."'");
	if($check_fbid<>''){
		#they have an account, so log them in
    	setcookie('dbrcid', $check_fbid, time()+60*60*24*30, '/');
	}else{
		#they are a new user, so log them in after creating their account
		//insert their new record
		if(!$email_address){
			$email_address = 'you@email.com';
		}
		$newuser = push_value("insert into users (name,email,facebook) values('".$first_name."','".$email_address."','".$fbid."')");
    	setcookie('dbrcid', $newuser, time()+60*60*24*30, '/');
	}
	//plant a cookie for their new user
	//return the record ID or true, whichever ends up being easier
	return true;
}

function update_facebook_account($fbid){
	//we will recieve a first name, email, and id value. 
	//first, check to see if there is an id already for this person
	if(isset($_COOKIE['dbrcid'])){
		$check_fbid = grab_value("select facebook from users where user='".$_COOKIE['dbrcid']."'");
		if(!$check_fbid){
			$updateuser = push_value("insert into users (facebook) values('".$fbid."')");
		}
		return true;
	}else{
		return 'Error: adding your facebook id failed. Use the contact form to complain to Paul.';
	}
	//return the record ID or true, whichever ends up being easier
}
/*
***************************************************************************
*********** Team/Player Creation ******************************************
***************************************************************************
*/


function team_types_select(){
	$temp = '';
	$connect = dbconnect();
	$q= "select team_type,label,species from team_types where active=1";
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	while ($r = mysql_fetch_object($r_q)){
		$temp .= '<option value="'.$r->team_type.'">'.$r->label.' - '.$r->species.'</option>'.PHP_EOL;
	}
	return $temp;
}

function team_coaches_cost($teamid){
	$connect = dbconnect();
	$q= "select coaches from teams where team = ".$teamid;
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	while ($r = mysql_fetch_object($r_q)){
		#check the coaches
		if($r->coaches && $r->coaches<>''){
			$coach_array = explode(',',$r->coaches);
			$coach_cost = (count($coach_array)*8) . 'mc';
		}
	}
	return $coach_cost;
}

function filled_out_team_sheet($team){
	$temp = 'team'.$team;
	$thisconnection = dbconnect();
	$q = "select * from teams where team=".$team;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

	while( $r = mysql_fetch_object($r_q) ) {

		$temp = '<div class="row-fluid">
					<div class="span2 team-results-'.$r->team.'">'.get_team_match_results_total($r->team).'</div>
					<div class="span2">Dice: <a href="#" class="db-editable-field" id="teamdata_'.$r->team.'" data-type="number" data-placeholder="0"  data-name="dice" data-teamid="'.$r->team.'" data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Dice" data-value="'.$r->dice.'">'.$r->dice.'</a> @ 6mc each</div>
					<div class="span2">Cards: <a href="#" class="db-editable-field" id="teamdata_'.$r->team.'" data-type="number" data-placeholder="0"  data-name="cards" data-teamid="'.$r->team.'" data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Cards" data-value="'.$r->cards.'">'.$r->cards.'</a> @ 10mc each</div>
					<div class="span2">League Points: <a href="#" class="db-editable-field" data-type="number" data-placeholder="0"  data-name="league_points"  data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="League Points" data-value="'.$r->league_points.'">'.$r->league_points.'</a></div>
					<div class="span2">Cash: <a href="#" class="db-editable-field" data-type="number" data-placeholder="0"  data-name="cash" data-teamid="'.$r->team.'" data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Extra Cash" data-value="'.$r->cash.'">'.$r->cash.'</a>mc</div>
					<div class="span2"><strong class="pull-right" id="roster_cost_'.$r->team.'">Roster Total: '.get_roster_total($r->team).'mc</strong></div>
	            </div>';
	            
		$temp .= ' <div class="row-fluid">
              <div class="span12">
                <table class="table table-bordered table-condensed table-striped"> 
                	<tr>
						<th>&nbsp;</th>
						<th>#</th>
						<th>Name</th>
						<th>Role</th>
						<th>Ex.</th>
						<th>Rank</th>
						<th>Move</th>
						<th>Strength</th>
						<th>Speed</th>
						<th>Skill</th>
						<th>Armor</th>
						<th>Abilities</th>
						<th><nobr>Special Rules</th>
						<th>Cost</th>
					</tr>';
		
		$skills_full = stat_affecting_skills_arrays();
		#$skills_finder = stat_affecting_skills();
		#print var_dump($skills_full).'<br><br><br>';

		for($i=1; $i<=14; $i++){
			$current = $r->{'player_'.$i};
			if(!empty($current)){
				#team member present
				$temp .= player_input_row_filled($current,$skills_full);
			}else{
				#no team member, so blank line
				$temp .= player_input_row($r->team_type,'',$i,$team,true);
			}
		}

		#check the coaches
		if($r->coaches && $r->coaches<>''){
			$coach_array = explode(',',$r->coaches);
			$coach_cost = (count($coach_array)*8) . 'mc';
		}
		$temp .= '<tr>
                    <td></td><td>--</td><td>Support Staff</td><td colspan="10"><a id="coaches_'.$r->team.'" class="s2coaches" data-name="coaches" data-type="select2" data-pk="'.$r->team.'" data-value="'.$r->coaches.'" data-url="push.php?a=teamdata" data-title="coaches"></a></td><td id="coaches_'.$r->team.'_cost">'.$coach_cost.'</td>
                  </tr>';

		$temp .= '</table>
				</div>
			</div>';

		$temp .= '
			<div class="row-fluid">
              <div class="span5 offset1 alert alert-warning">Motto: <a href="#" class="db-editable-field'.(empty($r->motto)?' editable-empty':'').'" data-type="textarea" data-rows="3" data-name="motto"  data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Motto" data-placeholder="Motto motto man..." data-value="'.$r->motto.'">'.(empty($r->motto)?'Empty':$r->motto).'</a></div>
              <div class="span5 alert alert-warning">Notes: <a href="#" class="db-editable-field'.(empty($r->notes)?' editable-empty':'').'" data-type="textarea" data-rows="3" data-name="notes"  data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Notes" data-placeholder="Notes" data-value="'.$r->notes.'">'.(empty($r->notes)?'Empty':$r->notes).'</a></div>
            </div>';
	}

	return $temp;
}	



function player_input_row($team_type,$position,$line_num='',$teamid='',$use_tr=false){
	$temp = '';
	if(isset($position) && $position<>''){
		
	}else{
		#its a blank player row
		if($use_tr==true){
			$temp = '<tr id="team'.$teamid.'_player'.$line_num.'" teamid="'.$teamid.'" playerline="'.$line_num.'" class="">'.PHP_EOL;
		}
		$temp .= '<td class=""></td><td class="">'.$line_num.'</td>
			<td colspan="12" class="span12"><a href="#" id="playername_'.$line_num.'" class="add-player editable-empty" data-type="select" data-pk="'.$line_num.'" data-value="" data-source="get.php?a=getteamplayer&t='.$team_type.'" title="Select Player">Add Player</a></td>
			'.PHP_EOL;
		if($use_tr==true){
			$temp .= '</tr>'.PHP_EOL;
		}	
			#<td class="span13" colspan="11"><a href="#" class="db-editable-field" id="playername_'.$line_num.'" data-type="text" data-pk="'.$line_num.'" data-url="" data-title="Enter Player Name">Add Player</a></td>
			
	}
	return $temp;
}


function player_input_row_filled($playerid,$abilities_array=''){
	$temp = '';
	$thisconnection = dbconnect();
	$q ='select * from team_members where team_member='.$playerid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		#experience defines rank and cost. for each rank, add 5 to the cost.
		#abilities affect stats, so here is an array of stat changing abilities
		$player_skills = explode(',',$r->abilities);
		for ($i = 0; $i < count($player_skills); ++$i) {
    		$target_array = searchForId($player_skills[$i], $abilities_array);
    		if($target_array!=''){
    			$t_stat = $abilities_array[$target_array]['stat'];
    			$t_modify = $abilities_array[$target_array]['modify'];        			
    			if(strpos($t_modify,'-')!==false || strpos($t_modify,'+')!==false){   
    				$oldval = $r->$t_stat;
    				$r->$t_stat = $oldval + $t_modify; #if modify contains a + or - then
    			}else{
    				$r->$t_stat = $t_modify; #else, just replace the stat
    			}
    		}
    	}
		$rank = player_rank($r->experience);
		$temp .= '<tr id="team'.$r->team.'_player'.$r->line_number.'" teamid="'.$r->team.'" playerline="'.$r->line_number.'" class="">
				<td class=""><a href="#deleteModal" data-toggle="modal" role="button" data-id="'.$r->team_member.'" data-playername="'.$r->name.'" id="killplayer_'.$r->team_member.'" class="killplayer icon-remove-sign"></a></td>
				<td class="">'.$r->line_number.'</td>
				<td class="">
					<a href="#" class="db-editable-field" id="playername_'.$r->team_member.'" data-type="text" data-placement="right" data-name="name"  data-pk="'.$r->team_member.'" data-url="push.php?a=playerdata" data-title="Enter Player Name" data-value="'.$r->name.'">'.$r->name.'</a>
					<!--<span class="pull-right"><a>Stats</a></span>-->
				</td>
				<td class="">'.player_pos($r->position).'</td>
				<td class=""><a href="#" class="db-editable-field" id="pexperience_'.$r->team_member.'" data-type="number" data-placeholder="0" data-placement="right" data-name="experience" data-teamid="'.$r->team.'" data-pk="'.$r->team_member.'" data-url="push.php?a=playerdata" data-title="Experience Points" data-value="'.$r->experience.'">'.$r->experience.'</a></td>
				<td class="player-rank">'.$rank.'</td>
				<td class="">'.$r->move.'</td>
				<td class="player-strength">'.$r->strength.'</td>
				<td class="player-speed">'.$r->speed.'</td>
				<td class="player-skill">'.$r->skill.'</td>
				<td class="player-armor">'.$r->armor.'</td>			
				<td class=""><a id="playerabilities_'.$r->team_member.'" class="s2multiple" data-name="abilities" data-placement="left" data-type="select2" data-teamid="'.$r->team.'" data-pk="'.$r->team_member.'" data-value="'.$r->abilities.'" data-url="push.php?a=playerdata" data-title="Select Ability"></a></td>
				<td class="">'.display_special_rules($r->special_rule).'</td>
				<td class=""><span class="player-cost">'.($r->cost + ($rank>1 ? ($rank-1)*5 : 0 )).'</span>mc</td>
			</tr>'.PHP_EOL;
	}

	return $temp;
}

function new_player_input_row_blank($team,$position_id,$player_row_id=''){
	#NEW IDEA: just have the table row guts and populate that into the row.
	$temp = '';
	$team_type = grab_value("select team_type from teams where team=".$team);
	#grab a blank record and insert it into the database for the type
	$newplayerid = new_player_db_record_row($team_type,grab_value('select position from players where player='.$position_id),str_replace('player_', '', $player_row_id),$team);
	$update_team_table = push_value("update teams set player_".str_replace('player_', '', $player_row_id)."=".$newplayerid." where team = ".$team);
	$thisconnection = dbconnect();
	$q ='select * from team_members where team_member='.$newplayerid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		#experience defines rank and cost. for each rank, add 5 to the cost.

		$temp .= '<td class=""><a href="#deleteModal" data-toggle="modal" role="button" data-id="'.$r->team_member.'" data-playername="'.$r->name.'" id="killplayer_'.$r->team_member.'" class="killplayer icon-remove-sign"></a></td>
			<td class="">'.$r->line_number.'</td>
			<td class="">
				<a href="#" class="db-editable-field" id="playername_'.$r->team_member.'" data-type="text" data-placement="right" data-name="name"  data-pk="'.$r->team_member.'" data-url="push.php?a=playerdata" data-title="Enter Player Name" data-value="'.$r->name.'">'.$r->name.'</a>
				<!--<span class="pull-right"><a>Stats</a></span>-->
			</td>
			<td class="">'.player_pos($r->position).'</td>
			<td class=""><a href="#" class="db-editable-field" id="pexperience_'.$r->team_member.'" data-type="number" data-placeholder="0" data-placement="right" data-name="experience" data-teamid="'.$r->team.'" data-pk="'.$r->team_member.'" data-url="push.php?a=playerdata" data-title="Experience Points" data-value="'.$r->experience.'">'.$r->experience.'</a></td>
			<td class="player-rank">1</td>
			<td class="">'.$r->move.'</td>
			<td class="player-strength">'.$r->strength.'</td>
			<td class="player-speed">'.$r->speed.'</td>
			<td class="player-skill">'.$r->skill.'</td>
			<td class="player-armor">'.$r->armor.'</td>			
			<td class=""><a id="playerabilities_'.$r->team_member.'" class="s2multiple" data-name="abilities" data-placement="left" data-type="select2" data-teamid="'.$r->team.'" data-pk="'.$r->team_member.'" data-value="'.$r->abilities.'" data-url="push.php?a=playerdata" data-title="Select Ability"></a></td>
			<td class="">'.display_special_rules($r->special_rule).'</td>
			<td class=""><span class="player-cost">'.($r->cost + ($rank>1 ? ($rank-1)*5 : 0 )).'</span>mc</td>
			'.PHP_EOL;
	}
	$newplayerdata = array(
		'newplayerid'=>$newplayerid,
		'newplayerrowdata'=> preg_replace("/\s+/", " ", $temp),
		'newplayerrowid'=>'player_'.$newplayerid,
		'oldplayerrowid'=> $player_row_id
		);
	return $newplayerdata;
}

function new_team($team_type,$team_name='Uncreative Team Name'){
	#create our new team
	$thisconnection = dbconnect();
	#add some players
	$temp='';
	$c=1;
	$q ='select * from team_types where team_type='.$team_type;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		#add in the team info
		$i_q ="insert into teams (team_name,team_type,user,dice,cards,cash,created,active,coaches) values ('".$team_name."',".$team_type.",".$_COOKIE['dbrcid'].",".$r->start_dice.",".$r->start_cards.",0,'".date('Y-m-d')."',1,'".$r->start_coach."')";
		$new_team_id = push_value($i_q);

		$player_q = "update teams set ";
		for($i=1; $i<=$r->start_guard; $i++){
			$temp = new_player_db_record_row($team_type,'g',$c,$new_team_id);
			$player_q .= "player_".$c."=".$temp.",";
			$c++;
		}
		for($i=1; $i<=$r->start_striker; $i++){
			$temp = new_player_db_record_row($team_type,'s',$c,$new_team_id);
			$player_q .= "player_".$c."=".$temp.",";
			$c++;
		}
		for($i=1; $i<=$r->start_jack; $i++){
			$temp = new_player_db_record_row($team_type,'j',$c,$new_team_id);
			$player_q .= "player_".$c."=".$temp.",";
			$c++;
		}
		for($i=1; $i<=$r->start_gh; $i++){
			$temp = new_player_db_record_row($team_type,'gh',$c,$new_team_id);
			$player_q .= "player_".$c."=".$temp.",";
			$c++;
		}
		for($i=1; $i<=$r->start_gs; $i++){
			$temp = new_player_db_record_row($team_type,'gs',$c,$new_team_id);
			$player_q .= "player_".$c."=".$temp.",";
			$c++;
		}

		$player_q .= "league_points=0 where team=".$new_team_id;
	};
	//add in all the players now
	$temp = push_value($player_q);
	//return $new_team_id;
	//instad of returning just the id, return the html for the nav and content
	$new_team_data = array('nav_data'=>render_new_team_tab_nav($new_team_id),'content_data'=>render_new_team_tab_content($new_team_id),'gototab'=>'pane'.$new_team_id);
	return json_encode($new_team_data);
}


function new_player_db_record_row($team_type,$position,$line_num='',$team_id){
	$temp = '';
	if(isset($position) && $position<>''){
		#its a player row
		$thisconnection = dbconnect();
		$q ='select * from players where team_type='.$team_type.' and position=\''.$position.'\'';
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
		while( $r = mysql_fetch_object($r_q) ) {
			$q_i= "insert into team_members (team,name,position,rank,experience,move,strength,speed,skill,armor,abilities,cost,active,line_number,special_rule) values (".$team_id.",'Player ".$line_num."','".$r->position."',1,0,".$r->move.",".$r->strength.",".$r->speed.",".$r->skill.",".$r->armor.",'".$r->abilities."',".$r->cost.",1,".$line_num.",'".$r->special_rule."')";
			$temp = push_value($q_i);
		}		
	}
	return $temp;
}
/*
***************************************************************************
*********** Team/Player Update ********************************************
***************************************************************************
*/

function record_team_result($team,$final_score,$match_result,$notes,$opponent){
	$u_q = "insert into results (team,final_score,match_result,notes,opponent) 
				values (".$team.",".$final_score.",'".$match_result."','".$notes."',".$opponent.")";
	$new_result = push_value($u_q);
	return $new_result;
}

function update_player_data($field_name,$field_value,$field_pk){
	#update a player data point
	if(empty($field_value)){
		$thevalue = '';
	}else{
		//test if its an array
		if(is_array($field_value)){
			$thevalue = implode(',', $field_value);
		}else{
			$thevalue=$field_value;
		}
	}
	$q = "update team_members set `".$field_name."` = '".$thevalue."' where team_member = ".$field_pk;

	$temp = push_value($q);
	return $temp;

}

function update_team_data($field_name,$field_value,$field_pk){
	#update a player data point
	if(empty($field_value)){
		$thevalue = '';
	}else{
		//test if its an array
		if(is_array($field_value)){
			$thevalue = implode(',', $field_value);
		}else{
			$thevalue=$field_value;
		}
	}
	$q = "update teams set `".$field_name."` = '".$thevalue."' where team = ".$field_pk;

	$temp = push_value($q);
	return $temp;

}

function player_rank_json($teamid,$playerid){
	$thisconnection = dbconnect();
	$p_q = "select rank,cost,team,line_number,experience from team_members where team_member=".$playerid;
	$p_r = mysql_query($p_q) or die(mysql_error() . print_db_error($p_q,__FUNCTION__));
	$player = mysql_fetch_object($p_r);

	$rank = player_rank($player->experience);
	$temp = array('team'=>$player->team,'line_number'=>$player->line_number,'rank'=>$rank,'cost'=>($player->cost + ($rank>1 ? ($rank-1)*5 : 0 )));
	return $temp;

}

/*
***************************************************************************
*********** Team/Player Removal *******************************************
***************************************************************************
*/
function retire_player($team_member,$action){
	#team members can be either retired (killed) or just remove (deleted). 
	#deleted players have their record deleted and their spot on the teams table set to 0
	#retired players have their spot on the teams table set to 0, and their active status goes to 0
	$temp='';
	#player data
	$thisconnection = dbconnect();
	$p_q = 'select * from team_members where team_member='.$team_member;
	$p_r = mysql_query($p_q) or die(mysql_error() . print_db_error($p_q,__FUNCTION__));
	$player = mysql_fetch_object($p_r);
	$teamtype = grab_value('select team_type from teams where player_'.$player->line_number.'='.$team_member);
	
	if($action=='retire'){
		$temp = push_value('update team_members set active=2 where team_member='.$team_member);
		$temp = push_value('update teams set player_'.$player->line_number.'=0 where team='.$player->team);
		$temp=1;
	}elseif($action=='delete'){
		#delete record
		$temp = push_value('update teams set player_'.$player->line_number.'=0 where team='.$player->team);
		$temp = push_value('delete from team_members where team_member='.$team_member);
		$temp=1;
	}elseif($action=='dead'){
		#dead, but keep for records
		$temp = push_value('update team_members set active=3 where team_member='.$team_member);
		$temp = push_value('update teams set player_'.$player->line_number.'=0 where team='.$player->team);
		$temp=1;
	}
	#since we have the line number, toss back a new select list based on this info to make it easier to deal with. Mostly with the line number
	if($temp==1){
		$temp = player_input_row($teamtype,'',$player->line_number,$player->team,false);
	}else{
		$temp = 'A slightly horrifying error has occurred. I have called for help and stopped the bleeding. Mostly. ';
	}
	$playerselectdata = array(
		'player_row'=>$player->line_number,
		'selectplayerrowdata'=> preg_replace("/\s+/", " ", $temp),
		'oldplayerrowid'=> 'player_'.$player->team_member,
		'teamid'=>$player->team
		);


	return $playerselectdata;
}

function retire_team($teamid,$action){
	#teams can be either retired(active=2) or just removed (deleted). 
	$temp='';
	if($action=='retire'){
		$temp = push_value('update team_members set active=2 where team='.$teamid);
		$temp = push_value('update teams set active=2 where team='.$teamid);
		$temp=1;
	}elseif($action=='delete'){
		#delete record
		$temp = push_value('delete from teams where team='.$teamid);
		$temp = push_value('delete from team_members where team='.$teamid);
		$temp=1;
	}
	
	return 'done';
}


/*
***************************************************************************
*********** Front Facing Display/Data Functions ***************************
***************************************************************************
*/

/*
SELECT results.*, COUNT(match_result)
FROM results
GROUP BY results.match_result,results.team

use this to get the total numbers of teams win/loss for each team



*/

function top_teams_display(){
	$temp = '';
	$connect = dbconnect();
	$q = 'select team_name, team, team as team2, motto,(select count(match_result) from results where match_result=\'w\' and team=team2) as total_wins from teams where active=1 order by total_wins desc limit 3';
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	$temp =  '<table class="table table-striped">';
	while( $r = mysql_fetch_object($r_q) ) {
	  $temp .= "<tr>";
	  #foreach ($r as $field){
	  #  $temp .= "<td>".stripslashes($field)."</td>";
	  #}
	  $temp .= '<td>'.$r->team_name.' 
	  '.(isset($r->motto) ? '<br><small>'.$r->motto.'</small>' : '').'</td>
	  <td>'.$r->total_wins.' wins</td>
	  <td><a href="/view/'.$r->team.'/" target="_blank">View Roster</a></td>';
	  $temp .= "</tr>";
	}
	$temp .= "</table>";

	return $temp;
}

function busy_teams_display(){
	$temp = '';
	$connect = dbconnect();
	$q = 'select team_name, team, team as team2, motto,(select count(match_result) from results where team=team2) as total_wins from teams where active=1 order by total_wins desc limit 3';
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	$temp =  '<table class="table table-striped">';
	while( $r = mysql_fetch_object($r_q) ) {
	  $temp .= "<tr>";
	  #foreach ($r as $field){
	  #  $temp .= "<td>".stripslashes($field)."</td>";
	  #}
	  $temp .= '<td>'.$r->team_name.' '.(isset($r->motto) ? '<br><small>'.$r->motto.'</small>' : '').'</td>
	  <td>'.$r->total_wins.' games played</td>
	  <td><a href="/view/'.$r->team.'/" target="_blank">View Roster</a></td>';
	  $temp .= "</tr>";
	}
	$temp .= "</table>";

	return $temp;
}

function update_user_info($field_name,$field_value,$field_pk){
	#update a player data point
	if(empty($field_value)){
		$thevalue = '';
	}else{
		//test if its an array
		if(is_array($field_value)){
			$thevalue = implode(',', $field_value);
		}else{
			$thevalue=$field_value;
		}
	}
	$q = "update users set `".$field_name."` = '".$thevalue."' where user = ".$field_pk;

	$temp = push_value($q);
	return $temp;

}

function get_roster_total($team){
	$temp_cost='';
	$thisconnection = dbconnect();
	$q = "select * from teams where team=".$team;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

	while( $r = mysql_fetch_object($r_q) ) {
		$team_cost = 0;
		$temp_cost += $r->dice*6;
		$temp_cost += $r->cards*10;
		$temp_cost += $r->cash;
		if(!empty($r->coaches)){
			$temp_cost += (substr_count($r->coaches,',')+1)*8;
		}
		#grab the player data
		$q2 = "select * from team_members where active=1 and team=".$team;
		$r_q2 = mysql_query($q2) or die(mysql_error() . print_db_error($q,__FUNCTION__));
		while( $r2 = mysql_fetch_object($r_q2) ) {
			$temp_cost += ((player_rank($r2->experience)-1)*5)+$r2->cost;
		}
	}
	return $temp_cost;
	
}


function render_skills_js_source(){
	$connect = dbconnect();
	$skills = array();
	$q= "select * from skills order by name asc";
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	while ($r = mysql_fetch_object($r_q)){
	  $row = array();
	  $row['id'] = $r->skill;
	  $row['text'] = $r->name;
	  $skills[] = $row;
	}
	return json_encode($skills);

}
function render_coaches_js_source(){
    $connect = dbconnect();
    $coaches = array();
    $q= "select * from players where team_type=0";
    $r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
    while ($r = mysql_fetch_object($r_q)){
      $row = array();
      $row['id'] = $r->player;
      $row['text'] = player_pos($r->position);
      $coaches[] = $row;
    }
    return json_encode($coaches);

}
function render_team_tabs_nav($userid){
	#example" <li><a href="#pane2" data-toggle="tab">My Rosters</a></li>
    $connect = dbconnect();
	$temp = '';
	$q ='select team,team_name from teams where active=1 and user='.$userid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		$temp .= '<li><a href="#pane'.$r->team.'" data-toggle="tab">'.$r->team_name.'</a></li>';
	}
	return $temp;
}

function render_team_tabs_content($userid){
	/*
	Example:
	 <div id="pane2" class="tab-pane">
	  <h4>Pane 2 Content</h4>
	    <p> and so on ...</p>
	 </div>
	  */
    $connect = dbconnect();
	$temp = '';
	$q ='select team,team_name,team_type from teams where active=1 and user='.$userid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		$temp .= '<div id="pane'.$r->team.'" class="tab-pane team-roster">

					<!-- name, actions, wins and losses -->
		            <div class="row-fluid">
						<div class="span4">
							<h3><a href="#" class="db-editable-field" id="teamdata_'.$r->team.'" data-mode="inline" data-type="text" data-name="team_name"  data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Team Name" data-value="'.$r->team_name.'">'.$r->team_name.'</a>
								<small>('.grab_value('select label from team_types where team_type='.$r->team_type).')</small>
							</h3>
						</div>

						<div class="span4">
							'.roster_actions_dropdown($r->team,$r->team_name).'
						</div>
						<div class="span4 alert alert-success hidden">
							system messages
						</div>
					</div>
					  
					'.filled_out_team_sheet($r->team).'
				</div>
	 ';
	 #<span class="team-results-'.$r->team.'">'.get_team_match_results_total($r->team).'</span>
	}
	return $temp;
}

function render_new_team_tab_nav($teamid){
    $connect = dbconnect();
	$temp = '';
	$q ='select team,team_name from teams where team='.$teamid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		$temp .= '<li><a href="#pane'.$r->team.'" data-toggle="tab">'.$r->team_name.'</a></li>';
	}
	return $temp;
}

function render_new_team_tab_content($teamid){
    $connect = dbconnect();
	$temp = '';
	$q ='select team,team_name,team_type from teams where team='.$teamid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		$temp .= '<div id="pane'.$r->team.'" class="tab-pane team-roster">
					  <h4><a href="#" class="db-editable-field" id="teamdata_'.$r->team.'" data-mode="inline" data-type="text" data-name="team_name"  data-pk="'.$r->team.'" data-url="push.php?a=teamdata" data-title="Team Name" data-value="'.$r->team_name.'">'.$r->team_name.'</a>
					  &nbsp;&nbsp;&nbsp;&nbsp;'.roster_actions_dropdown($r->team,$r->team_name).'
					  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="team-results-'.$r->team.'">'.get_team_match_results_total($r->team).'</span>
					  <br><small>('.grab_value('select label from team_types where team_type='.$r->team_type).')</small>
					  </h4>
					    <p>'.filled_out_team_sheet($r->team).'</p>
					 </div>
	 ';
	}
	return $temp;
}



/*
***************************************************************************
*********** Misc Helper Functions *****************************************
***************************************************************************
*/

function get_team_statistics($teamid=0){
	$connect = dbconnect();

    $total_games = grab_value('select count(result) from results where team='.$teamid);
    if ($total_games>0){
    	$total_wins = grab_value('select count(match_result) from results where match_result=\'w\' and team='.$teamid);
	    $temp = '<h4>'.grab_value('select team_name from teams where team='.$teamid).' stats</h4>';
	    $temp .= '<p><strong>Overall Win Percentage: '.sprintf("%.0f%%", ($total_wins/$total_games) * 100).'</strong></p><hr>';
	    $temp .= stats_team_matchup_ranks($teamid);
    }else{
    	$temp = '<h4>'.grab_value('select team_name from teams where team='.$teamid).' stats</h4>';
	    $temp .= '<p>There are no results data logged for this team.</p>';
    }
    $temp .= '<hr><p>For games against teams not listed above, there is no data. Adding more results will yield more accurate stats. Games with only one result have 
    				a higher percentage but are not as accurate a result as a match up with many results.</p>';
    $temp .= '<p>Chances for wins are never 100% or 0%, as there is always the slightest twist of fate that will totally screw you over whenever you bet on a sure thing.</p>';
    return $temp;
}

function stats_team_matchup_ranks($teamid){

	$connect = dbconnect();
	/*
    $q = "select results.*, 
	team_types.label,
	count(results.opponent) as team_wins,
	(select count(match_result) from results where match_result='w' and team = ".$teamid.") as total_wins
	FROM results INNER JOIN team_types ON  team_types.team_type = results.opponent
	WHERE results.match_result = 'w' AND results.team = ".$teamid."
	group by results.opponent
	ORDER BY team_wins DESC, results.final_score desc";
	)
	*/

	################################################################################
	###### BETTER WAY TO DO IT THAT IS ACTUALLY ACCURATE
	################################################################################


	$q = "select results.*, team_types.label,results.opponent as new_oppo,
	(select count(match_result) from results where match_result='w' and team = ".$teamid." and opponent = new_oppo) as total_wins,
	(select count(match_result) from results where match_result='l' and team = ".$teamid." and opponent = new_oppo) as total_losses
	FROM results  INNER JOIN team_types ON  team_types.team_type = results.opponent
	where results.team = ".$teamid." group by results.opponent order by total_wins desc, total_losses asc";

	/*
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
	$temp =  "<table>";
	while ($r = mysql_fetch_row($r_q)){
	  $temp .= "<tr>";
	  foreach ($r as $field){
	    $temp .= "<td>".stripslashes($field)."</td>";
	  }
	  $temp .= "</tr>";
	}
	$temp .= "</table>";

	result,team,final score,result,notes,new_oppo,label,opponent,total_wins,total_losses
	 */
	$temp = '';
	$r_q = mysql_query($q) or die (mysql_error().'<br> Query: '.$q);
    while ($r = mysql_fetch_object($r_q)){
    	$total_games_against_opp = $r->total_wins + $r->total_losses;
    	$win_chance = ($r->total_wins/$total_games_against_opp) * 100;
    	if($win_chance==100){
    		$win_chance=99;
    	}elseif($win_chance==0){
    		$win_chance=1;
    	}
    	if($temp <> ''){
    		#sub line
    		$temp .= '<li><strong>'.$r->label.' at a '.sprintf("%.0f%%", $win_chance).' chance</strong> based on '.$total_games_against_opp.' game results.</li>'.PHP_EOL;
    	}else{
    		#headline
    		$temp .= '<p>Matchup with the best chance to pull a win is against the <strong>'.$r->label.' with a '.sprintf("%.0f%%", $win_chance).' chance</strong> to win, based on '.$total_games_against_opp.' games.</p>'.PHP_EOL;
    		$temp .= '<p>Other matchups, in order of easiest to hardest, are as follows:<ul>'.PHP_EOL;
    	}
    }
    $temp .=' </ul></p>'.PHP_EOL;
   

    return $temp;

}

function get_team_match_results_total($teamid=0){
	$q_l = "select count(*) from results where team=".$teamid." and match_result='l'";
	$q_w = "select count(*) from results where team=".$teamid." and match_result='w'";
	$wins = grab_value($q_w);
	$losses = grab_value($q_l);
	$temp = 'Wins:'.$wins.' &nbsp;&nbsp;&nbsp; Losses:'.$losses;
	return $temp;
}

function roster_actions_dropdown($teamid,$teamname='',$printshort=''){
	$temp = '
		<div class="btn-group">
		  <a class="btn dropdown-toggle btn-info btn-mini" data-toggle="dropdown" href="#">
		    Roster Actions
		    <span class="caret"></span>
		  </a>
		  <ul class="dropdown-menu">
		    <li><a href="#recordResultModal" data-toggle="modal" role="button" data-id="'.$teamid.'" data-teamname="'.$teamname.'" 
		    		id="result_'.$teamid.'" class="record-result "><i class="icon-pencil"></i> Record Match Results</a></li>
		    <li><a href="#teamStatsModal" data-toggle="modal" role="button" data-id="'.$teamid.'" data-teamname="'.$teamname.'" 
		    		class="view-team-stats "><i class="icon-list-alt"></i> Review Team Stats</a></li>
		    <li class="divider"></li>
		    <li><a href="print.php?team='.md5($teamid).'" target="_blank" class=" "><i class="icon-print"></i> Print Full Roster</a></li>
		    <li><a href="print.php?team='.md5($teamid).'&printshort=1" target="_blank" class=" "><i class="icon-print"></i> Print Brief Roster</a></li>
		    <li><a href="javascript:fbShare(\''.$teamname.'\',\''.$teamid.'\');"><img width="14" src="/bootstrap/img/facebook_logo.png"> Share via Facebook</a></li>
		    <li class="divider"></li>
		    <li><a href="#deleteTeamModal" data-toggle="modal" role="button" data-id="'.$teamid.'" data-teamname="'.$teamname.'" 
		    		id="killteam_'.$teamid.'" class="killteam "><i class="icon-remove-sign"></i> Delete Team</a></li>
		  </ul>
		</div>
		';

	return $temp;
}

function send_message($source_message){
	if (isset($source_message)){
		#$message = mysqli_real_escape_string(trim($message);
		$headers = 'From: paul@paulsrants.com' . "\r\n" .'X-Mailer: PHP/' . phpversion();  
		$message = $source_message . PHP_EOL;
		$message .= 'From: '.grab_value("select concat_ws(' ',name,email) from users where user = " . $_COOKIE["dbrcid"]);
		mail('paulguise@gmail.com', 'Dreadball Team Manager Contact', $message, $headers);
	}
	return 'Message Sent!';
}

function random_team_name_generator(){
	$temp = '';

	$states = array('1'=>'Alabama','2'=>'Alaska','3'=>'Arizona','4'=>'Arkansas','5'=>'California','6'=>'Colorado','7'=>'Connecticut','8'=>'Delaware','10'=>'Florida','11'=>'Georgia','12'=>'Hawaii','13'=>'Idaho','14'=>'Illinois','15'=>'Indiana','16'=>'Iowa','17'=>'Kansas','18'=>'Kentucky','19'=>'Louisiana','20'=>'Maine','21'=>'Maryland','22'=>'Massachusetts','23'=>'Michigan','24'=>'Minnesota','25'=>'Mississippi','26'=>'Missouri','27'=>'Montana','28'=>'Nebraska','29'=>'Nevada','30'=>'New Hampshire','31'=>'New Jersey','32'=>'New Mexico','33'=>'New York','34'=>'North Carolina','35'=>'North Dakota','36'=>'Ohio','37'=>'Oklahoma','38'=>'Oregon','39'=>'Pennsylvania','40'=>'Rhode Island','41'=>'South Carolina','42'=>'South Dakota','43'=>'Tennessee','44'=>'Texas','45'=>'Utah','46'=>'Vermont','47'=>'Virginia','48'=>'Washington','49'=>'West Virginia','50'=>'Wisconsin','9'=>'Wyoming');
	$animals = array('1'=>'Meerkats','2'=>'Badgers','3'=>'Fluffy Bunnies','4'=>'Sloths','5'=>'River Otters','6'=>'Leopard Geckos','7'=>'Pandas','8'=>'Hedgehogs','9'=>'Flying Squirrels','10'=>'Semi-Wild Hares','11'=>'Beaver Tails','12'=>'Muntjacs','13'=>'Squirrel Monkeys','14'=>'Penguins','15'=>'Kangaroos','16'=>'Blobfishes','17'=>'Warthogs','18'=>'Elephant Seals','19'=>'Fruit Bats','20'=>'Batfish','21'=>'Hyenas','22'=>'Muskrats','23'=>'Wild Dogs','24'=>'Alligators','25'=>'Shrews','26'=>'Blue Jays','27'=>'Wombats','28'=>'Platypuses','29'=>'Yellow Bellied Sapsucker','30'=>'Ocelots'		);
	$st = rand (1,50);
	$an = rand (1,30);

	return $states[rand(1,50)].' '.$animals[rand (1,30)];
}

function player_rank($exp){

	$x = $exp;
	$r = 0;
	while($x >= $r) {
		#minus R from X
		$r++;
		$x -= $r;
	}
	return $r;
}

function player_pos($str){
	switch ($str) {
		case 'g':
			return 'Guard';
			break;
		
		case 's':
			return 'Striker';
			break;
		
		case 'j':
			return 'Jack';
			break;
		
		case 'oc':
			return 'Offensive Coach';
			break;
				
		case 'dc':
			return 'Defensive Coach';
			break;
				
		case 'sc':
			return 'Support Coach';
			break;
		
		case 'ch':
			return 'Cheerleader';
			break;
		
		case 'gi':
			return 'Giant';
			break;
		
		case 'gh':
			return 'Gaurd - Hard';
			break;

		case 'gs':
			return 'Gaurd - Sticky';
			break;

		default:
			return ' - ';
			break;
	}
}
/*
***************************************************************************
*********** Printing Specific Items ***************************************
***************************************************************************
*/

function printable_player_input_row($team_type,$position,$line_num='',$teamid='',$use_tr=false){
	$temp = '';
	if(isset($position) && $position<>''){
		
	}else{
		#its a blank player row
		if($use_tr==true){
			$temp = '<tr id="team'.$teamid.'_player'.$line_num.'" teamid="'.$teamid.'" playerline="'.$line_num.'" class="">'.PHP_EOL;
		}
		$temp .= '<td class="span1">'.$line_num.'</td>
			<td colspan="13" class="span12"></td>
			'.PHP_EOL;
		if($use_tr==true){
			$temp .= '</tr>'.PHP_EOL;
		}	
			#<td class="span13" colspan="11"><a href="#" class="db-editable-field" id="playername_'.$line_num.'" data-type="text" data-pk="'.$line_num.'" data-url="" data-title="Enter Player Name">Add Player</a></td>
			
	}
	return $temp;
}

function printable_player_input_row_filled($playerid){
	$temp = '';
	$abilities_array = stat_affecting_skills_arrays();
	$thisconnection = dbconnect();
	$q ='select * from team_members where team_member='.$playerid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
		#experience defines rank and cost. for each rank, add 5 to the cost.
		$player_skills = explode(',',$r->abilities);
		for ($i = 0; $i < count($player_skills); ++$i) {
    		$target_array = searchForId($player_skills[$i], $abilities_array);
    		if($target_array!=''){
    			$t_stat = $abilities_array[$target_array]['stat'];
    			$t_modify = $abilities_array[$target_array]['modify'];        			
    			if(strpos($t_modify,'-')!==false || strpos($t_modify,'+')!==false){   
    				$oldval = $r->$t_stat;
    				$r->$t_stat = $oldval + $t_modify; #if modify contains a + or - then
    			}else{
    				$r->$t_stat = $t_modify; #else, just replace the stat
    			}
    		}
    	}


		$rank = player_rank($r->experience);
		$temp .= '<tr id="team'.$r->team.'_player'.$r->line_number.'" teamid="'.$r->team.'" playerline="'.$r->line_number.'" class="">
			<td class="span1">'.$r->line_number.'
			</td>
			<td class="span3">'.$r->name.'</td>
			<td class="span1">'.player_pos($r->position).'</td>
			<td class="">'.$r->experience.'</td>
			<td class="">'.$rank.'</td>
			<td class="">'.$r->move.'</td>
			<td class="">'.$r->strength.'</td>
			<td class="">'.$r->speed.'</td>
			<td class="">'.$r->skill.'</td>
			<td class="">'.$r->armor.'</td>
			
			<td class="span6">'.display_abilities($r->abilities).'</td>

			<td class="">'.display_special_rules($r->special_rule).'</td>
			<td class="span1">'.($r->cost + ($rank>1 ? ($rank-1)*5 : 0 )).'</td>
			</tr>'.PHP_EOL;
	}

	return $temp;
}


function printable_team_sheet($team){
	if(isset($_REQUEST['printshort']) && $_REQUEST['printshort']==1){
		$printshort=true;
	}else{
		$printshort=false;
	}
	$temp = 'team'.$team;
	$thisconnection = dbconnect();
	$q = "select * from teams where team=".$team;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

	$temp = '<table class="table table-hover table-striped span11" style="">';
	while( $r = mysql_fetch_object($r_q) ) {
		//team name
		$temp .= '<tr>
					<td colspan=8><h4>'.$r->team_name.'&nbsp;<small>('.grab_value('select label from team_types where team_type='.$r->team_type).')</small></h4></td>
					<td colspan=3>Dice: '.$r->dice.' @ 6mc each</td>
					<td colspan=3>Cards: '.$r->cards.' @ 10mc each</td>
				</tr>';

		//team crest
		//notes
		#dice
		#cards
		#players
			$temp .= "<tr><td>#</td><td>Name</td><td>Role</td><td>Ex.</td><td>Rank</td><td>Move</td>
							<td>Strength</td><td>Speed</td><td>Skill</td><td>Armor</td>
							<td>Abilities</td><td><nobr>Special Rules</td><td>Cost</td></tr>";
		for($i=1; $i<=14; $i++){
			$current = $r->{'player_'.$i};
			if(!empty($current)){
				#team member present
				$temp .= printable_player_input_row_filled($current);
			}else{
				#no team member, so blank line
				if($printshort==false){
				$temp .= printable_player_input_row($r->team_type,'',$i,$team,true);
				}
			}
		}
		//team motto
		$temp .= '<tr>
					<td colspan=3>Support Staff @ 8mc:<br> '.display_support_staff($r->coaches).'</td>
					<td colspan=4>Motto:<br>'.$r->motto.'</td>
					<td colspan=3>Notes:<br>'.$r->notes.'</td>
					<td colspan=2><strong><span class="pull-right">Total Cost: </span></strong>League Points: '.$r->league_points.'<br>Cash: '.$r->cash.'</td>
					<td colspan=2 id="roster_cost_'.$r->team.'"><strong>'.get_roster_total($r->team).'mc</strong></td>
					</tr>';
		#cost
	}
	$temp .= '</table>';

	return $temp;
}

function display_special_rules($rules){
	$temp = '';
	if(isset($rules) && $rules<>''){
		$thisconnection = dbconnect();
		$q = "select * from special_rules where special_rule in(".$rules.")";
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

		while( $r = mysql_fetch_object($r_q) ) {
			if($temp<>''){
				$temp .=', ';
			}
			$temp .= '<nobr>'.$r->label;
		
		}
	}
	return $temp;
}

function display_abilities($skills){
	$temp = '';
	if(isset($skills) && $skills<>''){
		$thisconnection = dbconnect();
		$q = "select name from skills where skill in(".$skills.")";
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

		while( $r = mysql_fetch_object($r_q) ) {
			if($temp<>''){
				$temp .=', ';
			}
			$temp .= ''.$r->name;
		
		}
	}
	return $temp;
}

function display_support_staff($coaches){
	$temp = '';
	if(isset($coaches) && $coaches<>''){
		$thisconnection = dbconnect();
		$q = "select position from players where player in(".$coaches.")";
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

		while( $r = mysql_fetch_object($r_q) ) {
			if($temp<>''){
				$temp .=', ';
			}
			$temp .= ''.player_pos($r->position);
		}
	}
	return $temp;
}

function print_special_rules($special_rules){
	$temp = '';
	if(isset($special_rules) && $special_rules<>''){
		$thisconnection = dbconnect();
		$q = "select * from special_rules where special_rule in(".$special_rules.")";
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

		while( $r = mysql_fetch_object($r_q) ) {
			$temp .= '<p><b>'.$r->label.'</b>'.$r->description.'</p>';
		}
	}
}

function print_player_abilities($abilities){
	$temp = '';
	if(isset($abilities) && $abilities<>''){
		$thisconnection = dbconnect();
		$q = "select * from skills where skill in(".$abilities.")";
		$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));

		while( $r = mysql_fetch_object($r_q) ) {
			$temp .= '<p><b>'.$r->name.'</b>'.$r->description.'</p>';
		}
	}	
}

/*
***************************************************************************
*********** In the words of the mighty MCP, "END OF LINE" *****************
***************************************************************************
*/

function stat_affecting_skills_arrays(){
	$thisconnection = dbconnect();
	$skills_array = array();
	$row = array('skillid'=>'','stat'=>'','modify'=>'');
	$skills_array[] = $row;
	$q = "select * from skills where value_modifier IS NOT NULL and value_modifier > ''";
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {
	  $row = array('skillid'=>$r->skill,'stat'=>$r->affected_stat,'modify'=>$r->value_modifier);
	  $skills_array[] = $row;
	}
	return $skills_array;
}

function searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['skillid'] == $id) {
           #print 'found:'.$key.'<br>';
           return $key;
       }
   }
   return null;
}

function get_player_stats($playerid){
	$temp = '';
	$abilities_array = stat_affecting_skills_arrays();
	$thisconnection = dbconnect();
	$q = "select * from team_members where team_member = ".$playerid;
	$r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
	while( $r = mysql_fetch_object($r_q) ) {

		$player_skills = explode(',',$r->abilities);
		for ($i = 0; $i < count($player_skills); ++$i) {
    		$target_array = searchForId($player_skills[$i], $abilities_array);
    		if($target_array!=''){
    			$t_stat = $abilities_array[$target_array]['stat'];
    			$t_modify = $abilities_array[$target_array]['modify'];        			
    			if(strpos($t_modify,'-')!==false || strpos($t_modify,'+')!==false){   
    				$oldval = $r->$t_stat;
    				$r->$t_stat = $oldval + $t_modify; #if modify contains a + or - then
    			}else{
    				$r->$t_stat = $t_modify; #else, just replace the stat
    			}
    		}
    	}



		$temp = array('strength'=>$r->strength,'speed'=>$r->speed,'skill'=>$r->skill,'armor'=>$r->armor);
	}
	return $temp;
}




?>