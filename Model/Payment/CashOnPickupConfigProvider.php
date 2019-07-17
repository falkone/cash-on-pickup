<?php
/**
 * @package    Falkone_CashOnPickup
 * @author     Falk Ulbricht
 * @copyright  2019 Falkone
 * @license    https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @since      1.0.0
 */

namespace Falkone\CashOnPickup\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class CashOnPickupConfigProvider
 */
class CashOnPickupConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    protected $code = CashOnPickup::PAYMENT_METHOD_CASHONPICKUP_CODE;

    /**
     * @var AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper       $escaper
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;

        $this->methods[$this->code] = $paymentHelper->getMethodInstance($this->code);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];

        if ($this->methods[$this->code]->isAvailable()) {
            $config['payment']['instructions'][$this->code] = $this->getInstructions($this->code);
        }

        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
}