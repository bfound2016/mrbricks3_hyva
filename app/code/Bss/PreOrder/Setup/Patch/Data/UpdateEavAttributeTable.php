<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\PreOrder\Setup\Patch\Data;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateEavAttributeTable implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @param CollectionFactory $groupCollectionFactory
     */
    public function __construct(
        CollectionFactory $groupCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Update attribute
     */
    public function apply()
    {
        $groupCollection = $this->groupCollectionFactory->create();
        $arrGroups = [];
        foreach ($groupCollection as $group) {
            if (in_array($group->getAttributeGroupName(), $arrGroups)) {
                continue;
            }
            array_push($arrGroups, $group->getAttributeGroupName());
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
