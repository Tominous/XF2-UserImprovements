<?php

namespace ThemeHouse\UserImprovements\XF\Pub\Controller;

use XF\Entity\User;
use XF\Mvc\FormAction;
use XF\Mvc\Reply\View;

/**
 * Class Account
 * @package ThemeHouse\UserImprovements\XF\Pub\Controller
 */
class Account extends XFCP_Account
{
    /**
     * @return \XF\Mvc\Reply\Redirect|View
     */
    public function actionAccountDetails()
    {
        $return = parent::actionAccountDetails();

        if ($return instanceof View) {
            $canChangeName = $this->canTHUIChangeUsername();

            $return->setParam('canTHUIChangeName', $canChangeName[0]);
            $return->setParam('nextTHUIChangeOn', $canChangeName[1]);

            $return->setParam('thuiUsernameColorValues', array_merge(range(1, 27), [0]));
        }

        return $return;
    }

    /**
     * @return array
     */
    protected function canTHUIChangeUsername()
    {
        $visitor = \XF::visitor();
        $nextChangeOn = 0;

        if ($visitor->hasPermission('klUI', 'klUIChangeUsername')) {
            if ($visitor->hasPermission('klUI', 'klUINoLimit')) {
                $canChangeName = [true, 0];
            } else {
                $finder = $this->em()->getFinder('ThemeHouse\UserImprovements:UsernameChange');
                /** @var \ThemeHouse\UserImprovements\Entity\UsernameChange $record */
                $record = $finder->order('change_date', 'DESC')->where('user_id', $visitor->user_id)->fetchOne();

                $groups = $visitor->secondary_group_ids;
                $groups[] = $visitor->user_group_id;

                $finder = $this->finder('XF:PermissionEntry');

                $finder->where('permission_group_id', 'klUI')
                    ->where('permission_id', 'klUIChangeUsernameTime');

                $finder->whereOr(
                    ['user_group_id', $groups],
                    ['user_id', $visitor->user_id]
                );

                $perms = $finder->fetch();

                $minusOne = false;
                $days = [];
                foreach ($perms as $perm) {
                    $value = $perm->permission_value_int;

                    if ($value !== -1) {
                        $days[] = $perm->permission_value_int;
                    } else {
                        $minusOne = true;
                    }
                }

                if (empty($days)) {
                    $days = [0];
                }

                $minDays = min($days);


                if ($minDays === 0 && $minusOne) {
                    $canChangeName = false;
                } else {
                    $canChangeName = !$record || ($record->change_date < \XF::$time - $minDays * 86400);

                    if (!$record) {
                        $nextChangeOn = 0;
                    } else {
                        $nextChangeOn = $record->change_date + $minDays * 86400;
                    }
                }
            }

            return [$canChangeName, $nextChangeOn];
        }

        return [false, 0];
    }

    /**
     * @return \XF\Mvc\Reply\Redirect|View
     * @throws \Exception
     * @throws \XF\PrintableException
     */
    public function actionTHUIDeactivate()
    {
        if ($this->isPost()) {
            $visitor = \XF::visitor();

            /** @var \ThemeHouse\UserImprovements\Entity\UserDisabled $disableRecord */
            $disableRecord = $this->em()->create('ThemeHouse\UserImprovements:UserDisabled');

            if ($visitor->hasPermission('klUI', 'klUISelfReactivate')) {
                if ($visitor->hasPermission('klUI', 'klUISelfReactivationTime') === -1) {
                    $disableRecord->latest_restore_date = 4102444800;
                } else {
                    $disableRecord->latest_restore_date = min(
                        $visitor->hasPermission('klUI', 'klUISelfReactivationTime') * 86400 + \XF::$time,
                        \XF::$time + 157680000
                    );
                }
            }

            $disableRecord->save();

            $visitor->user_state = 'disabled';
            $visitor->save();

            /** @var \XF\ControllerPlugin\Login $loginPlugin */
            $loginPlugin = $this->plugin('XF:Login');
            $loginPlugin->logoutVisitor();

            return $this->redirect($this->buildLink('index'));
        } else {
            $view = $this->view('ThemeHouse\UserImprovements:SelfDeactivate', 'thuserimprovements_self_deactivate');
            return $this->addAccountWrapperParams($view, 'self_deactivate');
        }
    }

    /**
     * @param User $visitor
     * @return FormAction
     * @throws \XF\Db\Exception
     */
    protected function accountDetailsSaveProcess(User $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        $visitor = \XF::visitor();

        if ($visitor->hasPermission('klUI', 'klUIChangeUsername')) {
            $canChangeName = $this->canTHUIChangeUsername()[0];

            if ($form instanceof FormAction) {
                if ($canChangeName) {
                    $input = $this->filter(['user' => ['username' => 'str']]);

                    if ($input['user']['username'] !== $visitor->username) {
                        $username = \XF::visitor()->username;
                        $form->complete(function () use ($username) {
                            /** @var \ThemeHouse\UserImprovements\Entity\UsernameChange $changeRecord */
                            $changeRecord = $this->em()->create('ThemeHouse\UserImprovements:UsernameChange');
                            $changeRecord->old_username = $username;
                            $changeRecord->save();

                            /** @var \XF\Repository\IP $ipRepo */
                            $ipRepo = $this->repository('XF:Ip');
                            $userId = \XF::visitor()->user_id;
                            $ipRepo->logIp($userId, $this->request->getIp(), 'user', $userId, 'account_details_edit');
                        });
                    }
                } else {
                    $input = [];
                }
            }
        }

        if ($visitor->hasPermission('klUI', 'klUIChoseUsernameColor')) {
            $color = $this->filter(['user' => ['th_name_color_id' => 'int']]);
            $input['user']['th_name_color_id'] = $color['user']['th_name_color_id'];
        } else {
            $input['user']['th_name_color_id'] = 0;
        }

        $form->basicEntitySave($visitor, $input['user']);

        if ($this->filter('reset_profile_views', 'bool')) {
            /** @var \ThemeHouse\UserImprovements\Repository\User $repo */
            $repo = $this->repository('ThemeHouse\UserImprovements:User');
            $repo->resetProfileViews($visitor);
        }

        return $form;
    }

    /**
     * @param User $visitor
     * @return FormAction
     */
    protected function savePrivacyProcess(User $visitor)
    {
        $form = parent::savePrivacyProcess($visitor);

        $visitor = \XF::visitor();

        if ($form instanceof FormAction) {
            $input = $this->filter([
                'privacy' => [
                    'th_view_profile_stats' => 'str',
                    'th_view_username_changes' => 'str',
                    'th_view_profile_views' => 'str'
                ]
            ]);

            $userPrivacy = $visitor->getRelationOrDefault('Privacy');
            $form->setupEntityInput($userPrivacy, $input['privacy']);

            $form->complete(function () use ($visitor) {
                /** @var \XF\Repository\IP $ipRepo */
                $ipRepo = $this->repository('XF:Ip');
                $ipRepo->logIp($visitor->user_id, $this->request->getIp(), 'user', $visitor->user_id, 'privacy_edit');
            });
        }

        return $form;
    }
}
