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
echo " Start building Linux binaries"
echo "-------------------------------------------"
npm run make -- --platform linux

echo "-------------------------------------------"
echo " Fixing permissions for output files"
echo "-------------------------------------------"
chmod 777 -R /app/out/

echo ""
echo ""
echo "-------------------------------------------"
echo " Done building binaries"
echo "-------------------------------------------"
exit 0