<?php
namespace ngyuki\Tests;

use ngyuki\MicrosoftTranslator\Service;
use ngyuki\MicrosoftTranslator\HttpAdapter\HttpAdapterInterface;
use ngyuki\MicrosoftTranslator\TokenStorage\DummyTokenStorage;

use Phake;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $storage;
    private $service;
    private $client_id;
    private $client_secret;

    protected function setUp()
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT);

        $this->adapter = Phake::mock('ngyuki\MicrosoftTranslator\HttpAdapter\HttpAdapterInterface');
        $this->storage = new DummyTokenStorage();

        $this->client_id = "hogehoge";
        $this->client_secret = "piyopiyo";

        $this->service = new Service($this->adapter, $this->storage, $this->client_id, $this->client_secret);
    }

    /**
     * @test
     * @dataProvider data
     */
    public function test($method, $params)
    {
        $this->storage->save("abcxyz", time() + 60);

        $retval = array(1, 2, 3, 4, 5);

        Phake::when($this->adapter)->get(Phake::anyParameters())->thenReturn(json_encode($retval));

        $actual = call_user_func_array(array($this->service, $method), array_values($params));

        $this->assertSame($retval, $actual);

        $params = array('appId' => 'Bearer abcxyz') + $params;

        Phake::verify($this->adapter)->get(
            'http://api.microsofttranslator.com/V2/Ajax.svc/' . ucfirst($method),
            $params,
            array()
        );
    }

    public function data()
    {
        return array(
            array('detect',
                array('text' => "abc123")),

            array('getLanguagesForTranslate',
                array()),

            array('getTranslations',
                array('text' => "abc123", 'from' => "en", 'to' => "ja", 'maxTranslations' => 13)),

            array('translate',
                array('text' => "abc123", 'from' => "en", 'to' => "ja", 'contentType' => 'text/plain', 'category' => "hoge")),
        );
    }

    /**
     * @test
     */
    public function detectArray()
    {
        $this->storage->save("abcxyz", time() + 60);

        $retval = array(1, 2, 3, 4, 5);

        Phake::when($this->adapter)->get(Phake::anyParameters())
            ->thenReturn("\xEF\xBB\xBF" . json_encode($retval));

        $actual = $this->service->detectArray(array("abc", "789"));

        $this->assertSame($retval, $actual);

        $params = array('appId' => 'Bearer abcxyz', 'texts' => '["abc","789"]');

        Phake::verify($this->adapter)->get(
            'http://api.microsofttranslator.com/V2/Ajax.svc/DetectArray',
            $params,
            array()
        );
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage json decode error
     */
    public function jsonDecodeError()
    {
        $this->storage->save("abcxyz", 0);

        Phake::when($this->adapter)->get(Phake::anyParameters())->thenReturn("xxx");

        $this->service->detect("abc");
    }

    /**
     * @test
     */
    public function jsonDecodeNull()
    {
        $this->storage->save("abcxyz", 0);

        Phake::when($this->adapter)->get(Phake::anyParameters())->thenReturn("null");

        $this->assertNull($this->service->detect("abc"));
    }

    /**
     * @test
     */
    public function auth()
    {
        $this->storage->save(null, 0);

        $token = array('access_token' => 'hoehoe', 'expires_in' => 42);

        Phake::when($this->adapter)->post(
            "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13",
            array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope' => 'http://api.microsofttranslator.com',
                'grant_type' => 'client_credentials',
            ),
            array()
        )->thenReturn(json_encode($token));

        $this->service->detect("abc");

        Phake::inOrder(
            Phake::verify($this->adapter, Phake::times(1))->post(Phake::anyParameters()),
            Phake::verify($this->adapter, Phake::times(1))->get(Phake::anyParameters())
        );

        $this->assertSame($token['access_token'], $this->storage->load());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage returned invalid access token.
     */
    public function auth_ng()
    {
        $this->storage->save(null, 0);

        Phake::when($this->adapter)->post(Phake::anyParameters())->thenReturn(json_encode(array()));

        $this->service->detect("abc");
    }
}
