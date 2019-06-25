<?php

namespace ThemeHouse\UserImprovements\Entity;

use ThemeHouse\UserImprovements\XF\Entity\Trophy;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property int trophy_category_id
 * @property mixed|null MasterTitle
 * @property mixed|null addon_id
 */
class TrophyCategory extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_userimprovements_trophy_category';
        $structure->shortName = 'ThemeHouse\UserImprovements:TrophyCategory';
        $structure->primaryKey = 'trophy_category_id';
        $structure->columns = [
            'trophy_category_id' => [
                'type' => self::STR,
                'maxLength' => 50,
                'required' => 'thuserimprovements_please_enter_valid_trophy_category_id',
                'unique' => 'thuserimprovements_trophy_category_ids_must_be_unique',
                'match' => 'alphanumeric'
            ],
            'display_order' => ['type' => self::UINT, 'default' => 1]
        ];
        $structure->getters = [
            'title' => true,
            'addon_id' => true
        ];
        $structure->relations = [
            'MasterTitle' => [
                'entity' => 'XF:Phrase',
                'type' => self::TO_ONE,
                'conditions' => [
                    ['language_id', '=', 0],
                    ['title', '=', 'trophy_category.', '$trophy_category_id']
                ]
            ]
        ];
        $structure->options = [
            'delete_empty_only' => true
        ];

        return $structure;
    }

    /**
     * @return \XF\Phrase
     */
    public function getTitle()
    {
        return \XF::phrase($this->getPhraseName());
    }

    /**
     * @return string
     */
    public function getPhraseName()
    {
        return 'trophy_category.' . $this->trophy_category_id;
    }

    /**
     * @return mixed|null|\XF\Entity\Phrase
     */
    public function getMasterPhrase()
    {
        $phrase = $this->MasterTitle;
        if (!$phrase) {
            /** @var \XF\Entity\Phrase $phrase */
            $phrase = $this->_em->create('XF:Phrase');
            $phrase->title = $this->_getDeferredValue(function () {
                return $this->getPhraseName();
            }, 'save');
            $phrase->language_id = 0;
            $phrase->addon_id = '';
        }

        return $phrase;
    }

    /**
     * @return string
     */
    public function getAddonId()
    {
        return '';
    }

    /**
     * @throws \Exception
     * @throws \XF\PrintableException
     */
    protected function _postSave()
    {
        if ($this->isUpdate()) {
            if ($this->isChanged('trophy_category_id')) {
                /** @var \XF\Entity\Phrase $phrase */
                $phrase = $this->getExistingRelation('MasterTitle');
                if ($phrase) {
                    $phrase->addon_id = $this->addon_id;
                    $phrase->title = $this->getPhraseName();
                    $phrase->save();
                }

                $trophies = $this->finder('XF:Trophy')
                    ->where('th_trophy_category_id', $this->getExistingValue('trophy_category_id'))
                    ->fetch();

                foreach ($trophies as $trophy) {
                    /** @var Trophy $trophy */
                    $trophy->th_trophy_category_id = $this->trophy_category_id;
                    $trophy->save();
                }
            }
        }
    }

    /**
     *
     */
    protected function _preDelete()
    {
        if ($this->getOption('delete_empty_only')) {
            $hasPermissions = $this->db()->fetchOne(
                'SELECT 1 FROM xf_trophy WHERE th_trophy_category_id = ? LIMIT 1',
                $this->trophy_category_id
            );
            if ($hasPermissions) {
                $this->error(\XF::phrase('thuserimprovements_you_must_delete_all_trophies_within_trophy_category_before_deleted'));
            }
        }
    }

    /**
     *
     */
    protected function _postDelete()
    {
        $phrase = $this->MasterTitle;
        if ($phrase) {
            $phrase->delete();
        }

        if (!$this->getOption('delete_empty_only')) {
            $this->db()->update(
                'xf_trophy',
                ['th_trophy_category_id' => ''],
                'th_trophy_category_id = ?',
                $this->trophy_category_id
            );
        }
    }
}
