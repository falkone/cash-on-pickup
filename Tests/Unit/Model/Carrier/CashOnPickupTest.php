<?php
/**
 * @package    Falkone_CashOnPickup
 * @author     Falk Ulbricht
 * @copyright  2019 Falkone
 * @license    https://www.gnu.org/licenses/gpl-3.0.de.html GNU General Public License 3
 * @since      1.0.0
 */

namespace Falkone\CashOnPickup\Tests\Unit\Model\Carrier;

use Falkone\CashOnPickup\Model\Carrier\CashOnPickup;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CashOnPickupTest
 */
class CashOnPickupTest extends TestCase
{
    /**
     * @var CashOnPickup
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var MethodFactory|MockObject
     */
    private $methodFactoryMock;

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * Init
     */
    public function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'isSetFlag', 'getValue'])
            ->getMock();

        $this->errorFactoryMock = $this
            ->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->methodFactoryMock = $this
            ->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->helper = new ObjectManager($this);
        $this->model  = $this->helper->getObject(
            CashOnPickup::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'rateErrorFactory' => $this->errorFactoryMock,
                'logger' => $this->loggerMock,
                'rateResultFactory' => $this->resultFactoryMock,
                'rateMethodFactory' => $this->methodFactoryMock,
            ]
        );
    }

    /**
     * tests return false when carrier is inactive
     */
    public function testCollectRates_CarrierIsDisabled_ReturnFalse()
    {
        $request = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $returnResult = $this->model->collectRates($request);

        $this->assertFalse($returnResult);
    }

    /**
     * tests RateResult when carrier is active with different fee's and rates
     *
     * @dataProvider shippingDataProvider
     */
    public function testCollectRates_TestCalculation($price, $fee, $fee_type, $expectedPrice)
    {
        $request = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(true);
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap(
            [
                ['carriers/cash_on_pickup/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, true],
                ['carriers/cash_on_pickup/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, $price],
                ['carriers/cash_on_pickup/handling_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, $fee_type],
                ['carriers/cash_on_pickup/handling_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, $fee],
            ]
        );
        $method = $this->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setMethod', 'setMethodTitle', 'setPrice', 'setCost'])
            ->getMock();
        $this->methodFactoryMock->expects($this->once())->method('create')->willReturn($method);

        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['append'])
            ->getMock();
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($result);

        $returnPrice = null;
        $method->expects($this->once())->method('setPrice')->with($this->captureArg($returnPrice));

        $returnCost = null;
        $method->expects($this->once())->method('setCost')->with($this->captureArg($returnCost));

        $returnMethod = null;
        $result->expects($this->once())->method('append')->with($this->captureArg($returnMethod));

        $returnResult = $this->model->collectRates($request);

        $this->assertEquals($expectedPrice, $returnPrice);
        $this->assertEquals($expectedPrice, $returnCost);
        $this->assertEquals($method, $returnMethod);
        $this->assertEquals($result, $returnResult);
    }

    /**
     * tests getAllowedMethods method
     */
    public function testGetAllowedMethods()
    {
        $name = 'some_name';
        $expected = [$this->model->getCarrierCode() => $name];

        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturnMap(
            [
                ['carriers/cash_on_pickup/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, $name],
            ]
        );
        $returnResult = $this->model->getAllowedMethods();

        $this->assertEquals($expected, $returnResult);
    }

    /**
     * Captures the argument and saves it in the given variable
     *
     * @param $captureVar
     * @return \PHPUnit\Framework\Constraint\Callback
     */
    private function captureArg(&$captureVar)
    {
        return $this->callback(function ($argToMock) use (&$captureVar) {
            $captureVar = $argToMock;

            return true;
        });
    }


    /**
     * @return array
     */
    public function shippingDataProvider()
    {
        return [
            'test_no_price_no_free' =>
                ['price' => 0, 'fee' => 0, 'fee_type' => 'F', 'expectedPrice' => 0],
            'test_price_no_free' =>
                ['price' => 5, 'fee' => 0, 'fee_type' => 'F', 'expectedPrice' => 5],
            'test_price_and_order_free' =>
                ['price' => 5, 'fee' => 2, 'fee_type' => 'F', 'expectedPrice' => 7],
            'test_price_and_percent_free' =>
                ['price' => 5, 'fee' => 15, 'fee_type' => 'P', 'expectedPrice' => 5.75],
        ];
    }
}
