<?php

declare(strict_types=1);


$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->ignoreVCSIgnored(true)
    ->exclude('Application/views')
    ->exclude('metadata.php')
    ->exclude('source');

/**
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */
return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PSR12' => true,
        '@PHP74Migration' => true,
        'header_comment' => ['header' => <<<'EOF'
            This file is part of OXID eSales AG EasyCredit module
            Copyright © OXID eSales AG. All rights reserved.

            Licensed under the GNU GPL v3 - See the file LICENSE for details.
            EOF],
        'numeric_literal_separator' => true,
    ])
    ->setFinder($finder);