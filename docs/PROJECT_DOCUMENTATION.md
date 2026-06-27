# Svaraa Jewels - Project Documentation

**Document Version**: 1.0  
**Date**: June 2026  
**Platform**: Laravel 12.x + Filament v5

---

## Executive Summary

Svaraa Jewels is a premium e-commerce platform specializing in jewelry sales, built on Laravel with Filament admin panel. The system provides a complete online shopping experience including product browsing, cart management, secure payments via Razorpay, order processing, and administrative tools.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Technical Specifications](#technical-specifications)
4. [Database Design](#database-design)
5. [Core Modules](#core-modules)
6. [API Endpoints](#api-endpoints)
7. [Admin Panel Features](#admin-panel-features)
8. [Payment Integration](#payment-integration)
9. [Security Features](#security-features)
10. [Deployment Guide](#deployment-guide)
11. [Monitoring & Operations](#monitoring--operations)
12. [Project Requirements Specification (PRS)](#project-requirements-specification-prs)
17. [Software Requirements Specification (SRS)](#software-requirements-specification-srs)
18. [User Stories](#user-stories)
19. [Future Enhancements](#future-enhancements)

---

## Project Overview

### Project Name
Svaraa Jewels

### Project Type
E-commerce Web Application

### Technology Stack
- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Blade Templates + JavaScript
- **Admin Panel**: Filament v5
- **Database**: MySQL
- **Cache**: Redis
- **Queue**: Redis
- **Payment Gateway**: Razorpay
- **Build Tool**: Vite

### Key Features
- Product catalog with categories
- Shopping cart (guest & authenticated users)
- Wishlist management
- Secure payment processing (Razorpay)
- Cash on Delivery (COD) support
- Coupon & gift card system
- Admin dashboard with analytics
- Payment audit trail
- Refund management

---

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Client (Browser)                     │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Load Balancer / CDN                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Laravel Application                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Web Routes │  │  API Routes  │  │ Admin Panel  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         │                 │                  │              │
│         ▼                 ▼                  ▼              │
│  ┌──────────────────────────────────────────────────────┐│
│  │              Controllers & Services                      ││
│  └──────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼             ▼               ▼
┌──────────────────┐ ┌──────────────┐ ┌──────────────┐
│    MySQL DB      │ │    Redis     │ │  Razorpay API  │
│  (Primary Data)  │ │ (Cache/Queue)│ │ (Payments)     │
└──────────────────┘ └──────────────┘ └──────────────┘
```

### Design Patterns Used
- **MVC Architecture**: Standard Laravel MVC pattern
- **Repository Pattern**: Implicit via Eloquent ORM
- **Service Layer**: Dedicated service classes for business logic
- **Observer Pattern**: Payment event handling

---

## Technical Specifications

### System Requirements
| Requirement | Specification |
|-------------|----------------|
| PHP Version | ^8.2 |
| Laravel Version | ^12.0 |
| Database | MySQL 8.0+ / MariaDB 10.6+ |
| Web Server | Apache/Nginx |
| Memory | Minimum 2GB RAM |

### Composer Dependencies
```
laravel/framework: ^12.0
filament/filament: ^5.6
laravel/tinker: ^2.10.1
razorpay/razorpay: ^2.9
```

### Environment Configuration
```env
APP_NAME="Svaraa Jewels"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://svaraa.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=svaraa_production
DB_USERNAME=svaraa_user
DB_PASSWORD=secure_password

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

RAZORPAY_KEY=your_live_key
RAZORPAY_SECRET=your_live_secret
RAZORPAY_WEBHOOK_SECRET=webhook_secret
```

---

## Database Design

### Entity Relationship Diagram

```
┌─────────┐       ┌──────────┐       ┌──────────┐
│  users  │───────│   carts  │───────│ products │
└─────────┘       └──────────┘       └──────────┘
     │                 │                   │
     ▼                 ▼                   │
┌─────────┐       ┌──────────┐              │
│ address │       │ orders   │◄─────────────┘
└─────────┘       └──────────┘
     │                 │
     ▼                 ▼
┌──────────┐       ┌─────────────┐
│ wishlist │       │order_items  │
└──────────┘       └─────────────┘

┌──────────┐       ┌─────────────┐
│ category │───────┤ product_image│
└──────────┘       └─────────────┘

┌─────────┐       ┌──────────┐
│ payment │───────│ payments │
└─────────┘       └──────────┘
│ payment_logs │
│ payment_events │
│ payment_alerts │
│ admin_activity_logs │
│ daily_payment_metrics │
```

### Database Tables

#### Users Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | User's full name |
| email | string | Unique email address |
| password | string | Hashed password |
| email_verified_at | timestamp | Email verification time |

#### Products Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| category_id | bigint | Foreign key to categories |
| name | string | Product name |
| slug | string | URL-friendly slug |
| price | decimal | Product price (INR) |
| weight | decimal | Product weight |
| purity | string | Gold/Silver purity |
| description | text | Product description |
| thumbnail | string | Thumbnail image path |
| status | string | active/inactive |
| stock | integer | Available quantity |

#### Categories Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Category name |
| slug | string | URL-friendly slug |
| image | string | Category image path |
| status | string | active/inactive |

#### Orders Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key (nullable) |
| order_number | string | Unique order number (SVR-YYYYMMDD-XXXXX) |
| subtotal | decimal | Order subtotal |
| discount | decimal | Coupon discount |
| shipping | decimal | Shipping cost |
| tax | decimal | Tax amount |
| total | decimal | Total amount |
| payment_method | string | cod/card/upi |
| payment_status | string | pending/captured/failed/refunded |
| order_status | enum | pending/processing/paid/shipped/delivered/cancelled |
| customer_name | string | Customer name |
| customer_email | string | Customer email |
| customer_phone | string | Customer phone |
| shipping_address | text | Full shipping address |
| razorpay_order_id | string | Razorpay order ID |
| razorpay_payment_id | string | Razorpay payment ID |
| paid_at | timestamp | Payment captured time |

#### Order Items Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| order_id | bigint | Foreign key to orders |
| product_id | bigint | Foreign key to products |
| product_name | string | Product name at purchase |
| quantity | integer | Quantity ordered |
| price | decimal | Price per unit |
| subtotal | decimal | Line total |

#### Cart Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key (nullable for guests) |
| product_id | bigint | Foreign key |
| quantity | integer | Quantity in cart |

#### Payment Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| order_id | bigint | Foreign key |
| payment_gateway | string | razorpay |
| gateway_order_id | string | Gateway order reference |
| transaction_id | string | Payment transaction ID |
| amount | decimal | Payment amount |
| status | string | pending/captured/failed |

#### Coupons Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| code | string | Coupon code |
| type | string | percentage/fixed |
| value | decimal | Discount value |
| min_order_value | decimal | Minimum order requirement |
| usage_limit | integer | Max usage count |
| used_count | integer | Current usage count |
| expires_at | timestamp | Expiration date |
| first_order_only | boolean | First order exclusive |

#### Gift Cards Table
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| code | string | Unique gift card code |
| initial_balance | decimal | Initial balance |
| current_balance | decimal | Remaining balance |
| recipient_email | string | Recipient email |
| recipient_name | string | Recipient name |
| purchased_by | bigint | Foreign key to users |
| expires_at | timestamp | Expiration date |

---

## Core Modules

### 1. Product Module
**File**: `app/Http/Controllers/ProductController.php`

**Features**:
- Product listing with filters (category, search, price range, sort)
- Product detail page with image gallery
- Related products display
- Category-based browsing

**Service**: `app/Services/ProductService.php`
- Cached product queries (5-minute cache)
- Full-text search functionality
- Featured products selection

### 2. Cart Module
**File**: `app/Http/Controllers/CartController.php`

**Features**:
- Add/remove/update cart items
- Support for guest cart (session-based)
- Merge cart on user login
- Stock validation on add/update
- Tax calculation (18% GST)

**Service**: `app/Services/CartService.php`
- Dual storage: Database (authenticated) + Session (guests)
- Automatic cart merging
- Total calculation with tax

### 3. Checkout Module
**File**: `app/Http/Controllers/CheckoutController.php`

**Features**:
- Multi-step checkout process
- Address selection/management
- Order summary calculation
- COD and online payment support
- Order success page

**Service**: `app/Services/CheckoutService.php`
- Order creation with transaction safety
- Stock reduction on successful payment
- Email notifications (customer + admin)
- Shipping calculation (free above ₹50,000)

### 4. Payment Module
**File**: `app/Http/Controllers/RazorpayController.php`

**Features**:
- Razorpay order creation
- Payment signature verification
- Webhook handling
- Payment retry mechanism
- Status checking

**Service**: `app/Services/PaymentService.php`
- Complete Razorpay integration
- Webhook event processing (captured, authorized, failed, refunded)
- Payment reconciliation
- Automated stock management on payments

### 5. Wishlist Module
**File**: `app/Http/Controllers/WishlistController.php`

**Features**:
- Add products to wishlist
- Remove from wishlist
- View wishlist items

### 6. User Management
**Features**:
- Registration & login (Laravel Breeze)
- Email verification
- Password reset
- Profile management
- Address book

### 7. Admin Panel
**Files**: `app/Filament/Admin/Resources/*`

**Features**:
- Product management
- Category management
- Order management
- User management
- Operations dashboard
- Payment audit
- Queue health monitoring

---

## API Endpoints

### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home page |
| GET | `/shop` | Product listing |
| GET | `/product/{slug}` | Product detail |
| GET | `/category/{slug}` | Category products |
| GET | `/cart` | View cart |
| POST | `/cart/add` | Add to cart |
| POST | `/cart/update/{id}` | Update cart item |
| DELETE | `/cart/remove/{id}` | Remove from cart |
| DELETE | `/cart/clear` | Clear cart |
| POST | `/payment/create` | Create Razorpay order |
| POST | `/payment/verify` | Verify payment signature |
| POST | `/payment/retry/{order}` | Retry failed payment |
| POST | `/webhooks/razorpay` | Razorpay webhook |

### Authenticated Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | User dashboard |
| GET | `/orders` | Order history |
| GET | `/orders/{order}` | Order detail |
| GET | `/account` | Account overview |
| GET | `/account/orders` | Account orders |
| POST | `/checkout/process` | Process checkout |
| GET | `/checkout/success/{order}` | Order success |
| GET | `/wishlist` | View wishlist |
| POST | `/wishlist/add` | Add to wishlist |
| DELETE | `/wishlist/remove/{id}` | Remove from wishlist |

---

## Admin Panel Features

### Resource Management

#### Orders
- List with status filters
- View order details
- Edit order status
- Retry payment actions
- Export to CSV

#### Products
- CRUD operations
- Image management
- Category assignment
- Stock tracking
- Price management

#### Categories
- CRUD operations
- Image upload
- Status management

#### Coupons
- Create/edit coupon codes
- Type selection (percentage/fixed)
- Usage limits
- Expiration dates

### Analytics Dashboard
Access: `/admin/operations`

| Metric | Description |
|--------|-------------|
| Orders Today | Daily order count |
| Revenue Today | Daily captured revenue |
| Pending Payments | Awaiting payment |
| Failed Payments | Failed payments count |
| Refund Count | Processed refunds |
| Stock Conflicts | Stock shortage events |

### Payment Audit
Access: `/admin/payment-audit`

- Immutable payment history
- Request/response payloads
- Correlation tracking
- Latency measurement

### Queue Health
Access: `/admin/queue-health`

- Pending jobs count
- Failed jobs monitoring
- Dead-letter candidates

---

## Payment Integration

### Razorpay Integration

**Supported Payment Methods**:
1. **Credit/Debit Cards**
2. **UPI**
3. **Net Banking**
4. **Wallets**
5. **Cash on Delivery (COD)** - Manual processing

### Payment Flow

```
User Checkout → Create Order → Payment Method Selection
     ↓              ↓                ↓
   COD Flow    Razorpay Flow      COD Flow
     ↓              ↓                ↓
  Mark Paid    Create RZP Order   Mark Pending
     ↓              ↓                ↓
  Send Email   Verify Signature   Send Email
     ↓              ↓                ↓
   Success      Capture Payment    Success
```

### Webhook Events Handled

| Event | Action |
|-------|--------|
| payment.captured | Mark order as paid, reduce stock, send emails |
| payment.authorized | Store authorization, wait for capture |
| payment.failed | Mark order as failed, notify customer |
| refund.processed | Update order status, restore stock |

### Payment Retry Options

1. **Retry Payment** - Reset status to pending
2. **Retry Reconciliation** - Re-check with Razorpay API
3. **Retry Verification** - Re-verify signature
4. **Retry Email** - Resend confirmation emails
5. **Retry Finalization** - Full reconciliation + email

---

## Security Features

### Authentication & Authorization
- Laravel Breeze authentication
- Email verification
- Password hashing (bcrypt)
- Session-based authorization

### Payment Security
- Razorpay signature verification
- HMAC webhook validation
- Idempotent payment processing
- Transaction-safe stock management
- Payment event deduplication

### Data Protection
- CSRF protection
- SQL injection prevention (Eloquent)
- XSS protection (Blade escaping)
- Rate limiting on payment endpoints
- Secure session management

### Admin Security
- Filament admin panel
- Admin activity logging
- Payment action audit trail
- Failed job tracking

---

## Deployment Guide

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Redis
- Nginx/Apache

### Setup Steps

```bash
# Clone repository
git clone <repository-url>
cd svaraa-jewels

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Database migration
php artisan migrate --force

# Storage linking
php artisan storage:link
```

### Queue Worker (Supervisor)

```ini
[program:svaraa-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/svaraa-jewels/artisan queue:work redis --queue=emails,default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/svaraa-jewels/storage/logs/queue-worker.log
stopwaitsecs=3600
```

### Cron Setup

```cron
* * * * * cd /var/www/svaraa-jewels && php artisan schedule:run >> /dev/null 2>&1
```

---

## Monitoring & Operations

### CLI Commands

```bash
# Check payment alerts
php artisan payments:alerts:check

# Reconcile pending payments
php artisan payments:reconcile

# View failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {uuid}

# Clear failed jobs
php artisan queue:forget --all
```

### Alert Thresholds (.env)

```env
PAYMENT_ALERT_WEBHOOK_FAILURES=5
PAYMENT_ALERT_RECONCILIATION_FAILURES=3
PAYMENT_ALERT_STOCK_CONFLICTS=1
PAYMENT_ALERT_QUEUE_BACKLOG=50
PAYMENT_ALERT_FAILED_JOBS=1
PAYMENT_ALERT_CAPTURE_SUCCESS_RATE_MIN=90
```

---

## Project Requirements Specification (PRS)

### Business Requirements

#### BR-001: Product Catalog
- Display products with images, pricing, and descriptions
- Organize products into categories
- Enable product search and filtering
- Support product variants (weight, purity)

#### BR-002: Shopping Cart
- Allow guest users to add products to cart
- Preserve cart for authenticated users across sessions
- Enable cart modification (add/update/remove items)
- Validate stock before allowing cart operations

#### BR-003: Checkout & Payments
- Collect customer shipping information
- Support multiple payment methods (Razorpay, COD)
- Process payments securely
- Send order confirmations via email

#### BR-004: Admin Management
- Manage products and categories
- View and update order statuses
- Process refunds
- Monitor system health

#### BR-005: Discount System
- Support percentage and fixed amount coupons
- Validate coupon conditions
- Apply discounts to orders
- Track coupon usage

### Functional Requirements

#### FR-001: User Registration
- Users can register with email and password
- Email verification required
- Profile management available

#### FR-002: Product Browsing
- Paginated product listing
- Category-based filtering
- Price range filtering
- Sort by price/name/newest

#### FR-003: Cart Operations
- Add products with quantity validation
- Update quantities with stock check
- Remove items from cart
- Clear entire cart

#### FR-004: Order Placement
- Validate cart before checkout
- Calculate subtotal, shipping, tax
- Create order with unique order number
- Reduce stock on successful payment

#### FR-005: Payment Processing
- Create Razorpay order
- Verify payment signature
- Handle webhook events
- Support payment retry

---

## Software Requirements Specification (SRS)

### System Architecture

The system follows Laravel's MVC architecture with service layer pattern:

```
Request → Route → Controller → Service → Model → Database
                    ↓
              Response ← View
```

### Module Specifications

#### Product Module (FR-002)
**Input**: Category filter, search query, sort preference  
**Output**: Paginated product list with details  
**Processing**: 
- Query products from database with filters
- Apply sorting logic
- Cache results for performance

#### Cart Module (FR-003)
**Input**: Product ID, quantity  
**Output**: Updated cart state  
**Processing**:
- Validate stock availability
- Update session/database cart
- Recalculate totals

#### Checkout Module (FR-004)
**Input**: Customer details, cart contents  
**Output**: Order confirmation  
**Processing**:
- Create order in transaction
- Link order items
- Send confirmation emails
- Clear cart

#### Payment Module (FR-005)
**Input**: Order details  
**Output**: Payment status  
**Processing**:
- Create Razorpay order
- Verify signature
- Process webhook events
- Update order status

### Non-Functional Requirements

| NFR | Description |
|-----|-------------|
| Performance | Page load < 3 seconds, API response < 500ms |
| Scalability | Support 1000+ concurrent users |
| Availability | 99.9% uptime |
| Security | PCI DSS compliant payment handling |
| Maintainability | Modular code with service layer |

---

## User Stories

### Customer User Stories

| ID | User Story | Priority |
|----|------------|----------|
| US-001 | As a customer, I want to browse products by category so I can find what I need | High |
| US-002 | As a customer, I want to search products by name so I can find specific items | High |
| US-003 | As a customer, I want to add products to cart so I can purchase them later | High |
| US-004 | As a customer, I want to checkout as guest so I don't need to register | Medium |
| US-005 | As a customer, I want to pay via Razorpay so I can use multiple payment methods | High |
| US-006 | As a customer, I want to track my order status so I know delivery progress | Medium |
| US-007 | As a customer, I want to use coupons for discounts | Medium |
| US-008 | As a customer, I want to create a wishlist for future purchases | Low |

### Admin User Stories

| ID | User Story | Priority |
|----|------------|----------|
| US-101 | As an admin, I want to manage products so I can update inventory | High |
| US-102 | As an admin, I want to view orders so I can process them | High |
| US-103 | As an admin, I want to monitor payments so I can identify issues | High |
| US-104 | As an admin, I want to export orders so I can analyze sales | Medium |

---

## Future Enhancements

### Planned Features

1. **Product Reviews & Ratings**
2. **Advanced Analytics Dashboard**
3. **Multi-currency Support**
4. **Mobile App API**
5. **Push Notifications**
6. **Inventory Management Alerts**
7. **Loyalty Points System**

### Technical Improvements

1. **Elasticsearch for Product Search**
2. **Redis Caching Optimization**
3. **Microservice Architecture**
4. **Docker Containerization**
5. **CI/CD Pipeline**

---

## Appendix

### File Structure

```
app/
├── Http/Controllers/
│   ├── ProductController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   ├── OrderController.php
│   ├── RazorpayController.php
│   └── WishlistController.php
├── Services/
│   ├── CartService.php
│   ├── CheckoutService.php
│   ├── PaymentService.php
│   ├── ProductService.php
│   ├── CouponService.php
│   └── RefundService.php
├── Models/
│   ├── Product.php
│   ├── Order.php
│   ├── Cart.php
│   ├── Payment.php
│   ├── Coupon.php
│   └── User.php
└── Filament/Admin/Resources/
    ├── Orders/
    ├── Products/
    ├── Categories/
    └── Users/

database/
└── migrations/

resources/
└── views/
    ├── pages/
    ├── components/
    └── emails/
```

### License
MIT License