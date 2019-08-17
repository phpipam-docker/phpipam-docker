# phpIPAM Apache container 

FROM phpipam/phpipam-base:latest

ARG BUILD_DATE
ARG VCS_REF
ARG VERSION
ARG BRANCH

LABEL org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="phpIPAM Apache Container" \
      org.label-schema.description="phpIPAM is an open-source web IP address management application (IPAM). Its goal is to provide light, modern and useful IP address management." \
      org.label-schema.url="https://phpipam.net/" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vcs-url="https://github.com/phpipam/phpipam/" \
      org.label-schema.vendor="phpIPAM" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0" \
      maintainer="Gary Allan <github@gallan.co.uk>"

WORKDIR /phpipam

# Download phpIPAM
RUN git clone -b $BRANCH --recursive --depth 1 https://github.com/phpipam/phpipam.git /phpipam \
    && cd /phpipam \
    && git config core.fileMode false \
    && ln -s /phpipam/config.docker.php /phpipam/config.php \
    && chmod -R u=rw,go=r /phpipam \
    && chmod -R a+X /phpipam \
    && find /phpipam -type d -name upload -exec chmod a=rwX {} \; \
    && chmod a=rwX /phpipam/css/images/logo

# Run Apache
EXPOSE 80

ENTRYPOINT ["/sbin/tini", "--"]
CMD /start_apache2
