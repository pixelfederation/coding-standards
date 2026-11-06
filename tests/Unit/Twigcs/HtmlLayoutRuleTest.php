<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Unit\Twigcs;

use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PixelFederation\CodingStandards\Twigcs\Rule\HtmlLayoutRule;
use PixelFederation\CodingStandards\Twigcs\Ruleset\PixelFederation;

final class HtmlLayoutRuleTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function templates(): iterable
    {
        yield 'literal inline body' => ['<pre><code>{{ output }}</code></pre>', 0];

        yield 'expanded label' => ["<span\n    class=\"label\"\n>\n    {{- 'label'|trans -}}\n</span>", 0];
        yield 'compact label' => ['<span class="label">{{ label }}</span>', 4];
        yield 'two attributes on one line' => ["<img\n    src=\"image.png\" alt=\"\"\n>", 1];
        yield 'single attribute on tag line' => ["<img src=\"image.png\"\n>", 1];
        yield 'terminator on attribute line' => ["<img\n    src=\"image.png\">", 1];
        yield 'plain inline content' => ['<b>text</b>', 2];
        yield 'no attributes' => ["<b>\n    text\n</b>", 0];
        yield 'void element' => ["<img\n    alt=\"\"\n/>", 0];
        yield 'greater than inside value' => ["<div\n    title=\"a > b\"\n>\n</div>", 0];
        yield 'Twig strings and comments' => ["{# <a href=\"x\">text</a> #}\n{{ '<b>text</b>' }}", 0];
        yield 'HTML comment' => ['<!-- <b>text</b> -->', 0];
        yield 'Twig delimiter inside string' => ["{{ '}} <b>text</b>' }}", 0];
        yield 'conditional attributes and helper' => [
        <<<'TWIG'
<div
	{% if enabled %}
		class="enabled"
	{% endif %}
	{{ stimulus_controller('example', {
		message: '<b>test</b>',
	}) }}
>
	{{ content }}
</div>
TWIG,
        0,
        ];

        yield 'literal bodies' => [
            "<script>\nconst html = '<b>text</b>';\n</script>\n<pre>\n<b>literal</b>\n</pre>",
        0,
        ];
    }

    #[DataProvider('templates')]
    public function testLayout(string $template, int $expectedCount): void
    {
        $rule = new HtmlLayoutRule(Violation::SEVERITY_ERROR);
        $violations = $rule->check(new TokenStream([], new Source($template, 'test', 'test.html.twig')));

        self::assertCount($expectedCount, $violations);
        foreach ($violations as $violation) {
            self::assertSame('test.html.twig', $violation->getFilename());
            self::assertSame(Violation::SEVERITY_ERROR, $violation->getSeverity());
        }
    }

    public function testLocationAndReuse(): void
    {
        $rule = new HtmlLayoutRule(Violation::SEVERITY_ERROR);
        $source = new Source("\n<img alt=\"\"\n>", 'test');
        $violations = $rule->check(new TokenStream([], $source));

        self::assertSame(2, $violations[0]->getLine());
        self::assertSame(5, $violations[0]->getColumn());
        self::assertSame([], $rule->check(new TokenStream([], new Source("<br>\n", 'test'))));
    }

    public function testRulesetPreservesOfficialRules(): void
    {
        self::assertCount(6, new PixelFederation(3)->getRules());
    }
}
