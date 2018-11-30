#!/bin/bash

DAY="${1}"
TIME=0;
shift;

if [ "${DAY}" = "--time" ]; then
	TIME="1"
	DAY="${1}"
	shift;
fi;

if ! [[ "${DAY}" =~ ^[0-9]+$ ]]; then
	echo 'Invalid Day: '${DAY};
	exit 1;
fi;

if [ ! -e "/code/${DAY}/run.php" ]; then
	echo 'Unknown Day: '${DAY};
	exit 1;
fi;

if [ "${TIME}" = "1" ]; then
	time php /code/${DAY}/run.php ${@}
else
	php /code/${DAY}/run.php ${@}
fi;
