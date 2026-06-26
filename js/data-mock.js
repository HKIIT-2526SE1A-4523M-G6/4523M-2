/* ============================================
   Data Mock Center - js/data-mock.js
   ============================================ */

const MOCK_DATA = {
  products: [
    {
      sku: "FP-001",
      name: "Oak Dining Chair",
      model: "DC-101",
      category: "Dining",
      price: 450.00,
      stockStatus: "in-stock",
      options: [
        { color: "natural oak", material: "solid wood" },
        { color: "walnut", material: "solid wood" }
      ]
    },
    {
      sku: "FP-002",
      name: "Large Dining Table",
      model: "DT-202",
      category: "Dining",
      price: 2500.00,
      stockStatus: "in-stock",
      options: [
        { color: "oak", material: "solid wood" },
        { color: "black", material: "metal frame" }
      ]
    },
    {
      sku: "FP-003",
      name: "3-Seater Fabric Sofa",
      model: "SF-303",
      category: "Living Room",
      price: 3800.00,
      stockStatus: "low-stock",
      options: [
        { color: "gray", material: "fabric" },
        { color: "blue", material: "fabric" }
      ]
    },
    {
      sku: "FP-004",
      name: "Wooden Wardrobe",
      model: "WW-404",
      category: "Bedroom",
      price: 1800.00,
      stockStatus: "in-stock",
      options: [
        { color: "brown", material: "solid wood" },
        { color: "white", material: "laminate" }
      ]
    },
    {
      sku: "FP-005",
      name: "Industrial Bookshelf",
      model: "BS-505",
      category: "Study",
      price: 1200.00,
      stockStatus: "in-stock",
      options: [
        { color: "black", material: "steel frame" },
        { color: "oak", material: "wood" }
      ]
    },
    {
      sku: "FP-006",
      name: "Queen Size Bed Frame",
      model: "BF-606",
      category: "Bedroom",
      price: 2200.00,
      stockStatus: "in-stock",
      options: [
        { color: "white", material: "metal" },
        { color: "brown", material: "wood veneer" }
      ]
    }
  ],

  users: [
    {
      userId: "U-001",
      username: "customer_demo",
      password: "cust1234",
      role: "customer",
      email: "customer@furnipro.com"
    },
    {
      userId: "U-002",
      username: "staff_demo",
      password: "staff1234",
      role: "staff",
      email: "staff@furnipro.com"
    }
  ]
}
