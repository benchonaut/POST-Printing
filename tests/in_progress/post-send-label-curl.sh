curl -X POST -H "Content-type: application/x-www-form-urlencoded" -d "client=1&type=label&file=$(base64 -w0 label.pdf)" http://127.0.0.1/print.php -kLv
