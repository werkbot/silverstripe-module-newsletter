#!/bin/bash

# Sync master and develop with remote
#
# Run with:
# sh sync-branches-with-remote.sh

git checkout master
git pull
git checkout develop
git pull