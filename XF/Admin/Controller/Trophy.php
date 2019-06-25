<?php

namespace ThemeHouse\UserImprovements\XF\Admin\Controller;

use ThemeHouse\UserImprovements\Entity\TrophyCategory;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Trophy
 * @package ThemeHouse\UserImprovements\XF\Admin\Controller
 */
class Trophy extends XFCP_Trophy
{
    /**
     * @return View
     */
    public function actionIndex()
    {
        $view = parent::actionIndex();

        if ($view instanceof View && $view->getParam('trophies')) {
            $categoryRepo = $this->getTHUITrophyCategoryRepo();

            $trophies = $view->getParam('trophies')->groupBy('th_trophy_category_id');
            $categoryFinder = $categoryRepo->findTrophyCategoriesForList();
            $categories = $categoryFinder->fetch();

            if (isset($trophies[''])) {
                $trophies['uncategorized'] = $trophies[''];
                unset($trophies['']);
            }

            $view->setParam('trophies', $trophies);
            $view->setParam('trophyCategories', $categories);

            $options = $view->getParam('options');
            $options[] = $this->em()->find('XF:Option', 'klUIProfileTrophyShowcase');
            $view->setParam('options', $options);
        }

        return $view;
    }

    /**
     * @return \ThemeHouse\UserImprovements\Repository\TrophyCategory
     */
    protected function getTHUITrophyCategoryRepo()
    {
        /** @var \ThemeHouse\UserImprovements\Repository\TrophyCategory $repo */
        $repo = $this->repository('ThemeHouse\UserImprovements:TrophyCategory');
        return $repo;
    }

    /**
     * @param ParameterBag $params
     * @return View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionTHUICategoryEdit(ParameterBag $params)
    {
        /** @var \ThemeHouse\UserImprovements\Entity\TrophyCategory $category */
        $category = $this->assertTHUITrophyCategoryExists($params['trophy_category_id']);

        return $this->thuiTrophyCategoryAddEdit($category);
    }

    /**
     * @param string $id
     * @param array|string|null $with
     * @param null|string $phraseKey
     *
     * @return \ThemeHouse\UserImprovements\XF\Entity\Trophy
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertTHUITrophyCategoryExists($id, $with = null, $phraseKey = null)
    {
        /** @var \ThemeHouse\UserImprovements\XF\Entity\Trophy $trophy */
        $trophy = $this->assertRecordExists('ThemeHouse\UserImprovements:TrophyCategory', $id, $with, $phraseKey);
        return $trophy;
    }

    /**
     * @param TrophyCategory $category
     * @return View
     */
    protected function thuiTrophyCategoryAddEdit(TrophyCategory $category)
    {
        $viewParams = [
            'category' => $category
        ];

        return $this->view('ThemeHouse\UserImprovements:TrophyCategory\Edit', 'thuserimprovements_trophy_category_edit',
            $viewParams);
    }

    /**
     * @return View
     */
    public function actionTHUICategoryAdd()
    {
        /** @var \ThemeHouse\UserImprovements\Entity\TrophyCategory $category */
        $category = $this->em()->create('ThemeHouse\UserImprovements:TrophyCategory');

        return $this->thuiTrophyCategoryAddEdit($category);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionTHUICategorySave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params['trophy_category_id']) {
            /** @var \ThemeHouse\UserImprovements\Entity\TrophyCategory $category */
            $category = $this->assertTHUITrophyCategoryExists($params['trophy_category_id']);
        } else {
            /** @var \ThemeHouse\UserImprovements\Entity\TrophyCategory $category */
            $category = $this->em()->create('ThemeHouse\UserImprovements:TrophyCategory');
        }

        $this->thuiTrophyCategorySaveProcess($category)->run();

        return $this->redirect($this->buildLink('trophies') . $this->buildLinkHash($category->trophy_category_id));
    }

    /**
     * @param TrophyCategory $category
     * @return FormAction
     */
    protected function thuiTrophyCategorySaveProcess(TrophyCategory $category)
    {
        $form = $this->formAction();

        $categoryInput = $this->filter([
            'trophy_category_id' => 'str',
            'display_order' => 'uint'
        ]);

        $form->basicEntitySave($category, $categoryInput);

        $phraseInput = $this->filter([
            'title' => 'str'
        ]);

        $form->validate(function (FormAction $form) use ($phraseInput) {
            if ($phraseInput['title'] === '') {
                $form->logError(\XF::phrase('please_enter_valid_title'), 'title');
            }
        });

        $form->apply(function () use ($phraseInput, $category) {
            $masterTitle = $category->getMasterPhrase();
            $masterTitle->phrase_text = $phraseInput['title'];
            $masterTitle->save();
        });

        return $form;
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|View
     * @throws \XF\PrintableException
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionTHUICategoryDelete(ParameterBag $params)
    {
        /** @var TrophyCategory $category */
        $category = $this->assertTHUITrophyCategoryExists($params['trophy_category_id']);
        if (!$category->preDelete()) {
            return $this->error($category->getErrors());
        }

        if ($this->isPost()) {
            $category->delete();
            return $this->redirect($this->buildLink('trophies'));
        } else {
            $viewParams = [
                'category' => $category
            ];

            return $this->view(
                'ThemeHouse\UserImprovements:Category\Delete',
                'thuserimprovements_trophy_category_delete',
                $viewParams
            );
        }
    }

    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionTHUIReward()
    {
        $categoryRepo = $this->getTHUITrophyCategoryRepo();
        $trophyRepo = $this->getTrophyRepo();

        $trophies = $trophyRepo->findTrophiesForList()->fetch()->groupBy('th_trophy_category_id');
        $categoryFinder = $categoryRepo->findTrophyCategoriesForList();
        $categories = $categoryFinder->fetch();

        if (isset($trophies[''])) {
            $trophies['uncategorized'] = $trophies[''];
            unset($trophies['']);
        }

        $viewParams = [
            'trophies' => $trophies,
            'categories' => $categories
        ];

        return $this->view('ThemeHouse\UserImprovements:Trophy\Reward', 'thuserimprovements_reward_trophy',
            $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\Redirect
     */
    public function actionTHUIRewardSave()
    {
        $input = $this->filter([
            'users' => 'str',
            'trophies' => 'array-int'
        ]);

        $users = explode(',', $input['users']);
        $users = array_map('trim', $users);
        $users = array_filter($users);
        /** @var \XF\Repository\User $userRepo */
        $userRepo = $this->repository('XF:User');
        $users = $userRepo->getUsersByNames($users);

        $trophyRepo = $this->getTrophyRepo();

        $trophies = $this->finder('XF:Trophy')
            ->where('trophy_id', $input['trophies'])
            ->fetch();

        foreach ($users as $user) {
            foreach ($trophies as $trophy) {
                $trophyRepo->awardTrophyToUser($trophy, $user);
            }
        }

        return $this->redirect($this->buildLink('trophies'), \XF::phrase('thuserimprovements_trophies_rewarded'));
    }

    /**
     * @param \XF\Entity\Trophy $trophy
     * @return FormAction
     */
    protected function trophySaveProcess(\XF\Entity\Trophy $trophy)
    {
        $form = parent::trophySaveProcess($trophy);

        $trophyInput = $this->filter([
            'th_trophy_category_id' => 'str',
            'th_icon_type' => 'str',
            'th_hidden' => 'uint',
            'th_predecessor' => 'uint',
            'th_icon_css' => 'str'
        ]);

        if ($trophyInput['th_predecessor']) {
            /** @var \ThemeHouse\UserImprovements\XF\Entity\Trophy $predecessor */
            $predecessor = $this->assertTrophyExists($trophyInput['th_predecessor']);
            if ($predecessor) {
                $trophyInput['th_trophy_category_id'] = $predecessor->th_trophy_category_id;
            } else {
                $trophyInput['th_predecessor'] = 0;
            }
        }

        if ($trophyInput['th_icon_type'] == 'fa') {
            $trophyInput['th_icon_value'] = $this->filter('th_icon_fa', 'str');
        } elseif ($trophyInput['th_icon_type'] == 'image') {
            $trophyInput['th_icon_value'] = $this->filter('th_icon_image', 'str');
        } else {
            $trophyInput['th_icon_value'] = '';
        }

        $form->basicEntitySave($trophy, $trophyInput);

        return $form;
    }

    /**
     * @param \XF\Entity\Trophy $trophy
     * @return View
     */
    protected function trophyAddEdit(\XF\Entity\Trophy $trophy)
    {
        $view = parent::trophyAddEdit($trophy);

        if ($view instanceof View) {
            $category = $this->filter(['trophy_category_id' => 'STR']);

            $trophy = $view->getParam('trophy');
            if ($category['trophy_category_id'] && !$trophy->th_trophy_category_id) {
                $trophy->th_trophy_category_id = $category['trophy_category_id'];
            }

            $categoryRepo = $this->getTHUITrophyCategoryRepo();
            /** @var \XF\Mvc\Entity\Finder $categoryFinder */
            $categoryFinder = $categoryRepo->findTrophyCategoriesForList();
            $categories = $categoryFinder->fetch();

            $trophyRepo = $this->getTrophyRepo();
            $trophyFinder = $trophyRepo->findTrophiesForList();

            $trophyFinder
                ->where('th_predecessor', '!=', $trophy->trophy_id)
                ->where('trophy_id', '!=', $trophy->trophy_id);

            $trophies = $trophyFinder->fetch();

            $view->setParam('categories', $categories);
            $view->setParam('trophies', $trophies);
        }

        return $view;
    }
}
