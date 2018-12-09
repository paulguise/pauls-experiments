<?php

require ('engine.php');
#print grab_value("select concat_ws(' ',team_type,position) as value from players where player=1");

if(!isset($_COOKIE["dbrcid"])){
  #header("Location: index.php");
}else{
  #set up the user info to make things easier
  $thisconnection = dbconnect();
  $q = "select * from users where user = " . $_COOKIE["dbrcid"];
  $r_q = mysql_query($q) or die(mysql_error() . print_db_error($q,__FUNCTION__));
  $user = mysql_fetch_object($r_q);
  $useremail = $user->email;
  $username =$user->name;
  $userpass = $user->password;
  if($username==''){
    $username = $useremail;
  }
  if(!$user->facebook){
    $userlogout = '| <a class="navbar-link" href="logout.php">Logout</a>';
  }else{
    $userlogout = '';
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Dreadball Team Manager - Created by Paul Guise (Beta version 1.5)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">


    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen,print">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }

    </style>
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap-editable.js"></script>
    <link href="bootstrap/css/bootstrap-editable.css" rel="stylesheet">
    <script src="bootstrap/select2/select2.js"></script>
    <link href="bootstrap/select2/select2.css" rel="stylesheet">
    <link href="bootstrap/select2/select2-bootstrap.css" rel="stylesheet">


  </head>

  <body>


    <div class="container-fluid">

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <div class="brand">Dreadball Team Manager</div>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">
               <a class="navbar-link" href="#userInfoModal" data-toggle="modal" id="nav_username_display">Hi Person</a> | <a href="#">Logout</a>
            </p>
            <ul class="nav">
              <li><a href="#aboutModal" data-toggle="modal" role="button" >About</a></li>
              <li><a href="#contactModal" data-toggle="modal" role="button" >Contact</a></li>
              <li>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>


      <div class="tabbable">
        <ul class="nav nav-tabs" id="main_nav_tab_contain">
          <li><a href="#pane1" data-toggle="tab">Welcome</a></li>
          <li><a href="#pane2" data-toggle="tab">World Stats</a></li>
          <?#= render_team_tabs_nav($_COOKIE['dbrcid'])?>
          <li class="active"><a href="#pane201999" data-toggle="tab">New Team Look Tab</a></li>
          <li id="newteampanelink"><a href="#newteampane" data-toggle="tab">New Roster</a></li>
        </ul>

        <div class="tab-content">
          <div id="pane1" class="tab-pane">
            <div class="hero-unit">
              <h1>Greetings, Dreadballers!</h1><br>
              <p>
                This is the Dreadball Team Manager. The premise is simple: click on New Roster, select your team, give it a name (or let one be picked for you) and click 
                <button class="btn btn-success btn-small" >Start The Draft!</button> Your new roster is created and ready to go. Any value that is <a>underlined</a> can be edited and your input is
                immediately saved. When you're ready to ground your opponents mercilessly into the pitch, click <a class="btn btn-success btn-small">Print Roster</a> to print out a full
                page version of the roster.
              </p>
              <p>
                That's it. Create as many teams as you like, add as many players (up to 14 of course) as you want.
              </p></div>
              <p class="alert alert-info"><strong>UPDATE #4 - 2013-10-08 :</strong> You can now view team stats for your team. You can also see the top 3 winning-est teams featured on the World Stats tab. Player stats are still in development, but are coming along. More soon.</p>
              <p class="alert"><strong>UPDATE #3 - 2013-10-04 :</strong> Sharing via Facebook is enabled now and functioning, so feel free to spread the love around :). Individual player stats are nearly done, as are the
                team stats page view so you can see all the fun details about how your team does against everyone else. Stay tuned!</p>
              <p class="alert">UPDATE #2 - 2013-10-02 : Teams can now record match results. There is more to come on the team stats feature, which is why you are asked to enter more than just 
                  win or lose. Further refinements to overall system and more behind the scenes work done for the individual player stats. Also added a copyright notice. More soon!</p>
              <p class="alert">UPDATE #1 - 2013-09-30 : You now have the option to print either a full roster or a shorter one, sans empty rows. Also, all options related to teams are now in the 
                <button class="btn dropdown-toggle btn-info btn-small"> Roster Actions</button> menu, as well as some features that will soon be finished and available.</p>
              
              <p ><small>COPYRIGHT: Dreadball, the Dreadball name, logo, team names, player position titles, abilities labels, as well as anything else that is in the game 
                is Copyright &copy; <a href="http://www.manticgames.com/" target="_blank">Mantic Games</a>. This web application is free to use for anyone wanting to use it.</small></p>
          </div>
          <div id="pane2" class="tab-pane">
            <p>
            <h2>Top Winning Teams</h2>
            </p>
            <br class="clearfix" />
            <p>
            <h2>Busiest Teams</h2>
            </p>
          </div>

          <?#= render_team_tabs_content($_COOKIE['dbrcid'])?>
          <div id="pane201999" class="tab-pane team-roster active">

            <!-- name, actions, wins and losses -->
            <div class="row-fluid">
              <div class="span4">
                <h3>Team Name Here <small>(Midgard Delvers)</small></h3>
              </div>
              <div class="span4">
                <div class="btn-group">
                    <a class="btn dropdown-toggle btn-info btn-mini" data-toggle="dropdown" href="#">
                      Roster Actions
                      <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                      <li><a href="#recordResultModal" data-toggle="modal" role="button" data-id="201" data-teamname="Bowflex Squat Thrusts" 
                          id="result_201" class="record-result "><i class="icon-pencil"></i> Record Match Results</a></li>
                      <li><a href="#teamStatsModal" data-toggle="modal" role="button" data-id="201" data-teamname="Bowflex Squat Thrusts" 
                          class="view-team-stats "><i class="icon-list-alt"></i> Review Team Stats</a></li>
                      <li class="divider"></li>
                      <li><a href="print.php?team=757b505cfd34c64c85ca5b5690ee5293" target="_blank" class=" "><i class="icon-print"></i> Print Full Roster</a></li>
                      <li><a href="print.php?team=757b505cfd34c64c85ca5b5690ee5293&printshort=1" target="_blank" class=" "><i class="icon-print"></i> Print Brief Roster</a></li>
                      <li><a href="javascript:fbShare('Bowflex Squat Thrusts','201');"><img width="14" src="/bootstrap/img/facebook_logo.png"> Share via Facebook</a></li>
                      <li class="divider"></li>
                      <li><a href="#deleteTeamModal" data-toggle="modal" role="button" data-id="201" data-teamname="Bowflex Squat Thrusts" 
                          id="killteam_201" class="killteam "><i class="icon-remove-sign"></i> Delete Team</a></li>
                    </ul>
                  </div>
              </div>
              <div class="span4 alert alert-success hidden">system messages</div>
            </div>

            <!-- dice, cards, lp, cash -->
            <div class="row-fluid">
              <div class="span1">Wins: 3 </div>
              <div class="span1">Losses: 2 </div>
              <div class="span2">Dice: 3 @ 6mc each</div>
              <div class="span2">Cards: 2 @ 10mc each</div>
              <div class="span2">League Points: 3</div>
              <div class="span2">Cash: 11mc</div>
              <div class="span2"><strong class="pull-right">Roster Total: 145mc</strong></div>
            </div>

            <!-- player data table, including support staff -->
            <div class="row-fluid">
              <div class="span12">
                <table class="table table-bordered table-condensed table-striped">
                  <tr>
                    <th></th><th class="span1">#</th><th class="span3">Name</th><th>Role</th><th>Ex.</th><th>Rank</th><th>Move</th><th>Strength</th><th>Speed</th><th>Skill</th><th>Armour</th><th class="span4">Abilities</th><th class="span2">Special Rules</th><th>Cost</th>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>1</td><td><a>Ted Mosses</a><span class="pull-right"><a>Stats</a></span></td><td>Guard</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>4</td><td>Steady, Skill</td><td>[rules]</td><td>13mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>2</td><td><a>Ted Mosses</a><span class="pull-right"><a>Stats</a></td><td>Guard</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>4</td><td>Steady, Skill</td><td></td><td>13mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>3</td><td><a>Bob Dole</a><span class="pull-right"><a>Stats</a></td><td>Jack</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>4</td><td>Steady, Skill</td><td></td><td>9mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>4</td><td><a>Bob Dole</a><span class="pull-right"><a>Stats</a></td><td>Jack</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>4</td><td>Steady, Skill</td><td></td><td>9mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>5</td><td><a>Bob Dole</a><span class="pull-right"><a>Stats</a></td><td>Jack</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>4</td><td>Steady, Skill</td><td></td><td>9mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>6</td><td><a>Turner Hooch</a><span class="pull-right"><a>Stats</a></td><td>Striker</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>3</td><td>Steady, Skill</td><td></td><td>10mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>7</td><td><a>Turner Hooch</a><span class="pull-right"><a>Stats</a></td><td>Striker</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>3</td><td>Steady, Skill</td><td></td><td>10mc</td>
                  </tr>
                  <tr>
                    <td><a class="icon-remove-sign"></a></td><td>8</td><td><a>Turner Hooch</a><span class="pull-right"><a>Stats</a></td><td>Striker</td><td>2</td><td>2</td><td>4</td><td>4</td><td>4</td><td>4</td><td>3</td><td>Steady, Skill</td><td></td><td>10mc</td>
                  </tr>
                  <tr>
                    <td></td><td>9</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>
                  <tr>
                    <td></td><td>10</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>
                  <tr>
                    <td></td><td>11</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>
                  <tr>
                    <td></td><td>12</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>
                  <tr>
                    <td></td><td>13</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>
                  <tr>
                    <td></td><td>14</td><td><a>Add Player</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                  </tr>

                  <tr>
                    <td></td><td>--</td><td><a>Support Staff</a></td><td colspan="10">Offensive Coach, Defensive Coach, Cheerleader</td><td>16mc</td>
                  </tr>

                </table>
              </div>
            </div>

            <!-- support and info fields -->

            <div class="row-fluid">
              <div class="span5 offset1 alert alert-warning">Motto: </div>
              <div class="span5 alert alert-warning">Notes: </div>
            </div>
          </div>

          <div id="newteampane" class="tab-pane">
            <h4>New Roster</h4>
            <p>Enter the new team details below and start the draft.</p>
            <div class="form-inline ">
             <button class="btn btn-inverse" data-action="randomname" id="get_rand_name">Surprise Me</button>
             <input type="text" class="input span3" name="new_team_name" id="new_team_name" placeholder="New Team Name...">
              
                    <select id="team_type">

                    </select>
              <button class="btn btn-success" data-action="addteamroster" id="add_roster_btn" >Start The Draft!</button> <div id="loading_newteam" class="help-inline"></div>
            </div>
          </div>
        </div><!-- /.tab-content -->
      </div><!-- /.tabbable -->


<div id="recordResultModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">And the winner is...</h3>
  </div>
  <div class="modal-body">
    <p>Record your results below and submit.</p>
    <hr>
      <form name="result_form" id="result_form" class="form-contact" method="post">
        <select class="input-medium" name="match_result" id="match_result" required>
          <option value="">Result</option>
          <option value="w">Win</option>
          <option value="l">Loss</option>
        </select>
        <input class="input-small" name="final_score" id="final_score" max="7" min="0" type="number" placeholder="Final Score" required>
        <select class="input-medium" name="opponent_team" id="opponent_team" required>
          <option value="">Against Who</option>

        </select>
        <textarea rows="2" class="input-block-level" placeholder="Thoughts..." id="result_notes" name="result_notes"></textarea>
        <input type="hidden" name="action" value="recordresult">
        <input type="hidden" name="result_team_id" id="result_team_id" value="0">
        <button class="btn btn-primary" type="submit">Submit</button>&nbsp;<span class="result-form-messages inline"> </span>
        <p></p>
      </form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div id="teamStatsModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Who's on first?</h3>
  </div>
  <div class="modal-body">
    <img src="/bootstrap/img/loading.gif">
      
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div id="aboutModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">About DB Roster Creator</h3>
  </div>
  <div class="modal-body">
    <p>So I was erasing yet another dead player off of my Dreadball roster when I rubbed right through the paper. It was annoying since my players, stats, abilities, scores, etc. always keep changing. So I thought to myself, "I got to get a better team".</p>
    <p>Then I thought "why can't we have a roster creator online"? Searching turned up a couple of possibilities, and one that seems to be the default (an Excel spreadsheet). So, because I like re-inventing the wheel, I created this; the Dreadball Team Manager (working title)</p>
    <p>You can create your teams, add your players, abilities, and print it and go. Then when you win (I'm an optimist), you can come back, add the experience, choose abilities, and mark down your stats. You'll soon be able to compare your teams with others to see how you fair against other users and possibly schedule in a game. The possibilities are endless!</p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div id="contactModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Contact Paul</h3>
  </div>
  <div class="modal-body">
    <p>Got an error? Have an idea? Want to see something else on here?</p>
    <p>While our operators are NOT standing buy (I have a life you know) I will be happy to hear your thoughts about how to make the Dreadball Team Manager better. If you get an error or something isn't working, please send as much info as possible.</p>
    <p></p>
    <hr>
      <form name="contact_form" id="contact_form" class="form-contact" method="post">
        <textarea rows="6" class="input-block-level" placeholder="Whats this about?" id="contact_message"></textarea>
        <input type="hidden" name="action" value="sendmessage">
        <button class="btn btn-block btn-small btn-primary" type="submit">Send Message</button>
        <div class="error-messages"></div>
      </form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div id="userInfoModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Your info</h3>
  </div>
  <div class="modal-body">
      <h3 class="text-center">My name is <a href="#" class="user-editable-field" id="user_name" data-type="text" data-name="name" data-pk="" data-url="push.php?a=userdata" data-title="Your Name" data-value="<?=$username?>">Yourname</a><br>
      My email is email@email.com</a>
      <br>
      I use <a href="#" class="user-editable-field" id="user_pass" data-type="text" data-name="password" data-pk="" data-url="push.php?a=userdata" data-title="sshhhhh..." data-value="<?=$userpass?>">tester</a> for a password.
      </h3>
      <p>Yes the password is not hidden by *'s. Frankly, I hate that, which is why its not there. Besides, is there someone looking over your shoulder? (You totally just looked, didn't you? :)</p>
    <div class="user-info-form-errors"></div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Remove <span id="kill_player_name" name="kill_player_name"></span> from roster?</h3>
  </div>
  <div class="modal-body text-center">
    <div class="text-center;">
      <p>
          <button class="btn btn-inverse kill-confirm" data-action="dead" id="kill_player_dead">Hes Dead, Jim</button> 
          <button class="btn btn-success kill-confirm" data-action="retire" id="kill_player_retire">I'm too old for this s**t</button> 
          <button class="btn btn-danger kill-confirm" data-action="delete" id="kill_player_delete">Nobody likes you</button></p>
      <p> </p>
      <p> </p>
      <p>Dead, retired, and delete, respectively. <br>Delete removes player completely from system, so be sure.</p>
    <input type="hidden" name="kill_team_member" id="kill_team_member" value="0"></div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
  </div>
</div>

<div id="deleteTeamModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Remove <span id="kill_team_name" name="kill_team_name"></span> from manager?</h3>
  </div>
  <div class="modal-body text-center">
    <div class="text-center;">
      <p>
          <button class="btn btn-success kill-team-confirm" data-action="retire" id="kill_team_retire">So long and thanks for all the fish.</button> 
          <button class="btn btn-danger kill-team-confirm" data-action="delete" id="kill_team_delete">They're rubbish.</button></p>
      <p> </p>
      <p>Retire, and delete, respectively. </p>
      <p>Delete removes the team and all players, even retired ones. Be sure before you fire the whole team as they might take offense.</p>
    <input type="hidden" name="kill_team" id="kill_team" value="0"></div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
  </div>
</div>

<hr>
      <footer>
        <p>Dreadball Team Manager &copy; Paul Guise <?=date('Y')?> | <a href="http://www.manticgames.com/Shop-Home/DreadBall.html" target="_blank">Dreadball</a> &copy; <a href="http://www.manticgames.com/" target="_blank">Mantic Games</a></p>
      </footer>

    </div><!--/.fluid-container-->

  </body>
</html>
