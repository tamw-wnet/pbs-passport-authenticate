<?php
/*
activate.php

*/
show_admin_bar(false);
get_header();

$passport = new PBS_Passport_Authenticate(dirname(__FILE__));
$pluginImageDir = $passport->assets_url . 'img';


$defaults = get_option('pbs_passport_authenticate');
$station_nice_name = $defaults['station_nice_name'];

// this script only takes one possible argument

$activation_token = (!empty($_REQUEST['activation_token']) ? str_replace(' ', '-', trim($_REQUEST['activation_token'])) : '');


if ($activation_token){
  $mvaultinfo = $passport->lookup_activation_token($activation_token);
  $return = array();
  if (empty($mvaultinfo['membership_id'])){
    $return['errors'] = 'This activation code is invalid';
  } else {
    // this is a theoretically valid token.  

    if ($mvaultinfo['status']!='On') {
      $return['errors'] = 'This account has been disabled';
    }
    if (!empty($mvaultinfo['activation_date'])) {
      $return['errors'] = 'Your account has already been activated.  <a href="' . site_url('pbsoauth/loginform')  . '">Please sign in here</a>.<br /><br />You only need to activate the first time you use ' . $station_nice_name . ' Passport.';
    }
    if (empty($return['errors'])){ 
      // nothing wrong with this account, so
      // see if we're already logged in
      $laas_client = $passport->get_laas_client();
      $userinfo = $laas_client->check_pbs_login();
      if ($userinfo){
        // the user is logged in already.  Activate them!
        $pbs_uid = $userinfo["pid"];
        $mvault_client = $passport->get_mvault_client();
        $mvaultinfo = $mvault_client->activate($mvaultinfo['membership_id'], $pbs_uid);
        $userinfo["membership_info"] = $mvaultinfo;
        $success = $laas_client->validate_and_append_userinfo($userinfo);
        $login_referrer = site_url();
        if ( !empty($_COOKIE["pbsoauth_login_referrer"]) ){
          $login_referrer = $_COOKIE["pbsoauth_login_referrer"];
          setcookie( 'pbsoauth_login_referrer', '', 1, '/', $_SERVER['HTTP_HOST']);
        }
        wp_redirect($login_referrer);
        exit();
      }
      // if NOT logged in, redirect to the login page so they can activate there
      $loginuri = site_url('pbsoauth/loginform') . '?membership_id=' . $mvaultinfo['membership_id'];
      wp_redirect($loginuri);
      exit();
    }
  }
}
?>
<div class='pbs-passport-authenticate-wrap cf'>
<div class="pbs-passport-authenticate activate cf">
<div class='passport-middle'>
 
  
  <?php 
  if (!empty($defaults['station_passport_logo'])) {
	  echo '<div class="pp-logo-head"><img src="' . $defaults['station_passport_logo'] . '" alt="'.$station_nice_name.' Passport" /></div>'; 
	}
  ?>
  
  <div class='pp-narrow'>
<h1>Enter your activation code:</h1>
<form action="" method="POST" class='cf'>
<input name="activation_token" type="text" value="<?php echo $activation_token; ?>" />
<button><i class="fa fa-arrow-circle-right"></i> <span>Enter Code</span></button>
</form>
<?php
if (!empty($return['errors'])){
  echo "<h3 class='error'>" . $return['errors'] . "</h3>";
}
?>

<h3>How do I find my activation code?</h3>

<p>If you are an active, qualifying member of  <?php echo $station_nice_name; ?>  ($60+ annually or $5+ monthly), look for an email you received containing your activation code.</p>  
<?php if (class_exists('WNET_Passport_Already_Member')) { ?>
<h3>Don't have an activation code?</h3>
<p><a href="<?php echo site_url('pbsoauth/alreadymember/'); ?>">Look up your activation code.</a></p>
<?php } ?>


<h3>Have questions or technical issues?</h3>
<p>Check out our <a href="<?php echo site_url('/passport/faq/'); ?>">FAQ Page</a> to see if your question has already been answered. For technical support, please call 1-844-417-6327 or <a href="http://help.pbs.org/support/tickets/new">submit a support ticket</a>.</p>


</div><!-- .pp-narrow -->

<div class='service-options cf'>
	<ul>

	<li class="activate">
	<h4>Already activated?</h4>
	<a href="<?php echo site_url('pbsoauth/loginform/'); ?>" ><button class='pp-button-outline'>MEMBER SIGN IN <img src='<?php echo $pluginImageDir; ?>/passport_compass_gray.svg'/></button></a>
	</li>
	
	<?php if (!empty($defaults['join_url'])) { ?>
	<li class="becomemember">
	<h4>Not a <?php echo $station_nice_name; ?> member?</h4>
	<a href="<?php echo $defaults['join_url']; ?>"><button class='pp-button-outline'>BECOME A MEMBER <i class="fa fa-heart-o"></i></button></a></li>
	<?php } ?>

	</ul>
	</div>


</div>
</div>
</div>
<?php get_footer();
