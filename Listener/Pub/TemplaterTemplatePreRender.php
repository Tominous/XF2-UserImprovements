<?php

namespace ThemeHouse\UserImprovements\Listener\Pub;

use XF\Template\Templater;

/**
 * Class TemplaterTemplatePreRender
 * @package ThemeHouse\UserImprovements\Listener\Pub
 */
class TemplaterTemplatePreRender
{
    /**
     * @param Templater $templater
     * @param $type
     * @param $template
     * @param array $params
     */
    public static function memberTrophies(Templater $templater, &$type, &$template, array &$params)
    {
        $trophies = $params['trophies'];
        $categorizedTrophies = ['uncategorized' => []];

        foreach ($trophies as $value) {
            $follId = $value->Trophy->th_follower;
            $user = $params['user']->user_id;
            if (!$follId || !isset($trophies["{$user}-{$follId}"])) {
                if ($value->Trophy->th_trophy_category_id === '') {
                    $categorizedTrophies['uncategorized'][] = $value;
                } else {
                    $categorizedTrophies[$value->Trophy->th_trophy_category_id][] = $value;
                }
            }
        }

        $params['trophies'] = $categorizedTrophies;
        $params['trophyCategories'] = \XF::app()->em()->getFinder('ThemeHouse\UserImprovements:TrophyCategory')
            ->order('display_order')
            ->fetch();
    }

    /**
     * @param Templater $templater
     * @param $type
     * @param $template
     * @param array $params
     */
    public static function memberAbout(Templater $templater, &$type, &$template, array &$params)
    {
        $trophies = isset($params['trophies']) ? $params['trophies'] : [];
        $categorizedTrophies = ['uncategorized' => []];

        if ($trophies) {
            foreach ($trophies as $value) {
                $follId = $value->Trophy->th_follower;
                $user = $params['user']->user_id;
                if (!$follId || !isset($trophies["{$user}-{$follId}"])) {
                    if ($value->Trophy->th_trophy_category_id === '') {
                        $categorizedTrophies['uncategorized'][] = $value;
                    } else {
                        $categorizedTrophies[$value->Trophy->th_trophy_category_id][] = $value;
                    }
                }
            }
        }

        $params['trophies'] = $categorizedTrophies;
        $params['trophyCategories'] = \XF::app()->em()->getFinder(
            'ThemeHouse\UserImprovements:TrophyCategory'
        )->order('display_order')->fetch();
    }

    /**
     * @param Templater $templater
     * @param $type
     * @param $template
     * @param array $params
     */
    public static function helpPageTrophies(Templater $templater, &$type, &$template, array &$params)
    {
        /** @var \ThemeHouse\UserImprovements\XF\Repository\Trophy $trophyRepo */
        $trophyRepo = \XF::app()->em()->getRepository('XF:Trophy');
        $userId = \XF::visitor()->user_id;
        $userTrophies = $trophyRepo->findUserTrophies($userId)->fetch();

        $categoryFinder = \XF::app()->em()->getFinder('ThemeHouse\UserImprovements:TrophyCategory');
        $params['trophyCategories'] = $categoryFinder->order('display_order')->fetch();

        $trophies = $trophyRepo->prepareTHUITrophiesForHelpPage($params['trophies'], $userTrophies);

        $trophiesCategorized = [];

        foreach ($trophies as $trophyId => $trophy) {
            $trophyCategoryId = $trophy['entity']->th_trophy_category_id ?: 'uncategorized';
            $trophiesCategorized[$trophyCategoryId][$trophyId] = $trophy;
        }

        $params['trophies'] = $trophiesCategorized;
    }
}
