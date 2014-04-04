<?php

namespace miiCard\Consumers\Model;

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
   * Signifies that a Directory API search field wasn't recognised.
   *
   * When the Directory API receives a query on a field that it doesn't
   * recognise the search is cancelled, this error code returned
   * with an error message that includes the set of valid query fields.
   */
  const UNKNOWN_SEARCH_CRITERION = 10;

  /**
   * Signifies that a Directory API search returned no results.
   *
   * No results can happen for a number of reasons, but most frequently because
   * there isn't a miiCard member who has published the details searched for,
   * or there is but that member has disabled their public profile page for
   * some reason.
   */
  const NO_MATCHES = 11;
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

  /** Signifies that your account has not been enabled for the Financial API. */
  const FINANCIAL_DATA_SUPPORT_DISABLED = 1001;

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
 * Details the kind of qualification a user has linked to their miiCard profile.
 *
 * @package MiiCardConsumers
 */
class QualificationType {
  /** The Qualification object describes an academic qualification. */
  const ACADEMIC = 0;

  /** The Qualification object describes a professional membership. */
  const PROFESSIONAL = 1;
}

/**
 * Details the kind of second factor used during a two-step verification.
 *
 * @package MiiCardConsumers
 */
class AuthenticationType {
  /** No second factor was employed. */
  const NONE = 0;

  /** A software token, such as an SMS or software OAUTH provider was used. */
  const SOFT = 1;

  /** A hardware token, such as a YubiKey was used. */
  const HARD = 2;
}

/**
 * Details the state of a request to refresh financial data.
 */
class RefreshState {
  /** The status of the refresh cannot be determined. */
  const UNKNOWN = 0;

  /** The refresh is complete and up-to-date data is now available. */
  const DATA_AVAILABLE = 1;

  /** The refresh is currently in-progress and may take some minutes. */
  const IN_PROGRESS = 2;
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
  protected $qualifications;
  /** @access protected */
  protected $identityAssured;
  /** @access protected */
  protected $hasPublicProfile;
  /** @access protected */
  protected $publicProfile;
  /** @access protected */
  protected $dateOfBirth;
  /** @access protected */
  protected $age;
  /** @access protected */
  protected $creditBureauVerification;

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
   * @param array $qualifications
   *   The miiCard member's academic and professional qualifications.
   * @param int $age
   *   The miiCard member's age in whole years, if shared and known.
   * @param CreditBureauVerification $creditBureauVerification
   *   The miiCard memeber's latest credit bureau data, if shared and known.
   */
  public function __construct($username, $salutation, $first_name, $middle_name, $last_name, $previous_first_name, $previous_middle_name, $previous_last_name, $last_verified, $profile_url, $profile_short_url, $card_image_url, $email_addresses, $identities, $phone_numbers, $postal_addresses, $web_properties, $identity_assured, $has_public_profile, $public_profile, $date_of_birth, $qualifications, $age, $creditBureauVerification) {
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
    $this->qualifications = $qualifications;
    $this->age = $age;

    $this->creditBureauVerification = $creditBureauVerification;
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
   * Gets the miiCard member's age in whole years, if known and shared.
   */
  public function getAge() {
    return $this->age;
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
   * Gets the array of Qualification objects associated with the member.
   */
  public function getQualifications() {
    return $this->qualifications;
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
   * Gets the user's credit bureau data
   */
  public function getCreditBureauVerification() {
	return $this->creditBureauVerification;
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

    $qualifications = Util::TryGet($hash, 'Qualifications');
    $qualifications_parsed = array();
    if (isset($qualifications) && is_array($qualifications)) {
      foreach ($qualifications as $qualification) {
        array_push($qualifications_parsed, Qualification::FromHash($qualification));
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

	$credit_bureau_verification = Util::TryGet($hash, 'CreditBureauVerification');
	$credit_bureau_verification_parsed = NULL;
	if (isset($credit_bureau_verification)) {
		$credit_bureau_verification_parsed = CreditBureauVerification::FromHash($credit_bureau_verification);
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
      $date_of_birth_parsed,
      $qualifications_parsed,
      Util::TryGet($hash, 'Age'),
      $credit_bureau_verification_parsed
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
 * Represents an academic qualification or professional membership.
 *
 * @package MiiCardConsumers
 */
class Qualification {
  protected $type;
  protected $title;
  protected $dataProvider;
  protected $dataProviderUrl;

  /**
   * Initialises a new Qualification object.
   *
   * @param int $type
   *   The kind of qualification described; see QualificationType class.
   * @param string $title
   *   The title or level of qualification.
   * @param string $data_provider
   *   The name of the provider of the data.
   * @param string $data_provider_url
   *   A URL to a publicly-accessible verification of the qualification, or to
   *   the data provider's site if no such public verification exists.
   */
  public function __construct($type, $title, $data_provider, $data_provider_url) {
    $this->type = $type;
    $this->title = $title;
    $this->dataProvider = $data_provider;
    $this->dataProviderUrl = $data_provider_url;
  }

  /**
   * Gets the type of qualification; see the QualificationType class.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Gets the title or level of the qualification.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Gets the name of the provider of the data.
   */
  public function getDataProvider() {
    return $this->dataProvider;
  }

  /**
   * Gets the URL to a publicly-accessible verification of the qualification,
   * or to the data provider's site if no such public verification exists.
   */
  public function getDataProviderUrl() {
    return $this->dataProviderUrl;
  }

  /**
   * Builds a new Qualification from a hash obtained from the Claims API.
   *
   * @param array $hash
   *   The hash containing details about the qualification.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new Qualification(
      Util::TryGet($hash, 'Type'),
      Util::TryGet($hash, 'Title'),
      Util::TryGet($hash, 'DataProvider'),
      Util::TryGet($hash, 'DataProviderUrl')
    );
  }
}

/**
 * Records the manner of authentication that took place for a given sharing of a
 * miiCard member's identity data.
 *
 * @package MiiCardConsumers
 */
class AuthenticationDetails {
  protected $authenticationTimeUtc;
  protected $secondFactorTokenType;
  protected $secondFactorProvider;
  protected $locations;

  /**
   * Initialises a new AuthenticationDetails object.
   *
   * @param int $authentication_time_utc
   *   The time and date at which the user authenticated.
   * @param int $second_factor_token_type
   *   The kind of second-factor employed - see the AuthenticationTokenType
   *   class.
   * @param string $second_factor_provider
   *   The name of the provider of the second authentication factor, if any.
   * @param array $locations
   *   The collection of GeographicLocation objects that represent the location
   *   of the miiCard member at the point of authentication as reported by zero
   *   or more different sources.
   */
  public function __construct($authentication_time_utc, $second_factor_token_type, $second_factor_provider, $locations) {
    $this->authenticationTimeUtc = $authentication_time_utc;
    $this->secondFactorTokenType = $second_factor_token_type;
    $this->secondFactorProvider = $second_factor_provider;
    $this->locations = $locations;
  }

  /**
   * Gets the time and date at which the user authenticated.
   */
  public function getAuthenticationTimeUtc() {
    return $this->authenticationTimeUtc;
  }

  /**
   * Gets the kind of second-factor employed.
   */
  public function getSecondFactorTokenType() {
    return $this->secondFactorTokenType;
  }

  /**
   * Gets the name of the provider of the second authentication factor, if any.
   */
  public function getSecondFactorProvider() {
    return $this->secondFactorProvider;
  }

  /**
   * Gets the collection of GeographicLocation objects that represent the
   * location of the miiCard member at the point of authentication as reported
   * by zero or more different sources.
   */
  public function getLocations() {
    return $this->locations;
  }

  /**
   * Builds a new AuthenticationDetails from a hash obtained from the Claims API
   *
   * @param array $hash
   *   The hash from the Claims API.
   */
  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    $locations = Util::TryGet($hash, 'Locations');
    $locations_parsed = array();
    if (isset($locations) && is_array($locations)) {
      foreach ($locations as $location) {
        array_push($locations_parsed, GeographicLocation::FromHash($location));
      }
    }

    // Try parsing the authentication time as a timestamp.
    preg_match('/\/Date\((\d+)\)/', Util::TryGet($hash, 'AuthenticationTimeUtc'), $matches);

    $authentication_time_utc_parsed = NULL;
    if (isset($matches) && count($matches) > 1) {
      $authentication_time_utc_parsed = ($matches[1] / 1000);
    }

    return new AuthenticationDetails(
      $authentication_time_utc_parsed,
      Util::TryGet($hash, 'SecondFactorTokenType'),
      Util::TryGet($hash, 'SecondFactorProvider'),
      $locations_parsed
    );
  }
}

/**
 * Represents a miiCard member's geographic location as reported by a single
 * data provider.
 *
 * @package MiiCardConsumers
 */
class GeographicLocation {
  /** @access protected */
  protected $locationProvider;
  /** @access protected */
  protected $latitude;
  /** @access protected */
  protected $longitude;
  /** @access protected */
  protected $latLongAccuracyMetres;
  /** @access protected */
  protected $approximateAddress;

  /**
   * Initialises a new GeographicLocation.
   *
   * @param string $location_provider
   *   The name of the location provider by whom the location data
   *   was supplied.
   * @param double $latitude
   *   The latitude of the location, if known, or null.
   * @param double $longitude
   *   The longitude of the location, if known, or null.
   * @param int $lat_long_accuracy_metres
   *   The approximate accuracy with which the location specified by
   *   latitude and longitude has been pinpointed, if they were specified and an
   *   accuracy figure is available.
   * @param PostalAddress $approximate_address
   *   The approximate postal address of the location as reported by
   *   the provider, if known. Fields of the postal address may not be populated
   *   depending on the level of accuracy reached.
   */
  public function __construct($location_provider, $latitude, $longitude, $lat_long_accuracy_metres, $approximate_address) {
    $this->locationProvider = $location_provider;
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->latLongAccuracyMetres = $lat_long_accuracy_metres;
    $this->approximateAddress = $approximate_address;
  }

  /**
   * Gets the name of the location provider by whom the location data
   * was supplied.
   */
  public function getLocationProvider() {
    return $this->locationProvider;
  }

  /**
   * Gets the latitude of the location, if known, or null.
   */
  public function getLatitude() {
    return $this->latitude;
  }

  /**
   * Gets the longitude of the location, if known, or null.
   */
  public function getLongitude() {
    return $this->longitude;
  }

  /**
   * Gets the approximate accuracy with which the location specified by
   * latitude and longitude has been pinpointed, if they were specified and an
   * accuracy figure is available.
   */
  public function getLatLongAccuracyMetres() {
    return $this->latLongAccuracyMetres;
  }

  /**
   * Gets the approximate postal address of the location as reported by
   * the provider, if known. Fields of the postal address may not be populated
   * depending on the level of accuracy reached.
   */
  public function getApproximateAddress() {
    return $this->approximateAddress;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new GeographicLocation(
      Util::TryGet($hash, 'LocationProvider'),
      Util::TryGet($hash, 'Latitude'),
      Util::TryGet($hash, 'Longitude'),
      Util::TryGet($hash, 'LatLongAccuracyMetres'),
      PostalAddress::FromHash(Util::TryGet($hash, 'ApproximateAddress'))
    );
  }
}

/**
 * A single transaction reported against a financial account whose details
 * have been shared by a miiCard member.
 *
 * @package MiiCardConsumers
 */
class FinancialTransaction {
  /** @access protected */
  protected $date;
  /** @access protected */
  protected $amountCredited;
  /** @access protected */
  protected $amountDebited;
  /** @access protected */
  protected $description;
  /** @access protected */
  protected $id;

  /**
   * Initialises a new FinancialTransaction.
   *
   * @param int $date
   *   The date (and possibly time, depending on the provider) at which
   *   the transaction took place.
   * @param double $amount_credited
   *   The amount credited to the account in this transaction measured in the
   *   parent account's currency, or null if the transaction represents a debit.
   * @param double $amount_debited
   *   The amount debited from the account in this transaction measured in the
   *   parent account's currency, or null if the transaction represents a credit.
   * @param string $description
   *   A description of the transaction.
   * @param string $id
   *   An identifier for the transaction, if the data provider reported one.
   */
  public function __construct($date, $amount_credited, $amount_debited, $description, $id) {
    $this->date = $date;
    $this->amountCredited = $amount_credited;
    $this->amountDebited = $amount_debited;
    $this->description = $description;
    $this->id = $id;
  }

  /**
   * Gets the date (and possibly time, depending on the provider) at which
   * the transaction took place.
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Gets the amount credited to the account in this transaction measured in the
   * parent account's currency, or null if the transaction represents a debit.
   */
  public function getAmountCredited() {
    return $this->amountCredited;
  }

  /**
   * Gets the amount debited from the account in this transaction measured in the
   * parent account's currency, or null if the transaction represents a credit.
   */
  public function getAmountDebited() {
    return $this->amountDebited;
  }

  /**
   * Gets a description of the transaction.
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Gets an identifier for the transaction, if the data provider reported one.
   */
  public function getID() {
    return $this->id;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new FinancialTransaction(
      Util::TryGetDate($hash, 'Date'),
      Util::TryGet($hash, 'AmountCredited'),
      Util::TryGet($hash, 'AmountDebited'),
      Util::TryGet($hash, 'Description'),
      Util::TryGet($hash, 'ID')
    );
  }
}

/**
 * Represents credit bureau verification data from a 3rd party.
 * miiCard forwards this data from the 3rd party without
 * modification and takes no responsibility for its accuracy.
 *
 * @package MiiCardConsumers
*/
class CreditBureauVerification {
  /** @access protected */
  protected $data;
  /** @access protected */
  protected $lastVerified;

  public function __construct($data, $lastVerified) {
    $this->data = $data;
    $this->lastVerified = $lastVerified;
  }

  public function getData() {
    return $this->data;
  }

  public function getLastVerified() {
    return $this->lastVerified;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new CreditBureauVerification(
      Util::TryGet($hash, 'Data'),
      Util::TryGetDate($hash, 'LastVerified')
    );
  }
}

/**
 * Represents the result of Claims API request
 *
 * @package MiiCardConsumers
*/
class CreditBureauRefreshStatus {
  /** @access protected */
  protected $state;

  public function __construct($state) {
    $this->state = $state;
  }

  public function getState() {
    return $this->state;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new CreditBureauRefreshStatus(
      Util::TryGet($hash, 'State')
    );
  }
}

/**
 * Details of a single financial account that a miiCard member has elected to share
 * with a relying party application.
 *
 * @package MiiCardConsumers
 */
class FinancialAccount {
  /** @access protected */
  protected $accountName;
  /** @access protected */
  protected $holder;
  /** @access protected */
  protected $sortCode;
  /** @access protected */
  protected $accountNumber;
  /** @access protected */
  protected $type;
  /** @access protected */
  protected $fromDate;
  /** @access protected */
  protected $lastUpdatedUtc;
  /** @access protected */
  protected $closingBalance;
  /** @access protected */
  protected $debitsSum;
  /** @access protected */
  protected $debitsCount;
  /** @access protected */
  protected $creditsSum;
  /** @access protected */
  protected $creditsCount;
  /** @access protected */
  protected $currencyIso;
  /** @access protected */
  protected $transactions;

  /**
   * Initialises a new FinancialAccount.
   *
   * @param string $account_name
   *   The name of the account as reported by the provider.
   * @param string $holder
   *   The name of the account holder as reported by the financial provider.
   * @param string $sort_code
   *   The partial sort code of the account.
   * @param string $account_number
   *   The partial account number of the account.
   * @param string $type
   *   The type of account.
   * @param int $from_date
   *   The date (and possibly time, depending on the provider) from which this set
   *   of account details applies.
   * @param int $last_updated_utc
   *   The date (and possibly time, depending on the provider) at which the details
   *   of this account were last updated.
   * @param double $closing_balance
   *   The closing balance, measured in the account currency.
   * @param double $debits_sum
   *   The total value of debits, measured in the account currency.
   * @param int $debits_count
   *   The total number of debits made in the period.
   * @param double $credits_sum
   *   The total value of credits, measured in the account currency.
   * @param int $credits_count
   *   The total number of credits made in the period.
   * @param string $currency_iso
   *   The ISO 4217 code for the currency in which transactions are made
   *   for this account.
   * @param array $transactions
   *   The transactions that took place on this account between the
   *   dates detailed. If no transactions are available, or if no transaction-level
   *   data was agreed to be shared, this shall be an empty IEnumerable.
   */
  public function __construct($account_name, $holder, $sort_code, $account_number, $type, $from_date, $last_updated_utc, $closing_balance, $debits_sum, $debits_count, $credits_sum, $credits_count, $currency_iso, $transactions) {
    $this->accountName = $account_name;
    $this->holder = $holder;
    $this->sortCode = $sort_code;
    $this->accountNumber = $account_number;
    $this->type = $type;
    $this->fromDate = $from_date;
    $this->lastUpdatedUtc = $last_updated_utc;
    $this->closingBalance = $closing_balance;
    $this->debitsSum = $debits_sum;
    $this->debitsCount = $debits_count;
    $this->creditsSum = $credits_sum;
    $this->creditsCount = $credits_count;
    $this->currencyIso = $currency_iso;
    $this->transactions = $transactions;
  }

  /**
   * Gets the name of the account as reported by the provider.
   */
  public function getAccountName() {
    return $this->accountName;
  }

  /**
   * Gets the name of the account holder as reported by the financial provider.
   */
  public function getHolder() {
    return $this->holder;
  }

  /**
   * Gets the partial sort code of the account.
   */
  public function getSortCode() {
    return $this->sortCode;
  }

  /**
   * Gets the partial account number of the account.
   */
  public function getAccountNumber() {
    return $this->accountNumber;
  }

  /**
   * Gets the type of account.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Gets the date (and possibly time, depending on the provider) from which this set
   * of account details applies.
   */
  public function getFromDate() {
    return $this->fromDate;
  }

  /**
   * Gets the date (and possibly time, depending on the provider) at which the details
   * of this account were last updated.
   */
  public function getLastUpdatedUtc() {
    return $this->lastUpdatedUtc;
  }

  /**
   * Gets the closing balance, measured in the account currency.
   */
  public function getClosingBalance() {
    return $this->closingBalance;
  }

  /**
   * Gets the total value of debits, measured in the account currency.
   */
  public function getDebitsSum() {
    return $this->debitsSum;
  }

  /**
   * Gets the total number of debits made in the period.
   */
  public function getDebitsCount() {
    return $this->debitsCount;
  }

  /**
   * Gets the total value of credits, measured in the account currency.
   */
  public function getCreditsSum() {
    return $this->creditsSum;
  }

  /**
   * Gets the total number of credits made in the period.
   */
  public function getCreditsCount() {
    return $this->creditsCount;
  }

  /**
   * Gets the ISO 4217 code for the currency in which transactions are made
   * for this account.
   */
  public function getCurrencyIso() {
    return $this->currencyIso;
  }

  /**
   * Gets the transactions that took place on this account between the
   * dates detailed. If no transactions are available, or if no transaction-level
   * data was agreed to be shared, this shall be an empty IEnumerable.
   */
  public function getTransactions() {
    return $this->transactions;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new FinancialAccount(
      Util::TryGet($hash, 'AccountName'),
      Util::TryGet($hash, 'Holder'),
      Util::TryGet($hash, 'SortCode'),
      Util::TryGet($hash, 'AccountNumber'),
      Util::TryGet($hash, 'Type'),
      Util::TryGetDate($hash, 'FromDate'),
      Util::TryGetDate($hash, 'LastUpdatedUtc'),
      Util::TryGet($hash, 'ClosingBalance'),
      Util::TryGet($hash, 'DebitsSum'),
      Util::TryGet($hash, 'DebitsCount'),
      Util::TryGet($hash, 'CreditsSum'),
      Util::TryGet($hash, 'CreditsCount'),
      Util::TryGet($hash, 'CurrencyIso'),
      Util::TryGetArray($hash, 'Transactions', 'miiCard\Consumers\Model\FinancialTransaction::FromHash')
    );
  }
}

/**
 * Details of a single financial credit card account that a miiCard member has elected to share
 * with a relying party application.
 *
 * @package MiiCardConsumers
 */
class FinancialCreditCard {
  /** @access protected */
  protected $accountName;
  /** @access protected */
  protected $holder;
  /** @access protected */
  protected $accountNumber;
  /** @access protected */
  protected $type;
  /** @access protected */
  protected $fromDate;
  /** @access protected */
  protected $lastUpdatedUtc;
  /** @access protected */
  protected $creditLimit;
  /** @access protected */
  protected $runningBalance;
  /** @access protected */
  protected $debitsSum;
  /** @access protected */
  protected $debitsCount;
  /** @access protected */
  protected $creditsSum;
  /** @access protected */
  protected $creditsCount;
  /** @access protected */
  protected $currencyIso;
  /** @access protected */
  protected $transactions;

  /**
   * Initialises a new FinancialCreditCard.
   *
   * @param string $account_name
   *   The name of the account as reported by the provider.
   * @param string $holder
   *   The name of the account holder as reported by the financial provider.
   * @param string $account_number
   *   The partial account number of the account.
   * @param string $type
   *   The type of account.
   * @param int $from_date
   *   The date (and possibly time, depending on the provider) from which this set
   *   of account details applies.
   * @param int $last_updated_utc
   *   The date (and possibly time, depending on the provider) at which the details
   *   of this account were last updated.
   * @param double $credit_limit
   *   The credit limit of the account.
   * @param double $running_balance
   *   The running balance, measured in the account currency.
   * @param double $debits_sum
   *   The total value of debits, measured in the account currency.
   * @param int $debits_count
   *   The total number of debits made in the period.
   * @param double $credits_sum
   *   The total value of credits, measured in the account currency.
   * @param int $credits_count
   *   The total number of credits made in the period.
   * @param string $currency_iso
   *   The ISO 4217 code for the currency in which transactions are made
   *   for this account.
   * @param array $transactions
   *   The transactions that took place on this account between the
   *   dates detailed. If no transactions are available, or if no transaction-level
   *   data was agreed to be shared, this shall be an empty IEnumerable.
   */
  public function __construct($account_name, $holder, $account_number, $type, $from_date, $last_updated_utc, $credit_limit, $running_balance, $debits_sum, $debits_count, $credits_sum, $credits_count, $currency_iso, $transactions) {
    $this->accountName = $account_name;
    $this->holder = $holder;
    $this->accountNumber = $account_number;
    $this->type = $type;
    $this->fromDate = $from_date;
    $this->lastUpdatedUtc = $last_updated_utc;
    $this->creditLimit = $credit_limit;
    $this->runningBalance = $running_balance;
    $this->debitsSum = $debits_sum;
    $this->debitsCount = $debits_count;
    $this->creditsSum = $credits_sum;
    $this->creditsCount = $credits_count;
    $this->currencyIso = $currency_iso;
    $this->transactions = $transactions;
  }

  /**
   * Gets the name of the account as reported by the provider.
   */
  public function getAccountName() {
    return $this->accountName;
  }

  /**
   * Gets the name of the account holder as reported by the financial provider.
   */
  public function getHolder() {
    return $this->holder;
  }

  /**
   * Gets the partial account number of the account.
   */
  public function getAccountNumber() {
    return $this->accountNumber;
  }

  /**
   * Gets the type of account.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Gets the date (and possibly time, depending on the provider) from which this set
   * of account details applies.
   */
  public function getFromDate() {
    return $this->fromDate;
  }

  /**
   * Gets the date (and possibly time, depending on the provider) at which the details
   * of this account were last updated.
   */
  public function getLastUpdatedUtc() {
    return $this->lastUpdatedUtc;
  }

  /**
   * Gets the credit limit of the account.
   */
  public function getCreditLimit() {
    return $this->creditLimit;
  }

  /**
   * Gets the running balance, measured in the account currency.
   */
  public function getRunningBalance() {
    return $this->runningBalance;
  }

  /**
   * Gets the total value of debits, measured in the account currency.
   */
  public function getDebitsSum() {
    return $this->debitsSum;
  }

  /**
   * Gets the total number of debits made in the period.
   */
  public function getDebitsCount() {
    return $this->debitsCount;
  }

  /**
   * Gets the total value of credits, measured in the account currency.
   */
  public function getCreditsSum() {
    return $this->creditsSum;
  }

  /**
   * Gets the total number of credits made in the period.
   */
  public function getCreditsCount() {
    return $this->creditsCount;
  }

  /**
   * Gets the ISO 4217 code for the currency in which transactions are made
   * for this account.
   */
  public function getCurrencyIso() {
    return $this->currencyIso;
  }

  /**
   * Gets the transactions that took place on this account between the
   * dates detailed. If no transactions are available, or if no transaction-level
   * data was agreed to be shared, this shall be an empty IEnumerable.
   */
  public function getTransactions() {
    return $this->transactions;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new FinancialCreditCard(
      Util::TryGet($hash, 'AccountName'),
      Util::TryGet($hash, 'Holder'),
      Util::TryGet($hash, 'AccountNumber'),
      Util::TryGet($hash, 'Type'),
      Util::TryGetDate($hash, 'FromDate'),
      Util::TryGetDate($hash, 'LastUpdatedUtc'),
      Util::TryGet($hash, 'CreditLimit'),
      Util::TryGet($hash, 'RunningBalance'),
      Util::TryGet($hash, 'DebitsSum'),
      Util::TryGet($hash, 'DebitsCount'),
      Util::TryGet($hash, 'CreditsSum'),
      Util::TryGet($hash, 'CreditsCount'),
      Util::TryGet($hash, 'CurrencyIso'),
      Util::TryGetArray($hash, 'Transactions', 'miiCard\Consumers\Model\FinancialTransaction::FromHash')
    );
  }
}

/**
 * A single financial provider containing summary or transaction-level data.
 *
 * @package MiiCardConsumers
 */
class FinancialProvider {
  /** @access protected */
  protected $providerName;
  /** @access protected */
  protected $financialAccounts;
  /** @access protected */
  protected $financialCreditCards;

  /**
   * Initialises a new FinancialProvider.
   *
   * @param string $provider_name
   *   The name of the financial provider.
   * @param array $financial_accounts
   *   The set of financial accounts at this provider which the miiCard member
   *   has elected to share information about.
   * @param array $financial_credit_cards
   *   The set of financial credit card accounts at this provider which the miiCard
   *   member has elected to share information about.
   */
  public function __construct($provider_name, $financial_accounts, $financial_credit_cards) {
    $this->providerName = $provider_name;
    $this->financialAccounts = $financial_accounts;
    $this->financialCreditCards = $financial_credit_cards;
  }

  /**
   * Gets the name of the financial provider.
   */
  public function getProviderName() {
    return $this->providerName;
  }

  /**
   * Gets the set of financial accounts at this provider which the miiCard member
   * has elected to share information about.
   */
  public function getFinancialAccounts() {
    return $this->financialAccounts;
  }

  /**
   * Gets the set of financial credit card accounts at this provider which the miiCard member
   * has elected to share information about.
   */
  public function getFinancialCreditCards() {
    return $this->financialCreditCards;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new FinancialProvider(
      Util::TryGet($hash, 'ProviderName'),
      Util::TryGetArray($hash, 'FinancialAccounts', 'miiCard\Consumers\Model\FinancialAccount::FromHash'),
      Util::TryGetArray($hash, 'FinancialCreditCards', 'miiCard\Consumers\Model\FinancialCreditCard::FromHash')
    );
  }
}

/**
 * Represents a collection of financial data that a miiCard member has elected to share with a
 * relying party.
 *
 * @package MiiCardConsumers
 */
class MiiFinancialData {
  /** @access protected */
  protected $financialProviders;

  /**
   * Initialises a new MiiFinancialData.
   *
   * @param array $financial_providers
   *   The collection of FinancialProvider objects representing the providers
   *   whose accounts the member has chosen to share.
   */
  public function __construct($financial_providers) {
    $this->financialProviders = $financial_providers;
  }

  /**
   * Gets the collection of FinancialProvider objects representing the providers
   * whose accounts the member has chosen to share.
   */
  public function getFinancialProviders() {
    return $this->financialProviders;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new MiiFinancialData(
      Util::TryGetArray($hash, 'FinancialProviders', 'miiCard\Consumers\Model\FinancialProvider::FromHash')
    );
  }
}

class FinancialRefreshStatus {
  /** @access protected */
  protected $state;

  /**
   * Initialises a new FinancialRefreshStatus.
   *
   * @param int $state
   */
  public function __construct($state) {
    $this->state = $state;
  }

  public function getState() {
    return $this->state;
  }

  public static function FromHash($hash) {
    if (!isset($hash)) {
      return NULL;
    }

    return new FinancialRefreshStatus(
      Util::TryGet($hash, 'State')
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

  /**
   * Gets a value from an associative array as a date, or NULL if it doesn't
   * exist or cannot be parsed as such.
   *
   * @param array $hash
   *   The associative array.
   * @param string $key
   *   The key whose value is to be returned if available.
   */
  public static function TryGetDate($hash, $key) {
    // Try parsing the last updated date as a timestamp.
    preg_match('/\/Date\((\d+)\)/', Util::TryGet($hash, $key), $matches);

    $parsed = NULL;
    if (isset($matches) && count($matches) > 1) {
      $parsed = ($matches[1] / 1000);
    }

    return $parsed;
  }

  /**
   * Gets a value from an associative array as an array by calling a converter
   * function on each element, or an empty array if the element doesn't exist
   * in the hash or isn't itself an array.
   *
   * @param array $hash
   *   The associative array.
   * @param string $key
   *   The key whose value is to be returned if available.
   * @param Callable $converter_callable
   *   The Callable that will take an element from the input array and turn
   *   it into a PHP object type.
   */
  public static function TryGetArray($hash, $key, $converter_callable) {
    $raw = Util::TryGet($hash, $key);
    $parsed = array();
    if (isset($raw) && is_array($raw)) {
      foreach ($raw as $item) {
        array_push($parsed, call_user_func($converter_callable, $item));
      }
    }

    return $parsed;
  }
}