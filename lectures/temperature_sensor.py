import random
import time
import paho.mqtt.client as mqtt

# MQTT settings
BROKER = "80.186.144.43"
PORT = 1883
TOPIC = "devices/sensor/temperature"


def main():
    client = mqtt.Client()

    try:
        client.connect(BROKER, PORT, 60)
        print(f"Connected to MQTT broker {BROKER}")
        print("Publishing temperature values every 5 seconds...")
        print("Press Ctrl+C to stop.\n")

        while True:
            temperature = round(random.uniform(18, 22), 1)

            client.publish(TOPIC, str(temperature))

            print(f"Published: {temperature} °C")

            time.sleep(5)

    except KeyboardInterrupt:
        print("\nStopping temperature sensor...")

    except Exception as e:
        print(f"Error: {e}")

    finally:
        client.disconnect()
        print("Disconnected from broker.")


if __name__ == "__main__":
    main()