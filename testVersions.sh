#!/bin/bash

cd "$(dirname "$0")"

ONLYDAY=""
if [ "${1}" != "" ]; then
	ONLYDAY="${1}"
fi;

for DAY in `seq 1 25`; do
	if [ "${ONLYDAY}" != "" -a "${ONLYDAY}" != "${DAY}" ]; then
		continue;
	fi;

	if [ -e ${DAY} ]; then
		echo "Day ${DAY}:"

		for DOCKERFILE in `ls docker/Dockerfile-*`; do
			VERSION="${DOCKERFILE#*-}"
			printf "%12s: " "${VERSION}"
			# TODO: This doesn't check if it passes or fails, just how long it runs for...
			./docker.sh --${VERSION} --time ${DAY} | grep -i real;
		done;
		echo ""
	fi;
done;
