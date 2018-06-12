#!/bin/bash

mkdir -p "$HOME/tmp"
PIDFILE="$HOME/tmp/project.pid"

if [ -e "${PIDFILE}" ] && (ps -u $(whoami) -opid= |
                           grep -P "^\s*$(cat ${PIDFILE})$" &> /dev/null); then
  echo "Already running."
  exit 99
fi

bash $HOME/Desktop/project/main.sh > $HOME/tmp/project.log &

echo $! > "${PIDFILE}"
chmod 644 "${PIDFILE}"