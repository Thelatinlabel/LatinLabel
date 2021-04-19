<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\FooterLink;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Class FooterLinkTest
 * Test for \Aheadworks\Rma\Block\FooterLink
 *
 * @package Aheadworks\Rma\Test\Unit\Block
 */
class FooterLinkTest extends TestCase
{
    /**
     * @var FooterLink
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var HttpContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
    }

    /**
     * Test addLink method, customer logged in
     */
    public function testAddLinkCustomerLoggedIn()
    {
        $this->httpContextMock->expects($this->once())
            ->method('getValue')
            ->with(CustomerContext::CONTEXT_AUTH)
            ->willReturn(true);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            FooterLink::class,
            [
                'config' => $this->configMock,
                'httpContext' => $this->httpContextMock
            ]
        );
    }

    /**
     * Test addLink method, customer not logged in
     *
     * @param bool $isAllowGuestsCreateRequest
     * @dataProvider boolDataProvider
     */
    public function testAddLinkCustomerNotLoggedIn($isAllowGuestsCreateRequest)
    {
        $this->configMock->expects($this->once())
            ->method('isAllowGuestsCreateRequest')
            ->willReturn($isAllowGuestsCreateRequest);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            FooterLink::class,
            [
                'config' => $this->configMock,
                'httpContext' => $this->httpContextMock
            ]
        );
    }

    /**
     * Bool data provider
     *
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
