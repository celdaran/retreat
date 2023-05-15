export DATE=$(date +%Y%m%d)
export DATETIME=$(date +%Y%m%d-%H%M)

echo "Date is ${DATE}"
echo "Date and time is ${DATETIME}"

echo "Running Option 3"
php -f run.php -- --expense option3r --asset option3 --income income1 -d 373 > .tmp/"${DATETIME}".option3r.csv

grep "2056-01" .tmp/"${DATETIME}".option* | grep ",,"
