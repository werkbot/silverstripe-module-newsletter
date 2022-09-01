#!/bin/bash

# Delete any local release or hotfix branches
#
# Run with:
# sh delete-stale-release-branches.sh

for branch in $(git for-each-ref --format='%(refname)' refs/heads/)
do
    if echo $branch | grep hotfix &> /dev/null || echo $branch | grep release &> /dev/null;
    then
      BRANCHNAME=$(echo $branch | sed -re "s/refs\/heads\///")
      git branch -D $BRANCHNAME
    fi
done