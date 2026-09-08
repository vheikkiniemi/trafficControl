# Wiring

Based on `.ino` file, the wiring is:

* **Push button**

  * One side → **Arduino 5 V**
  * Other side → **D2**
  * **10 kΩ resistor** from D2 → **GND**

* **Ultrasonic sensor HC-SR04**

  * VCC → **5 V**
  * GND → **GND**
  * TRIG → **D3**
  * ECHO → **D5**

* **Buzzer**

  * * → **D6**
  * − → **GND**

* **Pedestrian red LED**

  * Arduino **D7** → resistor → LED anode `+`
  * LED cathode `−` → **GND**

* **Pedestrian green LED**

  * Arduino **D8** → resistor → LED anode `+`
  * LED cathode `−` → **GND**

* **Servo**

  * Signal → **D9**
  * +5 V → **5 V**
  * GND → **GND**

* **Main traffic light red LED**

  * Arduino **D11** → resistor → LED anode `+`
  * LED cathode `−` → **GND**

* **Main traffic light yellow LED**

  * Arduino **D12** → resistor → LED anode `+`
  * LED cathode `−` → **GND**

* **Main traffic light green LED**

  * Arduino **D13** → resistor → LED anode `+`
  * LED cathode `−` → **GND**

For the LEDs, something like **220–330 Ω** is appropriate for the series resistor.

So the Arduino pin summary is:

```text
D2   Push button
D3   HC-SR04 TRIG
D5   HC-SR04 ECHO
D6   Buzzer
D7   Pedestrian red
D8   Pedestrian green
D9   Servo
D11  Traffic red
D12  Traffic yellow
D13  Traffic green
```

And all devices should share a **common GND**.


# Troubleshooting tips

* **Nothing works**

  * Check that Arduino has power.
  * Check the **5 V and GND rails** on the breadboard.
  * Make sure all components have a **common GND**.

* **LED does not light**

  * Check LED polarity: long leg = **anode (+)**, short leg = **cathode (−)**.
  * Check the series resistor.
  * Verify the correct Arduino pin: D7, D8, D11, D12 or D13.

* **Wrong LED lights**

  * Compare the wiring with the pin definitions in the INO file.
  * A useful test is `digitalWrite(pin, HIGH)` for one LED at a time.

* **Push button does not work / triggers randomly**

  * D2 must not be left floating.
  * Check the **10 kΩ pull-down resistor between D2 and GND**.
  * Pressing the button should connect **D2 to 5 V**.
  * Test with `Serial.println(digitalRead(2));`.

* **HC-SR04 always returns 0 or incorrect distances**

  * Check: VCC → 5 V, GND → GND, TRIG → D3, ECHO → D5.
  * Make sure TRIG and ECHO are not reversed.
  * Test with a large flat object about **20–50 cm** in front of the sensor.
  * Your program intentionally ignores measurements **≤ 5 cm or ≥ 100 cm**.

* **Servo does not move**

  * Signal → D9.
  * Check 5 V and GND.
  * If the Arduino resets when the servo moves, the servo may draw too much current. Use a separate regulated 5 V supply and **connect its GND to Arduino GND**.

* **Buzzer is silent**

  * Check buzzer → D6 and GND.
  * Important: your code currently has:

    ```cpp
    bool Mute = true;
    ```

    This **disables the buzzer**. For testing, change it to:

    ```cpp
    bool Mute = false;
    ```

* **System reacts unexpectedly**

  * Open **Serial Monitor at 9600 baud**.
  * The program prints messages such as `C:01`, `I:61`, `I:11`, etc., which are very useful for following the state changes.
  * Note that **any received serial character triggers the system** because of the current `Serial.available()` logic.

A particularly effective troubleshooting strategy for this project is to test **one device at a time**: LED → button → ultrasonic sensor → servo → buzzer, and only after those work individually, test the complete traffic-light logic.
