#!/bin/bash -eu

# Regex patterns
commit_regex="^\[[A-Z]+-[0-9]+\] (build|chore|ci|docs|feat|fix|perf|refactor|revert|style|test)(\([a-zA-Z0-9\-_.]+\))?!?: .{1,120}$"
branch_regex="^(feature|bugfix|hotfix)\/[A-Z]+-[0-9]+.*$|^(develop|release\/[0-9]+\.[0-9]+|main)$"

# Get the commit message and branch name

regex_issue_id="[a-zA-Z0-9,\.\_\-]+-[0-9]+"
branch_name=$(git symbolic-ref --short HEAD)
issue_id=$(echo "$branch_name" | grep -o -E "$regex_issue_id")

commit_msg_file=$1
commit_message=$(cat "$commit_msg_file")

current_branch=$(git rev-parse --abbrev-ref HEAD)

# Verifica se o commit é um merge
if [[ "$commit_message" =~ ^Merge\ .* ]]; then
    echo "✅ Merge commit detected. Skipping validation."
    exit 0
fi

# Validate the branch name
if ! echo "$current_branch" | grep -Eq "$branch_regex"; then
    echo "❌ Current branch name does not follow the required pattern!"
    echo "Expected patterns:"
    echo "  - (feature|bugfix|hotfix)/JIRA-ID.* (e.g., feature/PROJECT-123-new-feature)"
    echo "  - (develop|release)/major.minor (e.g., release/1.2)"
    echo "  - main"
    echo ""
    echo "Your current branch:"
    echo "  $current_branch"
    exit 1
fi

# Validate the commit message
if ! echo "[$issue_id] $commit_message" | grep -Eq "$commit_regex"; then
    echo "❌ Commit message does not follow the required format!"
    echo "Expected format: [JIRA-ID] <type>(<scope>): <description>"
    echo ""
    echo "Examples:"
    echo "  [PROJECT-123] feat(login): add authentication module"
    echo "  [TASK-456] fix(api): handle null pointer exception"
    echo "  [BUG-789] chore: update dependencies"
    echo ""
    echo "Your commit message:"
    echo " [$issue_id] $commit_message"
    exit 1
fi

if [[  ! "$commit_message" =~ ^Merge\ .* ]]; then
    echo "[$issue_id] $commit_message" > "$commit_msg_file"
fi

# If both validations pass
exit 0
