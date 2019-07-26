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
use Magento\Payment\Block\Info\Instructions;
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
                $result = ($quoteShippingMethod === $this->getFullCarrierMethodeCode());
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
    private function getFullCarrierMethodeCode() {
        // ToDo: get the carrierCode and methodCode from method instate of using same constance
        $carrierCode = CashOnPickupCarrier::SHIPPING_METHOD_CASHONPICKUP_CODE;
        $methodCode = CashOnPickupCarrier::SHIPPING_METHOD_CASHONPICKUP_CODE;
        return $carrierCode . '_' . $methodCode;
    }
}