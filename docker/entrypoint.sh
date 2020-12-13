#!/bin/bash

DAY="0"
FILE="run.php"
TIME="0";

while [ "${1}" != "" ]; do
	case "${1}" in
		--file)
			shift
			FILE="${1}"
			;;
		--time)
			TIME="1"
			;;
		*)
			DAY="${1}"
			shift
			break;
			;;
	esac
	shift
done

if [ "${DAY}" == "test" ]; then
	/code/test.sh
	exit ${?}
fi;

if ! [[ "${DAY}" =~ ^[0-9]+$ ]]; then
	echo 'Invalid Day: '${DAY};
	exit 1;
fi;

if [ ! -e "/code/${DAY}/run.php" ]; then
	echo 'Unknown Day: '${DAY};
	exit 1;
fi;

if [ ! -e "/code/${DAY}/${FILE}" ]; then
	echo 'Unknown File: '${FILE};
	exit 1;
fi;

if [ "${TIME}" = "1" ]; then
	export TIMED=1
	time php /code/${DAY}/${FILE} ${@}
else
	php /code/${DAY}/${FILE} ${@}
fi;
