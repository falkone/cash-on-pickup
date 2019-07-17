/**
 * @package    Falkone_CashOnPickup
 * @author     Falk Ulbricht
 * @copyright  2019 Falkone
 * @license    https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @since      1.0.0
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cash_on_pickup',
                component: 'Falkone_CashOnPickup/js/view/payment/method-renderer/cashonpickup-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
