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
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CouponPossibleGenerationAmount;
use Sylius\Bundle\PromotionBundle\Validator\CouponGenerationAmountValidator;
use Sylius\Component\Promotion\Generator\GenerationPolicyInterface;
use Sylius\Component\Promotion\Generator\ReadablePromotionCouponGeneratorInstructionInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CouponGenerationAmountValidatorTest extends TestCase
{
    /** @var GenerationPolicyInterface&MockObject */
    private MockObject $generationPolicyMock;

    /** @var ExecutionContextInterface&MockObject */
    private MockObject $contextMock;

    private CouponGenerationAmountValidator $couponGenerationAmountValidator;

    protected function setUp(): void
    {
        $this->generationPolicyMock = $this->createMock(GenerationPolicyInterface::class);
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->couponGenerationAmountValidator = new CouponGenerationAmountValidator($this->generationPolicyMock);
        $this->couponGenerationAmountValidator->initialize($this->contextMock);
    }

    public function testAddsViolation(): void
    {
        /** @var ReadablePromotionCouponGeneratorInstructionInterface&MockObject $instructionMock */
        $instructionMock = $this->createMock(ReadablePromotionCouponGeneratorInstructionInterface::class);
        $constraint = new CouponPossibleGenerationAmount();
        $instructionMock->expects($this->atLeastOnce())->method('getAmount')->willReturn(17);
        $instructionMock->expects($this->atLeastOnce())->method('getCodeLength')->willReturn(1);
        $this->generationPolicyMock->expects($this->once())->method('isGenerationPossible')->with($instructionMock)->willReturn(false);
        $this->generationPolicyMock->expects($this->once())->method('getPossibleGenerationAmount')->with($instructionMock);
        $this->contextMock->expects($this->once())->method('addViolation');
        $this->couponGenerationAmountValidator->validate($instructionMock, $constraint);
    }

    public function testDoesNotAddViolation(): void
    {
        /** @var ReadablePromotionCouponGeneratorInstructionInterface&MockObject $instructionMock */
        $instructionMock = $this->createMock(ReadablePromotionCouponGeneratorInstructionInterface::class);
        $constraint = new CouponPossibleGenerationAmount();
        $instructionMock->expects($this->once())->method('getAmount')->willReturn(5);
        $instructionMock->expects($this->once())->method('getCodeLength')->willReturn(1);
        $this->generationPolicyMock->expects($this->once())->method('isGenerationPossible')->with($instructionMock)->willReturn(true);
        $this->generationPolicyMock->expects($this->never())->method('getPossibleGenerationAmount')->with($instructionMock);
        $this->contextMock->expects($this->never())->method('addViolation');
        $this->couponGenerationAmountValidator->validate($instructionMock, $constraint);
    }
}
