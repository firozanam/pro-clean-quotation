#!/bin/bash

# Pro Clean Quotation - Production Build Script
# This script creates a distributable version of the plugin
# Excludes development dependencies and unnecessary files

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

# Copy composer.json (production only)
cp composer.json "$BUILD_PLUGIN_DIR/"

# Copy directories
cp -r assets "$BUILD_PLUGIN_DIR/"
cp -r includes "$BUILD_PLUGIN_DIR/"
cp -r languages "$BUILD_PLUGIN_DIR/"
cp -r templates "$BUILD_PLUGIN_DIR/"

# Copy vendor - PRODUCTION DEPENDENCIES ONLY
if [ -d "vendor" ]; then
    echo "Copying production vendor dependencies..."
    mkdir -p "$BUILD_PLUGIN_DIR/vendor"
    
    # Copy autoload files
    cp vendor/autoload.php "$BUILD_PLUGIN_DIR/vendor/"
    cp -r vendor/composer "$BUILD_PLUGIN_DIR/vendor/"
    
    # Production dependencies only (from composer.json require section)
    # mpdf and its dependencies
    [ -d "vendor/mpdf" ] && cp -r vendor/mpdf "$BUILD_PLUGIN_DIR/vendor/"
    [ -d "vendor/setasign" ] && cp -r vendor/setasign "$BUILD_PLUGIN_DIR/vendor/"
    [ -d "vendor/psr" ] && cp -r vendor/psr "$BUILD_PLUGIN_DIR/vendor/"
    [ -d "vendor/paragonie" ] && cp -r vendor/paragonie "$BUILD_PLUGIN_DIR/vendor/"
    [ -d "vendor/myclabs" ] && cp -r vendor/myclabs "$BUILD_PLUGIN_DIR/vendor/"
    
    echo "Skipping dev dependencies (phpunit, mockery, brain, sebastian, etc.)"
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
find "$BUILD_PLUGIN_DIR" -name "*.md" -path "*/vendor/*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "CHANGELOG*" -path "*/vendor/*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "LICENSE*" -path "*/vendor/*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "composer.json" -path "*/vendor/*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".travis.yml" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".scrutinizer.yml" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name "infection.json*" -exec rm -f {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR" -name ".psalm" -exec rm -rf {} + 2>/dev/null || true

# Remove test/doc directories from vendor
echo "Removing tests and docs from vendor..."
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "tests" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "test" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "Tests" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "docs" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "doc" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "examples" -exec rm -rf {} + 2>/dev/null || true
find "$BUILD_PLUGIN_DIR/vendor" -type d -name "example" -exec rm -rf {} + 2>/dev/null || true

# Clean up mPDF fonts - keep only essential Western European fonts
echo "Optimizing mPDF fonts (removing unnecessary fonts)..."
TTFONTS_DIR="$BUILD_PLUGIN_DIR/vendor/mpdf/mpdf/ttfonts"
if [ -d "$TTFONTS_DIR" ]; then
    # Create temp directory for fonts we want to keep
    mkdir -p "$TTFONTS_DIR.keep"
    
    # Keep only DejaVu fonts (default) and FreeSans/FreeSerif for Western languages
    cp "$TTFONTS_DIR"/DejaVu*.ttf "$TTFONTS_DIR.keep/" 2>/dev/null || true
    cp "$TTFONTS_DIR"/DejaVuinfo.txt "$TTFONTS_DIR.keep/" 2>/dev/null || true
    cp "$TTFONTS_DIR"/FreeSans*.ttf "$TTFONTS_DIR.keep/" 2>/dev/null || true
    cp "$TTFONTS_DIR"/FreeSerif*.ttf "$TTFONTS_DIR.keep/" 2>/dev/null || true
    cp "$TTFONTS_DIR"/FreeMono*.ttf "$TTFONTS_DIR.keep/" 2>/dev/null || true
    
    # Remove all fonts and restore only the ones we need
    rm -rf "$TTFONTS_DIR"
    mv "$TTFONTS_DIR.keep" "$TTFONTS_DIR"
    
    FONT_COUNT=$(ls -1 "$TTFONTS_DIR"/*.ttf 2>/dev/null | wc -l | tr -d ' ')
    echo "  Kept $FONT_COUNT essential fonts (removed CJK, ancient scripts, etc.)"
fi

# Remove plugin test directories if present
rm -rf "$BUILD_PLUGIN_DIR/tests" 2>/dev/null || true
rm -rf "$BUILD_PLUGIN_DIR/node_modules" 2>/dev/null || true

# Create zip file
echo "Creating zip archive..."
cd "$BUILD_DIR"
rm -f "$ZIP_FILE"
zip -rq "$ZIP_FILE" "$PLUGIN_NAME-$VERSION" -x "*.DS_Store" -x "*__MACOSX*"

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
echo "Vendor packages included:"
ls -la "$BUILD_PLUGIN_DIR/vendor/" 2>/dev/null || echo "  (no vendor directory)"
echo ""
