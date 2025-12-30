# Pro Clean Quotation System - User Manual

**Version:** 1.0.0  
**For:** Façade & Roof Cleaning Services  
**Platform:** WordPress  
**Last Updated:** December 2025

---

## 📖 Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Managing Quotes](#managing-quotes)
4. [Managing Bookings & Appointments](#managing-bookings--appointments)
5. [Calendar Management](#calendar-management)
6. [Services & Pricing](#services--pricing)
7. [Employee Management](#employee-management)
8. [Settings Configuration](#settings-configuration)
9. [Email System](#email-system)
10. [Adding Forms to Your Website](#adding-forms-to-your-website)
11. [Frequently Asked Questions](#frequently-asked-questions)
12. [Troubleshooting](#troubleshooting)

---

## 🚀 Getting Started

### What This Plugin Does

The Pro Clean Quotation System automates your entire quote-to-booking process:

✅ **Customers can:**
- Get instant price quotes based on property size
- Receive quotes via email with PDF attachments
- Book appointments directly online
- Receive automated reminders 24 hours before service

✅ **You can:**
- Manage all quotes and bookings from one dashboard
- Set your own pricing rules (no coding required)
- Track customer history and interactions
- Monitor email delivery
- Integrate with external systems via webhooks

### First-Time Setup (5 Minutes)

1. **Go to WordPress Admin** → **Quotations** → **Settings**
2. **Fill in Company Information:**
   - Company Name
   - Email Address
   - Phone Number
   - Service Area (postal codes)
3. **Set Your Pricing** (see [Pricing Configuration](#pricing-configuration))
4. **Configure Email Settings** (see [Email Configuration](#email-configuration))
5. **Add Forms to Your Website** (see [Adding Forms](#adding-forms-to-your-website))

---

## 📊 Dashboard Overview

**Location:** Quotations → Dashboard

The dashboard gives you a quick snapshot of your business:

### Key Statistics

- **Quotes Today:** Number of new quote requests received today
- **Pending Quotes:** Quotes waiting for customer response (last 30 days)
- **This Week Revenue:** Total estimated value of quotes this week
- **Conversion Rate:** Percentage of quotes that became bookings (last 30 days)

### Recent Quotes Table

Displays the 5 most recent quote requests with:
- Quote number
- Customer name and email
- Service type and property size
- Total amount
- Status (New, Viewed, Booked, Expired)
- Date submitted
- Quick actions (View button)

### Quick Actions

Four shortcut cards to common tasks:
- **Pricing Settings** → Update service rates
- **Email Templates** → Customize email content
- **Form Settings** → Configure form fields
- **Integration** → Manage plugin connections

### System Status

Real-time health check showing:
- Plugin version
- WordPress version
- PHP version
- MotoPress Appointment status (for advanced booking)
- WooCommerce status (for online payments)
- Email notifications status

---

## 📝 Managing Quotes

**Location:** Quotations → Quotes

### Understanding Quote Statuses

| Status | Meaning | What to Do |
|--------|---------|------------|
| **New** | Just submitted, not yet viewed | Review and contact customer |
| **Viewed** | You've opened the quote | Follow up if needed |
| **Booked** | Customer scheduled service | Prepare for appointment |
| **Expired** | Quote validity period passed | Contact customer to update |
| **Declined** | Customer decided not to proceed | Archive or delete |

### Viewing Quote Details

1. Go to **Quotations** → **Quotes**
2. Click **View** on any quote
3. You'll see:
   - Complete customer information
   - Property details (size, type, materials)
   - Service requirements
   - **Price Breakdown:**
     - Base Rate
     - Size-based cost (per sqm/linear meter)
     - Complexity adjustments
     - Subtotal
     - VAT/Tax
     - **Total Amount**
   - Quote validity period
   - Email history (sent/opened)

### Editing a Quote

1. Click **Edit** on the quote
2. You can modify:
   - Customer contact information
   - Property details
   - Price adjustments (manual override)
   - Quote status
   - Validity date
3. Click **Save** to update

> **Note:** Editing a quote does NOT automatically resend the email. Use the "Resend Email" action if needed.

### Converting Quote to Booking

**Fastest way to create a booking from a quote:**

1. Open the quote details
2. Click **Convert to Booking** button
3. You'll be redirected to the appointment form with customer details pre-filled
4. Select date and time
5. Click **Create Appointment**

### Searching & Filtering Quotes

**Search Options:**
- By customer name or email
- By status (dropdown filter)
- By service type (Façade, Roof, Both)
- By date range

**Example:** To find all pending quotes from last week:
1. Select "New" from Status filter
2. Use date picker to select last week
3. Click **Filter**

### Deleting Quotes

1. Click the **Delete** link (with trash icon)
2. Confirm deletion
3. Quote is permanently removed

> **Warning:** Deletion is permanent. Consider changing status to "Declined" instead of deleting for record-keeping.

---

## 📅 Managing Bookings & Appointments

**Location:** Quotations → Appointments

### Booking Statuses Explained

| Status | Meaning | Customer Notified? |
|--------|---------|-------------------|
| **Pending** | Awaiting confirmation | Yes (booking confirmation sent) |
| **Confirmed** | Service scheduled | Yes |
| **In Progress** | Team is on-site | No |
| **Completed** | Job finished | Optional (completion email) |
| **Cancelled** | Booking cancelled | Yes (if via system) |

### Viewing Appointment Details

1. Go to **Quotations** → **Appointments**
2. Click **View** on any appointment
3. Details shown:
   - Customer information (name, email, phone)
   - Service date and time window
   - Service type and property details
   - Total amount and payment status
   - **Original quote reference** (if converted)
   - Assigned employee (if set)
   - Special notes/requirements
   - Status history

### Creating Manual Appointments

**Use this when customer books via phone or in-person:**

1. Go to **Quotations** → **Appointments**
2. Click **Add New Appointment**
3. Fill in the form:
   - **Customer Information** (name, email, phone)
   - **Service Details** (type, date, time)
   - **Property Information**
   - **Pricing** (or link to existing quote)
   - **Notes** (special requirements)
4. Select **Assigned Employee** (optional)
5. Click **Create Appointment**

### Editing Appointments

1. Click **Edit** on the appointment
2. You can change:
   - Date and time (checks availability)
   - Assigned employee
   - Status
   - Notes
   - Payment status
3. Click **Save Changes**

> **Important:** Rescheduling sends an automatic email to the customer with the new date/time.

### Cancelling Appointments

1. Open the appointment
2. Click **Cancel Appointment**
3. Enter cancellation reason (optional)
4. Confirm cancellation
5. Customer receives automatic cancellation email

**Cancellation Policy Notes:**
- Free cancellation up to 48 hours before service
- System enforces this policy automatically
- Reason is logged for your records

### Sending Manual Reminders

1. Open the appointment details
2. Click **Send Reminder** button
3. Immediate reminder email sent to customer

> **Note:** Automated 24-hour reminders are sent automatically. Use manual reminders only for special cases.

---

## 🗓️ Calendar Management

**Location:** Quotations → Calendar

### Calendar Views

Switch between different views:
- **Month View:** See all bookings for the month
- **Week View:** Detailed weekly schedule
- **Day View:** Hour-by-hour breakdown

### Understanding Calendar Colors

Bookings are color-coded by service type:
- **Blue:** Façade cleaning
- **Green:** Roof cleaning
- **Purple:** Both services
- **Red:** Cancelled bookings
- **Gray:** Completed bookings

### Managing Availability

**Setting Blocked Dates (Holidays/Maintenance):**

1. Go to **Quotations** → **Settings** → **Integration** tab
2. Scroll to **Availability Overrides**
3. Add dates you're NOT available:
   - Click **Add Blocked Date**
   - Select date
   - Enter reason (e.g., "Holiday", "Equipment maintenance")
   - Click **Save**

**Business Hours Configuration:**

1. Go to **Quotations** → **Settings** → **General** tab
2. Configure hours for each day:
   - Monday-Friday: Default 8:00-18:00
   - Saturday: Default 9:00-15:00
   - Sunday: Default OFF
3. Enable/disable days as needed

**Booking Capacity Limits:**

Control how many bookings per day:
- Go to **Settings** → **General**
- Set **Maximum Daily Bookings** (default: 3)
- System automatically blocks new bookings when limit reached

---

## 🛠️ Services & Pricing

### Managing Services

**Location:** Quotations → Services

**Default Services:**
- Façade Cleaning
- Roof Cleaning
- Both Services (combined)

**Adding Custom Services:**

1. Click **Add New Service**
2. Fill in:
   - **Service Name** (e.g., "Gutter Cleaning")
   - **Description** (visible to customers)
   - **Category** (optional grouping)
   - **Duration** (minutes per service)
   - **Base Price** (starting price)
   - **Status** (Active/Inactive)
3. Click **Create Service**

**Editing Services:**

1. Click **Edit** on the service
2. Modify any fields
3. Click **Update Service**

> **Tip:** Set service to "Inactive" to hide it temporarily without deleting.

### Service Categories

**Location:** Quotations → Service Categories

Organize services into categories for better organization:

**Example Categories:**
- Exterior Cleaning
- Roof Services
- Maintenance Packages
- Emergency Services

**Adding Category:**
1. Click **Add New Category**
2. Enter **Name** and **Description**
3. Set **Display Order** (lower numbers appear first)
4. Click **Save**

### Pricing Configuration

**Location:** Quotations → Settings → Pricing Tab

#### Base Pricing

Set starting prices for each service:

| Field | Default | Description |
|-------|---------|-------------|
| **Façade Base Rate** | €150 | Minimum charge for façade cleaning |
| **Façade Per SQM** | €2.50 | Additional charge per square meter |
| **Façade Per Linear Meter** | €5.00 | Charge per linear meter |
| **Roof Base Rate** | €200 | Minimum charge for roof cleaning |
| **Roof Per SQM** | €3.00 | Additional charge per square meter |
| **Roof Per Linear Meter** | €6.00 | Charge per linear meter |

**Example Calculation:**
```
Customer requests façade cleaning:
- Property: 150 sqm, 45 linear meters

Base Rate:              €150.00
Size (150 sqm × €2.50): €375.00
Linear (45m × €5.00):   €225.00
───────────────────────────────
Subtotal:               €750.00
VAT (21%):              €157.50
───────────────────────────────
TOTAL:                  €907.50
```

#### Property Type Multipliers

Adjust pricing based on property type:

| Property Type | Multiplier | Effect |
|---------------|-----------|--------|
| **Residential** | 1.0× | Standard pricing |
| **Commercial** | 1.2× | +20% surcharge |
| **Industrial** | 1.5× | +50% surcharge |

**Why?** Commercial/industrial properties typically require more equipment, safety measures, and time.

#### Surface Material Multipliers

Different materials require different cleaning methods:

| Material | Multiplier | Effect |
|----------|-----------|--------|
| Brick | 1.0× | Standard |
| Stone | 1.1× | +10% |
| Glass | 1.3× | +30% (delicate) |
| Metal | 1.2× | +20% |
| Concrete | 1.0× | Standard |
| Composite | 1.4× | +40% (special care) |

#### Building Height Adjustment

Taller buildings = more equipment + safety measures:

- **Height Multiplier:** 5% per floor above ground level
- **Example:** 3-story building = +10% (2 floors above ground × 5%)

#### Minimum Quote Value

Set the minimum amount you'll accept:
- **Default:** €100
- **Purpose:** Covers travel and setup costs

#### Tax/VAT Configuration

- **Tax Rate:** 21% (default for Netherlands)
- **Tax Inclusive:** Enable if your prices include tax

> **Important:** After changing pricing, the system automatically clears the price cache.

### Automatic Pricing Features

The plugin includes smart pricing that adjusts automatically:

#### 1. Seasonal Pricing

**Automatically adjusts based on service date:**

| Season | Months | Adjustment |
|--------|--------|------------|
| **Peak Season** | March-August | +10% |
| **Normal Season** | September-November | Standard |
| **Off-Season** | December-February | -10% discount |

**Why?** Encourages bookings during slower periods.

#### 2. Demand-Based Pricing

**Adjusts based on booking volume:**

| Capacity | Adjustment | Reason |
|----------|------------|--------|
| ≥80% booked | +20% | High demand |
| ≥60% booked | +10% | Medium demand |
| <60% booked | Standard | Normal demand |

**Calculation:** Based on bookings for the requested service week vs. your maximum capacity.

#### 3. Bulk Discounts

**Automatic discounts for large properties:**

| Property Size | Discount | Example Savings |
|--------------|----------|-----------------|
| 5000+ sqm | 20% | €1,000 quote → €800 |
| 2000+ sqm | 15% | €1,000 quote → €850 |
| 1000+ sqm | 10% | €1,000 quote → €900 |
| 500+ sqm | 5% | €1,000 quote → €950 |

**Why?** Larger jobs = more efficient use of time and equipment.

#### 4. Repeat Customer Discounts

**Loyalty rewards for returning customers:**

| Completed Bookings | Discount | Recognition |
|-------------------|----------|-------------|
| 5+ bookings | 10% | VIP Customer |
| 2-4 bookings | 5% | Returning Customer |
| First booking | 0% | New Customer |

**How It Works:** System automatically recognizes customers by email address.

#### 5. Promotional Codes

**Create custom discount codes:**

1. Currently managed in code (planned admin UI in Phase 2)
2. **Example codes:**
   - `WELCOME10` → 10% off for orders €100+
   - `SPRING25` → €25 off for orders €200+
   - `BULK50` → 15% off large properties (max €50 discount)

**Phase 2 Feature:** Full promotional code management UI coming soon.

---

## 👥 Employee Management

**Location:** Quotations → Employees

### Adding Employees

1. Click **Add New Employee**
2. Fill in:
   - **Name** (e.g., "John Smith")
   - **Email** (for notifications)
   - **Phone**
   - **Description** (skills, experience)
   - **Status** (Active/Inactive)
   - **Working Hours** (when they're available)
3. Click **Save Employee**

### Assigning Employees to Bookings

**During booking creation:**
1. Open appointment form
2. Select employee from **Assigned Technician** dropdown
3. Save appointment

**For existing bookings:**
1. Edit the appointment
2. Change **Assigned Technician**
3. Save changes

### Employee Schedule Tracking

- View employee workload in Calendar view
- Filter calendar by employee
- See availability conflicts before assigning

> **Note:** Employee management integrates with MotoPress Appointment plugin if installed.

---

## ⚙️ Settings Configuration

**Location:** Quotations → Settings

The Settings page has 5 tabs:

### General Tab

**Company Information:**
- Company Name (appears on emails and PDFs)
- Company Email (customer contact email)
- Company Phone (displayed on website)
- Company Address (for invoices/PDFs)

**Service Area:**
- Postal Codes (comma-separated)
- Example: `1000, 2000, 3000`
- Leave empty to serve all areas
- System validates customer postcodes against this list

**Business Hours:**
- Configure for each day of week
- Set start time and end time
- Enable/disable specific days
- Affects booking availability

**Booking Configuration:**
- **Booking Buffer Time:** Minutes between bookings (default: 60)
- **Max Daily Bookings:** Maximum per day (default: 3)
- **Lead Time:** Minimum days before booking (default: 1)
- **Quote Validity:** Days quote remains valid (default: 30)

### Pricing Tab

*(See [Pricing Configuration](#pricing-configuration) above)*

All base rates, multipliers, and minimum charges are configured here.

### Email Tab

**Email Notifications:**
- Enable/disable all email notifications (master switch)

**Sender Information:**
- **From Name:** Name that appears in emails (e.g., "Pro Clean Services")
- **From Email:** Email address shown as sender
- **Admin Notification Email:** Where new quote alerts are sent

**Email Templates:**
- Templates are located in `/templates/email/` folder
- Customize HTML templates directly
- Variables available:
  - `{customer_name}` → Customer's name
  - `{quote_number}` → Quote reference number
  - `{total_price}` → Total amount
  - `{service_date}` → Booking date
  - And more...

**SMTP Configuration:**
- **Recommended:** Install "WP Mail SMTP" plugin for reliable delivery
- **Supported Services:** SendGrid, Mailgun, Gmail, etc.
- Without SMTP: Uses default PHP `mail()` function (less reliable)

### Form Tab

**Security & Validation:**

- **Rate Limiting:**
  - Maximum submissions per 5 minutes per IP
  - Default: 5 submissions
  - Prevents spam and abuse

- **Quote Validity:**
  - How long quotes remain valid
  - Default: 30 days
  - After expiry, status changes to "Expired"

- **Required Fields:**
  - Configure which form fields are mandatory
  - Currently set in code (admin UI planned)

- **Duplicate Prevention:**
  - 5-minute cooldown per email address
  - Prevents accidental double submissions

### Integration Tab

**MotoPress Appointment:**
- Enable/disable integration
- **Status Indicator:**
  - ✓ Green: Plugin active
  - ⚠ Orange: Plugin not installed
- **What It Does:** Enhanced booking management and calendar features

**WooCommerce (Optional):**
- Enable/disable integration
- **Required For:** Online deposit payments
- **Status Indicator:**
  - ✓ Green: Active - online payments available
  - ℹ Gray: Not installed - cash/bank transfer only

**Online Payments (if WooCommerce active):**
- **Enable Deposits:** Allow customers to pay online
- **Deposit Percentage:** % of total required (default: 20%)
- **Payment Methods:** Configured in WooCommerce settings

**Webhook Integration:**
- Configure webhook URL for external systems
- **Events Available:**
  1. `quote.submitted` → New quote received
  2. `quote.accepted` → Customer accepted quote
  3. `quote.rejected` → Customer declined
  4. `booking.created` → New booking made
  5. `booking.confirmed` → Booking confirmed
  6. `booking.completed` → Service completed
  7. `booking.cancelled` → Booking cancelled
  8. `payment.received` → Payment processed

**Webhook Features:**
- HMAC SHA-256 signature verification
- Automatic retry (3 attempts with exponential backoff)
- Delivery logging in database
- Payload includes all relevant data

---

## 📧 Email System

**Location:** Quotations → Email Logs

### Email Types Sent Automatically

| Email Type | Trigger | Sent To | Includes |
|------------|---------|---------|----------|
| **Quote Confirmation** | Customer submits form | Customer | PDF quote attachment |
| **Admin Notification** | New quote received | Admin | Quote summary |
| **Booking Confirmation** | Booking created | Customer | Service details, date/time |
| **Booking Reminder** | 24 hours before service | Customer | Service details |
| **Appointment Confirmation** | Appointment scheduled | Customer | Full appointment details |

### Monitoring Email Delivery

**Viewing Email Logs:**

1. Go to **Quotations** → **Email Logs**
2. See complete history:
   - **Email Type**
   - **Recipient**
   - **Subject Line**
   - **Sent Date/Time**
   - **Status** (Sent ✓ or Failed ✗)
   - **Error Message** (if failed)

**Filtering Logs:**
- Filter by email type (dropdown)
- Filter by status (sent/failed)
- Search by recipient email or subject
- Date range selection

### Resending Failed Emails

1. Find the failed email in logs
2. Click **View** to see details
3. Click **Resend Email** button
4. Confirm resend
5. Check status in logs

### Email Troubleshooting

**If emails aren't being delivered:**

1. **Check System Status:**
   - Dashboard → System Status
   - Verify "Email Notifications: Enabled"

2. **Install SMTP Plugin:**
   - Install "WP Mail SMTP" from WordPress plugins
   - Configure with SendGrid or Mailgun (free tier available)
   - Much more reliable than default `mail()` function

3. **Check Spam Folders:**
   - Ask customers to check spam/junk
   - Add your domain to their safe senders

4. **Verify Email Settings:**
   - Settings → Email Tab
   - Ensure "From Email" is valid
   - Use domain email (not gmail/yahoo)

5. **Test Email Function:**
   - Settings → Email Tab
   - Use "Send Test Email" button
   - Check if it arrives

**Common Issues:**

| Problem | Solution |
|---------|----------|
| Emails go to spam | Configure SPF/DKIM records in DNS |
| Emails not sending | Install WP Mail SMTP plugin |
| Attachments missing | Check PDF generation in settings |
| Wrong sender name | Update "From Name" in Email settings |

### Customizing Email Templates

**Template Files Location:**
```
/wp-content/plugins/pro-clean-quotation/templates/email/
```

**Available Templates:**
- `quote-confirmation.php` - Customer quote email
- `admin-notification.php` - Admin alert email
- `booking-confirmation.php` - Customer booking email
- `booking-reminder.php` - 24-hour reminder
- `appointment-confirmation.php` - Appointment details

**To Customize:**
1. Copy template to your theme folder:
   ```
   /wp-content/themes/your-theme/pro-clean-quotation/email/
   ```
2. Edit the HTML/CSS
3. Use available variables (documented in template comments)
4. Save and test

> **Tip:** Template overrides in theme folder won't be lost during plugin updates.

---

## 🌐 Adding Forms to Your Website

**Location:** Quotations → Shortcodes

The plugin provides 3 shortcodes to add forms to your pages:

### 1. Quote Request Form

**Full quote form with price calculation**

**Basic Shortcode:**
```
[pcq_quote_form]
```

**Customized Example:**
```
[pcq_quote_form title="Get Your Free Quote" columns="1" style="compact"]
```

**Parameters:**

| Parameter | Default | Options | Description |
|-----------|---------|---------|-------------|
| `title` | "Get Your Free Quote" | Any text | Form heading |
| `show_title` | true | true/false | Show/hide title |
| `style` | default | default, compact, modern | Visual style |
| `columns` | 2 | 1 or 2 | Form layout |

**Where to Use:**
- Homepage
- Services page
- Contact page
- Landing pages

### 2. Direct Booking Form

**Schedule service without quote**

**Basic Shortcode:**
```
[pcq_booking_form]
```

**Customized Example:**
```
[pcq_booking_form title="Schedule Your Cleaning" show_title="true"]
```

**Parameters:**

| Parameter | Default | Options | Description |
|-----------|---------|---------|-------------|
| `title` | "Book Your Service" | Any text | Form heading |
| `show_title` | true | true/false | Show/hide title |
| `quote_id` | - | Quote ID | Pre-fill from quote |

**Where to Use:**
- Booking page
- Thank you page (after quote)
- Direct booking page for existing customers

### 3. Quick Price Calculator

**Estimate prices without contact form**

**Basic Shortcode:**
```
[pcq_quote_calculator]
```

**Customized Example:**
```
[pcq_quote_calculator title="Estimate Your Price" show_contact_form="false"]
```

**Parameters:**

| Parameter | Default | Options | Description |
|-----------|---------|---------|-------------|
| `title` | "Quick Price Calculator" | Any text | Calculator heading |
| `show_title` | true | true/false | Show/hide title |
| `show_contact_form` | false | true/false | Add contact form after |

**Where to Use:**
- Pricing page
- Homepage (informational)
- FAQ page
- Blog posts about pricing

### How to Add Shortcodes

#### Method 1: WordPress Block Editor (Gutenberg)

1. Edit your page/post
2. Click **+** to add block
3. Search for "Shortcode"
4. Select **Shortcode** block
5. Paste shortcode (e.g., `[pcq_quote_form]`)
6. Click **Publish** or **Update**

#### Method 2: Classic Editor

1. Edit your page/post
2. Place cursor where you want the form
3. Type or paste the shortcode
4. Click **Publish** or **Update**

#### Method 3: Text Widget (Sidebar/Footer)

1. Go to **Appearance** → **Widgets**
2. Add **Text** or **Custom HTML** widget
3. Paste shortcode
4. Save widget

#### Method 4: Theme Template

Add directly to your theme files:
```php
<?php echo do_shortcode('[pcq_quote_form]'); ?>
```

### Styling Forms

Forms automatically inherit your theme's styles. To customize:

1. **Via Theme Customizer:**
   - Appearance → Customize → Additional CSS
   - Add custom CSS

2. **Example Custom CSS:**
```css
/* Customize quote form */
.pcq-quote-form {
    max-width: 800px;
    margin: 0 auto;
}

.pcq-quote-form .form-field {
    margin-bottom: 20px;
}

.pcq-quote-form button[type="submit"] {
    background-color: #007cba;
    padding: 15px 40px;
    font-size: 18px;
}
```

---

## ❓ Frequently Asked Questions

### General Questions

**Q: Do I need coding skills to use this plugin?**  
A: No! Everything is managed through the WordPress admin interface. No coding required.

**Q: Can I use this plugin in multiple languages?**  
A: Yes! The plugin is translation-ready and includes Dutch and French translations. You can add more languages using Poedit.

**Q: Does it work with my WordPress theme?**  
A: Yes! The plugin works with any WordPress theme. Forms automatically adapt to your theme's styling.

**Q: What if I'm already using a booking plugin?**  
A: The plugin works standalone but integrates seamlessly with MotoPress Appointment for enhanced features.

### Pricing Questions

**Q: Can I set different prices for different services?**  
A: Yes! Each service (façade, roof, etc.) has its own base rate and per-sqm pricing.

**Q: How do the automatic discounts work?**  
A: The system automatically applies:
- Seasonal pricing (peak/off-season)
- Bulk discounts for large properties
- Repeat customer discounts
- Demand-based pricing adjustments

All discounts are transparent and shown in the price breakdown.

**Q: Can I manually override prices?**  
A: Yes! You can edit any quote and manually adjust the pricing before sending to the customer.

**Q: How do I create promotional codes?**  
A: Currently, promo codes are managed in the code. A full admin interface for promotional codes is planned for Phase 2.

### Booking Questions

**Q: Can customers cancel or reschedule bookings?**  
A: Customers can request cancellation/rescheduling by contacting you. You then update it in the admin panel. Self-service customer portal is planned for Phase 2.

**Q: What happens if two bookings overlap?**  
A: The system prevents double-bookings automatically. When checking availability, it ensures no time conflicts.

**Q: Can I block specific dates (holidays)?**  
A: Yes! Go to Settings → Integration → Availability Overrides to block dates.

**Q: How far in advance can customers book?**  
A: You control this with the "Lead Time" setting (default: 1 day minimum). You can also set a maximum lead time if needed.

### Email Questions

**Q: Why are my emails going to spam?**  
A: This is common with default WordPress mail. Install "WP Mail SMTP" plugin and use a service like SendGrid or Mailgun. Also configure SPF/DKIM DNS records.

**Q: Can I customize the email templates?**  
A: Yes! Copy templates to your theme folder and edit the HTML. See [Customizing Email Templates](#customizing-email-templates).

**Q: How do I test if emails are working?**  
A: Go to Settings → Email Tab and use the "Send Test Email" button.

**Q: What if a customer doesn't receive their quote email?**  
A: Check Email Logs to see if it was sent. If failed, you can resend it. Also ask customer to check spam folder.

### Technical Questions

**Q: What are the minimum requirements?**  
A:
- WordPress 6.4+
- PHP 8.0+
- MySQL 8.0+
- HTTPS/SSL certificate recommended

**Q: Does it work on mobile devices?**  
A: Yes! All forms and admin interfaces are fully responsive and mobile-optimized.

**Q: Can I export customer data?**  
A: Yes! Use the export functionality in the Quotes and Appointments sections. GDPR-compliant data export is available.

**Q: Does it integrate with other systems?**  
A: Yes! Use webhooks to send data to external CRM, accounting software, or custom applications. See [Webhook Integration](#integration-tab).

**Q: Is customer data secure?**  
A: Yes! The plugin follows WordPress security best practices:
- All database queries use prepared statements (SQL injection protection)
- All user input is sanitized and validated
- CSRF tokens on all forms
- Rate limiting to prevent abuse
- Secure password storage

---

## 🔧 Troubleshooting

### Common Issues & Solutions

#### Issue: Forms Not Displaying

**Symptoms:** Shortcode appears as text on page  
**Solutions:**
1. Check shortcode spelling: `[pcq_quote_form]` not `[pcp_quote_form]`
2. Ensure plugin is activated
3. Clear cache (if using caching plugin)
4. Try different WordPress editor (Classic vs Block)

#### Issue: Quotes Not Saving

**Symptoms:** Form submits but no quote in admin  
**Solutions:**
1. Check if emails are being sent (Email Logs)
2. Verify database tables exist (check with database admin tool)
3. Check PHP error logs
4. Ensure proper file permissions
5. Deactivate other plugins temporarily to check for conflicts

#### Issue: Calendar Not Showing Bookings

**Symptoms:** Calendar displays but bookings don't appear  
**Solutions:**
1. Refresh the page
2. Check if bookings have correct dates
3. Verify booking status (only active bookings show)
4. Clear browser cache
5. Check JavaScript console for errors

#### Issue: Price Calculation Incorrect

**Symptoms:** Quoted price doesn't match expected amount  
**Solutions:**
1. Verify pricing settings (Settings → Pricing)
2. Check if automatic adjustments are applying:
   - Seasonal pricing (check date)
   - Bulk discounts (check property size)
   - Demand pricing (check booking volume)
3. Review property type multiplier
4. Verify tax/VAT rate
5. Check for active promotional codes

#### Issue: Reminders Not Sending

**Symptoms:** No reminder emails 24 hours before booking  
**Solutions:**
1. Check if reminders are enabled (Settings)
2. Verify WordPress cron is working:
   - Install "WP Crontrol" plugin to check
   - Ensure cron is not disabled in wp-config.php
3. Check Email Logs for failed attempts
4. Verify booking has correct date/time
5. Ensure customer email is valid

#### Issue: PDF Attachments Missing

**Symptoms:** Quote emails arrive but no PDF  
**Solutions:**
1. Check if mPDF library is installed (via Composer)
2. Verify temp directory is writable
3. Check PHP memory limit (increase if needed)
4. Review error logs for PDF generation errors
5. Test PDF generation manually in admin

#### Issue: Slow Performance

**Symptoms:** Admin pages load slowly  
**Solutions:**
1. Optimize database (delete old dummy data)
2. Clean up email logs (delete old entries)
3. Increase PHP memory limit
4. Enable caching (pricing cache is automatic)
5. Check for conflicting plugins
6. Upgrade hosting if on shared hosting

### Getting More Help

**Plugin Support Resources:**

1. **Documentation:**
   - This user manual
   - `/docs/quotation_system_spec.md` (technical specs)
   - `/tests/README.md` (testing guide)

2. **Check Error Logs:**
   - WordPress Debug Log: `/wp-content/debug.log`
   - Server Error Log: Ask your hosting provider
   - Browser Console: Press F12 (for JavaScript errors)

3. **System Information:**
   - Dashboard → System Status
   - Copy system info when reporting issues

4. **Contact Developer:**
   - Include system information
   - Describe steps to reproduce issue
   - Attach relevant screenshots
   - Check error logs first

---

## 📞 Quick Reference

### Essential Links

| Task | Location |
|------|----------|
| View Dashboard | Quotations → Dashboard |
| Manage Quotes | Quotations → Quotes |
| Manage Bookings | Quotations → Appointments |
| View Calendar | Quotations → Calendar |
| Update Pricing | Quotations → Settings → Pricing |
| Configure Email | Quotations → Settings → Email |
| Check Email Logs | Quotations → Email Logs |
| Get Shortcodes | Quotations → Shortcodes |

### Shortcode Quick Copy

```
Quote Form:      [pcq_quote_form]
Booking Form:    [pcq_booking_form]
Calculator:      [pcq_quote_calculator]
```

### Default Credentials

- **Admin Access:** Your WordPress admin login
- **Plugin Location:** Quotations menu (left sidebar)
- **Settings:** Quotations → Settings

### Support Contacts

- **Plugin Version:** 1.0.0
- **Documentation:** This file (USER_MANUAL.md)
- **Technical Specs:** /docs/quotation_system_spec.md

---

## 🎉 You're Ready to Go!

Congratulations! You now know how to:

✅ Manage quotes and bookings  
✅ Configure pricing rules  
✅ Set up email notifications  
✅ Add forms to your website  
✅ Monitor customer interactions  
✅ Customize settings for your business

**Next Steps:**

1. Complete the initial setup (5 minutes)
2. Add the quote form to your website
3. Test the complete flow (submit a test quote)
4. Customize pricing for your services
5. Configure email templates with your branding

**Need Help?** Refer to the [Troubleshooting](#troubleshooting) section or contact your developer.

---

**Happy Quoting! 🚀**
