const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../../app");
const Order = require("../../src/models/orderModel");
const Meja = require("../../src/models/mejaModel");
const Menu = require("../../src/models/menuModel");
const connectDB = require("../../src/config/database");

describe("Module 5 - Error Handling", () => {
  let testMenuId;

  beforeAll(async () => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.spyOn(console, 'error').mockImplementation(() => { });

    await connectDB();

    // Create a test menu item
    const menu = await Menu.create({
      name: "Nasi Goreng",
      description: "Nasi goreng spesial dengan telur dan ayam",
      price: 25000,
      category: "main",
      isAvailable: true,
    });

    testMenuId = menu._id;
  });

  beforeEach(async () => {
    // Clean collections before each test
    await Order.deleteMany({});
    await Meja.deleteMany({});
  });

  afterAll(async () => {
    await Menu.deleteMany({});
    await Order.deleteMany({});
    await Meja.deleteMany({});
    await mongoose.connection.close();
  });

  describe("POST /createOrders - Error handling", () => {
    test("harus mengembalikan status 400 ketika meja tidak tersedia", async () => {
      await Meja.create({
        tableNumber: 7,
        capacity: 4,
        status: "reserved",
        customerName: "Budi Santoso",
      });

      const orderData = {
        tableNumber: 7,
        items: [{ menuId: testMenuId, quantity: 2 }],
      };

      const response = await request(app).post("/createOrders").send(orderData);

      expect(response.status).toBe(400);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty(
        "error",
        "Meja tidak tersedia atau sedang dipesan",
      );
    });

    test("harus mengembalikan status 400 ketika item menu tidak valid", async () => {
      // Create available table
      await Meja.create({
        tableNumber: 8,
        capacity: 4,
        status: "available",
      });

      const invalidMenuId = new mongoose.Types.ObjectId();

      const orderData = {
        tableNumber: 8,
        items: [{ menuId: invalidMenuId, quantity: 1 }],
      };

      const response = await request(app).post("/createOrders").send(orderData);

      expect(response.status).toBe(400);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body.error).toContain("Beberapa item menu tidak valid");
    });
  });

  describe("Global error handling", () => {
    test("harus menangani rute yang tidak ada dengan status 404", async () => {
      const response = await request(app).get("/non-existent-endpoint");

      expect(response.status).toBe(404);
    });
  });
});
