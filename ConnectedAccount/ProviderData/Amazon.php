<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Amazon
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class Amazon extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'user/profile';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('user_id');
    }

    /**
     * @return mixed|null
     */
    public function getUsername()
    {
        return $this->requestFromEndpoint('name');
    }

    /**
     * @return mixed|null
     */
    public function getEmail()
    {
        return $this->requestFromEndpoint('email');
    }
}
