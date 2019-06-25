<?php

namespace ThemeHouse\UserImprovements\XF\Entity;

/**
 * Class MemberStat
 * @package ThemeHouse\UserImprovements\XF\Entity
 */
class MemberStat extends XFCP_MemberStat
{
    /**
     * @return array
     */
    public function getTHUITrophies()
    {
        /** @var \ThemeHouse\UserImprovements\XF\Repository\Trophy $trophyRepo */
        $trophyRepo = $this->em()->getRepository('XF:Trophy');

        $trophies = $trophyRepo->findTrophiesForList();

        return $this->filterTHUITrophiesBySortOrder($trophies);
    }

    /**
     * @param $trophies
     * @return array
     */
    protected function filterTHUITrophiesBySortOrder($trophies)
    {
        $filtered = [];

        $sortOrderRules = $this->getTHUISortOrderRules();

        if (isset($sortOrderRules[$this->sort_order])) {
            $rule = $sortOrderRules[$this->sort_order];

            foreach ($trophies as $trophy) {
                if (count($trophy->user_criteria) !== 1) {
                    continue;
                }
                $userCriteria = isset($trophy->user_criteria[0]) ? $trophy->user_criteria[0] : null;
                if ($userCriteria['rule'] === $rule) {
                    $filtered[$trophy->trophy_id] = $trophy;
                }
            }
        }

        return $filtered;
    }

    /**
     * @return array
     */
    protected function getTHUISortOrderRules()
    {
        return [
            'message_count' => 'messages_posted',
            'reaction_score' => 'reaction_score',
        ];
    }
}
