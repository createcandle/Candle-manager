screen -X quit > /dev/null 2>/dev/null;
nohup screen -L listenoutput.txt /dev/ttyUSB0 115200;
