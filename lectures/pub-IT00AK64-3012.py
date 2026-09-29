import paho.mqtt.client as mqtt

BROKER = "80.186.144.43"
PORT = 1883
TOPIC = "lab/test"
MESSAGE = "hello from Vaatturinpolku and python"

client = mqtt.Client()

try:
    client.connect(BROKER, PORT, 60)
    client.publish(TOPIC, MESSAGE)
    print(f"Published '{MESSAGE}' to topic '{TOPIC}'")
    client.disconnect()
except Exception as e:
    print(f"Error: {e}")