#!/bin/sh
if [ -d /home/$1 ]
then
    echo "/home/$1 already exists... Not creating."
else
    cp /etc/skel /home/$1 -R
fi
chown $1:users /home/$1 -R
usermod -d /home/$1 $1
