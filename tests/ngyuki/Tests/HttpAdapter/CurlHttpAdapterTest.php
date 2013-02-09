<?php
namespace ngyuki\Tests\HttpAdapter;

use ngyuki\MicrosoftTranslator\HttpAdapter\CurlHttpAdapter;

class CurlHttpAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function setProxy()
    {
        $adapter = new CurlHttpAdapter();

        $adapter->setProxy("http://192.2.3.4:8080");

        $this->assertSame("http://192.2.3.4:8080", $adapter->getProxy());
    }

    /**
     * @test
     */
    public function get_ok()
    {
        $url = getenv('PHPUNIT_URL_GET_OK');

        if (strlen($url) == 0)
        {
            $this->markTestSkipped('require env PHPUNIT_URL_GET_OK');
        }

        $adapter = new CurlHttpAdapter();
        $this->assertNotEmpty($adapter->get($url, array('a' => 1), array("X-Test", "xxx")));
    }

    /**
     * @test
     */
    public function post_ok()
    {
        $url = getenv('PHPUNIT_URL_POST_OK');

        if (strlen($url) == 0)
        {
            $this->markTestSkipped('require env PHPUNIT_URL_POST_OK');
        }

        $adapter = new CurlHttpAdapter();
        $this->assertNotEmpty($adapter->post($url));
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage http response code "404"
     */
    public function get_ng()
    {
        $url = getenv('PHPUNIT_URL_NOTFOUND');

        if (strlen($url) == 0)
        {
            $this->markTestSkipped('require env PHPUNIT_URL_NOTFOUND');
        }

        $adapter = new CurlHttpAdapter();
        $this->assertNotEmpty($adapter->get($url));
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage connect
     */
    public function invalid_proxy()
    {
        $url = getenv('PHPUNIT_URL_GET_OK');
        $proxy = getenv('PHPUNIT_INVALID_PROXY');

        if ((strlen($url) == 0) || (strlen($proxy) == 0))
        {
            $this->markTestSkipped('require env PHPUNIT_URL_GET_OK or PHPUNIT_INVALID_PROXY');
        }

        $adapter = new CurlHttpAdapter();
        $adapter->setProxy($proxy);
        $adapter->setTimeout(1);

        $adapter->get($url);
    }
}
