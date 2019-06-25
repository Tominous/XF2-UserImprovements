<?php

namespace ThemeHouse\UserImprovements\Cron;

/**
 * Class Views
 * @package ThemeHouse\UserImprovements\Cron
 */
class Views
{
    /**
     * @throws \XF\Db\Exception
     */
    public static function runViewUpdate()
    {
        $app = \XF::app();

        /** @var \ThemeHouse\userImprovements\Repository\User $userRepo */
        $userRepo = $app->repository('ThemeHouse\UserImprovements:User');
        $userRepo->batchUpdateProfileViews();
    }
}
