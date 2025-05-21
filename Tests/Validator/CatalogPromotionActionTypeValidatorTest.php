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
use Sylius\Bundle\PromotionBundle\Validator\CatalogPromotionActionTypeValidator;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CatalogPromotionActionType;
use Sylius\Component\Promotion\Model\CatalogPromotionActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class CatalogPromotionActionTypeValidatorTest extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    private MockObject $contextMock;
    private CatalogPromotionActionTypeValidator $catalogPromotionActionTypeValidator;
    private const ACTION_TYPES = [
        'test',
        'another_test',
    ];

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionActionTypeValidator = new CatalogPromotionActionTypeValidator(self::ACTION_TYPES);
        $this->initialize($this->contextMock);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionActionType(): void
    {
        /** @var Constraint|MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionActionTypeValidator->validate($actionMock, $constraintMock);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionAction(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionActionTypeValidator->validate(new stdClass(), new CatalogPromotionActionType());
    }

    public function testDoesNothingWhenPassedActionHasNullAsType(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn(null);
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionActionTypeValidator->validate($actionMock, new CatalogPromotionActionType());
    }

    public function testDoesNothingWhenPassedActionHasAnEmptyStringAsType(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn('');
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionActionTypeValidator->validate($actionMock, new CatalogPromotionActionType());
    }

    public function testDoesNothingWhenCatalogPromotionActionHasValidType(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn('test');
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->catalogPromotionActionTypeValidator->validate($actionMock, new CatalogPromotionActionType());
    }

    public function testAddsViolationWhenTypeIsUnknown(): void
    {
        /** @var ConstraintViolationBuilderInterface|MockObject $violationBuilderMock */
        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $constraint = new CatalogPromotionActionType();
        $actionMock->expects($this->once())->method('getType')->willReturn('not_existing_type');
        $this->contextMock->expects($this->once())->method('buildViolation')->with($constraint->invalidType)->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('setParameter')->with('{{ available_action_types }}', implode(', ', self::ACTION_TYPES))
            ->willReturn($violationBuilderMock)
        ;
        $violationBuilderMock->expects($this->once())->method('atPath')->with('type')->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('addViolation');
        $this->catalogPromotionActionTypeValidator->validate($actionMock, $constraint);
    }
}
