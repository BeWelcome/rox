# for customization of the aspell plugin
# 

default:
	rm -f *.rws
	rm -f *.multi
	ln -s /usr/local/lib/aspell/en.multi en.multi
	ln -s /usr/local/lib/aspell/en-only.rws en-only.rws
	ln -s /usr/local/lib/aspell/en_US-only.rws en_US-only.rws
	ln -s /usr/local/lib/aspell/en_GB-only.rws en_GB-only.rws	
	aspell --lang=en create master /usr/local/lib/aspell/en_US-custom.rws < custom.dic
	ln -s /usr/local/lib/aspell/en_US-custom.rws en_US-custom.rws

