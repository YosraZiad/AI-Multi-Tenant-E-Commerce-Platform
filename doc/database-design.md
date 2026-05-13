# Database Design

## Objectives

- support multi-tenancy safely
- keep data relationships clear for e-commerce workflows
- allow future AI features without redesigning the core

## Core Multi-Tenant Strategy

Recommended baseline:

- shared database, shared schema
- every tenant-owned table contains tenant_id
- all data access enforces tenant scoping

Optional later:

- move to schema-per-tenant for strict isolation if enterprise requirements demand it

## Core Entities (Initial)

- tenants
- users
- tenant_users (membership/roles)
- products
- categories
- product_variants
- inventory_items
- customers
- addresses
- carts
- cart_items
- orders
- order_items
- payments
- shipments
- coupons
- audits

## Key Relationship Notes

- tenant has many users through tenant_users
- tenant has many products, orders, customers
- product has many variants
- cart belongs to customer and tenant
- order belongs to customer and tenant
- order has many order_items
- payment belongs to order

## Indexing and Constraints

- add composite indexes with tenant_id first for tenant tables
- enforce unique constraints scoped by tenant_id where needed
  - example: unique (tenant_id, sku)
  - example: unique (tenant_id, email)
- use foreign keys consistently for referential integrity

## Suggested Common Columns

For tenant-owned tables:

- id (bigint/uuid)
- tenant_id
- created_at
- updated_at
- deleted_at (if soft delete is required)

For commerce precision:

- monetary values in integer minor units (example: cents)
- explicit currency column where applicable

## Audit and Security

- store actor_id and action in audits table
- track critical changes (price, stock, role, order status)
- avoid hard delete for financial records when possible

## Migration Order (Practical)

1. tenants
2. users + tenant_users
3. catalog tables (categories, products, variants, inventory)
4. customer tables
5. cart tables
6. order/payment/shipment tables
7. audit and analytics support tables
