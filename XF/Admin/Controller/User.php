<?php

namespace ThemeHouse\UserImprovements\XF\Admin\Controller;

/**
 * Class User
 * @package ThemeHouse\UserImprovements\XF\Admin\Controller
 */
class User extends XFCP_User
{
    /**
     * @param \XF\Entity\User $user
     * @return \XF\Mvc\FormAction
     * @throws \Exception
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        $input = $this->filter(['user' => ['user_state' => 'str']]);

        if ($user->user_state === 'disabled' && $input['user']['user_state'] !== 'disabled') {
            $finder = $this->app()->em()->getFinder('ThemeHouse\UserImprovements:UserDisabled');
            $record = $finder->where('user_id', $user->user_id)->fetchOne();
            if ($record) {
                $record->delete();
            }
        }

        if ($user->user_state !== 'disabled'
            && $input['user']['user_state'] === 'disabled') {
            /** @var \ThemeHouse\UserImprovements\Entity\UserDisabled $disableRecord */
            $disableRecord = $this->em()->create('ThemeHouse\UserImprovements:UserDisabled');
            $disableRecord->user_id = $user->user_id;
            $disableRecord->save();
        }

        return $form;
    }
}
