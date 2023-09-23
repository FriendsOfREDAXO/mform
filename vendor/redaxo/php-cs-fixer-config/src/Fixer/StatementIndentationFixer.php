<?php

declare(strict_types=1);

namespace Redaxo\PhpCsFixerConfig\Fixer;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

final class StatementIndentationFixer extends AbstractProxyFixer
{
    public function getName(): string
    {
        return 'Redaxo/statement_indentation';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Each statement must be indented.',
            [
                new CodeSample(
                    '<?php
if ($baz == true) {
  echo "foo";
}
else {
      echo "bar";
}
',
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return parent::isCandidate($tokens) && $tokens->isMonolithicPhp();
    }

    protected function createProxyFixers(): array
    {
        return [new \PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer()];
    }
}
