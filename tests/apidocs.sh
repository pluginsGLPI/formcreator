#!/bin/sh
#
# After success script for apidocs on Travis CI.
#
# Apidoc doesn't work on php 7.2 because compatibility problems,
# that's why this script is used on php 7.1 until the problem is solved
#

# find if we are in a valid branch to build docs
GENERATE_DOCS=false
if echo "$TRAVIS_BRANCH" | grep -q -P '^(master|develop|support/)'; then
    GENERATE_DOCS=true
fi

if [ "$GENERATE_DOCS" = false ] || [ "$TRAVIS_PULL_REQUEST" != false ]; then
    echo "skipping documents update"
    exit 0;
fi

# setup_git only for the main repo and not forks
echo "Configuring git user"
git config --global user.email "apps@teclib.com"
git config --global user.name "Teclib' bot"
echo "adding a new remote"
# please set a personal token in https://github.com/settings/tokens
# enable "public_repo" for a public repository or "repo" otherwise
# then set the $GH_TOKEN to this value in your travis dashboard
git remote add origin-pages https://"$GH_TOKEN"@github.com/"$TRAVIS_REPO_SLUG".git > /dev/null 2>&1
echo "fetching from the new remote"
git fetch origin-pages

# check if gh-pages exist in remote
if [ "git branch -r --list origin-pages/gh-pages" ]; then
    # clean the repo and generate the docs
    git checkout .
    wget -O apigen.phar https://github.com/ApiGen/ApiGen/releases/download/v4.1.2/apigen.phar
    php apigen.phar generate \
        --access-levels=public,protected,private \
        --todo \
        --deprecated \
        --tree \
        -s inc -d development/code-documentation/"$TRAVIS_BRANCH"/

    #add layouts and remove default
    find development/code-documentation/"$TRAVIS_BRANCH"/ -type f -name "*.html" -exec sed -i "1s/^/---\\nlayout: codeDocumentation\\n---\\n/" "{}" \;
    rm development/code-documentation/"$TRAVIS_BRANCH"/resources/style.css

    # commit_website_files
    echo "adding the code documentation report"
    git add development/code-documentation/"$TRAVIS_BRANCH"/*
    echo "creating a branch for the new documents"
    git checkout -b localCi
    git commit -m "changes to be merged"
    git checkout -f -b gh-pages origin-pages/gh-pages
    git rm -r development/code-documentation/"$TRAVIS_BRANCH"/*
    git checkout localCi development/code-documentation/"$TRAVIS_BRANCH"/
    git add development/code-documentation/"$TRAVIS_BRANCH"/*

    # upload_files
    echo "pushing the up to date documents"
    git commit --message "docs: update code documentation report"
    git fetch origin-pages
    git rebase origin-pages/gh-pages
    git push --quiet --set-upstream origin-pages gh-pages --force
fi
