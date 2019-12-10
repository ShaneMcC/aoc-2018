#!/bin/bash

cd "$(dirname "$0")"

for DAY in `seq 1 25`; do
	if [ -e ${DAY} ]; then
		echo -n "Day ${DAY}:"
		if [ -e ${DAY}/answers.txt ]; then
			PART1=$(cat ${DAY}/answers.txt | head -n 1)
			PART2=$(cat ${DAY}/answers.txt | head -n 2 | tail -n 1)

			RESULT=$(${DAY}/run.php 2>/dev/null | grep -Pzl ".*${PART1}.*\n.*${PART2}.*")
			# RESULT=$(./docker.sh ${DAY} 2>/dev/null | grep -Pzl ".*${PART1}.*\n.*${PART2}.*")

			if [ "${RESULT}" = "" ]; then
				echo -e "\033[0;32m" "Fail." "\033[0m";
			else
				echo -e "\033[0;32m" "Success." "\033[0m";
			fi;
		else
			echo -e "\033[0;32m" "Untested." "\033[0m";
		fi;
	fi;
done;
