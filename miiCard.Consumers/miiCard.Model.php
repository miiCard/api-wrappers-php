<?php

/**
 * @file
 * Wrapper classes around objects returned from the miiCard API.
 */

/**
 * @package MiiCardConsumers
 */

/**
 * Represents different possible statuses returned by the miiCard API.
 *
 * @package MiiCardConsumers
 */
class MiiApiCallStatus {
  /** Signifies that the API call was successful. */
  const SUCCESS = 0;

  /** Signifies that the API call failed.
   *
   *  More detail may be found by interrogating the MiiApiResponse's
   *  getErrorCode function.
   */
  const FAILURE = 1;
}

/** Represents the specific error type returned by the miiCard API.
 *@package MiiCardConsumers */
class MiiApiErrorCode {
  /** Signifies that there was no error. */
  const SUCCESS = 0;
  /**
   * Signifies that the miiCard member has revoked access to your app.
   *
   * Your application needs to make the member go through the OAuth process
   * again to obtain fresh access tokens if you wish to use the API on their
   * behalf.
   */
  const ACCESS_REVOKED = 100;

  /**
   * Signifies that the user can't share data due to a lapsed subscription.
   */
  const USER_SUBSCRIPTION_LAPSED = 200;

  /** Signifies that your account is not on the transactional model. */
  const TRANSACTIONAL_SUPPORT_DISABLED = 1000;

  /**
   * Signifies that your account's transactional status is development-only.
   *
   * This is the case when your application hasn't yet been made live in the
   * miiCard system, for example while we process your billing details and
   * perform final checks.
   */
  const DEVELOPMENT_TRANSACTIONAL_SUPPORT_ONLY = 1010;

  /**
   * Signifies that the snapshot ID was either invalid.
   *
   * The ID may not exist, or doesn't belong to the user whose access tokens
   * were used to make the API request. */
  const INVALID_SNAPSHOT_ID = 1020;

  /**
   * Signifies that your application has been suspended.
   *
   * No API access can take place until miiCard releases the suspension on
   * your application.
   */
  const BLACKLISTED = 2000;

  /**
   * Signifies that your application has been disabled.
   *
   * Your applications can be disabled either by your request or by miiCard.
   * miiCard members will be unable to go through the OAuth process for
   * your application, though you will still be able to access the API.
   */
  const PRODUCT_DISABLED = 2010;

  /**
   * Signifies that your application has been deleted.
   *
   * miiCard members will be unable to go through the OAuth process for your
   * application, nor will you be able to access identity details through
   * the API.
   */
  const PRODUCT_DELETED = 2020;

  /**
   * Signifies that a more general exception took place during the call.
   *
   * Further information may be available by calling the MiiApiResponse's
   * getErrorMessage() function.
   */
  const EXCEPTION = 10000;
}

/**
 * Details the kind of web property a user has linked to their miiCard profile.
 *
 * @package MiiCardConsumers
 */
class WebPropertyType {
  /** The WebProperty object describes a domain name. */
  const DOMAIN = 0;
  /** The WebProperty object describes a website. */
  const WEBSITE = 1;
}

/**
 * Base class of all verifiable information supplied by the miiCard API.
 *
 * @abstract
 * @package MiiCardConsumers
 */
abstract class Claim {
  /** @access protected */
  protected $verified = FALSE;

  /**
   * Initialises a new Claim.
   *
   * @param bool $verified
   *   Indicates whether the piece of information has been verified by miiCard
   *   or not. A fuller discussion of verified vs unverified information can
   *   be found at the miiCard.com API documentation entry for the GetClaims
   *   method.
   *
   * @link http://www-test.miicard.com/developers/claims-api#GetClaims
   *   GetClaims API documentation.
   */
  public function __construct($verified) {
    $this->verified = $verified;
  }

  /**
   * Gets whether the piece of information has been verified by miiCard.
   *
   * @link http://www-test.miicard.com/developers/claims-api#GetClaims
   *   GetClaims API documentation.
   */
  public function getVerified() {
    return $this->verified;
  }
}

/**
 * Represents the miiCard member's account details on another website.
 *
 * @package MiiCardConsumers
 */
class Identity extends Claim {
  /** @access protected */
  protected $source;
  /** @access protected */
  protected $userId;
  /** @access protected */
  protected $profileUrl;

  /**
   * Initialises a new Identity object.
   *
   * @param bool $verified
   *   Indicates whether the identity has been verified.
   * @param string $source
   *   The source of the identity, i.e. the name of the provider.
   * @param string $user_id
   *   The miiCard member's user ID on the provider site.
   * @param string $profile_url
   *   The miiCard member's public profile URL on the provider
   *   site, if known
   */
  public function __construct($verified, $source, $user_id, $profile_url) {
    parent::__construct($verified);

    $this->source = $source;
    $this->userId = $user_id;
    $this->profileUrl = $profile_url;
  }

  /**
   * Builds a new Identity from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The hash containing details about a single identity.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new Identity(
      Util::TryGet($hash, 'Verified'),
      Util::TryGet($hash, 'Source'),
      Util::TryGet($hash, 'UserId'),
      Util::TryGet($hash, 'ProfileUrl')
    );
  }

  /**
   * Gets the source of the identity, i.e. the name of the provider.
   */
  public function getSource() {
    return $this->source;
  }

  /**
   * Gets the miiCard member's user ID on the provider site.
   */
  public function getUserId() {
    return $this->userId;
  }

  /**
   * Gets the miiCard member's public profile URL on the provider site.
   */
  public function getProfileUrl() {
    return $this->profileUrl;
  }
}

/**
 * Represents an email address belonging to the miiCard member.
 *
 * @package MiiCardConsumers
 */
class EmailAddress extends Claim {
  /** @access protected */
  protected $displayName;
  /** @access protected */
  protected $address;
  /** @access protected */
  protected $isPrimary;

  /**
   * Initialises a new EmailAddress object.
   *
   * @param bool $verified
   *   Indicates whether the identity has been verified.
   * @param string $display_name
   *   The name that the user has used to describe this email
   *   address.
   * @param string $address
   *   The email address.
   * @param bool $is_primary
   *   Indicates whether this is the user's primary email address on the
   *   miiCard service.
   */
  public function __construct($verified, $display_name, $address, $is_primary) {
    parent::__construct($verified);

    $this->displayName = $display_name;
    $this->address = $address;
    $this->isPrimary = $is_primary;
  }

  /**
   * Builds a new EmailAddress from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The hash containing details about a single email address.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new EmailAddress(
      Util::TryGet($hash, 'Verified'),
      Util::TryGet($hash, 'DisplayName'),
      Util::TryGet($hash, 'Address'),
      Util::TryGet($hash, 'IsPrimary')
    );
  }

  /**
   * Gets the name that the user has used to describe this email address.
   */
  public function getDisplayName() {
    return $this->displayName;
  }

  /**
   * Gets the email address.
   */
  public function getAddress() {
    return $this->address;
  }

  /**
   * Indicates whether this is the user's primary email address.
   */
  public function getIsPrimary() {
    return $this->isPrimary;
  }
}

/**
 * Represents a phone number that the user has linked to their miiCard profile.
 *
 * @package MiiCardConsumers
 */
class PhoneNumber extends Claim {
  /** @access protected */
  protected $displayName;
  /** @access protected */
  protected $countryCode;
  /** @access protected */
  protected $nationalNumber;
  /** @access protected */
  protected $isMobile;
  /** @access protected */
  protected $isPrimary;

  /**
   * Initialises a new PhoneNumber object.
   *
   * @param bool $verified
   *   Indicates whether the phone number has been verified.
   * @param string $display_name
   *   The name that the user has given to describe this phone number.
   * @param string $country_code
   *   The ITU-T E. 164 country code of the phone number.
   * @param string $national_number
   *   The national component of the phone number.
   * @param bool $is_mobile
   *   Indicates whether the number relates to a mobile phone or not.
   * @param bool $is_primary
   *   Indicates whether this is the user's primary phone number on the
   *   miiCard service.
   */
  public function __construct($verified, $display_name, $country_code, $national_number, $is_mobile, $is_primary) {
    parent::__construct($verified);

    $this->displayName = $display_name;
    $this->countryCode = $country_code;
    $this->nationalNumber = $national_number;
    $this->isMobile = $is_mobile;
    $this->isPrimary = $is_primary;
  }

  /**
   * Builds a new PhoneNumber from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about a single phone number.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new PhoneNumber(
      Util::TryGet($hash, 'Verified'),
      Util::TryGet($hash, 'DisplayName'),
      Util::TryGet($hash, 'CountryCode'),
      Util::TryGet($hash, 'NationalNumber'),
      Util::TryGet($hash, 'IsMobile'),
      Util::TryGet($hash, 'IsPrimary')
    );
  }

  /**
   * Gets the name that the user has given to describe this phone number.
   */
  public function getDisplayName() {
    return $this->displayName;
  }

  /**
   * Gets the ITU-T E. 164 country code of the phone number.
   */
  public function getCountryCode() {
    return $this->countryCode;
  }

  /**
   * Gets the national component of the phone number.
   */
  public function getNationalNumber() {
    return $this->nationalNumber;
  }

  /**
   * Gets whether the number relates to a mobile phone or not.
   */
  public function getIsMobile() {
    return $this->isMobile;
  }

  /**
   * Gets whether this is the user's primary phone number.
   */
  public function getIsPrimary() {
    return $this->isPrimary;
  }
}

/**
 * Represents a postal address that the user has added to their profile.
 *
 * @package MiiCardConsumers
 */
class PostalAddress extends Claim {
  /** @access protected */
  protected $house;
  /** @access protected */
  protected $line1;
  /** @access protected */
  protected $line2;
  /** @access protected */
  protected $city;
  /** @access protected */
  protected $region;
  /** @access protected */
  protected $code;
  /** @access protected */
  protected $country;
  /** @access protected */
  protected $isPrimary;

  /**
   * Initialises a new PostalAddress object.
   *
   * @param bool $verified
   *   Indicates whether the address has been verified.
   * @param string $house
   *   The name or number of the building referred to by the address.
   * @param string $line1
   *   The first line of the address.
   * @param string $line2
   *   The second line of the address.
   * @param string $city
   *   The city of the address.
   * @param string $region
   *   The region (for example, county, state or department) of the address.
   * @param string $code
   *   The postal code of the address.
   * @param string $country
   *   The country of the address.
   * @param bool $is_primary
   *   Indicates whether this is the user's primary postal address on the
   *   miiCard service.
   */
  public function __construct($verified, $house, $line1, $line2, $city, $region, $code, $country, $is_primary) {
    parent::__construct($verified);

    $this->house = $house;
    $this->line1 = $line1;
    $this->line2 = $line2;
    $this->city = $city;
    $this->region = $region;
    $this->code = $code;
    $this->country = $country;
    $this->isPrimary = $is_primary;
  }

  /**
   * Builds a new PostalAddress from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about a single postal address.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new PostalAddress(
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

  /**
   * Gets the name or number of the building referred to by the address.
   */
  public function getHouse() {
    return $this->house;
  }

  /**
   * Gets the first line of the address.
   */
  public function getLine1() {
    return $this->line1;
  }

  /**
   * Gets the second line of the address.
   */
  public function getLine2() {
    return $this->line2;
  }

  /**
   * Gets the city of the address.
   */
  public function getCity() {
    return $this->city;
  }

  /**
   * Gets the region (for example, county or state) of the address.
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * Gets the postal code of the address.
   */
  public function getCode() {
    return $this->code;
  }

  /**
   * Gets the country of the address.
   */
  public function getCountry() {
    return $this->country;
  }

  /**
   * Gets whether this is the user's primary postal address.
   */
  public function getIsPrimary() {
    return $this->isPrimary;
  }
}

/**
 *  Represents a web property that the member has added to their profile.
 *
 * @package MiiCardConsumers
 */
class WebProperty extends Claim {
  /** @access protected */
  protected $displayName;
  /** @access protected */
  protected $identifier;
  /** @access protected */
  protected $type;

  /**
   * Initialises a new WebProperty object.
   *
   * @param bool $verified
   *   Indicates whether the web property has been verified.
   * @param string $display_name
   *   The display name that the user has given the web property.
   * @param string $identifier
   *   The identifier of the property, which will be specific to the type of
   *   property being describes. For domain-type properties this will be the
   *   domain name, while for site-type properties this will be a URL to a page
   *   or site.
   * @param int $type
   *   The type of web property has that been linked. Corresponds to a constant
   *   from the WebPropertyType class.
   */
  public function __construct($verified, $display_name, $identifier, $type) {
    parent::__construct($verified);

    $this->displayName = $display_name;
    $this->identifier = $identifier;
    $this->type = $type;
  }

  /**
   * Builds a new WebProperty from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about a single web property.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new WebProperty(
      Util::TryGet($hash, 'Verified'),
      Util::TryGet($hash, 'DisplayName'),
      Util::TryGet($hash, 'Identifier'),
      Util::TryGet($hash, 'Type')
    );
  }

  /**
   * Gets the display name that the user has given the web property.
   */
  public function getDisplayName() {
    return $this->displayName;
  }

  /**
   * Gets the identifier of the property, specific to the type of property.
   *
   * For domain-type properties this will be the domain name, while for
   * site-type properties this will be a URL to a page or site.
   */
  public function getIdentifier() {
    return $this->identifier;
  }

  /**
   * Gets the type of web property has that been linked.
   *
   * Corresponds to a constant from the WebPropertyType class.
   */
  public function getType() {
    return $this->type;
  }
}

/**
 * Represents a subset of the miiCard member's identity.
 *
 * @package MiiCardConsumers
 */
class MiiUserProfile {
  /** @access protected */
  protected $username;
  /** @access protected */
  protected $salutation;
  /** @access protected */
  protected $firstName;
  /** @access protected */
  protected $middleName;
  /** @access protected */
  protected $lastName;
  /** @access protected */
  protected $previousFirstName;
  /** @access protected */
  protected $previousMiddleName;
  /** @access protected */
  protected $previousLastName;
  /** @access protected */
  protected $lastVerified;
  /** @access protected */
  protected $profileUrl;
  /** @access protected */
  protected $profileShortUrl;
  /** @access protected */
  protected $cardImageUrl;
  /** @access protected */
  protected $emailAddresses;
  /** @access protected */
  protected $identities;
  /** @access protected */
  protected $phoneNumbers;
  /** @access protected */
  protected $postalAddresses;
  /** @access protected */
  protected $webProperties;
  /** @access protected */
  protected $identityAssured;
  /** @access protected */
  protected $hasPublicProfile;
  /** @access protected */
  protected $publicProfile;
  /** @access protected */
  protected $dateOfBirth;

  /**
   * Initialises a new MiiUserProfile object.
   *
   * @param string $username
   *   The miiCard username of the member.
   * @param string $salutation
   *   The salutation of the member (e.g. 'Mr', 'Mrs' etc).
   * @param string $first_name
   *   The first name of the member
   * @param string $middle_name
   *   The middle name of the member, if known.
   * @param string $last_name
   *   The last name of the member.
   * @param string $previous_first_name
   *   The previous first name of the member, if known.
   * @param string $previous_middle_name
   *   The previous middle name of the member, if known.
   * @param string $previous_last_name
   *   The previous last name of the member, if known.
   * @param int $last_verified
   *   The UNIX timestamp representing the date the user's identity was last
   *   verified.
   * @param string $profile_url
   *   The URL to the member's public miiCard profile page.
   * @param string $profile_short_url
   *   The short URL to the member's public miiCard profile page.
   * @param string $card_image_url
   *   The URL to the user's miiCard card image, as shown on their public
   *   profile page.
   * @param array $email_addresses
   *   An array of EmailAddress objects associated with the member.
   * @param array $identities
   *   An array of alternative identities for the member, for example on
   *   social media sites.
   * @param array $phone_numbers
   *   An array of PhoneNumber objects associated with the member.
   * @param array $postal_addresses
   *   An array of PostalAddress objects associated with the member.
   * @param array $web_properties
   *   An array of WebProperty objects associated with the member.
   * @param bool $identity_assured
   *   Indicates whether the member has met the level of identity
   *   assurance required by your application.
   * @param bool $has_public_profile
   *   Indicates whether the user has published their public profile. If
   *   FALSE, the card image and profile URLs are assumed to not resolve.
   * @param MiiUserProfile $public_profile
   *   A MiiUserProfile object representing the subset of identity information
   *   that they have made publicly available on their profile page.
   * @param int $date_of_birth
   *   The miiCard user's date of birth
   */
  public function __construct($username, $salutation, $first_name, $middle_name, $last_name, $previous_first_name, $previous_middle_name, $previous_last_name, $last_verified, $profile_url, $profile_short_url, $card_image_url, $email_addresses, $identities, $phone_numbers, $postal_addresses, $web_properties, $identity_assured, $has_public_profile, $public_profile, $date_of_birth) {
    $this->username = $username;
    $this->salutation = $salutation;

    $this->firstName = $first_name;
    $this->middleName = $middle_name;
    $this->lastName = $last_name;

    $this->previousFirstName = $previous_first_name;
    $this->previousMiddleName = $previous_middle_name;
    $this->previousLastName = $previous_last_name;

    $this->lastVerified = $last_verified;
    $this->profileUrl = $profile_url;
    $this->profileShortUrl = $profile_short_url;
    $this->cardImageUrl = $card_image_url;

    $this->emailAddresses = $email_addresses;
    $this->identities = $identities;
    $this->phoneNumbers = $phone_numbers;
    $this->postalAddresses = $postal_addresses;
    $this->webProperties = $web_properties;

    $this->identityAssured = $identity_assured;
    $this->hasPublicProfile = $has_public_profile;
    $this->publicProfile = $public_profile;

    $this->dateOfBirth = $date_of_birth;
  }

  /**
   * Gets the miiCard username of the member.
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * Gets the salutation of the member (e.g. 'Mr', 'Mrs' etc).
   */
  public function getSalutation() {
    return $this->salutation;
  }

  /**
   * Gets the first name of the member.
   */
  public function getFirstName() {
    return $this->firstName;
  }

  /**
   * Gets the middle name of the member, if known.
   */
  public function getMiddleName() {
    return $this->middleName;
  }

  /**
   * Gets the last name of the member, if known.
   */
  public function getLastName() {
    return $this->lastName;
  }

  /**
   * Gets the UNIX timestamp representing the date of birth of the member.
   */
  public function getDateOfBirth() {
    return $this->dateOfBirth;
  }

  /**
   * Gets the previous first name of the member, if known.
   */
  public function getPreviousFirstName() {
    return $this->previousFirstName;
  }

  /**
   * Gets the previous middle name of the member, if known.
   */
  public function getPreviousMiddleName() {
    return $this->previousMiddleName;
  }

  /**
   * Gets the previous last name of the member, if known.
   */
  public function getPreviousLastName() {
    return $this->previousLastName;
  }

  /**
   * Gets the UNIX timestamp of the date the identity was last verified.
   */
  public function getLastVerified() {
    return $this->lastVerified;
  }

  /**
   * Gets the URL to the member's public miiCard profile page.
   */
  public function getProfileUrl() {
    return $this->profileUrl;
  }

  /**
   * Gets the short URL to the member's public miiCard profile page.
   */
  public function getProfileShortUrl() {
    return $this->profileShortUrl;
  }

  /**
   * Gets the URL to the user's miiCard card image.
   */
  public function getCardImageUrl() {
    return $this->cardImageUrl;
  }

  /**
   * Gets the array of EmailAddress objects associated with the member.
   */
  public function getEmailAddresses() {
    return $this->emailAddresses;
  }

  /**
   * Gets the array of alternative identities for the member.
   */
  public function getIdentities() {
    return $this->identities;
  }

  /**
   * Gets the array of PhoneNumber objects associated with the member.
   */
  public function getPhoneNumbers() {
    return $this->phoneNumbers;
  }

  /**
   * Gets the array of PostalAddress objects associated with the member.
   */
  public function getPostalAddresses() {
    return $this->postalAddresses;
  }

  /**
   * Gets the array of WebProperty objects associated with the member.
   */
  public function getWebProperties() {
    return $this->webProperties;
  }

  /**
   * Gets whether the member has met LOA required by your application.
   */
  public function getIdentityAssured() {
    return $this->identityAssured;
  }

  /**
   * Gets whether the user has published their public profile.
   *
   * If FALSE, the card image and profile URLs are assumed to not resolve.
   */
  public function getHasPublicProfile() {
    return $this->hasPublicProfile;
  }

  /**
   * Gets the MiiUserProfile object representing the user's public profile.
   */
  public function getPublicProfile() {
    return $this->publicProfile;
  }

  /**
   * Builds a new MiiUserProfile from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about a single user profile.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    $emails = Util::TryGet($hash, 'EmailAddresses');
    $emails_parsed = array();
    if (isset($emails) && is_array($emails)) {
      foreach ($emails as $email) {
        array_push($emails_parsed, EmailAddress::FromHash($email));
      }
    }

    $identities = Util::TryGet($hash, 'Identities');
    $identities_parsed = array();
    if (isset($identities) && is_array($identities)) {
      foreach ($identities as $identity) {
        array_push($identities_parsed, Identity::FromHash($identity));
      }
    }

    $phone_numbers = Util::TryGet($hash, 'PhoneNumbers');
    $phone_numbers_parsed = array();
    if (isset($phone_numbers) && is_array($phone_numbers)) {
      foreach ($phone_numbers as $phone_number) {
        array_push($phone_numbers_parsed, PhoneNumber::FromHash($phone_number));
      }
    }

    $web_properties = Util::TryGet($hash, 'WebProperties');
    $web_properties_parsed = array();
    if (isset($web_properties) && is_array($web_properties)) {
      foreach ($web_properties as $web_property) {
        array_push($web_properties_parsed, WebProperty::FromHash($web_property));
      }
    }

    $postal_addresses = Util::TryGet($hash, 'PostalAddresses');
    $postal_addresses_parsed = array();
    if (isset($postal_addresses) && is_array($postal_addresses)) {
      foreach ($postal_addresses as $postal_address) {
        array_push($postal_addresses_parsed, PostalAddress::FromHash($postal_address));
      }
    }

    $public_profile = Util::TryGet($hash, 'PublicProfile');
    $public_profile_parsed = NULL;
    if (isset($public_profile) && is_array($public_profile)) {
      $public_profile_parsed = MiiUserProfile::FromHash($public_profile);
    }

    // Try parsing the last-verified as a timestamp.
    preg_match('/\/Date\((\d+)\)/', Util::TryGet($hash, 'LastVerified'), $matches);

    $last_verified_parsed = NULL;
    if (isset($matches) && count($matches) > 1) {
      $last_verified_parsed = ($matches[1] / 1000);
    }

    $date_of_birth_parsed = NULL;
    preg_match('/\/Date\((\d+)\)/', Util::TryGet($hash, 'DateOfBirth'), $matches_dob);
    if (isset($matches_dob) && count($matches_dob) > 1) {
      $date_of_birth_parsed = ($matches_dob[1] / 1000);
    }

    return new MiiUserProfile(
      Util::TryGet($hash, 'Username'),
      Util::TryGet($hash, 'Salutation'),
      Util::TryGet($hash, 'FirstName'),
      Util::TryGet($hash, 'MiddleName'),
      Util::TryGet($hash, 'LastName'),
      Util::TryGet($hash, 'PreviousFirstName'),
      Util::TryGet($hash, 'PreviousMiddleName'),
      Util::TryGet($hash, 'PreviousLastName'),
      $last_verified_parsed,
      Util::TryGet($hash, 'ProfileUrl'),
      Util::TryGet($hash, 'ProfileShortUrl'),
      Util::TryGet($hash, 'CardImageUrl'),
      $emails_parsed,
      $identities_parsed,
      $phone_numbers_parsed,
      $postal_addresses_parsed,
      $web_properties_parsed,
      Util::TryGet($hash, 'IdentityAssured'),
      Util::TryGet($hash, 'HasPublicProfile'),
      $public_profile_parsed,
      $date_of_birth_parsed
    );
  }
}

/**
 * Represents metadata of a snapshot of a single miiCard member's details.
 *
 * @package MiiCardConsumers
 */
class IdentitySnapshotDetails {
  protected $snapshotId;
  protected $username;
  protected $timestampUtc;
  protected $wasTestUser;

  /**
   * Initialises a new IdentitySnapshotDetails object.
   *
   * @param string $snapshot_id
   *   The unique identifier for this snapshot.
   * @param string $username
   *   The username of the miiCard member of whose data the snapshot was
   *   taken.
   * @param int $timestamp_utc
   *   The UNIX timestamp representing the date the snapshot of the user's
   *   identity was taken.
   */
  public function __construct($snapshot_id, $username, $timestamp_utc, $was_test_user) {
    $this->snapshotId = $snapshot_id;
    $this->username = $username;
    $this->timestampUtc = $timestamp_utc;
    $this->wasTestUser = $was_test_user;
  }

  /**
   * Gets the unique identifier for this snapshot.
   */
  public function getSnapshotId() {
    return $this->snapshotId;
  }

  /**
   * Gets the username of the miiCard member within the snapshot.
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * Gets the UNIX timestamp when the snapshot of the user's identity was taken.
   */
  public function getTimestampUtc() {
    return $this->timestampUtc;
  }

  /**
   * Gets whether the user was marked as a test user when the snapshot was made.
   *
   * Identity checks are skipped for test users, so production code should
   * reject snapshots for test users as appropriate.
   */
  public function getWasTestUser() {
    return $this->wasTestUser;
  }

  /**
   * Builds a new IdentitySnapshotDetails from a hash obtained from the API.
   *
   * @param array $hash
   *   The has containing details about a single snapshot.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    // Try parsing the last-verified as a timestamp.
    preg_match('/\/Date\((\d+)\)/', Util::TryGet($hash, 'TimestampUtc'), $matches);

    $timestamp_utc_parsed = NULL;
    if (isset($matches) && count($matches) > 1) {
      $timestamp_utc_parsed = ($matches[1] / 1000);
    }

    return new IdentitySnapshotDetails(
      Util::TryGet($hash, 'SnapshotId'),
      Util::TryGet($hash, 'Username'),
      $timestamp_utc_parsed,
      Util::TryGet($hash, 'WasTestUser')
    );
  }
}

/**
 * Represents a single snapshot of miiCard member's identity.
 *
 * @package MiiCardConsumers
 */
class IdentitySnapshot {
  protected $details;
  protected $snapshot;

  /**
   * Initialises a new IdentitySnapshot object.
   *
   * @param IdentitySnapshotDetails $details
   *   An IdentitySnapshotDetails object that describes the contents of the
   *   snapshot.
   * @param MiiUserProfile $snapshot
   *   The MiiUserProfile object containing the miiCard member's identity at
   *   the point the snapshot was taken.
   */
  public function __construct($details, $snapshot) {
    $this->details = $details;
    $this->snapshot = $snapshot;
  }

  /**
   * Gets the IdentitySnapshotDetails describing the snapshot.
   */
  public function getDetails() {
    return $this->details;
  }

  /**
   * Gets the snapshotted MiiUserProfile object.
   */
  public function getSnapshot() {
    return $this->snapshot;
  }

  /**
   * Builds a new IdentitySnapshot from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about the identity snapshot.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new IdentitySnapshot(
      IdentitySnapshotDetails::FromHash(Util::TryGet($hash, 'Details')),
      MiiUserProfile::FromHash(Util::TryGet($hash, 'Snapshot'))
    );
  }
}

/**
 * A wrapper around most responses to miiCard Claims API calls.
 *
 * Also details the success or failure of the call and containing additional
 * information to help diagnose issues.
 *
 * @package MiiCardConsumers
 */
class MiiApiResponse {
  /** @access protected */
  protected $status;
  /** @access protected */
  protected $errorCode;
  /** @access protected */
  protected $errorMessage;
  /** @access protected */
  protected $data;
  /** @access protected */
  protected $isTestUser;

  /**
   * Initialises a new MiiApiResponse object.
   *
   * @param int $status
   *   The overall status of the API call, linked to one of the
   *   constants of the MiiApiCallStatus class.
   * @param int $error_code
   *   The error code that describes any error that occurred during the API
   *   call, linked to one of the constants of the MiiApiErrorCode class.
   * @param string $error_message
   *   Additional error message optionally supplied for certain types of API
   *   error. Intended for diagnostics only and strictly not suitable for
   *   display to the public.
   * @param mixed $data
   *   The payload of the response, whose type will vary depending on the API
   *   method being called.
   */
  public function __construct($status, $error_code, $error_message, $is_test_user, $data) {
    $this->status = $status;
    $this->errorCode = $error_code;
    $this->errorMessage = $error_message;
    $this->isTestUser = $is_test_user;

    $this->data = $data;
  }

  /**
   * Gets the overall status of the API call.
   *
   * Linked to one of the constants of the MiiApiCallStatus class.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Gets the error code that describes any error during API access.
   */
  public function getErrorCode() {
    return $this->errorCode;
  }

  /**
   * Gets any additional INTERNAL error message.
   *
   * Intended for diagnostics only and strictly not suitable for display to
   * the public.
   */
  public function getErrorMessage() {
    return $this->errorMessage;
  }

  /**
   * Gets the payload of the response.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Gets whether the user is currently marked as a tester.
   *
   * Identity assurance checks are skipped for test users for development
   * purposes - your production code should check this field and reject
   * test accounts as appropriate.
   */
  public function getIsTestUser() {
    return $this->isTestUser;
  }

  /**
   * Builds a new MiiApiResponse from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The has containing details about a single API call response.
   * @param Callable $data_processor
   *   A Callable that turns the JSON data payload into a PHP object, if
   *   required.
   * @param bool $is_array_payload
   *   Indicates that the payload of the response is an array-type, over which
   *   the $data_processor Callable should be iteratively evaluated.
   */
  public static function FromHash($hash, $data_processor, $is_array_payload = FALSE) {
    if (!isset($hash)) {
      return NULL;
    }

    $payload_json = Util::TryGet($hash, 'Data');
    $payload = NULL;

    if ($data_processor !== NULL) {
      if ($is_array_payload === TRUE) {
        if (isset($payload_json)) {
          $payload = array();
          foreach ($payload_json as $item) {
            array_push($payload, call_user_func($data_processor, $item));
          }
        }
      }
      else {
        $payload = call_user_func($data_processor, $payload_json);
      }
    }
    elseif ($payload_json !== NULL) {
      $payload = $payload_json;
    }

    return new MiiApiResponse(
      Util::TryGet($hash, 'Status'),
      Util::TryGet($hash, 'ErrorCode'),
      Util::TryGet($hash, 'ErrorMessage'),
      Util::TryGet($hash, 'IsTestUser'),
      $payload
    );
  }
}

/**
 * General utilities class.
 *
 * @package MiiCardConsumers
 */
class Util {
  /**
   * Gets a value from an associative array, or NULL if the key doesn't exist.
   *
   * @param array $hash
   *   The associative array.
   * @param string $key
   *   The key whose value is to be returned if available.
   */
  public static function TryGet($hash, $key) {
    if (!isset($hash) || !is_array($hash) || !array_key_exists($key, $hash)) {
      return FALSE;
    }

    return $hash[$key];
  }
}
