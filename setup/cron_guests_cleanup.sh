#!/bin/bash
echo 'DELETE FROM guestsonline WHERE updated < NOW() - INTERVAL 2 hour' | mysql BW_MAIN
