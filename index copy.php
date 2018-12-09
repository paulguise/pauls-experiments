<?php

require ('engine.php');
$message = '';

if(isset($_COOKIE['dbrcid']) && $_COOKIE['dbrcid']>0){
  header('Location: roster.php');
}


if(isset($_REQUEST['email']) && $_REQUEST['email']<>'' && isset($_REQUEST['password']) && $_REQUEST['password']<>'' && isset($_REQUEST['action'])){
  #check the pass
  $checkpass = grab_value("select user from users where email = '".$_REQUEST['email']."' and password = '".$_REQUEST['password']."'");
  if (!empty($checkpass)){
    #$message = 'CORRECT.';
    #put a cookie on their system so we know who the hell they are
    setcookie('dbrcid', $checkpass, time()+60*60*24*30, '/');
    header('Location: roster.php');
  }else{
    #they want to create an account, so put in their info and redirect them.
    $newuser = push_value("insert into users (name,email,password) values('".$_REQUEST['email']."','".$_REQUEST['email']."','".$_REQUEST['password']."')");
    setcookie('dbrcid', $newuser, time()+60*60*24*30, '/');
    header('Location: /roster.php');
    #$message = 'The password is incorrect.';
  }
}else{
  $message = '<br />You forget something there, chucklehead?';
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Dreadball Team Manager - Sign In</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 600px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading, .form-signin .checkbox {
        margin-bottom: 10px;
      }

    </style>
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

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

  // Here we subscribe to the auth.authResponseChange JavaScript event. This event is fired
  // for any authentication related change, such as login, logout or session refresh. This means that
  // whenever someone who was previously logged out tries to log in again, the correct case below 
  // will be handled. 
  FB.Event.subscribe('auth.authResponseChange', function(response) {
    // Here we specify what we do with the response anytime this event occurs. 
    if (response.status === 'connected') {
      // The response object is returned with a status field that lets the app know the current
      // login status of the person. In this case, we're handling the situation where they 
      // have logged in to the app.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // In this case, the person is logged into Facebook, but not into the app, so we call
      // FB.login() to prompt them to do so. 
      // In real-life usage, you wouldn't want to immediately prompt someone to login 
      // like this, for two reasons:
      // (1) JavaScript created popup windows are blocked by most browsers unless they 
      // result from direct interaction from people using the app (such as a mouse click)
      // (2) it is a bad experience to be continually prompted to login upon page load.
      FB.login({scope: 'email'});
    } else {
      // In this case, the person is not logged into Facebook, so we call the login() 
      // function to prompt them to do so. Note that at this stage there is no indication
      // of whether they are logged into the app. If they aren't then they'll see the Login
      // dialog right after they log in to Facebook. 
      // The same caveats as above apply to the FB.login() call here.
      FB.login({scope: 'email'});
    }
  });
  };

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
      $.post('push.php',{a:'createfacebook',first_name:response.first_name,email:response.email,id:response.id},function(data) {
        if(data==true){
          console.log('facebook login successful:'+data)
        }else{
          console.log('facebook login failed:'+data)
        }
      });
    });
  }
</script>

<!--
  Below we include the Login Button social plugin. This button uses the JavaScript SDK to
  present a graphical Login button that triggers the FB.login() function when clicked.

  Learn more about options for the login button plugin:
  /docs/reference/plugins/login/ -->


    <div class="container">

        <form class="form-signin form-inline text-center" action="" method="post">
          <h2 class="form-signin-heading">Dreadball Team Manager</h2>
          <input type="text" class="input-medium" placeholder="Email address" name="email">
          <input type="password" class="input-medium" placeholder="Password" name="password">
          <button class="btn" type="submit">Sign Up / Sign in</button>
          <input type="hidden" name="action" value="login">
          <?=$message?>
          <hr>
          <fb:login-button show-faces="true" width="200" max-rows="1"></fb:login-button>
        </form>
 
      

<hr>
      <footer class=" text-center">
        <p>Roster Creator &copy; Paul Guise <?=date('Y')?> | <a href="http://www.manticgames.com/Shop-Home/DreadBall.html" target="_blank">Dreadball</a> &copy; <a href="http://www.manticgames.com/" target="_blank">Mantic Games</a></p>
      </footer>

    </div> <!-- /container -->
  </body>
</html>
