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
use Sylius\Bundle\PromotionBundle\Validator\CatalogPromotionActionGroupValidator;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CatalogPromotionActionGroup;
use Sylius\Component\Promotion\Model\CatalogPromotionActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CatalogPromotionActionGroupValidatorTest extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    private MockObject $contextMock;
    private CatalogPromotionActionGroupValidator $catalogPromotionActionGroupValidator;
    private const VALIDATION_GROUPS = [
        'test' => [
            'test_group',
        ],
        'another_test' => [
            'another_test_group',
        ],
    ];

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionActionGroupValidator = new CatalogPromotionActionGroupValidator(self::VALIDATION_GROUPS);
        $this->initialize($this->contextMock);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionActionGroup(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        /** @var Constraint|MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionActionGroupValidator->validate($actionMock, $constraintMock);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionAction(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionActionGroupValidator->validate(new stdClass(), new CatalogPromotionActionGroup());
    }

    public function testDoesNothingWhenTypeIsNull(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn(null);
        $this->contextMock->expects($this->never())->method('getValidator');
        $this->catalogPromotionActionGroupValidator->validate($actionMock, new CatalogPromotionActionGroup());
    }

    public function testDoesNothingWhenTypeIsAnEmptyString(): void
    {
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn('');
        $this->contextMock->expects($this->never())->method('getValidator');
        $this->catalogPromotionActionGroupValidator->validate($actionMock, new CatalogPromotionActionGroup());
    }

    public function testPassesConfiguredValidationGroupsForFurtherValidation(): void
    {
        /** @var ValidatorInterface|MockObject $validatorMock */
        $validatorMock = $this->createMock(ValidatorInterface::class);
        /** @var ContextualValidatorInterface|MockObject $contextualValidatorMock */
        $contextualValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        /** @var ConstraintViolationListInterface|MockObject $violationListMock */
        $violationListMock = $this->createMock(ConstraintViolationListInterface::class);
        /** @var CatalogPromotionActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $constraint = new CatalogPromotionActionGroup();
        $actionMock->expects($this->once())->method('getType')->willReturn('test');
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('inContext')->with($this->contextMock)->willReturn($contextualValidatorMock);
        $contextualValidatorMock->expects($this->once())->method('validate')->with($actionMock, null, ['test_group'])
            ->willReturn($contextualValidatorMock)
        ;
        $this->contextMock->expects($this->once())->method('getViolations')->willReturn($violationListMock);
        $violationListMock->expects($this->once())->method('count')->willReturn(1);
        $this->catalogPromotionActionGroupValidator->validate($actionMock, $constraint);
    }
}
