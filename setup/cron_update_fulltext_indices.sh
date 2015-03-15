#!/bin/bash

# reindex the forums fulltext index and rotate to use it
/usr/bin/indexer --rotate forums
/usr/bin/indexer --rotate suggestions