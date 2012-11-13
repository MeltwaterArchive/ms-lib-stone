#!/bin/bash

# dev-setup.sh
#       Install this project into a dev environment, to speed up writing
#       and debugging
#
# Author    Stuart Herbert
#           (stuart.herbert@datasift.com)
#
# Copyright (c) 2012 MediaSift Ltd
#           All rights reserved
#
# ========================================================================

INSTALL_PATH=/usr/share/php/DataSift/Stone
SRC_PATH=`pwd`/src/main/php/DataSift/Stone

function info() {
    echo "$1"
}

function error() {
    echo "*** error: $1"
    exit 1
}

USERID="`id -u`"
if [[ $USERID != 0 ]]; then
    error "Must be root to run this script, sorry. Try sudo?"
fi

# remove any existing install
if [[ -e $INSTALL_PATH ]] ; then
    info "Removing existing install in $INSTALL_PATH"
    if [[ -L $INSTALL_PATH ]] ; then
        rm $INSTALL_PATH || error "Unable to remove existing install, sorry"
    else
        rm -rf $INSTALL_PATH || error "Unable to remove existing install, sorry"
    fi
fi

# make the directory that the symlink will go into
if [[ ! -e `dirname $INSTALL_PATH` ]] ; then
    mkdir -p `dirname $INSTALL_PATH` || error "Unable to make `dirname $INSTALL_PATH` folder, sorry"
fi

# install the symlink we need
info "Installing symlink for your code"
ln -s $SRC_PATH $INSTALL_PATH || error "Unable to install symlink, sorry"

# all done
info "All done"