<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\Provider;

use XF\Entity\ConnectedAccountProvider;
use XF\ConnectedAccount\Http\HttpResponseException;
use XF\ConnectedAccount\Provider\AbstractProvider;

/**
 * Class Twitch
 * @package ThemeHouse\UserImprovements\ConnectedAccount\Provider
 */
class Twitch extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'ThemeHouse\UserImprovements:Service\Twitch';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\UserImprovements:ProviderData\Twitch';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'client_id' => '',
            'client_secret' => ''
        ];
    }

    /**
     * @param ConnectedAccountProvider $provider
     * @param null $redirectUri
     * @return array
     */
    public function getOAuthConfig(ConnectedAccountProvider $provider, $redirectUri = null)
    {
        return [
            'key' => $provider->options['client_id'],
            'secret' => $provider->options['client_secret'],
            'scopes' => [],
            'redirect' => $redirectUri ?: $this->getRedirectUri($provider)
        ];
    }

    /**
     * @param HttpResponseException $e
     * @param null $error
     */
    public function parseProviderError(HttpResponseException $e, &$error = null)
    {
        $response = json_decode($e->getResponseContent(), true);
        if (is_array($response) && isset($response['error']['message'])) {
            $e->setMessage($response['error']['message']);
        }
        parent::parseProviderError($e, $error);
    }
}
