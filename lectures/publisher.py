import paho.mqtt.client as mqtt
import json
import time
import random
from datetime import datetime, UTC

# Make sure to install dependencies before running:
# Linux: sudo apt install python3-paho-mqtt
# Python packages: pip install paho-mqtt

BROKER = "intelligent-device-server.local"
PORT = 1883
DEVICE_NAME = "pi1"

def on_connect(client, userdata, flags, rc, properties=None):
    print("✅ Connected with result code:", rc)

client = mqtt.Client(
    client_id=DEVICE_NAME,
    protocol=mqtt.MQTTv5,
    callback_api_version=mqtt.CallbackAPIVersion.VERSION2
)

client.on_connect = on_connect

print("✅ Connecting to", BROKER)
client.connect(BROKER, PORT, 60)

# ✅ REQUIRED: start networking loop
client.loop_start()

# ✅ Wait for connection to establish
time.sleep(2)

try:
    while True:
        temperature = round(random.uniform(20.0, 25.0), 2)

        payload = {
            "value": temperature,
            "unit": "C",
            "timestamp": datetime.now(UTC).isoformat()
        }

        topic = f"devices/{DEVICE_NAME}/temperature"

        result = client.publish(topic, json.dumps(payload), qos=1)

        print("📥 Publish rc:", result.rc, "payload:", payload)

        time.sleep(5)

except KeyboardInterrupt:
    print("\n🛑 Interrupted by user, shutting down...")

finally:
    client.loop_stop()
    client.disconnect()
    print("✅ Disconnected cleanly")
