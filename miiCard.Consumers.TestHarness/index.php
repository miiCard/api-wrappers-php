<?php
    require_once('../miiCard.Consumers/miiCard.Consumers.php');

    $miiCardObj = new MiiCard('test1key', 'Test1secret');

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'miiCardLogin')
    {
        $miiCardObj->beginAuthorisation();
    }
    else if ($miiCardObj->isAuthorisationCallback())
    {
         $miiCardObj->handleAuthenticationCallback();
         if ($miiCardObj->isAuthorisationSuccess())
         {
            ?>
            <h1>Success!</h1>
            <ul>
                <li>Access token: <?php echo $miiCardObj->getAccessToken(); ?></li>
                <li>Access token secret: <?php echo $miiCardObj->getAccessTokenSecret(); ?></li>
            </ul>

            <?php

            $apiWrapper = new MiiCardOAuthClaimsService('test1key', 'Test1secret', $miiCardObj->getAccessToken(), $miiCardObj->getAccessTokenSecret());
            $claims = $apiWrapper->getClaims();
         }
         else {
            ?><h1>Auth failed =/ </h1><?php
         }
    }
    else
    {
        ?>
        <a href="?action=miiCardLogin">Sign in with miiCard</a>
        <?php
    }
 ?>