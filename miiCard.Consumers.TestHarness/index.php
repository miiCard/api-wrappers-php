<?php
    require_once('../miiCard.Consumers/miiCard.Consumers.php');
    require_once('prettify.php');
    
    if (session_id() == "")
    {
        session_start();
    }
          
    $consumerKey = isset($_REQUEST['consumerKey']) ? $_REQUEST['consumerKey'] : NULL;
    $consumerSecret = isset($_REQUEST['consumerSecret']) ? $_REQUEST['consumerSecret'] : NULL;
    $accessToken = isset($_REQUEST['accessToken']) ? $_REQUEST['accessToken'] : NULL;
    $accessTokenSecret = isset($_REQUEST['accessTokenSecret']) ? $_REQUEST['accessTokenSecret'] : NULL;
    
    $socialAccountId = isset($_REQUEST['socialAccountId']) ? $_REQUEST['socialAccountId'] : NULL;
    $socialAccountType = isset($_REQUEST['socialAccountType']) ? $_REQUEST['socialAccountType'] : NULL;
    $assuranceImageType = isset($_REQUEST['assuranceImageType']) ? $_REQUEST['assuranceImageType'] : NULL;
        
    const SESSION_KEY_CONSUMER_KEY = 'miiCard.PHP.TestHarness.ConsumerKey';
    const SESSION_KEY_CONSUMER_SECRET = 'miiCard.PHP.TestHarness.ConsumerSecret';
    
    const POSTBACK_FLAG = "testHarnessPostback";
    
    $isTestHarnessPostback = isset($_REQUEST[POSTBACK_FLAG]);
    
    if (!$isTestHarnessPostback)
    {
        // If we're coming here for the first time or as a result of an OAuth
        // redirect (i.e. $isTestHarnessPostback is false) then try restoring
        // the consumer key and secret
        if ($consumerKey == null && isset($_SESSION[SESSION_KEY_CONSUMER_KEY]))
        {
            $consumerKey = $_SESSION[SESSION_KEY_CONSUMER_KEY];    
        }
        if ($consumerSecret == null && isset($_SESSION[SESSION_KEY_CONSUMER_SECRET])) 
        {
        	$consumerSecret = $_SESSION[SESSION_KEY_CONSUMER_SECRET];    
        }
    }
    
    $incompleteConsumerDetails = ($consumerKey == null || $consumerSecret == null);
    $incompleteOAuthDetails = $incompleteConsumerDetails || ($accessToken == null || $accessTokenSecret == null);
    
    $isLoginRequest = isset($_REQUEST['action']) && $_REQUEST['action'] == 'miiCardLogin';
    
    $showOAuthDetailsRequiredError = $incompleteOAuthDetails;
    $showConsumerDetailsRequiredError = $incompleteConsumerDetails && $isLoginRequest;
    $showOAuthError = false;
    
    $miiCardObj = null;
    if (!$incompleteConsumerDetails) 
    {
        $miiCardObj = new MiiCard($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    }

    if ($isLoginRequest && !$incompleteConsumerDetails && $miiCardObj !== null)
    {
        // Keep track of the consumer key and secret so we can restore them on callback
        $_SESSION[SESSION_KEY_CONSUMER_KEY] = $consumerKey;
        $_SESSION[SESSION_KEY_CONSUMER_SECRET] = $consumerSecret;
              
        $miiCardObj->beginAuthorisation();
        return;
    }
    else if ($miiCardObj !== null && $miiCardObj->isAuthorisationCallback() && $incompleteOAuthDetails)
    {
        // If we're being called as a result of the OAuth process, handle the callback to try to obtain
        // an access token and secret
        $miiCardObj->handleAuthorisationCallback();
        if ($miiCardObj->isAuthorisationSuccess())
        {     
            // Grab the access token and secret - we'll later render them into the two text boxes so that
            // we continue to receive them on posts.
            $accessToken = $miiCardObj->getAccessToken();
            $accessTokenSecret = $miiCardObj->getAccessTokenSecret();
        }
        else
        {
            $showOAuthError = true;
        }
    }
    else if ($miiCardObj !== null && !$incompleteOAuthDetails && isset($_REQUEST['btn-invoke']))
    {
        $fn = $_REQUEST['btn-invoke'];
        
        $miiCardObj = new MiiCardOAuthClaimsService($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        switch ($fn)
        {
            case 'get-claims':
                $lastGetClaimsResult = $miiCardObj->getClaims();
                break;
            case 'is-user-assured':
                $lastIsUserAssuredResult = $miiCardObj->isUserAssured();
                break;
            case 'is-social-account-assured':
                if (isset($_REQUEST['socialAccountId']) && isset($_REQUEST['socialAccountType']))
                {
                    $lastIsSocialAccountAssuredResult = $miiCardObj->isSocialAccountAssured($_REQUEST['socialAccountId'], $_REQUEST['socialAccountType']);
                }
                break;
            case 'assurance-image':
                if (isset($_REQUEST['assuranceImageType']))
                {
                    $showAssuranceImage = true;
                }
                break;
        }
    }
?>
<!doctype html>
<html>
  <head>
      <link rel="Stylesheet" type="text/css" href="styles/bootstrap.min.css" />
      <link rel="Stylesheet" type="text/css" href="styles/Site.css" />
      <script href="js/jquery-1.8.2.min.js"></script>
      <script href="js/bootstrap.min.js"></script>
      <title>miiCard PHP API Wrappers Test Harness</title>
  </head>
<body>
<div class="container">
    <div class="row">
        <div class="span12">
            <h1>PHP miiCard API test harness</h1>
        </div>
    </div>
    <form method="POST" action="index.php">
        <input type="hidden" name="<?php echo POSTBACK_FLAG ?>" value="true" />
        <div class="page-header">
            <h1>OAuth token settings
            <small>Enter manually or <button type="submit" name="action" value="miiCardLogin" class="btn btn-large">Login with miiCard &raquo;</button></small>
            </h1>
        </div>
        <div class="row">
            <div class="span12">

                <?php
                if ($showConsumerDetailsRequiredError)
                {
                ?>
                <div class="alert alert-error">
                    You need to specify at least the consumer key and secret.
                </div>
                <?php
                } 
                else if ($showOAuthDetailsRequiredError) 
                { 
                ?>
                <div class="alert alert-error">
                    Keys and secrets are required fields.
                </div>
                <?php 
                }
                else if ($showOAuthError)
                {
                ?>
                <div class="alert alert-error">
                    There was a problem with the authorisation process.
                </div>
                <?php  
                }  
                ?>

                <label for="consumerKey">OAuth Consumer Key</label>
                <input type="text" name="consumerKey" value="<?php echo $consumerKey; ?>"/>
                <label for="consumerSecret">OAuth Consumer Secret</label>
                <input type="text" name="consumerSecret" value="<?php echo $consumerSecret; ?>" />
                <label for="accessToken">OAuth Access Token</label>
                <input type="text" name="accessToken" value="<?php echo $accessToken; ?>" />
                <label for="accessTokenSecret">OAuth Access Token Secret</label>
                <input type="text" name="accessTokenSecret" value="<?php echo $accessTokenSecret; ?>" />
            </div>
        </div>
        <div class="page-header">
            <h1>API methods
            <small>Find the method you want to invoke</small>
            </h1>
        </div>
        <div class="page-header">
            <h2>GetClaims
            <small>Gets the set of data a user has shared with the application</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <p>There are no parameters</p>
                <h4>Result</h4>
                <?php if (isset($lastGetClaimsResult)) { ?>
                <p><?php echo renderResponse($lastGetClaimsResult) ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="get-claims" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2>IsUserAssured
            <small>Determines if the user has a current financial validation</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <p>There are no parameters</p>
                <h4>Result</h4>
                <?php if (isset($lastIsUserAssuredResult)) { ?>
                <p><?php echo renderResponse($lastIsUserAssuredResult) ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="is-user-assured" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2>IsSocialAccountAssured
            <small>Determines if a given social account belongs to the user</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="socialAccountId">Social account ID</label>
                <input type="text" name="socialAccountId" value="<?php echo $socialAccountId; ?>" />
                <label for="socialAccountType">Social account type (e.g. 'Twitter')</label>
                <input type="text" name="socialAccountType" value="<?php echo $socialAccountType; ?>" />
    
                <h4>Result</h4>
                <?php if (isset($lastIsSocialAccountAssuredResult)) { ?>
                <p><?php echo renderResponse($lastIsSocialAccountAssuredResult) ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="is-social-account-assured" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2>AssuranceImage
            <small>Renders a graphical representation of LOA</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="assuranceImageType">Image type</label>
                <input type="text" name="assuranceImageType" value="<?php echo $assuranceImageType; ?>" />
    
                <h4>Result</h4>
                <?php if (isset($showAssuranceImage)) { ?>
                <p><img src="assuranceimage.php?oauth-consumer-key=<?php echo rawurlencode($consumerKey); ?>&oauth-consumer-secret=<?php echo rawurlencode($consumerSecret); ?>&oauth-access-token=<?php echo rawurlencode($accessToken); ?>&oauth-access-token-secret=<?php echo rawurlencode($accessTokenSecret); ?>&type=<?php echo rawurlencode($assuranceImageType); ?>" /></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="assurance-image" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>