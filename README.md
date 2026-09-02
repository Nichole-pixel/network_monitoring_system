# Network Monitoring System (NMS)

## Overview
The Network Monitoring System (NMS) is a centralized web dashboard and autonomous client agent designed for managing and restricting web access across a network of computers.

Instead of relying on router-level (Mikrotik) DNS filtering, this system uses a lightweight, background **Python Client Agent** that polls the central NMS server and dynamically modifies the local Windows `hosts` file to block domains with a `0.0.0.0` blackhole routing technique.

### Key Features
- **Centralized Dashboard:** A clean, modular PHP-based web interface for managing policies, rules, and client computers.
- **Autonomous Agent:** A Python script that runs quietly in the background on client machines, automatically applying network restrictions.
- **Hardware-locked:** The agent dynamically detects the machine's physical MAC address to ensure rules are mapped precisely to the correct computer.
- **Instant Connection Drops:** Blocked websites are routed to `0.0.0.0`, resulting in immediate `ERR_ADDRESS_INVALID` errors rather than SSL certificate mismatches.
- **Auto-DNS Flushing:** The agent automatically flushes the Windows DNS cache when applying new rules to prevent bypasses.

---

## 🛠 System Requirements

### Server (Admin Computer)
- **Web Server:** Apache (via XAMPP) or PHP Built-in Web Server.
- **Database:** MySQL / MariaDB (via XAMPP).
- **PHP Version:** PHP 8.0+

### Client (Monitored Computers)
- **OS:** Windows 10 / 11.
- **Python:** Python 3.8+ installed and added to the system PATH.

---

## 🚀 Setup Instructions

### 1. Database Configuration
1. Open XAMPP and start **Apache** and **MySQL**.
2. Open your web browser and navigate to `http://localhost/phpmyadmin`.
3. Create a new database named `nms_db`.
4. Run the `setup_database.php` script by visiting `http://localhost:8000/setup_database.php` in your browser. This will automatically generate all necessary tables and create the default admin account:
   - **Username:** `admin`
   - **Password:** `admin123`

### 2. Starting the System
You can start the entire system with a single click using the provided batch script:
1. Double click the **`Start_NMS.bat`** file.
2. Click **Yes** on the Administrator permission prompt (required for the Python agent to modify the system `hosts` file).
3. The script will automatically:
   - Start the PHP Web Server on `localhost:8000`.
   - Start the Python Client Agent in the background.
   - Open your web browser directly to the dashboard.

---

## 💻 Usage Guide

### 1. Registering a Client
Because the Python agent dynamically reads the physical hardware, you must register the exact MAC address of the computer you wish to monitor.
1. When you run `Start_NMS.bat`, check the Python terminal window that appears.
2. Note the text that says: `Starting NMS Client Agent for MAC: XX:XX:XX:XX:XX:XX`.
3. Log into the NMS Dashboard (`http://localhost:8000`).
4. Go to **Client Dashboard** -> **Add Client**.
5. Input the exact MAC address from step 2.

### 2. Creating Policies & Rules
1. Go to the **Policy Dashboard** and add a new policy (e.g., "Social Media Block").
2. Assign domains to that policy (e.g., `facebook.com`).
3. Go to the **Rules Dashboard** and link your newly created Policy to the Client you registered in Step 1.
4. Wait exactly 15 seconds. The Python agent will poll the API, fetch the new rule, and instantly block the websites on the client machine!

---

## 🛑 Important: Troubleshooting Browser Bypasses

Modern web browsers try to bypass local network rules using encrypted tunnels. If a blocked website is still loading, it is because of **Secure DNS (DNS-over-HTTPS)**.

**To ensure the blocking system works, you must disable Secure DNS in the client's browser:**
1. Open Google Chrome or Microsoft Edge.
2. Navigate to **Settings > Privacy and Security > Security**.
3. Scroll down to the **"Use secure DNS"** section.
4. Toggle this setting **OFF**.
5. Restart the browser. 
*(Note: For laboratory deployments, it is highly recommended to use Windows Group Policy (GPO) to permanently disable Secure DNS across all student computers).*
