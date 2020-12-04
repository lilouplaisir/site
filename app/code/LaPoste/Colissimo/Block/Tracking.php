<?php

namespace LaPoste\Colissimo\Block;

class Tracking extends \Magento\Framework\View\Element\Template
{
    protected $unifiedTrackingApi;

    protected $orderRepository;

    protected $remoteAddress;

    protected $colissimoStatus;

    private $order;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \LaPoste\Colissimo\Api\UnifiedTrackingApi $unifiedTrackingApi,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \LaPoste\Colissimo\Api\ColissimoStatus $colissimoStatus
    ) {
        parent::__construct($context);

        $this->unifiedTrackingApi = $unifiedTrackingApi;
        $this->orderRepository = $orderRepository;
        $this->remoteAddress = $remoteAddress;
        $this->colissimoStatus = $colissimoStatus;
    }

    public function getPostCollection()
    {
        return $this->_postFactory
            ->create()
            ->getCollection();
    }


    public function getOrder()
    {
        if (null === $this->order) {
            $rawTrackHash = $this->getRequest()->getParam('trackhash');

            $trackHash = $this->unifiedTrackingApi->decrypt($rawTrackHash);


            $this->order = $this->orderRepository
                ->get($trackHash)
                ;
        }

        return $this->order;
    }

    public function getTracks()
    {
        $result = [];

        foreach ($this->getOrder()->getTracksCollection() as $track) {
            if (\LaPoste\Colissimo\Model\Carrier\Colissimo::CODE === $track->getCarrierCode()) {
                $result[] = $track;
            }
        }

        return $result;
    }

    public function getStatusForTrack(\Magento\Sales\Model\Order\Shipment\Track $track)
    {
        return $this->unifiedTrackingApi->getTrackingInfo(
            $track->getTrackNumber(),
            $this->remoteAddress->getRemoteAddress(),
            null,
            null,
            null,
            $track->getStoreId()
        );
    }

    public function getMainStatus($status)
    {
        try {
            if ($status->parcel->statusDelivery) {
                return __('Delivered');
            }

            $internalCode = $this->colissimoStatus
                ->getInternalCodeForClp($status->parcel->eventLastCode);

            $colissimoStatusInfo = $this->colissimoStatus
                ->getStatusInfo($internalCode);

            if (!empty($colissimoStatusInfo['is_anomaly'])) {
                return __('Anomaly');
            }

            return $status->parcel->eventLastLabel;
        } catch (\Exception $e) {
            return '';
        }
    }
}
