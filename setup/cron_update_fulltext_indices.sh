#!/bin/bash

# reindex the forums fulltext index and rotate to use it
/usr/bin/indexer --rotate --quiet forums
/usr/bin/indexer --rotate --quiet suggestions