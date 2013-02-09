<?php
namespace ngyuki\MicrosoftTranslator\TokenStorage;

interface TokenStorageInterface
{
    /**
     * ストレージにトークンを保存する
     *
     * @param string $token  トークン文字列
     * @param int    $expire 有効期限
     */
    public function save($token, $expire);

    /**
     * ストレージからトークンを取得する
     *
     * @return string|null
     */
    public function load();
}
