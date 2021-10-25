<?php

declare(strict_types=1);

$header = <<<'EOF'
This file is part of DbUnit.

(c) Sebastian Bergmann <sebastian@phpunit.de>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
//        'declare_strict_types' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => ['header' => $header],
        'trailing_comma_in_multiline' => true,
        'trim_array_spaces' => true,
        'line_ending' => true,
        'modernize_types_casting' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_short_bool_cast' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_align' => false,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'self_accessor' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'native_function_invocation' => true,
    ])
;

return $config;
