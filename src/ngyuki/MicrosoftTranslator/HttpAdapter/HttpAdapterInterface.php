<?php
namespace ngyuki\MicrosoftTranslator\HttpAdapter;

interface HttpAdapterInterface
{
    public function get($url, array $params = array(), array $headers = array());
    public function post($url, array $params = array(), array $headers = array());
}
