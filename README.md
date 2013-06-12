api-wrappers-php
================

PHP wrapper classes around the miiCard API and OAuth authorisation process.

##What is miiCard

miiCard lets anybody prove their identity to the same level of traceability as using a passport, driver's licence or photo ID. We then allow external web applications to ask miiCard users to share a portion of their identity information with the application through a web-accessible API.

##What is the library for?
miiCard's API is an OAuth-protected web service supporting SOAP, POX and JSON - [documentation](http://www.miicard.com/developers) is available. The library wraps the JSON endpoint of the API, making it easier to make the required OAuth signed requests. It also provides a very simple code interface for initiating an OAuth exchange with miiCard to obtain OAuth access token and secret information for a miiCard member.

You can obtain a consumer key and secret from miiCard by contacting us on our support form, including the details listed on the developer site.

##Usage

###Namespaces
The wrapper library uses namespaces to avoid conflicts during integration.

* Root namesapce is **miiCard\Consumers**
* Consumer library functions, such as API wrappers live in **miiCard\Consumers\Consumers**
* Models such as those returned by API **miiCard\Consumers\Model**

###Performing the OAuth exchange
*Note: you must have called session_start before trying to initiate an OAuth exchange as certain temporary tokens need to be persisted across stages of the process.*

To start an OAuth exchange, first build a MiiCard wrapper object and call its beginAuthorisation method - this will obtain a request token and redirect the user to the miiCard site to log in:

    use miiCard\Consumers\Consumers;
    use miiCard\Consumers\Model;

    $miiCardObj = new Consumers\MiiCard($consumerKey, $consumerSecret)
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

    $api = new Consumers\MiiCardOAuthClaimsService($miiCardObj->getConsumerKey(), $miiCardObj->getConsumerSecret(),
                                                   $miiCardObj->getAccessToken(), $miiCardObj->getAccessTokenSecret());

    $claimsResponse = $api->getClaims();

*Note: Avoid trying to process the same authorisation callback twice, for example if the user refreshes the callback page*

###Accessing the miiCard API

Assuming you've stored the access token and secret, you can create a MiiCardOAuthClaimsService object suitable for accessing the API by supplying those parameters to the MiiCard constructor:

    use miiCard\Consumers\Consumers;
    use miiCard\Consumers\Model;

    $api = new Consumers\MiiCardOAuthClaimsService($consumerKey, $consumerSecret,
                                                   $accessToken, $accessTokenSecret);

    $claimsResponse = $api->getClaims();

##Test harness

The [miiCard.Consumers.TestHarness folder](api-wrappers-php/blob/master/miiCard.Consumers.TestHarness) contains a quick test harness to allow some interactive testing of the library. It may serve as a guide for how to quickly get up and running with the library but hasn't been extensively checked for correctness or security and should be considered a local diagnostic tool only.

##Documentation

Documentation is provided in the docs folder, and is intended to supplement the API documentation available on the [miiCard Developers site](http://www.miicard.com/developers).

##Mapping from API data types
The following list is provided as a convenient cheat-sheet, and maps the API's methods and data types to their equivalents in the PHP wrapper library classes.

###Methods
<table>
<tr><th>API method</td><th>PHP equivalent (given $api instance of MiiCardOAuthClaimsService)</th></tr>
<tr><td>AssuranceImage</td><td>$api->assuranceImage($type)</td></tr>
<tr><td>GetClaims</td><td>$api->getClaims()</td></tr>
<tr><td>GetIdentitySnapshot</td><td>$api->getIdentitySnapshot($snapshotId)</td></tr>
<tr><td>GetIdentitySnapshotDetails</td><td>$api->getIdentitySnapshotDetails()<br /><b>Or, for a specific snapshot:</b><br />$api->getIdentitySnapshotDetails($snapshotId)</td></tr>
<tr><td>GetIdentitySnapshotPdf</td><td>$api->getIdentitySnapshotPdf($snapshotId)</td></tr>
<tr><td>IsSocialAccountAssured</td><td>$api->isSocialAccountAssured($socialAccountId, $socialAccountType)</td></tr>
<tr><td>IsUserAssured</td><td>$api->isUserAssured()</td></tr>
</table>

###Data types

####Model\EmailAddress
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $email instance of EmailAddress)</th></tr>
<tr><td>DisplayName</td><td>$email->getDisplayName()</td></tr>
<tr><td>Address</td><td>$email->getAddress()</td></tr>
<tr><td>IsPrimary</td><td>$email->getIsPrimary()</td></tr>
<tr><td>Verified</td><td>$email->getVerified()</td></tr>
</table>

####Model\Identity
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $identity instance of Identity)</th></tr>
<tr><td>Source</td><td>$identity->getSource()</td></tr>
<tr><td>UserId</td><td>$identity->getUserId()</td></tr>
<tr><td>ProfileUrl</td><td>$identity->getProfileUrl()</td></tr>
<tr><td>Verified</td><td>$identity->getVerified()</td></tr>
</table>

####Model\IdentitySnapshot
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $snapshot instance of IdentitySnapshot)</th></tr>
<tr><td>Details</td><td>$snapshot->getDetails()</td></tr>
<tr><td>Snapshot</td><td>$snapshot->getSnapshot()</td></tr>
</table>

####Model\IdentitySnapshotDetails
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $snapshotDetails instance of IdentitySnapshotDetails)</th></tr>
<tr><td>SnapshotId</td><td>$snapshotDetails->getSnapshotId()</td></tr>
<tr><td>Username</td><td>$snapshotDetails->getUsername()</td></tr>
<tr><td>TimestampUtc</td><td>$snapshotDetails->getTimestampUtc()</td></tr>
<tr><td>WasTestUser</td><td>$snapshotDetails->getWasTestUser()</td></tr>
</table>

####Model\MiiApiCallStatus enumeration type
<table>
<tr><th>API data-type property</td><th>PHP equivalent</th></tr>
<tr><td>Success</td><td>MiiApiCallStatus::SUCCESS</td></tr>
<tr><td>Failure</td><td>MiiApiCallStatus::FAILURE</td></tr>
</table>

####Model\MiiApiErrorCode enumeration type
<table>
<tr><th>API data-type property</td><th>PHP equivalent</th></tr>
<tr><td>Success</td><td>MiiApiCallStatus::SUCCESS</td></tr>
<tr><td>AccessRevoked</td><td>MiiApiCallStatus::ACCESS_REVOKED</td></tr>
<tr><td>UserSubscriptionLapsed</td><td>MiiApiCallStatus::USER_SUBSCRIPTION_LAPSED</td></tr>
<tr><td>TransactionalSupportDisabled</td><td>MiiApiCallStatus::TRANSATIONAL_SUPPORT_DISABLED</td></tr>
<tr><td>DevelopmentTransactionalSupportOnly</td><td>MiiApiCallStatus::DEVELOPMENT_TRANSACTIONAL_SUPPORT_ONLY</td></tr>
<tr><td>InvalidSnapshotId</td><td>MiiApiCallStatus::INVALID_SNAPSHOT_ID</td></tr>
<tr><td>Blacklisted</td><td>MiiApiCallStatus::BLACKLISTED</td></tr>
<tr><td>ProductDisabled</td><td>MiiApiCallStatus::PRODUCT_DISABLED</td></tr>
<tr><td>ProductDeleted</td><td>MiiApiCallStatus::PRODUCT_DELETED</td></tr>
<tr><td>Exception</td><td>MiiApiCallStatus::EXCEPTION</td></tr>
</table>

####Model\MiiApiResponse
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $response instance of MiiApiResponse)</th></tr>
<tr><td>Status</td><td>$response->getStatus()</td></tr>
<tr><td>ErrorCode</td><td>$response->getErrorCode()</td></tr>
<tr><td>ErrorMessage</td><td>$response->getErrorMessage()</td></tr>
<tr><td>Data</td><td>$response->getData()</td></tr>
<tr><td>IsTestUser</td><td>$response->getIsTestUser()</td></tr>
</table>

####Model\MiiUserProfile
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $profile instance of MiiUserProfile)</th></tr>
<tr><td>Salutation</td><td>$profile->getSalutation()</td></tr>
<tr><td>FirstName</td><td>$profile->getFirstName()</td></tr>
<tr><td>MiddleName</td><td>$profile->getMiddleName()</td></tr>
<tr><td>LastName</td><td>$profile->getLastName()</td></tr>
<tr><td>DateOfBirth</td><td>$profile->getDateOfBirth()</td></tr>
<tr><td>PreviousFirstName</td><td>$profile->getPreviousFirstName()</td></tr>
<tr><td>PreviousMiddleName</td><td>$profile->getPreviousMiddleName()</td></tr>
<tr><td>PreviousLastName</td><td>$profile->getPreviousLastName()</td></tr>
<tr><td>LastVerified</td><td>$profile->getLastVerified()</td></tr>
<tr><td>ProfileUrl</td><td>$profile->getProfileUrl()</td></tr>
<tr><td>ProfileShortUrl</td><td>$profile->getProfileShortUrl()</td></tr>
<tr><td>CardImageUrl</td><td>$profile->getCardIamgeUrl()</td></tr>
<tr><td>EmailAddresses</td><td>$profile->getEmailAddresses()</td></tr>
<tr><td>Identities</td><td>$profile->getIdentities()</td></tr>
<tr><td>PhoneNumbers</td><td>$profile->getPhoneNumbers()</td></tr>
<tr><td>PostalAddresses</td><td>$profile->getPostalAddresses()</td></tr>
<tr><td>WebProperties</td><td>$profile->getWebProperties()</td></tr>
<tr><td>IdentityAssured</td><td>$profile->getIdentityAssured()</td></tr>
<tr><td>HasPublicProfile</td><td>$profile->getHasPublicProfile()</td></tr>
<tr><td>PublicProfile</td><td>$profile->getPublicProfile()</td></tr>
</table>

####Model\PhoneNumber
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $phone instance of PhoneNumber)</th></tr>
<tr><td>DisplayName</td><td>$phone->getDisplayName()</td></tr>
<tr><td>CountryCode</td><td>$phone->getCountryCode()</td></tr>
<tr><td>NationalNumber</td><td>$phone->getNationalNumber()</td></tr>
<tr><td>IsMobile</td><td>$phone->getIsMobile()</td></tr>
<tr><td>IsPrimary</td><td>$phone->getIsPrimary()</td></tr>
<tr><td>Verified</td><td>$phone->getVerified()</td></tr>
</table>

####Model\PostalAddress
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $address instance of PostalAddress)</th></tr>
<tr><td>House</td><td>$address->getHouse()</td></tr>
<tr><td>Line1</td><td>$address->getLine1()</td></tr>
<tr><td>Line2</td><td>$address->getLine2()</td></tr>
<tr><td>City</td><td>$address->getCity()</td></tr>
<tr><td>Region</td><td>$address->getRegion()</td></tr>
<tr><td>Code</td><td>$address->getCode()</td></tr>
<tr><td>Country</td><td>$address->getCountry()</td></tr>
<tr><td>IsPrimary</td><td>$address->getIsPrimary()</td></tr>
<tr><td>Verified</td><td>$address->getVerified()</td></tr>
</table>

####Model\WebProperty
<table>
<tr><th>API data-type property</td><th>PHP equivalent (given $property instance of WebProperty)</th></tr>
<tr><td>DisplayName</td><td>$property->getDisplayName()</td></tr>
<tr><td>Identifier</td><td>$property->getIdentifier()</td></tr>
<tr><td>Type</td><td>$property->getType()</td></tr>
<tr><td>Verified</td><td>$property->getVerified()</td></tr>
</table>

####Model\WebPropertyType enumeration type
<table>
<tr><th>API data-type property</td><th>PHP equivalent</th></tr>
<tr><td>Domain</td><td>WebPropertyType::DOMAIN</td></tr>
<tr><td>Website</td><td>WebPropertyType::WEBSITE</td></tr>
</table>

##Dependencies
For performing OAuth operations we include [Andy Smith](http://term.ie/blog/)'s PHP OAuth library, available under the MIT licence. For more information see its [Google Code page](http://oauth.googlecode.com/svn/code/php/).

Some automated tests are provided that exercise the library's mapping from JSON to corresponding PHP objects, available in the miiCard.Consumers/test folder - these depend upon [PHPUnit](https://github.com/sebastianbergmann/phpunit/), though if you don't want to run the test suite then this isn't necessary.

##A note on certificates
We use CURL to make HTTP requests in this wrapper, enabling the hostname-validation and peer-validation features. To allow peer validation to work we have bundled the certificate chain for the server hosting the miiCard API and OAuth endpoints, sts.miicard.com. The certificate chain is valid until 2014.

##Contributing
* Use GitHub issue tracking to report bugs in the library
* If you're going to submit a patch, please base it off the development branch if available
* Join the [miiCard.com developer forums](http://devforum.miicard.com) to keep up to date with the latest releases and planned changes

##Licence
Copyright (c) 2012, miiCard Limited All rights reserved.

http://opensource.org/licenses/BSD-3-Clause

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

- Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

- Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

- Neither the name of miiCard Limited nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.