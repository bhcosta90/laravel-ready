#!/bin/zsh
# Check if sail is enabled
sailEnabled=$(grep -i '^SAIL_ENABLED' .env | cut -d '=' -f2 | tr '[:upper:]' '[:lower:]')
sailCommand=$([[ "$sailEnabled" = "true" ]] && echo "./vendor/bin/sail" || echo "")
echo "Running Tests"
if [ -n "$sailCommand" ]; then
    $sailCommand php artisan test --parallel
else
    php artisan test --parallel
fi
STATUS_CODE=$?
if [ $STATUS_CODE -ne 0 ]; then
    echo "Tests failed"
    exit 1
else
    echo "Tests passed"
fi
