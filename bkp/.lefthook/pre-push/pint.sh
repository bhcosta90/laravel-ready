#!/bin/zsh
# Check if sail is enabled
sailEnabled=$(grep -i '^SAIL_ENABLED' .env | cut -d '=' -f2 | tr '[:upper:]' '[:lower:]')
sailCommand=$([[ "$sailEnabled" = "true" ]] && echo "./vendor/bin/sail" || echo "")

STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep ".php\{0,1\}$") || true
for FILE in $STAGED_FILES
do
    if [ -n "$sailCommand" ]; then
        $sailCommand ./vendor/bin/pint "${FILE}" > /dev/null >&1;
    else
        ./vendor/bin/pint ./vendor/bin/pint "${FILE}" > /dev/null >&1;
    fi
    git add "${FILE}";
done;


echo "Running Pint"
if [ -n "$sailCommand" ]; then
    $sailCommand ./vendor/bin/pint --test
else
    ./vendor/bin/pint --test
fi
STATUS_CODE=$?
if [ $STATUS_CODE -ne 0 ]; then
    echo "Pint failed"
    exit 1
else
    echo "Pint passed"
fi
