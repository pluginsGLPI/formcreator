#!/bin/sh
#
# After success script for Travis CI
#

# please keep tasks strongly separated,
# no matter they have the same if block

# please set $TX_USER and $TX_TOKEN in your travis dashboard

# find if we are in a valid branch to build docs and locales
GENERATE_LOCALES=false
GENERATE_DOCS=false
if echo "$TRAVIS_BRANCH" | grep -q -P '^(master|develop|support/|release/)'; then
    GENERATE_LOCALES=true
    GENERATE_DOCS=true
fi
if [ "$GENERATE_LOCALES" = true ] && [ "$TRAVIS_PULL_REQUEST" = false ]; then
    echo "updating source language"
    sudo apt install transifex-client
    echo "[https://www.transifex.com]" > ~/.transifexrc
    echo "api_hostname = https://api.transifex.com" >> ~/.transifexrc
    echo "hostname = https://www.transifex.com" >> ~/.transifexrc
    echo "token = ${TX_TOKEN}" >> ~/.transifexrc
    echo "password = ${TX_TOKEN}" >> ~/.transifexrc
    echo "username = ${TX_USER}" >> ~/.transifexrc
    # php vendor/bin/robo locales:send
else
    echo "skipping source language update"
fi

if [ "$GENERATE_DOCS" = true ] && [ "$TRAVIS_PULL_REQUEST" = false ]; then
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
        echo "generating the docs"
        # clean the repo and generate the docs
        git checkout .
        echo "code coverage"
        find development/coverage/"$TRAVIS_BRANCH"/ -type f -name "*.html" -exec sed -i "1s/^/---\\nlayout: coverage\\n---\\n/" "{}" \;
        find development/coverage/"$TRAVIS_BRANCH"/ -type f -name "*.html" -exec sed -i "/bootstrap.min.css/d" "{}" \;
        find development/coverage/"$TRAVIS_BRANCH"/ -type f -name "*.html" -exec sed -i "/report.css/d" "{}" \;

        # commit_website_files
        echo "adding the coverage report"
        git add development/coverage/"$TRAVIS_BRANCH"/*
        echo "creating a branch for the new documents"
        git checkout -b localCi
        git commit -m "changes to be merged"
        git checkout -f -b gh-pages origin-pages/gh-pages
        git rm -r development/coverage/"$TRAVIS_BRANCH"/*
        git checkout localCi development/coverage/"$TRAVIS_BRANCH"/
        git add development/coverage/"$TRAVIS_BRANCH"/*

        # upload_files
        echo "pushing the up to date documents"
        git commit --message "docs: update test reports"
        git fetch origin-pages
        git rebase origin-pages/gh-pages
        git push --quiet --set-upstream origin-pages gh-pages --force
    fi
else
    echo "skipping documents update"
fi
