<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Reddit
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class Reddit extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'api/v1/me';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('name');
    }
}
