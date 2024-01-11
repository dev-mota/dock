#/bin/bash

total=`find ./jobs/ -maxdepth 1 -type d | wc -l`
gmmsb=`find ./jobs/ -maxdepth 1 -type d -name "gmmsb*"| wc -l`
GMMSB_m=`find ./jobs/ -maxdepth 1 -type d -name "GMMSB*"| wc -l`
gmsb=`find ./jobs/ -maxdepth 1 -type d -name "gmsb*"| wc -l`

total=`echo $(($total-$gmmsb-$GMMSB_m-$gmsb))`
echo $total
