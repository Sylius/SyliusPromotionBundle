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

namespace Tests\Sylius\Bundle\PromotionBundle\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\PromotionBundle\Validator\CatalogPromotionScopeTypeValidator;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CatalogPromotionScopeType;
use Sylius\Component\Promotion\Model\CatalogPromotionScopeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class CatalogPromotionScopeTypeValidatorTest extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    private MockObject $contextMock;
    private CatalogPromotionScopeTypeValidator $catalogPromotionScopeTypeValidator;
    private const SCOPE_TYPES = [
        'test',
        'another_test',
    ];

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionScopeTypeValidator = new CatalogPromotionScopeTypeValidator(self::SCOPE_TYPES);
        $this->initialize($this->contextMock);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionScopeType(): void
    {
        /** @var Constraint|MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var CatalogPromotionScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionScopeTypeValidator->validate($scopeMock, $constraintMock);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionScope(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionScopeTypeValidator->validate(new stdClass(), new CatalogPromotionScopeType());
    }

    public function testDoesNothingWhenPassedScopeHasNullAsType(): void
    {
        /** @var CatalogPromotionScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $scopeMock->expects($this->once())->method('getType')->willReturn(null);
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionScopeTypeValidator->validate($scopeMock, new CatalogPromotionScopeType());
    }

    public function testDoesNothingWhenPassedScopeHasAnEmptyStringAsType(): void
    {
        /** @var CatalogPromotionScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $scopeMock->expects($this->once())->method('getType')->willReturn('');
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionScopeTypeValidator->validate($scopeMock, new CatalogPromotionScopeType());
    }

    public function testDoesNothingWhenCatalogPromotionScopeHasValidType(): void
    {
        /** @var CatalogPromotionScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $scopeMock->expects($this->once())->method('getType')->willReturn('test');
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionScopeTypeValidator->validate($scopeMock, new CatalogPromotionScopeType());
    }

    public function testAddsViolationWhenTypeIsUnknown(): void
    {
        /** @var ConstraintViolationBuilderInterface|MockObject $violationBuilderMock */
        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var CatalogPromotionScopeInterface|MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $constraint = new CatalogPromotionScopeType();
        $scopeMock->expects($this->once())->method('getType')->willReturn('not_existing_type');
        $this->contextMock->expects($this->once())->method('buildViolation')->with($constraint->invalidType)->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('setParameter')->with('{{ available_scope_types }}', implode(', ', self::SCOPE_TYPES))
            ->willReturn($violationBuilderMock)
        ;
        $violationBuilderMock->expects($this->once())->method('atPath')->with('type')->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('addViolation');
        $this->catalogPromotionScopeTypeValidator->validate($scopeMock, $constraint);
    }
}
