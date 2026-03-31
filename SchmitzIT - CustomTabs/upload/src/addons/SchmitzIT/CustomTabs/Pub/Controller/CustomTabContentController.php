<?php

namespace SchmitzIT\CustomTabs\Pub\Controller;

use SchmitzIT\CustomTabs\Entity\CustomTab;
use SchmitzIT\CustomTabs\Entity\CustomTabContent;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Pub\Controller\AbstractController;

class CustomTabContentController extends AbstractController
{
    // ------------------------------------------------------------------
    // Edit form (shown inside an overlay or dedicated page)
    // ------------------------------------------------------------------

    public function actionEdit(ParameterBag $params): AbstractReply
    {
        $tab      = $this->assertTabExists($params->tab_id);
        $resource = $this->assertResourceExists($params->resource_id);

        if (!$tab->canAddContent()) {
            return $this->noPermission();
        }

        $content = $this->getTabRepo()->findContentForResourceAndTab(
            $resource->resource_id,
            $tab->tab_id
        );

        if (!$content) {
            /** @var CustomTabContent $content */
            $content = $this->em()->create('SchmitzIT\CustomTabs:CustomTabContent');
            $content->tab_id      = $tab->tab_id;
            $content->resource_id = $resource->resource_id;
        }

        return $this->view(
            'SchmitzIT\CustomTabs:CustomTabContent\Edit',
            'schmitzit_custom_tab_content_edit',
            [
                'tab'      => $tab,
                'resource' => $resource,
                'content'  => $content,
            ]
        );
    }

    // ------------------------------------------------------------------
    // Save
    // ------------------------------------------------------------------

    public function actionSave(ParameterBag $params): AbstractReply
    {
        $this->assertPostOnly();

        $tab      = $this->assertTabExists($params->tab_id);
        $resource = $this->assertResourceExists($params->resource_id);

        if (!$tab->canAddContent()) {
            return $this->noPermission();
        }

        $content = $this->getTabRepo()->findContentForResourceAndTab(
            $resource->resource_id,
            $tab->tab_id
        );

        if (!$content) {
            /** @var CustomTabContent $content */
            $content = $this->em()->create('SchmitzIT\CustomTabs:CustomTabContent');
            $content->tab_id      = $tab->tab_id;
            $content->resource_id = $resource->resource_id;
        }

        $message = $this->plugin('XF:Editor')->fromInput('content');

        $content->content      = $message;
        $content->content_html = $this->app->bbCode()->render($message, 'html', 'post', null);
        $content->last_edit_date    = \XF::$time;
        $content->last_edit_user_id = \XF::visitor()->user_id;

        $content->save();

        return $this->redirect(
            $this->buildLink('resources', $resource) . '#tab-' . $tab->tab_id
        );
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function actionDelete(ParameterBag $params): AbstractReply
    {
        $tab      = $this->assertTabExists($params->tab_id);
        $resource = $this->assertResourceExists($params->resource_id);

        if (!$tab->canAddContent()) {
            return $this->noPermission();
        }

        $content = $this->getTabRepo()->findContentForResourceAndTab(
            $resource->resource_id,
            $tab->tab_id
        );

        if ($content) {
            $content->delete();
        }

        return $this->redirect($this->buildLink('resources', $resource));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function assertTabExists(int $tabId): CustomTab
    {
        return $this->assertRecordExists('SchmitzIT\CustomTabs:CustomTab', $tabId);
    }

    protected function assertResourceExists(int $resourceId): \XFRM\Entity\ResourceItem
    {
        return $this->assertRecordExists('XFRM:ResourceItem', $resourceId);
    }

    protected function getTabRepo(): \SchmitzIT\CustomTabs\Repository\CustomTabRepository
    {
        return $this->repository('SchmitzIT\CustomTabs:CustomTabRepository');
    }
}
