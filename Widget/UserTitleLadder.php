<?php

namespace ThemeHouse\UserImprovements\Widget;

use XF\Widget\AbstractWidget;

/**
 * Class UserTitleLadder
 * @package ThemeHouse\UserImprovements\Widget
 */
class UserTitleLadder extends AbstractWidget
{
    /**
     * @return \XF\Widget\WidgetRenderer
     */
    public function render()
    {
        /** @var \XF\Entity\Option $option */
        $option = $this->em()->find('XF:Option', 'userTitleLadderField');
        /** @var \XF\Repository\UserTitleLadder $repo */
        $repo = $this->app()->em()->getRepository('XF:UserTitleLadder');
        $titles = $repo->findLadder()->fetch();

        $lastKey = 0;
        $nextKey = 0;
        $points = \XF::visitor()->{$option->option_value};

        foreach ($titles as $key => $value) {
            if ($key <= $points) {
                $lastKey = $key;
                continue;
            }

            $nextKey = $key;
            break;
        }

        $templater = $this->app->templater();

        if ($nextKey) {
            $progress = ($points - $lastKey) / ($nextKey - $lastKey) * 100 | 0;
            $phrase = \XF::phrase('thuserimprovements_progress_bar_tooltip', [
                'has' => $templater->filterNumber($templater, $points, $escape),
                'needs' => $templater->filterNumber($templater, $nextKey, $escape),
                'word' => \XF::phrase('thuserimprovements_progress_bar_' . $option->option_value)
            ]);
        } else {
            $progress = 100;
            $phrase = \XF::phrase('thuserimprovements_progress_bar_tooltip_max');
        }

        return $this->renderer('thuserimprovements_widget_user_title_ladder', [
            'progress' => $progress,
            'tooltip' => $phrase
        ]);
    }
}
