curl https://raw.githubusercontent.com/dwyl/english-words/master/words_alpha.txt | awk 'length($0) > 5'| awk 'length($0) < 11'|sed 's/\r//g'
