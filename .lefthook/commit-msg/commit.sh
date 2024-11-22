#!/bin/zsh
# The script below adds the branch name automatically to
# every one of your commit messages. The regular expression
# below searches for JIRA issue key's. The issue key will
# be extracted out of your branch name
REGEX_ISSUE_ID="[a-zA-Z0-9,\.\_\-]+-[0-9]+"
NC='\033[0m'
BBlue='\033[1;34m'
BRed='\033[1;31m'
# Find current user email
USER_EMAIL=$(git config user.email)
# Verify if the user email is set to a devsquad email
if [[ "$USER_EMAIL" != *"devsquad.com" ]]; then
    echo -e "${BRed}You are not using a DevSquad email as your user.email... ${NC}"
    echo -e "${BBlue}You can use ${BRed}git config user.email \"type your devsquad email\"${BBlue} to set up the user.email.${NC}"
    exit 1
fi
# Find current branch name
BRANCH_NAME=$(git symbolic-ref --short HEAD)
# Extract issue id from branch name
ISSUE_ID=$(echo "$BRANCH_NAME" | grep -o -E "$REGEX_ISSUE_ID")
