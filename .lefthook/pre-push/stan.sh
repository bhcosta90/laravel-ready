#!/bin/zsh
# Check if sail is enabled
sailEnabled=$(grep -i '^SAIL_ENABLED' .env | cut -d '=' -f2 | tr '[:upper:]' '[:lower:]')
sailCommand=$([[ "$sailEnabled" = "true" ]] && echo "./vendor/bin/sail" || echo "")
echo "Running Stan"
if [ -n "$sailCommand" ]; then
    $sailCommand ./vendor/bin/phpstan analyse --memory-limit=2G
else
    ./vendor/bin/phpstan analyse --memory-limit=2G
fi
STATUS_CODE=$?
if [ $STATUS_CODE -ne 0 ]; then
    echo "Stan failed"
    exit 1
else
    echo "Stan passed"
fi
