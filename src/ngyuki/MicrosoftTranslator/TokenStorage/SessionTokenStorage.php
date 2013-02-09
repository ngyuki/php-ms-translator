<?php
namespace ngyuki\MicrosoftTranslator\TokenStorage;

class SessionTokenStorage implements TokenStorageInterface
{
    public function save($token, $expire)
    {
        if (isset($_SESSION) === false)
        {
            session_start();
        }

        $_SESSION[__CLASS__] = array($token, $expire);
    }

    public function load()
    {
        if (isset($_SESSION) === false)
        {
            session_start();
        }

        if (isset($_SESSION[__CLASS__]))
        {
            list ($token, $expire) = $_SESSION[__CLASS__];

            if (time() < $expire)
            {
                return $token;
            }
        }

        return null;
    }
}
