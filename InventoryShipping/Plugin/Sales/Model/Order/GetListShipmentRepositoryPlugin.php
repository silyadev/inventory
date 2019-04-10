<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Model\Order;

use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Model\Order\ShipmentRepository;

/**
 * Add Source Information to shipments loaded with Magento\Sales\Api\ShipmentRepositoryInterface::getList
 */
class GetListShipmentRepositoryPlugin
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * @var GetSourceCodeByShipmentId
     */
    private $getSourceCodeByShipmentId;

    /**
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     * @param GetSourceCodeByShipmentId $getSourceCodeByShipmentId
     */
    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory,
        GetSourceCodeByShipmentId $getSourceCodeByShipmentId
    ) {
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
        $this->getSourceCodeByShipmentId = $getSourceCodeByShipmentId;
    }

    /**
     * Add Source Information to shipments.
     *
     * @param ShipmentRepository $subject
     * @param \Magento\Sales\Api\Data\ShipmentInterface[] $searchResult
     * @return \Magento\Sales\Api\Data\ShipmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(ShipmentRepository $subject, $searchResult)
    {
        /** @var \Magento\Sales\Api\Data\ShipmentInterface $shipment */
        foreach ($searchResult->getItems() as $shipment) {
            $shipmentExtension = $shipment->getExtensionAttributes();
            if (empty($shipmentExtension)) {
                $shipmentExtension = $this->shipmentExtensionFactory->create();
            }
            $sourceCode = $this->getSourceCodeByShipmentId->execute((int)$shipment->getId());
            $shipmentExtension->setSourceCode($sourceCode);
            $shipment->setExtensionAttributes($shipmentExtension);
        }

        return $searchResult;
    }
}
