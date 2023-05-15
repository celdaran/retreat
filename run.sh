export DATE=$(date +%Y-%m-%d)

echo "Date is ${DATE}"

echo "Running Option 1"
php -f run.php -- --expense option1 --asset option1 --income income1 -d 385 > .tmp/"${DATE}".option1.csv

echo "Running Option 2"
php -f run.php -- --expense option2 --asset option2 --income income1 -d 382 > .tmp/"${DATE}".option2.csv

echo "Running Option 3"
php -f run.php -- --expense option3 --asset option3 --income income1 -d 373 > .tmp/"${DATE}".option3.csv

echo "Running Option 4"
php -f run.php -- --expense option4 --asset option4 --income income1 -d 366 > .tmp/"${DATE}".option4.csv

echo "Running Option 5"
php -f run.php -- --expense option5 --asset option5 --income income1 -d 360 > .tmp/"${DATE}".option5.csv

grep "2056-01" .tmp/"${DATE}".option* | grep ",,"
