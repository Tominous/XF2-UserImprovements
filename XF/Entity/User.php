<?php

namespace ThemeHouse\UserImprovements\XF\Entity;

use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Structure;

/**
 * @property int th_name_color_id
 * @property int th_view_count
 *
 * @property ArrayCollection Trophies
 * @property ArrayCollection|UsernameChange[] THUIUsernameHistory
 */
class User extends XFCP_User
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns = array_merge($structure->columns, [
            'th_name_color_id' => ['type' => self::UINT, 'max' => 27, 'default' => 0, 'changeLog' => false],
            'th_view_count' => ['type' => self::UINT, 'default' => 0]
        ]);

        $structure->relations = array_merge($structure->relations, [
            'Trophies' => [
                'entity' => 'XF:UserTrophy',
                'type' => self::TO_MANY,
                'conditions' => 'user_id',
                'key' => 'trophy_id'
            ],
            'THUIUsernameHistory' => [
                'entity' => 'ThemeHouse\UserImprovements:UsernameChange',
                'type' => self::TO_MANY,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ]);

        return $structure;
    }

    /**
     * @return bool
     */
    public function canViewTHUIProfileStatsBar()
    {
        $visitor = \XF::visitor();

        return (($this->user_id == $visitor->user_id || $this->isPrivacyCheckMet('th_view_profile_stats', $visitor))
                && $this->user_state != 'disabled')
            || $visitor->hasPermission('klUIModerator', 'klUIBypassPSBPrivacy')
            || !$this->hasPermission('klUI', 'klUIManageStatsPrivacy');
    }

    /**
     * @return bool
     */
    public function canViewTHUIUsernameHistory()
    {
        $visitor = \XF::visitor();

        return ($visitor->hasPermission('klUI', 'klUIViewUsernameChanges')
                && ($this->user_id == $visitor->user_id || $this->isPrivacyCheckMet('th_view_username_changes',
                        $visitor))
                && $this->user_state != 'disabled')
            || $visitor->hasPermission('klUIModerator', 'klUIBypassUNCPrivacy')
            || !$this->hasPermission('klUI', 'klUIManageUsernamePrivacy');
    }

    /**
     * @return bool
     */
    public function canViewTHUIProfileViewCount()
    {
        $visitor = \XF::visitor();

        return $this->isPrivacyCheckMet('th_view_profile_views', $visitor);
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canViewTHUIFullProfile(&$error = null)
    {
        if ($this->user_state === "disabled" && !\XF::visitor()->hasPermission('klUIModerator',
                'klUISeeDeactivatedProfile')) {
            $error = \XF::phraseDeferred('thuserimprovements_account_has_been_deactivated');
            return false;
        } else {
            return parent::canViewFullProfile($error);
        }
    }

    /**
     * @param null $position
     * @return ArrayCollection|array
     */
    public function getTHUIHighestTrophies($position = null)
    {
        $limit = $this->getTHUITrophyShowcaseSize($position);

        if (!$limit) {
            return [];
        }

        $finder = $this->em()->getFinder('XF:UserTrophy')
            ->where('user_id', $this->user_id)
            ->with('Trophy')
            ->order('Trophy.trophy_points', 'DESC')
            ->limit($limit);

        return $finder->fetch();
    }

    /**
     * @param null $position
     * @return bool|int
     */
    public function getTHUITrophyShowcaseSize($position = null)
    {
        if (is_null($position)) {
            $a1 = $this->hasPermission('klUI', "klUITSS_profile");
            $a2 = $this->hasPermission('klUI', "klUITSS_postbit");
            $a3 = $this->hasPermission('klUI', "klUITSS_tooltip");
            $amount = max($a1, $a2, $a3);
        } else {
            $amount = $this->hasPermission('klUI', "klUITSS_{$position}");
        }

        if ($amount == -1) {
            return PHP_INT_MAX;
        } else {
            return $amount;
        }
    }

    /**
     * @param null $position
     * @return ArrayCollection|array
     */
    public function getTHUIUserChoiceTrophies($position = null)
    {
        $limit = $this->getTHUITrophyShowcaseSize($position);

        if (!$limit) {
            return [];
        }

        $finder = $this->em()->getFinder('XF:UserTrophy')
            ->where('user_id', $this->user_id)
            ->with('Trophy')
            ->where('th_showcased', true)
            ->limit($limit);

        $trophies = $finder->fetch();

        if (!$trophies->count()) {
            return $this->getTHUILatestTrophies();
        }

        return $trophies;
    }

    /**
     * @param null $position
     * @return ArrayCollection|array
     */
    public function getTHUILatestTrophies($position = null)
    {
        $limit = $this->getTHUITrophyShowcaseSize($position);

        if (!$limit) {
            return [];
        }

        $finder = $this->em()->getFinder('XF:UserTrophy')
            ->where('user_id', $this->user_id)
            ->with('Trophy')
            ->order('award_date', 'DESC')
            ->limit($limit);

        return $finder->fetch();
    }
}
