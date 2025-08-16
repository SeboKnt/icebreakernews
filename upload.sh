#!/bin/bash
# Minimal SFTP Upload Script

# Check credentials
[ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ] || [ -z "$SFTP_PASS" ] && { echo "Missing credentials"; exit 1; }

sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no $SFTP_USER@$SFTP_HOST << 'EOF'
put -r public_html/
quit
EOF

[ $? -eq 0 ] && echo "Upload successful" || exit 1