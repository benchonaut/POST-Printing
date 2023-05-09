git log --pretty=format:"%h%x09%an%x09%ad%x09%s"  >> _COMMIT_HISTORY.log
git squash master
git reset $(git commit-tree HEAD^{tree} -m "A new start");
git add -A 
git commit -m "init"
git push --force
