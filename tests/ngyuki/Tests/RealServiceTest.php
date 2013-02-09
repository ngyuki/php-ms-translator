<?php
namespace ngyuki\Tests;

use ngyuki\MicrosoftTranslator\Service;
use ngyuki\MicrosoftTranslator\HttpAdapter\CurlHttpAdapter;
use ngyuki\MicrosoftTranslator\TokenStorage\DummyTokenStorage;

/**
 *
 * @group realservice
 *
 */
class RealServiceTest extends \PHPUnit_Framework_TestCase
{
    private $service;

    protected function setUp()
    {
        $clientId = getenv('PHPUNIT_CLIENT_ID');
        $clientSecret = getenv('PHPUNIT_CLIENT_SECRET');

        if ((strlen($clientId) == 0) || (strlen($clientSecret) == 0))
        {
            $this->markTestSkipped("unset env PHPUNIT_CLIENT_ID or PHPUNIT_CLIENT_SECRET");
        }

        $adapter = new CurlHttpAdapter();

        $proxy = getenv('PHPUNIT_HTTP_PROXY');

        if (strlen($proxy))
        {
            $adapter->setProxy($proxy);
        }

        $storage = new DummyTokenStorage();

        $this->service = new Service($adapter, $storage, $clientId, $clientSecret);
    }

    /**
     * @test
     */
    public function detectArray()
    {
        $texts = array(
            "les erreurs sont parfois amusants.",
            "you can try to enter a longer phrase.",
            "Questo un testo italiano.",
        );

        $langs = $this->service->detectArray($texts);
        $this->assertEquals(array("fr", "en", "it"), $langs);
    }

    /**
     * @test
     */
    public function getLanguagesForTranslate()
    {
        $langs = $this->service->getLanguagesForTranslate();

        $this->assertInternalType("array", $langs);
        $this->assertContains('ja', $langs);
        $this->assertContains('en', $langs);
    }

    /**
     * @test
     */
    public function getTranslations()
    {
        $obj = $this->service->getTranslations("hello", "en", "ja", 10);
        $this->assertInstanceOf("stdClass", $obj);
    }

    /**
     * @test
     */
    public function translate()
    {
        $text = $this->service->translate("hello", "en", "ja");
        $this->assertEquals("こんにちは", $text);
    }
}
