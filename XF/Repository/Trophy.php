<?php

namespace ThemeHouse\UserImprovements\XF\Repository;

/**
 * Class Trophy
 * @package ThemeHouse\UserImprovements\XF\Repository
 */
class Trophy extends XFCP_Trophy
{
    /**
     * @param $trophies
     * @param $userTrophies
     * @return array
     */
    public function prepareTHUITrophiesForHelpPage($trophies, $userTrophies)
    {
        $userId = \XF::visitor()->user_id;

        $newTrophies = [];

        foreach ($trophies as $trophyId => $trophy) {
            $newTrophy = [
                'earned' => isset($userTrophies["{$userId}-{$trophyId}"]),
                'level' => 0,
                'max_level' => 0,
                'entity' => $trophy,
                'predecessors' => [],
                'followers' => [],
            ];

            if ($newTrophy['earned'] && $trophy->th_predecessor) {
                $newTrophy['predecessors'] = $this->getTHUITrophyPredecessors($trophy, $trophies);
            }
            if (($newTrophy['earned'] || !$trophy->th_predecessor) && $trophy->th_follower) {
                $newTrophy['followers'] = $this->getTHUITrophyFollowers($trophy, $trophies);
            }

            $newTrophies[$trophyId] = $newTrophy;
        }

        foreach ($newTrophies as $trophy) {
            if ($trophy['earned']) {
                $predecessorIds = array_keys($trophy['predecessors']);
                foreach ($predecessorIds as $predecessorId) {
                    unset($newTrophies[$predecessorId]);
                }
            }

            if ($trophy['earned'] || !$trophy['entity']->th_predecessor) {
                $followerIds = array_keys($trophy['followers']);
                foreach ($followerIds as $followerId) {
                    if (isset($newTrophies[$followerId]) && !$newTrophies[$followerId]['earned']) {
                        unset($newTrophies[$followerId]);
                    }
                }
            }
        }

        foreach ($newTrophies as &$trophy) {
            $trophy['level'] = count($trophy['predecessors']) + 1;
            $trophy['max_level'] = $trophy['level'] + count($trophy['followers']);
        }

        return $newTrophies;
    }

    /**
     * @param $pred
     * @param $trophies
     * @return array
     */
    public function getTHUITrophyPredecessors($pred, $trophies)
    {
        $predecessors = [];

        while ($pred->th_predecessor && $pred = $trophies[$pred->th_predecessor]) {
            $predecessors[$pred->trophy_id] = $pred;
        }


        uasort($predecessors, function ($a, $b) {
            return $a->trophy_points > $b->trophy_points;
        });

        return $predecessors;
    }

    /**
     * @param $follower
     * @param $trophies
     * @return array
     */
    public function getTHUITrophyFollowers($follower, $trophies)
    {
        $followers = [];

        while ($follower->th_follower && $follower = $trophies[$follower->th_follower]) {
            $followers[$follower->trophy_id] = $follower;
        }

        uasort($followers, function ($a, $b) {
            return $a->trophy_points > $b->trophy_points;
        });

        return $followers;
    }

    /**
     * @param $trophies
     * @return array
     */
    public function getTHUITrophyProgressCriteria($trophies)
    {
        $rule = null;
        $dataKey = null;
        $additionalData = [];

        if (count($trophies) < 2) {
            return [];
        }

        $supportedRules = $this->getTHUISupportedTrophyProgressRules();

        foreach ($trophies as $trophy) {
            if (count($trophy->user_criteria) !== 1) {
                return [];
            }
            $userCriteria = $trophy->user_criteria[0];
            if (!$rule) {
                if (!isset($supportedRules[$userCriteria['rule']])) {
                    return [];
                }
                $rule = $userCriteria['rule'];
            } elseif ($userCriteria['rule'] !== $rule) {
                return [];
            }

            #$dataKeys = array_keys($userCriteria['data']);
            if (!$dataKey && !$additionalData) {
                $additionalData = $userCriteria['data'];
            } elseif (!$dataKey && $additionalData) {
                foreach ($additionalData as $key => $data) {
                    if (!isset($userCriteria['data'][$key])) {
                        return [];
                    }
                    if ($userCriteria['data'][$key] === $data) {
                        unset($userCriteria['data'][$key]);
                        continue;
                    }
                    if (!$dataKey) {
                        $dataKey = $key;
                        unset($userCriteria['data'][$key]);
                        unset($additionalData[$key]);
                        continue;
                    }
                    return [];
                }
            } elseif ($dataKey && isset($userCriteria['data'][$dataKey])) {
                unset($userCriteria['data'][$dataKey]);
                if ($userCriteria['data'] != $additionalData) {
                    return [];
                }
            }
        }

        if (!$dataKey) {
            return [];
        }

        return [
            'rule' => $rule,
            'dataKey' => $dataKey,
            'additionalData' => $additionalData,
            'valueKey' => $supportedRules[$rule]['valueKey'],
            'statsPhrase' => \XF::phrase($supportedRules[$rule]['statsPhraseTitle']),
        ];
    }

    /**
     * @return array
     */
    protected function getTHUISupportedTrophyProgressRules()
    {
        return [
            'messages_posted' => [
                'valueKey' => 'message_count',
                'statsPhraseTitle' => 'thuserimprovements_most_messages',
            ],
            'reaction_score' => [
                'valueKey' => 'reaction_score',
                'statsPhraseTitle' => 'thuserimprovements_highest_reaction_score',
            ],
        ];
    }
}
