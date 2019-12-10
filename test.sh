#!/bin/bash

cd "$(dirname "$0")"

for DAY in `seq 1 25`; do
	if [ -e ${DAY} ]; then
		echo -n "Day ${DAY}:"
		if [ -e ${DAY}/answers.txt -a $(cat ${DAY}/answers.txt 2>/dev/null | wc -l) -ne 0 ]; then
			PART1=$(cat ${DAY}/answers.txt | head -n 1)
			if [ $(cat ${DAY}/answers.txt | wc -l) -eq 1 ]; then
				PART2=""
			else
				PART2=$(cat ${DAY}/answers.txt | head -n 2 | tail -n 1)
			fi;

			RESULT=$(${DAY}/run.php 2>/dev/null | grep -Pzl "(?s).*${PART1}.*\n.*${PART2}.*")
			# RESULT=$(./docker.sh ${DAY} 2>/dev/null | grep -Pzl "(?s).*${PART1}.*\n.*${PART2}.*")

			if [ "${RESULT}" = "" ]; then
				echo -e "\033[1;31m" "Fail." "\033[0m";
			else
				echo -e "\033[0;32m" "Success." "\033[0m";
			fi;
		else
			echo -e "\033[1;31m" "Untested." "\033[0m";
		fi;
	fi;
done;
