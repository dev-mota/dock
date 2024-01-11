#! /bin/bash 

cd $1


if [ "$2" == "new" ]
then
    molecules=$(ls new_*.sdf)
else
    molecules=$(ls *.sdf)
fi

if [ ! -d "obprop" ]
then
    mkdir obprop
else
    rm obprop/*
fi

rm ../obprop.csv

#echo "name formula mol_weight exact_mass canonical_SMILES InChI num_atoms num_bonds num_residues sequence num_rings logP PSA MR" > ../tmp.csv
echo "name formula mol_weight exact_mass canonical_SMILES InChI num_atoms num_bonds num_residues sequence num_rotors num_rings logP PSA MR" > ../tmp.csv

for molecule in $molecules
do
    echo $molecule
    logname=`echo $molecule | cut -d '/' -f 1`

#     echo $logname

    obprop "$molecule" > obprop/$logname.prop

    properties=`awk '{printf("%s ", $2)}' obprop/$logname.prop`
    echo $properties >> ../tmp.csv
done

#sed -e 's/\s/;/g' ../tmp.csv > ../obprop.csv
sed -e 's/\s/;/g' ../tmp.csv > obprop/obprop.csv
rm ../tmp.csv