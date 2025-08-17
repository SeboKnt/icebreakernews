#!/bin/bash
# Erweitertes SFTP Upload Script mit vollständiger Synchronisation

# Check credentials
[ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ] || [ -z "$SFTP_PASS" ] && { echo "Missing credentials"; exit 1; }

echo "Starting full sync upload..."

# Vollständige Synchronisation: Erst alles löschen, dann neu hochladen
sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no $SFTP_USER@$SFTP_HOST << 'EOF'
# Entferne alle existierenden Dateien (außer versteckten Systemdateien)
rm -r *
# Lade das komplette public_html Verzeichnis hoch
put -r public_html/
put public_html/.env.php
put public_html/.htaccess
quit
EOF

if [ $? -eq 0 ]; then
    echo "Full sync upload successful"
    echo "All remote files were replaced with local versions"
else
    echo "Upload failed"
    exit 1
fi