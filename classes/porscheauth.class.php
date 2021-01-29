<?php
namespace App;
ini_set("xdebug.var_display_max_children", '-1');
ini_set("xdebug.var_display_max_data", '-1');
ini_set("xdebug.var_display_max_depth", '-1');

require './vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * PorscheAuth class
 */
class PorscheAuth
{
    private string $email;
    private string $password;
    private Client $webSession;
    private string $accessToken;
    private string $refreshToken;
    private string $idToken;
    private int $expiration;

    private bool $tokenRefreshed = false;
    private CookieJar $cookieJar;
    private string $last_location = "";
    private string $auth_code = "";

    private string $lang = "gb/en_GB";

    private string $userAgent = "Android REL 4.4.4; en_US";
    private string $client_id = "TZ4Vf5wnKeipJxvatJ60lPHYEzqZ4WNp";
    private string $porscheCookiedomain = "https://login.porsche.com";
    private string $porscheLogin = "https://login.porsche.com/auth/gb/en_GB";
    private string $porscheLoginAuth = "https://login.porsche.com/auth/api/v1/gb/en_GB/public/login";
    private string $porscheAPIClientID = "TZ4Vf5wnKeipJxvatJ60lPHYEzqZ4WNp";
    private string $porscheAPIRedirectURI = "https://my-static02.porsche.com/static/cms/auth.html";
    private string $porscheAPIAuth = "https://login.porsche.com/as/authorization.oauth2";
    private string $porscheAPIToken = "https://login.porsche.com/as/token.oauth2";
    private string $porscheAPI = "https://connect-portal.porsche.com/core/api/v3/gb/en_GB";
    private string $porscheAPIVehicleByVin = "https://api.porsche.com/core/api/v3/gb/en_GB/vehicles/";

    public function __construct(
        string $user,
        string $pass,
        Client $session = null,
        string $access_token,
        string $refresh_token,
        string $id_token,
        int $expiration_date = 0
        )
    {
        $this->email = $user;
        $this->password = $pass;
        $this->webSession = $session;
        $this->accessToken = $access_token;
        $this->refreshToken = $refresh_token;
        $this->idToken = $id_token;
        $this->expiration = $expiration_date;

        $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();

        if ($this->webSession == null)
        {
            $this->webSession = new GuzzleHttp\Client(['cookies' => $this->cookieJar]);
        }
        else {
            // Set this->webSession header access_token and expiration
        }

        $this->authFlow();
    }

    public function getCar(string $url, int $vehicleNum = 0)
    {
        // Store this elsewhere to cut down
        // on copy/pastes, mang. 
        $headers = [
            "Authorization" => "Bearer ".$this->accessToken, 
            "apikey" => $this->porscheAPIClientID, 
            "User-Agent" => $this->userAgent
        ];

        $promise = $this->sendRequestAsync($url, $headers);

        $testResult = $promise->then(
            function (ResponseInterface $res) {
                // Use vehiclenum
                $car = json_decode($res->getBody())[0];

                // $this->vin = $car->vin;
                // $this->modelDescription = $car->modelDescription;
                // $this->modelYear = $car->modelYear;
                // $this->vehicleImage = $car->pictures[0]->url;

                $extras = $this->getCarInfoByVin($this->porscheAPIVehicleByVin . $car->vin);

                // Ehh, ICE vehicles do not have this? 
                // At least not Macan I think? Cannot test this myself
                // if ($extras->carControlData != null)
                // {
                //     $this->batteryLevel = $extras->carControlData->batteryLevel->value;
                // }

                //var_dump($extras);
                
                return $extras;
            },

            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );

        //$result = $promise->wait();
        $result = $testResult->wait();
        
        //$result = json_decode($result->getBody());

        return $result;
    }

    private function getCarInfoByVin(string $url)
    {
        // Store this elsewhere to cut down
        // on copy/pastes, mang. 
        $headers = [
            "Authorization" => "Bearer ".$this->accessToken, 
            "apikey" => $this->porscheAPIClientID, 
            "User-Agent" => $this->userAgent
        ];

        $promise = $this->sendRequestAsync($url, $headers);

        

        $promise->then(
            function (ResponseInterface $res) {
                // Use vehiclenum                
                $car = json_decode($res->getBody());
                return $car;
            },

            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );

        $result = $promise->wait();
        
        $result = json_decode($result->getBody());

        return $result;
    }

    private function authFlow()
    {
        $this->handlePorscheAuth();
    }

    private function handlePorscheAuth()
    {
        // Save these values for later use maybe?
        $formData = [
            'form_params' => [
                'sec' => "",
                'resume' => "",
                'thirdPartyId' => "",
                'state' => "",
                'username' => $this->email,
                'password' => $this->password,
                'keeploggedin' => false
            ],
            'allow_redirects' => [
                'max' => 30,
            ],
            'cookies' => $this->cookieJar
        ];

        // Handle wrong password (account temporarily locked)
        // /login/gb/en_GB?sec=high&resume=&state=ACCOUNT_TEMPORARILY_LOCKED&...
        $loginResponse = $this->postWebAsync($this->porscheLoginAuth, $formData);
        
        $loginResponse->then(
            function (ResponseInterface $res) {
                //echo $res->getStatusCode() . "\n";
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        
        $loginResponse->wait();

        $codeVerifier = $this->getCodeVerifier(40);
        $codeChallenge = $this->getCodeChallenge($codeVerifier);

        $this->handleAuthCode($codeChallenge);
        $this->handleApiToken($codeVerifier);
    }

    private function handleAuthCode(string $challenge)
    {
        $query = [ 
            "scope" => "openid", 
            "response_type" => "code", 
            "access_type" => "offline", 
            "prompt" => "none", 
            "client_id" => $this->porscheAPIClientID, 
            "redirect_uri" => $this->porscheAPIRedirectURI, 
            "code_challenge" => $challenge, 
            "code_challenge_method" => "S256" 
        ];

        $data = [
            'query' => $query,
            'allow_redirects' => [
                'max' => 30,
                'track_redirects' => true
            ],
            'cookies' => $this->cookieJar
        ];

        $authResponse = $this->getWebAsync($this->porscheAPIAuth, $data);

        $authResponse->then(
            function (ResponseInterface $res) {
                $lastRedir =  explode(",", $res->getHeaderLine('X-Guzzle-Redirect-History'));
                
                $this->last_location = end($lastRedir);

                $code = explode("?code=", $this->last_location);
                $this->auth_code = $code[1];               
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        
        $authResponse->wait();
    }

    private function handleApiToken(string $verifier)
    {
        $data = [
            'form_params' => [
                'grant_type' => "authorization_code",
                'client_id' => $this->porscheAPIClientID,
                'redirect_uri' => $this->porscheAPIRedirectURI,
                'code' => $this->auth_code,
                'prompt' => 'none',
                'code_verifier' => $verifier
            ],
            'allow_redirects' => [
                'max' => 30,
            ],
            'cookies' => $this->cookieJar
        ];

        $tokenResponse = $this->postWebAsync($this->porscheAPIToken, $data);

        $tokenResponse->then(
            function (ResponseInterface $res) {
                $token_data = json_decode($res->getBody());
                
                $this->idToken = $token_data->id_token;
        
                $this->accessToken = $token_data->access_token;
                $this->expiration = $token_data->expires_in;              
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        
        $tokenResponse->wait();
    }

    private function postWebAsync(string $url, array $data)
    {
        $response = $this->webSession->postAsync($url, $data);
        return $response;
    }

    private function sendRequestAsync(string $url, array $headers)
    {
        $request = new Request('GET', $url, $headers);
        $response = $this->webSession->sendAsync($request);

        return $response;
    }

    private function getWebAsync(string $url, array $data)
    {
        $response = $this->webSession->getAsync($url, $data);
        return $response;
    }

    private function getQueryAsync(string $url, string $query)
    {
        $response2 = $this->webSession->getAsync($url, [
            'query' => $query,
            'allow_redirects' => [
                'max' => 30,
                'track_redirects' => true
            ],
            'cookies' => $this->cookieJar
        ]);

        $response2->then(
            function (ResponseInterface $res) {
                $lastRedir =  explode(",", $res->getHeaderLine('X-Guzzle-Redirect-History'));
                
                $this->last_location = end($lastRedir);

                $code = explode("?code=", $this->last_location);
                $this->auth_code = $code[1];               
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        
        $response2->wait();
    }

    private function getCodeVerifier(int $byteLength)
    {
        $codeVerifier = $this->urlsafe_b64encode(random_bytes($byteLength));
        
        $pattern = "/[^a-zA-Z0-9]/";
        $replacement = "";

        $codeVerifier = preg_replace($pattern, $replacement, $codeVerifier);
        
        return $codeVerifier;
    }

    private function getCodeChallenge(string $verifier)
    {
        $codeChallenge = pack('H*', hash('sha256', $verifier));
        $codeChallenge = $this->urlsafe_b64encode($codeChallenge);
        $codeChallenge = str_replace("=", "", $codeChallenge);

        return $codeChallenge;
    }

    private function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/'),array('-','_'),$data);
        return mb_convert_encoding($data,'HTML-ENTITIES','utf-8');
        //return $data;
    }
    
    private function urlsafe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        //return mb_convert_encoding(base64_decode($data),'HTML-ENTITIES','utf-8');;
        return base64_decode($data);
    }
}
