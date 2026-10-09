# pfSense WAS-110 Monitor

This widget will display status from an SFP running the 8311 community firmware version 2.8.3 or higher. It assumes that your module is reachable from pfSense at the IP address 192.168.11.1 (this is typically the default address, if this is not the case the address can be changed in was110.ajax.php).

<img width="706" height="463" alt="image" src="https://github.com/user-attachments/assets/6ef2005d-7beb-42d4-ba21-42ddf10d68da" />

## Installation

In pfSense go to Diagnostics/Command Prompt and in the Execute box paste:

`curl -L -o /tmp/was110.zip "https://github.com/WizADSL/pfSense_WAS-110_Monitor/raw/refs/heads/main/was110_widget.zip" && unzip -q -o /tmp/was110.zip -d /tmp/was110_tmp && mv -f /tmp/was110_tmp/was110.inc /usr/local/www/widgets/include/ && mv -f /tmp/was110_tmp/was110*.php /usr/local/www/widgets/widgets/ && rm -rf /tmp/was110.zip /tmp/was110_tmp`

This will download the ZIP file from GitHub, extract it and copy the files to the appropriate directories. You should then find the WAS-110 widget option on the pfSense dashboard under Available Widgets.

This project was created with AI and manual tweaks.
