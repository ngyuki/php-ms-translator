<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use ngyuki\MicrosoftTranslator\Service;
use ngyuki\MicrosoftTranslator\HttpAdapter\CurlHttpAdapter;
use ngyuki\MicrosoftTranslator\TokenStorage\SessionTokenStorage;

class WebFront
{
    private $_cfg = array();

    /**
     * @var Service
     */
    private $_service;

    public function __construct()
    {
        include __DIR__ . '/config.php';
        $this->_cfg = get_defined_vars();

        $this->_initService();
    }

    private function _initService()
    {
        $cfg = $this->_cfg;

        $adapter = new CurlHttpAdapter();
        $storage = new SessionTokenStorage();

        if (isset($cfg['http_proxy']) && strlen($cfg['http_proxy']))
        {
            $adapter->setProxy($cfg['http_proxy']);
        }

        $this->_service = new Service($adapter, $storage, $cfg['client_id'], $cfg['client_secret']);
    }

    public function main()
    {
        $text = "";
        $result = "";

        if (isset($_GET["text"]))
        {
            $text = $_GET["text"];

            if (isset($_GET["ja"]))
            {
                $result = $this->_service->translate($text, "ja", "en");
            }
            else
            {
                $result = $this->_service->translate($text, "en", "ja");
            }
        }

        $this->render($text, $result);
    }

    private function render($text, $result)
    {
        include 'index.phtml';
    }
}

$obj = new WebFront();
$obj->main();
