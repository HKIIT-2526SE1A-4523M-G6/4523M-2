/* ============================================
   Data Mock Center - js/data-mock.js
   ============================================ */

const MOCK_DATA = {
  products: [
    {
      sku: "FP-001",
      name: "Oak Dining Table",
      model: "DT-2026",
      category: "Dining",
      price: 899.00,
      stockStatus: "in-stock",
      options: [
        { color: "natural oak", material: "solid wood" },
        { color: "walnut", material: "solid wood" }
      ]
    },
    {
      sku: "FP-002",
      name: "Leather Sofa",
      model: "LS-310",
      category: "Living Room",
      price: 1299.00,
      stockStatus: "low-stock",
      options: [
        { color: "black", material: "leather" },
        { color: "brown", material: "leather" }
      ]
    },
    {
      sku: "FP-003",
      name: "Modern Bed Frame",
      model: "BF-450",
      category: "Bedroom",
      price: 699.00,
      stockStatus: "in-stock",
      options: [
        { color: "white", material: "metal" },
        { color: "gray", material: "wood veneer" }
      ]
    },
    {
      sku: "FP-004",
      name: "Bookshelf",
      model: "BS-120",
      category: "Study",
      price: 299.00,
      stockStatus: "out-of-stock",
      options: [
        { color: "oak", material: "wood" },
        { color: "black", material: "metal frame" }
      ]
    },
    {
      sku: "FP-005",
      name: "Coffee Table",
      model: "CT-330",
      category: "Living Room",
      price: 199.00,
      stockStatus: "in-stock",
      options: [
        { color: "glass top", material: "metal legs" },
        { color: "wood top", material: "solid oak" }
      ]
    },
    {
      sku: "FP-006",
      name: "Office Chair",
      model: "OC-210",
      category: "Office",
      price: 149.00,
      stockStatus: "in-stock",
      options: [
        { color: "black", material: "mesh" },
        { color: "blue", material: "fabric" }
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
