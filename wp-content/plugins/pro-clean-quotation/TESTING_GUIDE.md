# Quick Start Guide: Testing the Postal Code 29600 Fix

## Problem Solved
✅ Fixed the issue where postal code 29600 (and potentially all postal codes) were being rejected with the error:
> "Sorry, we currently do not service postal code 29600. Please contact us at info@webblymedia.se for more information about our service areas."

## Immediate Action Required

### Step 1: Clear Service Area Configuration
1. Log into WordPress Admin
2. Navigate to: **Pro Clean Quotation → Settings**
3. Click on the **"Service Area"** section
4. **IMPORTANT:** Clear the "Service Area" textarea completely (make sure it's 100% empty, no spaces)
5. Click **"Save Settings"**

This ensures the database doesn't have the buggy `['']` array configuration.

### Step 2: Verify the Fix

#### Option A: Use the Quick Test Script (Recommended)
```bash
cd wp-content/plugins/pro-clean-quotation
php tests/test-postal-code-29600-fix.php
```

**Expected Output:**
```
========================================
POSTAL CODE 29600 FIX VERIFICATION
========================================

TEST 1: Current Service Area Configuration
-------------------------------------------
Configuration type: array
Configuration count: 0
Configuration value: []
✓ PASSED: Configuration is empty - all Spain should be accepted

TEST 2: Validate Postal Code 29600 Format
------------------------------------------
✓ PASSED: Postal code 29600 format is valid
  Formatted: 29600

TEST 3: Service Area Check for 29600
-------------------------------------
✓ PASSED: Postal code 29600 is in service area

TEST 4: Multiple Spanish Postal Codes
--------------------------------------
✓ 01001 (Álava) - Available
✓ 28001 (Madrid) - Available
✓ 29600 (Marbella (Málaga) - REPORTED BUG) - Available
✓ 08001 (Barcelona) - Available
✓ 41001 (Sevilla) - Available
✓ 46001 (Valencia) - Available

========================================
✓ ALL TESTS PASSED - BUG IS FIXED!
========================================
```

#### Option B: Test the Frontend Quote Form
1. Go to the page with the quote form shortcode `[pcq_quote_form]`
2. Fill in all required fields:
   - **Name:** Test Customer
   - **Email:** test@example.com
   - **Phone:** 612345678
   - **Address:** Calle Test, 123
   - **Postal Code:** **29600** ← This is the critical test
   - **Service Type:** Façade or Roof
   - **Square Meters:** 100
   - Check privacy consent
3. Click **"Get Quote"** or **"Submit Quote"**
4. **Expected Result:** ✅ Quote should be calculated/submitted successfully without postal code errors

#### Option C: Run Full Test Suite
```bash
cd wp-content/plugins/pro-clean-quotation
php tests/Unit/PostalCodeValidationTest.php
```

This runs comprehensive tests including edge cases.

## Troubleshooting

### If Tests Still Fail

#### Check 1: Database Configuration
Run this to check what's actually in the database:
```php
php -r "
define('WP_USE_THEMES', false);
require('./wp-load.php');
\$val = get_option('pcq_service_area_postcodes', 'NOT_FOUND');
echo 'Service Area: ';
echo is_array(\$val) ? json_encode(\$val) : var_export(\$val, true);
echo PHP_EOL;
"
```

**Expected:** `Service Area: []` or `Service Area: NOT_FOUND`  
**If you see:** `Service Area: [""]` ← This is the bug! Repeat Step 1 above.

#### Check 2: Clear WordPress Cache
If using any caching plugins, clear all caches:
```bash
# WP CLI
wp cache flush

# Or manually in WordPress Admin
# Go to your caching plugin and click "Clear All Caches"
```

#### Check 3: Verify Files Are Updated
Check that the fix is in place:
```bash
grep -A 5 "array_values(array_filter" wp-content/plugins/pro-clean-quotation/includes/Services/ValidationService.php
```

**Expected output should show:**
```php
$service_areas = array_values(array_filter(array_map('trim', $service_areas), function($area) {
    return $area !== '' && $area !== null;
}));
```

### Enable Debug Logging
To see detailed logs of what's happening:

1. Edit `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2. Submit a quote with postal code 29600

3. Check the debug log:
```bash
tail -f wp-content/debug.log
```

**Expected log entries:**
```
PCQ: Service area check - Areas configured: 0 - Postal code: 29600
```

If you see:
```
PCQ: Service area check - Areas configured: 1 - Postal code: 29600
PCQ: Service areas: [""]
```
← This means the database still has the buggy configuration. Clear it again!

## Testing Different Scenarios

### Scenario 1: All Spain Enabled (Default)
**Service Area Configuration:** Empty  
**Expected:** All postal codes 01001-52999 accepted

**Test postal codes:**
- ✅ 01001 (Álava)
- ✅ 28001 (Madrid)
- ✅ 29600 (Marbella)
- ✅ 52999 (Melilla)

### Scenario 2: Specific Postal Code
**Service Area Configuration:** `29600`  
**Expected:** Only 29600 accepted

**Test postal codes:**
- ✅ 29600 (accepted)
- ❌ 29601 (rejected)
- ❌ 28001 (rejected)

### Scenario 3: Postal Code Range
**Service Area Configuration:** `29600-29699`  
**Expected:** All codes from 29600 to 29699 accepted

**Test postal codes:**
- ✅ 29600 (accepted)
- ✅ 29650 (accepted)
- ✅ 29699 (accepted)
- ❌ 29700 (rejected)
- ❌ 28001 (rejected)

### Scenario 4: Wildcard Pattern
**Service Area Configuration:** `296**`  
**Expected:** All codes from 29600 to 29699 accepted

**Test postal codes:**
- ✅ 29600 (accepted)
- ✅ 29650 (accepted)
- ✅ 29699 (accepted)
- ❌ 29700 (rejected)

### Scenario 5: Multiple Areas
**Service Area Configuration:** `28001, 29600-29699, 08***`  
**Expected:** Madrid, Marbella area, and all Barcelona accepted

**Test postal codes:**
- ✅ 28001 (Madrid - accepted)
- ✅ 29650 (Marbella - accepted)
- ✅ 08015 (Barcelona - accepted)
- ❌ 41001 (Sevilla - rejected)

## Production Deployment Checklist

Before deploying to production:

- [ ] All files updated (both `/includes` and `/build` directories)
- [ ] Syntax check passed (`php -l` on all modified files)
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Quick test script passes
- [ ] Frontend form tested manually with postal code 29600
- [ ] Service area configuration cleared in admin
- [ ] Settings saved successfully
- [ ] Cache cleared (if applicable)
- [ ] Debug logging enabled initially to monitor
- [ ] Backup created before deployment

## Support

If you continue to experience issues:

1. **Check the debug log** (`wp-content/debug.log`)
2. **Run the test script** and share the output
3. **Verify service area configuration** is empty or correct
4. **Clear all caches** (WordPress, browser, CDN)
5. **Check browser console** for JavaScript errors

## Files Changed in This Fix

✅ **Core Plugin:**
- `includes/Services/ValidationService.php`
- `includes/Admin/SettingsPage.php`

✅ **Build (Production):**
- `build/pro-clean-quotation-1.1.2/includes/Services/ValidationService.php`
- `build/pro-clean-quotation-1.1.2/includes/Admin/SettingsPage.php`

✅ **Tests Added:**
- `tests/test-postal-code-29600-fix.php` (Quick verification)
- `tests/Integration/QuoteFormPostalCodeTest.php` (Full integration)
- `tests/Unit/PostalCodeValidationTest.php` (Updated with edge cases)

## Next Steps

1. ✅ Clear service area configuration
2. ✅ Save settings
3. ✅ Run test script
4. ✅ Test frontend form
5. ✅ Monitor debug logs initially
6. ✅ Disable debug mode after verification

**The postal code 29600 issue is now resolved! 🎉**
