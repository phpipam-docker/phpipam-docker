# phpIPAM Apache container

# STAGE 0

FROM garyallan/phpipam-base:latest

ARG BUILD_DATE
ARG VCS_REF
ARG VERSION
ARG BRANCH

# Clone phpIPAM application
RUN apk add --no-cache git && /install_phpipam $BRANCH $VCS_REF

# STAGE 1

FROM garyallan/phpipam-base:latest

ARG BUILD_DATE
ARG VCS_REF
ARG VERSION
ARG BRANCH

RUN apk add nano graphviz php83-pecl-xhprof

LABEL org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="phpIPAM Apache Container" \
      org.label-schema.description="phpIPAM is an open-source web IP address management application (IPAM). Its goal is to provide light, modern and useful IP address management." \
      org.label-schema.url="https://phpipam.net/" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vcs-url="https://github.com/phpipam/phpipam/" \
      org.label-schema.vendor="phpIPAM" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0" \
      org.opencontainers.image.source="https://github.com/phpipam-docker/phpipam-docker" \
      maintainer="Gary Allan <github@gallan.co.uk>"

WORKDIR /

# Copy phpIPAM
COPY --from=0 /phpipam/ /phpipam/

COPY root/ /

# Run Apache
EXPOSE 80

ENTRYPOINT ["/sbin/tini", "--"]
CMD /start_apache2
