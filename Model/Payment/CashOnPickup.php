<?php
/**
 * @package    Falkone_CashOnPickup
 * @author     Falk Ulbricht
 * @copyright  2019 Falkone
 * @license    https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @since      1.0.0
 */

namespace Falkone\CashOnPickup\Model\Payment;

use Falkone\CashOnPickup\Block\Form\CashOnPickup as CashOnPickupBlock;
use Falkone\CashOnPickup\Model\Carrier\CashOnPickup as CashOnPickupCarrier;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote\Address;

/**
 * Class CashOnPickup
 * ToDo: extend from \Magento\Payment\Model\Method\Adapter instate of deprecated AbstractMethod
 */
class CashOnPickup extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CASHONPICKUP_CODE = 'cash_on_pickup';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CASHONPICKUP_CODE;

    /**
     * Cash On Pickup payment block paths
     *
     * @var string
     */
    protected $_formBlockType = CashOnPickupBlock::class;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var CashOnPickupCarrier
     */
    private $cashOnPickupShippingMethod;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                            $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param Logger                                                  $logger
     * @param CashOnPickupCarrier                                     $cashOnPickupShippingMethod
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     * @param DirectoryHelper                                         $directory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Falkone\CashOnPickup\Model\Carrier\CashOnPickup $cashOnPickupShippingMethod,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->cashOnPickupShippingMethod = $cashOnPickupShippingMethod;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $result = false;
        if ($quote !== null) {
            /** @var Address $quoteShippingAddress */
            $quoteShippingAddress = $quote->getShippingAddress();
            if ($quoteShippingAddress) {
                $quoteShippingMethod = $quoteShippingAddress->getShippingMethod();
                $result              = ($quoteShippingMethod === $this->getFullCarrierMethodeCode());
            }
        }

        return $result && parent::isAvailable($quote);
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * build the full shipping method name necessary for compare
     *
     * @return string
     */
    private function getFullCarrierMethodeCode()
    {
        $carrierCode = $this->cashOnPickupShippingMethod->getCarrierCode();
        $methodCode  = $this->cashOnPickupShippingMethod->getId();
        return $carrierCode . '_' . $methodCode;
    }
}