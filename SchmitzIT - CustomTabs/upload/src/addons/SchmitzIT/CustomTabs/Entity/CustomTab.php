<?php

namespace SchmitzIT\CustomTabs\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int    $tab_id
 * @property int    $display_order
 * @property string $tab_title
 * @property bool   $active
 * @property array  $allowed_group_ids
 *
 * RELATIONS
 * @property-read \XF\Mvc\Entity\AbstractCollection|\SchmitzIT\CustomTabs\Entity\CustomTabContent[] $Contents
 */
class CustomTab extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table      = 'xf_schmitzit_custom_tab';
        $structure->shortName  = 'SchmitzIT\CustomTabs:CustomTab';
        $structure->primaryKey = 'tab_id';

        $structure->columns = [
            'tab_id'           => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'display_order'    => ['type' => self::UINT, 'default' => 10],
            'tab_title'        => ['type' => self::STR, 'maxLength' => 100, 'required' => 'please_enter_valid_title'],
            'active'           => ['type' => self::BOOL, 'default' => true],
            'allowed_group_ids'=> ['type' => self::LIST_COMMA, 'default' => [], 'list' => ['type' => 'posint']],
        ];

        $structure->relations = [
            'Contents' => [
                'entity'     => 'SchmitzIT\CustomTabs:CustomTabContent',
                'type'       => self::TO_MANY,
                'conditions' => 'tab_id',
                'key'        => 'tab_id',
            ],
        ];

        $structure->defaultWith = [];

        return $structure;
    }

    /**
     * Check whether the current visitor is allowed to add/edit content on this tab.
     */
    public function canAddContent(): bool
    {
        $visitor = \XF::visitor();
        if ($visitor->is_admin || $visitor->is_moderator) {
            return true;
        }

        $allowedGroups = $this->allowed_group_ids;
        if (empty($allowedGroups)) {
            return false;
        }

        foreach ($visitor->secondary_group_ids as $gid) {
            if (in_array($gid, $allowedGroups, true)) {
                return true;
            }
        }

        return in_array($visitor->user_group_id, $allowedGroups, true);
    }
}
