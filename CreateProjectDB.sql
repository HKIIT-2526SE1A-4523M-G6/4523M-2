DROP DATABASE IF EXISTS projectDB;
CREATE DATABASE projectDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projectDB;

CREATE TABLE Customer (
    customerID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    contactNumber VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    company VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Staff (
    staffID VARCHAR(20) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Material (
    materialID INT AUTO_INCREMENT PRIMARY KEY,
    materialName VARCHAR(100) NOT NULL,
    physicalQty INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Furniture (
    furnitureID INT AUTO_INCREMENT PRIMARY KEY,
    furnitureName VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    materialID INT,
    materialQty INT NOT NULL DEFAULT 1,
    FOREIGN KEY (materialID) REFERENCES Material(materialID)
) ENGINE=InnoDB;

CREATE TABLE Orders (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    customerID INT NOT NULL,
    furnitureID INT NOT NULL,
    orderQty INT NOT NULL,
    totalAmount DECIMAL(10,2) NOT NULL,
    deliveryAddress TEXT NOT NULL,
    deliveryDate DATETIME NOT NULL,
    orderStatus VARCHAR(20) DEFAULT 'Open',
    FOREIGN KEY (customerID) REFERENCES Customer(customerID),
    FOREIGN KEY (furnitureID) REFERENCES Furniture(furnitureID)
) ENGINE=InnoDB;

INSERT INTO Staff (staffID, password) VALUES 
('admin1', 'secret1'),
('admin2', 'secret2');

INSERT INTO Material (materialName, physicalQty, unit) VALUES 
('Oak Wood', 100, 'kg'),
('Steel Frame', 50, 'pcs');

INSERT INTO Furniture (furnitureName, description, image, price, materialID, materialQty) VALUES 
('Oak Dining Chair', 'Model: DC-101', 'assets/Sample_furntiure_images/1.png', 450.00, 1, 5),
('Industrial Bookshelf', 'Model: BS-505', 'assets/Sample_furntiure_images/5.png', 1200.00, 2, 1);