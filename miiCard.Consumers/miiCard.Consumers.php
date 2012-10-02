<?php
    require_once('oauth/OAuth.php');
    require_once('miiCard.Model.php');

    class OAuthSignedRequestMaker
    {
        private $_consumerKey, $_consumerSecret;
        protected $_accessToken, $_accessTokenSecret;

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

        protected function makeSignedRequest($url, $params = array(), $headers = array())
        {
            $consumerToken = new OAuthToken($this->_consumerKey, $this->_consumerSecret);
            $accessToken = null;

            if ($this->getAccessToken() != null && $this->getAccessTokenSecret() != null)
            {
                $accessToken = new OAuthToken($this->getAccessToken(), $this->getAccessTokenSecret());
            }

            $request = OAuthRequest::from_consumer_and_token($consumerToken, $accessToken, 'POST', $url, $params);
            $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumerToken, $accessToken);

            return $this->makeHttpRequest($request->get_normalized_http_url(), $request->get_parameters(), $headers);
        }

        private function makeHttpRequest($url, $params = array(), $headers = array())
        {
            $data = '';
            if (count($params) > 0)
            {
                $data = http_build_query($params);
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
                // TODO: Remove after beta testing!
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FORBID_REUSE => TRUE,
                CURLOPT_FRESH_CONNECT => TRUE,
            );

            $curl_options[CURLOPT_POST] = TRUE;
            $curl_options[CURLOPT_POSTFIELDS] = $data;

            $ch = curl_init();
            curl_setopt_array($ch, $curl_options);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            return $response;
        }
    }

    class MiiCardException extends Exception {}

    class MiiCardOAuthServiceBase extends OAuthSignedRequestMaker
    {
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

    class MiiCardOAuthClaimsService extends MiiCardOAuthServiceBase
    {
        function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
        {
            parent::__construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        }

        public function getClaims()
        {

        }

        public function isSocialAccountAssured()
        {

        }

        public function isUserAssured()
        {

        }

        public function assuranceImage()
        {

        }

        private function makeRequest($url, $postData, $payloadProcessor, $wrappedResponse)
        {

        }
    }

    class MiiCardServiceUrls
    {
        // const OAUTH_ENDPOINT = "https://127.0.0.1:444/auth/oauth.ashx";
        const OAUTH_ENDPOINT = "https://sts.miicard.com/auth/oauth.ashx";
        const CLAIMS_SVC = "https://sts.miicard.com/api/v1/Claims.svc/json";

        public function getMethodUrl($method)
        {
            return MiiCardServiceUrls::CLAIMS_SVC . "/" . $method;
        }
    }

    class MiiCard extends OAuthSignedRequestMaker
    {
        private $_callbackUrl;

        const SESSION_KEY_ACCESS_TOKEN = "miiCard.OAuth.InProgress.AccessToken";
        const SESSION_KEY_ACCESS_TOKEN_SECRET = "miiCard.OAuth.InProgress.AccessTokenSecret";

        function __construct($consumerKey, $consumerSecret, $accessToken = null, $accessTokenSecret = null)
        {
            parent::__construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

            $this->_callbackUrl = $this->getDefaultCallbackUrl();
        }

        public function getAccessToken()
        {
            $toReturn = parent::getAccessToken();

            if ($toReturn == null && isset($_SESSION) && isset($SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN]))
            {
                $toReturn = $SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN];
            }

            return $toReturn;
        }

        public function getAccessTokenSecret()
        {
            $toReturn = parent::getAccessTokenSecret();
            if ($toReturn == null && isset($_SESSION) && isset($SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET]))
            {
                $toReturn = $SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET];
            }

            return $toReturn;
        }

        public function getDefaultCallbackUrl()
        {
            $isHttps = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
            $httpProtocol = $isHttps ? 'https' : 'http';

            return $httpProtocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        }

        public function beginAuthorisation($callbackUrl = null)
        {
            if (isset($callbackUrl))
            {
                $this->_callbackUrl = $callbackUrl;
            }

            $requestToken = $this->getRequestToken();

            $this->ensureSessionAvailable();
            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN] = $this->getAccessToken();
            $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET] = $this->getAccessTokenSecret();

            header('Location: ' . MiiCardServiceUrls::OAUTH_ENDPOINT . "?oauth_token=" . $requestToken->getKey(), TRUE, 302);
            $this->ensureSessionCommitted();

            exit;
        }

        public function isAuthorisationCallback()
        {
            return isset($_GET['oauth_verifier']);
        }

        public function handleAuthenticationCallback()
        {
            $token = array_key_exists('oauth_token', $_REQUEST) ? $_REQUEST['oauth_token'] : '';
            $verifier = array_key_exists('oauth_verifier', $_REQUEST) ? $_REQUEST['oauth_verifier'] : '';

            if (empty($token) || empty($verifier))
            {
                return;
            }

            $this->processAccessToken($verifier);
        }

        public function isAuthorisationSuccess()
        {
            return $this->getAccessToken() != null && $this->getAccessTokenSecret();
        }

        public function getUserProfile()
        {
            if ($this->getAccessToken() == null || $this->getAccessTokenSecret() == null)
            {
                throw new MiiCardException("You must set the access token and access token secret to make calls into the miiCard API");
            }
            else
            {

            }
        }

        public function clearMiiCard()
        {
            if (isset($_SESSION))
            {
                $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN] = null;
                $_SESSION[MiiCard::SESSION_KEY_ACCESS_TOKEN_SECRET] = null;
            }
        }

        protected function getRequestToken()
        {
            $url = MiiCardServiceUrls::OAUTH_ENDPOINT;
            $params = array('oauth_callback' => $this->_callbackUrl);

            echo var_dump($params);

            $response = $this->makeSignedRequest($url, $params);
            parse_str($response, $token);
            if (!array_key_exists('oauth_token', $token))
            {
                throw new MiiCardException("No token received from OAuth service - check credentials:\n" . var_dump($token));
            }

            return new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }

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
            $this->_accessTokenSecret = $token['oauth_token_secret'];

            return new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        }

        private function ensureSessionAvailable()
        {
            if (!session_id())
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

        private function ensureSessionCommitted()
        {
            session_write_close();
        }
    }
?>