<?php

namespace ThemeHouse\UserImprovements\ConnectedAccount\ProviderData;

use XF\ConnectedAccount\ProviderData\AbstractProviderData;

/**
 * Class BattleNet
 * @package ThemeHouse\UserImprovements\ConnectedAccount\ProviderData
 */
class BattleNet extends AbstractProviderData
{
    /**
     * @return string
     */
    public function getDefaultEndpoint()
    {
        return 'account/user';
    }

    /**
     * @return mixed|null
     */
    public function getProviderKey()
    {
        return $this->requestFromEndpoint('id');
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        $name = $this->requestFromEndpoint('battletag');
        $tag = explode('#', $name);
        return $tag[0];
    }
}
