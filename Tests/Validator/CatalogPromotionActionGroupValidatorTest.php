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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\CatalogPromotionActionGroupValidator;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CatalogPromotionActionGroup;
use Sylius\Component\Promotion\Model\CatalogPromotionActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CatalogPromotionActionGroupValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private MockObject $context;

    private CatalogPromotionActionGroupValidator $catalogPromotionActionGroupValidator;

    private const VALIDATION_GROUPS = [
        'test' => [
            'group1' => 'test_group',
        ],
        'another_test' => [
            'group1' => 'another_test_group',
        ],
    ];

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionActionGroupValidator = new CatalogPromotionActionGroupValidator(self::VALIDATION_GROUPS);
        $this->catalogPromotionActionGroupValidator->initialize($this->context);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionActionGroup(): void
    {
        /** @var CatalogPromotionActionInterface&MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        /** @var Constraint&MockObject $constraintMock */
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
        /** @var CatalogPromotionActionInterface&MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn(null);
        $this->context->expects($this->never())->method('getValidator');
        $this->catalogPromotionActionGroupValidator->validate($actionMock, new CatalogPromotionActionGroup());
    }

    public function testDoesNothingWhenTypeIsAnEmptyString(): void
    {
        /** @var CatalogPromotionActionInterface&MockObject $actionMock */
        $actionMock = $this->createMock(CatalogPromotionActionInterface::class);
        $actionMock->expects($this->once())->method('getType')->willReturn('');
        $this->context->expects($this->never())->method('getValidator');
        $this->catalogPromotionActionGroupValidator->validate($actionMock, new CatalogPromotionActionGroup());
    }

    public function testPassesConfiguredValidationGroupsForFurtherValidation(): void
    {
        $constraint = new CatalogPromotionActionGroup();

        /** @var CatalogPromotionActionInterface&MockObject $action */
        $action = $this->createMock(CatalogPromotionActionInterface::class);

        $action->method('getType')->willReturn('test');

        /** @var ValidatorInterface&MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);

        /** @var ContextualValidatorInterface&MockObject $contextualValidator */
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->context->expects($this->once())
            ->method('getValidator')
            ->willReturn($validator);

        $validator->expects($this->once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->once())
            ->method('validate')
            ->with($action, null, ['group1' => 'test_group'])
            ->willReturn($contextualValidator);

        $this->catalogPromotionActionGroupValidator->validate($action, $constraint);
    }
}
