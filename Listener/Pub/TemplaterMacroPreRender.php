<?php

namespace ThemeHouse\UserImprovements\Listener\Pub;

use XF\Template\Templater;

class TemplaterMacroPreRender
{
    protected static $visitorTrophies = null;

    protected static function getVisitorTrophyIds()
    {
        if (!self::$visitorTrophies) {
            $visitor = \XF::visitor();
            self::$visitorTrophies = \XF::db()->fetchAllColumn('
                SELECT
                    trophy_id
                FROM
                  xf_user_trophy
                WHERE
                  user_id = ?
            ', $visitor->user_id);
        }

        return self::$visitorTrophies;
    }

    /**
     * @param $trophyId
     * @return bool
     */
    protected static function visitorHasTrophy($trophyId)
    {
        return in_array($trophyId, self::getVisitorTrophyIds());
    }

    public static function thuserimprovementsTrophyMacrosUserTrophyItem(
        Templater $templater,
        &$type,
        &$template,
        &$name,
        array &$arguments,
        array &$globalVars
    ) {
        $arguments['visitorEarnt'] = self::visitorHasTrophy($arguments['trophy']['trophy_id']);
    }
}
