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
use Sylius\Bundle\PromotionBundle\Validator\PromotionNotCouponBasedValidator;
use stdClass;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\PromotionNotCouponBased;
use Sylius\Component\Promotion\Model\PromotionCouponInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class PromotionNotCouponBasedValidatorTest extends TestCase
{
    /**
     * @var ExecutionContextInterface|MockObject
     */
    private MockObject $contextMock;
    private PromotionNotCouponBasedValidator $promotionNotCouponBasedValidator;
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->promotionNotCouponBasedValidator = new PromotionNotCouponBasedValidator($this->contextMock);
        $this->initialize($this->contextMock);
    }

    public function testDoesNothingWhenValueIsNull(): void
    {
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->promotionNotCouponBasedValidator->validate(null, new PromotionNotCouponBased());
    }

    public function testThrowsAnExceptionWhenConstraintIsNotPromotionNotCouponBased(): void
    {
        /** @var PromotionCouponInterface|MockObject $couponMock */
        $couponMock = $this->createMock(PromotionCouponInterface::class);
        /** @var Constraint|MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->expectException(UnexpectedTypeException::class);
        $this->promotionNotCouponBasedValidator->validate($couponMock, $constraintMock);
    }

    public function testThrowsAnExceptionWhenValueIsNotACoupon(): void
    {
        $this->contextMock->expects($this->never())->method('buildViolation');
        $this->expectException(UnexpectedValueException::class);
        $this->promotionNotCouponBasedValidator->validate(new stdClass(), new PromotionNotCouponBased());
    }

    public function testDoesNothingWhenCouponHasNoPromotion(): void
    {
        /** @var PromotionCouponInterface|MockObject $couponMock */
        $couponMock = $this->createMock(PromotionCouponInterface::class);
        $this->contextMock->expects($this->never())->method('buildViolation');
        $couponMock->expects($this->once())->method('getPromotion')->willReturn(null);
        $this->promotionNotCouponBasedValidator->validate($couponMock, new PromotionNotCouponBased());
    }

    public function testDoesNothingWhenCouponHasPromotionAndItsCouponBased(): void
    {
        /** @var PromotionCouponInterface|MockObject $couponMock */
        $couponMock = $this->createMock(PromotionCouponInterface::class);
        /** @var PromotionInterface|MockObject $promotionMock */
        $promotionMock = $this->createMock(PromotionInterface::class);
        $this->contextMock->expects($this->never())->method('buildViolation');
        $promotionMock->expects($this->once())->method('isCouponBased')->willReturn(true);
        $couponMock->expects($this->once())->method('getPromotion')->willReturn($promotionMock);
        $this->promotionNotCouponBasedValidator->validate($couponMock, new PromotionNotCouponBased());
    }

    public function testAddsViolationWhenCouponHasPromotionButItsNotCouponBased(): void
    {
        /** @var ConstraintViolationBuilderInterface|MockObject $violationBuilderMock */
        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        /** @var PromotionCouponInterface|MockObject $couponMock */
        $couponMock = $this->createMock(PromotionCouponInterface::class);
        /** @var PromotionInterface|MockObject $promotionMock */
        $promotionMock = $this->createMock(PromotionInterface::class);
        $constraint = new PromotionNotCouponBased();
        $this->contextMock->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('atPath')->with('promotion')->willReturn($violationBuilderMock);
        $violationBuilderMock->expects($this->once())->method('addViolation');
        $promotionMock->expects($this->once())->method('isCouponBased')->willReturn(false);
        $couponMock->expects($this->once())->method('getPromotion')->willReturn($promotionMock);
        $this->promotionNotCouponBasedValidator->validate($couponMock, $constraint);
    }
}
