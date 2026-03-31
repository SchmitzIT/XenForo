<?php

namespace SchmitzIT\CustomTabs\Admin\Controller;

use SchmitzIT\CustomTabs\Entity\CustomTab;
use XF\Admin\Controller\AbstractController;
use XF\ControllerPlugin\DeletePlugin;
use XF\ControllerPlugin\TogglePlugin;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class CustomTabController extends AbstractController
{
    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    public function actionIndex(): AbstractReply
    {
        $tabs = $this->getTabRepo()->findAllTabs();

        return $this->view(
            'SchmitzIT\CustomTabs:CustomTab\Listing',
            'schmitzit_custom_tab_list',
            ['tabs' => $tabs]
        );
    }

    // ------------------------------------------------------------------
    // Create / Edit
    // ------------------------------------------------------------------

    protected function tabAddEdit(CustomTab $tab): AbstractReply
    {
        $groupRepo = $this->repository('XF:UserGroup');
        $userGroups = $groupRepo->findUserGroupsForList()->fetch();

        return $this->view(
            'SchmitzIT\CustomTabs:CustomTab\Edit',
            'schmitzit_custom_tab_edit',
            [
                'tab'        => $tab,
                'userGroups' => $userGroups,
            ]
        );
    }

    public function actionAdd(): AbstractReply
    {
        /** @var CustomTab $tab */
        $tab = $this->em()->create('SchmitzIT\CustomTabs:CustomTab');
        return $this->tabAddEdit($tab);
    }

    public function actionEdit(ParameterBag $params): AbstractReply
    {
        $tab = $this->assertTabExists($params->tab_id);
        return $this->tabAddEdit($tab);
    }

    // ------------------------------------------------------------------
    // Save
    // ------------------------------------------------------------------

    protected function tabSaveProcess(CustomTab $tab): FormAction
    {
        $form = $this->formAction();

        $input = $this->filter([
            'tab_title'         => 'str',
            'display_order'     => 'uint',
            'active'            => 'bool',
            'allowed_group_ids' => 'array-uint',
        ]);

        $form->basicEntitySave($tab, $input);

        return $form;
    }

    public function actionSave(ParameterBag $params): AbstractReply
    {
        $this->assertPostOnly();

        if ($params->tab_id) {
            $tab = $this->assertTabExists($params->tab_id);
        } else {
            /** @var CustomTab $tab */
            $tab = $this->em()->create('SchmitzIT\CustomTabs:CustomTab');
        }

        $this->tabSaveProcess($tab)->run();

        return $this->redirect($this->buildLink('custom-tabs'));
    }

    // ------------------------------------------------------------------
    // Toggle active state
    // ------------------------------------------------------------------

    public function actionToggle(): AbstractReply
    {
        /** @var TogglePlugin $plugin */
        $plugin = $this->plugin('XF:Toggle');
        return $plugin->actionToggle('SchmitzIT\CustomTabs:CustomTab');
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function actionDelete(ParameterBag $params): AbstractReply
    {
        $tab = $this->assertTabExists($params->tab_id);

        /** @var DeletePlugin $plugin */
        $plugin = $this->plugin('XF:Delete');
        return $plugin->actionDelete(
            $tab,
            $this->buildLink('custom-tabs/delete', $tab),
            $this->buildLink('custom-tabs/edit', $tab),
            $this->buildLink('custom-tabs'),
            $tab->tab_title
        );
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function assertTabExists(int $tabId): CustomTab
    {
        return $this->assertRecordExists('SchmitzIT\CustomTabs:CustomTab', $tabId);
    }

    protected function getTabRepo(): \SchmitzIT\CustomTabs\Repository\CustomTabRepository
    {
        return $this->repository('SchmitzIT\CustomTabs:CustomTabRepository');
    }
}
