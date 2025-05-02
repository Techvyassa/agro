# Testing Guide for Item Master and Sales Order Management

This guide covers how to test both the Web UI and API functionality of the Item Master and Sales Order components.

## Prerequisites

- Laravel installation with database setup
- PHP 8.0+ installed
- Composer installed
- A REST API client like Postman or Insomnia (for API testing)

## Database Setup

Before testing, ensure your database is migrated with test data:

```bash
php artisan migrate
php artisan db:seed
```

## Feature Testing

### 1. Web UI Testing

#### Testing Item Master Management

1. **Access the admin dashboard**:
   - Navigate to `/admin/login`
   - Login with admin credentials
   - Verify you can see the admin dashboard with item and sales order statistics

2. **Item Listing**:
   - Go to `/admin/items`
   - Verify the table shows existing items
   - Check pagination works if you have many items

3. **Creating an Item**:
   - Click "Add New Item" button
   - Fill out the form with test data:
     - Item Code: TEST001
     - Item Name: Test Item
     - Category: Test Category
     - HSN: TEST123
     - Rate: 100.00
     - Status: Active
     - Description: This is a test item
   - Submit the form
   - Verify you're redirected to the items list with a success message
   - Verify the new item appears in the list

4. **Viewing Item Details**:
   - Click the "View" button for any item
   - Verify all item details are displayed correctly

5. **Editing an Item**:
   - Click the "Edit" button for any item
   - Change some fields
   - Submit the form
   - Verify the changes are saved and shown in the items list

6. **Deleting an Item**:
   - Click the "Delete" button for any item
   - Confirm the deletion
   - Verify the item is removed from the list

#### Testing Sales Order Management

1. **Sales Order Listing**:
   - Go to `/admin/sales`
   - Verify the table shows existing sales orders
   - Check pagination works if you have many orders

2. **Creating a Sales Order**:
   - Click "Create New Order" button
   - Fill out the form:
     - SO Number: SO001
     - Select an item from the dropdown
     - Verify that item details are auto-populated
     - Set Quantity: 5
     - Verify the Total Amount updates correctly
   - Submit the form
   - Verify you're redirected to the sales orders list with a success message
   - Verify the new order appears in the list

3. **Viewing Sales Order Details**:
   - Click the "View" button for any sales order
   - Verify all order details are displayed correctly
   - Check that the total amount calculation is correct

4. **Editing a Sales Order**:
   - Click the "Edit" button for any sales order
   - Change the quantity
   - Verify the total updates automatically
   - Submit the form
   - Verify the changes are saved and shown in the orders list

5. **Deleting a Sales Order**:
   - Click the "Delete" button for any sales order
   - Confirm the deletion
   - Verify the order is removed from the list

### 2. API Testing

You can test the API endpoints using Postman, Insomnia, or any other API testing tool.

#### Testing Item Master API

1. **Get All Items**:
   ```
   GET /api/items
   ```
   Expected response: 200 OK with JSON array of items

2. **Get Single Item**:
   ```
   GET /api/items/{id}
   ```
   Expected response: 200 OK with JSON object of the item

3. **Create Item**:
   ```
   POST /api/items
   Content-Type: application/json
   
   {
     "item_code": "API001",
     "item_name": "API Test Item",
     "category": "API Category",
     "hsn": "API123",
     "rate": 150.00,
     "description": "Item created via API",
     "is_active": true
   }
   ```
   Expected response: 201 Created with JSON object of created item

4. **Update Item**:
   ```
   PUT /api/items/{id}
   Content-Type: application/json
   
   {
     "item_code": "API001",
     "item_name": "API Updated Item",
     "category": "API Category",
     "hsn": "API123",
     "rate": 200.00,
     "description": "Item updated via API",
     "is_active": true
   }
   ```
   Expected response: 200 OK with JSON object of updated item

5. **Delete Item**:
   ```
   DELETE /api/items/{id}
   ```
   Expected response: 200 OK with success message

#### Testing Sales Order API

1. **Get All Sales Orders**:
   ```
   GET /api/sales
   ```
   Expected response: 200 OK with JSON array of sales orders

2. **Get Single Sales Order**:
   ```
   GET /api/sales/{id}
   ```
   Expected response: 200 OK with JSON object of the sales order

3. **Create Sales Order**:
   ```
   POST /api/sales
   Content-Type: application/json
   
   {
     "so_no": "API-SO001",
     "item_name": "API Test Item",
     "category": "API Category",
     "hsn": "API123",
     "qty": 10,
     "rate": 150.00
   }
   ```
   Expected response: 201 Created with JSON object of created sales order

4. **Update Sales Order**:
   ```
   PUT /api/sales/{id}
   Content-Type: application/json
   
   {
     "so_no": "API-SO001",
     "item_name": "API Test Item",
     "category": "API Category",
     "hsn": "API123",
     "qty": 15,
     "rate": 150.00
   }
   ```
   Expected response: 200 OK with JSON object of updated sales order

5. **Delete Sales Order**:
   ```
   DELETE /api/sales/{id}
   ```
   Expected response: 200 OK with success message

## Automated Testing with PHPUnit

You can also create automated tests for both Item Master and Sales Order components. Here are examples of test cases you can implement:

### Item Master Test

```php
<?php

namespace Tests\Feature;

use App\Models\ItemMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_items()
    {
        ItemMaster::factory()->count(3)->create();
        
        $response = $this->getJson('/api/items');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'items');
    }
    
    public function test_can_create_item()
    {
        $itemData = [
            'item_code' => 'TEST001',
            'item_name' => 'Test Item',
            'category' => 'Test Category',
            'hsn' => 'TEST123',
            'rate' => 100.00,
            'description' => 'Test description',
            'is_active' => true
        ];
        
        $response = $this->postJson('/api/items', $itemData);
        
        $response->assertStatus(201)
                 ->assertJsonFragment(['item_name' => 'Test Item']);
                 
        $this->assertDatabaseHas('item_masters', ['item_code' => 'TEST001']);
    }
    
    // Add more test methods for update, delete, etc.
}
```

### Sales Order Test

```php
<?php

namespace Tests\Feature;

use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_sales_orders()
    {
        SalesOrder::factory()->count(3)->create();
        
        $response = $this->getJson('/api/sales');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'sales_orders');
    }
    
    public function test_can_create_sales_order()
    {
        $orderData = [
            'so_no' => 'SO001',
            'item_name' => 'Test Item',
            'category' => 'Test Category',
            'hsn' => 'TEST123',
            'qty' => 5,
            'rate' => 100.00
        ];
        
        $response = $this->postJson('/api/sales', $orderData);
        
        $response->assertStatus(201)
                 ->assertJsonFragment(['so_no' => 'SO001']);
                 
        $this->assertDatabaseHas('sales_orders', ['so_no' => 'SO001']);
    }
    
    // Add more test methods for update, delete, etc.
}
```

## Running the Tests

To run all tests:

```bash
php artisan test
```

To run a specific test:

```bash
php artisan test --filter=ItemMasterTest
php artisan test --filter=SalesOrderTest
```