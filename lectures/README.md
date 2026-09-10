# Software Stack Layers

```
+-------------------------------------------------------+
| 6. Cloud Orchestration & Fleet Management             |
+-------------------------------------------------------+
| 5. Application Layer (Logic & HMI/UX)                 |
+-------------------------------------------------------+
| 4. Intelligence / Edge AI Layer (TinyML, Inference)   |
+-------------------------------------------------------+
| 3. Middleware & System Services (Security, Comms, OTA)|
+-------------------------------------------------------+
| 2. Operating System / RTOS & Drivers                  |
+-------------------------------------------------------+
| 1. Hardware Abstraction Layer (HAL) & Firmware        |
+-------------------------------------------------------+
                    [ HARDWARE ]

```

**1. Hardware Abstraction Layer (HAL) & BSP**

* **Role:** Exposes hardware peripherals (I2C, SPI, GPIO, UART, PCIe) to upper software layers while masking silicon-specific details.
* **Key Tech:** Board Support Packages (BSPs), vendor abstraction drivers (e.g., STM32 HAL, ESP-IDF HAL, NXP MCUXpresso).
* **Teaching Focus:** Why hardware portability matters and how abstraction layers trade execution speed for code maintainability.

**2. Operating System & Runtime Environment**

* **Role:** Manages system resources, task scheduling, memory, and hardware interrupts. The choice depends entirely on device constraints:
* *Ultra-constrained (MCUs):* Real-Time OS (FreeRTOS, Zephyr RTOS) for deterministic, low-latency execution.
* *Rich Edge Devices (MPUs/GPUs):* Embedded Linux (Yocto, Ubuntu Core) or Android Things for complex multitasking.


* **Teaching Focus:** Determinism vs. throughput, memory management, and process scheduling in embedded environments.

**3. Middleware & System Services**

* **Role:** Provides essential "plumbing" for networking, security, data serialization, and life-cycle management.
* **Key Tech:**
* *Protocols:* MQTT, CoAP, gRPC, BLE, Zigbee.
* *Security:* Secure Boot, TLS, cryptographic hardware integrations (TPM, Secure Element).
* *Management:* Over-The-Air (OTA) update agents (e.g., RAUC, Mender).


* **Teaching Focus:** Resource-friendly communication protocols and embedded device security fundamentals.

**4. Intelligence & Edge AI Layer**

* **Role:** Enables local, on-device data processing, sensor fusion, and machine learning inference without relying on cloud round-trips.
* **Key Tech:**
* *Frameworks:* TensorFlow Lite / TFLite Micro, Edge Impulse, ONNX Runtime, PyTorch Mobile.
* *Hardware Acceleration:* NPU/TPU drivers, CUDA/TensorRT (NVIDIA Jetson), OpenVINO.


* **Teaching Focus:** Model quantization (FP32 $\to$ INT8), latency constraints, and TinyML pipelines.

**5. Application Layer**

* **Role:** Implements the actual domain logic (e.g., predictive maintenance algorithms, autonomous navigation, smart thermostat control) and optional local user interfaces.
* **Key Tech:** C, C++, Rust (for low-level/safety-critical code), Python (for prototyping/embedded Linux), MicroPython.
* **Teaching Focus:** Event-driven architecture, state machines, and API integration between software layers.

**6. Cloud Orchestration & Telemetry (Device-to-Cloud Integration)**

* **Role:** While running locally, intelligent devices sync telemetry, receive fleet configuration changes, and report status back to cloud/edge servers.
* **Key Tech:** AWS IoT Core, Azure IoT Hub, custom WebSockets/REST APIs.
* **Teaching Focus:** Digital twins, shadow states, and surviving offline/intermittent network conditions.

---