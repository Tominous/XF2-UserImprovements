<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class DeviantArt
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class DeviantArt extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'user/whoami';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('userid');
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('username');
    }

    /**
     * @return mixed|null
     */
    public function getAvatarUrl()
    {
        return $this->requestFromEndpoint('usericon');
    }
}
