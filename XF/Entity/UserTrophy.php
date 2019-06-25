<?php

namespace ThemeHouse\UserImprovements\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * Class UserTrophy
 * @package ThemeHouse\UserImprovements\XF\Entity
 *
 * @property boolean th_showcased
 */
class UserTrophy extends XFCP_UserTrophy
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns = array_merge($structure->columns, [
            'th_showcased' => ['type' => self::BOOL, 'default' => 0]
        ]);

        return $structure;
    }
}
