<?php

namespace ThemeHouse\UserImprovements;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepResult;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;
use XF\Entity\AddOn;

/**
 * Class Setup
 * @package ThemeHouse\UserImprovements
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait {
        install as public traitInstall;
    }
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     * @param array $stepParams
     *
     * @return null|StepResult
     * @throws \XF\PrintableException
     */
    public function install(array $stepParams = [])
    {
        /** @var AddOn $legacyAddOn */
        $legacyAddOn = \XF::em()->find('XF:AddOn', 'KL/UserImprovements');
        if ($legacyAddOn) {
            $this->db()->delete('xf_addon', "addon_id = 'ThemeHouse/UserImprovements'");
            $legacyAddOn->addon_id = 'ThemeHouse/UserImprovements';
            $legacyAddOn->save();
            return null;
        }

        return $this->traitInstall($stepParams);
    }

    /**
     *
     */
    public function installStep1()
    {
        $this->schemaManager()->createTable(
            'xf_th_userimprovements_username_changes',
            function (Create $table) {
                $table->addColumn('change_id', 'INT', 10)->autoIncrement();
                $table->addColumn('user_id', 'INT', 10);
                $table->addColumn('old_username', 'VARCHAR', 50);
                $table->addColumn('change_date', 'INT', 10);
            }
        );
    }

    /**
     *
     */
    public function installStep2()
    {
        $this->schemaManager()->createTable(
            'xf_th_userimprovements_user_disables',
            function (Create $table) {
                $table->addColumn('user_id', 'INT', 10)->primaryKey();
                $table->addColumn('disable_date', 'INT', 10);
                $table->addColumn('latest_restore_date', 'INT', 10);
            }
        );
    }

    /**
     *
     */
    public function installStep3()
    {
        $this->schemaManager()->createTable(
            'xf_th_userimprovements_trophy_category',
            function (Create $table) {
                $table->addColumn('trophy_category_id', 'VARCHAR', 50)->primaryKey();
                $table->addColumn('display_order', 'INT', 10);
            }
        );
    }

    /**
     *
     */
    public function installStep4()
    {
        $this->schemaManager()->createTable(
            'xf_th_userimprovements_user_view',
            function (Create $table) {
                $table->engine('MEMORY');
                $table->addColumn('user_id', 'int');
                $table->addColumn('total', 'int');
                $table->addPrimaryKey('user_id');
            }
        );
    }

    /**
     *
     */
    public function installStep5()
    {
        $this->schemaManager()->alterTable(
            'xf_user',
            function (Alter $table) {
                $table->addColumn('th_name_color_id', 'TINYINT')->setDefault(0);
                $table->addColumn('th_view_count', 'INT')->setDefault(0);
            }
        );
    }

    /**
     *
     */
    public function installStep6()
    {
        $this->schemaManager()->alterTable(
            'xf_user_privacy',
            function (Alter $table) {
                $table->addColumn('th_view_profile_stats', 'ENUM',
                    ['everyone', 'members', 'followed', 'none'])->setDefault('everyone');
                $table->addColumn('th_view_username_changes', 'ENUM',
                    ['everyone', 'members', 'followed', 'none'])->setDefault('everyone');
                $table->addColumn('th_view_profile_views', 'ENUM',
                    ['everyone', 'members', 'followed', 'none'])->setDefault('everyone');
            }
        );
    }

    /**
     *
     */
    public function installStep7()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->addColumn('th_trophy_category_id', 'VARCHAR', 50)->setDefault('');
                $table->addColumn('th_icon_type', 'VARCHAR', 25)->setDefault('');
                $table->addColumn('th_icon_value', 'VARCHAR', 100)->setDefault('');
                $table->addColumn('th_hidden', 'TINYINT', 1)->setDefault(0);
                $table->addColumn('th_predecessor', 'INT', 10)->setDefault(0);
                $table->addColumn('th_follower', 'INT', 10)->setDefault(0);
                $table->addColumn('th_icon_css', 'BLOB')->nullable();
            }
        );
    }

    /**
     *
     */
    public function installStep8()
    {
        $this->schemaManager()->alterTable(
            'xf_user_trophy',
            function (Alter $table) {
                $table->addColumn('th_showcased', 'BOOL')->setDefault(0);
            }
        );
    }

    /**
     *
     */
    public function installStep9()
    {
        $this->db()->insertBulk('xf_connected_account_provider', [
            [
                'provider_id' => 'th_battlenet',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\BattleNet',
                'display_order' => 80,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_deviantart',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\DeviantArt',
                'display_order' => 90,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_dropbox',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Dropbox',
                'display_order' => 100,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_discord',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Discord',
                'display_order' => 110,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_amazon',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Amazon',
                'display_order' => 120,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_reddit',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Reddit',
                'display_order' => 130,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_pinterest',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Pinterest',
                'display_order' => 140,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_instagram',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Instagram',
                'display_order' => 150,
                'options' => '[]'
            ],
            [
                'provider_id' => 'th_twitch',
                'provider_class' => 'ThemeHouse\\UserImprovements:Provider\\Twitch',
                'display_order' => 160,
                'options' => '[]'
            ]
        ], 'provider_id');
    }

    /**
     *
     */
    public function installStep10()
    {
        $this->applyGlobalPermissionInt('klUI', 'klUITSS_profile', -1);
        $this->applyGlobalPermissionInt('klUI', 'klUITSS_postbit', -1);
        $this->applyGlobalPermissionInt('klUI', 'klUITSS_tooltip', -1);
        $this->app->jobManager()->enqueueUnique(
            'permissionRebuild',
            'XF:PermissionRebuild',
            [],
            false
        );
    }

    /**
     *
     */
    public function upgrade1000171Step1()
    {
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_amazon'], "provider_id = 'amazon'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_battlenet'],
            "provider_id = 'battlenet'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_deviantart'],
            "provider_id = 'deviantart'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_discord'],
            "provider_id = 'discord'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_dropbox'],
            "provider_id = 'dropbox'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_instagram'],
            "provider_id = 'instagram'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_pinterest'],
            "provider_id = 'pinterest'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_reddit'], "provider_id = 'reddit'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'kl_twitch'], "provider_id = 'twitch'");
    }

    /**
     *
     */
    public function upgrade1000171Step2()
    {
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_amazon'], "provider = 'amazon'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_battlenet'], "provider = 'battlenet'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_deviantart'], "provider = 'deviantart'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_discord'], "provider = 'discord'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_dropbox'], "provider = 'dropbox'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_instagram'], "provider = 'instagram'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_pinterest'], "provider = 'pinterest'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_reddit'], "provider = 'reddit'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'kl_twitch'], "provider = 'twitch'");
    }

    /**
     *
     */
    public function upgrade1000370Step1()
    {
        /** @var \XF\Repository\ConnectedAccount $repo */
        $repo = $this->app->em()->getRepository('XF:ConnectedAccount');

        $conAccs = $this->app->em()->getFinder('XF:UserConnectedAccount')
            ->where('provider', 'LIKE', 'kl%')
            ->fetch()->groupBy('user_id');

        foreach ($conAccs as $key => $group) {
            $firstEntry = array_shift($group);
            $repo->rebuildUserConnectedAccountCache($firstEntry->User);
        }
    }

    /**
     *
     */
    public function upgrade1000470Step1()
    {
        $this->schemaManager()->alterTable(
            'xf_user_trophy',
            function (Alter $table) {
                $table->addColumn('kl_ui_showcased', 'BOOL')->setDefault(0);
            }
        );
    }

    /**
     *
     */
    public function upgrade1010071Step1()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->addColumn('kl_ui_icon_css', 'BLOB');
            }
        );
    }

    /**
     *
     */
    public function upgrade1010072Step1()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->changeColumn('kl_ui_icon_css')->nullable();
            }
        );
    }

    /**
     *
     */
    public function upgrade1020470Step1()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->addColumn('kl_ui_percentage', 'FLOAT')->setDefault(0);
            }
        );
    }

    /**
     *
     */
    public function upgrade1020570Step1()
    {
        $this->schemaManager()->renameTable('xf_kl_ui_username_changes', 'xf_th_userimprovements_username_changes');
        $this->schemaManager()->renameTable('xf_kl_ui_user_disables', 'xf_th_userimprovements_user_disables');
        $this->schemaManager()->renameTable('xf_kl_ui_trophy_category', 'xf_th_userimprovements_trophy_category');
        $this->schemaManager()->renameTable('xf_kl_ui_user_view', 'xf_th_userimprovements_user_view');
    }

    /**
     *
     */
    public function upgrade1020570Step2()
    {
        $this->schemaManager()->alterTable(
            'xf_user',
            function (Alter $table) {
                $table->renameColumn('kl_ui_name_color_id', 'th_name_color_id');
                $table->renameColumn('kl_ui_view_count', 'th_view_count');
            }
        );
    }

    /**
     *
     */
    public function upgrade1020570Step3()
    {
        $this->schemaManager()->alterTable(
            'xf_user_privacy',
            function (Alter $table) {
                $table->renameColumn('kl_ui_view_profile_stats', 'th_view_profile_stats');
                $table->renameColumn('kl_ui_view_username_changes', 'th_view_username_changes');
                $table->renameColumn('kl_ui_view_profile_views', 'th_view_profile_views');
            }
        );
    }

    /**
     *
     */
    public function upgrade1020570Step4()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->renameColumn('kl_ui_trophy_category_id', 'th_trophy_category_id');
                $table->renameColumn('kl_ui_icon_type', 'th_icon_type');
                $table->renameColumn('kl_ui_icon_value', 'th_icon_value');
                $table->renameColumn('kl_ui_hidden', 'th_hidden');
                $table->renameColumn('kl_ui_predecessor', 'th_predecessor');
                $table->renameColumn('kl_ui_follower', 'th_follower');
                $table->renameColumn('kl_ui_icon_css', 'th_icon_css');
                $table->dropColumns([
                    'kl_ui_percentage'
                ]);
            }
        );
    }

    /**
     *
     */
    public function upgrade1020570Step5()
    {
        $this->schemaManager()->alterTable(
            'xf_user_trophy',
            function (Alter $table) {
                $table->renameColumn('kl_ui_showcased', 'th_showcased');
            }
        );
    }

    /**
     *
     */
    public function upgrade1020570Step6()
    {
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_amazon'],
            "provider_id = 'kl_amazon'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_battlenet'],
            "provider_id = 'kl_battlenet'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_deviantart'],
            "provider_id = 'kl_deviantart'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_discord'],
            "provider_id = 'kl_discord'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_dropbox'],
            "provider_id = 'kl_dropbox'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_instagram'],
            "provider_id = 'kl_instagram'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_pinterest'],
            "provider_id = 'kl_pinterest'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_reddit'],
            "provider_id = 'kl_reddit'");
        $this->db()->update('xf_connected_account_provider', ['provider_id' => 'th_twitch'],
            "provider_id = 'kl_twitch'");
    }

    /**
     *
     */
    public function upgrade1020570Step7()
    {
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_amazon'], "provider = 'kl_amazon'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_battlenet'], "provider = 'kl_battlenet'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_deviantart'], "provider = 'kl_deviantart'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_discord'], "provider = 'kl_discord'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_dropbox'], "provider = 'kl_dropbox'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_instagram'], "provider = 'kl_instagram'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_pinterest'], "provider = 'kl_pinterest'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_reddit'], "provider = 'kl_reddit'");
        $this->db()->update('xf_user_connected_account', ['provider' => 'th_twitch'], "provider = 'kl_twitch'");
    }

    /**
     *
     */
    public function upgrade1020570Step8()
    {
        $this->db()->rawQuery("
            UPDATE xf_phrase
            SET title = SUBSTRING(title, 7)
            WHERE title LIKE 'kl_ui_trophy_category.%'
        ");
    }

    /**
     *
     */
    public function upgrade1030092Step1()
    {
        $this->app->jobManager()->enqueueUnique('languageRebuild', 'XF:Atomic', [
            'execute' => ['XF:PhraseRebuild', 'XF:TemplateRebuild']
        ]);
    }

    /**
     *
     */
    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_th_userimprovements_username_changes');
    }

    /**
     *
     */
    public function uninstallStep2()
    {
        $this->schemaManager()->dropTable('xf_th_userimprovements_user_disables');
    }

    /**
     *
     */
    public function uninstallStep3()
    {
        $this->schemaManager()->dropTable('xf_th_userimprovements_trophy_category');
    }

    /**
     *
     */
    public function uninstallStep4()
    {
        $this->schemaManager()->dropTable('xf_th_userimprovements_user_view');
    }

    /**
     *
     */
    public function uninstallStep5()
    {
        $this->schemaManager()->alterTable(
            'xf_user',
            function (Alter $table) {
                $table->dropColumns([
                    'th_name_color_id'
                ]);
            }
        );
    }

    /**
     *
     */
    public function uninstallStep6()
    {
        $this->schemaManager()->alterTable(
            'xf_user_privacy',
            function (Alter $table) {
                $table->dropColumns([
                    'th_view_profile_stats',
                    'th_view_username_changes',
                    'th_view_profile_views'
                ]);
            }
        );
    }

    /**
     *
     */
    public function uninstallStep7()
    {
        $this->schemaManager()->alterTable(
            'xf_trophy',
            function (Alter $table) {
                $table->dropColumns([
                    'th_trophy_category_id',
                    'th_icon_type',
                    'th_icon_value',
                    'th_hidden',
                    'th_predecessor',
                    'th_follower',
                    'th_icon_css',
                    'kl_ui_percentage'
                ]);
            }
        );
    }

    /**
     *
     */
    public function uninstallStep8()
    {
        $this->schemaManager()->alterTable(
            'xf_user_trophy',
            function (Alter $table) {
                $table->dropColumns([
                    'th_showcased'
                ]);
            }
        );
    }

    /**
     *
     */
    public function uninstallStep9()
    {
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_amazon'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_battlenet'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_deviantart'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_discord'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_dropbox'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_instagram'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_pinterest'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_reddit'");
        $this->db()->delete('xf_connected_account_provider', "provider_id = 'th_twitch'");
    }

    /**
     *
     */
    public function uninstallStep10()
    {
        $this->db()->delete('xf_user_connected_account', "provider = 'th_amazon'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_battlenet'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_deviantart'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_discord'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_dropbox'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_instagram'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_pinterest'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_reddit'");
        $this->db()->delete('xf_user_connected_account', "provider = 'th_twitch'");
    }
}
