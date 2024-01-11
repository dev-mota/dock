import re
import sys
import atomium

def distinctAtoms(file):
    model = re.findall('ATOM.*', file.read())

    return model

def distinctModel(protein):
    
    result=""

    listAtoms="-1"
    
    isMultipleModels=False
    numModels=1
    withoutChain=False

    for m in protein:

        match=re.search(r"ATOM\s+\d+\s+[a-z\s0-9A-Z]+\s+([A-Z]).*",m)
        match2=re.search(r"(ATOM\s+\d+\s+[a-zA-Z]+\s+[a-zA-Z]+)\s+([0-9]+)(.*)",m)
        
        if match2:
            currentAtom="X"
            result+=match2.group(1)+ " " + currentAtom + ''.ljust(4-len(match2.group(2))) + match2.group(2) + match2.group(3) + "\n"
            withoutChain=True
        
        if match and not withoutChain:
        
            currentAtom=match.group(1)
            current=re.search(r".*"+currentAtom+"$",listAtoms)
        
            if not current:
                already=re.search(r""+currentAtom,listAtoms)
                if listAtoms!= "-1" and not already and not isMultipleModels:
                    result+="TER\n"
        
            if not already:
                listAtoms+=currentAtom
                if not isMultipleModels:
                    result+=match.group()+"\n"
            else:
                isMultipleModels=True
                numModels+=1
                listAtoms="-1"
            

    result+="TER\n"
    
    print("Número de Modelos:", numModels)
        
    return result


protein =""

f = open(sys.argv[1], 'r')

match = re.search(r".pdb$", sys.argv[1])

if match:
    protein=distinctAtoms(f)
    protein=distinctModel(protein)
    with open(sys.argv[1], 'w') as f:
        sys.stdout = f
        print(protein)
    sys.stdout = sys.__stdout__

    modelPdb = atomium.open(sys.argv[1])

    for model in modelPdb.models:
        print(model.center_of_mass)
        print(model.chains())
        #print(model.atoms())

exit
