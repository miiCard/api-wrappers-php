<?php

/** @package miiCard.Consumers */
    require_once('oauth/OAuth.php');
    require_once('miiCard.Model.php');

    /** Houses the URLs of the OAuth endpoint and Claims API endpoint.
     *
     *@package miiCard.Consumers         */
    class MiiCardServiceUrls
    {
        /** URL of the OAuth authorisation endpoint. */
        const OAUTH_ENDPOINT = "https://127.0.0.1:444/auth/oauth.ashx";
        /** URL of the Claims API v1 JSON endpoint. */
        const CLAIMS_SVC = "https://127.0.0.1:444/api/v1/Claims.svc/json";

        /** Calculates the URL to be requested when the specified method name
        * of the Claims API is to be invoked.
        *
        * @param string $method The name of the API method to be invoked. */
        public static function getMethodUrl($method)
        {
            return MiiCardServiceUrls::CLAIMS_SVC . "/" . $method;
        }
    }

    /** Base class for classes that make OAuth 1.0a-signed HTTP requests
     *
     * @abstract
     * @package miiCard.Consumers */
    abstract class OAuthSignedRequestMaker
    {
        /** The OAuth consumer key. */
        private $_consumerKey;
        /** The OAuth consumer secret. */
        private $_consumerSecret;
        /** The OAuth access token, if known. */
        protected $_accessToken;
        /** The OAuth access token secret, if known. */
        protected $_accessTokenSecret;

        /** Initialises a new OAuthSignedRequestMaker with specified key and secret information.
        *
        * A consumer key and secret are mandatory - without them an InvalidArgumentException is thrown.
        * The caller may supply an access token and secret if they are known, or omit them if they intend
        * to make requests that aren't signed by an access token and secret (for example, as would be the
        * case during an initial OAuth exchange).
        *
        *@param string $consumerKey The OAuth consumer key obtained by request from miiCard.
        *@param string $consumerSecret The OAuth consumer secret obtained by request from miiCard.
        *@param string $accessToken The OAuth access token obtained by performing an OAuth exchange.
        *@param string $accessTokenSecret The OAuth access token secret obtained by performing an OAuth exchange. */
        function __construct($consumerKey, $consumerSecret, $accessToken = null, $accessTokenSecret = null)
        {
            if (!isset($consumerKey))
            {
                throw new InvalidArgumentException("consumerKey cannot be null");
            }
            else if (!isset($consumerSecret))
            {
                throw new InvalidArgumentException("consumerSecret cannot be null");
            }

            $this->_consumerKey = $consumerKey;
            $this->_consumerSecret = $consumerSecret;
            $this->_accessToken = $accessToken;
            $this->_accessTokenSecret = $accessTokenSecret;
        }

        /** Gets the OAuth consumer key */
        public function getConsumerKey() { return $this->_consumerKey; }
        /** Gets the OAuth consumer secret */
        public function getConsumerSecret() { return $this->_consumerSecret; }

        /** Gets the OAuth access token, or null if not set */
        public function getAccessToken()
        {
            if (isset($this->_accessToken))
            {
                return $this->_accessToken;
            }
            else
            {
                return null;
            }
        }

        /** Gets the OAuth access token secret, or null if not set */
        public function getAccessTokenSecret()
        {
            if (isset($this->_accessTokenSecret))
            {
                return $this->_accessTokenSecret;
            }
            else
            {
                return null;
            }
        }

        /** Makes an OAuth-signed HTTP POST request
         *
         * Makes signed requests both during the initial OAuth exchange (where OAuth parameters are sent form-encoded
         * as part of the body of the request, with $rawPostBody = false) and afterwards when accessing the API (when
         * JSON-encoded parameters are sent raw in the body of the request).
         *
         *@param string $url The URL to be requested.
         *@param mixed $params An array of parameters to be sent with the request as a post body (if $rawPostBody = false),
         *or a string containing the raw post body to be sent (if $rawPostBody = true).
         *@param array $headers Additional HTTP headers to be sent with the request.
         *@param bool $rawPostBody If true, $params is interpreted as pre-parsed content to be dropped into the body of the
         *request as-is. If false, the default, $params is interpreted as an array of key-value pairs to be form-encoded and
         *sent in the body of the request. */
        protected function makeSignedRequest($url, $params, $headers = array(), $rawPostBody = false)
        {
            $consumerToken = new OAuthToken($this->_consumerKey, $this->_consumerSecret);
            $accessToken = null;

            if ($this->getAccessToken() != null && $this->getAccessTokenSecret() != null)
            {
                $accessToken = new OAuthToken($this->getAccessToken(), $this->getAccessTokenSecret());
            }

            if ($rawPostBody)
            {
            	$request = OAuthRequest::from_consumer_and_token($consumerToken, $accessToken, 'POST', $url, null);
            }
            else
            {
                $request = OAuthRequest::from_consumer_and_token($consumerToken, $accessToken, 'POST', $url, $params);            
            }

            $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumerToken, $accessToken);

            if ($rawPostBody)
            {
                array_push($headers, $request->to_header());
            }

            if ($rawPostBody)
            {
                return $this->makeHttpRequest($request->get_normalized_http_url(), $params, $headers, $rawPostBody);
            }
            else
            {   
                return $this->makeHttpRequest($request->get_normalized_http_url(), $request->get_parameters(), $headers, $rawPostBody);
            }
        }

        /** Makes an HTTP Post request, returning the response.
         *
         *@param string $url The URL to be requested.
         *@param mixed $params An array of parameters to be sent with the request as a post body (if $rawPostBody = false),
         *or a string containing the raw post body to be sent (if $rawPostBody = true).
         *@param array $headers Additional HTTP headers to be sent with the request.
         *@param bool $rawPostBody If true, $params is interpreted as pre-parsed content to be dropped into the body of the
         *request as-is. If false, the default, $params is interpreted as an array of key-value pairs to be form-encoded and
         *sent in the body of the request. */
        private function makeHttpRequest($url, $params, $headers = array(), $rawPostBody = false)
        {
            if (!$rawPostBody)
            {     
              $data = '';
              if (isset($params) && is_array($params) && count($params) > 0)
              {
                  $data = http_build_query($params);
              }
            }
            
            $uri = @parse_url($url);
            $path = isset($uri['path']) ? $uri['path'] : '/';

            if (isset($uri['query']))
            {
                $path .= '?' . $uri['query'];
            }

            $start = microtime(TRUE);
            $port = isset($uri['port']) ? $uri['port'] : 443;
            $socket = 'ssl://' . $uri['host'] . ':' . $port;
            $headers += array
            (
            	'Accept:',
            	'Host: ' . $uri['host'] . ($port != 443 ? ':' . $port : ''),
              'User-Agent: miiCard PHP'
            );

            $curl_options = array
            (
                CURLOPT_URL => $url,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_CAINFO => dirname(__FILE__) . "/certs/sts.miicard.com.pem",
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FORBID_REUSE => TRUE,
                CURLOPT_FRESH_CONNECT => TRUE,
            );

            if ($rawPostBody)
            {
                $curl_options[CURLOPT_POSTFIELDS] = $params;
            }
            else 
            {
                $curl_options[CURLOPT_POSTFIELDS] = $data;            
            }             

            $ch = curl_init();
            curl_setopt_array($ch, $curl_options);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            return $response;
        }
    }

    /** Base class for exceptions raised by the library
     *
     *@package miiCard.Consumers         */
    class MiiCardException extends Exception {}

    /** Base class for wrappers around an OAuth-protected API
     *
     *@abstract
     *@package miiCard.Consumers     */
    abstract class MiiCardOAuthServiceBase extends OAuthSignedRequestMaker
    {
        /** Initialises a new MiiCardOAuthServiceBase with specified OAuth credentials.
         *
         *@param string $consumerKey The OAuth consumer key.
         *@param string $consumerSecret The OAuth consumer secret.
         *@param string $accessToken The OAuth access token.
         *@param string $accessTokenSecret The OAuth access token secret. */
        function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
        {
            if (!isset($accessToken))
            {
                throw new InvalidArgumentException("accessToken cannot be null");
            }
            else if (!isset($accessTokenSecret))
            {
                throw new InvalidArgumentException("accessTokenSecret cannot be null");
            }

            parent::__construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        }
    }

    /** Wrapper around the miiCard Claims API v1.
     *
     * This class wraps the miiCard Claims API v1, exposing the same methods as PHP functions
     * and return types as PHP objects rather than raw JSON.
     *
     * @package miiCard.Consumers         */
    class MiiCardOAuthClaimsService extends MiiCardOAuthServiceBase
    {
        /** Initialises a new MiiCardOAuthClaimsService with specified OAuth credentials.
         *
         * If any constructor parameters are omitted, an InvalidArgumentException shall be thrown.
         *
         *@param string $consumerKey The OAuth consumer key.
         *@param string $consumerSecret The OAuth consumer secret.
         *@param string $accessToken The OAuth access token.
         *@param string $accessTokenSecret The OAuth access token secret. */
        function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
        {
            parent::__construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        }

        /** Gets the identity claims that the miiCard member has shared with your application. */
        public function getClaims()
        {
            return $this->makeRequest('GetClaims', null, 'MiiUserProfile::FromHash', true);
        }

        /** Gets whether the miiCard member owns a social media account identified by the specified
         * ID and type, assuming that they have shared details of that account with your application.
         *
         *@param string $socialAccountId The ID of the user on the social network in question as supplied by that
         *social network - see the miiCard Developers site API documentation for more details.
         *@param string $socialAccountType The network on which thr miiCard member may have an account - see the
         *miiCard Developers site API documentation for more details. */
        public function isSocialAccountAssured($socialAccountId, $socialAccountType)
        {
            $requestArray = array();
            $requestArray['socialAccountId'] = $socialAccountId;
            $requestArray['socialAccountType'] = $socialAccountType;
                        
            return $this->makeRequest('IsSocialAccountAssured', json_encode($requestArray), null, true);
        }

        /** Gets whether the miiCard member's identity has been assured by miiCard through linking a
         *financial account to their miiCard profile. */
        public function isUserAssured()
        {
            return $this->makeRequest('IsUserAssured', null, null, true);
        }

        /** Gets a PNG-encoded image representation of the miiCard member's identity assurance status.
         *
         *@param string $type One of 'banner', 'badge-small' or 'badge' that determines the size and content
         *of the assurance image. */
        public function assuranceImage($type)
        {
            $requestArray = array();
            $requestArray['type'] = $type;
            
            return $this->makeRequest('AssuranceImage', json_encode($requestArray), null, false);
        }

        /** Gets details of a snapshot identified by its ID, or of all snapshots for the miiCard member if
         *not supplied.
         *@param string $snapshotId The unique identifier of the snapshot for which details should be
         *retrieved, or null if details of all snapshots should be retrieved. */
        public function getIdentitySnapshotDetails($snapshotId)
        {
            $requestArray = array();
            if (isset($snapshotId))
            {
                $requestArray['snapshotId'] = $snapshotId;
            }

            return $this->makeRequest('GetIdentitySnapshotDetails', json_encode($requestArray), 'IdentitySnapshotDetails::FromHash', true, true);
        }

        /** Gets the snapshot of a miiCard member's identity specified by the supplied snapshot ID. To discover existing
         *snapshots, use the getIdentitySnapshotDetails function.
         *@param string $snapshotId The unique identifier of the snapshot for which details should be
         *retrieved. */
        public function getIdentitySnapshot($snapshotId)
        {
            $requestArray = array();
            $requestArray['snapshotId'] = $snapshotId;

            return $this->makeRequest('GetIdentitySnapshot', json_encode($requestArray), 'IdentitySnapshot::FromHash', true);
        }

        /** Makes an OAuth signed request to the specified Claims API method, and parses the response into
         * an appropriate PHP object.
         *
         *@param string $methodName The name of the Claims API method to invoke.
         *@param string $postData JSON string of parameter data required by the API method, if any.
         *@param string $payloadProcessor Callable to be invoked to process the payload of the response, if any.
         *@param bool $wrappedResponse Specifies whether the response from the API is wrapped in a MiiApiResponse object (true), or is
         *a raw stream (false).
         *@param bool $arrayTypePayload Specifies that the payload of the response is an array-type - examples would be the GetIdentitySnapshotDetails
         *call which returns an array of IdentitySnapshotDetails objects. */
        private function makeRequest($methodName, $postData, $payloadProcessor, $wrappedResponse, $arrayTypePayload = false)
        {
            $response = $this->makeSignedRequest(MiiCardServiceUrls::getMethodUrl($methodName), $postData, array(0 => "Content-Type: application/json"), true);
            if ($response != null)
            {
                if ($wrappedResponse) 
                {
                    $response = json_decode($response, true);
                    return MiiApiResponse::FromHash($response, $payloadProcessor, $arrayTypePayload);
                }
                else if ($payloadProcessor != null)
                {
                    return call_user_func($payloadProcessor, $response);
                }
                else
                {
                    return $response;
                }
            }
            else
            {
                throw new MiiCardException("An empty response was received from the server");
            }
        }
    }

    /** Wrapper around the miiCard OAuth authorisation process.
     * @package miiCard.Consumers    */
    class MiiCard extends OAuthSignedRequestMaker
    {
        /** The callback URL that the OAuth process will return to once completed. */
        private $_callbackUrl;
        private $_referrerCode;

        /** @access private */ const SESSION_KEY_ACCESS_TOKEN = "miiCard.OAuth.InProgress.AccessToken";
        /** @access private */ const SESSION_KEY_ACCESS_TOKEN_SECRET = "miiCard.OAuth.InProgress.AccessTokenSecret";

        /** Builds a new MiiCard object using the supplied OAuth credentials.
         *
         *@param string $consumerKey The OAuth consumer key.
         *@param string $consumerSecret The OAuth consumer secret.
         *@param string $accessToken The OAuth access token.
         *@param string $accessTokenSecret The OAuth access token secret.
         *@param string $referrerCode Your referrer code, if you have one.
         */
        function __construct($consumerKey, $consumerSecret, $accessToken = null, $accessTokenSecret = null, $referrerCode = null)
        {
            parent::__construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

            $this->_callbackUrl = $this->getDefaultCallbackUrl();
            $this->_referrerCode = $referrerCode;
        }

        /** Gets the access token that this MiiCard object was constructed with, or was obtained via an OAuth exchange. */
        public function getAccessToken()
        {
            $toReturn = parent::getAccessToken();
            if ($toReturn == null && isset($_SESSION) && isset($_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN]))
            {
                $toReturn = $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN];
            }

            return $toReturn;
        }

        /** Gets the access token secret that this MiiCard object was constructed with, or was obtained via an OAuth exchange. */
        public function getAccessTokenSecret()
        {
            $toReturn = parent::getAccessTokenSecret();
            if ($toReturn == null && isset($_SESSION) && isset($_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET]))
            {
                $toReturn = $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET];
            }

            return $toReturn;
        }

        /** Gets the default callback URL that the OAuth process will return to once completed. */
        public function getDefaultCallbackUrl()
        {
            $isHttps = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
            $httpProtocol = $isHttps ? 'https' : 'http';

            return $httpProtocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        }

        /** Starts an OAuth authorisation process. Your script must not have sent any HTML content to the
        * browser at the point when this is called, and should have called session_start().
        *
        *@param string $callbackUrl The URL that should be returned to after the OAuth process completes. This
        *is automatically detected if not supplied. */
        public function beginAuthorisation($callbackUrl = null)
        {
            $this->ensureSessionAvailable();
            $this->clearMiiCard();
       
            if (isset($callbackUrl))
            {
                $this->_callbackUrl = $callbackUrl;
            }

            $requestToken = $this->getRequestToken();

            $this->_accessToken = $requestToken->getKey();
            $this->_accessTokenSecret = $requestToken->getSecret();

            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN] = $this->getAccessToken();
            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET] = $this->getAccessTokenSecret();
            
            $redirectUrl = MiiCardServiceUrls::OAUTH_ENDPOINT . "?oauth_token=" . rawurlencode($requestToken->getKey());
            if (isset($this->_referrerCode))
            {
                $redirectUrl .= "&referrer=" . $this->_referrerCode;
            }
            
            // Doing a header here means we never set the session cookie, which is bad if we're the first thing
            // that ever tries as we'll forget the request token secret. Instead, do a quick bounce through a
            // meta refresh
            ?>
                <html><head><meta http-equiv="refresh" content="0;<?php echo $redirectUrl ?>"></head>
                <body>You should be redirected automatically - if not, <a href="<?php echo $redirectUrl ?>">click here</a>.</body></html>
            <?php
            
            exit(0);
        }

        /** Determines whether the current request is the result of a callback from the OAuth exchange.
         *
         * The caller should check this function on each page load of the callback page, and attempt to handle the
         * OAuth callback only in the event that it returns true. */
        public function isAuthorisationCallback()
        {
            return isset($_GET['oauth_verifier']);
        }

        /** Processes the OAuth callback, obtaining an access token and access token secret in the process.
         *
         * The caller should check the return value of the isAuthorisationSuccess function after trying to handle
         * the callback. */
        public function handleAuthorisationCallback()
        {
            $this->ensureSessionAvailable();
                    
            $token = array_key_exists('oauth_token', $_REQUEST) ? $_REQUEST['oauth_token'] : '';
            $verifier = array_key_exists('oauth_verifier', $_REQUEST) ? $_REQUEST['oauth_verifier'] : '';
            
            if (empty($token) || empty($verifier))
            {
                return;
            }

            $this->processAccessToken($verifier);
        }

        /** Determines if the attempt to obtain OAuth access token and secret information was successful. If true,
        * the caller can obtain the two tokens using the getAccessToken and getAccessTokenSecret functions. */
        public function isAuthorisationSuccess()
        {
            return $this->getAccessToken() != null && $this->getAccessTokenSecret() != null;
        }

        /** Gets the identity claims the miiCard member elected to share with your application. This is a convenience method,
         *and building a MiiCardOAuthClaimsService object is the preferred approach. */
        public function getUserProfile()
        {
            if ($this->getAccessToken() == null || $this->getAccessTokenSecret() == null)
            {
                throw new MiiCardException("You must set the access token and access token secret to make calls into the miiCard API");
            }
            else
            {
                $api = new MiiCardOAuthClaimsService($this->getConsumerKey(), $this->getConsumerSecret(), $this->getAccessToken(), $this->getAccessTokenSecret());
                $response = $api->getClaims();

                if ($response->getStatus() == MiiApiCallStatus::SUCCESS)
                {
                    return $response->getData();
                }
                else
                {
                    return null;
                }
            }
        }

        /** Clears any OAuth credentials that might be stored ahead of performing a new OAuth exchange. This is
        * called automatically by the beginAuthorisation method. */
        public function clearMiiCard()
        {
            if (isset($_SESSION))
            {
                $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN] = null;
                $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET] = null;
            }
        }

        /** Obtains an OAuth request token from the miiCard OAuth endpoint. */
        protected function getRequestToken()
        {
            $url = MiiCardServiceUrls::OAUTH_ENDPOINT;
            $params = array('oauth_callback' => $this->_callbackUrl);

            $response = $this->makeSignedRequest($url, $params);
            parse_str($response, $token);
            
            if (!array_key_exists('oauth_token', $token))
            {
                throw new MiiCardException("No token received from OAuth service - check credentials");
            }

            return new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }

        /** Converts the request token currently stored by this object into a fully-fledged access token
         *using the specified server-supplied verifier.
         *
         *@param string $verifier The server supplied verifier that signifies the request token has been authorised
         *by the miiCard member. */
        protected function processAccessToken($verifier)
        {
            $url = MiiCardServiceUrls::OAUTH_ENDPOINT;
            $params = array('oauth_verifier' => $verifier);

            $response = $this->makeSignedRequest($url, $params);
            if (empty($response))
            {
                throw new MiiCardException('Nothing received from miiCard');
            }
            parse_str($response, $token);

            $this->_accessToken = $token['oauth_token'];
            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN] = $token['oauth_token'];
            
            $this->_accessTokenSecret = $token['oauth_token_secret'];
            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET] = $token['oauth_token_secret'];

            return new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }

        /** Attempts to make sure that session state is available. */
        private function ensureSessionAvailable()
        {
            if (session_id() == "")
            {
                // Save current session data before starting it, as PHP will destroy it.
                $session_data = isset($_SESSION) ? $_SESSION : NULL;
                session_start();

                // Restore session data.
                if (!empty($session_data))
                {
                    $_SESSION += $session_data;
                }
            }
        }
    }
?>