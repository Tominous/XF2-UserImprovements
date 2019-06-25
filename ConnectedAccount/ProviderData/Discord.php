<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Discord
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class Discord extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'users/@me';
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('username');
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        $uid = $this->getProviderKey();
        $avatarHash = $this->requestFromEndpoint('avatar');

        return "https://cdn.discordapp.com/avatars/{$uid}/{$avatarHash}.jpg";
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }
}
