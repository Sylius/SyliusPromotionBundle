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
use Sylius\Bundle\PromotionBundle\Validator\CatalogPromotionScopeGroupValidator;
use Sylius\Bundle\PromotionBundle\Validator\Constraints\CatalogPromotionScopeGroup;
use Sylius\Component\Promotion\Model\CatalogPromotionScopeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CatalogPromotionScopeGroupValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface&MockObject */
    private ExecutionContextInterface $context;

    private CatalogPromotionScopeGroupValidator $catalogPromotionScopeGroupValidator;

    /** @var CatalogPromotionScopeInterface&MockObject */
    private CatalogPromotionScopeInterface $scope;

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
        $this->catalogPromotionScopeGroupValidator = new CatalogPromotionScopeGroupValidator(self::VALIDATION_GROUPS);
        $this->catalogPromotionScopeGroupValidator->initialize($this->context);
        $this->scope = $this->createMock(CatalogPromotionScopeInterface::class);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionScopeGroup(): void
    {
        /** @var Constraint&MockObject $constraint */
        $constraint = $this->createMock(Constraint::class);

        self::expectException(UnexpectedTypeException::class);

        $this->catalogPromotionScopeGroupValidator->validate($this->scope, $constraint);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionScope(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->catalogPromotionScopeGroupValidator->validate(new \stdClass(), new CatalogPromotionScopeGroup());
    }

    public function testDoesNothingWhenTypeIsNull(): void
    {
        $this->scope->expects(self::once())->method('getType')->willReturn(null);

        $this->context->expects(self::never())->method('getValidator');

        $this->catalogPromotionScopeGroupValidator->validate($this->scope, new CatalogPromotionScopeGroup());
    }

    public function testDoesNothingWhenTypeIsAnEmptyString(): void
    {
        $this->scope->expects(self::once())->method('getType')->willReturn('');

        $this->context->expects(self::never())->method('getValidator');

        $this->catalogPromotionScopeGroupValidator->validate($this->scope, new CatalogPromotionScopeGroup());
    }

    public function testPassesConfiguredValidationGroupsForFurtherValidation(): void
    {
        $constraint = new CatalogPromotionScopeGroup();

        $this->scope->method('getType')->willReturn('test');

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
            ->with($this->scope, null, ['group1' => 'test_group'])
            ->willReturn($contextualValidator);

        $this->catalogPromotionScopeGroupValidator->validate($this->scope, $constraint);
    }
}
