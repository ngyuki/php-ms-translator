<?php
namespace ngyuki\MicrosoftTranslator;

use ngyuki\MicrosoftTranslator\TokenStorage\TokenStorageInterface;
use ngyuki\MicrosoftTranslator\HttpAdapter\HttpAdapterInterface;

use RuntimeException;

class Service
{
    private $_adapter;
    private $_storage;
    private $_clientId;
    private $_clientSecret;

    public function __construct(HttpAdapterInterface $adapter, TokenStorageInterface $storage, $clientId, $clientSecret)
    {
        $this->_adapter = $adapter;
        $this->_storage = $storage;
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
    }

    private function _stripBom($str)
    {
        $bom = "\xEF\xBB\xBF";

        if (strncmp($str, $bom, strlen($bom)) === 0)
        {
            $str = substr($str, strlen($bom));
        }

        return $str;
    }

    private function _jsonDecode($str)
    {
        $data = json_decode($str);

        if ($data === null)
        {
            $errno = json_last_error();

            if ($errno !== JSON_ERROR_NONE)
            {
                throw new \UnexpectedValueException("json decode error [$errno]");
            }
        }

        return $data;
    }

    private function _get($url, $params, array $headers = array())
    {
        $ret = $this->_adapter->get($url, $params, $headers);
        $ret = $this->_stripBom($ret);
        $ret = $this->_jsonDecode($ret);
        return $ret;
    }

    private function _post($url, $params, array $headers = array())
    {
        $ret = $this->_adapter->post($url, $params, $headers);
        $ret = $this->_stripBom($ret);
        $ret = $this->_jsonDecode($ret);
        return $ret;
    }

    private function _getAccessToken()
    {
        $url = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13";

        $params = array(
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'scope' => 'http://api.microsofttranslator.com',
            'grant_type' => 'client_credentials',
        );

        return $this->_post($url, $params);
    }

    private function _getAuth()
    {
        $access_token = $this->_storage->load();

        if ($access_token === null)
        {
            $now = time();
            $token = $this->_getAccessToken();

            if (!isset($token->access_token) || !isset($token->expires_in))
            {
                throw new RuntimeException("returned invalid access token.");
            }

            $this->_storage->save($token->access_token, $token->expires_in + $now);

            $access_token = $token->access_token;
        }

        return "Bearer $access_token";
    }

    private function _callMethod($name, array $params)
    {
        $name = ucfirst($name);

        $url = "http://api.microsofttranslator.com/V2/Ajax.svc/$name";

        $params['appId'] = $this->_getAuth();

        return $this->_get($url, $params);
    }

    public function detect($text)
    {
        $params = array(
            'appId' => '',
            'text' => $text,
        );

        return $this->_callMethod(__FUNCTION__, $params);
    }

    public function detectArray(array $texts)
    {
        $texts = json_encode(array_values($texts));

        $params = array(
            'appId' => '',
            'texts' => $texts,
        );

        return $this->_callMethod(__FUNCTION__, $params);
    }

    public function getLanguagesForTranslate()
    {
        $params = array(
            'appId' => '',
        );

        return $this->_callMethod(__FUNCTION__, $params);
    }

    public function getTranslations($text, $from, $to, $maxTranslations)
    {
        $params = array(
            'appId' => '',
            'text' => $text,
            'from' => $from,
            'to' => $to,
            'maxTranslations' => $maxTranslations,
        );

        return $this->_callMethod(__FUNCTION__, $params);
    }

    public function translate($text, $from, $to, $contentType = "text/plain", $category = "general")
    {
        $params = array(
            'appId' => '',
            'text' => $text,
            'from' => $from,
            'to' => $to,
            'contentType' => $contentType,
            'category' => $category,
        );

        return $this->_callMethod(__FUNCTION__, $params);
    }
}
