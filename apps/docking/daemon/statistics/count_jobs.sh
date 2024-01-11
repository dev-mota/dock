#bin/bash

#GUIDELINES:#
# ./count_jobs.sh "year"
# The results will be given in a separated file named "count_jobsYEAR.txt" with the job names in the first column followed by the number of ligands per job.
# The last line of the file corresponds to the total number of jobs followed by the total number of ligands.

#year=$1

jobs_folder=/var/www.new/dockthorV2/apps/docking/daemon/jobs
now="$(date +'%d-%m-%Y')"
year="$(echo $now | cut -d "-" -f3)"
echo "Searching for jobs in the year" $year
#year=2019
#rm count_jobs$now.txt

dir=$(ls -lht --time-style=long-iso $jobs_folder | grep $year"-" | grep -v GMMSB | grep -v gmmsb | grep -v Gmmsb | grep -v vsisa | grep -v urok | awk '{print $8}')
njobs=`ls -lht --time-style=long-iso $jobs_folder | grep $year"-" | grep -v GMMSB | grep -v gmmsb | grep -v Gmmsb | grep -v vsisa | grep -v urok | wc -l`

for job in $dir
do
	ligands=`ls $jobs_folder/$job/LIGAND/*.top | wc -l`
	totalligands=$(($totalligands + $ligands))
	echo $job ";" $ligands >> count_jobs$now.txt
#	echo $job
done

cwd=/var/www.new/dockthorV2/apps/docking/daemon/statistics
mean=$(($totalligands / $njobs))
echo $now "statistics:" $njobs "jobs submitted a total of" $totalligands "ligands, resulting in a mean of" $mean "ligand(s) per job." > $cwd/count_jobs$now.txt
#echo $now "statistics:" $njobs "jobs submitted a total of" $totalligands "ligands, resulting in a mean of" $mean "ligand(s) per job."
#echo $njobs ";" $totalligands >> count_jobs$now.txt
