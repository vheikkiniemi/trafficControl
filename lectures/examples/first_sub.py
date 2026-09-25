import paho.mqtt.client as mqtt

BROKER = "192.168.1.100"
PORT = 1883
TOPIC = "ville/test"

def on_connect(client, userdata, flags, reason_code, properties=None):
    print(f"Connected with result code: {reason_code}")
    client.subscribe(TOPIC)
    print(f"Subscribed to: {TOPIC}")

def on_message(client, userdata, msg):
    print(f"Topic: {msg.topic}")
    print(f"Message: {msg.payload.decode()}")
    print("-" * 30)

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)

client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER, PORT, 60)

print("Waiting for messages...")
client.loop_forever()