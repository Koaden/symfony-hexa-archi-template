<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require __DIR__.'/../../vendor/autoload.php';

/** @var list<string> $violations */
$violations = [];

$check = static function (array $node, string $path, string $file) use (&$check, &$violations): void {
    foreach ($node as $key => $value) {
        $key = (string) $key;
        $current = '' === $path ? $key : $path.'.'.$key;

        if (1 !== preg_match('/^[a-z0-9_]+$/', $key)) {
            $violations[] = sprintf('%s: key "%s" must be lowercase snake_case without dots, spaces or hyphens', basename($file), $current);
        }

        if (is_array($value)) {
            $check($value, $current, $file);
        }
    }
};

foreach (glob(__DIR__.'/../../translations/*.yaml') ?: [] as $file) {
    $catalog = Yaml::parseFile($file);

    if (is_array($catalog)) {
        $check($catalog, '', $file);
    }
}

if ([] !== $violations) {
    echo "FAIL Translation keys must be nested snake_case\n".implode("\n", $violations)."\n";
    exit(1);
}
