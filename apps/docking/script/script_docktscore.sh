################################################################################
#   Script dockAnalizing.sh - DockThor v.4.0.0                                 #
#   Script subANALYZE.sh - DockThor v.4.0.0                                    #
#   Script que executa o dtstatistic e o DockTRScore para um determinado       #
#   ligantes.                                                                  #
#                                                                              #
#   Release Notes:                                                             #
#                                                                              #
#       - Implementação da execução de pós processamento DockTRScore com       #
#         varredura de parâmetros;                                             #
#                                                                              #
#   Desenvolvido por: Marcelo Monteiro Galheigo                                #
#   Versão: 4.0.0                                                              #
#   Janeiro de 2018                                                            #
################################################################################
#!/bin/bash

export DTSTATISTICS_LIBDIR=/prj/prjdma/dockthor/dockthor_programs/dockthor-hpc/dtstatistic/dtstatisticv5.py
export TOOLS_DIR=/prj/prjdma/dockthor/dockthor_programs/dockthor-hpc/docktscore
export ALGORITHMS_DIR=/prj/prjdma/dockthor/dockthor_programs/dockthor-hpc
export MSMS=/prj/prjdma/dockthor/dockthor_programs/dockthor-hpc/docktscore/msms_i86_64Linux2_2.6.1


export ligandId=$2
export outputDir=$3
export dtLabel="result-"
export numOfBindModes=$4
export rmsd=$5
export referenceFileParameter="$6 $7"

cd ${outputDir}

outputDir=`pwd`

export docktscoreDir="${outputDir}/docktscore-${ligandId}"

#Running dtstatistic
#COMMAND="dtstatistic -l ${ligandId} -t -n ${numOfBindModes} -c ${rmsd} -o ${dtLabel}${ligandId} $referenceFileParameter"
COMMAND="python3 ${DTSTATISTICS_LIBDIR} -l ${ligandId} -t -n ${numOfBindModes} -c ${rmsd} -o ${dtLabel}${ligandId} $referenceFileParameter"
$COMMAND

#Running DockTScore
mkdir ${docktscoreDir}

cp ${outputDir}/${dtLabel}${ligandId}.* ${docktscoreDir}

cd ${docktscoreDir}


export protein=`ls -1 $1`
ln -s ${protein} .
protein=`basename ${protein}`

proteinLabel=`echo $protein | sed 's#.in$##'`

awk 'NF>1{printf("ATOM  %5s %-4s %3s %1s%4s    %8.3f%8.3f%8.3f %5.2f %5.2f          %2s  \n",$1, length($13)>3 ? $13 : " "$13 ,$14,$15,$16,$3,$4,$5,$12,$2,substr($13,1,1))}' ${protein} > ${proteinLabel}_prep.pdb

protein=${proteinLabel}_prep.pdb

python3 ${ALGORITHMS_DIR}/docktscore/scripts/split_multi_mol2_Nposes.py -i ${dtLabel}${ligandId}.mol2 -n ${numOfBindModes}
#python ${ALGORITHMS_DIR}/DockTSCore/versions/v_007_000_000/scripts/split_multi_mol2_Nposes.py -i ${dtLabel}${ligandId}.mol2 -n ${numOfBindModes

top=1

while [ "$top" -le "${numOfBindModes}" ]
  do

      	${ALGORITHMS_DIR}/docktscore/scripts/DockTScore.sh -p ${protein} -l ${dtLabel}${ligandId}_top$top.mol2 -o ${dtLabel}${ligandId}_top$top -s ${ALGORITHMS_DIR}/docktscore/coeff/general2764.coeff 2>/dev/null &
	top=$((top + 1))
done

wait

line=1

#cp ${outputDir}/${dtLabel}${ligandId}.log /tmp/${dtLabel}${ligandId}.orig


top=1

while [ "$top" -le "${numOfBindModes}" ]
  do
      line=$((line + 1))
      dockTScore=`cat ${dtLabel}${ligandId}_top$top.score | grep "DockTScore:" | cut -d ' ' -f2`
      
      if [ -n "$dockTScore" ]
       then
#           echo -e "TOP: ${top}\tDockTScore: ${dockTScore}"
           sed -i ''${line}'s#\-*[0-9]*\.*[0-9]*\( *\)$#'${dockTScore}'\1#' ${outputDir}/${dtLabel}${ligandId}.log
      fi

      top=$((top + 1))
done

rm -rf ${docktscoreDir}


