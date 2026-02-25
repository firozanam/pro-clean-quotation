# Pro Clean Quotation System - User Manual

**Version 1.3.0**  
**Last Updated: February 2026**

---

## Table of Contents

1. [Introduction](#1-introduction)
   - 1.1 [Overview](#11-overview)
   - 1.2 [Core Features](#12-core-features)
   - 1.3 [System Requirements](#13-system-requirements)
2. [Installation & Activation](#2-installation--activation)
   - 2.1 [Before You Begin](#21-before-you-begin)
   - 2.2 [Installation Methods](#22-installation-methods)
   - 2.3 [Activation](#23-activation)
   - 2.4 [Post-Installation Setup](#24-post-installation-setup)
3. [Initial Configuration](#3-initial-configuration)
   - 3.1 [Accessing Settings](#31-accessing-settings)
   - 3.2 [General Settings](#32-general-settings)
   - 3.3 [Pricing Settings](#33-pricing-settings)
   - 3.4 [Email Settings](#34-email-settings)
   - 3.5 [SMTP Configuration](#35-smtp-configuration)
   - 3.6 [Form Settings](#36-form-settings)
   - 3.7 [Integration Settings](#37-integration-settings)
4. [Usage & Functionality](#4-usage--functionality)
   - 4.1 [Dashboard Overview](#41-dashboard-overview)
   - 4.2 [Managing Services](#42-managing-services)
   - 4.3 [Managing Employees](#43-managing-employees)
   - 4.4 [Managing Quotes](#44-managing-quotes)
   - 4.5 [Managing Bookings](#45-managing-bookings)
   - 4.6 [Managing Appointments](#46-managing-appointments)
   - 4.7 [Calendar View](#47-calendar-view)
   - 4.8 [Customer Management](#48-customer-management)
   - 4.9 [Email Logs](#49-email-logs)
   - 4.10 [Using Shortcodes](#410-using-shortcodes)
5. [How It Works](#5-how-it-works)
   - 5.1 [Quote Flow Process](#51-quote-flow-process)
   - 5.2 [Pricing Calculation Engine](#52-pricing-calculation-engine)
   - 5.3 [Booking System](#53-booking-system)
   - 5.4 [Email Notification System](#54-email-notification-system)
   - 5.5 [PDF Generation](#55-pdf-generation)
   - 5.6 [Data Storage Architecture](#56-data-storage-architecture)
6. [Troubleshooting](#6-troubleshooting)
7. [Support](#7-support)

---

## 1. Introduction

### 1.1 Overview

The **Pro Clean Quotation System** is a comprehensive WordPress plugin designed specifically for cleaning service businesses specializing in façade and roof cleaning. The plugin automates the entire quotation and booking process, from initial customer inquiry to scheduled service appointment.

This powerful system enables your customers to:
- Request instant price quotes through an intuitive online form
- Receive detailed PDF quotations via email
- Book services directly from their quote
- Select preferred appointment times

For business owners, the plugin provides:
- Complete quote and booking management
- Automated email notifications
- Calendar-based appointment scheduling
- Customer relationship management
- Comprehensive reporting dashboard

### 1.2 Core Features

| Feature | Description |
|---------|-------------|
| **Instant Quote Calculator** | Real-time price calculation based on property size, service type, and various factors |
| **PDF Quote Generation** | Professional PDF quotations automatically generated and emailed to customers |
| **Online Booking System** | Customers can book services directly from their quote confirmation |
| **Calendar Management** | Visual calendar view of all appointments with drag-and-drop functionality |
| **Email Automation** | Automated confirmation emails, reminders, and admin notifications |
| **Service Management** | Define multiple cleaning services with custom pricing |
| **Employee Management** | Assign employees to services and manage availability |
| **Customer Database** | Centralized customer information and quote history |
| **Multi-language Support** | Compatible with WPML and Polylang for international operations |
| **Service Area Control** | Restrict services to specific postal codes or regions |

### 1.3 System Requirements

**Minimum Requirements:**
- WordPress version 6.4 or higher
- PHP version 8.0 or higher
- MySQL version 5.7 or higher
- HTTPS connection (recommended)

**Recommended Plugins (Optional):**
- **MotoPress Appointment Lite** - Enhanced booking management and calendar integration
- **WooCommerce** - Advanced payment processing for deposits and online payments

**Browser Compatibility:**
- Google Chrome (latest version)
- Mozilla Firefox (latest version)
- Safari (latest version)
- Microsoft Edge (latest version)

---

## 2. Installation & Activation

### 2.1 Before You Begin

Before installing the Pro Clean Quotation System, ensure you have:

1. **Administrator access** to your WordPress dashboard
2. **FTP or file manager access** (for manual installation)
3. A **backup** of your WordPress site (recommended)
4. Verified that your server meets the **minimum requirements**

### 2.2 Installation Methods

#### Method A: WordPress Admin Upload (Recommended)

1. Download the plugin ZIP file from your account
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins → Add New**
4. Click the **Upload Plugin** button at the top of the page
5. Click **Choose File** and select the downloaded ZIP file
6. Click **Install Now**
7. Wait for the installation to complete

#### Method B: FTP Upload

1. Extract the plugin ZIP file on your computer
2. Connect to your web server via FTP
3. Navigate to the `/wp-content/plugins/` directory
4. Upload the extracted `pro-clean-quotation` folder
5. Ensure all files have been transferred successfully

### 2.3 Activation

1. Go to **Plugins → Installed Plugins**
2. Locate **"Pro Clean Quotation System"** in the list
3. Click the **Activate** link
4. Upon activation, the plugin will:
   - Create necessary database tables
   - Set up default configuration options
   - Create required pages (booking page, confirmation page)
   - Schedule automated maintenance tasks

### 2.4 Post-Installation Setup

After activation, you'll see a new menu item labeled **"Quotations"** in your WordPress admin sidebar. A notice may appear recommending optional plugins (MotoPress Appointment and WooCommerce) for enhanced functionality.

**Immediate Next Steps:**
1. Configure your company information in Settings
2. Set up your service pricing
3. Add your cleaning services
4. Configure email notifications
5. Create frontend pages with shortcodes

---

## 3. Initial Configuration

### 3.1 Accessing Settings

1. From the WordPress admin dashboard, click **Quotations** in the sidebar
2. Navigate to **Settings** from the submenu
3. The settings page is organized into tabs:
   - General
   - Pricing
   - Email
   - SMTP
   - Form
   - Integration
   - Update

### 3.2 General Settings

The General tab contains your company information and service area configuration.

#### Company Information

| Field | Description | Required |
|-------|-------------|----------|
| **Company Name** | Your business name as it appears on quotes and emails | Yes |
| **Company Email** | Main contact email address | Yes |
| **Company Phone** | Contact phone number for customer inquiries | No |
| **Service Area** | Postal codes or ranges you service | No |

#### Service Area Configuration

The Service Area field allows you to restrict quote requests to specific geographic regions:

**Format Examples:**
- **Specific code:** `28001` - Serves only this postal code
- **Range:** `29600-29699` - Serves all codes in this range
- **Wildcard:** `296**` - Serves codes 29600 through 29699
- **Multiple areas:** `28***, 29***, 08***` - Serves multiple regions

**Important:** Leave the field empty to accept all valid Spanish postal codes (01001-52999).

### 3.3 Pricing Settings

Configure your service pricing structure in the Pricing tab.

#### Pricing Fields

| Setting | Description | Default |
|---------|-------------|---------|
| **Façade Base Rate (€)** | Fixed starting price for façade cleaning | €150.00 |
| **Façade Per SQM (€)** | Price per square meter for façade | €2.50 |
| **Roof Base Rate (€)** | Fixed starting price for roof cleaning | €200.00 |
| **Roof Per SQM (€)** | Price per square meter for roof | €3.00 |
| **Minimum Quote Value (€)** | Lowest acceptable quote amount | €100.00 |
| **VAT Rate (%)** | Tax percentage applied to quotes | 21% |

#### Pricing Formula

The final quote price is calculated using this formula:

```
Subtotal = Base Rate + (Square Meters × Rate per SQM) + (Linear Meters × Rate per Linear Meter) + Adjustments
Total = Subtotal + (Subtotal × VAT Rate)
```

**Adjustments** may include:
- Property type multipliers (commercial vs. residential)
- Building height surcharges
- Surface material complexity factors
- Custom field modifiers

### 3.4 Email Settings

Configure email notification behavior in the Email tab.

#### Email Configuration Options

| Setting | Description |
|---------|-------------|
| **Enable Email Notifications** | Master switch for all email notifications |
| **From Name** | Sender name displayed in customer emails |
| **From Email** | Sender email address |
| **Admin Notification Email** | Email address receiving new quote/booking alerts |
| **Quote Email Subject** | Customizable subject line for quote emails |
| **Booking Email Subject** | Customizable subject line for booking confirmations |

#### Email Templates

The plugin uses customizable HTML email templates for:
- Quote confirmation (sent to customer)
- Admin notification (sent to administrator)
- Booking confirmation (sent to customer)
- Booking reminder (sent before appointment)

### 3.5 SMTP Configuration

For reliable email delivery, configure SMTP settings in the SMTP tab.

#### SMTP Settings

| Field | Description | Example |
|-------|-------------|---------|
| **SMTP Host** | Mail server hostname | `smtp.gmail.com` |
| **SMTP Port** | Server port number | `587` |
| **Encryption** | SSL or TLS encryption | `TLS` |
| **Username** | SMTP authentication username | `your@email.com` |
| **Password** | SMTP authentication password | `********` |

**Testing SMTP:** Click the "Test SMTP" button to verify your configuration before saving.

### 3.6 Form Settings

Customize the quote form behavior in the Form tab.

#### Available Options

| Setting | Description |
|---------|-------------|
| **Required Fields** | Define which fields customers must complete |
| **Field Labels** | Customize field labels and help text |
| **Validation Rules** | Set minimum/maximum values for measurements |
| **Custom Fields** | Add additional form fields as needed |
| **Maintenance Mode** | Temporarily disable the quote form with a custom message |

### 3.7 Integration Settings

Configure third-party plugin integrations in the Integration tab.

#### MotoPress Appointment Integration

When MotoPress Appointment is installed and active:
- Appointments sync automatically with MotoPress calendar
- Employee availability respects MotoPress schedules
- Service durations match MotoPress configuration

#### WooCommerce Integration

When WooCommerce is active:
- Deposit payments can be collected online
- Full payment processing available
- Order history linked to quotes

---

## 4. Usage & Functionality

### 4.1 Dashboard Overview

The main dashboard provides an at-a-glance view of your quotation business.

**Access:** Quotations → Dashboard

#### Dashboard Statistics

| Metric | Description |
|--------|-------------|
| **Quotes Today** | Number of quotes submitted today |
| **Pending Quotes** | Quotes awaiting action |
| **This Week Revenue** | Total value of confirmed bookings this week |
| **Conversion Rate** | Percentage of quotes converted to bookings |

#### Dashboard Sections

1. **Recent Quotes** - Latest quote submissions with quick actions
2. **Upcoming Bookings** - Scheduled services in the next 7 days
3. **Quick Actions** - Shortcuts to common configuration tasks

### 4.2 Managing Services

Services are the core offerings customers can request quotes for.

**Access:** Quotations → Services

#### Adding a New Service

1. Click **Add New Service** at the top of the page
2. Complete the service form:

| Field | Description | Required |
|-------|-------------|----------|
| **Service Name** | Display name for the service | Yes |
| **Description** | Detailed service description | No |
| **Duration** | Estimated service duration (minutes) | Yes |
| **Base Rate** | Fixed starting price | Yes |
| **Rate per SQM** | Price per square meter | Yes |
| **Rate per Linear Meter** | Price per linear meter (for gutters, etc.) | No |
| **Category** | Service category for organization | No |
| **Color** | Calendar display color | No |
| **Status** | Active or Inactive | Yes |

3. Click **Save Service**

#### Service Categories

Organize services into categories for better management:

**Access:** Quotations → Service Categories

Categories help group similar services (e.g., "Façade Cleaning", "Roof Services", "Gutter Maintenance").

### 4.3 Managing Employees

Employees are team members who perform cleaning services.

**Access:** Quotations → Employees

#### Adding a New Employee

1. Click **Add New Employee**
2. Complete the employee form:

| Field | Description | Required |
|-------|-------------|----------|
| **Name** | Full name | Yes |
| **Email** | Contact email | No |
| **Phone** | Contact phone | No |
| **Description** | Notes about the employee | No |
| **Avatar** | Profile photo | No |
| **Services** | Services this employee can perform | No |
| **Working Hours** | Weekly availability schedule | No |
| **Status** | Active or Inactive | Yes |

3. Click **Save Employee**

#### Employee-Service Assignment

Assign employees to specific services:
1. Edit an employee record
2. Check the services they can perform
3. Save changes

The booking system will only show availability for employees assigned to the selected service.

### 4.4 Managing Quotes

Quotes are customer price requests submitted through your website.

**Access:** Quotations → Quotes

#### Quote List View

The quotes list displays:
- Quote number
- Customer name and email
- Service type
- Total amount
- Status
- Submission date
- Quick action buttons

#### Quote Statuses

| Status | Description |
|--------|-------------|
| **New** | Freshly submitted, no action taken |
| **Pending** | Under review |
| **Sent** | Quote email sent to customer |
| **Viewed** | Customer has opened the quote |
| **Booked** | Converted to a booking |
| **Accepted** | Customer approved the quote |
| **Rejected** | Customer declined the quote |
| **Expired** | Quote validity period has passed |
| **Cancelled** | Quote was cancelled |

#### Viewing a Quote

1. Click **View** on any quote row
2. The quote detail page shows:
   - Customer information
   - Property details
   - Service specifications
   - Price breakdown
   - Quote history

#### Quote Actions

From the quote view, you can:
- **Download PDF** - Generate and download the quote PDF
- **Resend Email** - Resend the quote confirmation email
- **Convert to Booking** - Create a booking from this quote
- **Edit** - Modify quote details
- **Change Status** - Update the quote status

### 4.5 Managing Bookings

Bookings are confirmed service appointments.

**Access:** Quotations → Bookings

#### Booking List View

The bookings list displays:
- Booking number
- Customer name
- Service type
- Service date and time
- Total amount
- Payment status
- Booking status

#### Booking Statuses

| Status | Description |
|--------|-------------|
| **Pending** | Awaiting confirmation or payment |
| **Confirmed** | Booking is confirmed |
| **In Progress** | Service is being performed |
| **Completed** | Service finished successfully |
| **Cancelled** | Booking was cancelled |
| **No-show** | Customer did not appear |

#### Creating a Booking

Bookings are typically created by customers from their quote, but administrators can also create bookings manually:

1. Click **Add New Booking**
2. Select an existing quote or enter details manually
3. Choose service date and time
4. Assign employee (optional)
5. Click **Create Booking**

### 4.6 Managing Appointments

Appointments are scheduled service times in the calendar system.

**Access:** Quotations → Appointments

#### Appointment Management

The appointments interface allows you to:
- View all scheduled appointments
- Create new appointments
- Edit existing appointments
- Cancel appointments
- Assign employees

### 4.7 Calendar View

The calendar provides a visual overview of all scheduled services.

**Access:** Quotations → Calendar

#### Calendar Features

- **Monthly/Weekly/Daily views** - Switch between time scales
- **Employee filtering** - View appointments by employee
- **Drag and drop** - Reschedule appointments by dragging
- **Color coding** - Services display in their assigned colors
- **Click to edit** - Click any appointment to view details

#### Navigating the Calendar

1. Use the arrow buttons to navigate between periods
2. Click "Today" to return to the current date
3. Use the filter dropdown to show specific employees
4. Click any appointment to view or edit details

### 4.8 Customer Management

The customer database stores all customer information.

**Access:** Quotations → Customers

#### Customer List

View all customers who have submitted quotes or bookings:
- Name and contact information
- Total quotes submitted
- Total bookings made
- Customer since date

#### Customer Details

Click any customer to view:
- Contact information
- Quote history
- Booking history
- Notes

### 4.9 Email Logs

Track all emails sent by the system.

**Access:** Quotations → Email Logs

#### Log Information

Each log entry shows:
- Email type (quote confirmation, booking reminder, etc.)
- Recipient email address
- Subject line
- Send date and time
- Status (sent/failed)
- Error messages (if failed)

### 4.10 Using Shortcodes

Shortcodes allow you to place plugin functionality on any page or post.

#### Available Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[pcq_quote_form]` | Displays the quote request form |
| `[pcq_booking_form]` | Displays the booking form |
| `[pcq_booking_confirmation]` | Displays booking confirmation message |
| `[pcq_quote_calculator]` | Displays a simplified price calculator |

#### Shortcode Attributes

**Quote Form:**
```
[pcq_quote_form title="Get Your Free Quote" show_title="true" style="default" columns="2"]
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `title` | Any text | "Get Your Free Quote" |
| `show_title` | true/false | true |
| `style` | default/minimal/modern | default |
| `columns` | 1/2 | 2 |

**Booking Form:**
```
[pcq_booking_form quote_id="123" title="Book Your Service" show_title="true"]
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `quote_id` | Specific quote ID | (auto-detected) |
| `title` | Any text | "Book Your Service" |
| `show_title` | true/false | true |

#### Creating Pages with Shortcodes

1. Go to **Pages → Add New**
2. Enter a page title (e.g., "Request a Quote")
3. Add the desired shortcode in the content area
4. Publish the page

**Recommended Page Structure:**
- **Quote Page:** Contains `[pcq_quote_form]`
- **Booking Page:** Contains `[pcq_booking_form]` (auto-created)
- **Confirmation Page:** Contains `[pcq_booking_confirmation]` (auto-created)

---

## 5. How It Works

### 5.1 Quote Flow Process

Understanding the complete quote workflow helps you manage customer expectations and system configuration.

#### Step-by-Step Process

```
┌─────────────────────────────────────────────────────────────────┐
│                    QUOTE SUBMISSION FLOW                        │
└─────────────────────────────────────────────────────────────────┘

1. CUSTOMER VISITS WEBSITE
   │
   ▼
2. FILLS OUT QUOTE FORM
   │  • Selects service type
   │  • Enters property measurements
   │  • Provides contact information
   │
   ▼
3. REAL-TIME VALIDATION
   │  • JavaScript validates inputs
   │  • Postal code checked against service area
   │  • Rate limiting prevents spam
   │
   ▼
4. AJAX CALCULATION REQUEST
   │  • Form data sent to server
   │  • QuoteCalculator processes request
   │  • Pricing engine applies rates
   │
   ▼
5. QUOTE RECORD CREATED
   │  • Unique quote number generated
   │  • Data stored in database
   │  • Security token created
   │
   ▼
6. PDF GENERATION
   │  • mPDF library creates PDF
   │  • Company branding applied
   │  • Price breakdown included
   │
   ▼
7. EMAIL NOTIFICATIONS
   │  • Customer receives quote + PDF
   │  • Admin receives notification
   │  • Email logged in system
   │
   ▼
8. CUSTOMER REVIEW
   │  • Customer views quote
   │  • Quote status updated to "Viewed"
   │  • Booking link provided
   │
   ▼
9. BOOKING (OPTIONAL)
   │  • Customer selects date/time
   │  • Availability checked
   │  • Booking confirmation sent
   │
   ▼
10. SERVICE COMPLETION
    │  • Appointment marked complete
    │  • Follow-up email sent (optional)
    │
    ▼
    END
```

### 5.2 Pricing Calculation Engine

The pricing engine calculates quotes using a sophisticated multi-factor formula.

#### Calculation Components

**1. Base Rate**
- Fixed starting price for each service type
- Covers basic service overhead

**2. Area-Based Pricing**
- Square meters × Rate per SQM
- Linear meters × Rate per Linear Meter (for gutters, edges)

**3. Adjustments**
Various factors can modify the base price:

| Factor | Effect |
|--------|--------|
| Property Type | Commercial properties may have higher rates |
| Building Height | Taller buildings require specialized equipment |
| Surface Material | Delicate surfaces may require special treatment |
| Roof Type | Complex roof structures increase difficulty |
| Custom Fields | Additional services or requirements |

**4. Tax Calculation**
- VAT applied to subtotal
- Configurable tax rate

**5. Minimum Charge**
- Ensures quotes meet minimum business requirements

#### Example Calculation

```
Service: Façade Cleaning
Property: 150 sqm residential building

Base Rate:           €150.00
Area Cost:           €375.00 (150 sqm × €2.50/sqm)
Height Adjustment:   € 25.00 (3-story building surcharge)
────────────────────────────
Subtotal:            €550.00
VAT (21%):           €115.50
────────────────────────────
TOTAL:               €665.50
```

### 5.3 Booking System

The booking system manages appointment scheduling with availability checking.

#### Availability Logic

```
┌─────────────────────────────────────────────────────────────────┐
│                  AVAILABILITY CHECK PROCESS                     │
└─────────────────────────────────────────────────────────────────┘

1. DATE SELECTION
   │
   ▼
2. BUSINESS HOURS CHECK
   │  • Is this a working day?
   │  • Is time within business hours?
   │
   ▼
3. EMPLOYEE AVAILABILITY
   │  • Are assigned employees available?
   │  • Do they work this service type?
   │
   ▼
4. EXISTING BOOKING CHECK
   │  • Any conflicts with existing appointments?
   │  • Buffer time respected?
   │
   ▼
5. RETURN AVAILABLE SLOTS
   │  • List of bookable time slots
   │  • Each marked available/unavailable
   │
   ▼
   END
```

#### Booking Creation

When a customer confirms a booking:
1. Quote token verified (prevents unauthorized bookings)
2. Time slot availability re-checked
3. Booking record created
4. Quote status updated to "Booked"
5. Confirmation emails sent
6. Reminder scheduled

### 5.4 Email Notification System

The email system handles all customer and admin communications.

#### Email Types

| Email Type | Recipient | Trigger |
|------------|-----------|---------|
| Quote Confirmation | Customer | Quote submitted |
| Admin Notification | Administrator | Quote submitted |
| Booking Confirmation | Customer | Booking created |
| Booking Reminder | Customer | 24 hours before appointment |
| Cancellation Notice | Customer | Booking cancelled |

#### Email Processing

```
1. Trigger Event Occurs
   │
   ▼
2. EmailManager Instantiated
   │
   ▼
3. Template Selected
   │  • HTML template loaded
   │  • Variables injected
   │
   ▼
4. PDF Generated (if applicable)
   │  • mPDF creates attachment
   │  • Stored temporarily
   │
   ▼
5. Email Sent via WordPress wp_mail()
   │  • Uses configured SMTP (if set)
   │  • Headers applied
   │
   ▼
6. Result Logged
   │  • Success/failure recorded
   │  • Error details captured
   │
   ▼
7. Cleanup
   • Temporary PDF deleted
```

### 5.5 PDF Generation

Professional PDF quotes are generated using the mPDF library.

#### PDF Contents

1. **Header**
   - Company logo or name
   - Company contact information

2. **Quote Metadata**
   - Quote number
   - Date issued
   - Valid until date

3. **Customer Information**
   - Name
   - Email
   - Phone
   - Property address

4. **Service Details**
   - Service type
   - Property measurements
   - Property specifications

5. **Price Breakdown**
   - Base rate
   - Area charges
   - Adjustments
   - Subtotal
   - VAT amount
   - Total

6. **Terms & Conditions**
   - Quote validity
   - Payment terms
   - Contact information

#### PDF Storage

- PDFs are generated on-demand
- Temporarily stored in `/wp-content/uploads/pro-clean-quotes/temp/`
- Automatically deleted after email delivery
- Daily cleanup removes files older than 24 hours

### 5.6 Data Storage Architecture

The plugin uses custom database tables for efficient data management.

#### Database Tables

| Table | Purpose |
|-------|---------|
| `wp_pq_quotes` | Stores all quote submissions |
| `wp_pq_bookings` | Stores confirmed bookings |
| `wp_pq_appointments` | Stores scheduled appointments |
| `wp_pq_customers` | Stores customer records |
| `wp_pq_services` | Stores service definitions |
| `wp_pq_service_categories` | Stores service categories |
| `wp_pq_employees` | Stores employee records |
| `wp_pq_employee_services` | Links employees to services |
| `wp_pq_email_logs` | Stores email sending history |
| `wp_pq_settings` | Stores plugin configuration |

#### Key Relationships

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Quotes     │────▶│   Bookings   │────▶│ Appointments │
└──────────────┘     └──────────────┘     └──────────────┘
       │                    │                    │
       │                    │                    │
       ▼                    ▼                    ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Customers   │     │   Services   │     │  Employees   │
└──────────────┘     └──────────────┘     └──────────────┘
```

---

## 6. Troubleshooting

### Common Issues

#### Quotes Not Submitting

**Symptoms:** Form hangs, error message, or no response

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify AJAX endpoint is accessible
3. Check PHP error logs
4. Ensure nonce verification passes
5. Verify rate limiting isn't blocking legitimate requests

#### Emails Not Sending

**Symptoms:** Customers don't receive confirmation emails

**Solutions:**
1. Check Email Logs for delivery status
2. Verify SMTP configuration
3. Test with "Test SMTP" button
4. Check spam folder
5. Verify email address is correct
6. Check WordPress cron is running

#### PDF Not Generating

**Symptoms:** No PDF attached to emails

**Solutions:**
1. Verify mPDF library is installed
2. Check PHP memory limit (increase if needed)
3. Verify uploads directory is writable
4. Check for PHP errors in logs

#### Calendar Not Loading

**Symptoms:** Calendar page shows empty or error

**Solutions:**
1. Verify FullCalendar library loads
2. Check admin JavaScript enqueues
3. Clear browser cache
4. Check for JavaScript conflicts with other plugins

#### Database Errors

**Symptoms:** "Table doesn't exist" or SQL errors

**Solutions:**
1. Deactivate and reactivate the plugin
2. Use Database Fixer in Settings
3. Manually run database update
4. Check WordPress database prefix

---

## 7. Support

### Getting Help

For technical support:

1. **Documentation** - Review this user manual thoroughly
2. **Email Logs** - Check for error messages
3. **Debug Mode** - Enable WordPress debug mode for detailed errors
4. **Contact Support** - Reach out with:
   - Plugin version
   - WordPress version
   - PHP version
   - Description of the issue
   - Steps to reproduce
   - Any error messages

### Plugin Updates

The plugin includes an automatic update checker:

1. Go to **Quotations → Settings → Update**
2. Click "Check for Updates"
3. If updates available, click "Update Now"
4. Always backup before updating

### Maintenance Mode

To temporarily disable quote submissions:

1. Go to **Quotations → Settings → Form**
2. Enable "Maintenance Mode"
3. Enter a custom message
4. Save settings

The quote form will display your message instead of the form.

---

## Appendix A: Quick Reference

### Admin Menu Structure

```
Quotations
├── Dashboard
├── Quotes
├── Bookings
├── Appointments
├── Calendar
├── Customers
├── Services
├── Service Categories
├── Employees
├── Settings
├── Email Logs
├── Shortcodes
└── Setup Booking Page
```

### Shortcode Quick Reference

| Shortcode | Purpose |
|-----------|---------|
| `[pcq_quote_form]` | Quote request form |
| `[pcq_booking_form]` | Booking form |
| `[pcq_booking_confirmation]` | Confirmation message |
| `[pcq_quote_calculator]` | Price calculator |

### Status Reference

**Quote Statuses:**
- `new` - Freshly submitted
- `pending` - Under review
- `sent` - Email sent
- `viewed` - Opened by customer
- `booked` - Converted to booking
- `accepted` - Approved
- `rejected` - Declined
- `expired` - Past validity date
- `cancelled` - Cancelled

**Booking Statuses:**
- `pending` - Awaiting confirmation
- `confirmed` - Confirmed
- `in_progress` - Being serviced
- `completed` - Finished
- `cancelled` - Cancelled
- `no_show` - Customer absent

---

**Document Version:** 1.0  
**Plugin Version:** 1.3.0  
**Copyright © 2026 Pro Clean Development Team**
