<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\Provider;

use XF\ConnectedAccount\Http\HttpResponseException;
use XF\ConnectedAccount\Provider\AbstractProvider;
use XF\Entity\ConnectedAccountProvider;

/**
 * Class Pinterest
 * @package ThemeHouse\UserImprovements\ConnectedAccount\Provider
 */
class Pinterest extends AbstractProvider
{
    /**
     * @return string
     */
    public function getOAuthServiceName()
    {
        return 'Pinterest';
    }

    /**
     * @return string
     */
    public function getProviderDataClass()
    {
        return 'ThemeHouse\UserImprovements:ProviderData\Pinterest';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'app_id' => '',
            'app_secret' => ''
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
            'key' => $provider->options['app_id'],
            'secret' => $provider->options['app_secret'],
            'scopes' => ['read_public'],
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
