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

namespace Tests\Sylius\Bundle\PromotionBundle\Criteria;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PromotionBundle\Criteria\CriteriaInterface;
use Sylius\Bundle\PromotionBundle\Criteria\Enabled;
use Sylius\Component\Promotion\Model\CatalogPromotionInterface;

final class EnabledTest extends TestCase
{
    private Enabled $enabled;

    protected function setUp(): void
    {
        $this->enabled = new Enabled();
    }

    public function testImplementsCriteriaInterface(): void
    {
        $this->assertInstanceOf(CriteriaInterface::class, $this->enabled);
    }

    public function testAddsFiltersToQueryBuilder(): void
    {
        /** @var QueryBuilder&MockObject $queryBuilderMock */
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['catalog_promotion']);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('catalog_promotion.enabled = :enabled')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('enabled', true)->willReturn($queryBuilderMock);
        $this->assertSame($queryBuilderMock, $this->enabled->filterQueryBuilder($queryBuilderMock));
    }

    public function testVerifiesCatalogPromotion(): void
    {
        /** @var CatalogPromotionInterface&MockObject $catalogPromotionMock */
        $catalogPromotionMock = $this->createMock(CatalogPromotionInterface::class);
        $catalogPromotionMock->expects($this->once())->method('isEnabled')->willReturn(true, false);
        $this->assertTrue($this->enabled->verify($catalogPromotionMock));
        $this->assertFalse($this->enabled->verify($catalogPromotionMock));
    }
}
