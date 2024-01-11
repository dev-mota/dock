# -*- coding: utf-8 -*-
#
# makeVinaCommandFromProtein.py -- automatically make a valid Vina command from a PDB file and a ligand (name)
#
# Author: Jason Vertrees
#	  Isabella Guedes	5/2015
# Date  : 2/2009
#
from __future__ import print_function

from pymol import cmd
from sys import argv
from os import path

# try to keep PyMOL quiet
cmd.feedback("disable","all","actions")
cmd.feedback("disable","all","results")
 
# prepare some output names
#protName= path.basename(argv[-2])
ligName = path.basename(argv[-1])
#outName = protName.split(".")[0] + "." + ligName.split(".")[0] + ".docked.pdbqt"
#logName = protName.split(".")[0] + "." + ligName.split(".")[0] + ".log"

#print protName 
# very unsafe commandline checking; needs updating
#cmd.load(argv[-2], protName)
cmd.load(argv[-1], ligName)
 
# remove the ligand before calculating the center of mass
#cmd.delete(ligName)
 
# load center of mass script
#cmd.do("run /home/isabella/workspace/tests_rescoring/scripts/com.py")
 
# calculate the center of mass and extents
#(comX, comY, comZ) = COM(ligName)
((maxX, maxY, maxZ), (minX, minY, minZ)) = cmd.get_extent(ligName)
(comX, comY, comZ) = (maxX-((maxX-minX)/2), maxY-((maxY-minY)/2), maxZ-((maxZ-minZ)/2))

# maximum number of points equals to 729000; cubicRootPoints - 1 = 89
cubicRootPoints = 89

# extra size on each dimension
tolerance = 0

# maximum ligand size (24A at least in one dimension) with rstep = 0.25
limitSize = 12

dimX = abs(maxX-minX)
dimY = abs(maxY-minY)
dimZ = abs(maxZ-minZ)

dimensions = (dimX, dimY, dimZ)

# dimension with the highest value
maxValue = max(dimensions)
gridSize = maxValue/2

# check the size of the ligand
if gridSize > limitSize:
	if gridSize > 20:
		gridSize = 20
		discretization = 0.42
	else:
		discretization = maxValue/cubicRootPoints
else:
	discretization = 0.25

# print the command line
#print "vina --receptor "+protName+"qt --ligand "+ligName+"qt --center_x ", str(comX), " --center_y ", str(comY)," --center_z ", str(comZ), " --size_x ", str(abs(maxX-minX)), " --size_y ", str(abs(maxY-minY)), " --size_z ",str(abs(maxZ-minZ))," --all", outName , " --exhaustiveness 200 --log ", logName, " \n"

#print the grid configuration
#print "gridSize:", str((maxValue+tolerance)/2), " \n", "X:", str(comX), " \n", "Y:", str(comY), " \n", "Z:", str(comZ), " \n", "rstep:", str(discretization), " \n"

#print ("gridSize: " "%.4f" % (gridSize))
#print ("X: " "%.4f" % (comX))
#print ("Y: " "%.4f" % (comY))
#print ("Z: " "%.4f" % (comZ))
#print ("rstep: " "%.4f" % (discretization))

# generating grid.conf file
with open('grid.conf', 'w') as f:
        f.write("gridSize: " "%.4f" % (gridSize) + "\n")
        f.write("X: " "%.4f" % (comX) + "\n")
        f.write("Y: " "%.4f" % (comY) + "\n")
        f.write("Z: " "%.4f" % (comZ) + "\n")
        f.write("rstep: " "%.4f" % (discretization) + "\n")
        f.write("centerX_min-max: " "%.4f;%.4f" % (maxX,minX) + "\n")
        f.write("centerY_min-max: " "%.4f;%.4f" % (maxY,minY) + "\n")
        f.write("centerZ_min-max: " "%.4f;%.4f" % (maxZ,minZ) + "\n")