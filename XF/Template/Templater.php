<?php

namespace ThemeHouse\UserImprovements\XF\Template;

use XF\Entity\User;

/**
 * Class Templater
 * @package ThemeHouse\UserImprovements\XF\Template
 */
class Templater extends XFCP_Templater
{
    /**
     * Extend the username templater function to support username colors.
     *
     * @param $templater
     * @param $escape
     * @param $user
     * @param bool $includeGroupStyling
     * @return string
     */
    public function fnUsernameClasses($templater, &$escape, $user, $includeGroupStyling = true)
    {
        $classes = parent::fnUsernameClasses($templater, $escape, $user, $includeGroupStyling);

        if ($user instanceof User) {
            if ($user->hasPermission('klUI', 'klUIChoseUsernameColor')) {
                /** @var \ThemeHouse\UserImprovements\XF\Entity\User $user */
                $classes .= " username--color-" . ($user->th_name_color_id ?: 0);
            }
        }

        if (!empty($user['user_state']) && $user['user_state'] == 'disabled') {
            $classes .= ' username--deactivated';
        }

        return trim($classes);
    }
}
