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

from serial import *
import json, hashlib, hmac
from sseclient import SSEClient
import requests
import time


def SerialCon():
    try:

        print("Opening serial port...")

        with Serial(port="/dev/ttyACM0", baudrate=9600, timeout=2) as arduino:

            time.sleep(0.3)

            print("Sending command to Arduino...")

            arduino.write(b"1")
            arduino.flush()

            print("Command sent.")

    except Exception as e:

        print("Serial error:", e)


def main():
    # ohjaukseen käytetty sivu
    url = "http://172.20.10.7:8080/change.php"
    try:
        req = SSEClient(url)
    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 404:
            print("URL " + url + " Not found!")
    except Exception:
        print("Fatal Error!")
    else:
        print("Connection OK!")

    # Käydään json-vastaus läpi
    serverKey = "e7f6c011776e8db7cd330b54174fd76f7d0216b612387a5ffcfb81e6f0919683"
    while True:
        for msg in req:
            if json.loads(msg.data)["state"] == 1:
                if (
                    json.loads(msg.data)["hash"]
                    == hmac.new(
                        bytes(serverKey, "utf-8"),
                        str(json.loads(msg.data)["time"]).encode("utf-8"),
                        hashlib.sha256,
                    ).hexdigest()
                ):
                    print("State change!")
                    SerialCon()
                    time.sleep(4)


if __name__ == "__main__":
    main()
