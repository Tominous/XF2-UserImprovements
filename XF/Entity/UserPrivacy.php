<?php

namespace ThemeHouse\UserImprovements\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * @property string th_view_username_changes
 * @property string th_view_profile_stats
 * @property string th_view_profile_views
 */
class UserPrivacy extends XFCP_UserPrivacy
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns = array_merge($structure->columns, [
            'th_view_profile_stats' => [
                'type' => self::STR,
                'default' => 'everyone',
                'allowedValues' => ['everyone', 'members', 'followed', 'none'],
                'verify' => 'verifyPrivacyChoice'
            ],
            'th_view_username_changes' => [
                'type' => self::STR,
                'default' => 'members',
                'allowedValues' => ['everyone', 'members', 'followed', 'none'],
                'verify' => 'verifyPrivacyChoice'
            ],
            'th_view_profile_views' => [
                'type' => self::STR,
                'default' => 'members',
                'allowedValues' => ['everyone', 'members', 'followed', 'none'],
                'verify' => 'verifyPrivacyChoice'
            ]
        ]);

        return $structure;
    }

    /**
     *
     */
    protected function _setupDefaults()
    {
        parent::_setupDefaults();

        $this->th_view_profile_stats = 'everyone';
        $this->th_view_username_changes = 'members';
        $this->th_view_profile_views = 'followed';
    }
}
