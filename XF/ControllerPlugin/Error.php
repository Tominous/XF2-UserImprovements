<?php

namespace ThemeHouse\UserImprovements\XF\ControllerPlugin;

/**
 * Class Error
 * @package ThemeHouse\UserImprovements\XF\ControllerPlugin
 */
class Error extends XFCP_Error
{
    /**
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \Exception
     * @throws \XF\PrintableException
     */
    public function actionDisabled()
    {
        /** @var \XF\Mvc\Entity\Finder $finder */
        $finder = \XF::app()->em()->getFinder('ThemeHouse\UserImprovements:UserDisabled');

        /** @var \ThemeHouse\UserImprovements\Entity\UserDisabled $record */
        $record = $finder->where('user_id', \XF::visitor()->user_id)->fetchOne();

        if ($record && $record->latest_restore_date >= \XF::$time) {
            if ($this->isPost()) {
                $record->delete();
                $visitor = \XF::visitor();
                $visitor->user_state = 'valid';
                $visitor->save();
                return $this->redirect($this->getDynamicRedirect());
            } else {
                $params = [
                    'record' => $record
                ];
                return $this->view('ThemeHouse\UserImprovements:SelfReactivate', 'thuserimprovements_self_reactivate',
                    $params);
            }
        }

        return parent::actionDisabled();
    }
}
