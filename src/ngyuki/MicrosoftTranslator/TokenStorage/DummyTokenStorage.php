<?php
namespace ngyuki\MicrosoftTranslator\TokenStorage;

class DummyTokenStorage implements TokenStorageInterface
{
    private $_token = null;

    public function save($token, $expire)
    {
        $this->_token = $token;
    }

    public function load()
    {
        return $this->_token;
    }
}
