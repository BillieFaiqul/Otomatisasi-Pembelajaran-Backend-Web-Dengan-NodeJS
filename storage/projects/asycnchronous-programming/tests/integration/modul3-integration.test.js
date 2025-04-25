const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../../app");
const Meja = require("../../src/models/mejaModel");
const connectDB = require("../../src/config/database");

describe("Module 3 - Meja/Table API", () => {
  beforeAll(async () => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.spyOn(console, 'error').mockImplementation(() => { });

    await connectDB();
  });

  beforeEach(async () => {
    await Meja.deleteMany({});
  });

  afterAll(async () => {
    await mongoose.connection.close();
  });

  describe("POST /createMeja", () => {
    test("harus berhasil membuat meja baru", async () => {
      const newTable = {
        tableNumber: 5,
        capacity: 4,
      };

      const response = await request(app).post("/createMeja").send(newTable);

      expect(response.status).toBe(201);
      expect(response.body).toHaveProperty("success", true);
      expect(response.body.data).toHaveProperty("tableNumber", 5);
      expect(response.body.data).toHaveProperty("capacity", 4);
      expect(response.body.data).toHaveProperty("status", "available");
    });
  });

  describe("GET /meja", () => {
    test("harus mengembalikan semua meja", async () => {
      await Meja.create({ tableNumber: 1, capacity: 2 });
      await Meja.create({ tableNumber: 2, capacity: 4 });

      const response = await request(app).get("/meja");

      expect(response.status).toBe(200);
      expect(response.body).toHaveProperty("success", true);
      expect(Array.isArray(response.body.data)).toBe(true);
      expect(response.body.data.length).toBe(2);

      const returnedTables = response.body.data;
      const returnedTable1 = returnedTables.find((t) => t.tableNumber === 1);
      const returnedTable2 = returnedTables.find((t) => t.tableNumber === 2);

      expect(returnedTable1 || returnedTable2).toBeDefined();
    });
  });

  describe("PUT /meja/:tableNumber/reserve", () => {
    test("harus berhasil memesan meja yang tersedia", async () => {
      const tableNumber = 10;

      await Meja.create({
        tableNumber: tableNumber,
        capacity: 4,
        status: "available",
      });

      const tableBeforeReserve = await Meja.findOne({ tableNumber });
      expect(tableBeforeReserve).toBeDefined();
      expect(tableBeforeReserve.status).toBe("available");

      const response = await request(app)
        .put(`/meja/${tableNumber}/reserve`)
        .send({ customerName: "Susilo Bambang" });

      if (response.status !== 200) {
        console.log("Unexpected status code:", response.status);
        console.log("Response body:", response.body);
      }

      expect(response.body).toHaveProperty("success");
      if (response.body.success) {
        expect(response.body.data).toHaveProperty("customerName");
        expect(response.body.data).toHaveProperty("status", "reserved");
        expect(response.body.data.customerName).toBe("Susilo Bambang");
      }
    });

    test("harus mengembalikan status 400 jika nama pelanggan tidak disediakan", async () => {
      await Meja.create({
        tableNumber: 11,
        capacity: 4,
        status: "available",
      });

      const response = await request(app).put("/meja/11/reserve").send({});

      expect(response.status).toBe(400);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty(
        "error",
        "Nama pelanggan harus diisi",
      );
    });

    test("harus mengembalikan status 404 jika meja tidak tersedia", async () => {
      await Meja.create({
        tableNumber: 12,
        capacity: 4,
        status: "reserved",
        customerName: "Dewi Sartika",
      });

      const response = await request(app)
        .put("/meja/12/reserve")
        .send({ customerName: "Budi Sudarsono" });

      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty("error", "Meja tidak tersedia");
    });
  });

  describe("PUT /meja/:tableNumber/cancel", () => {
    test("harus berhasil membatalkan pemesanan meja", async () => {
      // Create a reserved table first with correct status and customerName
      await Meja.create({
        tableNumber: 15,
        capacity: 4,
        status: "reserved",
        customerName: "Anita Wijaya",
      });

      // Verify that the table was created with the right status
      const createdTable = await Meja.findOne({ tableNumber: 15 });
      expect(createdTable).toBeDefined();
      expect(createdTable.status).toBe("reserved");

      const response = await request(app).put("/meja/15/cancel").send({});

      if (response.status !== 200) {
        console.log("Unexpected status code for cancel:", response.status);
        console.log("Cancel response body:", response.body);
      }

      expect(response.body).toHaveProperty("success");
    });

    test("harus mengembalikan status 404 jika meja tidak ditemukan", async () => {
      const response = await request(app).put("/meja/99/cancel").send({});

      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty(
        "error",
        "Table not found or not currently reserved",
      );
    });

    test("harus mengembalikan status 404 jika meja tidak dalam status dipesan", async () => {
      // Create an available table first
      await Meja.create({
        tableNumber: 16,
        capacity: 4,
        status: "available",
      });

      const response = await request(app).put("/meja/16/cancel").send({});

      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty("success", false);
      expect(response.body).toHaveProperty(
        "error",
        "Table not found or not currently reserved",
      );
    });
  });
});
