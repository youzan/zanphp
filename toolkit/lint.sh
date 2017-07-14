#!/bin/bash

echo ..
find ./ -type f  -name '*.php'| xargs -P4 -n1 php -l