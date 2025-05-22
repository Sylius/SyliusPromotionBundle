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
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionActionGroup;
use Sylius\Bundle\PromotionBundle\Validator\PromotionActionGroupValidator;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PromotionActionGroupValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private MockObject $context;

    private PromotionActionGroupValidator $promotionActionGroupValidator;

    /** @var PromotionActionInterface&MockObject */
    private PromotionActionInterface $promotionAction;

    /** @var ValidatorInterface&MockObject */
    private ValidatorInterface $validator;

    /** @var ContextualValidatorInterface&MockObject */
    private ContextualValidatorInterface $contextualValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->promotionActionGroupValidator = new PromotionActionGroupValidator([
            'action_two' => ['Default' => 'Default', 'type' => 'action_two'],
        ]);
        $this->promotionActionGroupValidator->initialize($this->context);
        $this->promotionAction = $this->createMock(PromotionActionInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionActionGroup(): void
    {
        /** @var Constraint&MockObject $constraint */
        $constraint = $this->createMock(Constraint::class);

        self::expectException(UnexpectedTypeException::class);

        $this->promotionActionGroupValidator->validate($this->promotionAction, $constraint);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfPromotionAction(): void
    {
        self::expectException(UnexpectedValueException::class);

        $this->promotionActionGroupValidator->validate(new \stdClass(), new PromotionActionGroup());
    }

    public function testCallsAValidatorWithGroup(): void
    {
        $this->promotionAction->expects(self::once())->method('getType')->willReturn('action_two');

        $this->context->expects(self::once())->method('getValidator')->willReturn($this->validator);

        $this->validator->expects(self::once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($this->contextualValidator);

        $this->contextualValidator->expects(self::once())
            ->method('validate')
            ->with(
                $this->promotionAction,
                null,
                ['Default' => 'Default', 'type' => 'action_two'],
            )->willReturn($this->contextualValidator);

        $constraint = new PromotionActionGroup(['groups' => ['Default', 'test_group']]);

        $this->promotionActionGroupValidator->validate($this->promotionAction, $constraint);
    }

    public function testCallsValidatorWithDefaultGroupsIfNoneProvidedForPromotionActionType(): void
    {
        $this->promotionAction->expects(self::once())->method('getType')->willReturn('action_one');

        $this->context->expects(self::once())->method('getValidator')->willReturn($this->validator);

        $this->validator->expects(self::once())
            ->method('inContext')
            ->with($this->context)
            ->willReturn($this->contextualValidator);

        $this->contextualValidator->expects(self::once())
            ->method('validate')
            ->with($this->promotionAction, null, ['Default', 'test_group'])
            ->willReturn($this->contextualValidator);

        $this->promotionActionGroupValidator->validate(
            $this->promotionAction,
            new PromotionActionGroup(['groups' => ['Default', 'test_group']]),
        );
    }
}
