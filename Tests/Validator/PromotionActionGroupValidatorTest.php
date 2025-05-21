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
    private MockObject $contextMock;

    private PromotionActionGroupValidator $promotionActionGroupValidator;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->promotionActionGroupValidator = new PromotionActionGroupValidator([
            'action_two' => ['Default' => 'Default', 'type' => 'action_two'],
        ]);

        $this->promotionActionGroupValidator->initialize($this->contextMock);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionActionGroup(): void
    {
        /** @var Constraint&MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var PromotionActionInterface&MockObject $promotionActionMock */
        $promotionActionMock = $this->createMock(PromotionActionInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->promotionActionGroupValidator->validate($promotionActionMock, $constraintMock);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfPromotionAction(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->promotionActionGroupValidator->validate(new stdClass(), new PromotionActionGroup());
    }

    public function testCallsAValidatorWithGroup(): void
    {
        /** @var PromotionActionInterface&MockObject $promotionActionMock */
        $promotionActionMock = $this->createMock(PromotionActionInterface::class);
        /** @var ValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(ValidatorInterface::class);
        /** @var ContextualValidatorInterface&MockObject $contextualValidatorMock */
        $contextualValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        $promotionActionMock->expects($this->once())->method('getType')->willReturn('action_two');
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('inContext')->with($this->contextMock)->willReturn($contextualValidatorMock);
        $contextualValidatorMock->expects($this->once())->method('validate')->with($promotionActionMock, null, ['Default', 'action_two'])->willReturn($contextualValidatorMock);
        $this->promotionActionGroupValidator->validate($promotionActionMock, new PromotionActionGroup(['groups' => ['Default', 'test_group']]));
    }

    public function testCallsValidatorWithDefaultGroupsIfNoneProvidedForPromotionActionType(): void
    {
        /** @var PromotionActionInterface&MockObject $promotionActionMock */
        $promotionActionMock = $this->createMock(PromotionActionInterface::class);
        /** @var ValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(ValidatorInterface::class);
        /** @var ContextualValidatorInterface&MockObject $contextualValidatorMock */
        $contextualValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        $promotionActionMock->expects($this->once())->method('getType')->willReturn('action_one');
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('inContext')->with($this->contextMock)->willReturn($contextualValidatorMock);
        $contextualValidatorMock->expects($this->once())->method('validate')->with($promotionActionMock, null, ['Default', 'test_group'])->willReturn($contextualValidatorMock);
        $this->promotionActionGroupValidator->validate($promotionActionMock, new PromotionActionGroup(['groups' => ['Default', 'test_group']]));
    }
}
