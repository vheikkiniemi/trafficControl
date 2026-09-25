import paho.mqtt.publish as publish

publish.single(
    topic="ville/test",
    payload="testing from Ville and python",
    hostname="192.168.1.100"
)

print("Message sent")