<?php
    // ==================================================================
    // Define some config stuffs that you might want to change
    // moodle location (no slash on end)
    $moodleurl = 'http://pc88-167.guide.gla.ac.uk/wset';
   
    // Access token for Moodle web services. You'll need to get your own
    // See documentation for Moodle web services
    $wstoken = '14339bcb267c0bee968a86ed87eadf65';
    // ==================================================================

    session_start();

    // determine login state using session
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
        error_log('User session found valid=' . $user['valid']);
    } else {
        $user = null;
        error_log('User session not found');
    }

    // check for token (from Moodle login page)
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        error_log('SSO Token found = ' . $token);

        // Validate token with web service
        // (Check that token really came from Moodle and get user details)
        $wsreturn = wsvalidate_token($token);
        if (!empty($wsreturn['valid'])) {
            $user = $wsreturn;
            $_SESSION['user'] = $user;
            error_log('Token validated, user session is set. User id = ' . $user['userid']);
        } else {
            unset($_SESSION['user']);
            $user = null;
            error_log('Token is not valid. User session is unset');
        }
    } else {
        $token = '';
        error_log('Token was not found in session');
    }

    // check for action
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = '';
    }

    // if action = logout...
    if (($action == 'logout')) {
        unset($_SESSION['user']);
        $wantsurl = $_SERVER['PHP_SELF'];
        header('Location: ' . $moodleurl . '/auth/wsetsso/logout.php?wantsurl=' . $wantsurl);
        die;
    } 

    // if action = SSO, we need to have validated token.
    // You are probbly safe to simply redirect to Moodle as token lifetime < Moodle session lifetime. 
    if (($action == 'sso') && $token) {
        header('Location: ' . $moodleurl);
        die;
    } 
    

    /**
     * Function to validate token
     * (please excuse horrible globals)
     */
    function wsvalidate_token($token) {
        global $wstoken, $moodleurl;

        require('curl.php');

        // web service function
        $wsfunction = 'auth_wsetsso_checktoken';

        // full url for ws call
        $wsurl = $moodleurl . '/webservice/xmlrpc/server.php?wstoken=' . $wstoken;        

        // XMLRPC magic
        $curl = new curl;
        $post = xmlrpc_encode_request($wsfunction, array($token));
        $raw_response = $curl->post($wsurl, $post);
        $response = xmlrpc_decode($raw_response);

        return $response;
    }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>WSET Fake CMS</title>

    <!-- Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">
    <h1>Fake CMS!</h1>

    <!-- bit of logic to see what we are going to do -->
    <?php if (!$user) { ?>
        <?php if ($action == 'login') { ?>
            <iframe src="<?php echo $moodleurl; ?>/login/index.php?wsetsso=1" width="500" height="600"></iframe>
        <?php } else { ?>
            <p>You are not logged into the fake CMS</p>
            <a class="btn btn-primary" href="index.php?action=login">Login via Moodle</a>
        <?php } ?>
    <?php } else { ?>
        <p>You have logged in to the fake CMS:</p>
        <ul>
            <li>User's firstname: <?php echo $user['firstname']; ?></li>
            <li>User's lastname: <?php echo $user['lastname']; ?></li>
            <li>User's Moodle id number: <?php echo $user['userid']; ?></li>
            <li>Email: <?php echo $user['email']; ?></li>
            <li>User's cohorts: <?php echo implode(', ', $user['cohorts']); ?></li>
        </ul>
        <a class="btn btn-primary" href="index.php?action=sso&token=<?php echo $token; ?>">SSO to Moodle</a><br />
        <a class="btn btn-danger" href="index.php?action=logout&token=<?php echo $token; ?>">Logout</a>
    <?php } ?>

    </div>
  </body>
</html>
