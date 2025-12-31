#!/bin/bash

# Pro Clean Quotation - Production Build Script
# Creates a clean, production-ready plugin package

set -e

PLUGIN_NAME="pro-clean-quotation"
VERSION="1.1.2"
BUILD_DIR="build"
DIST_DIR="dist"
PACKAGE_NAME="${PLUGIN_NAME}-${VERSION}"

echo "=========================================="
echo "Pro Clean Quotation Production Build"
echo "Version: ${VERSION}"
echo "=========================================="

# Step 1: Clean previous builds
echo ""
echo "[1/6] Cleaning previous builds..."
rm -rf "${BUILD_DIR}"
rm -rf "${DIST_DIR}"
mkdir -p "${BUILD_DIR}/${PACKAGE_NAME}"
mkdir -p "${DIST_DIR}"

# Step 2: Copy production files
echo "[2/6] Copying plugin files..."

# Core plugin files
cp pro-clean-quotation.php "${BUILD_DIR}/${PACKAGE_NAME}/"
cp USER_MANUAL.md "${BUILD_DIR}/${PACKAGE_NAME}/"
cp composer.json "${BUILD_DIR}/${PACKAGE_NAME}/"

# Copy only production directories (excluding dev dependencies)
cp -r assets "${BUILD_DIR}/${PACKAGE_NAME}/"
cp -r includes "${BUILD_DIR}/${PACKAGE_NAME}/"
cp -r languages "${BUILD_DIR}/${PACKAGE_NAME}/"
cp -r templates "${BUILD_DIR}/${PACKAGE_NAME}/"

echo "  ✓ Excluded: node_modules, vendor, tests, docs, build files"

# Step 3: Install production Composer dependencies
echo "[3/6] Installing Composer dependencies..."
cd "${BUILD_DIR}/${PACKAGE_NAME}"
composer install --no-dev --optimize-autoloader --no-interaction
cd ../..

# Step 4: Clean up development files
echo "[4/6] Removing development files..."
cd "${BUILD_DIR}/${PACKAGE_NAME}"

# Remove development documentation and test files
rm -rf docs/ tests/ node_modules/

# Remove all markdown documentation except USER_MANUAL.md
find . -maxdepth 1 -type f -name "*.md" ! -name "USER_MANUAL.md" -delete
find . -maxdepth 1 -type f -name "*.txt" -delete

# Remove development scripts
rm -f build-production.sh
rm -f setup-smtp.sh
rm -f create-confirmation-page.php
rm -f generate-dummy-data.php
rm -f verify-fix.php
rm -f pcq-smtp-config.php
rm -f wp-config-smtp.php

# Remove test files
find . -type f -name "*.test.js" -delete
find . -type f -name "*.test.php" -delete

# Remove editor/IDE files
find . -name ".DS_Store" -delete
find . -name "Thumbs.db" -delete
find . -name "*.swp" -delete
find . -name "*.swo" -delete
find . -name "*~" -delete

# Remove hidden development files
rm -f .gitignore
rm -f .editorconfig

# Remove package management files
rm -f package.json
rm -f package-lock.json
rm -f composer.lock
rm -f phpunit.xml

# Optimize mPDF vendor directory
if [ -d "vendor/mpdf/mpdf" ]; then
    echo "  Optimizing mPDF library..."
    
    # Remove mPDF development files
    cd vendor/mpdf/mpdf
    rm -f phpstan-baseline.neon ruleset.xml CHANGELOG.md CREDITS.txt README.md
    rm -rf tmp/
    
    # Keep only essential DejaVu fonts, remove all others
    cd ttfonts
    # Move DejaVu fonts to temp directory
    mkdir -p ../temp_fonts
    mv DejaVu*.ttf ../temp_fonts/ 2>/dev/null || true
    # Remove all fonts
    rm -f *.ttf *.otf
    # Restore DejaVu fonts
    mv ../temp_fonts/*.ttf . 2>/dev/null || true
    rmdir ../temp_fonts
    
    cd ../../../..
    echo "  ✓ Reduced mPDF fonts from 83 to $(ls -1 vendor/mpdf/mpdf/ttfonts/*.ttf 2>/dev/null | wc -l | tr -d ' ') essential fonts"
fi

cd ../..

# Step 5: Generate .mo files from .po files
echo "[5/6] Compiling language files..."
cd "${BUILD_DIR}/${PACKAGE_NAME}/languages"
for po_file in *.po; do
    if [ -f "$po_file" ]; then
        mo_file="${po_file%.po}.mo"
        echo "  Compiling ${po_file} -> ${mo_file}"
        msgfmt "$po_file" -o "$mo_file"
    fi
done
cd ../../..

# Step 6: Create ZIP package
echo "[6/6] Creating deployment package..."
cd "${BUILD_DIR}"
zip -r "../${DIST_DIR}/${PACKAGE_NAME}.zip" "${PACKAGE_NAME}" -q
cd ..

# Calculate package size
PACKAGE_SIZE=$(du -h "${DIST_DIR}/${PACKAGE_NAME}.zip" | cut -f1)

echo ""
echo "=========================================="
echo "✓ Build completed successfully!"
echo "=========================================="
echo "Package: ${DIST_DIR}/${PACKAGE_NAME}.zip"
echo "Size: ${PACKAGE_SIZE}"
echo ""
echo "Installation Instructions:"
echo "1. Upload ${PACKAGE_NAME}.zip to WordPress"
echo "2. Navigate to Plugins → Add New → Upload Plugin"
echo "3. Activate the plugin"
echo "4. Configure settings under Pro Clean menu"
echo "=========================================="

# Create installation instructions file
cat > "${DIST_DIR}/INSTALLATION.txt" << EOF
Pro Clean Quotation System v${VERSION}
Installation Instructions
========================================

INSTALLATION STEPS:

1. Backup your WordPress site before installation
2. Upload ${PACKAGE_NAME}.zip via WordPress Admin:
   - Go to Plugins → Add New → Upload Plugin
   - Choose the ZIP file
   - Click "Install Now"
3. Activate the plugin
4. Configure initial settings:
   - Go to Pro Clean → Settings
   - Configure email notifications
   - Set pricing rules
   - Add services and employees

SYSTEM REQUIREMENTS:

- WordPress 6.4 or higher
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+

RECOMMENDED PLUGINS (Optional):

- MotoPress Appointment Lite: Enhanced booking features
- WooCommerce: Advanced payment processing

POST-INSTALLATION:

1. Test the quote form using shortcode: [pcq_quote_form]
2. Test the booking form using: [pcq_booking_form]
3. Configure email templates
4. Set up employee schedules

SUPPORT:

- Documentation: See USER_MANUAL.md in plugin directory
- Plugin Settings: Pro Clean → Settings

========================================
EOF

echo ""
echo "Installation instructions saved to:"
echo "${DIST_DIR}/INSTALLATION.txt"
echo ""
