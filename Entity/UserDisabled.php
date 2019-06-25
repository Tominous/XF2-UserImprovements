<?php

namespace ThemeHouse\UserImprovements\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int user_id
 * @property int disable_date
 * @property int latest_restore_date
 */
class UserDisabled extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_userimprovements_user_disables';
        $structure->shortName = 'ThemeHouse\UserImprovements:UserDisabled';
        $structure->primaryKey = 'user_id';
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'default' => \XF::visitor()->user_id],
            'disable_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'latest_restore_date' => ['type' => self::UINT, 'default' => 0]
        ];

        return $structure;
    }
}
