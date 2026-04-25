# ISP Billing Dashboard

This project turns the Excel-based ISP billing workflow into a small PHP 8.2 dashboard backed by MariaDB.

## Run with Docker

1. Copy `.env.example` to `.env` and adjust values if needed.
2. Start the stack:

```bash
docker compose up --build
```

3. Open:
   - App: http://localhost:8080
   - phpMyAdmin: http://localhost:8081

## Default login

The seed data creates a demo admin user:

- Email: admin@isp.local
- Password: admin123

## Manual input from dashboard

Open the dashboard and scroll to the **Manual Input** section.

- Billing Entry: Save monthly users, collection, commission, bonus, and discount.
- Deposit Entry: Save deposit amount, date, type, medium, and reference.
- Cost Entry: Save ISP/Software/Other cost lines by month.

You can select an existing business or provide a new business name.

## Menu pages

- Dashboard: summary, business table, deposits, and manual billing/deposit entry.
- ISP Cost: cost rows, add/edit cost entries, and monthly cost totals.
- Charts: collection vs deposit and profit trends.

## Edit deposit date and details

In Business Details, go to Deposit Tracking and click **Edit** on any row.

- Update date, amount, type, medium, and reference.
- Save changes directly from the modal form.

## Rearrange dashboard

On the dashboard header:

- Click **Rearrange Dashboard** to enable drag-and-drop ordering of sections.
- Drag sections to your preferred layout.
- Click **Done Rearranging** to lock layout.
- Click **Reset Layout** to restore default order.

Layout order is saved in browser local storage.

## PDF export and sharing

Both the dashboard page and business details page include:

- **Export PDF**: downloads a report PDF.
- **Share PDF**: uses the browser/device share sheet when supported; otherwise falls back to download.

PDF generation runs client-side using jsPDF + AutoTable.

## CSV import formats

The import page supports CSV uploads for:

- businesses: name
- collections: business_name, total_users, total_collection, month
- commissions: business_name, percentage
- bonuses: business_name, percentage
- deposits: business_name, amount, date, type, medium, reference
- discounts: business_name, amount, month
- costs: type, amount, month

## API endpoints

- `/api/get_dashboard_data.php`
- `/api/get_business_list.php`
- `/api/get_business_details.php`
