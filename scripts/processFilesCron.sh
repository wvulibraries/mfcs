#!/bin/bash

# Source the environment variables
source /home/mfcs.lib.wvu.edu/scripts/env.sh

# Log environment variables for debugging
# printenv > /tmp/log/env.log

# Change to the correct directory
cd /home/mfcs.lib.wvu.edu/public_html/crons/

# Run the PHP script
/usr/local/bin/php processFiles.php

