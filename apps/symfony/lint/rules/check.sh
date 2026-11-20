#!/bin/sh
# Project rules that a linter cannot express natively. Run from anywhere, fails on the first violated rule set.
cd "$(dirname "$0")/../.." || exit 1

status=0

fail() {
    printf 'FAIL %s\n' "$1"
    printf '%s\n' "$2"
    status=1
}

found=$(grep -rnE 'phpstan-ignore' src tests config bin public 2>/dev/null)
[ -z "$found" ] || fail 'PHPStan ignores are forbidden, fix the code instead' "$found"

found=$(grep -nE 'ignoreErrors' lint/phpstan/phpstan.neon 2>/dev/null)
[ -z "$found" ] || fail 'ignoreErrors is forbidden in phpstan.neon' "$found"

found=$(grep -rn 'Auto-generated Migration' src/Infrastructure/Doctrine/Migrations 2>/dev/null)
[ -z "$found" ] || fail 'Remove the auto-generated migration docblock' "$found"

found=$(grep -rnE 'throw new \\?(Exception|RuntimeException|LogicException|InvalidArgumentException|DomainException)\(' src/Domain 2>/dev/null)
[ -z "$found" ] || fail 'Domain failures must throw a dedicated exception class' "$found"

php lint/rules/translation-keys.php || status=1

[ "$status" -eq 0 ] && echo 'Project rules OK'
exit "$status"
