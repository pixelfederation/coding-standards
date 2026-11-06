<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Rule\AbstractRule;
use FriendsOfTwig\Twigcs\Rule\RuleInterface;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Validator\Violation;
use Override;
use RuntimeException;

final class HtmlLayoutRule extends AbstractRule implements RuleInterface
{
    /**
     * @param list<string> $inlineTags
     */
    public function __construct(
        int $severity,
        private readonly array $inlineTags = ['strong', 'em', 'b', 'i', 'small'],
    ) {
        parent::__construct($severity);
    }

    /**
     * @return list<Violation>
     */
    #[Override]
    public function check(TokenStream $tokens): array
    {
        $source = $tokens->getSourceContext();
        $code = $this->maskTwig($source->getCode());
        preg_match_all(
            '~</?([a-zA-Z][\w:-]*)(?:[^<>"\x27]+|"[^"]*"|\x27[^\x27]*\x27)*>~',
            $code,
            $tags,
            PREG_OFFSET_CAPTURE,
        );
        $violations = [];
        $skipUntil = 0;

        foreach ($tags[0] as $index => [$tag, $offset]) {
            if ($offset < $skipUntil) {
                continue;
            }

            array_push($violations, ...$this->checkTag($code, $source->getPath(), $tag, $offset));
            $skipUntil = $this->rawElementEnd($code, $tags[1][$index][0], $offset + strlen($tag));
        }

        return $violations;
    }

    private function rawElementEnd(string $code, string $name, int $after): int
    {
        if (!in_array(strtolower($name), ['pre', 'textarea', 'script', 'style'], true)) {
            return 0;
        }

        $end = stripos($code, '</' . $name, $after);
        if ($end === false) {
            return strlen($code);
        }

        $terminator = strpos($code, '>', $end);

        return $terminator === false ? strlen($code) : $terminator + 1;
    }

    /**
     * @return list<Violation>
     */
    private function checkTag(string $code, string $path, string $tag, int $offset): array
    {
        if (str_starts_with($tag, '</')) {
            return $this->checkClosingTag($code, $path, $tag, $offset);
        }

        $violations = $this->checkAttributes($code, $path, $tag, $offset);
        if ($this->allowsInlineContent($tag)) {
            return $violations;
        }

        $after = $offset + strlen($tag);
        $lineEnd = strpos($code, "\n", $after);
        if (trim(substr($code, $after, ($lineEnd === false ? strlen($code) : $lineEnd) - $after)) !== '') {
            $violations[] = $this->violation(
                $code,
                $path,
                $after,
                'Put HTML content or the next element on a new line.',
            );
        }

        return $violations;
    }

    private function isInlineTag(string $tag): bool
    {
        preg_match('~^</?([a-zA-Z][\w:-]*)~', $tag, $name);
        $tagName = $name[1] ?? throw new RuntimeException('Cannot parse HTML tag name.');

        return in_array(strtolower($tagName), array_map(strtolower(...), $this->inlineTags), true);
    }

    private function allowsInlineContent(string $tag): bool
    {
        return $this->isInlineTag($tag)
            || preg_match('~^<(?:pre|textarea|script|style)(?=[\s>])~i', $tag) === 1;
    }

    /**
     * @return list<Violation>
     */
    private function checkClosingTag(string $code, string $path, string $tag, int $offset): array
    {
        return $this->isInlineTag($tag)
            ? []
            : $this->checkLine($code, $path, $offset, 'Put the closing HTML tag on its own line.');
    }

    /**
     * @return list<Violation>
     */
    private function checkAttributes(string $code, string $path, string $tag, int $offset): array
    {
        preg_match('~^<[a-zA-Z][\w:-]*~', $tag, $name);
        $nameEnd = strlen($name[0] ?? throw new RuntimeException('Cannot parse HTML tag name.'));
        preg_match_all(
            '~[^\s=/>]+(?:\s*=\s*(?:"[^"]*"|\x27[^\x27]*\x27|[^\s>]+))?~',
            substr($tag, $nameEnd, str_ends_with($tag, '/>') ? -2 : -1),
            $attributes,
            PREG_OFFSET_CAPTURE,
        );
        $violations = [];
        foreach ($attributes[0] as [, $relativeOffset]) {
            array_push($violations, ...$this->checkLine(
                $code,
                $path,
                $offset + $nameEnd + $relativeOffset,
                'Put each HTML attribute on its own line.',
            ));
        }

        if ($attributes[0] !== []) {
            array_push($violations, ...$this->checkLine(
                $code,
                $path,
                $offset + strlen($tag) - (str_ends_with($tag, '/>') ? 2 : 1),
                'Put the HTML opening tag terminator on its own line.',
            ));
        }

        return $violations;
    }

    /**
     * @return list<Violation>
     */
    private function checkLine(string $code, string $path, int $offset, string $message): array
    {
        return $this->startsLine($code, $offset) ? [] : [$this->violation($code, $path, $offset, $message)];
    }

    private function maskTwig(string $code): string
    {
        // Quoted Twig strings may contain HTML or even Twig closing delimiters.
        $string = '\x27(?:\\\\.|[^\x27\\\\])*\x27|"(?:\\\\.|[^"\\\\])*"';

        return preg_replace_callback(
            '~<!--.*?-->|\{\#.*?\#\}|\{\{(?:' . $string . '|(?:(?!\}\})[^\x27"]))*\}\}'
                . '|\{%(?:' . $string . '|(?:(?!%\})[^\x27"]))*%\}~s',
            static function (array $match): string {
                $masked = preg_replace('/[^\r\n]/', ' ', $match[0])
                    ?? throw new RuntimeException('Cannot mask Twig expression.');
                if (str_starts_with($match[0], '{{')) {
                    $masked[0] = 'x';
                }

                return $masked;
            },
            $code,
        ) ?? throw new RuntimeException('Cannot scan Twig expressions.');
    }

    private function startsLine(string $code, int $offset): bool
    {
        $lineStart = strrpos(substr($code, 0, $offset), "\n");
        $start = $lineStart === false ? 0 : $lineStart + 1;

        return trim(substr($code, $start, $offset - $start)) === '';
    }

    private function violation(string $code, string $path, int $offset, string $message): Violation
    {
        $prefix = substr($code, 0, $offset);
        $lineStart = strrpos($prefix, "\n");

        return $this->createViolation(
            $path,
            substr_count($prefix, "\n") + 1,
            $lineStart === false ? $offset : $offset - $lineStart - 1,
            $message,
        );
    }
}
