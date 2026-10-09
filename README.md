# Inventory Management System (DMS)

A web-based Inventory Management System built with **Laravel 12**,
**MySQL**, **Tailwind CSS 4**, and **JavaScript**. The project is being
developed in **Cursor IDE**.

The system is intended to help a distribution business manage products,
inventory, sales, customer accounts, payments, and reporting from one
application.

> **Project status:** In development. Features listed below describe the
> planned scope and may not all be implemented yet.

## Technology Stack

-   **Backend:** Laravel 12 / PHP
-   **Database:** MySQL
-   **Frontend:** Blade, Tailwind CSS 4, JavaScript
-   **Development environment:** Cursor IDE; local development can use
    XAMPP or another compatible PHP/MySQL environment
-   **Authorization:** Planned role and permission management, using
    Spatie Laravel Permission if adopted in the project

## Planned Modules

-   Dashboard
-   Product categories and product management
-   Customer management
-   Supplier/company management
-   Stock receiving and inventory tracking
-   Stock ledger and stock adjustments
-   Sales invoices
-   Customer ledger and outstanding balances
-   Customer payment receipts
-   Sales returns
-   Damaged and expired stock tracking
-   Salesmen and areas/routes
-   Reports
-   User management
-   Roles and permissions
-   System settings

## Key Business Workflows

### Inventory

Inventory movements should be recorded with a transaction reference so
stock changes can be traced and audited.

**Indicative calculation:**

`Opening Stock + Stock Received + Sales Returns - Sales - Damaged/Stock Out Adjustments = Current Stock`

The final calculation will depend on the implemented transaction rules.

### Customer balances

Customer account activity should be traceable through ledger entries.

**Indicative calculation:**

`Opening Balance + Credit Sales - Payments - Sales Returns/Credit Notes = Outstanding Balance`

The backend should be the source of truth for all financial
calculations.

## Local Development Setup

The following steps are a general guide. Adjust them to match the actual
project configuration.

### Requirements

-   PHP version compatible with Laravel 12
-   Composer
-   MySQL
-   Node.js and npm
-   Git (recommended)