<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class Twitch
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class Twitch extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'users';
    }

    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        $data = $this->requestUserData();
        return $data['id'];
    }

    /**
     * @return mixed
     */
    protected function requestUserData()
    {
        $user = $this->requestFromEndpoint('data');
        return $user[0];
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $data = $this->requestUserData();
        return $data['display_name'];
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        $data = $this->requestUserData();
        return $data['email'];
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        $data = $this->requestUserData();
        return $data['profile_image_url'];
    }
}
