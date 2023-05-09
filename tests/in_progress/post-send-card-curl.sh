base64 -w0 card.pdf > /tmp/.test.card.pdf.base64
curl -X POST -H "Content-type: application/x-www-form-urlencoded" -F "client=1" -F "type=card" -F "file=@/tmp/.test.card.pdf.base64" http://127.0.0.1/print.php -kLv
