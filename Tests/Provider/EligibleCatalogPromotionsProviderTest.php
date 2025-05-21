<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Bundle\PromotionBundle\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PromotionBundle\Criteria\CriteriaInterface;
use Sylius\Bundle\PromotionBundle\Provider\EligibleCatalogPromotionsProvider;
use Sylius\Bundle\PromotionBundle\Provider\EligibleCatalogPromotionsProviderInterface;
use Sylius\Component\Promotion\Model\CatalogPromotionInterface;
use Sylius\Component\Promotion\Repository\CatalogPromotionRepositoryInterface;

final class EligibleCatalogPromotionsProviderTest extends TestCase
{
    /** @var CatalogPromotionRepositoryInterface&MockObject */
    private MockObject $catalogPromotionRepositoryMock;

    /** @var CriteriaInterface&MockObject */
    private MockObject $firstCriterionMock;

    /** @var CriteriaInterface&MockObject */
    private MockObject $secondCriterionMock;

    private EligibleCatalogPromotionsProvider $eligibleCatalogPromotionsProvider;

    protected function setUp(): void
    {
        $this->catalogPromotionRepositoryMock = $this->createMock(CatalogPromotionRepositoryInterface::class);
        $this->firstCriterionMock = $this->createMock(CriteriaInterface::class);
        $this->secondCriterionMock = $this->createMock(CriteriaInterface::class);
        $this->eligibleCatalogPromotionsProvider = new EligibleCatalogPromotionsProvider($this->catalogPromotionRepositoryMock, [$this->firstCriterionMock, $this->secondCriterionMock]);
    }

    public function testImplementsEligibleCatalogPromotionsProviderInterface(): void
    {
        $this->assertInstanceOf(EligibleCatalogPromotionsProviderInterface::class, $this->eligibleCatalogPromotionsProvider);
    }

    public function testProvidesCatalogPromotionsBasedOnCriteria(): void
    {
        /** @var CatalogPromotionInterface&MockObject $firstCatalogPromotionMock */
        $firstCatalogPromotionMock = $this->createMock(CatalogPromotionInterface::class);
        /** @var CatalogPromotionInterface&MockObject $secondCatalogPromotionMock */
        $secondCatalogPromotionMock = $this->createMock(CatalogPromotionInterface::class);
        $this->catalogPromotionRepositoryMock->expects($this->once())->method('findByCriteria')->with([$this->firstCriterionMock, $this->secondCriterionMock])
            ->willReturn([$firstCatalogPromotionMock, $secondCatalogPromotionMock])
        ;
        $this->assertSame([$firstCatalogPromotionMock, $secondCatalogPromotionMock], $this->eligibleCatalogPromotionsProvider
            ->provide())
        ;
    }
}
