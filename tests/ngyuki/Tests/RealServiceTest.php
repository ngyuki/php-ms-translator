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
    private static $storage;
    private $adapter;
    private $service;

    public static function setUpBeforeClass()
    {
        self::$storage = new DummyTokenStorage();
    }

    protected function setUp()
    {
        $this->adapter = new CurlHttpAdapter();

        $proxy = getenv('PHPUNIT_HTTP_PROXY');

        if (strlen($proxy))
        {
            $this->adapter->setProxy($proxy);
        }

        $clientId = getenv('PHPUNIT_CLIENT_ID');
        $clientSecret = getenv('PHPUNIT_CLIENT_SECRET');

        $this->service = new Service($this->adapter, self::$storage, $clientId, $clientSecret);
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
