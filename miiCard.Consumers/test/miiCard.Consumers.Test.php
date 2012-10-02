<?php
    require_once('../miiCard.Model.php');

    class MiiUserProfileTest extends PHPUnit_Framework_TestCase
    {
        private $_jsonBody = '{"CardImageUrl":"https:\/\/my.miicard.com\/img\/test.png","EmailAddresses":[{"Verified":true,"Address":"test@example.com","DisplayName":"testEmail","IsPrimary":true},{"Verified":false,"Address":"test2@example.com","DisplayName":"test2Email","IsPrimary":false}],"FirstName":"Test","HasPublicProfile":true,"Identities":null,"IdentityAssured":true,"LastName":"User","LastVerified":"\/Date(1345812103)\/","MiddleName":"Middle","PhoneNumbers":[{"Verified":true,"CountryCode":"44","DisplayName":"Default","IsMobile":true,"IsPrimary":true,"NationalNumber":"7800123456"},{"Verified":false,"CountryCode":"44","DisplayName":"Default","IsMobile":false,"IsPrimary":false,"NationalNumber":"7800123457"}],"PostalAddresses":[{"House":"Addr1 House1","Line1":"Addr1 Line1","Line2":"Addr1 Line2","City":"Addr1 City","Region":"Addr1 Region","Code":"Addr1 Code","Country":"Addr1 Country","IsPrimary":true,"Verified":true},{"House":"Addr2 House1","Line1":"Addr2 Line1","Line2":"Addr2 Line2","City":"Addr2 City","Region":"Addr2 Region","Code":"Addr2 Code","Country":"Addr2 Country","IsPrimary":false,"Verified":false}],"PreviousFirstName":"PrevFirst","PreviousLastName":"PrevLast","PreviousMiddleName":"PrevMiddle","ProfileShortUrl":"http:\/\/miicard.me\/123456","ProfileUrl":"https:\/\/my.miicard.com\/card\/test","PublicProfile":{"CardImageUrl":"https:\/\/my.miicard.com\/img\/test.png","FirstName":"Test","HasPublicProfile":true,"IdentityAssured":true,"LastName":"User","LastVerified":"\/Date(1345812103)\/","MiddleName":"Middle","PreviousFirstName":"PrevFirst","PreviousLastName":"PrevLast","PreviousMiddleName":"PrevMiddle","ProfileShortUrl":"http:\/\/miicard.me\/123456","ProfileUrl":"https:\/\/my.miicard.com\/card\/test","PublicProfile":null,"Salutation":"Ms","Username":"testUser"},"Salutation":"Ms","Username":"testUser","WebProperties":[{"Verified":true,"DisplayName":"example.com","Identifier":"example.com","Type":0},{"Verified":false,"DisplayName":"2.example.com","Identifier":"http:\/\/www.2.example.com","Type":1}]}';
        private $_jsonResponseBody = '{"ErrorCode":0,"Status":0,"ErrorMessage":"A test error message","Data":true}';

        public function testCanDeserialiseUserProfile()
        {
            $o = MiiUserProfile::FromHash(json_decode($this->_jsonBody, true));

            $this->assertBasics($o);

            # Email addresses
            $emails = $o->getEmailAddresses();

            $email1 = $emails[0];

            $this->assertEquals(true, $email1->getVerified());
            $this->assertEquals("test@example.com", $email1->getAddress());
            $this->assertEquals("testEmail", $email1->getDisplayName());
            $this->assertEquals(true, $email1->getIsPrimary());

            $email2 = $emails[1];
            $this->assertEquals(false, $email2->getVerified());
            $this->assertEquals("test2@example.com", $email2->getAddress());
            $this->assertEquals("test2Email", $email2->getDisplayName());
            $this->assertEquals(false, $email2->getIsPrimary());

            # Phone numbers
            $phones = $o->getPhoneNumbers();

            $phone1 = $phones[0];
            $this->assertEquals(true, $phone1->getVerified());
            $this->assertEquals("44", $phone1->getCountryCode());
            $this->assertEquals("Default", $phone1->getDisplayName());
            $this->assertEquals(true, $phone1->getIsMobile());
            $this->assertEquals(true, $phone1->getIsPrimary());
            $this->assertEquals("7800123456", $phone1->getNationalNumber());

            $phone2 = $phones[1];
            $this->assertEquals(false, $phone2->getVerified());
            $this->assertEquals("44", $phone2->getCountryCode());
            $this->assertEquals("Default", $phone2->getDisplayName());
            $this->assertEquals(false, $phone2->getIsMobile());
            $this->assertEquals(false, $phone2->getIsPrimary());
            $this->assertEquals("7800123457", $phone2->getNationalNumber());

            # Web properties
            $props = $o->getWebProperties();

            $prop1 = $props[0];
            $this->assertEquals(true, $prop1->getVerified());
            $this->assertEquals("example.com", $prop1->getDisplayName());
            $this->assertEquals("example.com", $prop1->getIdentifier());
            $this->assertEquals(WebPropertyType::DOMAIN, $prop1->getType());

            $prop2 = $props[1];
            $this->assertEquals(false, $prop2->getVerified());
            $this->assertEquals("2.example.com", $prop2->getDisplayName());
            $this->assertEquals("http://www.2.example.com", $prop2->getIdentifier());
            $this->assertEquals(WebPropertyType::WEBSITE, $prop2->getType());

            # Postal addresses
            $addrs = $o->getPostalAddresses();

            $addr1 = $addrs[0];
            $this->assertEquals("Addr1 House1", $addr1->getHouse());
            $this->assertEquals("Addr1 Line1", $addr1->getLine1());
            $this->assertEquals("Addr1 Line2", $addr1->getLine2());
            $this->assertEquals("Addr1 City", $addr1->getCity());
            $this->assertEquals("Addr1 Region", $addr1->getRegion());
            $this->assertEquals("Addr1 Code", $addr1->getCode());
            $this->assertEquals("Addr1 Country", $addr1->getCountry());
            $this->assertEquals(true, $addr1->getIsPrimary());
            $this->assertEquals(true, $addr1->getVerified());

            $addr2 = $addrs[1];
            $this->assertEquals("Addr2 House1", $addr2->getHouse());
            $this->assertEquals("Addr2 Line1", $addr2->getLine1());
            $this->assertEquals("Addr2 Line2", $addr2->getLine2());
            $this->assertEquals("Addr2 City", $addr2->getCity());
            $this->assertEquals("Addr2 Region", $addr2->getRegion());
            $this->assertEquals("Addr2 Code", $addr2->getCode());
            $this->assertEquals("Addr2 Country", $addr2->getCountry());
            $this->assertEquals(false, $addr2->getIsPrimary());
            $this->assertEquals(false, $addr2->getVerified());

            $this->assertEquals(true, $o->getHasPublicProfile());

            $pp = $o->getPublicProfile();
            $this->assertBasics($pp);
            $this->assertEquals("testUser", $pp->getUsername());
        }

        public function testCanDeserialiseBoolean()
        {
            $o = MiiApiResponse::FromHash(json_decode($this->_jsonResponseBody, true), null);

            $this->assertEquals(MiiApiCallStatus::SUCCESS, $o->getStatus());
            $this->assertEquals(MiiApiErrorCode::SUCCESS, $o->getErrorCode());
            $this->assertEquals("A test error message", $o->getErrorMessage());
            $this->assertEquals(true, $o->getData());
        }

        private function assertBasics($obj)
        {
            $this->assertNotNull($obj);

            $this->assertEquals("https://my.miicard.com/img/test.png", $obj->getCardImageUrl());
            $this->assertEquals("Test", $obj->getFirstName());
            $this->assertEquals("Middle", $obj->getMiddleName());
            $this->assertEquals("User", $obj->getLastName());

            $this->assertEquals("PrevFirst", $obj->getPreviousFirstName());
            $this->assertEquals("PrevMiddle", $obj->getPreviousMiddleName());
            $this->assertEquals("PrevLast", $obj->getPreviousLastName());

            $this->assertEquals(true, $obj->getIdentityAssured());
            $this->assertEquals("/Date(1345812103)/", $obj->getLastVerified());

            $this->assertEquals(true, $obj->getHasPublicProfile());
            $this->assertEquals("http://miicard.me/123456", $obj->getProfileShortUrl());
            $this->assertEquals("https://my.miicard.com/card/test", $obj->getProfileUrl());
            $this->assertEquals("Ms", $obj->getSalutation());
            $this->assertEquals("testUser", $obj->getUsername());
        }
    }
?>