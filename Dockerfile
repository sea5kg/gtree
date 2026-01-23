FROM debian:12

LABEL "maintainer"="Evgenii Sopov <mrseakg@gmail.com>"
LABEL "repository"="https://github.com/freehackquest/fhq-server"

RUN apt-get update && \
    apt-get install -y \
    make \
    cmake \
    gcc \
    g++ \
    curl \
    pkg-config \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libpng-dev \
    apt-utils

# RUN sed -i -e "s/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/" /etc/locale.gen && \
#     echo 'LANG="en_US.UTF-8"'>/etc/default/locale && \
#     dpkg-reconfigure --frontend=noninteractive locales && \
#     update-locale LANG=en_US.UTF-8

COPY . /root/source-code

WORKDIR /root/source-code

RUN cmake -H. -B./tmp/release -DCMAKE_BUILD_TYPE=Release
RUN cmake --build ./tmp/release --config Release

# TODO tests will be later
# RUN  cd ./tmp/release && ctest --output-on-failure

RUN cp -rf /root/source-code/gtree /usr/bin/gtree

EXPOSE 10555
CMD gtree
