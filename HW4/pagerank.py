import networkx as nx
# fh=open("/Users/pengyuchen/NYD/edgeList.txt")
fout=open("/Users/pengyuchen/NYD/pagerank.txt", "w")

G=nx.read_edgelist("/Users/pengyuchen/NYD/edgeList.txt",create_using=nx.DiGraph())


result= nx.pagerank(G,alpha=0.85, personalization=None,
max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)

for item in result:
    # fout.write("/Users/pengyuchen/NYD/NYD/%s=%f\n"%(item,result[item]))
    fout.write("/Users/pengyuchen/NYD/NYD/"+item+"="+str(result[item])+"\n")
fout.close()
