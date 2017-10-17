#!/bin/bash

echo ..
find ./ -type f  -name '*.php'|grep -v -E 'ide-helper|ide_helper'| xargs -P4 -n1 php -l