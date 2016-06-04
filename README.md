# BeWelcome Rox

[![Build Status](https://travis-ci.org/BeWelcome/rox.svg?branch=bootstrap3)](https://travis-ci.org/BeWelcome/rox)

Check INSTALL for installation instructions.

You probably want to get started by checking out the different applications
in build/.  htdocs/bw/ is deprecated and the code needs to be rewritten in
build/.


Useful links:
* Developer space on Trac: http://trac.bewelcome.org/
* Git crash course: http://git.or.cz/course/svn.html


Standards we try to follow:
* PEAR coding standard: http://pear.php.net/manual/en/standards.php
* W3C strict XHTML 1.0: http://www.w3.org/TR/xhtml1/


Documentation for the code is available. Check

http://trac.bewelcome.org/wiki/RoxDocumentation

You can also generate an up-to-date version using PhpDocumentor (http://www.phpdoc.org)

Use the following command to generate the documentation (starting in the root of the code)

phpdoc -p -d build,modules,htdocs,roxlauncher,tools -title="Rox Documentation" -t doc --parseprivate --validate

(This will take a while.)
