#!/bin/bash

BASEIMAGE=shanemcc/aoc-2018-05
BASEDOCKERFILE="Dockerfile"

IMAGE=${BASEIMAGE}
DOCKERFILE=${BASEDOCKERFILE}
FORCEBUILD="0";
SHELL="0";

while true; do
	case "$1" in
		--jit|--hhvm)
			IMAGE=${BASEIMAGE}-${1/--/}
			DOCKERFILE=${BASEDOCKERFILE}-${1/--/}
			;;
		--build)
			FORCEBUILD="1";
			;;
		--shell)
			SHELL="1";
			;;
		*)
			break;
			;;
	esac
	shift
done

docker image inspect $IMAGE >/dev/null 2>&1
if [ $? -ne 0 -o ${FORCEBUILD} = "1" ]; then
	echo "One time setup: building docker image ${IMAGE}..."
	cd docker
	docker build . -t $IMAGE --file ${DOCKERFILE}
	cd ..
	echo "Image build complete."
fi

if [ "${SHELL}" = "1" ]; then
	docker run --rm -it -v $(pwd):/code $IMAGE bash
else
	docker run --rm -it -v $(pwd):/code $IMAGE /entrypoint.sh $@
fi;
