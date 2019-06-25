<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Pinterest
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class Pinterest extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'v1/me';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('data')['id'];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        $data = $this->requestFromEndpoint('data');

        return trim($data['first_name'] . ' ' . $data['last_name']);
    }
}
