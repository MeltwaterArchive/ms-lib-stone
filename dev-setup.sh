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

function info() {
    echo "$1"
}

function error() {
    echo "*** error: $1"
    exit 1
}

function absdir() {
    # make sure we have a parameter
    if [[ -z $1 ]] ; then
        error "Missing parameter 1 from absdir()"
    fi

    # do we have realpath installed?
    if [[ -x $REALPATH2 ]] ; then
        $REALPATH "$1"
        return
    fi

    # fake the realpath functionality
    ( cd "$1" && pwd ) || error "Folder '$1' does not exist"
}

# $1 - top level to add from
# $2 - top level to add to
# $3 - contents to link across
function add_to_vendor() {
    # what are we doing?
    info "Installing $3 into $2"

    # create any directories required
    TARGET_DIR="$2/`dirname $3`"
    if [[ ! -e $TARGETDIR ]] ; then
        make_dir "$TARGET_DIR"
    fi

    # work out the real source file
    SOURCE_FILE=`eval echo "$1/$3"`
    TARGET_FILE=`basename "$SOURCE_FILE"`

    # work out the relative path to link to
    relpath=`relative_path "$SOURCE_FILE" "$TARGET_DIR/$TARGET_FILE"`

    # create the symlink
    ln -s "$relpath" "$TARGET_DIR/$TARGET_FILE" || error "Unable to create symlink, sorry"

    # all done
    info "Installed $3 into $2"
}

function clone_git() {
    pushd "$VENDOR_SRC_DIR" || error "Unable to switch to folder '$VENDOR_SRC_DIR'"
    git clone "git@github.com:$1/$2.git" || error "Unable to clone git repo"
    popd
}


function download_tarball() {
    pushd "$VENDOR_SRC_DIR" || error "Unable to switch to folder '$VENDOR_SRC_DIR'"

    if [[ -n $2 ]] ; then
        curl -LsSo "$2" "$1" || error "Unable to download tarball"
    else
        wget "$1" || error "Unable to download tarball"
    fi

    popd
}

function make_dir() {
    # make sure we have a parameter
    if [[ -z $1 ]] ; then
        error "Missing parameter 1 to make_dir()"
    fi

    info "Creating folder '$1'"
    mkdir -p "$1" || error "Unable to create folder '$1', sorry"
}

function relative_path()
{
    python -c "import os.path; print os.path.relpath('$1','${2:-$PWD}')" | cut -c 4-;
}

function remove_dir() {
    # make sure we have a parameter
    if [[ -z $1 ]] ; then
        error "Missing parameter 1 to remove_dir()"
    fi

    # does the folder exist?
    if [[ -e $1 ]] ; then
        # yes it does - tell the user what we are doing
        info "Removing existing folder $1"

        # is it a symlink?
        if [[ -L $ ]] ; then
            # yes - remove the symlink
            rm "$1" || error "Unable to remove existing folder, sorry"
        else
            # no - remove the folder
            rm -rf "$1" || error "Unable to remove existing folder, sorry"
        fi
    fi
}

function unpack_tarball() {
    pushd "$VENDOR_SRC_DIR" || error "Unable to switch to folder '$VENDOR_SRC_DIR'"

    # make sure the file is there
    if [[ ! -f $1 ]] ; then
        error "Tarball '$1' not found in '$VENDOR_SRC_DIR'"
    fi

    # what is the file extension?
    ext=`echo $1 | awk -F . '{print $NF}'`

    # now, how to unpack it?
    case $ext in
        gz|gzip)
            tar -zxf "$1" || error "Unable to unpack tarball"
            ;;
        bzip|bz2)
            tar -jxf "$1" || error "Unable to unpack tarball"
            ;;
        zip)
            unzip "$1" || error "Unable to unpack tarball"
            ;;
        *)
            error "Unexpected file suffix '$ext'"
    esac

    # if we get here, then we successfully unpacked the tarball
    # and it is no longer required
    rm "$1" || error "Unable to remove unpacked tarball '$1'"

    popd
}

function install_dep_autoloader() {
    # clone the repo into the vendor_src folder
    clone_git stuartherbert Autoloader

    # link the PHP library folder
    add_to_vendor "$VENDOR_SRC_DIR/Autoloader/src/php" "$VENDOR_DIR/php" "Phix_Project/Autoloader4"
}

function install_dep_twig() {
    # which version of Twig do we want?
    TWIG_VERSION="1.11.1"

    # download the tarball from GitHub
    download_tarball "https://github.com/fabpot/Twig/archive/v${TWIG_VERSION}.tar.gz" "Twig-${TWIG_VERSION}.tar.gz"

    # unpack it
    unpack_tarball "Twig-${TWIG_VERSION}.tar.gz"

    # add it to the PHP library folder
    add_to_vendor "$VENDOR_SRC_DIR/Twig-${TWIG_VERSION}/lib/" "${VENDOR_DIR}/php/" "Twig"
}

# make sure that realpath is installed
REALPATH="`which realpath`"

TOPDIR_RAW="`dirname $0`"
TOPDIR="`absdir $TOPDIR_RAW`"
VENDOR_DIR="$TOPDIR/vendor"
VENDOR_SRC_DIR="$TOPDIR/vendor_src"

# remove any existing vendor folders
remove_dir "$VENDOR_DIR"
remove_dir "$VENDOR_SRC_DIR"

# create the vendor folders
make_dir "$VENDOR_DIR"
make_dir "$VENDOR_DIR/bin"
make_dir "$VENDOR_DIR/etc"
make_dir "$VENDOR_DIR/php"
make_dir "$VENDOR_SRC_DIR"

# install the Phix_Project PHP autoloader
install_dep_autoloader

# install the Twig PHP library
install_dep_twig

# all done
info "All done"
