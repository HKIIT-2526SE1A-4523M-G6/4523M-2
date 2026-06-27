-- "NO_AUTO_VALUE_ON_ZERO" suppress generate the next sequence number for AUTO_INCREMENT column
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";

-- Database: `ProjectDB`
DROP DATABASE IF EXISTS `ProjectDB`;

CREATE DATABASE IF NOT EXISTS `ProjectDB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ProjectDB`;

DROP TABLE IF EXISTS FurnitureMaterial;
DROP TABLE IF EXISTS OrderFurniture;
DROP TABLE IF EXISTS `Order`; -- 加上反引號 避免与保留字冲突
DROP TABLE IF EXISTS Staff;
DROP TABLE IF EXISTS FurnitureOption;
DROP TABLE IF EXISTS Furniture;
DROP TABLE IF EXISTS Customer;
DROP TABLE IF EXISTS Material;

/*Data for the table `Material` */

CREATE TABLE Material (
    materialID INT NOT NULL AUTO_INCREMENT,
    materialName VARCHAR(255) NOT NULL,
    materialPhysicalQty INT NOT NULL DEFAULT 0,
    materialUnit VARCHAR(50) NOT NULL,
    PRIMARY KEY (materialID)
) ENGINE=InnoDB;
INSERT INTO Material (materialName, materialPhysicalQty, materialUnit) VALUES 
('Oak Wood Plank', 500, 'pcs'),      -- materialID = 1
('Steel Tube', 200, 'meter'),        -- materialID = 2
('Fabric Cloth', 100, 'meter'),      -- materialID = 3
('High Density Foam', 50, 'block');  -- materialID = 4

/*Data for the table `Customer` */

CREATE TABLE Customer (
    customerID       BIGINT       NOT NULL AUTO_INCREMENT,
    fullName         VARCHAR(255) NOT NULL,
    customerPassword VARCHAR(255) NOT NULL,
    customerNumber   VARCHAR(20)  NOT NULL,
    customerAddress  VARCHAR(255) NOT NULL,
    customerName     VARCHAR(255) DEFAULT NULL,
    customerEmail    VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (customerID)
) ENGINE=InnoDB AUTO_INCREMENT = 250999001;
INSERT INTO Customer (customerPassword, customerName, fullName, customerNumber, customerAddress, customerEmail) VALUES 
('111111',    'Buyer',         'My Name 666',   '87654321', 'Address TBD', 'customer@furnipro.com'),
('cust1234',  'customer_demo', 'Customer Demo', '00000001', 'Address TBD', 'customer@furnipro.com');

/*Data for the table `Furniture` */

CREATE TABLE Furniture (
    furnitureID          INT            NOT NULL AUTO_INCREMENT,
    furnitureSKU         VARCHAR(20)    DEFAULT NULL,
    furnitureName        VARCHAR(255)   NOT NULL,
    furnitureModel       VARCHAR(50)    DEFAULT NULL,
    furnitureDescription VARCHAR(255)   NOT NULL,
    furniturePrice       DECIMAL(10, 2) NOT NULL,
    furnitureImage       VARCHAR(255)   DEFAULT NULL,
    furnitureCategory    VARCHAR(100)   DEFAULT NULL,
    furnitureStockStatus INT            NOT NULL DEFAULT 1, /* 1=in-stock, 2=low-stock, 3=out-of-stock */
    PRIMARY KEY (furnitureID)
) ENGINE=InnoDB;

INSERT INTO Furniture (furnitureSKU, furnitureName, furnitureModel, furnitureDescription, furniturePrice, furnitureImage, furnitureCategory, furnitureStockStatus) VALUES
('FP-001', 'Oak Dining Chair',     'DC-101', 'Classic style dining chair made of solid oak.',        450.00,  '1.png', 'Dining',      1),  -- furnitureID = 1
('FP-002', 'Large Dining Table',   'DT-202', '6-seater dining table, perfect for families.',         2500.00, '2.png', 'Dining',      1),  -- furnitureID = 2
('FP-003', '3-Seater Fabric Sofa', 'SF-303', 'Comfortable grey fabric sofa with foam filling.',      3800.00, '3.png', 'Living Room', 2),  -- furnitureID = 3 (low-stock，对应mock的low-stock)
('FP-004', 'Wooden Wardrobe',      'WW-404', 'Double door wardrobe with hanging space.',             1800.00, '4.png', 'Bedroom',     1),  -- furnitureID = 4
('FP-005', 'Industrial Bookshelf', 'BS-505', 'Modern style bookshelf with steel frame.',             1200.00, '5.png', 'Study',       1),  -- furnitureID = 5
('FP-006', 'Queen Size Bed Frame', 'BF-606', 'Sturdy bed frame for queen size mattress.',            2200.00, '6.png', 'Bedroom',     1);  -- furnitureID = 6


/*Data for the table `FurnitureOption ` */

CREATE TABLE FurnitureOption (
    optionID    INT NOT NULL AUTO_INCREMENT,
    furnitureID INT NOT NULL,
    optionColor    VARCHAR(100) NOT NULL,
    optionMaterial VARCHAR(100) NOT NULL,
    PRIMARY KEY (optionID),
    FOREIGN KEY (furnitureID) REFERENCES Furniture(furnitureID)
) ENGINE=InnoDB;

INSERT INTO FurnitureOption (furnitureID, optionColor, optionMaterial) VALUES
-- 1. Oak Dining Chair
(1, 'natural oak', 'solid wood'),
(1, 'walnut',      'solid wood'),
-- 2. Large Dining Table
(2, 'oak',   'solid wood'),
(2, 'black', 'metal frame'),
-- 3. 3-Seater Fabric Sofa
(3, 'gray', 'fabric'),
(3, 'blue', 'fabric'),
-- 4. Wooden Wardrobe
(4, 'brown', 'solid wood'),
(4, 'white', 'laminate'),
-- 5. Industrial Bookshelf
(5, 'black', 'steel frame'),
(5, 'oak',   'wood'),
-- 6. Queen Size Bed Frame
(6, 'white',  'metal'),
(6, 'brown',  'wood veneer');

/*Data for the table `Staff` */

CREATE TABLE Staff (
    staffID       BIGINT       NOT NULL AUTO_INCREMENT,
    staffPassword VARCHAR(255) NOT NULL,
    staffName     VARCHAR(255) NOT NULL,
    staffRole     VARCHAR(50)  NOT NULL,
    staffNumber   VARCHAR(20)  NOT NULL,
    staffEmail    VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (staffID)
) ENGINE=InnoDB AUTO_INCREMENT = 250111001;
INSERT INTO Staff (staffPassword, staffName, staffRole, staffNumber, staffEmail) VALUES 
('admin',     'Admin',      'Administrator', '12345678', 'staff@furnipro.com'),
('staff1234', 'staff_demo', 'Staff',         '00000002', 'staff@furnipro.com');

/*Data for the table `Order` */

CREATE TABLE `Order` ( -- 加上反引號 避免与保留字冲突
    orderID INT NOT NULL AUTO_INCREMENT,
    orderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    orderTotalAmount DECIMAL(10, 2) NOT NULL,
    customerID BIGINT NOT NULL, -- 💡 這裡已從 INT 修改為 BIGINT，與 Customer 表保持絕對一致
    orderDeliveryDate DATETIME NOT NULL,
    orderDeliveryAddress TEXT NOT NULL,
    orderStatu INT DEFAULT 1 NOT NULL,       /*1 Pending、2 Processing、3 Delivering、4 Completed、5 Cancelled*/
    PRIMARY KEY (orderID),
    FOREIGN KEY (customerID) REFERENCES Customer(customerID)
) ENGINE=InnoDB;

/*Data for the table `OrderFurniture` */

CREATE TABLE OrderFurniture (
    orderID INT NOT NULL,
    furnitureID INT NOT NULL,
    orderQty INT NOT NULL,
    PRIMARY KEY (orderID, furnitureID),
    FOREIGN KEY (orderID) REFERENCES `Order` (orderID), -- 加上反引號 避免与保留字冲突
    FOREIGN KEY (furnitureID) REFERENCES Furniture(furnitureID)
) ENGINE=InnoDB;

/*Data for the table `FurnitureMaterial` */

CREATE TABLE FurnitureMaterial (
    furnitureID INT NOT NULL,
    materialID INT NOT NULL,
    materialRequiredQty INT NOT NULL DEFAULT 1 CHECK (materialRequiredQty > 0),
    PRIMARY KEY (furnitureID, materialID),
    FOREIGN KEY (furnitureID) REFERENCES Furniture(furnitureID),
    FOREIGN KEY (materialID) REFERENCES Material(materialID)
) ENGINE=InnoDB;
INSERT INTO FurnitureMaterial (furnitureID, materialID, materialRequiredQty) VALUES 
-- 1. Oak Dining Chair (Requires: 2 Wood Planks)
(1, 1, 2),
-- 2. Large Dining Table (Requires: 10 Wood Planks)
(2, 1, 10),
-- 3. 3-Seater Fabric Sofa (Requires: 5 Wood Planks, 10 Fabric, 3 Foam Blocks)
(3, 1, 5), (3, 3, 10), (3, 4, 3),
-- 4. Wooden Wardrobe (Requires: 15 Wood Planks)
(4, 1, 15),
-- 5. Industrial Bookshelf (Requires: 4 Wood Planks, 6 Steel Tubes)
(5, 1, 4), (5, 2, 6),
-- 6. Queen Size Bed Frame (Requires: 12 Wood Planks)
(6, 1, 12);

COMMIT;