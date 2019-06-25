<?php

namespace ThemeHouse\UserImprovements\Repository;

use XF\Mvc\Entity\Repository;

/**
 * Class User
 * @package ThemeHouse\UserImprovements\Repository
 */
class User extends Repository
{
    /**
     * @throws \XF\Db\Exception
     */
    public function batchUpdateProfileViews()
    {
        $db = $this->db();
        $db->query("
			UPDATE
				xf_user AS u
			INNER JOIN
				xf_th_userimprovements_user_view AS uv
			ON
				(u.user_id = uv.user_id)
			SET
				u.th_view_count = u.th_view_count + uv.total
		");
        $db->emptyTable('xf_th_userimprovements_user_view');
    }

    /**
     * @param \XF\Entity\User $user
     * @throws \XF\Db\Exception
     */
    public function logProfileView(\XF\Entity\User $user)
    {
        $this->db()->query("
			INSERT INTO xf_th_userimprovements_user_view
				(user_id, total)
			VALUES
				(? , 1)
			ON DUPLICATE KEY UPDATE
				total = total + 1
		", $user->user_id);
    }

    /**
     * Set the profile view counter back to 0.
     *
     * @param \XF\Entity\User $user
     * @throws \XF\Db\Exception
     */
    public function resetProfileViews(\XF\Entity\User $user)
    {
        /** @var \ThemeHouse\UserImprovements\XF\Entity\User $user */
        $user->th_view_count = 0;
        $user->saveIfChanged();

        $this->db()->query("
			DELETE FROM
				xf_th_userimprovements_user_view
			WHERE
				user_id = ?
		", $user->user_id);
    }
}
