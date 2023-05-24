#!/bin/bash
cat | websocat --unidirectional --websocket-ignore-zeromsg --async-stdio ws://192.168.88.254:11111/

