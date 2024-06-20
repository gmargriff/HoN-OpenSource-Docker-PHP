FROM node:20-alpine3.19

RUN mkdir /app
WORKDIR /app

RUN apk update
RUN apk upgrade

RUN apk add git
RUN apk add zip

CMD ["sh", "-c", "/app/build.sh"]