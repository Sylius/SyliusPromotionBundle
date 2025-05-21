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
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionRuleGroup;
use Sylius\Bundle\PromotionBundle\Validator\PromotionRuleGroupValidator;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PromotionRuleGroupValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private MockObject $contextMock;

    private PromotionRuleGroupValidator $promotionRuleGroupValidator;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->promotionRuleGroupValidator = new PromotionRuleGroupValidator([
            'rule_two' => ['Default' => 'Default', 'rule' => 'rule_two'],
        ]);

        $this->promotionRuleGroupValidator->initialize($this->contextMock);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionRuleGroup(): void
    {
        /** @var Constraint&MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var PromotionRuleInterface&MockObject $promotionRuleMock */
        $promotionRuleMock = $this->createMock(PromotionRuleInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->promotionRuleGroupValidator->validate($promotionRuleMock, $constraintMock);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->promotionRuleGroupValidator->validate(new stdClass(), new PromotionRuleGroup());
    }

    public function testCallsAValidatorWithGroup(): void
    {
        /** @var PromotionRuleInterface&MockObject $promotionRuleMock */
        $promotionRuleMock = $this->createMock(PromotionRuleInterface::class);
        /** @var ValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(ValidatorInterface::class);
        /** @var ContextualValidatorInterface&MockObject $contextualValidatorMock */
        $contextualValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        $promotionRuleMock->expects($this->once())->method('getType')->willReturn('rule_two');
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('inContext')->with($this->contextMock)->willReturn($contextualValidatorMock);
        $contextualValidatorMock->expects($this->once())->method('validate')->with($promotionRuleMock, null, ['Default', 'rule_two'])->willReturn($contextualValidatorMock);
        $this->promotionRuleGroupValidator->validate($promotionRuleMock, new PromotionRuleGroup(['groups' => ['Default', 'test_group']]));
    }

    public function testCallsValidatorWithDefaultGroupsIfNoneProvidedForPromotionActionType(): void
    {
        /** @var PromotionRuleInterface&MockObject $promotionRuleMock */
        $promotionRuleMock = $this->createMock(PromotionRuleInterface::class);
        /** @var ValidatorInterface&MockObject $validatorMock */
        $validatorMock = $this->createMock(ValidatorInterface::class);
        /** @var ContextualValidatorInterface&MockObject $contextualValidatorMock */
        $contextualValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        $promotionRuleMock->expects($this->once())->method('getType')->willReturn('rule_one');
        $this->contextMock->expects($this->once())->method('getValidator')->willReturn($validatorMock);
        $validatorMock->expects($this->once())->method('inContext')->with($this->contextMock)->willReturn($contextualValidatorMock);
        $contextualValidatorMock->expects($this->once())->method('validate')->with($promotionRuleMock, null, ['Default', 'test_group'])->willReturn($contextualValidatorMock);
        $this->promotionRuleGroupValidator->validate($promotionRuleMock, new PromotionRuleGroup(['groups' => ['Default', 'test_group']]));
    }
}
