cd $1

cat bestranking.csv | grep "ligand" | head -n 100 | cut -d ';' -f1 > snap.txt


