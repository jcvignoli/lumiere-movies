#!/bin/bash

# Script to be run on first installation

# Build dist folder
sh ../node_modules/.bin/gulp build

# Copy config and git and github files
sh ./copy-files

