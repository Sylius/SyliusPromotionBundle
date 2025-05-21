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

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PromotionBundle\Criteria\CriteriaInterface;
use Sylius\Bundle\PromotionBundle\Criteria\DateRange;
use Sylius\Component\Promotion\Model\CatalogPromotionInterface;
use Symfony\Component\Clock\ClockInterface;

final class DateRangeTest extends TestCase
{
    /** @var ClockInterface&MockObject */
    private MockObject $clockMock;

    private DateRange $dateRange;

    protected function setUp(): void
    {
        $this->clockMock = $this->createMock(ClockInterface::class);
        $this->dateRange = new DateRange($this->clockMock);
    }

    public function testImplementsCriteriaInterface(): void
    {
        $this->assertInstanceOf(CriteriaInterface::class, $this->dateRange);
    }

    public function testAddsFiltersToQueryBuilder(): void
    {
        /** @var QueryBuilder&MockObject $queryBuilderMock */
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);
        $now = new DateTimeImmutable();
        $this->clockMock->expects($this->once())->method('now')->willReturn($now);
        $queryBuilderMock->expects($this->exactly(2))->method('andWhere')->willReturnMap([['o.startDate IS NULL OR o.startDate <= :date', $queryBuilderMock], ['o.endDate IS NULL OR o.endDate > :date', $queryBuilderMock]]);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('date', $now)->willReturn($queryBuilderMock);
        $this->assertSame($queryBuilderMock, $this->dateRange->filterQueryBuilder($queryBuilderMock));
    }

    public function testVerifiesCatalogPromotion(): void
    {
        /** @var CatalogPromotionInterface&MockObject $catalogPromotionMock */
        $catalogPromotionMock = $this->createMock(CatalogPromotionInterface::class);
        $tomorrow = new DateTimeImmutable('+1day');
        $yesterday = new DateTimeImmutable('-1day');
        $now = new DateTimeImmutable();
        $this->clockMock->expects($this->once())->method('now')->willReturn($now);
        $catalogPromotionMock->expects($this->exactly(3))->method('getStartDate')->willReturnMap([[$yesterday], [null], [$tomorrow]]);
        $catalogPromotionMock->expects($this->exactly(3))->method('getEndDate')->willReturnMap([[$tomorrow], [null], [$yesterday]]);
        $catalogPromotionMock->expects($this->once())->method('getStartDate')->willReturn($tomorrow);
        $catalogPromotionMock->expects($this->once())->method('getEndDate')->willReturn($yesterday);
        $this->assertFalse($this->dateRange->verify($catalogPromotionMock));
    }
}
