<?php

namespace ThemeHouse\UserImprovements\Pub\Controller;

use XF\Finder\User;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class Trophy
 * @package ThemeHouse\UserImprovements\Pub\Controller
 */
class Trophy extends AbstractController
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\Reroute
     */
    public function actionIndex(ParameterBag $params)
    {
        if ($params['trophy_id']) {
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
        $trophy = $this->assertViewableTrophy($params['trophy_id']);

        $this->assertCanonicalUrl($this->buildLink('trophies', $trophy));


        /** @var \ThemeHouse\UserImprovements\XF\Repository\Trophy $trophyRepo */
        $trophyRepo = $this->em()->getRepository('XF:Trophy');

        $trophies = $trophyRepo->findTrophiesForList()->fetch();

        $predecessors = $trophyRepo->getTHUITrophyPredecessors($trophy, $trophies);
        $followers = $trophyRepo->getTHUITrophyFollowers($trophy, $trophies);

        $trophies = array_merge($predecessors, [$trophy->trophy_id => $trophy], $followers);
        $trophyProgressCriteria = $trophyRepo->getTHUITrophyProgressCriteria($trophies);

        $page = $this->filterPage();
        $perPage = $this->options()->membersPerPage;

        $finder = \XF::app()->em()->getFinder('XF:UserTrophy')
            ->with('User')
            ->with('User.Option')
            ->with('User.Profile')
            ->where('trophy_id', $trophy->trophy_id)
            ->where('User.user_state', '=', 'valid')
            ->where('User.is_banned', '=', 0)
            ->order('award_date', 'desc')
            ->limitByPage($page, $perPage);

        $total = $finder->total();
        $this->assertValidPage($page, $perPage, $total, 'trophies', $trophy);

        $userTrophies = $finder->fetch();

        $viewParams = [
            'trophy' => $trophy,
            'trophies' => $trophies,
            'trophyProgressCriteria' => $trophyProgressCriteria,
            'userTrophies' => $userTrophies,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ];
        return $this->view('ThemeHouse\Topics:Trophy\View', 'thuserimprovements_trophy_view', $viewParams);
    }

    /**
     * @param $trophyId
     * @param array $extraWith
     * @return \XF\Entity\Trophy
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertViewableTrophy($trophyId, array $extraWith = [])
    {
        $extraWith[] = 'THUITrophyCategory';

        array_unique($extraWith);

        /** @var \ThemeHouse\UserImprovements\XF\Entity\Trophy $trophy */
        $trophy = $this->em()->find('XF:Trophy', $trophyId, $extraWith);
        if (!$trophy) {
            throw $this->exception($this->notFound(\XF::phrase('thuserimprovements_requested_trophy_not_found')));
        }

        $canView = !$trophy->th_hidden;
        if (!$canView) {
            throw $this->exception($this->noPermission());
        }

        return $trophy;
    }

    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionStatsMessageCount()
    {
        $limit = $this->options()->membersPerPage;

        /** @var User $userFinder */
        $userFinder = $this->finder('XF:User');
        $userFinder
            ->with('Option', true)
            ->with('Profile', true)
            ->isValidUser()
            ->order('message_count', 'desc')
            ->limit($limit)
            ->fetch();

        $viewParams = [
            'users' => $userFinder->fetch(),
            'extraData' => 'message_count',
            'title' => \XF::phrase('thuserimprovements_most_messages'),
        ];

        return $this->view(
            'ThemeHouse\Topics:Trophy\Stats\MessageCount',
            'thuserimprovements_trophy_stats',
            $viewParams
        );
    }

    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionStatsReactionScore()
    {
        $limit = $this->options()->membersPerPage;

        /** @var User $userFinder */
        $userFinder = $this->finder('XF:User');
        $userFinder
            ->with('Option', true)
            ->with('Profile', true)
            ->isValidUser()
            ->order('reaction_score', 'desc')
            ->limit($limit)
            ->fetch();

        $viewParams = [
            'users' => $userFinder->fetch(),
            'extraData' => 'reaction_score',
            'title' => \XF::phrase('thuserimprovements_highest_reaction_score'),
        ];

        return $this->view(
            'ThemeHouse\Topics:Trophy\Stats\ReactionScore',
            'thuserimprovements_trophy_stats',
            $viewParams
        );
    }
}
