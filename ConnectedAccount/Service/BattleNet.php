<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\Service;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class BattleNet
 * @package ThemeHouse\UserImprovements\ConnectedAccount\Service
 */
class BattleNet extends AbstractService
{

    /** -----------------------------------------------------------------------
     * Defined scopes.
     *
     * @link https://dev.battle.net/docs
     */
    const SCOPE_WOW_PROFILE = "wow.profile";
    /**
     *
     */
    const SCOPE_SC2_PROFILE = "sc2.profile";

    /** -----------------------------------------------------------------------
     * Defined API URIs.
     *
     * @link https://dev.battle.net/docs
     */
    const API_URI_US = 'https://us.api.battle.net/';
    /**
     *
     */
    const API_URI_EU = 'https://eu.api.battle.net/';
    /**
     *
     */
    const API_URI_KR = 'https://kr.api.battle.net/';
    /**
     *
     */
    const API_URI_TW = 'https://tw.api.battle.net/';
    /**
     *
     */
    const API_URI_CN = 'https://api.battlenet.com.cn/';
    /**
     *
     */
    const API_URI_SEA = 'https://sea.api.battle.net/';

    /**
     * BattleNet constructor.
     * @param CredentialsInterface $credentials
     * @param ClientInterface $httpClient
     * @param TokenStorageInterface $storage
     * @param array $scopes
     * @param UriInterface|null $baseApiUri
     * @throws \OAuth\OAuth2\Service\Exception\InvalidScopeException
     */
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct(
            $credentials,
            $httpClient,
            $storage,
            $scopes,
            $baseApiUri
        );

        if ($baseApiUri === null) {
            $this->baseApiUri = new Uri(self::API_URI_US);
        }
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->GetOAuthBaseUri() . 'authorize');
    }

    /**
     * @return null|string
     */
    private function GetOAuthBaseUri()
    {
        switch ($this->baseApiUri) {
            case self::API_URI_US:
                return 'https://us.battle.net/oauth/';
            case self::API_URI_EU:
                return 'https://eu.battle.net/oauth/';
            case self::API_URI_KR:
                return 'https://kr.battle.net/oauth/';
            case self::API_URI_TW:
                return 'https://tw.battle.net/oauth/';
            case self::API_URI_CN:
                return 'https://www.battlenet.com.cn/oauth/';
            case self::API_URI_SEA:
                return 'https://sea.battle.net/oauth/';
        }

        return null;
    }

    /**
     * @return Uri|UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri($this->GetOAuthBaseUri() . 'token');
    }

    /**
     * @return int
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * @param string $responseBody
     * @return \OAuth\Common\Token\TokenInterface|StdOAuth2Token
     * @throws TokenResponseException
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        $data = json_decode($responseBody, true);
        if ($data === null || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            $err = $data['error'];
            throw new TokenResponseException(
                "Error in retrieving token: \"$err\""
            );
        }

        $token = new StdOAuth2Token(
            $data['access_token'],
            null,
            $data['expires_in']
        );

        unset($data['access_token']);
        unset($data['expires_in']);

        $token->setExtraParams($data);

        return $token;
    }
}
