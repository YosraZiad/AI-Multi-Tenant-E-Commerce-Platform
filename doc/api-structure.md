# API Structure

## API Principles

- REST-first design
- versioned endpoints
- tenant-aware authorization and scoping
- consistent response format

## Base Pattern

- prefix: /api/v1
- authentication: token-based (Sanctum or JWT)
- tenant context resolved from authenticated membership or explicit tenant identifier

## Suggested Route Groups

- /auth
- /tenants
- /users
- /catalog
- /inventory
- /customers
- /carts
- /orders
- /payments
- /shipments
- /ai

## Example Endpoint Layout

- GET /api/v1/catalog/products
- POST /api/v1/catalog/products
- GET /api/v1/orders
- POST /api/v1/orders
- POST /api/v1/payments/intent
- POST /api/v1/ai/recommendations

## Response Envelope

Success:
{
"success": true,
"message": "OK",
"data": {},
"meta": {}
}

Error:
{
"success": false,
"message": "Validation failed",
"errors": {
"field": ["error message"]
}
}

## HTTP Status Guidelines

- 200: read/update success
- 201: create success
- 204: delete success (no body)
- 400: bad request
- 401: unauthenticated
- 403: unauthorized
- 404: not found
- 422: validation errors
- 500: internal error

## Layering Recommendation (Laravel)

- Controller: request/response only
- Action: single use case operation
- Service: orchestration and business rules
- Repository: persistence/query logic

## Security and Tenant Isolation

- all tenant-owned queries must include tenant scope
- use policies/gates for role-based permissions
- validate ownership before mutate/delete actions

## Pagination, Filtering, Sorting

- standard query params:
  - page, per_page
  - filter[field]=value
  - sort=field,-other_field
- return pagination metadata in meta

## API Documentation

- maintain OpenAPI spec for public/internal endpoints
- include request/response examples for each route
