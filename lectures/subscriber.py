import paho.mqtt.client as mqtt
import json

# Make sure to install dependencies before running:
# Linux: sudo apt install python3-paho-mqtt python3-psycopg2
# Python packages: pip install paho-mqtt psycopg2-binary

BROKER = "intelligent-device-server.local"
PORT = 1883
DEVICE_ID = "pi2"   # new device ID

def on_connect(client, userdata, flags, rc, properties=None):
    print("✅ Connected with result code:", rc)
    
    # Subscribe to all devices or specific one
    client.subscribe("devices/#")
    # or:
    # client.subscribe("devices/pi1/#")
    
    hello_payload = {
        "status": "online",
        "role": "subscriber"
    }

    client.publish(f"devices/{DEVICE_ID}/status", json.dumps(hello_payload), qos=1)

def on_message(client, userdata, msg):
    print("📥 Message received:")
    print("Topic:", msg.topic)

    try:
        payload = json.loads(msg.payload.decode())
        print("Payload:", payload)
    except Exception:
        print("Raw payload:", msg.payload)

client = mqtt.Client(
    client_id=DEVICE_ID,
    protocol=mqtt.MQTTv5,
    callback_api_version=mqtt.CallbackAPIVersion.VERSION2
)

client.on_connect = on_connect
client.on_message = on_message

print("✅ Connecting to", BROKER)
client.connect(BROKER, PORT, 60)

try:
    client.loop_forever()

except KeyboardInterrupt:
    print("\n🛑 Interrupted by user, shutting down...")

finally:
    client.disconnect()
    print("✅ Disconnected cleanly")

