import paho.mqtt.client as mqtt

BROKER = "80.186.144.43"
PORT = 1883
TOPIC = "devices/sensor/#"
HEATER = False


def on_connect(client, userdata, flags, reason_code, properties):
    if reason_code == 0:
        print("Connected to the MQTT broker")
        client.subscribe(TOPIC)
        print(f"Subscribed to: {TOPIC}")
    else:
        print(f"Connection failed: {reason_code}")


def on_message(client, userdata, message):
    global HEATER
    payload = message.payload.decode("utf-8")
#    print(type(payload))
    if payload < "20" and HEATER == False:
        print("Heater ON")
        HEATER = True
    elif payload >= "20" and HEATER == True:
        print("Heater OFF")
        HEATER = False
#    print(f"Topic: {message.topic}")
#    print(f"Message: {payload}")


client = mqtt.Client(
    callback_api_version=mqtt.CallbackAPIVersion.VERSION2,
    client_id="subscriber"
)

client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER, PORT, 60)
client.loop_forever()