<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# ShoppyCart

**Author:** Jan Nowak  
**Created:** December 2025

## Project Overview
ShoppyCart is a fun and modern Laravel shopping cart system with Livewire frontend, Tailwind CSS styling, and Laravel Breeze authentication.

## Key Features
- User authentication with Laravel Breeze + Livewire
- Product browsing and management
- Shopping cart functionality (add, update, remove items)
- Automated low stock notifications via queued jobs
- Daily sales reports sent via scheduled jobs

## Tech Stack
- **Backend**: Laravel 12
- **Frontend**: Livewire, Blade, Alpine.js
- **Styling**: Tailwind CSS
- **Database**: SQLite (dev), supports MySQL/PostgreSQL
- **Queue**: Database driver
- **Mail**: Log driver (dev)

## Development Guidelines

### Coding Standards
- Follow Laravel best practices
- Use Livewire for interactive UI components
- Utilize Tailwind utility classes for styling
- Keep controllers thin, use jobs for background processing
- Use Eloquent ORM for database operations

### Key Models
- `User`: Authenticated users
- `Product`: Products with stock tracking
- `Cart`: User shopping carts
- `CartItem`: Individual cart items
- `Sale`: Completed purchase records

### Important Jobs
- `NotifyLowStock`: Sends email when product stock is low
- `SendDailySalesReport`: Daily summary of sales (runs at 6 PM)

### Running Locally
Development server, queue worker, and scheduler need to be running. Frontend dev server also required.

## Notes
- Admin email address configured in `.env` as `MAIL_ADMIN_ADDRESS`
- Low stock threshold configurable per product
- All sales are recorded in `sales` table for reporting
