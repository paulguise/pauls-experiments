<?php

require ('engine.php');
#print grab_value("select concat_ws(' ',team_type,position) as value from players where player=1");

if(!isset($_COOKIE["dbrcid"])){
  header("Location: index.php");
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
    <title>Dreadball Team Manager - Created by Paul Guise (Beta version 1.9)</title>
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
      body #teamStatsModal{
        width:70%;
        margin-left:-35%;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap-editable.js"></script>
    <link href="bootstrap/css/bootstrap-editable.css" rel="stylesheet">
    <script src="bootstrap/select2/select2.js"></script>
    <link href="bootstrap/select2/select2.css" rel="stylesheet">
    <link href="bootstrap/select2/select2-bootstrap.css" rel="stylesheet">

      <script type="text/javascript">
          $.fn.editable.defaults.mode = 'popup';
          $.fn.editableform.buttons = '<button type="submit" class="btn btn-primary editable-submit"><i class="icon-ok icon-white"></i></button>'+
                                '<button type="button" class="btn editable-cancel"><i class="icon-remove"></i></button>';
          //$.fn.editable.defaults.mode = 'inline';
          var team_id = '';

          //console.log('loaded');

          $(document).ready(function() {

            //lets fire up a new team shall we? 
            $(document).on("click","#add_roster_btn",function(){
              if($('#new_team_name').val()=='' || $('#team_type').val()==0){
                alert('You need to select a team type and give it a name.')
              }else{
                //create the roster in the backend, and pitch back the team ID val
                $('#loading_newteam').html('<img src="/bootstrap/img/loading.gif">');
                $.post('push.php',{a:'newteam',team_type:$('#team_type').val(),team_name:$('#new_team_name').val()},function(t_data) {
                  var teamObj = jQuery.parseJSON(t_data); //nav data and content data, all ready to go
                  $('#newteampanelink').before(teamObj.nav_data); //prepend the name of the team to the nav in front of the new roster tab
                  $('#newteampane').before(teamObj.content_data); //prepend the team roster before the new roster display
                  $('#new_team_name').val(''); //reset the value of the team name box
                  $('#main_nav_tab_contain a[href="#'+teamObj.gototab+'"]').tab('show'); //shift focus to the new teams' tab
                  returnAccess();
                  $('#loading_newteam').html('');
                });
              }
            });

            //get a randome team name
            $(document).on("click","#get_rand_name",function(){
              //console.log('getting team name...');
              $.post( "get.php?a=getrandomname", function( data ) {
                $('#new_team_name').val(data);
              });
            });


            //user info editing
            $('#userInfoModal').editable({
              selector:'a.user-editable-field',
              mode:'inline',
              success: function(response, newValue) {
                  $(this).html(newValue); //update the value in the field
                  if($(this).data('name') == 'name'){
                    $('#nav_username_display').html('Hi '+newValue)
                  }
              }
            });

            //start the editing
            $('.tab-content').editable({
              selector:'a.db-editable-field',
              success: function(response, newValue) {
                  $(this).html(newValue); //update the value in the field
                  var editedField = $(this).data('name');
                  var calcteamId = $(this).data('teamid');

                  if(editedField =='team_name'){
                    $('#main_nav_tab_contain a[href="#pane'+$(this).data('pk')+'"]').html(newValue); //shift focus to the new teams' tab
                  }
                  //console.log('editable field fired:'+calcteamId+':'+newValue);
                  //check if this is an experience, cards, dice, or cash
                  if(editedField=='experience' || editedField=='cash' || editedField=='cards' || editedField=='dice' || editedField=='coaches'){
                    //alert('triggered'+$(this).data('teamid'));
                    recalculate_team_cost($(this).data('teamid'));
                  };
                  if(editedField=='experience'){
                    //grab a json string of the new rank and cost for the player
                    //alert('experience rank changer tripped');
                    $.post('get.php',{a:'getplayerrankdata',teamid:$(this).data('teamid'),playerid:$(this).data('pk')},function(data) {
                      //grab the json and return it 
                      var obj = jQuery.parseJSON(data);
                      var player_row = '#team'+obj.team+'_player'+obj.line_number;
                      $(player_row+' .player-rank').html(obj.rank);
                      $(player_row+' .player-cost').html(obj.cost);
                    })
                  }
              }
            });

            //update the entire row with a new player field
            $('.tab-content').editable({
              selector:'.add-player',
              emptytext:'Add player',
              showbuttons: false,
              success: function(response, newValue) {
                var theteamid = $(this).parents().eq(1).attr("teamid");
                var theplayerline = $(this).parents().eq(1).attr("playerline");
                $.post('get.php',{a:'getnewplayerrow',value:newValue,team_id:theteamid,player_row_id:theplayerline},function(data) {
                  //grab the json and return it 
                  var obj = jQuery.parseJSON(data);
                  //console.log('1 data loaded:add player');
                  //add a new player row of data points
                  $('#team'+theteamid+'_player'+theplayerline).html(obj.newplayerrowdata);
                  //update the team costs
                  recalculate_team_cost(theteamid);
                  returnAccess();
                })
              }
            });

            $('.s2multiple').editable({
              source:<?=render_skills_js_source()?>,
              select2: {multiple: true, placeholder:'Select abilities'},
              emptytext:'No Abilities',
              emptyclass:'editable-empty',
              success: function(response, newValue) {
                  //console.log('ability altered');
                  //here is where we change the stats. only three stats can be changed so far: strength,speed,skill,armor
                  reload_player_data($(this).data('teamid'),$(this).data('pk'),$(this).parents().eq(1).attr("playerline"));
              }
            });

            $('.s2coaches').editable({
              source:<?=render_coaches_js_source()?>,
              select2: {multiple: true, placeholder:"Select coaches"},
              emptytext:'Add coach or cheerleader',
              emptyclass:'editable-empty',
              success: function(response, newValue) {
                  //console.log('coaches altered');
                  //console.log('coaches recalc fired:'+$(this).data('pk'));
                  recalculate_team_cost($(this).data('pk'));
                  get_coach_cost($(this).data('pk'));
              }
            });

            //someone is going to die, but are they really?
            $(document).on("click", ".killplayer", function () {
              //console.log('kill player fired');
              var teamMemberId = $(this).data('id');
              //console.log('teamMemberId='+teamMemberId);
              var teamMemberName = $(this).data('playername');
              //console.log('playername='+teamMemberName);
              $("#deleteModal .modal-body #kill_team_member").val( teamMemberId );
              //$("#deleteModal #kill_player_name").html( teamMemberName );
            });

            //yes, they are really going to go
            $('.kill-confirm').click(function() {
              //close the modal
              $('#deleteModal').modal('hide');
              var killAction = $(this).data('action');
              //console.log('killAction='+killAction);
              $.post('push.php',{a:'retire_player',action:killAction,team_member:$('#kill_team_member').val()},function(k_data){
                //console.log('player retired');
                var obj = jQuery.parseJSON(k_data);
                 //add a new player row of data points
                $('#team'+obj.teamid+'_player'+obj.player_row).html(obj.selectplayerrowdata);
                recalculate_team_cost(obj.teamid);
              });
            });

            //everyone, get out of here
            $(document).on("click", ".killteam", function () {
              //console.log('kill team fired');
              var teamId = $(this).data('id');
              //console.log('teamId='+teamMemberId);
              var teamName = $(this).data('teamname');
              //console.log('teamname='+teamMemberName);
              $("#deleteTeamModal .modal-body #kill_team").val( teamId );
              $("#deleteTeamModal #kill_team_name").html( teamName );
            });
            
            //yes, they ate pizza
            $('.kill-team-confirm').click(function() {
              //close the modal
              $('#deleteTeamModal').modal('hide');
              var killAction = $(this).data('action');
              var killTeamId = $('#kill_team').val();
              //console.log('killAction='+killAction);
              $.post('push.php',{a:'retire_team',action:killAction,teamid:killTeamId},function(k_data){
                //console.log('team retired');
                //var obj = jQuery.parseJSON(k_data);
                  //remove the tab and the content divs
                  $('#pane'+killTeamId).remove();
                  $('#main_nav_tab_contain li.active').remove();
                  //shift focus to new team pane
                  $('#main_nav_tab_contain a:last').tab('show');
              });
            });

            function recalculate_team_cost(teamid){
              $.post('get.php',{a:'getteamcost',team_id:teamid},function(data) {
                  //update the row data
                 $('#roster_cost_'+teamid).html('Roster Total: '+data+'mc');
                 for (var i = 0; i < 2; i++ ) {
                      $('#roster_cost_'+teamid)
                          .animate( { backgroundColor: "#eee" }, 250 )
                          .animate( { backgroundColor: "transparent" }, 250 );
                  }
              });
            }

            function reload_player_data(teamid,playerid,playerline){
              //submit the player id to the backend and get a json string back
              $.post('get.php',{a:'getplayerstat',player:playerid},function(data) {
                var obj = jQuery.parseJSON(data);

                $.each(obj,function(s,val){
                  //alert(s+' '+val);
                  //console.log(s+' = '+val);
                  //var affectedRow = '#team'+teamid+'_player'+playerline+' .player-'+s;
                  //console.log(affectedRow);
                  $('#team'+teamid+'_player'+playerline+' .player-'+s).html(val);
                  //$(affectedRow).animate( { backgroundColor: "#eee" }, 250 ).animate( { backgroundColor: "transparent" }, 250 );
                  });
                });
              }

            function get_coach_cost(teamid){
              $.post('get.php',{a:'getcoachcost',team:teamid},function(data) {
                  $('#coaches_'+teamid+'_cost').html(data);
                });
            }

            function returnAccess(){
              // remove existing listeners and rebind listeners
              $('.s2multiple').editable({
                source:<?=render_skills_js_source()?>,
                select2: {multiple: true, placeholder:'Select abilities'},
                emptytext:'No Abilities',
                emptyclass:'editable-empty',
                success: function(response, newValue) {
                  //console.log('ability altered');
                  reload_player_data($(this).data('teamid'),$(this).data('pk'),$(this).parents().eq(1).attr("playerline"));
                }
              });

              $('.s2coaches').editable({
                source:<?=render_coaches_js_source()?>,
                select2: {multiple: true, placeholder:"Select coaches"},
                emptytext:'Add coach or cheerleader',
                emptyclass:'editable-empty',
                success: function(response, newValue) {
                    //console.log('coaches altered');
                    //console.log('coaches recalc fired:'+$(this).data('pk'));
                    recalculate_team_cost($(this).data('pk'));
                    get_coach_cost($(this).data('pk'));
                }
              });
            }// end returnAccessFunction


            $('#contact_form').submit(function() {
              //alert('submitted');
              var dataString = 'message=' + $("#contact_message").val();
              //$.post('push.php',{a:'sendmessage',message:$("#contact_message").val()},function(date){
              $.ajax({
                  type : "POST",
                  url : "push.php?a=sendmessage",
                  data : dataString,
                  cache : false,
                  success : function(data) {
                    //alert(data);
                    $('#contact_form .error-messages').html(data);
                    $('#contact_form #contact_message').val('');
                  }
              });
              return false;
            });

            $(document).on("click", ".record-result", function () {
              //console.log('kill team fired');
              var teamId = $(this).data('id');
              //console.log('teamId='+teamMemberId);
              var teamName = $(this).data('teamname');
              //console.log('teamname='+teamMemberName);
              $("#recordResultModal .modal-body #result_team_id").val( teamId );
              //$("#recordResultModal #team_name").html( teamName );
            });

            $('#result_form').on('submit',function(e) {
              e.preventDefault();
              $('.result-form-messages').html('').show();
              $.ajax({
                  type : "POST",
                  url : "push.php?a=recordresult",
                  data : $('#result_form').serialize(),
                  cache : false,
                  success : function(data) {
                    //alert(data);
                    var Rteamid=$('#result_team_id').val();
                    //update the background element
                    $.post('get.php',{a:'getteamwltotals',teamid:Rteamid},function(data) {
                      $('.team-results-'+Rteamid).html(data);
                    });

                    //$('#user_info_form').html(data);
                    $('.result-form-messages').html('Results have been recorded.')
                          .animate( { backgroundColor: "#eee" }, 250 )
                          .animate( { backgroundColor: "transparent" }, 250 )
                          .fadeOut(1500);
                    $('#result_form')[0].reset();
                  }
              });
              //return false;
            });

            $(document).on("click", ".view-team-stats", function () {
              //console.log('kill team fired');
              //show loading
              $('#teamStatsModal .modal-body').html('<img src="/bootstrap/img/loading.gif">');
              //var teamId = $(this).data('id');
              //console.log('teamId='+teamMemberId);
              var teamName = $(this).data('teamname');
              //console.log('teamname='+teamMemberName);
              //get the data for the team and populate the content div
              $.post('get.php',{a:'getteamstats',teamid:$(this).data('id')},function(t_data){
                //console.log('team data grabbed');
                //var obj = jQuery.parseJSON(t_data);
                //we should have a team name and a content html block
                $('#teamStatsModal .modal-body').html(t_data);
              });
            });

          }); //end jquery ready

      </script> 

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

<div id="fb-root"></div>

<script>

  var fbUsername = 'I';

  window.fbAsyncInit = function() {
    FB.init({
      appId      : '448225225294111', // App ID
      channelUrl : '//dreadball.paulsrants.com/channel.html', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });

    
  };

    function fbShare(tName,tLink){
      FB.ui(
      {
        method: 'feed',
        name: tName+' Dreadball Team',
        link: 'http://dreadball.paulsrants.com/view/'+tLink+'/',
        picture: 'http://dreadball.paulsrants.com/bootstrap/img/team-manager-logo-150.png',
        caption: 'View my Dreadball team',
        description: 'Created with the DB Team Manager @ http://dreadball.paulsrants.com.'
      },
      function(response) {
        if (response && response.post_id) {
          //alert('Your roster has been shared on your timeline.');
          $("#fbpostsuccess").slideDown().delay(3000).slideUp();

        } else {
          //alert('You cancelled, so nothing was published.');
          $("#fbpostfail").slideDown().delay(3000).slideUp();
        }
      });
    }

  // Load the SDK asynchronously
  (function(d){
   var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement('script'); js.id = id; js.async = true;
   js.src = "//connect.facebook.net/en_US/all.js";
   ref.parentNode.insertBefore(js, ref);
  }(document));

  // Here we run a very simple test of the Graph API after login is successful. 
  // This testAPI() function is only called in those cases. 
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Good to see you, ' + response.name + '.');
      var fbUsername = response.name;
      //if they created an account, but then signed up via facebook, add that fbid in there
      /*
      $.post('push.php',{a:'updatefacebook',id:response.id},function(data) {
        if(data==true){
          //console.log('facebook login successful:'+data)
        }else{
          //console.log('facebook login failed:'+data)
        }
      });
      */
    });
  }
</script>

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
               <a class="navbar-link" href="#userInfoModal" data-toggle="modal" id="nav_username_display">Hi <?=$username?></a> <?=$userlogout?>
            </p>
            <ul class="nav">
              <li><a href="#aboutModal" data-toggle="modal" role="button" >About</a></li>
              <li><a href="#contactModal" data-toggle="modal" role="button" >Contact</a></li>
              <li>
                <div class="alert alert-error fade in hide" id="fbpostfail">You cancelled, so nothing was published.<a class="close" data-dismiss="alert" href="#">&times;</a></div>
                <div class="alert alert-success fade in hide" id="fbpostsuccess">Your rosters' been shared. Thanks for spreading the love..<a class="close" data-dismiss="alert" href="#">&times;</a></div>
              </li>
              <!-- <li><a href="#notesModal" data-toggle="modal" role="button" >[notes]</a></li>-->
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">

      <div class="tabbable">
        <ul class="nav nav-tabs" id="main_nav_tab_contain">
          <li class="active"><a href="#pane1" data-toggle="tab">Welcome</a></li>
          <li><a href="#pane2" data-toggle="tab">World Stats</a></li>
          <?= render_team_tabs_nav($_COOKIE['dbrcid'])?>
          <li id="newteampanelink"><a href="#newteampane" data-toggle="tab">New Roster</a></li>
        </ul>

        <div class="tab-content">
          <div id="pane1" class="tab-pane active">
            <!--<h4>The Markup</h4>
            <pre>Code here ...</pre>-->
            <div class="hero-unit">
              <h1>Helloooooooooo Dreadballers!</h1><br>
              <p>
                This is the Dreadball Team Manager. The premise is simple: click on New Roster, select your team, give it a name (or let one be picked for you) and click 
                <button class="btn btn-success btn-small" >Start The Draft!</button> Your new roster is created and ready to go. Any value that is <a>underlined</a> can be edited and your input is
                immediately saved. When you're ready to ground your opponents mercilessly into the pitch, click <a class="btn btn-success btn-small">Print Roster</a> to print out a full
                page version of the roster.
              </p>
              <p>
                That's it. Create as many teams as you like, add as many players (up to 14 of course) as you want.
              </p></div>
              <div class="alert alert-success"><strong>UPDATE #6 - 2014-05-03 :</strong> Comments, comments, comments! I need your comments on what you want to see here! A few of you have dropped me a line (thank you for that) with suggestions and tweaks you would like to see, but I need more. There are some things that I would like to add, but they may not nessecarily be the items you all want to see. So, here is what I am going to do
                Below is the list of items that I am going to be adding in over the next few weeks. Some are big, some are small, but all of them are things that I think should be in there. If you have an opinion on it either way, I encourage you to hit the <a href="#contactModal" data-toggle="modal" >Contact</a> button and tell me so. If you can include your email in that message that would be helpful as well since none of you seem to want to <a href="#userInfoModal" data-toggle="modal" >click the name in the top right corner</a>
                and enter your own email address (<a href="#userInfoModal" data-toggle="modal" >or click this link</a>, it will let you edit your info). Otherwise, if you are unclear about things I may not put them in if I don't understand them. 
                And so, to the list<br><br>
                <ul>
                  <li>Stats: List of teams you have faced and your win/loss record against that team. This is only for team types (i.e. Trontek 29ers), not individual teams (such as Bobs Bruisers).</li>
                  <li>Mobile Friendly. The site will work well enough on a mobile phone size screen, but it could stand to work better. Editability is a big part of this sites' function and it is difficult 
                      to get lots of numbers and buttons on a small screen. A more mobile friendly layout (specifically a mobile only layout) will kill off some functionality, but will still allow you to view and print your teams from a phone.</li>
                  <li>Player specific tracking: This has been requested, but the people wanting it didn't clarify what exactly they wanted. Initial thoughts are to track player kills and deaths, but working this into the rosters' flow isn't possible. Instead, it will be a modal window
                    where the user will be able to add these fun and/or horrifying statistics. An icon will appear next to the player name where you can view the kill/death record for that player. If any other items are desired to be put in
                    then please drop a line to me with your request.</li>
                  <li>Giants. I was going to add these monsters in but was not sure how popular they were. My gaming groups don't use them but that doesn't mean others don't. If you wanna see them let me know.</li>
                  <li>League! The league engine I was created didn't really preform as I wanted to. Or...at all really. But since it was the first one I created, the next one will be better. Features for that will be League name (obviously), players, their teams, WLD stats, random initial 
                    seeding and then swiss system for pairings, length of league determined by creator, and finally a download/print feature so you can rub your victories in your opponents face. The catch will be that all teams will need to be created with the Roster Creator in order to 
                    track properly. Teams can be added to the league chart without having to be in the system, they just won't benefit from stats tracking. Users will be able to see all the leagues made, and will have to request to join, to which the creator will approve/deny. I would have it go the other 
                    way where the creator can invite users, but so many of you have not set your email addresses that no one would get the invites.</li>
                </ul><br><br>
                If you have any other concerns, comments, additional features or wish to donate to my beer fund, click the <a href="#contactModal" data-toggle="modal" >Contact</a> link on the top bar. You'll be glad you did.
              </div>
              <p class="alert"><strong>UPDATE #5 - 2013-11-29 :</strong> Well, its been busy at the homestead and on the workfront, so updates have been slow. First, I would like to thank those that dropped me a line with suggestions and especially errors that cropped up. Thanks for your help and support. This update sees a minor facelift to the team sheets, as well as adding some more basics for the stats and league play. 
                Also, I encourage you (yes you, the one reading this right now) to drop a line and let me know if you have any suggestions, errors to gripe about, or whatever else you think could be useful here. The contact form above will get your message to me. Cheers mates!</p>
              <p class="alert"><strong>UPDATE #4 - 2013-10-08 :</strong> You can now view team stats for your team. You can also see the top 3 winning-est teams featured on the World Stats tab. Player stats are still in development, but are coming along. More soon.</p>
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
            <h2>Top Winning Teams</h2><?=top_teams_display()?>
            </p>
            <br class="clearfix" />
            <p>
            <h2>Busiest Teams</h2><?=busy_teams_display()?>
            </p>
          </div>
          <?= render_team_tabs_content($_COOKIE['dbrcid'])?>
          <div id="newteampane" class="tab-pane">
            <h4>New Roster</h4>
            <p>Enter the new team details below and start the draft.</p>
            <div class="form-inline ">
             <button class="btn btn-inverse" data-action="randomname" id="get_rand_name">Surprise Me</button>
             <input type="text" class="input span3" name="new_team_name" id="new_team_name" placeholder="New Team Name...">
              
                    <select id="team_type">
                        <?=team_types_select()?>
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
    <p>Record your results below and submit. Draws only happen in tournaments when time runs out in a round. Otherwise, it's to the bitter end. Yar!</p>
    <hr>
      <form name="result_form" id="result_form" class="form-contact" method="post">
        <select class="input-medium" name="match_result" id="match_result" required>
          <option value="">Result</option>
          <option value="w">Win</option>
          <option value="l">Loss</option>
          <option value="d">Draw</option>
        </select>
        <input class="input-small" name="final_score" id="final_score" max="7" min="0" type="number" placeholder="Final Score" required>
        <select class="input-medium" name="opponent_team" id="opponent_team" required>
          <option value="">Against Who</option>
          <?=team_types_select()?>
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
    <!--<form class="form-contact" id="user_info_form" action="" method="post">-->
    <!--  <div class="input-prepend"><span class="add-on ">Name</span><input type="text" class="input span3 prependedInput" placeholder="You" name="user_name" required value="<?=$username?>"></div><br />
      <div class="input-prepend"><span class="add-on ">Email</span><input type="email" class="input span3 prependedInput" placeholder="you@domain.com" name="user_email" required value="<?=$useremail?>"></div><br />
      <div class="input-prepend"><span class="add-on ">Password</span><input type="password" class="input span3 prependedInput" placeholder="Psst..." name="user_pass" required value="<?=$userpass?>"></div><br />
    -->
      <h3 class="text-center">My name is <a href="#" class="user-editable-field" id="user_name" data-type="text" data-name="name" data-pk="<?=$_COOKIE["dbrcid"]?>" data-url="push.php?a=userdata" data-title="Your Name" data-value="<?=$username?>"><?=$username?></a><br>
        My email is <a href="#" class="user-editable-field" id="user_email" data-type="text" data-name="email" data-pk="<?=$_COOKIE["dbrcid"]?>" data-url="push.php?a=userdata" data-title="you@domain.com" data-value="<?=$useremail?>"><?=$useremail?></a>
        <? if(!$user->facebook){ ?>
        <br>
        I use <a href="#" class="user-editable-field" id="user_pass" data-type="text" data-name="password" data-pk="<?=$_COOKIE["dbrcid"]?>" data-url="push.php?a=userdata" data-title="sshhhhh..." data-value="<?=$userpass?>"><?=$userpass?></a> for a password.
        </h3>
        <p>Yes the password is not hidden by *'s. Frankly, I hate that, which is why its not there. Besides, is there someone looking over your shoulder? (You totally just looked, didn't you? :)</p>
        <? } ?>
              <!--<button class="btn btn-large btn-primary" type="submit" id="user_info_update">Update My Info</button>-->
            <!--</form>-->
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
