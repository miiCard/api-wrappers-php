api-wrappers-php
================

PHP wrapper classes around the miiCard API and OAuth authorisation process.

##What is miiCard

miiCard lets anybody prove their identity to the same level of traceability as using a passport, driver's licence or photo ID. We then allow external web applications to ask miiCard users to share a portion of their identity information with the application through a web-accessible API.

##What is the library for?
miiCard's API is an OAuth-protected web service supporting SOAP, POX and JSON - [documentation](http://www.miicard.com/developers) is available. The library wraps the JSON endpoint of the API, making it easier to make the required OAuth signed requests. It also provides a very simple code interface for initiating an OAuth exchange with miiCard to obtain OAuth access token and secret information for a miiCard member.

You can obtain a consumer key and secret from miiCard by contacting us on our support form, including the details listed on the developer site.

##Usage

###Performing the OAuth exchange
*Note: you must have called session_start before trying to initiate an OAuth exchange as certain temporary tokens need to be persisted across stages of the process.*

To start an OAuth exchange, first build a MiiCard wrapper object and call its beginAuthorisation method - this will obtain a request token and redirect the user to the miiCard site to log in:

    $miiCardObj = new MiiCard($consumerKey, $consumerSecret)
    $miiCardObj->beginAuthorisation();

Once the authorisation process has completed, the user will be redirected back to the page they left in your application. You can detect when your page is being called as an OAuth callback by asking the MiiCard object - you can then complete the authorisation process to obtain an access token and secret:

    if ($miiCardObj->isAuthorisationCallback())
    {
        $miiCardObj->handleAuthorisationCallback();

        if ($miiCardObj->isAuthorisationSuccess())
        {
            $accessToken = $miiCardObj->getAccessToken();
            $accessTokenSecret = $miiCardObj->getAccessTokenSecret();
        }
    }   

You can use the same MiiCard object to get the profile of the miiCard member who performed the authorisation:

    $userProfile = $miiCardObj->getUserProfile();

You can also extract the access token and secret information and construct a new API wrapper object to access the full API:

    $api = new MiiCardOAuthClaimsService($miiCardObj->getConsumerKey(), $miiCardObj->getConsumerSecret(),
                                         $miiCardObj->getAccessToken(), $miiCardObj->getAccessTokenSecret());

    $claimsResponse = $api->getClaims();

*Note: Avoid trying to process the same authorisation callback twice, for example if the user refreshes the callback page*

###Accessing the miiCard API

Assuming you've stored the access token and secret, you can create a MiiCardOAuthClaimsService object suitable for accessing the API by supplying those parameters to the MiiCard constructor:

    $api = new MiiCardOAuthClaimsService($consumerKey, $consumerSecret,
                                         $accessToken, $accessTokenSecret);

    $claimsResponse = $api->getClaims();

##Test harness

The miiCard.Consumers.TestHarness folder contains a quick test harness to allow some interactive testing of the library. It may serve as a guide for how to quickly get up and running with the library but hasn't been extensively checked for correctness or security and should be considered a local diagnostic tool only.

##Documentation

Documentation is provided in the doc folder, and is intended to supplement the API documentation available on the [miiCard Developers site](http://www.miicard.com/developers).

##Dependencies
For performing OAuth operations we include [Andy Smith](http://term.ie/blog/)'s PHP OAuth library, available under the MIT licence. For more information see its [Google Code page](http://oauth.googlecode.com/svn/code/php/).

Some automated tests are provided that exercise the library's mapping from JSON to corresponding PHP objects, available in the miiCard.Consumers/test folder - these depend upon [PHPUnit](https://github.com/sebastianbergmann/phpunit/), though if you don't want to run the test suite then this isn't necessary.

##Contributing
* Use GitHub issue tracking to report bugs in the library
* If you're going to submit a patch, please base it off the development branch if available
* Join the [miiCard.com developer forums](http://www.miicard.com/developers) to keep up to date with the latest releases and planned changes