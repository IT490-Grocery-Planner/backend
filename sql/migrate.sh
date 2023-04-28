#!/bin/bash
allThreads=(1 2 4 8 16 32 64 128)
for file in *.sql; do
    sudo mysql < "$file"
done
