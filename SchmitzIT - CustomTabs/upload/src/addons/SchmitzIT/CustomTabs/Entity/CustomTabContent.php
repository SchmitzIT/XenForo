<?php

namespace SchmitzIT\CustomTabs\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int    $content_id
 * @property int    $tab_id
 * @property int    $resource_id
 * @property string $content
 * @property string $content_html
 * @property int    $last_edit_date
 * @property int    $last_edit_user_id
 *
 * RELATIONS
 * @property-read \SchmitzIT\CustomTabs\Entity\CustomTab $Tab
 * @property-read \XFRM\Entity\ResourceItem $Resource
 * @property-read \XF\Entity\User $LastEditor
 */
class CustomTabContent extends Entity
{
    public static function getStructure(Structure $structure): Structure
    {
        $structure->table      = 'xf_schmitzit_custom_tab_content';
        $structure->shortName  = 'SchmitzIT\CustomTabs:CustomTabContent';
        $structure->primaryKey = 'content_id';

        $structure->columns = [
            'content_id'       => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'tab_id'           => ['type' => self::UINT, 'required' => true],
            'resource_id'      => ['type' => self::UINT, 'required' => true],
            'content'          => ['type' => self::STR, 'default' => ''],
            'content_html'     => ['type' => self::STR, 'default' => ''],
            'last_edit_date'   => ['type' => self::UINT, 'default' => 0],
            'last_edit_user_id'=> ['type' => self::UINT, 'default' => 0],
        ];

        $structure->relations = [
            'Tab' => [
                'entity'     => 'SchmitzIT\CustomTabs:CustomTab',
                'type'       => self::TO_ONE,
                'conditions' => 'tab_id',
                'primary'    => true,
            ],
            'Resource' => [
                'entity'     => 'XFRM:ResourceItem',
                'type'       => self::TO_ONE,
                'conditions' => 'resource_id',
                'primary'    => true,
            ],
            'LastEditor' => [
                'entity'     => 'XF:User',
                'type'       => self::TO_ONE,
                'conditions' => [['user_id', '=', '$last_edit_user_id']],
                'primary'    => true,
            ],
        ];

        return $structure;
    }

    public function isEmpty(): bool
    {
        return trim($this->content) === '';
    }
}
