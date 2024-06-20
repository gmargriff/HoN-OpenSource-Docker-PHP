#! /bin/sh

echo "-------------------------------------------"
echo " Install npm dependencies"
echo "-------------------------------------------"
npm install

echo "-------------------------------------------"
echo " Start building windows binaries"
echo "-------------------------------------------"
npm run make -- --platform win32

echo "-------------------------------------------"
echo " Start building MacOS binaries"
echo "-------------------------------------------"
npm run make -- --platform darwin

echo "-------------------------------------------"
echo "Done building Linux binares"
echo "-------------------------------------------"
npm run make -- --platform linux
exit 0