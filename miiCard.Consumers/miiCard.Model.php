<?php
class MiiApiCallStatus
{
    const SUCCESS = 0;
    const FAILURE = 1;
}

class MiiApiErrorCode
{
    const SUCCESS = 0;
    const ACCESS_REVOKED = 100;
    const USER_SUBSCRIPTION_LAPSED = 200;
    const EXCEPTION = 10000;
}

class WebPropertyType
{
    const DOMAIN = 0;
    const WEBSITE = 1;
}

class Claim
{
    private $_verified = false;
    
    function __construct($verified)
    {
        $this->verified = $verified;        
    }
    
    public function getVerified() { return $this->verified; }
}

class Identity extends Claim
{
    private $_source, $userId, $profileUrl;
    
    function __construct($verified, $source, $userId, $profileUrl)
    {
        parent::__construct($verified);
        
        $this->_source = $source;
        $this->_userId = $userId;
        $this->_profileUrl = $profileUrl;
    }
    
    public static function FromHash($hash)
    {
        return new Identity
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'Source'),
            Util::TryGet($hash, 'UserId'),
            Util::TryGet($hash, 'ProfileUrl')
        );
    }
    
    public function getSource() { return $this->_source; }
    public function getUserId() { return $this->_userId; }
    public function getProfileUrl() { return $this->_profileUrl; }
}

class EmailAddress extends Claim
{
    private $_displayName, $_address, $_isPrimary;
    
    function __construct($verified, $displayName, $address, $isPrimary)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_address = $address;
        $this->_isPrimary = $isPrimary;
    } 

    public static function FromHash($hash)
    {
        return new EmailAddress
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'DisplayName'),
            Util::TryGet($hash, 'Address'),
            Util::TryGet($hash, 'IsPrimary')
        );
    }
    
    public function getDisplayName() { return $this->_displayName; }
    public function getAddress() { return $this->_address; }
    public function getIsPrimary() { return $this->_isPrimary; }
}

class PhoneNumber extends Claim
{
    private $_displayName, $_countryCode, $_nationalNumber, $_isMobile, $_isPrimary;
    
    function __construct($verified, $displayName, $countryCode, $nationalNumber, $isMobile, $isPrimary)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_countryCode = $countryCode;
        $this->_nationalNumber = $nationalNumber;
        $this->_isMobile = $isMobile;
        $this->_isPrimary = $isPrimary;
    }

    public static function FromHash($hash)
    {
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
    
    public function getDisplayName() { return $this->_displayName; }
    public function getCountryCode() { return $this->_countryCode; }
    public function getNationalNumber() { return $this->_nationalNumber; }
    public function getIsMobile() { return $this->_isMobile; }
    public function getIsPrimary() { return $this->_isPrimary; }
}

class PostalAddress extends Claim
{
    private $_house, $_line1, $_line2, $_city, $_region, $_code, $_country, $_isPrimary;
    
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

    public static function FromHash($hash)
    {
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
    
    public function getHouse() { return $this->_house; }
    public function getLine1() { return $this->_line1; }
    public function getLine2() { return $this->_line2; }
    public function getCity() { return $this->_city; }
    public function getRegion() { return $this->_region; }
    public function getCode() { return $this->_code; }
    public function getCountry() { return $this->_country; }
    public function getIsPrimary() { return $this->_isPrimary; }
}

class WebProperty extends Claim
{
    private $_displayName, $_identifier, $_type;
    
    function __construct($verified, $displayName, $identifier, $type)
    {
        parent::__construct($verified);
        
        $this->_displayName = $displayName;
        $this->_identifier = $identifier;
        $this->_type = $type;
    }

    public static function FromHash($hash)
    {
        return new WebProperty
        (
            Util::TryGet($hash, 'Verified'),
            Util::TryGet($hash, 'DisplayName'),
            Util::TryGet($hash, 'Identifier'),
            Util::TryGet($hash, 'Type')
        );
    }
    
    public function getDisplayName() { return $this->_displayName; }
    public function getIdentifier() { return $this->_identifier; }
    public function getType() { return $this->_type; }
}

class MiiUserProfile
{
    private $_username, $_salutation, $_firstName, $_middleName, $_lastName;
    private $_previousFirstName, $_previousMiddleName, $_previousLastName;
    private $_lastVerified, $_profileUrl, $_profileShortUrl, $_cardImageUrl;
    private $_emailAddresses, $_identities, $_phoneNumbers, $_postalAddresses, $webProperties;
    private $_identityAssured, $_hasPublicProfile, $_publicProfile;
    
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

    public function getUsername() { return $this->_username; }
    public function getSalutation() { return $this->_salutation; }
    public function getFirstName() { return $this->_firstName; }
    public function getMiddleName() { return $this->_middleName; }
    public function getLastName() { return $this->_lastName; }

    public function getPreviousFirstName() { return $this->_previousFirstName; }
    public function getPreviousMiddleName() { return $this->_previousMiddleName; }
    public function getPreviousLastName() { return $this->_previousLastName; }

    public function getLastVerified() { return $this->_lastVerified; }
    public function getProfileUrl() { return $this->_profileUrl; }
    public function getProfileShortUrl() { return $this->_profileShortUrl; }
    public function getCardImageUrl() { return $this->_cardImageUrl; }

    public function getEmailAddresses() { return $this->_emailAddresses; }
    public function getIdentities() { return $this->_identities; }
    public function getPhoneNumbers() { return $this->_phoneNumbers; }
    public function getPostalAddresses() { return $this->_postalAddresses; }
    public function getWebProperties() { return $this->_webProperties; }

    public function getIdentityAssured() { return $this->_identityAssured; }
    public function getHasPublicProfile() { return $this->_hasPublicProfile; }
    public function getPublicProfile() { return $this->_publicProfile; }
    
    public static function FromHash($hash)
    {
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
    		Util::TryGet($hash, 'LastVerified'),
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

class MiiApiResponse
{
    private $_status, $_errorCode, $_errorMessage, $_data;

    function __construct($status, $errorCode, $errorMessage, $data)
    {
        $this->_status = $status;
        $this->_errorCode = $errorCode;
        $this->_errorMessage = $errorMessage;

        $this->_data = $data;
    }

    public function getStatus() { return $this->_status; }
    public function getErrorCode() { return $this->_errorCode; }
    public function getErrorMessage() { return $this->_errorMessage; }
    public function getData() { return $this->_data; }

    public static function FromHash($hash, $dataProcessor)
    {
        $payloadJson = Util::TryGet($hash, 'Data');
        $payload = null;

        if (isset($payloadJson) && isset($dataProcessor))
        {
            $payload = call_user_func($dataProcessor, $payloadJson);
        }
        else if (isset($payloadJson))
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

class Util
{
    public static function TryGet($hash, $key)
    {
        if (isset($hash[$key]))
        {
            return $hash[$key];
        }
        else
        {
            return null;
        }
    }
}
?>