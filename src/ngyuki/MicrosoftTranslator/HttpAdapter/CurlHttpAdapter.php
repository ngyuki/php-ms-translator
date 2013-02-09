<?php
namespace ngyuki\MicrosoftTranslator\HttpAdapter;

class CurlHttpAdapter implements HttpAdapterInterface
{
    private $_proxy = null;

    public function getProxy($proxy)
    {
        return $this->_proxy;
    }

    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
    }

    public function get($url, array $params = array(), array $headers = array())
    {
        return self::_request(false, $url, $params, $headers);
    }

    public function post($url, array $params = array(), array $headers = array())
    {
        return self::_request(true, $url, $params, $headers);
    }

    private function _request($isPost, $url, array $params = array(), array $headers = array())
    {
        $qs = http_build_query($params);

        if ($isPost == false)
        {
            $url .= '?' . $qs;
        }

        $ch = curl_init($url);

        try
        {
            if ($this->_proxy !== null)
            {
                curl_setopt($ch, CURLOPT_PROXY, $this->_proxy);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__DIR__) . '/ssl/' . 'GTECyberTrustGlobalRoot.crt');

            if ($isPost != false)
            {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $qs);
            }

            if (count($headers))
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            $ret = curl_exec($ch);

            if ($ret === false)
            {
                throw new \UnexpectedValueException(curl_error($ch));
            }

            $errno = curl_errno($ch);

            if ($errno !== 0)
            {
                throw new \UnexpectedValueException(curl_error($ch));
            }

            curl_close($ch);
        }
        catch (\Exception $ex)
        {
            curl_close($ch);
            throw $ex;
        }

        return $ret;
    }
}
