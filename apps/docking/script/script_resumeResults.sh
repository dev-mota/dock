cd $1

results=$(ls result-*.log | cut -d "." -f1)

#echo "Name;Score;T. Energy;I. Energy;vdW Energy;Electrostatic Energy;RMSD" > temp.csv
echo "Name;Score;T. Energy;I. Energy;vdW Energy;Electrostatic Energy" > temp.csv

for result in $results
do

    compound_name=`echo $result | awk -F "result-" '{print $2}'`
    score=`sed '2p; d' $result.log | awk '{print $9}'`
    total_energy=`sed '2p; d' $result.log | awk '{print $3}'`
    intermolecular_energy=`sed '2p; d' $result.log | awk '{print $4}'`
    vdW=`sed '2p; d' $result.log | awk '{print $5}'`
    coulomb=`sed '2p; d' $result.log | awk '{print $6}'`
    #rmsd=`sed '2p; d' $result.log | awk '{print $8}'`
    
#     echo $compound_name $score $total_energy $intermolecular_energy $vdW $coulomb $rmsd
    
    #temp=$compound_name";"$score";"$total_energy";"$intermolecular_energy";"$vdW";"$coulomb";"$rmsd
    temp=$compound_name";"$score";"$total_energy";"$intermolecular_energy";"$vdW";"$coulomb
    
    echo $temp >> temp.csv

done

# Sort compounds by Score (-g is used to sort float values)
sort --field-separator=';' -g -k2 -k3 temp.csv > bestranking.csv
rm temp.csv

cd ..




#$ ls *_run* | awk -F "_run_" '{print $1}' | sort -d -u > validFiles.txt

#Lista todos os arquivos _run_ , ordena e remove os duplicados, salvando no arquivo validFiles.txt

#$ ls *.top | awk -F ".top" '{print $1}' | sort -d > allFiles.txt

#Lista todos os .top e ordena, salvando no arquivo allFiles.txt sem a extensão

#$ awk 'NR==FNR{a[$0];next} !($0 in a)' validFiles.txt allFiles.txt

#Remove todas as linhas iguais a validFiles.txt em allFiles.txt, salvando como invalidFiles.txt
