FROM php:7.2-cli

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER nobody

CMD /entrypoint.sh
VOLUME /code
