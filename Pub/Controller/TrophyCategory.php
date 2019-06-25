<?php

namespace ThemeHouse\UserImprovements\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class TrophyCategory
 * @package ThemeHouse\UserImprovements\Pub\Controller
 */
class TrophyCategory extends AbstractController
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\Reroute
     */
    public function actionIndex(ParameterBag $params)
    {
        if ($params['trophy_category_id']) {
            return $this->rerouteController(__CLASS__, 'view', $params);
        }

        /** @var \XF\Entity\HelpPage $page */
        $page = $this->finder('XF:HelpPage')
            ->where('page_name', 'trophies')
            ->fetchOne();
        if (!$page) {
            return $this->error(\XF::phrase('requested_page_not_found'), 404);
        }

        return $this->redirect($this->buildLink('help/trophies'));
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionView(ParameterBag $params)
    {
        $trophyCategory = $this->assertViewableTrophyCategory($params['trophy_category_id']);

        $this->assertCanonicalUrl($this->buildLink('trophy-categories', $trophyCategory));

        /** @var \ThemeHouse\UserImprovements\XF\Repository\Trophy $trophyRepo */
        $trophyRepo = $this->em()->getRepository('XF:Trophy');

        $trophies = $trophyRepo->findTrophiesForList()
            ->where('th_trophy_category_id', $trophyCategory->trophy_category_id)
            ->fetch();

        $trophyProgressCriteria = $trophyRepo->getTHUITrophyProgressCriteria($trophies);

        $userId = \XF::visitor()->user_id;
        $userTrophies = $trophyRepo->findUserTrophies($userId)->fetch();

        $trophiesPrepared = $trophyRepo->prepareTHUITrophiesForHelpPage($trophies, $userTrophies);

        $viewParams = [
            'trophyCategory' => $trophyCategory,
            'trophies' => $trophies,
            'trophiesPrepared' => $trophiesPrepared,
            'trophyProgressCriteria' => $trophyProgressCriteria,
        ];
        return $this->view('ThemeHouse\Topics:Trophy\View', 'thuserimprovements_trophy_category_view', $viewParams);
    }

    /**
     * @param $trophyCategoryId
     * @param array $extraWith
     * @return \ThemeHouse\UserImprovements\Entity\TrophyCategory
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertViewableTrophyCategory($trophyCategoryId, array $extraWith = [])
    {
        array_unique($extraWith);

        /** @var \ThemeHouse\UserImprovements\Entity\TrophyCategory $trophyCategory */
        $trophyCategory = $this->em()->find(
            'ThemeHouse\UserImprovements:TrophyCategory',
            $trophyCategoryId,
            $extraWith
        );
        if (!$trophyCategory) {
            throw $this->exception(
                $this->notFound(\XF::phrase('thuserimprovements_requested_trophy_category_not_found'))
            );
        }

        return $trophyCategory;
    }
}
