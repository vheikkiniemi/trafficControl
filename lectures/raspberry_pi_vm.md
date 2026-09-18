# Building a Virtual Machine to Simulate a Raspberry Pi Server

## 🎯 Objective

Build a virtual machine that simulates the role of a Raspberry Pi as an intelligent-device gateway and server. The virtual machine will include:

* Debian 13
* An XFCE graphical environment similar to Raspberry Pi Desktop
* Network connectivity
* SSH remote access

## 1. Required Software

Download and install:

* [Oracle VirtualBox](https://www.virtualbox.org/)
* [Debian amd64 network installation image](https://www.debian.org/distrib/netinst)

Select the following architecture on the Debian download page:

```text
amd64
```

Do not download the Raspberry Pi OS disk image shown in the example image. It is an SD card image designed for ARM-based Raspberry Pi hardware.

On the official download page, Raspberry Pi OS is listed as compatible with Raspberry Pi devices. The separate Raspberry Pi Desktop for PC and Mac release is an older 32-bit version based on Debian 11. [Raspberry Pi OS downloads](https://www.raspberrypi.com/software/operating-systems/)

## 2. Create a New Virtual Machine

Open VirtualBox and select:

```text
New
```

Use the following settings:

| Setting           | Value                           |
| ----------------- | ------------------------------- |
| Name              | `intelligent-device-server`     |
| ISO Image         | The downloaded Debian ISO image |
| Type              | Linux                           |
| Version           | Debian (64-bit)                 |
| Base Memory       | 4096 MB                         |
| Processors        | 2                               |
| Virtual Hard Disk | 25 GB                           |
| Disk Allocation   | Dynamically allocated           |

If VirtualBox suggests an automatic installation, select:

```text
Skip Unattended Installation
```

A manual installation helps students understand the purpose of user accounts, disk partitioning, and software selections.

## 3. Configure the Network Adapters

Open the virtual machine settings:

```text
Settings → Network
```

### Adapter 1: Internet Connection

```text
Enable Network Adapter: Yes
Attached to: NAT
```

This adapter is used for:

* Downloading Debian packages
* Installing updates
* Accessing the internet

### Adapter 2: Server Connection

```text
Enable Network Adapter: Yes
Attached to: Host-only Adapter
```

This adapter is used for:

* SSH connections
* Opening the web page from the host computer
* Testing MQTT connections
* Keeping the server independent of the campus network

If a Host-only Adapter is not yet available, open:

```text
VirtualBox → Tools → Network
```

Create a new host-only network and leave its DHCP service enabled.

## 4. Configure the Display

Open:

```text
Settings → Display
```

Use the following settings:

| Setting                | Value              |
| ---------------------- | ------------------ |
| Video Memory           | 128 MB             |
| Graphics Controller    | VMSVGA             |
| Enable 3D Acceleration | Disabled initially |
| Monitor Count          | 1                  |

## 5. Install Debian

Start the virtual machine and select:

```text
Graphical install
```

### Recommended Installation Settings

| Setting          | Example                     |
| ---------------- | --------------------------- |
| Language         | English                     |
| Location         | Finland                     |
| Keyboard         | Finnish                     |
| Hostname         | `intelligent-device-server` |
| Domain name      | Leave empty                 |
| Username         | The student’s username      |
| Partitioning     | Guided – use entire disk    |
| Partition layout | All files in one partition  |
| Package manager  | Debian mirror               |
| Install GRUB     | Yes                         |

English is recommended as the operating system language. This makes error messages, online instructions, and screenshots in student reports easier to compare with technical documentation.

## 6. Select the Graphical Environment

When the installer asks which software should be installed, select:

```text
☑ Debian desktop environment
☑ Xfce
☑ SSH server
☑ Standard system utilities
```

Deselect other desktop environments if they are selected:

```text
☐ GNOME
☐ KDE Plasma
☐ LXDE
☐ Cinnamon
☐ MATE
☐ LXQt
```

The important selection is:

```text
XFCE
```

XFCE is a lightweight desktop environment that is visually and structurally similar to Raspberry Pi Desktop.

## 7. Complete the First Start-up

Sign in and open a terminal.

Check the operating system:

```bash
cat /etc/os-release
```

Check the system architecture:

```bash
uname -m
```

The result should be:

```text
x86_64
```

This indicates that the system is running in a PC-based virtual machine. A physical 64-bit Raspberry Pi would typically return:

```text
aarch64
```

Check the network interfaces:

```bash
ip address
```

The virtual machine should have two network interfaces:

* A NAT address, commonly `10.0.2.15`
* A host-only address, commonly something like `192.168.56.101`

Test the internet connection:

```bash
ping -c 4 debian.org
```

## 8. Update the System

```bash
sudo apt update
sudo apt full-upgrade -y
```

Restart the virtual machine if necessary:

```bash
sudo reboot
```

## 9. Install VirtualBox Guest Additions

VirtualBox Guest Additions improve features such as:

* Automatic display resizing
* Mouse integration
* Clipboard integration
* General virtual machine usability

### Install the Debian packages:

```bash
apt install build-essential dkms linux-headers-$(uname -r) -y
```

### Insert Guest Additions CD

In the VirtualBox menu of your Debian VM:

**Devices → Insert Guest Additions CD image…**

This mounts the ISO to `/media/cdrom` (or `/media/$USER/VBox_GAs_*`).

If you don’t have the ISO yet, VirtualBox will offer to download it.

### Mount CD (if not auto-mounted)

```bash
mkdir -p /mnt/cdrom
mount /dev/cdrom /mnt/cdrom
```

### Run installer

Run the Guest Additions installer script:

```bash
cd /mnt/cdrom
sh ./VBoxLinuxAdditions.run
```

If everything goes well, it will build kernel modules (`vboxguest`, `vboxsf`, `vboxvideo`).

### Restart the virtual machine:

```bash
sudo reboot
```

After restarting, try:

```text
View → Auto-resize Guest Display
```

from the VirtualBox menu.

## 10. Configure a Raspberry Pi-Style Desktop

The XFCE desktop can be customised through:

```text
Applications → Settings
```

The most relevant settings are:

* Desktop wallpaper
* Panel positioned at the top of the screen
* Application menu in the upper-left corner
* Terminal and web-browser shortcuts on the panel

The exact Raspberry Pi OS themes and icons should not be a technical requirement. The essential goal is to provide the same general user experience:

```text
Top panel
├── Application menu
├── File manager
├── Terminal
├── Web browser
└── Network status
```

### Enable autologin

Open the file `/etc/lightdm/lightdm.conf` and uncomment the following line (typically located around line 120):

```bash
autologin-user=linuxadmin
```

## 11. Install the Initial Tools

```bash
sudo apt install -y \
  curl \
  git \
  nano \
  openssh-server
```

Check the SSH service:

```bash
systemctl status ssh
```

Press `q` to exit the service status view.

## 12. Test the SSH Connection

Find the host-only network IP address:

```bash
hostname -I
```

Open PowerShell on the Windows host computer:

```powershell
ssh username@192.168.56.101
```

Replace the username and IP address with your own values.

## 13. Create a Recovery Snapshot

When the environment is working, shut down the virtual machine:

```bash
sudo poweroff
```

In VirtualBox, select:

```text
Snapshots → Take
```

Name the snapshot, for example:

```text
01-clean-debian-XFCE
```

You can create another snapshot after installing the server software:

```text
02-server-tools-installed
```

## Expected Result

The virtual machine now provides the server and gateway role of a Raspberry Pi:

```text
Debian 13 with XFCE
├── Raspberry Pi Desktop-style graphical environment
├── NAT internet connection
├── Separate host-only server network
└──  SSH remote access
```