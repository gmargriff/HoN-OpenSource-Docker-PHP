FROM node:20-alpine3.19

RUN mkdir /app
WORKDIR /app

RUN apk update
RUN apk upgrade

RUN apk add git
RUN apk add zip

RUN npm install

RUN npm run make -- --platform win32
RUN npm run make -- --platform darwin
CMD ["npm", "run", "make", "--", "--platform", "linux"]