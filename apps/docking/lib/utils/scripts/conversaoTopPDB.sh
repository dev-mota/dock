#! /bin/bash 

# name=`echo $2 | rev | cut -d '/' -f 1 | rev | cut -d '.' -f 1`
name=`echo $1 | cut -d '.' -f 1`
echo $name
outpath=`pwd $2`
echo $outpath

# awk -v 'RS=\n\n' '1;{exit}' $1 > ${name}_conv.tmp
awk -v 'RS=\n\n' '1;{exit}' $1 > $outpath/${name}_conv.tmp

# awk 'NF>2{printf("ATOM  %5s  %-3s %3s %1s%4s    %8.3f%8.3f%8.3f%6.2f   %3s          %2s  \n", $2,$1,"MOL","X","1",$5,$6,$7,$4,$3,$1)}' ${name}_conv.tmp > ${name}_mmff.pdb
# awk 'NF>2{printf("ATOM  %5s  %-3s %3s %1s%4s    %8.3f%8.3f%8.3f%6.2f %5.2f           %2s  \n", $2,$1,"MOL","X","1",$5,$6,$7,$4,$3,$1)}' $outpath/${name}_conv.tmp > ${name}_mmff.pdb
awk 'NF>2{printf("ATOM  %5s  %-3s %3s %1s%4s    %8.3f%8.3f%8.3f%6.2f %5.2f           %2s  \n", $2,$1,"MOL","X","1",$5,$6,$7,$4,$3,$1)}' $outpath/${name}_conv.tmp > $2

rm $outpath/${name}_conv.tmp