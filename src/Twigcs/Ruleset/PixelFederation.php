<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Twigcs\Ruleset;

use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\Ruleset\Official;
use FriendsOfTwig\Twigcs\Validator\Violation;
use Override;
use PixelFederation\CodingStandards\Twigcs\Rule\HtmlLayoutRule;

final class PixelFederation extends Official
{
    /**
     * @return list<RuleInterface>
     */
    #[Override]
    public function getRules(): array
    {
        return [...array_values(parent::getRules()), new HtmlLayoutRule(Violation::SEVERITY_ERROR)];
    }
}
