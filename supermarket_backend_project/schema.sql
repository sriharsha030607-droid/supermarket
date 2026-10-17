CREATE DATABASE IF NOT EXISTS supermarket;
USE supermarket;

CREATE TABLE IF NOT EXISTS CUSTOMER (
    CustomerID INT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Phone VARCHAR(20) NOT NULL UNIQUE,
    Address VARCHAR(200) NOT NULL
);

CREATE TABLE IF NOT EXISTS STORE (
    StoreID INT PRIMARY KEY,
    StoreName VARCHAR(100) NOT NULL,
    Location VARCHAR(150) NOT NULL,
    Phone VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS PRODUCT (
    ProductID INT PRIMARY KEY,
    ProductName VARCHAR(100) NOT NULL,
    Category VARCHAR(80) NOT NULL,
    Price DECIMAL(10,2) NOT NULL CHECK (Price > 0)
);

CREATE TABLE IF NOT EXISTS `ORDERS` (
    OrderID INT PRIMARY KEY,
    CustomerID INT NOT NULL,
    OrderDate DATE NOT NULL,
    TotalAmount DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (TotalAmount >= 0),
    PaymentMode ENUM('Cash','Card','UPI') NOT NULL,
    CONSTRAINT fk_orders_customer
      FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID)
      ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS ORDER_ITEM (
    OrderItemID INT PRIMARY KEY,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    UnitPrice DECIMAL(10,2) NOT NULL CHECK (UnitPrice > 0),
    Discount DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (Discount >= 0),
    CONSTRAINT fk_orderitem_order
      FOREIGN KEY (OrderID) REFERENCES `ORDERS`(OrderID)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orderitem_product
      FOREIGN KEY (ProductID) REFERENCES PRODUCT(ProductID)
      ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT IGNORE INTO CUSTOMER VALUES
(1,'Ravi Kumar','9876500001','Hyderabad'),
(2,'Ananya Rao','9876500002','Vijayawada'),
(3,'Kiran Kumar','9876500003','Guntur'),
(4,'Priya Sharma','9876500004','Warangal'),
(5,'Arjun Reddy','9876500005','Tirupati');

INSERT IGNORE INTO STORE VALUES
(1,'Central Market','Hyderabad','9123400001'),
(2,'City Supermarket','Vijayawada','9123400002'),
(3,'Fresh Mart','Guntur','9123400003'),
(4,'Green Basket','Warangal','9123400004'),
(5,'Daily Needs','Tirupati','9123400005');

INSERT IGNORE INTO PRODUCT VALUES
(1,'Rice','Grocery',65.00),
(2,'Bread','Bakery',40.00),
(3,'Milk','Dairy',30.00),
(4,'Apples','Fruits',120.00),
(5,'Cooking Oil','Grocery',150.00);

INSERT IGNORE INTO `ORDERS` VALUES
(1,1,'2026-10-01',650.00,'UPI'),
(2,2,'2026-10-02',240.00,'Cash'),
(3,3,'2026-10-03',360.00,'Card'),
(4,4,'2026-10-04',150.00,'UPI'),
(5,5,'2026-10-05',500.00,'Cash');

INSERT IGNORE INTO ORDER_ITEM VALUES
(1,1,1,10,65.00,0.00),
(2,2,2,6,40.00,10.00),
(3,3,3,12,30.00,0.00),
(4,4,4,1,120.00,5.00),
(5,5,5,4,125.00,0.00);
