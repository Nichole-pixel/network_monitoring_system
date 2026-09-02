import time
import urllib.request
import json
import os
import sys
import uuid

def get_mac_address():
    """Gets the MAC address of the current device in the format XX:XX:XX:XX:XX:XX"""
    mac_num = hex(uuid.getnode()).replace('0x', '').upper()
    mac_num = mac_num.zfill(12)
    mac = ':'.join(mac_num[i: i + 2] for i in range(0, 12, 2))
    return mac

# Configuration
NMS_URL = "http://localhost:8000/api/sync_client.php"
MAC_ADDRESS = get_mac_address()
POLL_INTERVAL = 15  # seconds

HOSTS_PATH = r"C:\Windows\System32\drivers\etc\hosts"
BLOCK_MARKER_START = "# --- NMS BLOCKS START ---"
BLOCK_MARKER_END = "# --- NMS BLOCKS END ---"

def is_admin():
    try:
        import ctypes
        return ctypes.windll.shell32.IsUserAnAdmin()
    except:
        return False

def get_blocked_domains():
    try:
        url = f"{NMS_URL}?mac={MAC_ADDRESS}"
        req = urllib.request.Request(url)
        with urllib.request.urlopen(req, timeout=5) as response:
            data = json.loads(response.read().decode())
            if data.get("status") == "success":
                return data.get("blocked_domains", [])
    except Exception as e:
        print(f"Error fetching rules: {e}")
    return None

def update_hosts_file(domains):
    try:
        with open(HOSTS_PATH, 'r') as f:
            lines = f.readlines()
        
        # Remove old NMS blocks
        new_lines = []
        in_block = False
        for line in lines:
            if line.strip() == BLOCK_MARKER_START:
                in_block = True
            elif line.strip() == BLOCK_MARKER_END:
                in_block = False
                continue
            elif not in_block:
                new_lines.append(line)
        
        # Add new NMS blocks
        if domains:
            # Ensure file ends with a newline before appending
            if new_lines and not new_lines[-1].endswith('\n'):
                new_lines[-1] += '\n'
                
            new_lines.append(f"{BLOCK_MARKER_START}\n")
            for domain in domains:
                new_lines.append(f"127.0.0.1 {domain}\n")
                new_lines.append(f"127.0.0.1 www.{domain}\n")
            new_lines.append(f"{BLOCK_MARKER_END}\n")
            
        with open(HOSTS_PATH, 'w') as f:
            f.writelines(new_lines)
            
        print(f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] Hosts file updated. Blocked {len(domains)} domains.")
    except PermissionError:
        print("Permission Denied: You must run this script as Administrator to modify the hosts file.")
        sys.exit(1)
    except Exception as e:
        print(f"Error updating hosts file: {e}")

def main():
    if not is_admin():
        print("Please run this script as Administrator!")
        sys.exit(1)
        
    print(f"Starting NMS Client Agent for MAC: {MAC_ADDRESS}")
    print(f"Polling {NMS_URL} every {POLL_INTERVAL} seconds...")
    
    while True:
        domains = get_blocked_domains()
        if domains is not None:
            update_hosts_file(domains)
        time.sleep(POLL_INTERVAL)

if __name__ == "__main__":
    main()
