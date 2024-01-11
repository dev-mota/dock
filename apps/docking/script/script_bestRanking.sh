cd $1

rankedCompounds=$(cat bestranking.csv | grep "ligand" | cut -d ";" -f1)
echo "# Top-energy pose of compounds ranked according to the score (given in kcal/mol)." > bestranking.mol2

for compound in $rankedCompounds
do

    # get the TOP_CONF pose
    python3 $2 -i 'result-'$compound.mol2 -n 1
   
    cat 'result-'$compound'_top1'.mol2 >> bestranking.mol2
    rm 'result-'$compound'_top1'.mol2

done

cd ..




#$ ls *_run* | awk -F "_run_" '{print $1}' | sort -d -u > validFiles.txt

#Lista todos os arquivos _run_ , ordena e remove os duplicados, salvando no arquivo validFiles.txt

#$ ls *.top | awk -F ".top" '{print $1}' | sort -d > allFiles.txt

#Lista todos os .top e ordena, salvando no arquivo allFiles.txt sem a extensão

#$ awk 'NR==FNR{a[$0];next} !($0 in a)' validFiles.txt allFiles.txt

#Remove todas as linhas iguais a validFiles.txt em allFiles.txt, salvando como invalidFiles.txt
