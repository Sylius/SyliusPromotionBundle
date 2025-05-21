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
    private MockObject $clock;

    private DateRange $dateRange;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = $this->createMock(ClockInterface::class);
        $this->dateRange = new DateRange($this->clock);
    }

    public function testImplementsCriteriaInterface(): void
    {
        self::assertInstanceOf(CriteriaInterface::class, $this->dateRange);
    }

    public function testAddsFiltersToQueryBuilder(): void
    {
        /** @var QueryBuilder&MockObject $queryBuilder */
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder->expects(self::once())->method('getRootAliases')->willReturn(['o']);

        $now = new DateTimeImmutable();

        $this->clock->expects(self::once())->method('now')->willReturn($now);

        $queryBuilder->expects(self::exactly(2))
            ->method('andWhere')
            ->willReturnMap(
                [
                    [
                        'o.startDate IS NULL OR o.startDate <= :date',
                        $queryBuilder,
                    ],
                    [
                        'o.endDate IS NULL OR o.endDate > :date',
                        $queryBuilder,
                    ],
                ],
            );

        $queryBuilder->expects(self::once())
            ->method('setParameter')
            ->with('date', $now)
            ->willReturn($queryBuilder);

        self::assertSame($queryBuilder, $this->dateRange->filterQueryBuilder($queryBuilder));
    }

    public function testVerifiesCatalogPromotion(): void
    {
        /** @var CatalogPromotionInterface&MockObject $catalogPromotion */
        $catalogPromotion = $this->createMock(CatalogPromotionInterface::class);

        $tomorrow = new DateTimeImmutable('+1day');
        $yesterday = new DateTimeImmutable('-1day');
        $now = new DateTimeImmutable();

        $this->clock->expects(self::once())->method('now')->willReturn($now);

        $catalogPromotion->method('getStartDate')
            ->willReturnOnConsecutiveCalls($yesterday, null, $tomorrow);

        $catalogPromotion->method('getEndDate')
            ->willReturnOnConsecutiveCalls($tomorrow, null, $yesterday);

        self::assertFalse($this->dateRange->verify($catalogPromotion));
    }
}
