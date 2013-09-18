<?php
    use miiCard\Consumers\Consumers;
    use miiCard\Consumers\Model;

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

    $identitySnapshotId = isset($_REQUEST['identitySnapshotId']) ? $_REQUEST['identitySnapshotId'] : NULL;
    $identitySnapshotDetailsSnapshotId = isset($_REQUEST['identitySnapshotDetailsSnapshotId']) ? $_REQUEST['identitySnapshotDetailsSnapshotId'] : NULL;
    $identitySnapshotPdfId = isset($_REQUEST['identitySnapshotPdfId']) ? $_REQUEST['identitySnapshotPdfId'] : NULL;

    $financialDataModestyLimit = isset($_REQUEST['financialDataModestyLimit']) ? $_REQUEST['financialDataModestyLimit'] : NULL;
    $getAuthenticationDetailsSnapshotId = isset($_REQUEST['getAuthenticationDetailsSnapshotId']) ? $_REQUEST['getAuthenticationDetailsSnapshotId'] : NULL;

    $cardImageSnapshotId = isset($_REQUEST['cardImageSnapshotId']) ? $_REQUEST['cardImageSnapshotId'] : NULL;
    $cardImageShowEmailAddress = isset($_REQUEST['cardImageShowEmailAddress']) ? ($_REQUEST['cardImageShowEmailAddress'] == 'on') : FALSE;
    $cardImageShowPhoneNumber = isset($_REQUEST['cardImageShowPhoneNumber']) ? ($_REQUEST['cardImageShowPhoneNumber'] == 'on') : FALSE;
    $cardImageFormat = isset($_REQUEST['cardImageFormat']) ? $_REQUEST['cardImageFormat'] : NULL;

    $directoryCriterion = isset($_REQUEST['directoryCriterion']) ? $_REQUEST['directoryCriterion'] : NULL;
    $directoryCriterionValue = isset($_REQUEST['directoryCriterionValue']) ? $_REQUEST['directoryCriterionValue'] : NULL;
    $directoryCriterionValueHashed = isset($_REQUEST['directoryCriterionValueHashed']) ? $_REQUEST['directoryCriterionValueHashed'] : NULL;

    $referrerCode = isset($_REQUEST['referrerCode']) ? $_REQUEST['referrerCode'] : NULL;
    $forceClaimsPicker = isset($_REQUEST['forceClaimsPicker']) ? $_REQUEST['forceClaimsPicker'] == 'on' : false;
    $signupMode = isset($_REQUEST['signupMode']) ? $_REQUEST['signupMode'] == 'on' : false;

    $lastDirectorySearchResult = NULL;
        
    const SESSION_KEY_CONSUMER_KEY = 'miiCard.PHP.TestHarness.ConsumerKey';
    const SESSION_KEY_CONSUMER_SECRET = 'miiCard.PHP.TestHarness.ConsumerSecret';
    
    const POSTBACK_FLAG = "testHarnessPostback";
    
    $isTestHarnessPostback = isset($_POST[POSTBACK_FLAG]);
    
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
        $miiCardObj = new Consumers\MiiCard($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $referrerCode, $forceClaimsPicker, $signupMode);
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
    else if (isset($_REQUEST['btn-invoke']) && $_REQUEST['btn-invoke'] == 'directory-search' && $directoryCriterionValue != NULL) {
        $directoryApi = new Consumers\MiiCardDirectoryService();
        $lastDirectorySearchResult = $directoryApi->findBy($directoryCriterion, $directoryCriterionValue, $directoryCriterionValueHashed);
    }
    else if ($miiCardObj !== null && !$incompleteOAuthDetails && isset($_REQUEST['btn-invoke']))
    {
        $fn = $_REQUEST['btn-invoke'];
        
        $miiCardObj = new Consumers\MiiCardOAuthClaimsService($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        $miiCardFinancialObj = new Consumers\MiiCardOAuthFinancialService($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
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
                    $showAssuranceImage = TRUE;
                }
                break;
            case 'card-image':
                $showCardImage = TRUE;
                break;
            case 'get-identity-snapshot-details':
                $lastGetIdentitySnapshotDetailsResult = $miiCardObj->getIdentitySnapshotDetails($_REQUEST['identitySnapshotDetailsSnapshotId']);
                break;
            case 'get-identity-snapshot':
                if (isset($_REQUEST['identitySnapshotId']))
                {
                    $lastGetIdentitySnapshotResult = $miiCardObj->getIdentitySnapshot($_REQUEST['identitySnapshotId']);
                }
                break;
            case 'get-identity-snapshot-pdf':
                if (isset($_REQUEST['identitySnapshotPdfId']) && strlen($_REQUEST['identitySnapshotPdfId']) > 0)
                {
                    header("Content-type: application/pdf");
                    header('Content-Disposition: attachment; filename="' . $_REQUEST['identitySnapshotPdfId'] . '".pdf');
                    echo $miiCardObj->getIdentitySnapshotPdf($_REQUEST['identitySnapshotPdfId']);
                    exit;
                }
                break;
            case 'get-authentication-details':
                $lastGetAuthenticationDetailsResult = $miiCardObj->getAuthenticationDetails($_REQUEST['getAuthenticationDetailsSnapshotId']);
                break;
            case 'is-refresh-in-progress':
                $lastIsRefreshInProgressResult = $miiCardFinancialObj->isRefreshInProgress();
                break;
            case 'refresh-financial-data':
                $lastRefreshFinancialDataResult = $miiCardFinancialObj->refreshFinancialData();
                break;
            case 'get-financial-transactions':
                $lastGetFinancialTransactionsResult = $miiCardFinancialObj->getFinancialTransactions();
                break;
        }
    }
?>
<!doctype html>
<html>
  <head>
      <link rel="Stylesheet" type="text/css" href="styles/bootstrap.min.css" />
      <link rel="Stylesheet" type="text/css" href="styles/Site.css" />
      <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
      <script type="text/javascript" src="js/bootstrap.min.js"></script>
      <title>miiCard PHP API Wrappers Test Harness</title>
      <style type="text/css">
      .page-header { margin-top: 50px; margin-bottom: 10px; }
      input[type=text] { width: 50%; }
      </style>
  </head>
<body>
<div class="container">
    <div class="row">
        <div class="span12">
            <h1>PHP miiCard API test harness</h1>
        </div>
    </div>
    <form method="POST" id="theForm">
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
                <label for="accessTokenSecret">Referrer Code (if any)</label>
                <input type="text" name="referrerCode" value="<?php echo $referrerCode; ?>" />
                <br />
                <input type="checkbox" name="forceClaimsPicker" value="on" <?php echo isset($referrerCode) && $referrerCode ? "checked=checked" : "" ?>" />&nbsp;Force claims picker
                <br />
                <input type="checkbox" name="signupMode" value="on" <?php echo isset($signupMode) && $signupMode ? "checked=checked" : "" ?>" />&nbsp;Signup mode (initially redirect to a signup, rather than login page)
            </div>
        </div>
        <div class="page-header">
            <h1>Claims API methods
            <small>Find the method you want to invoke</small>
            </h1>
        </div>
        <div class="page-header">
            <h2><a name="get-claims"></a>GetClaims
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
            <h2><a name="is-user-assured"></a>IsUserAssured
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
            <h2><a name="is-social-account-assured"></a>IsSocialAccountAssured
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
            <h2><a name="assurance-image"></a>AssuranceImage
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

        <div class="page-header">
            <h2><a name="card-image"></a>CardImage
            <small>Renders a card-image representation of LOA</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="cardImageSnapshotId">Snapshot ID (optional)</label>
                <input type="text" name="cardImageSnapshotId" value="<?php echo $cardImageSnapshotId; ?>" />

                <label for="cardImageFormat">Format (card, signature)</label>
                <input type="text" name="cardImageFormat" value="<?php echo $cardImageFormat; ?>" />

                <?php if ($cardImageShowEmailAddress == 'on') { ?>
                  <label class="checkbox"><input type="checkbox" name="cardImageShowEmailAddress" checked="checked" value="on" /> Show email address</label>
                <?php } else { ?>
                  <label class="checkbox"><input type="checkbox" name="cardImageShowEmailAddress" /> Show email address</label>
                <?php } ?>

                <?php if ($cardImageShowPhoneNumber == 'on') { ?>
                  <label class="checkbox"><input type="checkbox" name="cardImageShowPhoneNumber" checked="checked" value="on" /> Show phone number</label>
                <?php } else { ?>
                  <label class="checkbox"><input type="checkbox" name="cardImageShowPhoneNumber" /> Show phone number</label>
                <?php } ?>

                <h4>Result</h4>
                <?php if (isset($showCardImage)) { ?>
                <p><img src="cardimage.php?oauth-consumer-key=<?php echo rawurlencode($consumerKey); ?>&oauth-consumer-secret=<?php echo rawurlencode($consumerSecret); ?>&oauth-access-token=<?php echo rawurlencode($accessToken); ?>&oauth-access-token-secret=<?php echo rawurlencode($accessTokenSecret); ?>&snapshot-id=<?php echo rawurlencode($cardImageSnapshotId) ?>&show-email-address=<?php echo $cardImageShowEmailAddress ? 'true' : 'false'; ?>&show-phone-number=<?php echo $cardImageShowPhoneNumber ? 'true' : 'false' ?>&format=<?php echo $cardImageFormat ?>" /></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="card-image" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2><a name="get-identity-snapshot-details"></a>GetIdentitySnapshotDetails
            <small>Retrieve metadata about an identity snapshot</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="identitySnapshotDetailsSnapshotId">Snapshot ID (blank to list all)</label>
                <input type="text" name="identitySnapshotDetailsSnapshotId" value="<?php echo $identitySnapshotDetailsSnapshotId; ?>" />

                <h4>Result</h4>
                <?php if (isset($lastGetIdentitySnapshotDetailsResult)) { ?>
                <p><?php echo renderResponse($lastGetIdentitySnapshotDetailsResult); ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="get-identity-snapshot-details" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2><a name="get-identity-snapshot"></a>GetIdentitySnapshot
            <small>Retrieve a previously created snapshot of a miiCard member's identity</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="identitySnapshotId">Snapshot ID</label>
                <input type="text" name="identitySnapshotId" value="<?php echo $identitySnapshotId; ?>" />

                <h4>Result</h4>
                <?php if (isset($lastGetIdentitySnapshotResult)) { ?>
                <p><?php echo renderResponse($lastGetIdentitySnapshotResult); ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="get-identity-snapshot" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2><a name="get-identity-snapshot-pdf"></a>GetIdentitySnapshotPdf
            <small>Retrieve a PDF of a created snapshot of a miiCard member's identity</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="identitySnapshotId">Snapshot ID</label>
                <input type="text" name="identitySnapshotPdfId" value="<?php echo $identitySnapshotPdfId; ?>" />
                <br />
                <button type="submit" name="btn-invoke" value="get-identity-snapshot-pdf" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="page-header">
            <h2><a name="get-authentication-details"></a>GetAuthenticationDetails
            <small>Retrieve details of how a miiCard member authenticated</small>
            </h2>
        </div>
        <div class="row">
            <div class="span12">
                <h3>Parameters</h3>
                <label for="getAuthenticationDetailsSnapshotId">Snapshot ID</label>
                <input type="text" name="getAuthenticationDetailsSnapshotId" value="<?php echo $getAuthenticationDetailsSnapshotId; ?>" />

                <h4>Result</h4>
                <?php if (isset($lastGetAuthenticationDetailsResult)) { ?>
                <p><?php echo renderResponse($lastGetAuthenticationDetailsResult); ?></p>
                <?php } ?>
                <button type="submit" name="btn-invoke" value="get-authentication-details" class="btn btn-large">Invoke method &raquo;</button>
            </div>
        </div>

        <div class="financials">
            <div class="page-header">
                <h1>Financial API methods
                <small>Find the method you want to invoke</small>
                </h1>
            </div>

            <div class="page-header">
                <h2><a name="is-refresh-in-progress"></a>IsRefreshInProgress
                <small>Checks if a financial data refresh is ongoing</small>
                </h2>
            </div>
            <div class="row">
                <div class="span12">
                    <h4>Result</h4>
                    <?php if (isset($lastIsRefreshInProgressResult)) { ?>
                        <p><?php echo renderResponse($lastIsRefreshInProgressResult); ?></p>
                    <?php } ?>

                    <button type="submit" name="btn-invoke" value="is-refresh-in-progress" class="btn btn-large">Invoke method &raquo;</button>
                </div>
            </div>

            <div class="page-header">
                <h2><a name="refresh-financial-data"></a>RefreshFinancialData
                <small>Requests financial data be updated</small>
                </h2>
            </div>
            <div class="row">
                <div class="span12">
                    <h4>Result</h4>
                    <?php if (isset($lastRefreshFinancialDataResult)) { ?>
                        <p><?php echo renderResponse($lastRefreshFinancialDataResult); ?></p>
                    <?php } ?>

                    <button type="submit" name="btn-invoke" value="refresh-financial-data" class="btn btn-large">Invoke method &raquo;</button>
                </div>
            </div>

            <div class="page-header">
                <h2><a name="get-financial-transactions"></a>GetFinancialTransactions
                <small>Retrieve financial transactions that the member has shared</small>
                </h2>
            </div>
            <div class="row">
                <div class="span12">
                    <h4>Result</h4>

                    <label for="financialDataModestyLimit">Hide values absolutely greater than this for modesty (blank to disable)</label>
                    <input type="text" name="financialDataModestyLimit" value="<?php echo $financialDataModestyLimit; ?>" /> <br />

                    <?php if (isset($lastGetFinancialTransactionsResult)) {
                        $configuration = new PrettifyConfiguration($financialDataModestyLimit); ?>
                        <p><?php echo renderResponse($lastGetFinancialTransactionsResult, $configuration); ?></p>
                    <?php } ?>

                    <button type="submit" name="btn-invoke" value="get-financial-transactions" class="btn btn-large">Invoke method &raquo;</button>
                </div>
            </div>
        </div>

        <div class="directory">
            <div class="page-header">
                <h1><a name="directory-search"></a>Directory API
                <small>Lookup miiCard members by data they've published</small>
                </h1>
                <div class="alert alert-info">
                <strong>Notes</strong>
                <ul>
                  <li>The Directory API doesn't require OAuth tokens - just call at will</li>
                  <li>Only data that's been published by a miiCard member who's elected to be searchable can be used as a search criterion</li>
                </ul>
                </div>
            </div>
            <div class="row">
                <div class="span12">
                    <h3>Hash identifier <small><a href="#" data-toggle="sha1-hash" class="toggle">More/less</a></small></h3>
                    <div id="sha1-hash" style="display: none">
                        <p>The Directory API can searched by supplying either plaintext or hashed query values for enhanced privacy</p>
                        <label for="directoryPlaintextIdentifier">Plain text identifier</label>
                        <div class="input-prepend" style="display: block;">
                            <span class="add-on"><i class="icon-random"></i></span>
                            <input class="span6" type="text" placeholder="Identifier value" name="directoryPlaintextIdentifier" id="directoryPlaintextIdentifier" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="span12">
                  <h3>Search</h3>
                  <div class="input-prepend" style="display: block;">
                    <span class="add-on"><i class="icon-search"></i></span>
                    <input class="span2" name="directoryCriterionValue" id="directoryCriterionValue" type="text" placeholder="Search" value="<?php echo $directoryCriterionValue; ?>" />
                    <select name="directoryCriterion">
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_EMAIL ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_EMAIL) ? " selected" : "" ?>>Email address</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_PHONE ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_PHONE) ? " selected" : "" ?>>Phone number</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_TWITTER ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_TWITTER) ? " selected" : "" ?>>Twitter</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_FACEBOOK ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_FACEBOOK) ? " selected" : "" ?>>Facebook</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_LINKEDIN ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_LINKEDIN) ? " selected" : "" ?>>LinkedIn</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_GOOGLE ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_GOOGLE) ? " selected" : "" ?>>Google+</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_MICROSOFT_ID ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_MICROSOFT_ID) ? " selected" : "" ?>>Microsoft ID</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_EBAY ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_EBAY) ? " selected" : "" ?>>eBay</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_VERITAS_VITAE ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_VERITAS_VITAE) ? " selected" : "" ?>>Veritas Vitae</option>
                        <option value="<?php echo Consumers\MiiCardDirectoryService::CRITERION_USERNAME ?>"<?php echo ($directoryCriterion == Consumers\MiiCardDirectoryService::CRITERION_USERNAME) ? " selected" : "" ?>>Username</option>
                    </select>
                    <button type="submit" name="btn-invoke" value="directory-search" class="btn" style="margin-left: 0.7em;">Search &raquo;</button>
                  </div>
                  <label for="directoryCriterionValueHashed" class="checkbox"><input type="checkbox" id="directoryCriterionValueHashed" name="directoryCriterionValueHashed"<?php echo ($directoryCriterionValueHashed ? " checked='checked'" : "") ?> /> Identifier is a hex SHA-1 hash</label>
                  <h4>Result</h4>
                  <?php
                  if ($lastDirectorySearchResult != NULL) {
                  ?><p><?php echo renderUserProfile($lastDirectorySearchResult); ?></p><?php
                  }
                  else {
                    echo "No results";
                  }
                  ?>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var sha1timer = null;

        $('button[name="btn-invoke"]').click(function () {
            $('#theForm').attr('action', '#' + $(this).attr('value'));
        });

        $('input, select').keydown(function (e) {
            if (e.which == 10 || e.which == 13) {
                $(this).closest('.row').find('button[type="submit"]').click();
                return false;
            }
        });

        $('a.toggle').click(function (e) {
            $('#' + $(this).attr('data-toggle')).toggle();
            e.preventDefault();
        });

        $('#directoryCriterionValue').keyup(function () {
            $('#directory_plaintext_identifier').val('');
        });

        $('#directoryPlaintextIdentifier').keyup(function () {
            if (sha1timer) {
                window.clearTimeout(sha1timer);
                sha1timer = null;
            }

            if ($(this).val().length) {
                elem = $(this);

                sha1timer =
                    window.setTimeout(function () {
                        $.ajax('sha1.php?identifier=' + encodeURIComponent(elem.val()), {
                            async: false,
                            success: function (data) {
                                $('#directoryCriterionValue').val(data);
                                $('#directoryCriterionValueHashed').prop('checked', 'checked');
                            },
                            error: function (xhr, status, error) { alert(error); }
                        });
                    }, 200);
            }
        });
    });
</script>
</body>
</html>