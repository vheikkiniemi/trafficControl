#! /usr/bin/python3
# -*- coding: utf-8 -*-

"""
Copyright (c) 2020 Ville Heikkiniemi
Permission is granted to copy, distribute and/or modify this document under
the terms of the GNU Free Documentation License, Version 1.3 or any later
version published by the Free Software Foundation; with no Invariant Sections,
no Front-Cover Texts, and no Back-Cover Texts. A copy of the license can be
found online at http://www.fsf.org/licensing/licenses/fdl.txt
"""
import sys

from serial import *
import requests
import time
from datetime import datetime
import hmac, hashlib

def Tapahtuma(data):
        # Luodaan vastausdata
        nodeKey = '08dfcfc0c263d2817400a375869541c7714ed3b11ffb073902a0720eb0e47979'
        timeStamp = str(time.time())[0:10]
        msgId = hmac.new(bytes(nodeKey, 'utf-8'), (timeStamp).encode('utf-8'), hashlib.sha256).hexdigest()
        if len(data.split(":")) > 2:
                payload = {'nodeId': 'Home1', 'password': 'Sala1234#', 'event': data.split(":")[0], 'value': data.split(":")[1], 'sensor': data.split(":")[2], 'msgId': msgId, 'time': timeStamp}
        else:
                payload = {'nodeId': 'Home1', 'password': 'Sala1234#', 'event': data.split(":")[0], 'value': data.split(":")[1], 'msgId': msgId, 'time': timeStamp}
        # Lähetetään vastaus
        try:
                r = requests.post('http://172.20.10.7:8080/eventtodb.php', data=payload)
                #print("HTTP:", r.status_code)
                #print("Response:", repr(r.text))
                #print("Payload:", payload)
                if "OK" in r.content.decode("utf-8"):
                        if len(data.split(":")) > 2:
                                print("Event:", payload['event'] + ":" + payload['value'] + ":" + payload['sensor'], "No error!")
                        else:
                                print("Event:", payload['event'] + ":" + payload['value'], "No error!")
                else:
                        if len(data.split(":")) > 2:
                                print("Event:", payload['event'] + ":" + payload['value'] + ":" + payload['sensor'], "Error in content!")
                        else:
                                print("Event:", payload['event'] + ":" + payload['value'], "Error in content!")
        except Exception:
                print("Event:", payload['event'] + ":" + payload['value'], "Error!")
                pass

def main():
        # Yritetään luoda sarjaporttisoketti
        try:
                arduino = Serial(port='/dev/ttyACM0', baudrate=9600, timeout=None)
        except Exception:
                print('Tarkista portti')

        time.sleep(2)
        # Odotetaan sarjaporttiliikennettä
        while True:
                try:
                        Tapahtuma(arduino.readline()[:-2].decode())
                except KeyboardInterrupt:
                        print('Keskeytetään')
                        sys.exit()

if __name__ == "__main__":
        main()