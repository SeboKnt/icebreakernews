#!/bin/bash
# SFTP Upload Script

# Check credentials
[ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ] || [ -z "$SFTP_PASS" ] && { echo "Missing credentials"; exit 1; }

# Arrays für alle Dateipfade
declare -a all_files=()
declare -a dirs_to_scan=()

# Funktion zum Sammeln von Inhalten eines Verzeichnisses
scan_directory() {
    local current_dir="$1"
    echo "Scanning: $current_dir"
    
    # Hole Verzeichnisinhalt und filtere nur die ls-Ausgabe
    local temp_file="/tmp/sftp_output_$$"
    sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no $SFTP_USER@$SFTP_HOST << EOF > "$temp_file" 2>/dev/null
cd "$current_dir"
ls -la
quit
EOF
    
    # Parse nur echte ls-Zeilen (beginnen mit Dateiberechtigungen)
    while IFS= read -r line; do
        # Überspringe alles was nicht wie eine ls-Zeile aussieht
        if [[ "$line" =~ ^[d-][rwx-][rwx-][rwx-][rwx-][rwx-][rwx-][rwx-][rwx-][rwx-] ]]; then
            # Überspringe . und ..
            [[ "$line" =~ \ \.$  ]] && continue
            [[ "$line" =~ \ \.\.$  ]] && continue
            
            # Extrahiere Dateiname (alles nach dem letzten Leerzeichen)
            filename=$(echo "$line" | sed 's/.* //')
            [[ -z "$filename" ]] && continue
            
            # Baue vollständigen Pfad
            if [[ "$current_dir" == "." ]]; then
                full_path="$filename"
            else
                full_path="$current_dir/$filename"
            fi
            
            # Füge zum Array hinzu
            all_files+=("$full_path")
            
            # Wenn Verzeichnis, füge zur Scan-Liste hinzu
            if [[ "$line" =~ ^d ]]; then
                dirs_to_scan+=("$full_path")
            fi
        fi
    done < "$temp_file"
    
    rm -f "$temp_file"
}

# Starte mit Root
dirs_to_scan=(".")

# Scanne alle Verzeichnisse
while [[ ${#dirs_to_scan[@]} -gt 0 ]]; do
    current_dir="${dirs_to_scan[0]}"
    # Entferne erstes Element
    dirs_to_scan=("${dirs_to_scan[@]:1}")
    
    scan_directory "$current_dir"
done

echo ""
echo "=== ALLE DATEIEN UND VERZEICHNISSE ==="
for path in "${all_files[@]}"; do
    echo "$path"
done
echo "=== TOTAL: ${#all_files[@]} Einträge ==="

echo ""
echo "=== LÖSCHE ALLE DATEIEN (egal ob Datei oder Verzeichnis) ==="

# Lösche alle Einträge in umgekehrter Reihenfolge (tiefste zuerst)
for ((i=${#all_files[@]}-1; i>=0; i--)); do
    file="${all_files[i]}"
    echo "Deleting: $file"
    
    # Versuche erst rm (für Dateien), dann rmdir (für Verzeichnisse)
    sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no $SFTP_USER@$SFTP_HOST << EOF >/dev/null 2>&1
rm "$file"
rmdir "$file"
quit
EOF
done

echo "=== LÖSCHUNG ABGESCHLOSSEN ==="

# Jetzt: Neue Dateien hochladen
sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no $SFTP_USER@$SFTP_HOST << 'EOF'
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