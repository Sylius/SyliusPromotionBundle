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
use Sylius\Bundle\PromotionBundle\Validator\PromotionActionTypeValidator;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionActionType;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class PromotionActionTypeValidatorTest extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    private MockObject $contextMock;
    private PromotionActionTypeValidator $promotionActionTypeValidator;
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->promotionActionTypeValidator = new PromotionActionTypeValidator(['action_one' => 'action_one', 'action_two' => 'action_two']);
        $this->initialize($this->contextMock);
    }

    public function testThrowsAnExceptionIfConstraintIsNotAnInstanceOfPromotionActionType(): void
    {
        /** @var Constraint|MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        /** @var PromotionActionInterface|MockObject $promotionActionMock */
        $promotionActionMock = $this->createMock(PromotionActionInterface::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->promotionActionTypeValidator->validate($promotionActionMock, $constraintMock);
    }

    public function testThrowsAnExceptionIfValueIsNotAnInstanceOfPromotionAction(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->promotionActionTypeValidator->validate(new stdClass(), new PromotionActionType());
    }

    public function testAddsViolationIfPromotionActionHasInvalidType(): void
    {
        /** @var ConstraintViolationBuilderInterface|MockObject $constraintViolationBuilderMock */
        $constraintViolationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var PromotionActionInterface|MockObject $promotionActionMock */
        $promotionActionMock = $this->createMock(PromotionActionInterface::class);
        $promotionActionMock->expects($this->once())->method('getType')->willReturn('wrong_type');
        $this->contextMock->expects($this->once())->method('buildViolation')->with('sylius.promotion_action.invalid_type')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('setParameter')->with('{{ available_action_types }}', 'action_one, action_two')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('atPath')->with('type')->willReturn($constraintViolationBuilderMock);
        $constraintViolationBuilderMock->expects($this->once())->method('addViolation');
        $this->promotionActionTypeValidator->validate($promotionActionMock, new PromotionActionType());
    }
}
