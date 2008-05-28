#!/usr/bin/env python

"""
Merge CSS files.

This is checked into BW Rox trunk, but not actually executed from
here. Right now it's just a convenient place to store the script.
"""

import re
import os

filename = "bw_yaml.css";
lines = file(filename).readlines()
out_file = file(filename + ".compact", 'w')

for l in lines:
    matches = re.search("@import url\((.*?)\)", l)
    if matches and not l.find('/*') > -1:
        print matches.groups()
        import_file = matches.groups()[0]
        import_dir = os.path.dirname(import_file)
        for import_line in file(import_file).readlines():
            if import_line.find('@charset "UTF-8"') > 0: # should become re.sub
                print "Skip ". import_line
                pass
            elif import_line.find('url(') > 0:
                # TODO: relative paths should be fixed
                print import_line
                url_matches = re.search('url\(["\s]*(.*?)[\s"]*\)', import_line)
                if url_matches:
                    print url_matches.groups()
            else:
                out_file.write(import_line)
    else:
        out_file.write(l)

# at some point we could use cssutils to compress the CSS
# see http://code.google.com/p/cssutils/

out_file.close()

