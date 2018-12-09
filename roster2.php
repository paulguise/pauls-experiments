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
                 $('#roster_cost_'+teamid).html('Total Roster Cost: '+data+'mc');
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
        name: 'Dreadball Team Manager ',
        link: 'http://dreadball.paulsrants.com/',
        picture: 'http://dreadball.paulsrants.com/bootstrap/img/team-manager-logo-150.png',
        caption: 'Dreadball Team Roster - name',
        description: 'person created a Dreadball roster with the Dreadball Team Manager.'
      },
      function(response) {
        if (response && response.post_id) {
          alert('Your roster has been shared on your timeline.');
        } else {
          alert('Nothing was published.');
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
      //now we either log them in or create their account.
      /*
      $.post('push.php',{a:'createfacebook',first_name:response.first_name,email:response.email,id:response.id},function(data) {
        if(data==true){
          //console.log('facebook login successful:'+data)
          window.location = 'roster.php';
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
              <!--<li><a href="#donateModal" data-toggle="modal" role="button" >Donate</a></li>
              <li><a href="#notesModal" data-toggle="modal" role="button" >[notes]</a></li>-->
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">

      <div class="tabbable">
        <ul class="nav nav-tabs" id="main_nav_tab_contain">
          <li class="active"><a href="#pane1" data-toggle="tab">Welcome</a></li>
          <!--<li><a href="#pane2" data-toggle="tab">My Rosters</a></li>-->
          <?= render_team_tabs_nav($_COOKIE['dbrcid'])?>
          <li id="newteampanelink"><a href="#newteampane" data-toggle="tab">New Roster</a></li>
        </ul>

        <div class="tab-content">
          <div id="pane1" class="tab-pane active">
            <!--<h4>The Markup</h4>
            <pre>Code here ...</pre>-->
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
              </p>
              <hr>
              <p>UPDATE #2 - 2013-10-02 : Teams can now record match results. There is more to come on the team stats feature, which is why you are asked to enter more than just 
                  win or lose. Further refinements to overall system and more behind the scenes work done for the individual player stats. Also added a copyright notice. More soon!</p>
              <p>UPDATE #1 - 2013-09-30 : You now have the option to print either a full roster or a shorter one, sans empty rows. Also, all options related to teams are now in the 
                <button class="btn dropdown-toggle btn-info btn-small"> Roster Actions</button> menu, as well as some features that will soon be finished and available.</p>
              <hr>
              <p>COPYRIGHT: Dreadball, the Dreadball name, logo, team names, player position titles, abilities labels, as well as anything else that is in the game 
                is Copyright &copy; <a href="http://www.manticgames.com/" target="_blank">Mantic Games</a>.  </p>
                <p><a href="javascript:fbShare('tigers','4321564');" target="_top" >Test Link</a></p>
            </div>
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


<div id="donateModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Buy me a coffee...</h3>
  </div>
  <div class="modal-body">
    <p>I built this for a number of reasons, but making money from this site isn't one of them. However, if you really feel the burning need to contribute to this endeavor, you can support my caffeine habit which goes to fuel this web-based mania. Just click the link below and you can pitch whatever you feel you need to (hint: a large coffee at my favorite spot is $2.15).</p>
    <p>-Cheers</p>
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

<div id="notesModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Secret Notes (shhhhh)</h3>
  </div>
  <div class="modal-body">
 
      <div class="row-fluid">
            <div class="span8">
              <h2>Database Table Notes</h2>
              <p>'stats' table will hold wins/losses for teams and player stats including 1,2,3,4 point scores, 1,2,3 points hits, kills, passing lengths, catches [later]</p>
              <p>WARNING: This is beta and has next to no validation, so a wrong selection will erase all your team data form the screen. Ye be warned.</p>
              <p></p>

              <h2>User Flow / Features</h2>
              <p>Skills can offset stats, so track what happens in a separate field (skill_effect, var, 5, +/-int).</p>
              <p>Skills can be completely filled out in a given roles' table, after that table is full, allow access to all tables (for all seasons).</p>
              <ul>
                <li> Retire/remove players due to death</li>
                <li> Dynamically update team costs</li>
                <li> Printable templates</li>
              </ul>
            </div><!--/span-->
          </div><!--/row-->
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
