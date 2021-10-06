<?php
namespace amoCrm\actions\auth;

class AmoCrmAuthData
{
    public $domain;
    public $appId;
    public $authCode;
    public $secretKey;
    public $redirectLink;

    public function validate(): bool
    {
        return
            isset($this->appId)
            && isset($this->authCode)
            && isset($this->secretKey)
            && isset($this->redirectLink)
            && isset($this->domain);
    }

    public static function createAuthData(string $domain, string $appId, string $authCode, string $secretKey, string $redirectLink = INTEGRATION_REDIRECT_LINK): AmoCrmAuthData
    {
        $amoAuthData = new AmoCrmAuthData();
        $amoAuthData->appId = $appId;
        $amoAuthData->redirectLink = $redirectLink;
        $amoAuthData->authCode = $authCode;
        $amoAuthData->secretKey = $secretKey;
        $amoAuthData->domain = $domain;

        return $amoAuthData;
    }
}

final class OAuth2Data extends AmoCrmAuthData
{
    public $accessToken;
    public $tokenType;
    public $refreshToken;
    public $expired;

    public function validate(): bool
    {
        return
            parent::validate()
            && isset($this->refreshToken)
            && isset($this->accessToken)
            && isset($this->tokenType)
            && isset($this->expired);
    }
}

final class AmoCrmAuth
{
    private $auth;
    private $amoCrmWebClient;

    private static $authFilePath;
    private static $timeFormat = 'Y-m-d H:i:s';

    public function __construct(\amoCrm\AmoCrmWebClient $amoCrmWebClient, string $rootDir, $fileName = AUTH_FILE_NAME)
    {
        $this::$authFilePath = $rootDir . "/" . $fileName;
        $this->amoCrmWebClient = $amoCrmWebClient;
    }

    public function getRootUrl(string $domain = null): string
    {
        return "https://" . ($domain ?? $this->auth->domain) . ".amocrm.ru";
    }

    public function getDomain()
    {
        return $this->auth->domain;
    }

    public function getAuthString(): string
    {
        return $this->auth->tokenType . ' ' . $this->auth->accessToken;
    }

    public function init(AmoCrmAuthData $authData = null)
    {
        if ($authData === null) {
            $this->setAuth($this->loadAuthData());
        } else {
            $response = $this->sendAuthReq(
                [
                    'client_id' => $authData->appId,
                    'client_secret' => $authData->secretKey,
                    'grant_type' => 'authorization_code',
                    'code' => $authData->authCode,
                    'redirect_uri' => $authData->redirectLink,
                ], $authData->domain
            );

            $this->setAuth($this->createOAuth2Data($response, $authData));
        }
    }

    private function setAuth(OAuth2Data $auth)
    {
        $this->auth = $this->refreshToken($auth);
        $this->saveAuthData($this->auth);
    }

    private function sendAuthReq($data, string $domain = null)
    {
        return $this->amoCrmWebClient->getResponse($this->getRootUrl($domain) . '/oauth2/access_token', "POST", $data);
    }

    private function createOAuth2Data($response, AmoCrmAuthData $base): OAuth2Data
    {
        $interval = new \DateInterval('PT' . intval($response['expires_in']) . 'S');

        $o2 = new OAuth2Data();
        $o2->accessToken = $response['access_token'];
        $o2->tokenType = $response['token_type'];
        $o2->refreshToken = $response['refresh_token'];
        $o2->expired = (new \DateTime())->add($interval)->format($this::$timeFormat);
        $o2->domain = $base->domain;
        $o2->appId = $base->appId;
        $o2->authCode = $base->authCode;
        $o2->secretKey = $base->secretKey;
        $o2->redirectLink = $base->redirectLink;

        return $o2;
    }

    private function refreshToken(OAuth2Data $authData): OAuth2Data
    {
        if(!empty($authData->expired) && (new \DateTime() > date_create_from_format($this::$timeFormat, $authData->expired))) {
            $postData =
                [
                    'client_id' => $authData->appId,
                    'client_secret' => $authData->secretKey,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $authData->refreshToken,
                    'redirect_uri' => $authData->redirectLink
                ];
            $response = $this->sendAuthReq($postData, $authData->domain);
            return $this->createOAuth2Data($response, $authData);
        } else {
            return $authData;
        }
    }

    public function checkAuthFile(): bool
    {
        return file_exists($this::$authFilePath);
    }

    private function saveAuthData(OAuth2Data $authData)
    {
        file_put_contents($this::$authFilePath, encrypt(json_encode($authData), hash('sha256', AUTH_FILE_NAME), AUTH_IV));
    }

    private function loadAuthData(): OAuth2Data
    {
        $decode = json_decode(decrypt(file_get_contents($this::$authFilePath), hash('sha256', AUTH_FILE_NAME), AUTH_IV), true);
        $o2 = new OAuth2Data();
        $o2->expired = $decode['expired'];
        $o2->refreshToken = $decode['refreshToken'];
        $o2->tokenType = $decode['tokenType'];
        $o2->accessToken = $decode['accessToken'];
        $o2->domain = $decode['domain'];
        $o2->appId = $decode['appId'];
        $o2->authCode = $decode['authCode'];
        $o2->secretKey = $decode['secretKey'];
        $o2->redirectLink = $decode['redirectLink'];

        if (!$o2->validate()) {
            throw new \Exception("not validate Data");
        }

        return $o2;
    }
}