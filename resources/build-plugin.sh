#!/bin/bash
#Script to create a build of the plugin

# exit when any command fails
set -e

# print error message (red)
error() {
  echo -e "\033[38;5;9m[build-plugin] $1\033[0m"
}
# print info message (blue)
info() {
  echo -e "\033[38;5;75m[build-plugin] $1\033[0m"
}

###################
info "Creating plugin build"

# clean up
rm -rf node_modules

# minimal node_modules
yarn install --prod
# keep a copy
cp -r node_modules node_modules_prod

# install to get wp-scripts
yarn install
# build block (needs wp-scripts)
yarn run build
# create plugin zip (needs wp-scripts)
yarn run plugin-zip

# unzip to add prod node_modules
unzip DicomSupport.zip -d build/DicomSupport
# move prod node_modules to plugin
mv node_modules_prod build/DicomSupport/node_modules

# clean up
rm DicomSupport.zip

###################
info "Done creating plugin build in 'build/DicomSupport'"
