const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../../app");
const Order = require("../../src/models/orderModel");
const Meja = require("../../src/models/mejaModel");
const Menu = require("../../src/models/menuModel");
const connectDB = require("../../src/config/database");

describe("Module 4 - Order API", () => {
  let testMenuId1, testMenuId2;

  beforeAll(async () => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.spyOn(console, 'error').mockImplementation(() => { });

    await connectDB();

    const menu1 = await Menu.create({
      name: "Nasi Goreng Spesial",
      description: "Nasi goreng dengan telur, ayam, dan sayuran segar",
      price: 25000,
      category: "main",
      isAvailable: true,
    });

    const menu2 = await Menu.create({
      name: "Sate Ayam",
      description: "Sate ayam dengan bumbu kacang khas Indonesia",
      price: 20000,
      category: "appetizer",
      isAvailable: true,
    });

    testMenuId1 = menu1._id;
    testMenuId2 = menu2._id;
  });

  beforeEach(async () => {
    await Order.deleteMany({});
    await Meja.deleteMany({});
  });

  afterAll(async () => {
    await Menu.deleteMany({});
    await Order.deleteMany({});
    await Meja.deleteMany({});
    await mongoose.connection.close();
  });

  describe("POST /createOrders", () => {
    test("harus berhasil membuat pesanan baru", async () => {
      const table = await Meja.create({
        tableNumber: 3,
        capacity: 4,
        status: "available",
      });

      const orderData = {
        tableNumber: 3,
        items: [
          { menuId: testMenuId1, quantity: 1 },
          { menuId: testMenuId2, quantity: 2 },
        ],
      };

      const response = await request(app).post("/createOrders").send(orderData);

      expect(response.status).toBe(201);
      expect(response.body).toHaveProperty("success", true);
      expect(response.body.data).toHaveProperty("tableNumber", 3);
      expect(response.body.data).toHaveProperty("total", 65000);
      expect(response.body.data).toHaveProperty("status", "pending");

      const updatedTable = await Meja.findOne({ tableNumber: 3 });
      expect(updatedTable.status).toBe("reserved");
    });

    test("harus mengembalikan error ketika meja tidak tersedia", async () => {
      const table = await Meja.create({
        tableNumber: 4,
        capacity: 4,
        status: "reserved",
      });

      const orderData = {
        tableNumber: 4,
        items: [{ menuId: testMenuId1, quantity: 1 }],
      };

      const response = await request(app).post("/createOrders").send(orderData);

      console.log(response.status);
      console.log(response.body);

      expect(response.status).toBe(400);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty(
        "error",
        "Meja tidak tersedia atau sedang dipesan",
      );
    });
  });

  describe("GET /orders", () => {
    test("harus mengembalikan daftar pesanan", async () => {
      await Meja.create({
        tableNumber: 2,
        capacity: 4,
        status: "available",
      });

      const orderData = {
        tableNumber: 2,
        items: [
          { menuId: testMenuId1, quantity: 1 },
          { menuId: testMenuId2, quantity: 2 },
        ],
      };

      await request(app).post("/createOrders").send(orderData);

      const response = await request(app).get("/orders");

      expect(response.status).toBe(200);
      expect(response.body).toHaveProperty("success", true);
      expect(Array.isArray(response.body.data)).toBe(true);
      expect(response.body.data.length).toBeGreaterThan(0);
      expect(response.body.data[0]).toHaveProperty("tableNumber", 2);
    });
  });

  describe("PUT /orders/:orderId/status", () => {
    test("harus mengubah status pesanan", async () => {
      const table = await Meja.create({
        tableNumber: 5,
        capacity: 4,
        status: "reserved",
      });

      const order = await Order.create({
        tableNumber: 5,
        items: [
          { menuId: testMenuId1, quantity: 1 },
          { menuId: testMenuId2, quantity: 2 },
        ],
        total: 65000,
        status: "pending",
      });

      const response = await request(app)
        .put(`/orders/${order._id}/status`)
        .send({ status: "completed" });

      expect(response.status).toBe(200);
      expect(response.body).toHaveProperty("success", true);
      expect(response.body.data).toHaveProperty("status", "completed");
    });

    test("harus mengembalikan status 404 jika pesanan tidak ditemukan", async () => {
      const nonExistentOrderId = new mongoose.Types.ObjectId();

      const response = await request(app)
        .put(`/orders/${nonExistentOrderId}/status`)
        .send({ status: "completed" });

      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty("error", "Pesanan tidak ditemukan");
    });
  });
});
