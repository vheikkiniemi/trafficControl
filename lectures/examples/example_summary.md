# MQTT Server and Remote Administration Lab Summary

## Overview

This lab exercise focused on preparing a Debian/Raspberry Pi system for remote management and MQTT-based messaging. The system was updated, configured for SSH access, and equipped with a Mosquitto MQTT broker for publish/subscribe communication.

## Objectives Achieved

- Updated the operating system packages.
- Installed and configured the Mosquitto MQTT broker.
- Enabled mDNS hostname resolution using Avahi.
- Installed and enabled SSH remote access.
- Implemented SSH key-based authentication.
- Created a custom Mosquitto configuration.
- Verified network services and listening ports.
- Tested MQTT messaging functionality.
- Performed troubleshooting using logs and diagnostic tools.

---

## 1. System Preparation

The operating system was updated to ensure the latest packages and security patches were installed:

```bash
sudo apt update
sudo apt upgrade
```

Unused packages were removed with:

```bash
sudo apt autoremove
```

---

## 2. MQTT Broker Installation

Mosquitto and its client utilities were installed:

```bash
sudo apt install mosquitto mosquitto-clients
```

This provided:

- `mosquitto_pub` for publishing messages
- `mosquitto_sub` for subscribing to messages
- The Mosquitto broker service

---

## 3. Hostname Resolution with Avahi

Avahi was installed to support mDNS hostname resolution:

```bash
sudo apt install avahi-daemon
```

Connectivity was verified using:

```bash
ping raspberrypi.local
```

The hostname was inspected and modified when necessary:

```bash
cat /etc/hostname
nano /etc/hostname
```

---

## 4. SSH Server Configuration

The OpenSSH server was installed:

```bash
sudo apt install openssh-server
```

The SSH service was enabled:

```bash
sudo systemctl enable ssh
```

Service status was verified using:

```bash
sudo systemctl status ssh
```

---

## 5. SSH Key Authentication

SSH key-based authentication was configured by adding a public key to:

```bash
~/.ssh/authorized_keys
```

Example command:

```bash
echo "ssh-ed25519 ..." >> authorized_keys
```

Permissions were set appropriately:

```bash
chmod 600 authorized_keys
chmod 700 ~/.ssh
```

The SSH server configuration was reviewed:

```bash
cat /etc/ssh/sshd_config
```

---

## 6. Mosquitto Configuration

A custom Mosquitto configuration file was created:

```bash
cd /etc/mosquitto/conf.d
nano lab.conf
```

The configuration was later reviewed:

```bash
cat /etc/mosquitto/conf.d/lab.conf
```

The system was rebooted to apply changes:

```bash
sudo reboot
```

---

## 7. Service Verification

Listening network services and ports were inspected using:

```bash
ss -lntp
sudo ss -lntp
```

This verified that:

- SSH was listening on TCP port 22.
- Mosquitto was listening on TCP port 1883.

Network interfaces were checked using:

```bash
ip -br address
```

---

## 8. MQTT Functionality Testing

MQTT subscriptions were created for testing:

```bash
mosquitto_sub -h localhost -t "lamp/control"
```

and

```bash
mosquitto_sub -h localhost -t "lamp/status"
```

The following MQTT topics were used:

- `lamp/control`
- `lamp/status`

These subscriptions confirmed that the broker was operational and able to receive messages.

---

## 9. Troubleshooting and Diagnostics

System and service logs were monitored using:

```bash
journalctl -f
journalctl -u mosquitto -f
```

Recent log entries were viewed with:

```bash
journalctl -n 20
```

Network troubleshooting utilities were installed:

```bash
sudo apt install tcpdump
```

These tools helped verify broker activity and diagnose connection issues.

---

## Final Environment Status

The system was successfully configured as:

- A Debian/Raspberry Pi server
- An MQTT messaging platform using Mosquitto
- A remotely accessible host using SSH
- An mDNS-advertised device discoverable via `hostname.local`
- A platform ready for IoT experimentation and MQTT-based communication

### 