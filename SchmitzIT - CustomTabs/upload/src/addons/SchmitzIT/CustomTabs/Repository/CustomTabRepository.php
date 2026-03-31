<?php

namespace SchmitzIT\CustomTabs\Repository;

use SchmitzIT\CustomTabs\Entity\CustomTab;
use SchmitzIT\CustomTabs\Entity\CustomTabContent;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Repository;

class CustomTabRepository extends Repository
{
    /**
     * Return all active tabs ordered by display_order.
     *
     * @return AbstractCollection|CustomTab[]
     */
    public function findActiveTabs(): AbstractCollection
    {
        return $this->finder('SchmitzIT\CustomTabs:CustomTab')
            ->where('active', 1)
            ->order('display_order')
            ->fetch();
    }

    /**
     * Return all tabs (including inactive) for the ACP list.
     *
     * @return AbstractCollection|CustomTab[]
     */
    public function findAllTabs(): AbstractCollection
    {
        return $this->finder('SchmitzIT\CustomTabs:CustomTab')
            ->order('display_order')
            ->fetch();
    }

    /**
     * Fetch content rows for a given resource, keyed by tab_id.
     *
     * @param int $resourceId
     * @return AbstractCollection|CustomTabContent[]
     */
    public function findContentForResource(int $resourceId): AbstractCollection
    {
        return $this->finder('SchmitzIT\CustomTabs:CustomTabContent')
            ->where('resource_id', $resourceId)
            ->with('Tab')
            ->fetch();
    }

    /**
     * Fetch a single content record for a resource + tab combination.
     */
    public function findContentForResourceAndTab(int $resourceId, int $tabId): ?CustomTabContent
    {
        return $this->finder('SchmitzIT\CustomTabs:CustomTabContent')
            ->where('resource_id', $resourceId)
            ->where('tab_id', $tabId)
            ->fetchOne();
    }

    /**
     * Build a keyed array [tab_id => CustomTabContent] for a resource.
     *
     * @param int $resourceId
     * @return CustomTabContent[]
     */
    public function getContentMapForResource(int $resourceId): array
    {
        $contentCollection = $this->findContentForResource($resourceId);

        $map = [];
        foreach ($contentCollection as $content) {
            $map[$content->tab_id] = $content;
        }
        return $map;
    }
}
