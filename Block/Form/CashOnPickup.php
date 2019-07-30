<?php
/**
 * @package    Falkone_CashOnPickup
 * @author     Falk Ulbricht
 * @copyright  2019 Falkone
 * @license    https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @since      1.0.0
 */

namespace Falkone\CashOnPickup\Block\Form;

use Magento\OfflinePayments\Block\Form\AbstractInstruction;

/**
 * Block for Cash On Pickup payment method form
 *
 * @codeCoverageIgnore Contains no business logic
 */
class CashOnPickup extends AbstractInstruction
{
    /**
     * Cash on Pickup template
     *
     * @var string
     */
    protected $_template = 'Falkone_CashOnPickup::form/cashonpickup.phtml';
}