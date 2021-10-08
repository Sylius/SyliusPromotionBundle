<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\PromotionBundle\Criteria;

use Sylius\Component\Promotion\Model\CatalogPromotionInterface;

final class Enabled implements CriteriaInterface
{
    public function meets(array $catalogPromotions): array
    {
        return array_values(array_filter($catalogPromotions, function(CatalogPromotionInterface $catalogPromotion): bool {
            return $catalogPromotion->isEnabled();
        }));
    }
}
