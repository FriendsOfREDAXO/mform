<?php

declare(strict_types=1);

namespace Redaxo\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

use function count;

final class NoSemicolonBeforeClosingTagFixer extends AbstractFixer
{
    public function getName(): string
    {
        return 'Redaxo/no_semicolon_before_closing_tag';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Single instructions inside PHP tags must omit the semicolon.',
            [new CodeSample("<?php foo(); ?>\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return !$tokens->isMonolithicPhp();
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = count($tokens) - 1; $index > 1; --$index) {
            if (!$tokens[$index]->isGivenKind(T_CLOSE_TAG)) {
                continue;
            }

            $prev = $index - 1;
            if ($tokens[$prev]->isWhitespace(' ')) {
                --$prev;
            }
            if (!$tokens[$prev]->equals(';')) {
                continue;
            }

            $tokens->clearAt($prev);
        }
    }
}
