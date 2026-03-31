<?php

namespace SchmitzIT\CustomTabs;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    // -------------------------------------------------------------------------
    // Install
    // -------------------------------------------------------------------------

    public function installStep1(): void
    {
        $this->schemaManager()->createTable('xf_schmitzit_custom_tab', function (Create $table) {
            $table->addColumn('tab_id', 'int')->autoIncrement();
            $table->addColumn('display_order', 'int')->setDefault(10);
            $table->addColumn('tab_title', 'varchar', 100);
            $table->addColumn('active', 'tinyint', 1)->setDefault(1);
            // Comma-separated list of XF user-group IDs allowed to add content
            $table->addColumn('allowed_group_ids', 'blob');
            $table->addPrimaryKey('tab_id');
        });
    }

    public function installStep2(): void
    {
        $this->schemaManager()->createTable('xf_schmitzit_custom_tab_content', function (Create $table) {
            $table->addColumn('content_id', 'int')->autoIncrement();
            $table->addColumn('tab_id', 'int');
            $table->addColumn('resource_id', 'int');
            // HTML produced by the XF BB-code editor / rich-text input
            $table->addColumn('content', 'mediumtext');
            $table->addColumn('content_html', 'mediumtext');
            $table->addColumn('last_edit_date', 'int')->setDefault(0);
            $table->addColumn('last_edit_user_id', 'int')->setDefault(0);
            $table->addPrimaryKey('content_id');
            $table->addKey(['resource_id', 'tab_id'], 'resource_tab');
        });
    }

    // -------------------------------------------------------------------------
    // Uninstall
    // -------------------------------------------------------------------------

    public function uninstallStep1(): void
    {
        $this->schemaManager()->dropTable('xf_schmitzit_custom_tab_content');
    }

    public function uninstallStep2(): void
    {
        $this->schemaManager()->dropTable('xf_schmitzit_custom_tab');
    }
}
