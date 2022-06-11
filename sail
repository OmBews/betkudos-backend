#!/usr/bin/env bash

command=$1
shift 1
params="$@"

./vendor/bin/sail $command $params
