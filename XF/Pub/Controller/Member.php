<?php

namespace ThemeHouse\UserImprovements\XF\Pub\Controller;


use ThemeHouse\UserImprovements\XF\Entity\UserTrophy;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Member
 * @package ThemeHouse\UserImprovements\XF\Pub\Controller
 */
class Member extends XFCP_Member
{
    /**
     * @param ParameterBag $params
     * @return View
     */
    public function actionIndex(ParameterBag $params)
    {
        $reply = parent::actionIndex($params);

        if ($reply instanceof View && $active = $reply->getParam('active')) {
            $reply->setParam('trophies', $active->getTHUITrophies());
        }

        return $reply;
    }

    /**
     * @param ParameterBag $params
     * @return View
     * @throws \XF\Db\Exception
     */
    public function actionView(ParameterBag $params)
    {
        $return = parent::actionView($params);

        if ($return instanceof View) {
            $changeRecords = $this->em()->getFinder('ThemeHouse\UserImprovements:UsernameChange')->
            order('change_date', 'DESC')->where('user_id', $params['user_id'])->fetch(10);

            $return->setParam('username_changes', $changeRecords);

            if ($this->app->options()->klUiProfileViews) {
                /** @var \ThemeHouse\UserImprovements\Repository\User $repo */
                $repo = $this->repository('ThemeHouse\UserImprovements:User');
                $repo->logProfileView($return->getParam('user'));
            }
        }

        return $return;
    }

    /**
     * @return \XF\Mvc\Reply\Redirect|View
     */
    public function actionTHUITrophiesShowcaseSelect()
    {
        /** @var \ThemeHouse\UserImprovements\XF\Entity\User $visitor */
        $visitor = \XF::visitor();
        $options = \XF::app()->options();
        $trophies = $this->finder('XF:UserTrophy')
            ->where('user_id', $visitor->user_id)
            ->with('Trophy', true)
            ->order('Trophy.trophy_points')
            ->fetch();

        if ($options->klUIProfileTrophyShowcase != 3) {
            return $this->noPermission();
        }

        if (!$visitor->getTHUITrophyShowcaseSize()) {
            return $this->noPermission();
        }

        if ($this->isPost()) {
            $trophyIds = $this->filter('trophy_ids', 'array-int');
            $trophyIds = array_slice($trophyIds, 0, 5);

            foreach ($trophies as $trophy) {
                /** @var UserTrophy $trophy */
                $trophy->th_showcased = in_array($trophy->trophy_id, $trophyIds);
                $trophy->saveIfChanged();
            }

            return $this->redirect($this->buildLink('members', $visitor));
        } else {
            $group = $trophies->groupBy('th_showcased');

            if (isset($group[1])) {
                $selected = count($group[1]);
            } else {
                $selected = 0;
            }

            $viewParams = [
                'amountSelected' => $selected,
                'amount' => $visitor->getTHUITrophyShowcaseSize(),
                'unlimited' => min($visitor->hasPermission('klUI', "klUITSS_profile"),
                        $visitor->hasPermission('klUI', "klUITSS_postbit"),
                        $visitor->hasPermission('klUI', "klUITSS_tooltip")) == -1,
                'trophies' => $trophies
            ];

            return $this->view('ThemeHouse\UserImprovements:TrophyShowcase',
                'thuserimprovements_trophy_showcase_select', $viewParams);
        }
    }
}
