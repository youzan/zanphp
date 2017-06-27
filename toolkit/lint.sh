#!/bin/bash

echo ..
find ./src -type f  -name '*.php'| xargs -P4 -n1 php -l