SUPERMARKET MANAGEMENT SYSTEM — PHP + MySQL BACKEND

FILES
- schema.sql : creates the five tables and inserts 5 sample rows per table.
- db.php     : MySQL connection settings.
- api.php    : JSON API for listing, adding, updating, and deleting records.
- index.html : small test client to verify the backend API.

REQUIREMENTS
- XAMPP (Apache + MySQL) or another PHP/MySQL environment.
- PHP PDO MySQL extension enabled.

SETUP (XAMPP)
1. Copy this entire folder into: C:\xampp\htdocs\supermarket_backend_project
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin
4. Import schema.sql (Import tab), or open the SQL tab and run its contents.
5. Open db.php and set $user and $pass to your MySQL credentials.
6. Open http://localhost/supermarket_backend_project/

API EXAMPLES
GET    /api.php?table=CUSTOMER
GET    /api.php?table=STORE
GET    /api.php?table=PRODUCT
GET    /api.php?table=ORDERS
GET    /api.php?table=ORDER_ITEM

POST   /api.php?table=CUSTOMER   (JSON body with CustomerID, Name, Phone, Address)
PUT    /api.php?table=PRODUCT&id=6 (JSON body with fields to update)
DELETE /api.php?table=PRODUCT&id=6

This is a local coursework demo. Do not expose it publicly as-is. For a deployed app,
replace wildcard CORS, add authentication/authorization, CSRF protections as appropriate,
and avoid showing raw database errors to clients.
