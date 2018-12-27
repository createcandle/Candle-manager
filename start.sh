#!/bin/bash
clear
echo "Starting simple PHP server on port :8023"
php -S 0.0.0.0:8023
#stunnel4 -d 8024 -r 8023
