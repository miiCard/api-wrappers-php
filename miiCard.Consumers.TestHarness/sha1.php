<?php
    use miiCard\Consumers\Consumers;

    require_once('../miiCard.Consumers/miiCard.Consumers.php');

    $identifier = isset($_REQUEST['identifier']) ? $_REQUEST['identifier'] : NULL;

    if ($identifier != NULL) {
        echo Consumers\MiiCardDirectoryService::hashIdentifier($identifier);
    }
?>