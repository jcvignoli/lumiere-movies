#!/bin/bash

# Script to be run on first installation

# Build dist folder
cd .. && npm run build

# Copy config and git and github files
cd scripts && sh ./copy-files

