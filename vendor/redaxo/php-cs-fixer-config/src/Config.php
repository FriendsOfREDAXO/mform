<?php

declare(strict_types=1);

namespace Redaxo\PhpCsFixerConfig;

use PhpCsFixer\ConfigInterface;
use PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixers;
use Redaxo\PhpCsFixerConfig\Fixer\NoSemicolonBeforeClosingTagFixer;
use Redaxo\PhpCsFixerConfig\Fixer\StatementIndentationFixer;

class Config extends \PhpCsFixer\Config
{
    public function __construct(string $name = 'REDAXO')
    {
        parent::__construct($name);

        $this->setUsingCache(true);
        $this->setRiskyAllowed(true);
        $this->registerCustomFixers(new Fixers());
        $this->registerCustomFixers([
            new NoSemicolonBeforeClosingTagFixer(),
            new StatementIndentationFixer(),
        ]);
        $this->setRules([]);
    }

    public function setRules(array $rules): ConfigInterface
    {
        $default = [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            '@PHP81Migration' => true,
            '@PHP80Migration:risky' => true,
            '@PHPUnit84Migration:risky' => true,

            'align_multiline_comment' => true,
            'array_indentation' => true,
            'blank_line_before_statement' => false,
            'comment_to_phpdoc' => true,
            'compact_nullable_typehint' => true,
            'concat_space' => ['spacing' => 'one'],
            'control_structure_braces' => true,
            'control_structure_continuation_position' => true,
            'curly_braces_position' => [
                'allow_single_line_anonymous_functions' => false,
            ],
            'declare_parentheses' => true,
            'declare_strict_types' => false,
            'echo_tag_syntax' => ['format' => 'short'],
            'empty_loop_condition' => false,
            'escape_implicit_backslashes' => true,
            'global_namespace_import' => [
                'import_constants' => true,
                'import_functions' => true,
                'import_classes' => true,
            ],
            'heredoc_to_nowdoc' => true,
            'list_syntax' => ['syntax' => 'short'],
            'method_argument_space' => ['on_multiline' => 'ignore'],
            'multiline_comment_opening_closing' => true,
            'native_constant_invocation' => false,
            'no_alternative_syntax' => false,
            'no_blank_lines_after_phpdoc' => false,
            'no_extra_blank_lines' => true,
            'no_multiple_statements_per_line' => true,
            'no_null_property_initialization' => true,
            'no_superfluous_elseif' => true,
            'no_superfluous_phpdoc_tags' => [
                'allow_mixed' => true,
                'remove_inheritdoc' => true,
            ],
            'no_unreachable_default_argument_value' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'nullable_type_declaration_for_default_null_value' => true,
            'operator_linebreak' => false,
            'ordered_class_elements' => ['order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property',
                'construct',
                'phpunit',
                'method',
            ]],
            'ordered_imports' => ['imports_order' => [
                'class',
                'function',
                'const',
            ]],
            'php_unit_internal_class' => true,
            'php_unit_test_case_static_method_calls' => true,
            'phpdoc_align' => false,
            'phpdoc_no_package' => false,
            'phpdoc_order' => true,
            'phpdoc_separation' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_types_order' => false,
            'phpdoc_var_annotation_correct_order' => true,
            'psr_autoloading' => false,
            'semicolon_after_instruction' => false,
            'single_space_around_construct' => true,
            'statement_indentation' => false,
            'static_lambda' => true,
            'trailing_comma_in_multiline' => [
                'after_heredoc' => true,
                'elements' => ['arguments', 'arrays', 'match', 'parameters'],
            ],
            'use_arrow_functions' => false,
            'void_return' => false,

            ConstructorEmptyBracesFixer::name() => true,
            MultilinePromotedPropertiesFixer::name() => ['keep_blank_lines' => true],
            PhpdocSingleLineVarFixer::name() => true,

            'Redaxo/no_semicolon_before_closing_tag' => true,
            'Redaxo/statement_indentation' => true,
        ];

        return parent::setRules(array_merge($default, $rules));
    }
}
