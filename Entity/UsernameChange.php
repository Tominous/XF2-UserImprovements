<?php

namespace ThemeHouse\UserImprovements\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int change_id
 * @property int user_id
 * @property string old_username
 * @property int change_date
 */
class UsernameChange extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_userimprovements_username_changes';
        $structure->shortName = 'ThemeHouse\UserImprovements:UsernameChange';
        $structure->primaryKey = 'change_id';
        $structure->columns = [
            'change_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true, 'changeLog' => false],
            'user_id' => ['type' => self::UINT, 'default' => \XF::visitor()->user_id],
            'old_username' => ['type' => self::STR, 'maxLength' => 50],
            'change_date' => ['type' => self::UINT, 'default' => \XF::$time],
        ];

        return $structure;
    }
}
