#bin/bash

#GUIDELINES:#
# ./count_jobs.sh "year"
# The results will be given in a separated file named "count_jobsYEAR.txt" with the job names in the first column followed by the number of ligands per job.
# The last line of the file corresponds to the total number of jobs followed by the total number of ligands.

year=$1

rm count_jobs$year.txt

dir=$(ls -lht --time-style=long-iso jobs/ | grep $year | grep -v GMMSB | grep -v gmmsb | grep -v Gmmsb | grep -v vsisa | grep -v urok | awk '{print $8}')
njobs=`ls -lht --time-style=long-iso jobs/ | grep $year | grep -v GMMSB | grep -v gmmsb | grep -v Gmmsb | grep -v vsisa | grep -v urok | wc -l`

for job in $dir
do
	ligands=`ls jobs/$job/LIGAND/*.top | wc -l`
	totalligands=$(($totalligands + $ligands))
	echo $job ";" $ligands >> count_jobs$year.txt
#	echo $job
done

mean=$(($totalligands / $njobs))
echo $year "statistics:" $njobs "jobs submitted a total of" $totalligands "ligands, resulting in a mean of" $mean "ligand(s) per job."
echo $njobs ";" $totalligands >> count_jobs$year.txt
