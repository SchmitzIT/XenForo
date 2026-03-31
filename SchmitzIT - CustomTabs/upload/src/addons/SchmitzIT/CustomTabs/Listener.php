<?php

namespace SchmitzIT\CustomTabs;

use XF\Container;
use XF\Mvc\Reply\View;

class Listener
{
    /**
     * Fired on the `xfrm_resource_view` event.
     * Injects custom tabs and their content into the resource view params.
     */
    public static function xfrmResourceView(
        \XFRM\Pub\Controller\Resource $controller,
        \XFRM\Entity\ResourceItem $resource,
        View &$reply
    ): void {
        /** @var \SchmitzIT\CustomTabs\Repository\CustomTabRepository $repo */
        $repo = \XF::repository('SchmitzIT\CustomTabs:CustomTabRepository');

        $activeTabs  = $repo->findActiveTabs();
        $contentMap  = $repo->getContentMapForResource($resource->resource_id);

        // Filter: only include tabs that have content OR where the visitor can add content.
        // Tabs with neither are hidden entirely.
        $visibleTabs = [];
        foreach ($activeTabs as $tab) {
            $hasContent  = isset($contentMap[$tab->tab_id]) && !$contentMap[$tab->tab_id]->isEmpty();
            $canAddContent = $tab->canAddContent();

            if ($hasContent || $canAddContent) {
                $visibleTabs[$tab->tab_id] = $tab;
            }
        }

        $reply->setParam('schmitzit_custom_tabs',       $visibleTabs);
        $reply->setParam('schmitzit_custom_tab_content', $contentMap);
    }
}
