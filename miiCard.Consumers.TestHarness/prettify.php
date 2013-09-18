<?php
    use miiCard\Consumers\Consumers;
    use miiCard\Consumers\Model;

    /* A couple of functions render date-times and these come in as UTC (equiv. GMT) */
    date_default_timezone_set("GMT");

    class PrettifyConfiguration {
        protected $modestyLimit;

        public function __construct($modesty_limit) {
            $this->modestyLimit = $modesty_limit;
        }

        public function getModestyLimit() {
            return $this->modestyLimit;
        }
    }

    function renderResponse($obj, $configuration = NULL)
    {
        $toReturn = "<div class='response'>";
        
        $toReturn .= renderFact("Status", $obj->getStatus());
        $toReturn .= renderFact("Error code", $obj->getErrorCode());
        $toReturn .= renderFact("Error message", $obj->getErrorMessage());
        $toReturn .= renderFact("Is a test user?", $obj->getIsTestUser());
                
        $data = $obj->getData();
        
        if ($data instanceof Model\MiiUserProfile)
        {
            $toReturn .= renderUserProfile($data);
        }
        else if ($data instanceof Model\IdentitySnapshot)
        {
            $toReturn .= renderIdentitySnapshot($data);
        }
        else if ($data instanceof Model\IdentitySnapshotDetails)
        {
            $toReturn .= renderIdentitySnapshotDetails($data);
        }
        else if ($data instanceof Model\MiiFinancialData)
        {
            $toReturn .= renderFinancialData($data, $configuration);
        }
        else if ($data instanceof Model\FinancialRefreshStatus)
        {
            $toReturn .= renderFinancialRefreshStatus($data);
        }
        else if ($data instanceof Model\AuthenticationDetails)
        {
            $toReturn .= renderAuthenticationDetails($data);
        }
        else if (is_array($data) && count($data) > 0)
        {
            $sample = $data[0];
            if ($sample instanceof Model\IdentitySnapshotDetails)
            {
                $ct = 0;
                foreach ($data as $identitySnapshotDetails)
                {
                    $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                    $toReturn .= renderIdentitySnapshotDetails($identitySnapshotDetails);
                    $toReturn .= "</div>";
                }
            }
            else
            {
                $toReturn .= renderFact("Data", $data);
            }
        }
        else
        {
            $toReturn .= renderFact("Data", $data);
        }
        
        $toReturn .= "</div>";
        
        return $toReturn;
    }

    function renderAsDateTime($value)
    {
         if ($value != null)
         {
            return date('d-m-y H:i:s', $value) . " GMT";
         }
         else
         {
            return null;
         }
    }

    function renderAsDate($value)
    {
         if ($value != null)
         {
            return date('d-m-y', $value);
         }
         else
         {
            return null;
         }
    }
    
    function renderFact($factName, $factValue)
    {
        $factValueRender = "[Empty]";
        if ($factValue !== null)
        {
            $factValueRender = $factValue;    
        }
        
        return  "<div class='fact-row'><span class='fact-name'>$factName</span><span class='fact-value'>$factValueRender</span></div>";
    }

    function renderIdentitySnapshotDetails($identitySnapshotDetails)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("Snapshot ID", $identitySnapshotDetails->getSnapshotId());
        $toReturn .= renderFact("Username", $identitySnapshotDetails->getUsername());
        $toReturn .= renderFact("Timestamp",  date('d-m-y H:i:s', $identitySnapshotDetails->getTimestampUtc()) . " GMT");
        $toReturn .= renderFact("Was a test user?", $identitySnapshotDetails->getWasTestUser());
        $toReturn .= "</div>";

        return $toReturn;
    }

    function renderIdentitySnapshot($identitySnapshot)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFactHeading("Snapshot details");
        $toReturn .= renderIdentitySnapshotDetails($identitySnapshot->getDetails());

        $toReturn .= renderFactHeading("Snapshot contents");
        $toReturn .= renderUserProfile($identitySnapshot->getSnapshot());

        $toReturn .= "</div>";

        return $toReturn;
    }
    
    function renderIdentity($identity)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= renderFact("Source", $identity->getSource());
        $toReturn .= renderFact("User ID", $identity->getUserId());
        $toReturn .= renderFact("Profile URL", $identity->getProfileUrl());
        $toReturn .= renderFact("Verified?", $identity->getVerified());
        $toReturn .= "</div>";
        
        return $toReturn; 
    }
    
    function renderEmail($email)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= renderFact("Display name", $email->getDisplayName());
        $toReturn .= renderFact("Address", $email->getAddress());
        $toReturn .= renderFact("Is primary?", $email->getIsPrimary());
        $toReturn .= renderFact("Verified?", $email->getVerified());
        $toReturn .= "</div>";
        
        return $toReturn;
    }
    
    function renderAddress($address)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= renderFact("House", $address->getHouse());
        $toReturn .= renderFact("Line1", $address->getLine1());
        $toReturn .= renderFact("Line2", $address->getLine2());
        $toReturn .= renderFact("City", $address->getCity());
        $toReturn .= renderFact("Region", $address->getRegion());
        $toReturn .= renderFact("Code", $address->getCode());
        $toReturn .= renderFact("Country", $address->getCountry());
        $toReturn .= renderFact("Is primary?", $address->getIsPrimary());
        $toReturn .= renderFact("Verified?", $address->getVerified());
        $toReturn .= "</div>";
        
        return $toReturn; 
    }
    
    function renderPhone($number)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= renderFact("Display name", $number->getDisplayName());
        $toReturn .= renderFact("Country code", $number->getCountryCode());
        $toReturn .= renderFact("National number", $number->getNationalNumber());
        $toReturn .= renderFact("Is mobile?", $number->getIsMobile());
        $toReturn .= renderFact("Is primary?", $number->getIsPrimary());
        $toReturn .= renderFact("Verified?", $number->getVerified());
        $toReturn .= "</div>";
        
        return $toReturn; 
    }
    
    function renderWebProperty($property)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= renderFact("Display name", $property->getDisplayName());
        $toReturn .= renderFact("Identifier", $property->getIdentifier());
        $toReturn .= renderFact("Type", $property->getType());
        $toReturn .= renderFact("Verified?", $property->getVerified());
        $toReturn .= "</div>";
        
        return $toReturn;
    }

    function renderAuthenticationDetails($authenticationDetails)
    {
        $toReturn = "<div class='fact'>";
        $toReturn .= renderFactHeading("Authentication details");

        $toReturn .= renderFact("Timestamp UTC", renderAsDateTime($authenticationDetails->getAuthenticationTimeUtc()));
        $toReturn .= renderFact("2FA type", $authenticationDetails->getSecondFactorTokenType());
        $toReturn .= renderFact("2FA provider", $authenticationDetails->getSecondFactorProvider());

        $toReturn .= "<div class='fact'>";
        $toReturn .= renderFactHeading("Locations");

        $ct = 0;
        if ($authenticationDetails->getLocations() != NULL)
        {
            foreach ($authenticationDetails->getLocations() as $location)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderGeographicLocation($location);
                $toReturn .= "</div>";
            }
        }
        else
        {
            $toReturn .= "<p><i>No locations</i></p>";
        }

        $toReturn .= "</div></div>";

        return $toReturn;
    }

    function renderGeographicLocation($location)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("Provider", $location->getLocationProvider());
        $toReturn .= renderFact("Latitude", $location->getLatitude());
        $toReturn .= renderFact("Longitude", $location->getLongitude());
        $toReturn .= renderFact("Accuracy (metres, est.)", $location->getLatLongAccuracyMetres());

        if ($location->getApproximateAddress() != NULL)
        {
            $toReturn .= renderFactHeading("Approximate postal address");

            $toReturn .= renderAddress($location->getApproximateAddress());
        }
        else
        {
            $toReturn .= renderFact("Approximate postal address", NULL);
        }

        $toReturn .= "</div>";
        return $toReturn;
    }

    function renderFinancialRefreshStatus($status)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("State", $status->getState());

        $toReturn .= "</div>";

        return $toReturn;
    }

    function renderFinancialData($miiFinancialData, $configuration)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= "<h2>Financial Data</h2>";
        $toReturn .= renderFactHeading("Financial Providers");

        $ct = 0;
        if ($miiFinancialData->getFinancialProviders() != NULL)
        {
            foreach ($miiFinancialData->getFinancialProviders() as $provider)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderFinancialProvider($provider, $configuration);
                $toReturn .= "</div>";
            }
        }

        $toReturn .= "</div>";

        return $toReturn;
    }

    function renderFinancialProvider($financialProvider, $configuration)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("Name", $financialProvider->getProviderName());

        $toReturn .= renderFactHeading("Financial Accounts");

        $ct = 0;
        if ($financialProvider->getFinancialAccounts() != NULL)
        {
            foreach ($financialProvider->getFinancialAccounts() as $account)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderFinancialAccount($account, $configuration);
                $toReturn .= "</div>";
            }
        }

        $toReturn .= "</div>";

        return $toReturn;
    }

    function renderFinancialAccount($account, $configuration)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("Holder", $account->getHolder());
        $toReturn .= renderFact("Account number", $account->getAccountNumber());
        $toReturn .= renderFact("Sort code", $account->getSortCode());
        $toReturn .= renderFact("Account name", $account->getAccountName());
        $toReturn .= renderFact("Type", $account->getType());
        $toReturn .= renderFact("Last updated", renderAsDateTime($account->getLastUpdatedUtc()));
        $toReturn .= renderFact("Currency", $account->getCurrencyIso());
        $toReturn .= renderFact("Closing balance", getModestyFilteredAmount($account->getClosingBalance(), $configuration));
        $toReturn .= renderFact("Credits (count)", $account->getCreditsCount());
        $toReturn .= renderFact("Credits (sum)", getModestyFilteredAmount($account->getCreditsSum(), $configuration));
        $toReturn .= renderFact("Debits (count)", $account->getDebitsCount());
        $toReturn .= renderFact("Debits (sum)", getModestyFilteredAmount($account->getDebitsSum(), $configuration));

        $toReturn .= renderFactHeading("Transactions");

        $toReturn .= "<table class='table table-striped table-condensed table-hover'><thead><tr><th>Date</th><th>Description</th><th class='r'>Credit</th><th class='r'>Debit</th></tr></thead><tbody>";

        foreach ($account->getTransactions() as $transaction)
        {
            $toReturn .= sprintf("<tr><td>%s</td><td title='ID: %s'>%s</td><td class='r'>%s</td><td class='r d'>%s</td></tr>", renderAsDate($transaction->getDate()), $transaction->getID(), renderPossiblyNull($transaction->getDescription(), "[None]"), getModestyFilteredAmount($transaction->getAmountCredited(), $configuration), getModestyFilteredAmount($transaction->getAmountDebited(), $configuration));
        }

        $toReturn .= "</tbody></table>";

        $toReturn .= "</div>";
        return $toReturn;
    }

    function getModestyFilteredAmount($value, $configuration)
    {
        $toReturn = "";

        if (isset($value))
        {
            $limit = NULL;
            if ($configuration->getModestyLimit() != NULL) {
                $limit = $configuration->getModestyLimit();
            }

            if ($limit == NULL || abs($value) <= $limit)
            {
                $toReturn = sprintf("%.2f", $value);
            }
            else
            {
                $toReturn = "?.??";
            }
        }

        return $toReturn;
    }

    function renderPossiblyNull($possiblyNull, $alternative) {
      if (!isset($possiblyNull)) {
          return $alternative;
      }
      else {
          return $possiblyNull;
      }
    }

    function renderQualification($qualification)
    {
        $toReturn = "<div class='fact'>";

        $toReturn .= renderFact("Type", $qualification->getType());
        $toReturn .= renderFact("Title", $qualification->getTitle());
        $toReturn .= renderFact("Provider", $qualification->getDataProvider());
        $toReturn .= renderFact("Provider URL", $qualification->getDataProviderUrl());

        $toReturn .= "</div>";

        return toReturn;
    }
    
    function renderFactHeading($heading)
    {
        return "<h3>" . $heading . "</h3>";
    }
    
    function renderUserProfile($profile)
    {
        $toReturn = "<div class='fact'>";
        
        $toReturn .= "<h2>User profile</h2>";
        $toReturn .= renderFact("Username", $profile->getUsername());
        $toReturn .= renderFact("Salutation", $profile->getSalutation());
        $toReturn .= renderFact("First name", $profile->getFirstName());
        $toReturn .= renderFact("Middle name", $profile->getMiddleName());
        $toReturn .= renderFact("Last name", $profile->getLastName());
        $toReturn .= renderFact("Date of birth", renderAsDate($profile->getDateOfBirth()));
        $toReturn .= renderFact("Age", $profile->getAge());
        $toReturn .= renderFact("Identity verified?", $profile->getIdentityAssured());
        $toReturn .= renderFact("Identity last verified?", renderAsDateTime($profile->getLastVerified()));
        $toReturn .= renderFact("Has a public profile?", $profile->getHasPublicProfile());
        $toReturn .= renderFact("Previous first name", $profile->getPreviousFirstName());
        $toReturn .= renderFact("Previous middle name", $profile->getPreviousMiddleName());
        $toReturn .= renderFact("Previous last name", $profile->getPreviousLastName());
        $toReturn .= renderFact("Profile URL", $profile->getProfileUrl());
        $toReturn .= renderFact("Profile short URL", $profile->getProfileShortUrl());
        $toReturn .= renderFact("Card image URL", $profile->getCardImageUrl());                  
                
        $toReturn .= renderFactHeading("Postal addresses");
        $ct = 0;
        if ($profile->getPostalAddresses() != null)
        {
            foreach ($profile->getPostalAddresses() as $address)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderAddress($address);
                $toReturn .= "</div>";
            }
        }
        
        $toReturn .= renderFactHeading("Phone numbers");
        $ct = 0;        
        if ($profile->getPhoneNumbers() != null)
        {
            foreach ($profile->getPhoneNumbers() as $phone)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderPhone($phone);
                $toReturn .= "</div>";
            }
        }
        
        $toReturn .= renderFactHeading("Email addresses");
        $ct = 0;        
        if ($profile->getEmailAddresses() != null)
        {
            foreach ($profile->getEmailAddresses() as $email)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderEmail($email);
                $toReturn .= "</div>";
            }
        }
        
        $toReturn .= renderFactHeading("Internet identities");
        $ct = 0;        
        if ($profile->getIdentities() != null)
        {
            foreach ($profile->getIdentities() as $identity)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderIdentity($identity);
                $toReturn .= "</div>";
            }
        }
        
        $toReturn .= renderFactHeading("Web properties");
        $ct = 0;        
        if ($profile->getWebProperties() != null)
        {
            foreach ($profile->getWebProperties() as $property)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderWebProperty($property);
                $toReturn .= "</div>";
            }
        }

        $toReturn .= renderFactHeading("Qualifications");
        $ct = 0;
        if ($profile->getQualifications() != null)
        {
            foreach ($profile->getQualifications() as $qualification)
            {
                $toReturn .= "<div class='fact'><h4>[" . $ct++ . "]</h4>";
                $toReturn .= renderQualification($qualification);
                $toReturn .= "</div>";
            }
        }
        
        if ($profile->getPublicProfile() != null)
        {
            $toReturn .= "<div class='fact'><h4>Public profile</h4>";
            $toReturn .= renderUserProfile($profile->getPublicProfile());
            $toReturn .= "</div>";
        }
        
        $toReturn .= "</div>";
        
        return $toReturn;
    }
?>