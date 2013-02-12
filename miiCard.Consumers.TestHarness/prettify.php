<?php
    /* A couple of functions render date-times and these come in as UTC (equiv. GMT) */
    date_default_timezone_set("GMT");

    function renderResponse($obj)
    {
        $toReturn = "<div class='response'>";
        
        $toReturn .= renderFact("Status", $obj->getStatus());
        $toReturn .= renderFact("Error code", $obj->getErrorCode());
        $toReturn .= renderFact("Error message", $obj->getErrorMessage());
        $toReturn .= renderFact("Is a test user?", $obj->getIsTestUser());
                
        $data = $obj->getData();
        
        if ($data instanceof MiiUserProfile)
        {
            $toReturn .= renderUserProfile($data);
        }
        else if ($data instanceof IdentitySnapshot)
        {
            $toReturn .= renderIdentitySnapshot($data);
        }
        else if ($data instanceof IdentitySnapshotDetails)
        {
            $toReturn .= renderIdentitySnapshotDetails($data);
        }
        else if (is_array($data) && count($data) > 0)
        {
            $sample = $data[0];
            if ($sample instanceof IdentitySnapshotDetails)
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