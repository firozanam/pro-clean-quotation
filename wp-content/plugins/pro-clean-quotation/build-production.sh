#!/bin/bash

# Pro Clean Quotation - Production Build Script
# This script creates a distributable version of the plugin

set -e

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Extract version from main plugin file
VERSION=$(grep -m1 "Version:" pro-clean-quotation.php | sed 's/.*Version: *//' | tr -d '[:space:]')

if [ -z "$VERSION" ]; then
    echo "Error: Could not extract version from pro-clean-quotation.php"
    exit 1
fi

echo "========================================"
echo "Pro Clean Quotation - Production Build"
echo "Version: $VERSION"
echo "========================================"

# Define build directory
BUILD_DIR="$SCRIPT_DIR/build"
PLUGIN_NAME="pro-clean-quotation"
BUILD_PLUGIN_DIR="$BUILD_DIR/$PLUGIN_NAME-$VERSION"
ZIP_FILE="$BUILD_DIR/$PLUGIN_NAME-$VERSION.zip"

# Clean previous build
echo ""
echo "Cleaning previous build..."
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_PLUGIN_DIR"

# Files and directories to include
echo "Copying plugin files..."

# Copy main plugin file
cp pro-clean-quotation.php "$BUILD_PLUGIN_DIR/"

# Copy composer.json
cp composer.json "$BUILD_PLUGIN_DIR/"

# Copy directories
cp -r assets "$BUILD_PLUGIN_DIR/"
cp -r includes "$BUILD_PLUGIN_DIR/"
cp -r languages "$BUILD_PLUGIN_DIR/"
cp -r templates "$BUILD_PLUGIN_DIR/"

# Copy vendor if exists (for Composer dependencies)
if [ -d "vendor" ]; then
    echo "Copying vendor directory..."
    cp -r vendor "$BUILD_PLUGIN_DIR/"
fi

# Copy additional files if they exist
[ -f "README.txt" ] && cp README.txt "$BUILD_PLUGIN_DIR/"
[ -f "readme.txt" ] && cp readme.txt "$BUILD_PLUGIN_DIR/"
[ -f "LICENSE" ] && cp LICENSE "$BUILD_PLUGIN_DIR/"
[ -f "LICENSE.txt" ] && cp LICENSE.txt "$BUILD_PLUGIN_DIR/"
[ -f "CHANGELOG.md" ] && cp CHANGELOG.md "$BUILD_PLUGIN_DIR/"
[ -f "USER_MANUAL.md" ] && cp USER_MANUAL.md "$BUILD_PLUGIN_DIR/"

# Remove development files from build
echo "Removing development files..."
find "$BUILD_PLUGIN_DIR" -name ".git*" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".DS_Store" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "*.map" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "Thumbs.db" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".editorconfig" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".eslintrc*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".prettierrc*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "phpunit.xml*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "phpcs.xml*" -exec rm -f {} + 2>/dev/null || true

# Remove test directories if present
rm -rf "$BUILD_PLUGIN_DIR/tests" 2>/dev/null || true
rm -rf "$BUILD_PLUGIN_DIR/node_modules" 2>/dev/null || true

# Create zip file
echo "Creating zip archive..."
cd "$BUILD_DIR"
rm -f "$ZIP_FILE"
zip -r "$ZIP_FILE" "$PLUGIN_NAME-$VERSION" -x "*.DS_Store" -x "*__MACOSX*"

# Get file sizes
ZIP_SIZE=$(du -h "$ZIP_FILE" | cut -f1)
DIR_SIZE=$(du -sh "$PLUGIN_NAME-$VERSION" | cut -f1)

echo ""
echo "========================================"
echo "Build completed successfully!"
echo "========================================"
echo ""
echo "Build directory: $BUILD_PLUGIN_DIR"
echo "Directory size:  $DIR_SIZE"
echo ""
echo "Zip file: $ZIP_FILE"
echo "Zip size: $ZIP_SIZE"
echo ""
echo "Files included:"
ls -la "$BUILD_PLUGIN_DIR"
echo ""
