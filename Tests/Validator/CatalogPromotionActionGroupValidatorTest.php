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
    private ExecutionContextInterface $context;

    private CatalogPromotionActionGroupValidator $catalogPromotionActionGroupValidator;

    /** @var CatalogPromotionActionInterface&MockObject */
    private CatalogPromotionActionInterface $action;

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
        parent::setUp();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionActionGroupValidator = new CatalogPromotionActionGroupValidator(
            self::VALIDATION_GROUPS,
        );
        $this->catalogPromotionActionGroupValidator->initialize($this->context);
        $this->action = $this->createMock(CatalogPromotionActionInterface::class);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionActionGroup(): void
    {
        /** @var Constraint&MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);

        self::expectException(UnexpectedTypeException::class);

        $this->catalogPromotionActionGroupValidator->validate($this->action, $constraintMock);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionAction(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->catalogPromotionActionGroupValidator->validate(new \stdClass(), new CatalogPromotionActionGroup());
    }

    public function testDoesNothingWhenTypeIsNull(): void
    {
        $this->action->expects(self::once())->method('getType')->willReturn(null);

        $this->context->expects(self::never())->method('getValidator');

        $this->catalogPromotionActionGroupValidator->validate($this->action, new CatalogPromotionActionGroup());
    }

    public function testDoesNothingWhenTypeIsAnEmptyString(): void
    {
        $this->action->expects(self::once())->method('getType')->willReturn('');

        $this->context->expects(self::never())->method('getValidator');

        $this->catalogPromotionActionGroupValidator->validate($this->action, new CatalogPromotionActionGroup());
    }

    public function testPassesConfiguredValidationGroupsForFurtherValidation(): void
    {
        $constraint = new CatalogPromotionActionGroup();

        $this->action->method('getType')->willReturn('test');

        /** @var ValidatorInterface&MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);

        /** @var ContextualValidatorInterface&MockObject $contextualValidator */
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->context->expects(self::once())
            ->method('getValidator')
            ->willReturn($validator);

        $validator->expects(self::once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidator);

        $contextualValidator->expects(self::once())
            ->method('validate')
            ->with($this->action, null, ['group1' => 'test_group'])
            ->willReturn($contextualValidator);

        $this->catalogPromotionActionGroupValidator->validate($this->action, $constraint);
    }
}
