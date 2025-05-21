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
    private MockObject $contextMock;

    private CatalogPromotionScopeGroupValidator $catalogPromotionScopeGroupValidator;

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
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);
        $this->catalogPromotionScopeGroupValidator = new CatalogPromotionScopeGroupValidator(self::VALIDATION_GROUPS);
        $this->catalogPromotionScopeGroupValidator->initialize($this->contextMock);
    }

    public function testThrowsExceptionWhenConstraintIsNotCatalogPromotionScopeGroup(): void
    {
        /** @var CatalogPromotionScopeInterface&MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        /** @var Constraint&MockObject $constraintMock */
        $constraintMock = $this->createMock(Constraint::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionScopeGroupValidator->validate($scopeMock, $constraintMock);
    }

    public function testThrowsExceptionWhenValueIsNotCatalogPromotionScope(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->catalogPromotionScopeGroupValidator->validate(new stdClass(), new CatalogPromotionScopeGroup());
    }

    public function testDoesNothingWhenTypeIsNull(): void
    {
        /** @var CatalogPromotionScopeInterface&MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $scopeMock->expects($this->once())->method('getType')->willReturn(null);
        $this->contextMock->expects($this->never())->method('getValidator');
        $this->catalogPromotionScopeGroupValidator->validate($scopeMock, new CatalogPromotionScopeGroup());
    }

    public function testDoesNothingWhenTypeIsAnEmptyString(): void
    {
        /** @var CatalogPromotionScopeInterface&MockObject $scopeMock */
        $scopeMock = $this->createMock(CatalogPromotionScopeInterface::class);
        $scopeMock->expects($this->once())->method('getType')->willReturn('');
        $this->contextMock->expects($this->never())->method('getValidator');
        $this->catalogPromotionScopeGroupValidator->validate($scopeMock, new CatalogPromotionScopeGroup());
    }

    public function testPassesConfiguredValidationGroupsForFurtherValidation(): void
    {
        $constraint = new CatalogPromotionScopeGroup();

        /** @var CatalogPromotionScopeInterface&MockObject $scope */
        $scope = $this->createMock(CatalogPromotionScopeInterface::class);

        $scope->method('getType')->willReturn('test');

        /** @var ValidatorInterface&MockObject $validator */
        $validator = $this->createMock(ValidatorInterface::class);

        /** @var ContextualValidatorInterface&MockObject $contextualValidator */
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $this->contextMock->expects($this->once())
            ->method('getValidator')
            ->willReturn($validator);

        $validator->expects($this->once())
            ->method('inContext')
            ->with($this->contextMock)
            ->willReturn($contextualValidator);

        $contextualValidator->expects($this->once())
            ->method('validate')
            ->with($scope, null, ['group1' => 'test_group'])
            ->willReturn($contextualValidator);

        $this->catalogPromotionScopeGroupValidator->validate($scope, $constraint);
    }
}
