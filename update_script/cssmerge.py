#!/usr/bin/env python

"""
Merge CSS files.

This is checked into BW Rox trunk, but not actually executed from
here. Right now it's just a convenient place to store the script.
"""

import re

filename = "bw_yaml.css";
lines = file(filename).readlines()
out_file = file(filename + ".compact", 'w')

for l in lines:
    matches = re.search("@import url\((.*?)\)", l)
    if matches and not l.find('/*') > -1:
        print matches.groups()
        import_file = matches.groups()[0]
        for import_line in file(import_file).readlines():
            if import_line.find('@charset "UTF-8"') > 0:
                print import_line
            else:
                out_file.write(import_line)
    else:
        out_file.write(l)

# at some point we could use cssutils to compress the CSS
# see http://code.google.com/p/cssutils/

out_file.close()

