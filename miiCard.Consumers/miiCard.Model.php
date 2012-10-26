<?php
/** @package miiCard.Consumers
 */

/** Represents different possible statuses returned by the miiCard API.
 *
 *@package miiCard.Consumers */
class MiiApiCallStatus
{
    /** Signifies that the API call was successful. */
    const SUCCESS = 0;
    /** Signifies that the API call failed. More detail may be found by
    interrogating the MiiApiResponse's getErrorCode function. */
    const FAILURE = 1;
}

/** Represents the specific error type returned by the miiCard API.
 *@package miiCard.Consumers */
class MiiApiErrorCode
{
    /** Signifies that there was no error. */
    const SUCCESS = 0;
    /** Signifies that the miiCard member has revoked access to your application. Your application
     *needs to make the member go through the OAuth process again to obtain fresh access tokens if
     *you wish to use the API on their behalf. */
    const ACCESS_REVOKED = 100;
    /** Signifies that the user no longer has a paid-up subscription, and thus cannot share their
     *identity with your application. */
    const USER_SUBSCRIPTION_LAPSED = 200;

    /** Signifies that your account has not been enabled for transactional support. */
    const TRANSACTIONAL_SUPPORT_DISABLED = 1000;
    /** Signifies that your account's transactional support status is development-only. This is the
     *case when your application hasn't yet been made live in the miiCard system, for example
     *while we process your billing details and perform final checks. */
    const DEVELOPMENT_TRANSACTIONAL_SUPPORT_ONLY = 1010;
    /** Signifies that the snapshot ID supplied to a snapshot-based API method was either invalid
     * or corresponded to a user for which authorisation tokens didn't match. */
    const INVALID_SNAPSHOT_ID = 1020;

    /** Signifies that a more general exception took place during the call. Further information may
     *be available by calling the MiiApiResponse's getErrorMessage() function. */
    const EXCEPTION = 10000;
}

/** Details the kind of web property a user has linked to their miiCard profile. Used by the
 *WebProperty class.
 *@package miiCard.Consumers */
class WebPropertyType
{
    /** The WebProperty object describes a domain name. */
    const DOMAIN = 0;
    /** The WebProperty object describes a website. */
    const WEBSITE = 1;
}

/** Base class of all verifiable information supplied by the miiCard API.
 *@abstract
 *@package miiCard.Consumers */
abstract class Claim
{
    /** @access private */
    private $_verified = false;
    
    /** Initialises a new Claim.
     *
     *@param bool $verified Indicates whether the piece of information has been verified by miiCard or not.
     *
     *A fuller discussion of verified vs unverified information can be found at the miiCard.com API documentation entry for
     *the GetClaims method.
     *@link http://www-test.miicard.com/developers/claims-api#GetClaims GetClaims API documentation. */
    function __construct($verified)
    {
        $this->verified = $verified;        
    }
    
    /** Gets whether the piece of information has been verified by miiCard or not.
     *
     *A fuller discussion of verified vs unverified information can be found at the miiCard.com API documentation entry for
     *the GetClaims method.
     *@link http://www-test.miicard.com/developers/claims-api#GetClaims GetClaims API documentation. */
    public function getVerified() { return $this->verified; }
}

/** Represents the miiCard member's account details on another website, such as a social media site.
 *@package miiCard.Consumers */
class Identity extends Claim
{
    /** @access private */
    private $_source, $userId, $profileUrl;
    
    /** Initialises a new Identity object.
     *
     *@param bool $verified Indicates whether the identity has been verified.
     *@param string $source The source of the identity, i.e. the name of the provider.
     *@param string $userId The miiCard member's user ID on the provider site.
     *@param string $profileUrl The miiCard member's public profile URL on the provider
     *site, if known. */
    function __construct($verified, $source, $userId, $profileUrl)
    {
        parent::__construct($verified);
        
        $this->_source = $source;
        $this->_userId = $userId;
        $this->_profileUrl = $profileUrl;
    }
    
    /** Builds a new Identity from a hash obtained from the Claims API.
     *
     *@param array $hash The hash containing details about a single identity. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
        	return null;
        }

        return new Identity
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'Source'),
            Util::TryGet($hash, 'UserId'),
            Util::TryGet($hash, 'ProfileUrl')
        );
    }
    
    /** Gets the source of the identity, i.e. the name of the provider. */
    public function getSource() { return $this->_source; }
    /** Gets the miiCard member's user ID on the provider site. */
    public function getUserId() { return $this->_userId; }
    /** Gets the miiCard member's public profile URL on the provider site, if known. */
    public function getProfileUrl() { return $this->_profileUrl; }
}

/** Represents an email address that the miiCard member has linked to their profile.
 *@package miiCard.Consumers */
class EmailAddress extends Claim
{
    /** @access private */
    private $_displayName, $_address, $_isPrimary;
    
    /** Initialises a new EmailAddress object.
     *
     *@param bool $verified Indicates whether the identity has been verified.
     *@param string $displayName The name that the user has used to describe this email
     *address.
     *@param string $address The email address.
     *@param bool $isPrimary Indicates whether this is the user's primary email address
     *on the miiCard service. */
    function __construct($verified, $displayName, $address, $isPrimary)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_address = $address;
        $this->_isPrimary = $isPrimary;
    } 

    /** Builds a new EmailAddress from a hash obtained from the Claims API.
     *
     *@param array $hash The hash containing details about a single email address. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        return new EmailAddress
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'DisplayName'),
            Util::TryGet($hash, 'Address'),
            Util::TryGet($hash, 'IsPrimary')
        );
    }
    
    /** Gets the name that the user has used to describe this email address. */
    public function getDisplayName() { return $this->_displayName; }
    /** Gets the email address. */
    public function getAddress() { return $this->_address; }
    /** Indicates whether this is the user's primary email address on the miiCard service. */
    public function getIsPrimary() { return $this->_isPrimary; }
}

/** Represents a phone number that the user has linked to their miiCard profile.
 *@package miiCard.Consumers */
class PhoneNumber extends Claim
{
    /** @access private */
    private $_displayName, $_countryCode, $_nationalNumber, $_isMobile, $_isPrimary;
    
    /** Initialises a new PhoneNumber object.
     *
     *@param bool $verified Indicates whether the phone number has been verified.
     *@param string $displayName The name that the user has given to describe this phone number.
     *@param string $countryCode The ITU-T E. 164 country code of the phone number.
     *@param string $nationalNumber The national component of the phone number.
     *@param bool $isMobile Indicates whether the number relates to a mobile phone or not.
     *@param bool $isPrimary Indicates whether this is the user's primary phone number on the
     *miiCard service. */
    function __construct($verified, $displayName, $countryCode, $nationalNumber, $isMobile, $isPrimary)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_countryCode = $countryCode;
        $this->_nationalNumber = $nationalNumber;
        $this->_isMobile = $isMobile;
        $this->_isPrimary = $isPrimary;
    }

    /** Builds a new PhoneNumber from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single phone number. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        return new PhoneNumber
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'DisplayName'),
            Util::TryGet($hash, 'CountryCode'),
            Util::TryGet($hash, 'NationalNumber'),
            Util::TryGet($hash, 'IsMobile'),
            Util::TryGet($hash, 'IsPrimary')
        );
    }
    
    /** Gets the name that the user has given to describe this phone number. */
    public function getDisplayName() { return $this->_displayName; }
    /** Gets the ITU-T E. 164 country code of the phone number. */
    public function getCountryCode() { return $this->_countryCode; }
    /** Gets the national component of the phone number. */
    public function getNationalNumber() { return $this->_nationalNumber; }
    /** Gets whether the number relates to a mobile phone or not. */
    public function getIsMobile() { return $this->_isMobile; }
    /** Gets whether this is the user's primary phone number on the miiCard service. */
    public function getIsPrimary() { return $this->_isPrimary; }
}

/** Represents a postal address that the user has linked to their miiCard profile.
 *@package miiCard.Consumers */
class PostalAddress extends Claim
{
    /** @access private */
    private $_house, $_line1, $_line2, $_city, $_region, $_code, $_country, $_isPrimary;
    
    /** Initialises a new PostalAddress object.
     *
     *@param bool $verified Indicates whether the address has been verified.
     *@param string $house The name or number of the building referred to by the address.
     *@param string $line1 The first line of the address.
     *@param string $line2 The second line of the address.
     *@param string $city The city of the address.
     *@param string $region The region (for example, county, state or department) of the address.
     *@param string $code The postal code of the address.
     *@param string $country The country of the address.
     *@param boolean $isPrimary Indicates whether this is the user's primary postal address on the miiCard service. */
    function __construct($verified, $house, $line1, $line2, $city, $region, $code, $country, $isPrimary)
    {
        parent::__construct($verified);
        
        $this->_house = $house;
        $this->_line1 = $line1;
        $this->_line2 = $line2;
        $this->_city = $city;
        $this->_region = $region;
        $this->_code = $code;
        $this->_country = $country;
        $this->_isPrimary = $isPrimary;
    }

    /** Builds a new PostalAddress from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single postal address. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        return new PostalAddress
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'House'),
            Util::TryGet($hash, 'Line1'),
            Util::TryGet($hash, 'Line2'),
            Util::TryGet($hash, 'City'),
            Util::TryGet($hash, 'Region'),
            Util::TryGet($hash, 'Code'),
            Util::TryGet($hash, 'Country'),
            Util::TryGet($hash, 'IsPrimary')
        );
    }

    /** Gets the name or number of the building referred to by the address. */
    public function getHouse() { return $this->_house; }
    /** Gets the first line of the address.*/
    public function getLine1() { return $this->_line1; }
    /** Gets the second line of the address.*/
    public function getLine2() { return $this->_line2; }
    /** Gets the city of the address.*/
    public function getCity() { return $this->_city; }
    /** Gets the region (for example, county, state or department) of the address.*/
    public function getRegion() { return $this->_region; }
    /** Gets the postal code of the address.*/
    public function getCode() { return $this->_code; }
    /** Gets the country of the address.*/
    public function getCountry() { return $this->_country; }
    /** Gets whether this is the user's primary postal address on the miiCard service. */
    public function getIsPrimary() { return $this->_isPrimary; }
}

/** Represents a web property that the member has linked to their miiCard profile,
 *for example a domain name or web page.
 *@package miiCard.Consumers */
class WebProperty extends Claim
{
    /** @access private */
    private $_displayName, $_identifier, $_type;
    
    /** Initialises a new WebProperty object.
     *
     *@param bool $verified Indicates whether the web property has been verified.
     *@param string $displayName The display name that the user has given the web property.
     *@param string $identifier The identifier of the property, which will be specific to the type of
     *property being describes. For domain-type properties this will be the domain name, while for
     *site-type properties this will be a URL to a page or site.
     *@param int $type The type of web property has that been linked. Corresponds to a constant from the
     *WebPropertyType class. */
    function __construct($verified, $displayName, $identifier, $type)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_identifier = $identifier;
        $this->_type = $type;
    }

    /** Builds a new WebProperty from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single web property. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        return new WebProperty
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'DisplayName'),
            Util::TryGet($hash, 'Identifier'),
            Util::TryGet($hash, 'Type')
        );
    }
    
    /** Gets the display name that the user has given the web property.*/
    public function getDisplayName() { return $this->_displayName; }
    /** Gets the identifier of the property, which will be specific to the type of
     * property being describes. For domain-type properties this will be the domain name, while for
     * site-type properties this will be a URL to a page or site. */
    public function getIdentifier() { return $this->_identifier; }
    /** Gets the type of web property has that been linked. Corresponds to a constant from the
     *WebPropertyType class.*/
    public function getType() { return $this->_type; }
}

/** Represents the subset of the miiCard member's identity that they have agreed
 * to share with your application.
 *@package miiCard.Consumers */
class MiiUserProfile
{
    /** @access private */
    private $_username, $_salutation, $_firstName, $_middleName, $_lastName;
    /** @access private */
    private $_previousFirstName, $_previousMiddleName, $_previousLastName;
    /** @access private */
    private $_lastVerified, $_profileUrl, $_profileShortUrl, $_cardImageUrl;
    /** @access private */
    private $_emailAddresses, $_identities, $_phoneNumbers, $_postalAddresses, $webProperties;
    /** @access private */
    private $_identityAssured, $_hasPublicProfile, $_publicProfile;
    
    /** Initialises a new MiiUserProfile object.
     * @param string $username The miiCard username of the member.
     * @param string $salutation The salutation of the member (e.g. 'Mr', 'Mrs' etc).
     * @param string $firstName The first name of the member
     * @param string $middleName The middle name of the member, if known.
     * @param string $lastName The last name of the member.
     * @param string $previousFirstName The previous first name of the member, if known.
     * @param string $previousMiddleName The previous middle name of the member, if known.
     * @param string $previousLastName The previous last name of the member, if known.
     * @param int $lastVerified The UNIX timestamp representing the date the user's identity was last verified.
     * @param string $profileUrl The URL to the member's public miiCard profile page.
     * @param string $profileShortUrl The short URL to the member's public miiCard profile page.
     * @param string $cardImageUrl The URL to the user's miiCard card image, as shown on their public
     * profile page.
     * @param array $emailAddresses An array of EmailAddress objects associated with the member.
     * @param array $identities An array of alternative identities for the member, for example on social
     * media sites.
     * @param array $phoneNumbers An array of PhoneNumber objects associated with the member.
     * @param array $postalAddresses An array of PostalAddress objects associated with the member.
     * @param array $webProperties An array of WebProperty objects associated with the member.
     * @param bool $identityAssured Indicates whether the member has met the level of identity
     * assurance required by your application.
     * @param bool $hasPublicProfile Indicates whether the user has published their public profile. If false,
     * the card image and profile URLs are assumed to not resolve.
     * @param MiiUserProfile $publicProfile A MiiUserProfile object representing the subset of identity
     * information that they have made publicly available on their profile page.
     */
    function __construct($username, $salutation, $firstName, $middleName, $lastName, 
                         $previousFirstName, $previousMiddleName, $previousLastName,
                         $lastVerified, $profileUrl, $profileShortUrl, $cardImageUrl,
                         $emailAddresses, $identities, $phoneNumbers, $postalAddresses,
                         $webProperties, $identityAssured, $hasPublicProfile, $publicProfile)
    {
        $this->_username = $username;
        $this->_salutation = $salutation;
        
        $this->_firstName = $firstName;
        $this->_middleName = $middleName;
        $this->_lastName = $lastName;
        
        $this->_previousFirstName = $previousFirstName;
        $this->_previousMiddleName = $previousMiddleName;
        $this->_previousLastName = $previousLastName;
        
        $this->_lastVerified = $lastVerified;
        $this->_profileUrl = $profileUrl;
        $this->_profileShortUrl = $profileShortUrl;
        $this->_cardImageUrl = $cardImageUrl;
        
        $this->_emailAddresses = $emailAddresses;
        $this->_identities = $identities;
        $this->_phoneNumbers = $phoneNumbers;
        $this->_postalAddresses = $postalAddresses;
        $this->_webProperties = $webProperties;
        
        $this->_identityAssured = $identityAssured;
        $this->_hasPublicProfile = $hasPublicProfile;
        $this->_publicProfile = $publicProfile;        
    }

    /** Gets the miiCard username of the member.*/
    public function getUsername() { return $this->_username; }
    /** Gets the salutation of the member (e.g. 'Mr', 'Mrs' etc).*/
    public function getSalutation() { return $this->_salutation; }
    /** Gets the first name of the member*/
    public function getFirstName() { return $this->_firstName; }
    /** Gets the middle name of the member, if known.*/
    public function getMiddleName() { return $this->_middleName; }
    /** Gets the last name of the member, if known. */
    public function getLastName() { return $this->_lastName; }

    /** Gets the previous first name of the member, if known.*/
    public function getPreviousFirstName() { return $this->_previousFirstName; }
    /** Gets the previous middle name of the member, if known.*/
    public function getPreviousMiddleName() { return $this->_previousMiddleName; }
    /** Gets the previous last name of the member, if known.*/
    public function getPreviousLastName() { return $this->_previousLastName; }

    /** Gets the UNIX timestamp representing the date the user's identity was last verified.*/
    public function getLastVerified() { return $this->_lastVerified; }
    /** Gets the URL to the member's public miiCard profile page.*/
    public function getProfileUrl() { return $this->_profileUrl; }
    /** Gets the short URL to the member's public miiCard profile page.*/
    public function getProfileShortUrl() { return $this->_profileShortUrl; }
    /** Gets the URL to the user's miiCard card image, as shown on their public
     * profile page.*/
    public function getCardImageUrl() { return $this->_cardImageUrl; }

    /** Gets the array of EmailAddress objects associated with the member.*/
    public function getEmailAddresses() { return $this->_emailAddresses; }
    /** Gets the array of alternative identities for the member, for example on social
     * media sites.*/
    public function getIdentities() { return $this->_identities; }
    /** Gets the array of PhoneNumber objects associated with the member.*/
    public function getPhoneNumbers() { return $this->_phoneNumbers; }
    /** Gets the array of PostalAddress objects associated with the member.*/
    public function getPostalAddresses() { return $this->_postalAddresses; }
    /** Gets the array of WebProperty objects associated with the member.*/
    public function getWebProperties() { return $this->_webProperties; }

    /** Gets whether the member has met the level of identity
     * assurance required by your application. */
    public function getIdentityAssured() { return $this->_identityAssured; }
    /** Gets whether the user has published their public profile. If false,
     * the card image and profile URLs are assumed to not resolve. */
    public function getHasPublicProfile() { return $this->_hasPublicProfile; }
    /** Gets the MiiUserProfile object representing the subset of identity
     * information that they have made publicly available on their profile page.*/
    public function getPublicProfile() { return $this->_publicProfile; }
    
    /** Builds a new MiiUserProfile from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single user profile. */
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        $emails = Util::TryGet($hash, 'EmailAddresses');
        $emailsParsed = array();
        if (isset($emails) && is_array($emails))
        {
            foreach ($emails as $email)
            {
                array_push($emailsParsed, EmailAddress::FromHash($email));
            }
        }

        $identities = Util::TryGet($hash, 'Identities');
        $identitiesParsed = array();
        if (isset($identities) && is_array($identities))
        {
            foreach ($identities as $identity)
            {
                array_push($identitiesParsed, Identity::FromHash($identity));
            }
        }

        $phoneNumbers = Util::TryGet($hash, 'PhoneNumbers');
        $phoneNumbersParsed = array();
        if (isset($phoneNumbers) && is_array($phoneNumbers))
        {
            foreach ($phoneNumbers as $phoneNumber)
            {
                array_push($phoneNumbersParsed, PhoneNumber::FromHash($phoneNumber));
            }
        }

        $webProperties = Util::TryGet($hash, 'WebProperties');
        $webPropertiesParsed = array();
        if (isset($webProperties) && is_array($webProperties))
        {
            foreach ($webProperties as $webProperty)
            {
                array_push($webPropertiesParsed, WebProperty::FromHash($webProperty));
            }
        }

        $postalAddresses = Util::TryGet($hash, 'PostalAddresses');
        $postalAddressesParsed = array();
        if (isset($postalAddresses) && is_array($postalAddresses))
        {
            foreach ($postalAddresses as $postalAddress)
            {
                array_push($postalAddressesParsed, PostalAddress::FromHash($postalAddress));
            }
        }

        $publicProfile = Util::TryGet($hash, 'PublicProfile');
        $publicProfileParsed = null;
        if (isset($publicProfile) && is_array($publicProfile))
        {
            $publicProfileParsed = MiiUserProfile::FromHash($publicProfile);
        }

        // Try parsing the last-verified as a timestamp
        preg_match( '/\/Date\((\d+)\)/', Util::TryGet($hash, 'LastVerified'), $matches);

        $lastVerifiedParsed = null;
        if (isset($matches) && count($matches) > 1)
        {
          $lastVerifiedParsed = ($matches[1] / 1000);
        }

       	return new MiiUserProfile
        (
    		Util::TryGet($hash, 'Username'),
    		Util::TryGet($hash, 'Salutation'),
    		Util::TryGet($hash, 'FirstName'),
    		Util::TryGet($hash, 'MiddleName'),
    		Util::TryGet($hash, 'LastName'),
    		Util::TryGet($hash, 'PreviousFirstName'),
    		Util::TryGet($hash, 'PreviousMiddleName'),
    		Util::TryGet($hash, 'PreviousLastName'),
    		$lastVerifiedParsed,
    		Util::TryGet($hash, 'ProfileUrl'),
    		Util::TryGet($hash, 'ProfileShortUrl'),
    		Util::TryGet($hash, 'CardImageUrl'),
    		$emailsParsed,
    		$identitiesParsed,
    		$phoneNumbersParsed,
    		$postalAddressesParsed,
    		$webPropertiesParsed,
    		Util::TryGet($hash, 'IdentityAssured'),
    		Util::TryGet($hash, 'HasPublicProfile'),
    		$publicProfileParsed
        );
    }
}

/** Represents the metadata associated with a snapshot of a single miiCard member's
 *details.
 *@package miiCard.Consumers */
class IdentitySnapshotDetails
{
    private $_snapshotId, $_username, $_timestampUtc;

    /** Initialises a new IdentitySnapshotDetails object.
     *
     *@param string $snapshotId The unique identifier for this snapshot.
     *@param string $username The username of the miiCard member of whose data
     *the snapshot was taken.
     *@param int $timestampUtc The UNIX timestamp representing the date the
     *snapshot of the user's identity was taken. */
    function __construct($snapshotId, $username, $timestampUtc)
    {
        $this->_snapshotId = $snapshotId;
        $this->_username = $username;
        $this->_timestampUtc = $timestampUtc;
    }

    /** Gets the unique identifier for this snapshot. */
    public function getSnapshotId() { return $this->_snapshotId; }
    /** Gets the username of the miiCard member of whose data
     *the snapshot was taken. */
    public function getUsername() { return $this->_username; }
    /** Gets the UNIX timestamp representing the date the
     *snapshot of the user's identity was taken. */
    public function getTimestampUtc() { return $this->_timestampUtc; }

    /** Builds a new IdentitySnapshotDetails from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single snapshot.*/
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        // Try parsing the last-verified as a timestamp
        preg_match( '/\/Date\((\d+)\)/', Util::TryGet($hash, 'TimestampUtc'), $matches);

        $timestampUtcParsed = null;
        if (isset($matches) && count($matches) > 1)
        {
            $timestampUtcParsed = ($matches[1] / 1000);
        }

        return new IdentitySnapshotDetails
        (
            Util::TryGet($hash, 'SnapshotId'),
            Util::TryGet($hash, 'Username'),
            $timestampUtcParsed
        );
    }
}

/** Represents a single snapshot of miiCard member's identity.
 *@package miiCard.Consumers */
class IdentitySnapshot
{
    private $_details, $_snapshot;

    /** Initialises a new IdentitySnapshot object.
     *
     *@param IdentitySnapshotDetails $details An IdentitySnapshotDetails object
     *that describes the contents of the snapshot.
     *@param MiiUserProfile $snapshot The MiiUserProfile object containing the
     *miiCard member's identity at the point the snapshot was taken. */
    function __construct($details, $snapshot)
    {
        $this->_details = $details;
        $this->_snapshot = $snapshot;
    }

    /** Gets the IdentitySnapshotDetails object
     *that describes the contents of the snapshot. */
    public function getDetails() { return $this->_details; }
    /** Gets the MiiUserProfile object containing the
     *miiCard member's identity at the point the snapshot was taken. */
    public function getSnapshot() { return $this->_snapshot; }

    /** Builds a new IdentitySnapshot from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about the identity snapshot.
     **/
    public static function FromHash($hash)
    {
        if (!isset($hash))
        {
            return null;
        }

        return new IdentitySnapshot
        (
            IdentitySnapshotDetails::FromHash(Util::TryGet($hash, 'Details')),
            MiiUserProfile::FromHash(Util::TryGet($hash, 'Snapshot'))
        );
    }
}

/** A wrapper around most responses to miiCard Claims API calls, detailing
 *the success or failure of the call and containing additional information
 *to help diagnose issues.
 *@package miiCard.Consumers */
class MiiApiResponse
{
    /** @access private */
    private $_status, $_errorCode, $_errorMessage, $_data;

    /** Initialises a new MiiApiResponse object.
     *
     *@param int $status The overall status of the API call, linked to one of the
     *constants of the MiiApiCallStatus class.
     *@param int $errorCode The error code that describes any error that occurred
     *during the API call, linked to one of the constants of the MiiApiErrorCode
     *class.
     *@param string $errorMessage Additional error message optionally supplied for
     *certain types of API error. Intended for diagnostics only and strictly not
     *suitable for display to the public.
     *@param mixed $data The payload of the response, whose type will vary depending
     *on the API method being called. */
    function __construct($status, $errorCode, $errorMessage, $data)
    {
        $this->_status = $status;
        $this->_errorCode = $errorCode;
        $this->_errorMessage = $errorMessage;

        $this->_data = $data;
    }

    /** Gets the overall status of the API call, linked to one of the
     *constants of the MiiApiCallStatus class.*/
    public function getStatus() { return $this->_status; }
    /** Gets the error code that describes any error that occurred
     *during the API call, linked to one of the constants of the MiiApiErrorCode
     *class.*/
    public function getErrorCode() { return $this->_errorCode; }
    /** Gets the additional error message optionally supplied for
     *certain types of API error. Intended for diagnostics only and strictly not
     *suitable for display to the public.*/
    public function getErrorMessage() { return $this->_errorMessage; }
    /** Gets the payload of the response, whose type will vary depending
     *on the API method being called. */
    public function getData() { return $this->_data; }

    /** Builds a new MiiApiResponse from a hash obtained from the Claims API.
     *
     *@param array $hash The has containing details about a single API call response.
     *@param string A callable that turns the JSON data payload into a PHP object, if
     *required.
     *@param bool $isArrayPayload Indicates that the payload of the response is an array-type,
     *over which the $dataProcessor callable should be iteratively evaluated. */
    public static function FromHash($hash, $dataProcessor, $isArrayPayload = false)
    {
        if (!isset($hash))
        {
            return null;
        }

        $payloadJson = Util::TryGet($hash, 'Data');
        $payload = null;

        if ($dataProcessor !== null)
        {
            if ($isArrayPayload === true)
            {
                if (isset($payloadJson))
                {
                  $payload = array();
                  foreach ($payloadJson as $item)
                  {
                      array_push($payload, call_user_func($dataProcessor, $item));
                  }
                }
            }
            else
            {
                $payload = call_user_func($dataProcessor, $payloadJson);
            }
        }
        else if ($payloadJson !== null)
        {
            $payload = $payloadJson;
        }

        return new MiiApiResponse
        (
            Util::TryGet($hash, 'Status'),
            Util::TryGet($hash, 'ErrorCode'),
            Util::TryGet($hash, 'ErrorMessage'),
            $payload
        );
    }
}

/** General utilities class
 *@package miiCard.Consumers */
class Util
{
    /** Attempts to get a value from an associative array, returning
     *null if the key doesn't exist
     *@param array $hash The associative array.
     *@param string $key The key whose value is to be returned if available. */
    public static function TryGet($hash, $key)
    {
        if (!isset($hash) || !is_array($hash) || !array_key_exists($key, $hash))
        {
            return false;
        }

        return $hash[$key];
    }
}
?>