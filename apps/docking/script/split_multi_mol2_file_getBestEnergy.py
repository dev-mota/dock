#!/usr/bin/env python2.4
#

import os, sys



if __name__ == '__main__':
    import sys
    import getopt


    def usage():
        "Print helpful, accurate usage statement to stdout."
        print("Usage: split_multi_mol2_file.py -i filename")
        print
        print("    Description of command...")
        print("         -i     multi_mol2_filename")
        print("         -n     number of ligands to split")
        print("    Optional parameters:")
        print("        [-v]    verbose output")



    # process command arguments
    try:
        opt_list, args = getopt.getopt(sys.argv[1:], 'i:n:vh')
    except getopt.GetoptError as err:
        print("split_multi_mol2_file.py: {0}".format(err))
        usage()
        sys.exit(2)

    # initialize required parameters
    #-i: multi_mol2_filename
    multi_mol2_filename =  None
    verbose = False
    num = 1

    rank = 0

    #'i:vh'
    for o, a in opt_list:
        #print "o=", o, " a=", a
        if o in ('-i', '--i'):
            multi_mol2_filename = a
            if verbose: print('set multi_mol2_filename to ', a)
        if o in ('-n', '--n'):
            num = int(a)
            if verbose: print('set number of ligands to ', a)
        if o in ('-v', '--v'):
            verbose = True
            if verbose: print('set verbose to ', True)
        if o in ('-h', '--'):
            usage()
            sys.exit()

    if not  multi_mol2_filename:
        print('split_multi_mol2_file: multimol2 file name must be specified.')
        usage()
        sys.exit()


    #step one: open multiple mol2 file and get all the lines
    fptr = open(multi_mol2_filename)
    alllines = fptr.readlines()
    fptr.close()
    #step two: set up counter for filenames, molecule counter and flag
    molctr = 0
    #inmol = 0
    #step three: process alllines
    in_molecule = False
    
    for i in range(len(alllines)):	# log all top conformations in the multimol2 file
    #for i in range(2):	
        line = alllines[i]
        #optr.write(line)
        if line.find("@<TRIPOS>MOLECULE")==0: # check for beginning of mol
            rank +=1
            if rank > num:
                break
                        
            if rank == num:
                zid = multi_mol2_filename.strip()
                label = zid.split('.')
                filename = label[0] + "_top" + str(rank) + '.mol2'
                optr = open(filename, 'w')
                molctr += 1

        if rank == num:
            optr.write(line)
            
    if verbose: print('split %s into %d files',multi_mol2_filename, molctr)
    
    optr.close()
    if verbose: print("closed ", filename)
