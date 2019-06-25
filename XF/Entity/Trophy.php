<?php

namespace ThemeHouse\UserImprovements\XF\Entity;

use ThemeHouse\UserImprovements\Entity\TrophyCategory;
use XF\Mvc\Entity\Structure;

/**
 * @property int th_trophy_category_id
 * @property string th_icon_type
 * @property string th_icon_value
 * @property bool th_hidden
 * @property int th_predecessor
 * @property int th_follower
 * @property string th_icon_css
 *
 * @property Trophy THUIPredecessor
 * @property Trophy THUIFollower
 * @property TrophyCategory THUITrophyCategory
 */
class Trophy extends XFCP_Trophy
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns = array_merge($structure->columns, [
            'th_trophy_category_id' => ['type' => self::STR, 'default' => '', 'maxLength' => 50],
            'th_icon_type' => ['type' => self::STR, 'default' => '', 'maxLength' => 25],
            'th_icon_value' => ['type' => self::STR, 'default' => '', 'maxLength' => 100],
            'th_hidden' => ['type' => self::UINT, 'default' => 0],
            'th_predecessor' => ['type' => self::UINT, 'default' => 0],
            'th_follower' => ['type' => self::UINT, 'default' => 0],
            'th_icon_css' => ['type' => self::STR, 'default' => '']
        ]);

        $structure->relations = array_merge($structure->relations, [
            'THUIPredecessor' => [
                'entity' => 'XF:Trophy',
                'type' => self::TO_ONE,
                'conditions' => 'th_predecessor',
                'primary' => true
            ],
            'THUIFollower' => [
                'entity' => 'XF:Trophy',
                'type' => self::TO_ONE,
                'conditions' => 'th_follower',
                'primary' => true
            ],
            'THUITrophyCategory' => [
                'entity' => 'ThemeHouse\UserImprovements:TrophyCategory',
                'type' => self::TO_ONE,
                'conditions' => [
                    ['trophy_category_id', '=', '$th_trophy_category_id']
                ]
            ]
        ]);

        $structure->getters = array_merge($structure->getters, [
            'RecentlyAwardedUsers' => true
        ]);

        return $structure;
    }

    /**
     * @param int $limit
     * @return \XF\Mvc\Entity\ArrayCollection
     */
    public function getRecentlyAwardedUsers($limit = 5)
    {
        return \XF::app()->em()->getFinder('XF:UserTrophy')
            ->with('User')
            ->where('trophy_id', $this->trophy_id)
            ->where('User.user_state', '=', 'valid')
            ->where('User.is_banned', '=', 0)
            ->order('award_date', 'desc')
            ->limit($limit)
            ->fetch();
    }

    /**
     * @throws \Exception
     * @throws \XF\PrintableException
     */
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isChanged('th_predecessor')) {
            $predecessor = $this->THUIPredecessor;

            /* Reset existing predecessor */
            if ($this->getExistingValue('th_predecessor')) {
                if (!$predecessor || $predecessor->trophy_id !== $this->getExistingValue('th_predecessor')) {
                    /** @var Trophy $trophy */
                    $predecessor = \XF::app()->em()->getFinder('XF:Trophy')
                        ->where('trophy_id', $this->getExistingValue('th_predecessor'))
                        ->fetchOne();
                }

                if ($predecessor && $predecessor->th_follower === $this->trophy_id) {
                    $predecessor->th_follower = 0;
                    $predecessor->save();
                }
            }

            /* Add trophy to new predecessor */
            if ($this->th_predecessor) {
                if (!$predecessor || $predecessor->trophy_id !== $this->th_predecessor) {
                    if ($this->THUIPredecessor && $this->THUIPredecessor->trophy_id === $this->th_predecessor) {
                        $predecessor = $this->THUIPredecessor;
                    } else {
                        /** @var Trophy $trophy */
                        $predecessor = \XF::app()->em()->getFinder('XF:Trophy')
                            ->where('th_predecessor', $this->th_predecessor)
                            ->fetchOne();
                    }
                }

                if ($predecessor) {
                    $this->hydrateRelation('THUIPredecessor', $predecessor);

                    if ($predecessor->th_follower !== $this->trophy_id) {
                        $predecessor->th_follower = $this->trophy_id;
                        $predecessor->save();
                    }
                }
            }
        }

        if ($this->isChanged('th_follower') && $this->getExistingValue('th_follower')) {
            $follower = $this->THUIFollower;

            /* Reset existing follower */
            if (!$follower || $follower->trophy_id !== $this->getExistingValue('th_follower')) {
                /** @var Trophy $trophy */
                $follower = \XF::app()->em()->getFinder('XF:Trophy')
                    ->where('trophy_id', $this->getExistingValue('th_follower'))
                    ->fetchOne();
            }

            if ($follower && $follower->th_predecessor === $this->trophy_id) {
                $follower->th_predecessor = 0;
                $follower->save();
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \XF\PrintableException
     */
    protected function _preDelete()
    {
        parent::_preDelete();

        /* Remove trophy from follower */
        if ($this->th_follower) {
            $follower = $this->THUIFollower;

            if (!$follower) {
                /** @var Trophy $trophy */
                $follower = \XF::app()->em()->getFinder('XF:Trophy')
                    ->where('trophy_id', $this->th_follower)
                    ->fetchOne();
            }

            if ($follower && $follower->th_predecessor === $this->trophy_id) {
                $follower->th_predecessor = 0;
                $follower->save();
            }
        }

        /* Remove trophy from predecessor */
        if ($this->th_predecessor) {
            $predecessor = $this->THUIPredecessor;

            if (!$predecessor) {
                /** @var Trophy $trophy */
                $predecessor = \XF::app()->em()->getFinder('XF:Trophy')
                    ->where('trophy_id', $this->th_predecessor)
                    ->fetchOne();
            }

            if ($predecessor && $predecessor->th_follower === $this->trophy_id) {
                $predecessor->th_follower = 0;
                $predecessor->save();
            }
        }
    }
}
