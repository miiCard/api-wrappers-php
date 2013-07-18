<?php
    use miiCard\Consumers\Consumers;

    require_once('../miiCard.Consumers/miiCard.Consumers.php');
    
   /* ATTENTION
      DO NOT use this as an example of how to proxy an assurance image to a
      client browser - it's purely for diagnostics. In an actual application
      you would never expose any of the OAuth tokens or secrets to the client
      beyond those exchanged during the OAuth authorisation process.
      
      This example would be modified by pulling the values from a database.
   */
   
    $consumerKey = isset($_REQUEST['oauth-consumer-key']) ? $_REQUEST['oauth-consumer-key'] : NULL;
    $consumerSecret = isset($_REQUEST['oauth-consumer-secret']) ? $_REQUEST['oauth-consumer-secret'] : NULL;
    $accessToken = isset($_REQUEST['oauth-access-token']) ? $_REQUEST['oauth-access-token'] : NULL;
    $accessTokenSecret = isset($_REQUEST['oauth-access-token-secret']) ? $_REQUEST['oauth-access-token-secret'] : NULL;
    $snapshotId = isset($_REQUEST['snapshot-id']) ? $_REQUEST['snapshot-id'] : NULL;
    $showPhoneNumber = isset($_REQUEST['show-phone-number']) ? $_REQUEST['show-phone-number'] : NULL;
    $showEmailAddress = isset($_REQUEST['show-email-address']) ? $_REQUEST['show-email-address'] : NULL;
    $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : NULL;
    
    if ($consumerKey != null 
        && $consumerSecret != null 
        && $accessToken != null 
        && $accessTokenSecret != null)
    {
        $miiCardObj = new Consumers\MiiCardOAuthClaimsService($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        header("Content-type: image/png");
        echo $miiCardObj->getCardImage($snapshotId, $showEmailAddress == 'true', $showPhoneNumber == 'true', $format);
    }
?>